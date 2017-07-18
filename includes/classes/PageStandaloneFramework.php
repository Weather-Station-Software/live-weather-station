<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\HTTP\Client as HTTP;
use WeatherStation\System\URL\Handling as URL;

/**
 * Abstract class to interpret standalone pages in the wordpress context.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
        $query = new \WP_Query($args);
        if (sizeof($query->query_vars) > 0) {
            foreach ($query->query_vars as $key => $val) {
                switch ($key) {
                    case 'type':
                        $this->type = $val;
                        break;
                    case 'subformat':
                        $this->subformat = $val;
                        break;
                    default:
                        if (!is_array($val) && !empty($val)) {
                            $this->params[$key] = $val;
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
                        $this->subformat = strtolower($s);
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

            Logger::info('Page Generator', null, null, null, null, null , 0, 'Success while rendering file.' . HTTP::get_request_detail_as_text());
            exit();
        }
        else {
            $this->error(503);
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
        if ($code != 0) {
            $message = HTTP::get_http_status($code);
        }
        if ($message == '') {
            $message = 'Error Code ' . $code;
        }
        echo LWS_PLUGIN_NAME . ' / ' . $message;
        Logger::critical('Page Generator', null, null, null, null, null , $code, 'Unable to generate the requested page. Header "'. $message .'" sent to client.'  . HTTP::get_request_detail_as_text());
        exit();
    }
    
}