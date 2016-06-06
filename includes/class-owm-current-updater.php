<?php

/**
 * This class is responsible for all the croned updates from OpenWeatherMap current weather data.
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-owm-current-client.php');
require_once(LWS_INCLUDES_DIR.'class-weather-computer.php');
require_once(LWS_INCLUDES_DIR.'class-ephemeris-computer.php');

class Owm_Current_Updater {

    use Owm_Current_Client;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.0.0
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
     * @since    2.0.0
     * @access   public
     */
    public function cron_run(){
        $err = '';
        try {
            $err = 'collecting weather';
            $svc = 'OpenWeatherMap';
            $this->get_datas();
            $err = 'computing weather';
            $weather = new Weather_Computer();
            $weather->compute();
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute();
            Logger::info('Cron Engine', 'OpenWeatherMap', null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (Exception $ex) {
            Logger::critical('Cron Engine', 'OpenWeatherMap', null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
    }
}