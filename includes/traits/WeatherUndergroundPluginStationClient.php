<?php

namespace WeatherStation\SDK\WeatherUnderground\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\WeatherUnderground\WUGApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;


/**
 * WeatherUnderground station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait StationClient {

    use BaseClient;

    protected $wug_data;
    protected $facility = 'Weather Collector';

    /**
     * Verify if a station exists.
     *
     * @param string $id The station ID.
     * @return boolean True if the station exists, false otherwise.
     * @since 3.0.0
     */
    public static function station_exists($id) {
        if (($key = get_option('live_weather_station_wug_apikey')) == '') {
            return false;
        }
        $wug = new WUGApiClient();
        try {
            $raw_data = $wug->getRawStationData($id, $key);
            $weather = json_decode($raw_data, true);
            if (is_array($weather)) {
                if (array_key_exists('response', $weather)) {
                    if (!array_key_exists('error', $weather['response'])) {
                        return true;
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            return false;
        }
        return false;
    }

    /**
     * Format and store data.
     *
     * @param string $json_weather Weather array json formated.
     * @param array $station Station array.
     * @throws \Exception
     * @since 3.0.0
     */
    private function format_and_store($json_weather, $station) {
        $weather = json_decode($json_weather, true);
        if (!is_array($weather)) {
            throw new \Exception('JSON / '.(string)$json_weather);
        }
        if (array_key_exists('response', $weather)) {
            if (array_key_exists('error', $weather['response'])) {
                if (array_key_exists('description', $weather['response']['error'])) {
                    throw new \Exception($weather['response']['error']['description']);
                }
                else {
                    throw new \Exception('WeatherUnderground unknown exception');
                }
            }
            if (array_key_exists('features', $weather['response'])) {
                if (array_key_exists('conditions', $weather['response']['features'])) {
                    if ($weather['response']['features']['conditions'] != 1) {
                        throw new \Exception($weather['response']['error']['description']);
                    }
                }
                else {
                    throw new \Exception('WeatherUnderground unknown exception');
                }
            }
        }
        if (!empty($weather) && array_key_exists('current_observation', $weather)) {
            $observation = $weather['current_observation'];
            if (array_key_exists('display_location', $observation)) {
                $location = $observation['display_location'];
            }
            else {
                Logger::notice($this->facility, $this->service_name, $station['station_id'], $station['station_name'], null, null, 0, 'Data are empty or irrelevant.');
                return;
            }
            if ($station['station_name'] == '') {
                if (array_key_exists('observation_location', $observation)) {
                    $observation_location = $observation['observation_location'];
                    if (array_key_exists('full', $observation_location)) {
                        $station['station_name'] = substr($observation_location['full'], 0, 59);
                    }
                }
            }
            Logger::debug($this->facility, null, null, null, null, null, null, print_r($observation, true));
            if (array_key_exists('observation_epoch', $observation)) {
                $timestamp = date('Y-m-d H:i:s', $observation['observation_epoch']);
            }
            else {
                $timestamp = date('Y-m-d H:i:s');
            }
            // NAMain
            $type = 'NAMain';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $station['station_id'];
            $updates['module_type'] = $type;
            $updates['module_name'] = $this->get_fake_module_name($type);
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'last_refresh';
            $updates['measure_value'] = date('Y-m-d H:i:s');
            $this->update_data_table($updates);
            $updates['measure_type'] = 'last_seen';
            $updates['measure_value'] = $timestamp;
            $updates['measure_timestamp'] = $timestamp;
            $this->update_data_table($updates);
            $updates['measure_timestamp'] = $timestamp;
            if (array_key_exists('city', $location)) {
                $station['loc_city'] = substr($location['city'], 0, 59);
                $updates['measure_type'] = 'loc_city';
                $updates['measure_value'] = $station['loc_city'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('country_iso3166', $location)) {
                $station['loc_country_code'] = substr($location['country_iso3166'], 0, 2);
                $updates['measure_type'] = 'loc_country';
                $updates['measure_value'] = $station['loc_country_code'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('local_tz_long', $observation)) {
                $station['loc_timezone'] = substr($observation['local_tz_long'], 0, 49);
                $updates['measure_type'] = 'loc_timezone';
                $updates['measure_value'] = $station['loc_timezone'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('latitude', $location)) {
                $station['loc_latitude'] = $location['latitude'];
                $updates['measure_type'] = 'loc_latitude';
                $updates['measure_value'] = $station['loc_latitude'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('longitude', $location)) {
                $station['loc_longitude'] = $location['longitude'];
                $updates['measure_type'] = 'loc_longitude';
                $updates['measure_value'] = $station['loc_longitude'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('elevation', $location)) {
                $station['loc_altitude'] = $location['elevation'];
                $updates['measure_type'] = 'loc_altitude';
                $updates['measure_value'] = $station['loc_altitude'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('pressure_mb', $observation)) {
                $updates['measure_type'] = 'pressure';
                $updates['measure_value'] = $observation['pressure_mb'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('pressure_trend', $observation)) {
                $updates['measure_type'] = 'pressure_trend';
                $updates['measure_value'] = 'stable';
                if ($observation['pressure_trend'] == '-') {
                    $updates['measure_value'] = 'down';
                }
                elseif ($observation['pressure_trend'] == '+') {
                    $updates['measure_value'] = 'up';
                }
                $this->update_data_table($updates);
            }

            // NAModule1
            $type = 'NAModule1';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 1);
            $updates['module_type'] = $type;
            $updates['module_name'] = $this->get_fake_module_name($type);
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'last_refresh';
            $updates['measure_value'] = date('Y-m-d H:i:s');
            $this->update_data_table($updates);
            $updates['measure_type'] = 'last_seen';
            $updates['measure_value'] = $timestamp;
            $updates['measure_timestamp'] = $timestamp;
            $this->update_data_table($updates);
            $updates['measure_timestamp'] = $timestamp;
            if (array_key_exists('temp_c', $observation)) {
                $updates['measure_type'] = 'temperature';
                $updates['measure_value'] = $observation['temp_c'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('relative_humidity', $observation)) {
                $updates['measure_type'] = 'humidity';
                $updates['measure_value'] = $observation['relative_humidity'];
                $updates['measure_value'] = (integer)str_replace('%', '', $updates['measure_value']);
                $this->update_data_table($updates);
            }
            $this->update_table(self::live_weather_station_stations_table(), $station);
            Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

            // NAModule2
            $type = 'NAModule2';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 2);
            $updates['module_type'] = $type;
            $updates['module_name'] = $this->get_fake_module_name($type);
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'last_refresh';
            $updates['measure_value'] = date('Y-m-d H:i:s');
            $this->update_data_table($updates);
            $updates['measure_type'] = 'last_seen';
            $updates['measure_value'] = $timestamp;
            $updates['measure_timestamp'] = $timestamp;
            $this->update_data_table($updates);
            $updates['measure_timestamp'] = $timestamp;
            if (array_key_exists('wind_degrees', $observation)) {
                $updates['measure_type'] = 'windangle';
                $updates['measure_value'] = $observation['wind_degrees'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'gustangle';
                $updates['measure_value'] = $observation['wind_degrees'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('wind_kph', $observation)) {
                $updates['measure_type'] = 'windstrength';
                $updates['measure_value'] = $observation['wind_kph'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('wind_gust_kph', $observation)) {
                $updates['measure_type'] = 'guststrength';
                $updates['measure_value'] = $observation['wind_gust_kph'];
                $this->update_data_table($updates);
            }
            $this->update_table(self::live_weather_station_stations_table(), $station);
            Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

            // NAModule3
            $type = 'NAModule3';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 3);
            $updates['module_type'] = $type;
            $updates['module_name'] = $this->get_fake_module_name($type);
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'last_refresh';
            $updates['measure_value'] = date('Y-m-d H:i:s');
            $this->update_data_table($updates);
            $updates['measure_type'] = 'last_seen';
            $updates['measure_value'] = $timestamp;
            $updates['measure_timestamp'] = $timestamp;
            $this->update_data_table($updates);
            $updates['measure_timestamp'] = $timestamp;

            if (array_key_exists('precip_1hr_metric', $observation)) {
                $updates['measure_type'] = 'rain_hour_aggregated';
                $updates['measure_value'] = $observation['precip_1hr_metric'];
                $this->update_data_table($updates);
            }
            if (array_key_exists('precip_today_metric', $observation)) {
                $updates['measure_type'] = 'rain_day_aggregated';
                $updates['measure_value'] = $observation['precip_today_metric'];
                $this->update_data_table($updates);
            }
            $this->update_table(self::live_weather_station_stations_table(), $station);
            Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');



        }
        else {
            Logger::notice($this->facility, $this->service_name, $station['station_id'], $station['station_name'], null, null, 0, 'Data are empty or irrelevant.');
        }
    }

    /**
     * Get and store station's data.
     *
     * @return array WUG collected data.
     * @since 3.0.0
     */
    public function get_and_store_data() {
        if (($key = get_option('live_weather_station_wug_apikey')) == '') {
            $this->wug_data = array ();
            return array ();
        }
        $this->synchronize_wug_station();
        $this->wug_data = array();
        $stations = $this->get_all_wug_id_stations();
        $wug = new WUGApiClient();
        foreach ($stations as $station) {
            try {
                $raw_data = $wug->getRawStationData($station['service_id'], $key);
                $this->format_and_store($raw_data, $station);
            }
            catch(\Exception $ex)
            {
                if (isset($station['device_id']) && isset($station['device_name'])) {
                    $device_id = $station['device_id'];
                    $device_name = $station['device_name'];
                }
                else {
                    $device_id = null;
                    $device_name = null;
                }
                if (strpos($ex->getMessage(), 'this key does not exist') !== false) {
                    Logger::critical($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Wrong credentials. Please, verify your WeatherUnderground API key.');
                    return array();
                }
                if (strpos($ex->getMessage(), 'JSON /') > -1) {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'WeatherUnderground servers has returned empty response. Retry will be done shortly.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Temporary unable to contact WeatherUnderground servers. Retry will be done shortly.');
                    return array();
                }
            }
            if (isset($values) && is_array($values)) {
                $this->wug_data[] = $values;
            }
        }
        return $this->wug_data;
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_and_store_data();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute();
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute();
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
    }
}