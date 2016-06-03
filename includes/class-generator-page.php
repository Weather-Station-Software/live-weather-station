<?php

/**
 * Class to generate stickertags.txt text files.
 *
 * @since      3.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(dirname(__FILE__) . '/class-live-weather-station-standalone.php');


class Generator_Page extends Live_Weather_Station_Standalone {

    /**
     * Get available args.
     *
     * @since    3.0.0
     */
    protected function available_args() {
        $result = array();
        $result['type'] = ['stickertags'];
        $result['fields'] = ['station'];
        $result['variables']['station'] = '@/([A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[a-fA-F0-9]{2})/@i';
        return $result;
    }

    /**
     * Use the right generator to render the file.
     *
     * @since    3.0.0
     */
    protected function generate() {
        try {
            $file = LWS_INCLUDES_DIR . 'class-' . $this->type . '-generator.php';
            if (!file_exists($file)) {
                $this->error();
            }
            if (!include($file)) {
                $this->error();
            }
            $classname = ucfirst($this->type) . '_Generator';
            $generator = new $classname;
            $generator->send($this->params);
        }
        catch(LoggableException $ex) {
            Logger::exception($ex);
            $code = $ex->getCode();
            $message = $ex->getMessage();
            if ($code != 0) {
                $this->error($code, $message);
            }
            else {
                $this->error();
            }
        }
        catch(Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            if ($code != 0) {
                $this->error($code, $message);
            }
            else {
                $this->error();
            }
        }
    }

}