<?php

namespace WeatherStation\SDK\Ambient\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Ambient\AMBTApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\HTTP\Handling as HTTP;
use WeatherStation\Data\ID\Handling as IDManager;

/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
trait Client {

    use BaseClient, HTTP, IDManager;


    /**
     * Connects to the Netatmo account.
     *
     * @since 3.6.0
     */
    public function authentication($apikey) {
        $this->get_datas(false, $apikey);
        if ($this->last_ambient_error == '') {
            update_option('live_weather_station_ambient_key', $apikey);
            update_option('live_weather_station_ambient_connected', 1);
            return true;
        }
        else {
            update_option('live_weather_station_ambient_key', '');
            update_option('live_weather_station_ambient_connected', 0);
            return false;
        }
    }

    /**
     * Get station's datas.
     *
     * @param boolean $store Optional. Store the data.
     * @param string|boolean $apikey Optional. New API key if needed.
     *
     * @return array The ambient collected datas.
     * @since 3.6.0
     */
    public function get_datas($store=true, $apikey=false) {
        $currentkey = get_option('live_weather_station_ambient_key');
        $this->last_ambient_error = '';
        $this->ambient_datas = array();
        if ($currentkey != '' || $apikey) {
            if ($apikey) {
                $currentkey = $apikey;
            }
            $this->ambient_client = new AMBTApiClient($currentkey);
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    $this->ambient_datas = $this->ambient_client->getData();
                    $this->normalize_ambient_datas();
                    if ($store) {
                        $this->store_ambient_datas($this->get_all_ambt_stations());
                    }
                    Logger::notice($this->facility, $this->service_name, null, null, null, null, 0, 'Data retrieved.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, null, null, null, null, 0, 'Quota manager has forbidden to retrieve data.');
                    return array ();
                }
            }
            catch (\Exception $ex) {
                switch ($ex->getCode()) {
                    case 401:
                        $this->last_ambient_error = __('Wrong credentials. Please, verify your API key.', 'live-weather-station');
                        Logger::critical('Authentication', $this->service_name, null, null, null, null, $ex->getCode(), 'Wrong credentials. Please, verify your API key.');
                        break;
                    default:
                        $this->last_ambient_warning = __('Temporary unable to contact Ambient servers. Retry will be done shortly.', 'live-weather-station');
                        Logger::warning($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), $ex->getMessage());
                }
                Logger::critical($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), self::get_http_status($ex->getCode()) . ' => ' . $ex->getMessage());
                return array();
            }
        }
        return $this->ambient_datas;
    }

    /**
     * Detect the Ambient stations.
     *
     * @return array An array containing stations details.
     *
     * @since 3.6.0
     */
    protected function __get_stations(){
        $result = array();
        try {
            $this->get_datas(false);
            $datas = $this->ambient_datas ;
            foreach($datas as $station){
                $result[] = array('device_id' => $station['device_id'], 'station_name' => $station['fixed_device_name'], 'installed' => false);
            }
            foreach ($this->get_all_ambt_stations() as $item) {
                foreach ($result as &$station) {
                    if ($item['station_id'] == $station['device_id']) {
                        $station['installed'] = true;
                    }
                }
            }
            Logger::info('Backend', $this->service_name, null, null, null, null, 0, 'Job done: detecting stations.');
        }
        catch (\Exception $ex) {
            Logger::critical('Backend', $this->service_name, null, null, null, null, $ex->getCode(), 'Error while detecting stations: ' . $ex->getMessage());
            return array();
        }
        return $result;
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.6.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$ambt_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_datas();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_AMBT_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_AMBT_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}