<?php

/**
 * Class for manipulating HTTP
 *
 * @since      3.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-http-manipulation.php');


class Live_Weather_Station_Http {

    use Http_Manipulation;
}