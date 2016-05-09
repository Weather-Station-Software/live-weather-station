<?php

/**
 * This class is responsible for all the croned updates from OpenWeatherMap current weather data.
 *
 * @since      2.7.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-owm-pollution-client.php');

class Owm_Pollution_Updater {

    use Owm_Pollution_Client;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.7.0
     * @param      string    $Live_Weather_Station       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     * @access   public
     */
    public function __construct( $Live_Weather_Station, $version ) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Do the main job.
     *
     * @since    2.7.0
     * @access   public
     */
    public function cron_run(){
        try {
            $this->get_datas();
        }
        catch (Exception $ex) {
            error_log(LWS_PLUGIN_NAME . ' / ' . LWS_VERSION . ' / OWM Pollution Updater / Error code: ' . $ex->getCode() . ' / Error message: ' . $ex->getMessage());
        }
    }
}