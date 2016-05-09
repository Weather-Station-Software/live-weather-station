<?php

/**
 * OWM pollution collector for Live Weather Station plugin
 *
 * @since      2.7.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-owm-pollution-client.php');


class OWM_Pollution_Collector {

    use Owm_Pollution_Client;
}