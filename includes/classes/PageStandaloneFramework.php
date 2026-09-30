<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\HTTP\Client as HTTP;
use WeatherStation\System\URL\Handling as URL;

/**
 * Abstract class to interpret standalone pages in the wordpress context.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
abstract class Framework {

    protected $type = 'unknown';
    protected $subformat = 'standard';
    protected $params = array();

    /**
     * Apply configuration.
     *
     * @since 3.0.0
     */
    public static function apply_configuration() {
        URL::apply();
    }

    /**
     * Get the back path.
     *
     * @param string $path The current path.
     * @return string The back path.
     * @since 3.0.0
     */
    private function back_path($path) {
        if (strlen($path) > 0) {
            $path = substr($path, 0, strlen($path) - 1);
            while (substr($path, -1) != '/' && substr($path, -1) != '\\' && strlen($path) > 0) {
                $path = substr($path, 0, strlen($path) - 1);
            }
        }
        return $path;
    }

    /**
     * Try to include /wp-load.php.
     *
     * @return boolean True if wp-load.php is loaded, false otherwise.
     * @since 3.0.0
     */
    private function load_wp() {
        $path = dirname(__FILE__);
        $file = 'wp-load.php';
        while (strlen($path) > 0) {
            if (file_exists($path . $file)) {
                break;
            }
            else {
                $path = $this->back_path($path);
            }
        }
        if (!file_exists($path . $file)) {
            return false;
        }
        return include($path . $file);
    }

    /**
     * Load query string elements properties.
     *
     * @since 3.0.0
     */
    protected function load($available) {
        $args = preg_replace('/no_cache=([A-F0-9])+/', '', add_query_arg(null, null));
        if (strpos($args, '?') == strlen($args)-1) {
            $args = substr($args, 0, strlen($args)-1);
        }
        if (strpos($args, '?') > 0) {
            $args = substr($args, strpos($args, '?') + 1, 2500);
        }
        // Only parse the query string: running a WP_Query here would execute a full posts query on an anonymous endpoint.
        $vars = array();
        wp_parse_str($args, $vars);
        if (sizeof($vars) > 0) {
            foreach ($vars as $key => $val) {
                switch ($key) {
                    case 'type':
                        if (is_string($val) && preg_match('/^[A-Za-z0-9_]{1,40}$/', $val)) {
                            $this->type = $val;
                        }
                        break;
                    case 'subformat':
                        if (is_string($val) && preg_match('/^[A-Za-z0-9_-]{1,40}$/', $val)) {
                            $this->subformat = $val;
                        }
                        break;
                    default:
                        // Only declared fields are kept, and only if the whole value matches the expected format.
                        if (is_string($val) && $val !== '' && in_array($key, $available['fields'], true)) {
                            if (preg_match($available['variables'][$key], '/' . $val . '/', $matches) === 1 && sizeof($matches) > 1 && $matches[1] === $val) {
                                $this->params[$key] = $val;
                            }
                        }
                }
            }
        }
        $filled = false;
        foreach ($available['fields'] as $field) {
            if (array_key_exists($field, $this->params)) {
                $filled = true;
                break;
            }
        }
        if ($this->type == 'unknown' || !$filled) {
            foreach ($available['type'] as $fulltype) {
                $type = strtolower(substr($fulltype, 0, strpos($fulltype, '.')));
                if (strpos($args, '/'.$type.'/') !== false) {
                    $this->type = $type;
                    break;
                }
                if (strpos($args, $fulltype) !== false) {
                    $this->type = $type;
                    $s = $args;
                    while (strpos($s, '/') !== false) {
                        $s = substr($s, strpos($s, '/') + 1, 2500);
                    }
                    if (strpos($s, '_'.$fulltype) !== false) {
                        $s = substr($s, 0, strpos($s, '_'.$fulltype));
                    }
                    if ($s != '') {
                        if (preg_match('/^[A-Za-z0-9_-]{1,40}$/', $s)) {
                            $this->subformat = strtolower($s);
                        }
                    }
                    break;
                }
            }
            foreach ($available['fields'] as $field) {
                if (preg_match($available['variables'][$field], $args, $matches) ==1 ) {
                    if (sizeof($matches) > 1) {
                        $this->params[$field] = $matches[1];
                    }
                }
            }
        }
    }

    /**
     * Try to initialize this standalone page in the wordpress context.
     *
     * @return boolean True if context loading is done, false otherwise.
     * @since 3.0.0
     */
    private function init() {
        $result = $this->load_wp();
        return $result;
    }

    /**
     * Rate limit the logging of this anonymous endpoint (max 60 log rows per minute) to avoid log flooding.
     *
     * @return boolean True if a log row may be written now.
     * @since 3.9.0
     */
    private static function may_log($critical=false) {
        // Fixed window (start timestamp stored, never slid) so sustained traffic cannot mute logging forever.
        // Critical rows have their own counter and are never muted by informational ones.
        $key = $critical ? 'lws_standalone_log_rate_c' : 'lws_standalone_log_rate';
        $now = time();
        $state = get_transient($key);
        if (!is_array($state) || !isset($state['s'], $state['c']) || ($now - (int)$state['s']) >= 60) {
            $state = array('s' => $now, 'c' => 0);
        }
        if ((int)$state['c'] >= 60) {
            return false;
        }
        $state['c'] = (int)$state['c'] + 1;
        set_transient($key, $state, 120);
        return true;
    }

    /**
     * Run the logic of the standalone page.
     *
     * @since 3.0.0
     */
    public function run() {
        if($this->init()) {
            run_Live_Weather_Station();
            $this->load($this->available_args());
            $this->generate();

            // For analytics
            //' . ($subformat != 'standard' ? ' for '.$subformat.'_stickertags.txt' : '') . '

            if (self::may_log()) {
                Logger::info('Page Generator', null, null, null, null, null , 0, 'Success while rendering file.' . HTTP::get_request_detail_as_text());
            }
            exit();
        }
        else {
            // WordPress is not loaded: the plugin constants and the logger are not available.
            http_response_code(503);
            header('Content-type: text/plain; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            echo 'Service unavailable.';
            exit();
        }
    }

    /**
     * Get available args.
     *
     * @since 3.0.0
     */
    abstract protected function available_args();

    /**
     * Use the generator to render the file.
     *
     * @since 3.0.0
     */
    abstract protected function generate();

    /**
     * Renders error in output.
     *
     * @param integer $code Optional. An error code.
     * @param string $message Optional. An error message.
     * @param string $header Optional. An additional header.
     * @since 3.0.0
     */
    protected function error($code = 501, $message = '', $header = '') {
        http_response_code($code);
        if ($header == '') {
            $header = 'Content-type: text/plain; charset=utf-8';
        }
        header($header);
        header('X-Content-Type-Options: nosniff');
        if ($code != 0) {
            $message = HTTP::get_http_status($code);
        }
        if ($message == '') {
            $message = 'Error Code ' . $code;
        }
        echo LWS_PLUGIN_NAME . ' / ' . $message;
        if (self::may_log(true)) {
            Logger::critical('Page Generator', null, null, null, null, null , $code, 'Unable to generate the requested page. Header "'. $message .'" sent to client.'  . HTTP::get_request_detail_as_text());
        }
        exit();
    }
    
}