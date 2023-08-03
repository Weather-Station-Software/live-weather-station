<?php

namespace WeatherStation\SDK\Clientraw\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Generic\FileClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\ID\Handling as Id_Manipulation;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index as Weather_Index_Client;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\Data\DateTime\Conversion;
use WeatherStation\Data\Type\Description;
use WeatherStation\Data\Unit\Conversion as Units;
use WeatherStation\System\Schedules\Watchdog;


/**
 * Clientraw station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait StationClient {

    use Id_Manipulation, Conversion, Description, Units, Dashboard_Manipulation;

    protected $facility = 'Weather Collector';
    protected $service = 'File Handler - Clientraw';

    /**
     * Format and store data.
     *
     * @param string $raw_data Weather raw data.
     * @param array $station Station array.
     * @throws \Exception
     * @since 3.0.0
     */
    private function format_and_store($raw_data, $station) {
        $weather = $this->explode_data($raw_data);
        if (false === $weather) {
            throw new \Exception('Empty or unreadable file.');
        }
        else {
            try {
                $timezone = $station['loc_timezone'];
                $locat_ts = gmmktime((int)$weather[29], (int)$weather[30], (int)$weather[31], (int)$weather[36], (int)$weather[35], (int)$weather[141]);
                $timestamp = date('Y-m-d H:i:s', $this->get_date_from_tz($locat_ts, $timezone));
            }
            catch (\Exception $e) {
                throw new \Exception('Bad file format.');
            }
        }


        // NAMain
        try {
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
            $updates['measure_type'] = 'loc_city';
            $updates['measure_value'] = $station['loc_city'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_country';
            $updates['measure_value'] = $station['loc_country_code'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_timezone';
            $updates['measure_value'] = $station['loc_timezone'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'loc_altitude';
            $updates['measure_value'] = $station['loc_altitude'];
            $this->update_data_table($updates, $timezone);
            $station['loc_latitude'] = $weather[160];
            $updates['measure_type'] = 'loc_latitude';
            $updates['measure_value'] = $station['loc_latitude'];
            $this->update_data_table($updates, $timezone);
            $station['loc_longitude'] = 0 - $weather[161];
            $updates['measure_type'] = 'loc_longitude';
            $updates['measure_value'] = $station['loc_longitude'];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'pressure';
            $pressure_ref = $this->convert_from_mslp_to_baro($weather[6], $station['loc_altitude'], $weather[4]);
            $updates['measure_value'] = $pressure_ref;
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'pressure_sl';
            $updates['measure_value'] = $weather[6];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'battery';
            $updates['measure_value'] = 100;
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'signal';
            $updates['measure_value'] = 9999;
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'firmware';
            if (strpos($weather[count($weather)-1], '!!') !== false) {
                $updates['measure_value'] = str_replace('!!', '', $weather[count($weather)-1]);
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $station['last_refresh'] = date('Y-m-d H:i:s');
            $station['last_seen'] = $timestamp;
            $this->update_table(self::live_weather_station_stations_table(), $station);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule1
        try {
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
            $updates['measure_type'] = 'temperature';
            $updates['measure_value'] = $weather[4];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'temperature_min';
            $updates['measure_value'] = $weather[47];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'temperature_max';
            $updates['measure_value'] = $weather[46];
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'humidity';
            $updates['measure_value'] = $weather[5];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'humidity_min';
            $updates['measure_value'] = $weather[164];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'humidity_max';
            $updates['measure_value'] = $weather[163];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'absolute_humidity';
            $updates['measure_value'] = $this->compute_partial_absolute_humidity($weather[4], 100 * $pressure_ref, $weather[5]);
            $this->update_data_table($updates, $timezone);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule2
        try {
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
            $updates['measure_type'] = 'windangle';
            $updates['measure_value'] = $weather[3];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'winddirection';
            $updates['measure_value'] = (int)floor(($weather[3] + 180) % 360);
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'windstrength';
            $updates['measure_value'] = $this->get_reverse_wind_speed($weather[2], 4);
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'gustangle';
            $updates['measure_value'] = $weather[3];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'gustdirection';
            $updates['measure_value'] = (int)floor(($weather[3] + 180) % 360);
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'guststrength';
            $updates['measure_value'] = $this->get_reverse_wind_speed($weather[133], 4);
            $this->update_data_table($updates, $timezone);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule3
        try {
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
            $updates['measure_type'] = 'rain';
            $updates['measure_value'] = $weather[10];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'rain_day_aggregated';
            $updates['measure_value'] = $weather[7];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'rain_month_aggregated';
            $updates['measure_value'] = $weather[8];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'rain_year_aggregated';
            $updates['measure_value'] = $weather[9];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'rain_yesterday_aggregated';
            $updates['measure_value'] = $weather[19];
            $this->update_data_table($updates, $timezone);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule4
        try {
            $type = 'NAModule4';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 4);
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
            $updates['measure_type'] = 'temperature';
            $updates['measure_value'] = $weather[12];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'temperature_min';
            $updates['measure_value'] = $weather[129];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'temperature_max';
            $updates['measure_value'] = $weather[128];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'humidity';
            $updates['measure_value'] = $weather[13];
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'absolute_humidity';
            $updates['measure_value'] = $this->compute_partial_absolute_humidity($weather[12], 100 * $pressure_ref, $weather[13]);
            $this->update_data_table($updates, $timezone);
            $health = $this->compute_health_index($weather[12], $weather[13], null, null);
            foreach ($health as $key => $idx) {
                $updates['measure_type'] = $key;
                $updates['measure_value'] = $idx;
                $this->update_data_table($updates, $timezone);
            }
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule5
        try {
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
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'uv_index';
            if (is_numeric($weather[79])) {
                $updates['measure_value'] = $weather[79];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'irradiance';
            if (is_numeric($weather[127])) {
                $updates['measure_value'] = $weather[127];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule6
        try {
            $type = 'NAModule6';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 6);
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
            $updates['measure_type'] = 'soil_temperature';
            if (is_numeric($weather[14])) {
                $updates['measure_value'] = $weather[14];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'leaf_wetness';
            if (is_numeric($weather[156])) {
                $updates['measure_value'] = $weather[156];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'moisture_tension';
            if (is_numeric($weather[157])) {
                $updates['measure_value'] = $weather[157] * 10;
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule7
        try {
            $type = 'NAModule7';
            $updates['device_id'] = $station['station_id'];
            $updates['device_name'] = $station['station_name'];
            $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 7);
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
            $updates['measure_type'] = 'strike_count';
            if (is_numeric($weather[33])) {
                $updates['measure_value'] = $weather[33];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'strike_instant';
            if (is_numeric($weather[114])) {
                $updates['measure_value'] = $weather[114];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'strike_distance';
            if (is_numeric($weather[118])) {
                $updates['measure_value'] = $weather[118] * 1000;
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            $updates['measure_timestamp'] = $timestamp;
            $updates['measure_type'] = 'strike_bearing';
            if (is_numeric($weather[119])) {
                $updates['measure_value'] = $weather[119];
            }
            else {
                $updates['measure_value'] = 0;
            }
            $this->update_data_table($updates, $timezone);
            Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        }
        catch (\Exception $e) {
            Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
        }

        // NAModule9 - max 9 extra modules
        $tmp = array(16, 20, 21, 22,  23,  24,  25, 120, 121);
        $hum = array(17, 26, 27, 28, 122, 123, 124, 125, 126);
        $type = 'NAModule9';
        $updates['device_id'] = $station['station_id'];
        $updates['device_name'] = $station['station_name'];
        for ($i = 0; $i < 9; $i++) {
            if (!is_numeric($weather[$tmp[$i]])) {
                $weather[$tmp[$i]] = null;
            }
            if ($weather[$tmp[$i]] < -99) {
                $weather[$tmp[$i]] = null;
            }
            if (!is_numeric($weather[$hum[$i]])) {
                $weather[$hum[$i]] = null;
            }
            if ($weather[$hum[$i]] >= 100) {
                $weather[$hum[$i]] = null;
            }
            if (isset($weather[$tmp[$i]]) || isset($weather[$hum[$i]])) {
                try {
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 9, $i);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type) . ' #' . $i;
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    if (isset($weather[$tmp[$i]])) {
                        $updates['measure_type'] = 'temperature';
                        $updates['measure_value'] = $weather[$tmp[$i]];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (isset($weather[$hum[$i]])) {
                        $updates['measure_type'] = 'humidity';
                        $updates['measure_value'] = $weather[$hum[$i]];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (isset($weather[$tmp[$i]]) && isset($weather[$hum[$i]])) {
                        $updates['measure_type'] = 'absolute_humidity';
                        $updates['measure_value'] = $this->compute_partial_absolute_humidity($weather[$tmp[$i]], 100 * $pressure_ref, $weather[$hum[$i]]);
                        $this->update_data_table($updates, $timezone);
                        $health = $this->compute_health_index($weather[$tmp[$i]], $weather[$hum[$i]], null, null);
                        foreach ($health as $key => $idx) {
                            $updates['measure_type'] = $key;
                            $updates['measure_value'] = $idx;
                            $this->update_data_table($updates, $timezone);
                        }
                    }
                    Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }
                catch (\Exception $e) {
                    Logger::warning($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 5, 'Bad measurement format encountered.');
                }
            }
        }
    }

    /**
     * Explode raw data.
     *
     * @param array $raw_data Raw data to explode.
     * @return array|boolean Exploded data array if all is ok, false otherwise.
     * @since 3.0.0
     */
    public function explode_data($raw_data) {
        $weather = false;
        try {
            $weather = explode(' ', $raw_data);
            Logger::debug($this->facility, $this->service, null, null, null, null, null, print_r($weather, true));
            if (count($weather) < 167) {
                Logger::warning($this->facility, $this->service, null, null, null, null, null, '');
                return false;
            }
            else {
                if ($weather[0] != '12345') {
                    return false;
                }
            }
        }
        catch(\Exception $ex)
        {
            return false;
        }
        return $weather;
    }

    /**
     * Test the resource (is it accessible, is it correctly formatted?).
     *
     * @param integer $connection_type Type of connection.
     * @param string $resource The resource to query.
     * @return string Empty string if all is ok, a string message explaining error otherwise.
     * @since 3.0.0
     */
    public function test($connection_type, $resource) {
        $result = '';
        $raw_data = $this->get_data($connection_type, $resource);
        if (strpos($raw_data, 'Err #') !== false) {
            $result = $raw_data;
        }
        elseif (strpos($raw_data, '2345 ') != 1) {
            $result = __('The source you specified is not in the correct format or is corrupted.', 'live-weather-station');
        }
        else {
            $weather = $this->explode_data($raw_data);
            if (!is_array($weather)) {
                $result = __('Unable to read this file. This may mean that the file is inaccessible, unreadable, contains corrupted data, or is not in the correct format.', 'live-weather-station');
            }
        }
        return $result;
    }

    /**
     * Get station's data.
     *
     * @param integer $connection_type Type of connection.
     * @param string $resource The resource to query.
     * @param string $device_id Optional. The device ID.
     * @param string $device_name Optional. The device name.
     * @return array|string Data array if all is ok, a string message explaining error otherwise.
     * @since 3.0.0
     */
    private function get_data($connection_type, $resource, $device_id = null, $device_name = null) {
        $collector = new FileClient($connection_type);
        try {
            $result = $collector->getRawStationData($resource);
            Logger::notice($this->facility, $this->service, $device_id, $device_name, null, null, 0, 'Data retrieved.');
        }
        catch(\Exception $ex)
        {
            $msg = $ex->getMessage();
            if ($msg == '') {
                $msg = 'Unknown error';
            }
            $result = 'Err #' . $ex->getCode() . ' / ' . $msg;
            Logger::warning($this->facility, $this->service, $device_id, $device_name, null, null, $ex->getCode(), $msg);
        }
        return $result;
    }

    /**
     * Synchronize main table with station table.
     *
     * @since 3.0.0
     */
    private function synchronize_station() {
        $list = array();
        $stations = $this->get_all_clientraw_id_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_clientraw_id($station['guid']);
                $s = $this->get_station_information_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $this->update_stations_table($s);
                $list[] = $device_id;
            }
            $this->clean_clientraw_from_table($list);
        }
    }

    /**
     * Get and store station's data.
     *
     * @since 3.0.0
     */
    public function get_and_store_data(){
        $this->synchronize_station();
        $stations = $this->get_all_clientraw_id_stations();
        foreach ($stations as $station) {
            try {
                $raw_data = $this->get_data($station['connection_type'], $station['service_id'], $station['station_id'], $station['station_name']);
                $this->format_and_store($raw_data, $station);
            }
            catch (\Exception $ex) {
                Logger::error($this->facility, $this->service, $station['station_id'], $station['station_name'], null, null, $ex->getCode(), 'Error while collecting weather from Clientraw file data: ' . $ex->getMessage());
                continue;
            }
        }
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$raw_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather from clientraw file';
            $this->get_and_store_data();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_RAW_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_RAW_SID);
            Logger::info($system, $this->service, null, null, null, null, 0, 'Job done: collecting from clientraw file and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}