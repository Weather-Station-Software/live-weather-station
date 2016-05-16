<?php

/**
 * Abstract class to interpret standalone pages in the wordpress context.
 *
 * @since      3.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */


abstract class Live_Weather_Station_Standalone {

    protected $type = 'unknown';
    protected $params = array();

    /**
     * Get the back path.
     *
     * @param   string  $path      The current path.
     * @return  string      The back path.
     * @since    3.0.0
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
     * @return  boolean     True if wp-load.php is loaded, false otherwise.
     * @since    3.0.0
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
     * @since    3.0.0
     */
    protected function load() {
        $args = add_query_arg(null, null);
        if (strpos($args, '?') > 0) {
            $args = substr($args, strpos($args, '?') + 1, 2500);
        }
        $query = new WP_Query($args);
        if (sizeof($query->query_vars) > 0) {
            foreach ($query->query_vars as $key => $val) {
                if ($key == 'type') {
                    $this->type = $val;
                }
                elseif (!is_array($val) && !empty($val)) {
                    $this->params[$key] = $val;
                }
            }
        }
    }

    /**
     * Try to initialize this standalone page in the wordpress context.
     *
     * @return  boolean     True if context loading is done, false otherwise.
     * @since    3.0.0
     */
    private function init() {
        $result = $this->load_wp();
        return $result;
    }

    /**
     * Run the logic of the standalone page.
     *
     * @since    3.0.0
     */
    public function run() {
        if($this->init()) {
            run_Live_Weather_Station();
            $this->load();
            $this->generate();
            exit();
        }
        else {
            $this->error(503);
            exit();
        }
    }

    /**
     * Use the right generator.
     *
     * @since    3.0.0
     */
    abstract protected function generate();

    /**
     * Renders error in output.
     *
     * @param   integer     $code           Optional. An error code.
     * @param   string      $message        Optional. An error message.
     * @param   string      $header         Optional. An additional header.
     * @since    3.0.0
     */
    protected function error($code = 501, $message = '', $header = '') {
        http_response_code($code);
        if ($header == '') {
            $header = 'Content-type: text/plain; charset=utf-8';
        }
        header($header);
        if ($message == '' && $code == 501) {
            $message = LWS_PLUGIN_NAME . ' / Service Not Implemented / 501';
        }
        if ($message == '' && $code == 400) {
            $message = LWS_PLUGIN_NAME . ' / Bad Request / 400';
        }
        if ($message == '' && $code == 403) {
            $message = LWS_PLUGIN_NAME . ' / Forbidden / 403';
        }
        if ($message == '') {
            $message = 'Error Code ' . $code;
        }
        echo $message;
        exit();
    }
    
}