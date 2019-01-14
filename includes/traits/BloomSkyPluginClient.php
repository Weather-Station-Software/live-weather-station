<?php

namespace WeatherStation\SDK\BloomSky\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\BloomSky\BSKYApiClient;
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
        if ($this->last_bloomsky_error == '') {
            update_option('live_weather_station_bloomsky_key', $apikey);
            update_option('live_weather_station_bloomsky_connected', 1);
            return true;
        }
        else {
            update_option('live_weather_station_bloomsky_key', '');
            update_option('live_weather_station_bloomsky_connected', 0);
            return false;
        }
    }

    /**
     * Get station's datas.
     *
     * @param boolean $store Optional. Store the data.
     * @param string|boolean $apikey Optional. New API key if needed.
     *
     * @return array The bloomsky collected datas.
     * @since 3.6.0
     */
    public function get_datas($store=true, $apikey=false) {
        $currentkey = get_option('live_weather_station_bloomsky_key');
        $this->last_bloomsky_error = '';
        $this->bloomsky_datas = array();
        if ($currentkey != '' || $apikey) {
            if ($apikey) {
                $currentkey = $apikey;
            }
            $header = array();
            $header['Authorization'] = $currentkey;
            $this->bloomsky_client = new BSKYApiClient($header);
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    $this->bloomsky_datas = $this->bloomsky_client->getData();
                    //error_log(print_r($this->bloomsky_datas, true));
                    $this->normalize_bloomsky_datas();
                    if ($store) {
                        $this->store_bloomsky_datas($this->get_all_bsky_stations());
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
                        $this->last_bloomsky_error = __('Wrong credentials. Please, verify your API key.', 'live-weather-station');
                        Logger::critical('Authentication', $this->service_name, null, null, null, null, $ex->getCode(), 'Wrong credentials. Please, verify your API key.');
                        break;
                    default:
                        $this->last_bloomsky_warning = __('Temporary unable to contact BloomSky servers. Retry will be done shortly.', 'live-weather-station');
                        Logger::warning($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), $ex->getMessage());
                }
                Logger::critical($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), self::get_http_status($ex->getCode()) . ' => ' . $ex->getMessage());
                return array();
            }
        }
        return $this->bloomsky_datas;
    }

    /**
     * Detect the Netatmo stations and, optionaly, store them in the stations table.
     *
     * @param boolean $store Optional. Store the found stations in the stations table.
     * @return array An array containing stations details.
     *
     * @since 3.6.0
     */
    protected function __get_stations($store=false){
        $result = array();
        try {
            $this->get_datas(false);
            $datas = $this->bloomsky_datas ;
            foreach($datas as $station){
                $result[] = array('device_id' => $station['device_id'], 'station_name' => $station['device_name'], 'installed' => false);
            }
            if ($store) {
                foreach ($result as &$station) {
                    if ($this->insert_ignore_stations_table($station['device_id'], LWS_BSKY_SID)) {
                        $station['installed'] = true;
                        Logger::notice($this->facility, $this->service_name, $station['device_id'], $station['station_name'], null, null, null, 'Station added.');
                    }
                    else {
                        Logger::notice($this->facility, $this->service_name, $station['device_id'], $station['station_name'], null, null, null, 'This station was already added.');
                    }
                }
            }
            else {
                foreach ($this->get_all_bsky_stations() as $item) {
                    foreach ($result as &$station) {
                        if ($item['station_id'] == $station['device_id']) {
                            $station['installed'] = true;
                        }
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
        $cron_id = Watchdog::init_chrono(Watchdog::$bsky_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_datas();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_BSKY_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_BSKY_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}