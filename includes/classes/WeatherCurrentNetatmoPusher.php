<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\SDK\Netatmo\Plugin\Collector as Netatmo_Collector;
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
     * Merge data from Netatmo collector and weather computer.
     *
     * @param   array   $netatmo    Array with Netatmo collect.
     * @param   array   $comp       Array with computed values.
     *
     * @return  array   An array containing the merged data.
     * @since    2.5.0
     */
    public function merge_data($netatmo, $comp) {
        $result = array();
        if (is_array($netatmo) && count($netatmo) > 0) {
            $result = $netatmo;
        }
        if (is_array($comp) && count($comp)>0 && is_array($result) && count($result)>0) {
            foreach ($result['devices'] as &$device) {
                $mod = null;
                $k = -1;
                foreach ($comp as $key => $comp_module) {
                    if ($comp_module['device_id'] == $device['_id']) {
                        $mod = $comp_module;
                        $k = $key;
                        break;
                    }
                }
                if (isset($mod) && is_array($mod) && $k != -1) {
                    $device['modules'][] = $mod;
                }
            }
        }
        return $result;
    }

    /**
     * Do the main job.
     *
     * @since    2.5.0
     */
    public function cron_run(){
        $err = '';
        $svc = null;
        try {
            $err = 'obtaining';
            $svc = 'Netatmo';
            $netatmo = new Netatmo_Collector();
            $n = $netatmo->get_datas();
            $err = 'computing';
            $svc = null;
            $weather = new Weather_Index_Computer();
            $w = $weather->compute();
            $datas = $this->merge_data($n, $w);
            $err = 'pushing';
            $svc = 'OpenWeatherMap';
            $owmp = new OWM_Pusher();
            $owmp->post_data($datas);
            $svc = 'PWS Weather';
            $pwsp = new PWS_Pusher();
            $pwsp->post_data($datas);
            $svc = 'WOW Met Office';
            $wowp = new WOW_Pusher();
            $wowp->post_data($datas);
            $svc = 'Weather Underground';
            $wugp = new WUG_Pusher();
            $wugp->post_data($datas);
            Logger::info('Cron Engine', 'Netatmo', null, null, null, null, 0, 'Job done: obtaining, computing and pushing weather data.');
        }
        catch (\Exception $ex) {
            Logger::critical('Cron Engine', $svc, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' weather data: ' . $ex->getMessage());
        }
    }
}