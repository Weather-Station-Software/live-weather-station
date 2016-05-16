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
     * Use the right generator to render the file.
     *
     * @since    3.0.0
     */
    protected function generate() {
        try {
            if (!include(LWS_INCLUDES_DIR . 'class-' . $this->type . '-generator.php')) {
                $this->error();
            }
            $classname = ucfirst($this->type) . '_Generator';
            $generator = new $classname;
            $generator->send($this->params);
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