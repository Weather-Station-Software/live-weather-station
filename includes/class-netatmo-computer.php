<?php

/**
 * Netatmo computer for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-netatmo-client.php');


class Netatmo_Computer {

    use Netatmo_Client;
}