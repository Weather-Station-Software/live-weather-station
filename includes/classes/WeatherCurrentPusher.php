<?php

namespace WeatherStation\SDK\Generic\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\OpenWeatherMap\Plugin\Pusher as OWM_Pusher;
use WeatherStation\SDK\PWSWeather\Plugin\Pusher as PWS_Pusher;
use WeatherStation\SDK\MetOffice\Plugin\Pusher as WOW_Pusher;
use WeatherStation\SDK\WeatherUnderground\Plugin\Pusher as WUG_Pusher;

/**
 * This class is responsible for all the croned pushes to weather services.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.5.0
 */
class Pusher {

    private $Live_Weather_Station;
    private $version; 

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.5.0
     * @param      string    $Live_Weather_Station       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $Live_Weather_Station, $version ) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Do the main job.
     *
     * @since 2.5.0
     */
    public function cron_run(){
        $svc = null;
        try {
            $svc = 'OpenWeatherMap';
            $owmp = new OWM_Pusher();
            $owmp->push_data();
            Logger::info('Cron Engine', $svc, null, null, null, null, 0, 'Job done: pushing weather data.');
            $svc = 'PWS Weather';
            $pwsp = new PWS_Pusher();
            $pwsp->push_data();
            Logger::info('Cron Engine', $svc, null, null, null, null, 0, 'Job done: pushing weather data.');
            $svc = 'WOW Met Office';
            $wowp = new WOW_Pusher();
            $wowp->push_data();
            Logger::info('Cron Engine', $svc, null, null, null, null, 0, 'Job done: pushing weather data.');
            $svc = 'Weather Underground';
            $wugp = new WUG_Pusher();
            $wugp->push_data();
            Logger::info('Cron Engine', $svc, null, null, null, null, 0, 'Job done: pushing weather data.');
        }
        catch (\Exception $ex) {
            Logger::critical('Cron Engine', $svc, null, null, null, null, $ex->getCode(), 'Error while pushing weather data: ' . $ex->getMessage());
        }
    }
}