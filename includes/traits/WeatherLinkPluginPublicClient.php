<?php

namespace WeatherStation\SDK\WeatherLink\Plugin;

use WeatherStation\SDK\WeatherLink\Exception;
use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\WeatherLink\WLINKApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Unit\Conversion as Units;


/**
 * WeatherLink station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
trait PublicClient {

    use BaseClient, Units;

    protected $facility = 'Weather Collector';
    public $detected_station_name = '';
    public $detected_station_model = 'Davis Instruments';
    public $detected_timezone = '';
    public $detected_city = '';
    public $detected_latitude = 0;
    public $detected_longitude = 0;
    public $detected_altitude = 0;
    public $station_id = '';

    /**
     * Verify if a station is accessible.
     *
     * @param string $id The service ID.
     * @return string The error message, empty string otherwise.
     * @since 3.8.0
     */
    public function test_station($id) {
        $result = 'unknown station ID';
        try {
            $wlink = new WLINKApiClient();
            Quota::verify(self::$service, 'GET');
            $raw_data = $wlink->getRawStationMeta($id);
            $weather = json_decode($raw_data, true);
            if (is_array($weather)) {
                $result = '';
                if (array_key_exists('station_did', $weather)) {
                    $this->station_id = self::compute_unique_wlink_id($weather['station_did']);
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('station_name', $weather)) {
                    $this->detected_station_name = $weather['station_name'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('station_type', $weather)) {
                    $this->detected_station_model .= ' - ' . $weather['station_type'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('station_timezone', $weather)) {
                    $this->detected_timezone = $weather['station_timezone'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('user_city', $weather)) {
                    $this->detected_city = $weather['user_city'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('station_latitude', $weather)) {
                    $this->detected_latitude = $weather['station_latitude'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('station_longitude', $weather)) {
                    $this->detected_longitude = $weather['station_longitude'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
                if (array_key_exists('station_elevation_m', $weather)) {
                    $this->detected_altitude = $weather['station_elevation_m'];
                }
                else {
                    $result = 'WeatherLink sent inconsistent data';
                }
            }
            else {
                $result = 'no station with this credentials';
            }
        }
        catch(\Exception $ex)
        {
            $result = 'unable to contact WeatherLink servers';
        }
        return $result;
    }

    /**
     * Format and store data.
     *
     * @param string $json_meta Metadata array json formated.
     * @param string $json_data Data array json formated.
     * @param array $station Station array.
     * @throws \Exception
     * @since 3.8.0
     */
    private function format_and_store($json_meta, $json_data, $station) {
        $meta = json_decode($json_meta, true);
        $data = json_decode($json_data, true);
        if (!is_array($meta)) {
            throw new \Exception('JSON / Meta: '.(string)$meta);
        }
        if (!is_array($data)) {
            throw new \Exception('JSON / Data: '.(string)$data);
        }
        $weather = array_merge($data, $meta);
        Logger::debug($this->facility, $this->service_name, null, null, null, null, null, print_r($weather, true));
        if (!empty($weather) && is_array($weather)) {
            if (array_key_exists('station_name', $weather)) {
                $station['station_name'] = $weather['station_name'];
            }
            if ($station['station_name'] == '') {
                $station['station_name'] = '< NO NAME >';
            }
            if (array_key_exists('station_timezone', $weather)) {
                $station['loc_timezone'] = $weather['station_timezone'];
            }
            $timezone = $this->get_timezone($station, null, $station['guid'], $station['station_id']);
            if (array_key_exists('user_city', $weather)) {
                $station['loc_city'] = $weather['user_city'];
            }
            if (array_key_exists('station_latitude', $weather)) {
                $station['loc_latitude'] = $weather['station_latitude'];
            }
            if (array_key_exists('station_longitude', $weather)) {
                $station['loc_longitude'] = $weather['station_longitude'];
            }
            if (array_key_exists('station_elevation_m', $weather)) {
                $station['loc_altitude'] = $weather['station_elevation_m'];
            }

            if (array_key_exists('observation_time_rfc822', $weather)) {
                try {
                    $timestamp = date('Y-m-d H:i:s', strtotime($weather['observation_time_rfc822']));
                } catch (Exception $e) {
                    $timestamp = date('Y-m-d H:i:s');
                }
            } else {
                $timestamp = date('Y-m-d H:i:s');
            }
            $observation = array();
            if (array_key_exists('davis_current_observation', $weather)) {
                $observation = $weather['davis_current_observation'];
            }

            $pressure_ref = null;
            $temperature_ref = null;
            $humidity_ref = null;
            $updates = array();

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
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'last_seen';
            $updates['measure_value'] = $timestamp;
            $updates['measure_timestamp'] = $timestamp;
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'loc_city';
            $updates['measure_value'] = $station['loc_city'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_country';
            $updates['measure_value'] = $station['loc_country_code'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_timezone';
            $updates['measure_value'] = $station['loc_timezone'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_latitude';
            $updates['measure_value'] = $station['loc_latitude'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_longitude';
            $updates['measure_value'] = $station['loc_longitude'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_altitude';
            $updates['measure_value'] = $station['loc_altitude'];
            $this->update_data_table($updates, $timezone);
            if (array_key_exists('pressure_mb', $weather)) {
                if (array_key_exists('temp_c', $weather)) {
                    $temperature_ref = $weather['temp_c'];
                }
                $updates['measure_type'] = 'pressure';
                $pressure_ref = $this->convert_from_mslp_to_baro($weather['pressure_mb'], $station['loc_altitude'], $temperature_ref);
                $updates['measure_value'] = $pressure_ref;
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'pressure_sl';
                $updates['measure_value'] = $weather['pressure_mb'];
                $this->update_data_table($updates, $timezone);
            }
            if (array_key_exists('user_registered_unix', $weather)) {
                $updates['measure_type'] = 'last_setup';
                $updates['measure_value'] = date('Y-m-d H:i:s', $weather['user_registered_unix']);
                $this->update_data_table($updates, $timezone);
            }
            if (array_key_exists('station_firmware', $weather)) {
                $updates['measure_type'] = 'firmware';
                $updates['measure_value'] = $weather['station_firmware'];
                $this->update_data_table($updates, $timezone);
            }
            $station['last_refresh'] = date('Y-m-d H:i:s');
            $station['last_seen'] = $timestamp;
            $this->update_table(self::live_weather_station_stations_table(), $station);
            Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');


            // NAModule1
            if (array_key_exists('temp_c', $weather) || array_key_exists('relative_humidity', $weather)) {
                $type = 'NAModule1';
                $updates['device_id'] = $station['station_id'];
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 1);
                $updates['module_type'] = $type;
                $updates['module_name'] = $this->get_fake_module_name($type);
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates, $timezone);
                $updates['measure_timestamp'] = $timestamp;
                if (array_key_exists('temp_c', $weather)) {
                    $updates['measure_type'] = 'temperature';
                    $temperature_ref = $weather['temp_c'];
                    $updates['measure_value'] = $temperature_ref;
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('relative_humidity', $weather)) {
                    $updates['measure_type'] = 'humidity';
                    $humidity_ref = $weather['relative_humidity'];
                    $updates['measure_value'] = $humidity_ref;
                    $this->update_data_table($updates, $timezone);
                }
                if (isset($temperature_ref) && isset($pressure_ref) && isset($humidity_ref)) {
                    $updates['measure_type'] = 'absolute_humidity';
                    $updates['measure_value'] = $this->compute_partial_absolute_humidity($temperature_ref, 100 * $pressure_ref, $humidity_ref);
                    $this->update_data_table($updates, $timezone);
                    $temperature_ref = null;
                    $humidity_ref = null;
                }
                Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
            }

            // NAModule2
            if (array_key_exists('wind_degrees', $weather) || array_key_exists('wind_mph', $weather)) {
                $type = 'NAModule2';
                $updates['device_id'] = $station['station_id'];
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 2);
                $updates['module_type'] = $type;
                $updates['module_name'] = $this->get_fake_module_name($type);
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates, $timezone);
                $updates['measure_timestamp'] = $timestamp;
                if (array_key_exists('wind_degrees', $weather)) {
                    $updates['measure_type'] = 'windangle';
                    $updates['measure_value'] = $weather['wind_degrees'];
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'winddirection';
                    $updates['measure_value'] = (int)floor(($weather['wind_degrees'] + 180) % 360);
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'gustangle';
                    $updates['measure_value'] = $weather['wind_degrees'];
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'gustdirection';
                    $updates['measure_value'] = (int)floor(($weather['wind_degrees'] + 180) % 360);
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('wind_mph', $weather)) {
                    $updates['measure_type'] = 'windstrength';
                    $updates['measure_value'] = $this->get_reverse_wind_speed($weather['wind_mph'], 1);
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('wind_ten_min_gust_mph', $observation)) {
                    $updates['measure_type'] = 'guststrength';
                    $updates['measure_value'] = $this->get_reverse_wind_speed($observation['wind_ten_min_gust_mph'], 1);
                    $this->update_data_table($updates, $timezone);
                }
                Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
            }

            // NAModule3
            if (array_key_exists('rain_day_in', $observation) || array_key_exists('rain_rate_in_per_hr', $observation)) {
                $type = 'NAModule3';
                $updates['device_id'] = $station['station_id'];
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 3);
                $updates['module_type'] = $type;
                $updates['module_name'] = $this->get_fake_module_name($type);
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates, $timezone);
                $updates['measure_timestamp'] = $timestamp;
                if (array_key_exists('rain_rate_in_per_hr', $observation)) {
                    $updates['measure_type'] = 'rain';
                    $updates['measure_value'] = $this->get_reverse_rain($observation['rain_rate_in_per_hr'], 1);
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('rain_day_in', $observation)) {
                    $updates['measure_type'] = 'rain_day_aggregated';
                    $updates['measure_value'] = $this->get_reverse_rain($observation['rain_day_in'], 1);
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('rain_month_in', $observation)) {
                    $updates['measure_type'] = 'rain_month_aggregated';
                    $updates['measure_value'] = $this->get_reverse_rain($observation['rain_month_in'], 1);
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('rain_year_in', $observation)) {
                    $updates['measure_type'] = 'rain_year_aggregated';
                    $updates['measure_value'] = $this->get_reverse_rain($observation['rain_year_in'], 1);
                    $this->update_data_table($updates, $timezone);
                }
                Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
            }

            // NAModule5
            if (array_key_exists('uv_index', $observation) || array_key_exists('solar_radiation', $observation)) {
                $type = 'NAModule5';
                $updates['device_id'] = $station['station_id'];
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 5);
                $updates['module_type'] = $type;
                $updates['module_name'] = $this->get_fake_module_name($type);
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates, $timezone);
                if (array_key_exists('uv_index', $observation)) {
                    $updates['measure_type'] = 'uv_index';
                    $updates['measure_value'] = $observation['uv_index'];
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('solar_radiation', $observation)) {
                    $updates['measure_type'] = 'irradiance';
                    $updates['measure_value'] = $observation['solar_radiation'];
                    $this->update_data_table($updates, $timezone);
                }
                $this->update_data_table($updates, $timezone);
                Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
            }
        }
        else {
            Logger::notice($this->facility, $this->service_name, $station['station_id'], $station['station_name'], null, null, 0, 'Data are empty or irrelevant.');
        }
    }

    /**
     * Get and store station's data.
     *
     * @since 3.8.0
     */
    public function get_and_store_data() {
        $this->synchronize_wlink_station();
        $stations = $this->get_all_wlink_id_stations();
        foreach ($stations as $st => $station) {
            $device_id = $station['station_id'];
            $device_name = $station['station_name'];
            try {
                $wlink = new WLINKApiClient();
                if (Quota::verify($this->service_name, 'GET', 2)) {
                    $raw_meta = $wlink->getRawStationMeta($station['service_id']);
                    $raw_data = $wlink->getRawStationData($station['service_id']);
                    $this->format_and_store($raw_meta, $raw_data, $station);
                    Logger::notice($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Data retrieved.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Quota manager has forbidden to retrieve data.');
                }
            }
            catch(\Exception $ex)
            {
                if (strpos($ex->getMessage(), 'JSON /') !== false) {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'WeatherLink servers has returned empty response. Retry will be done shortly.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Temporary unable to contact WeatherLink servers. Retry will be done shortly.');
                    return array();
                }
            }
        }
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.8.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$wlink_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_and_store_data();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_WLINK_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_WLINK_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}