<?php

namespace WeatherStation\SDK\Realtime\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Generic\FileClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\ID\Handling as Id_Manipulation;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\Data\DateTime\Conversion;
use WeatherStation\Data\Type\Description;
use WeatherStation\Data\Unit\Conversion as Units;
use WeatherStation\System\Schedules\Watchdog;


/**
 * Realtime station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait StationClient {

    use Dashboard_Manipulation, Id_Manipulation, Conversion, Description, Units;

    protected $facility = 'Weather Collector';
    protected $service = 'File Handler - Realtime';

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
        if (!$weather) {
            throw new \Exception('Bad file format.');
        }
        Logger::debug($this->facility, $this->service, null, null, null, null, null, print_r($weather, true));
        $locat_ts = gmmktime($weather[1][0].$weather[1][1], $weather[1][3].$weather[1][4], $weather[1][6].$weather[1][7], $weather[0][3].$weather[0][4], $weather[0][0].$weather[0][1], '20'.$weather[0][strlen($weather[0])-2].$weather[0][strlen($weather[0])-1]);
        $timestamp = date('Y-m-d H:i:s', $this->get_date_from_tz($locat_ts, $station['loc_timezone']));
        $wind_unit = 0;
        switch (strtolower($weather[13])) {
            case 'm/s':
                $wind_unit = 2;
                break;
            case 'mph':
                $wind_unit = 1;
                break;
            case 'kts':
                $wind_unit = 4;
                break;
        }
        $temperature_unit = 0;
        switch (strtolower($weather[14])) {
            case 'f':
                $temperature_unit = 1;
                break;
            case 'k':
                $temperature_unit = 2;
                break;
        }
        if (strtolower($weather[15]) == 'in') {
            $pressure_unit = 1;
        }
        else {
            $pressure_unit = 0;
        }
        if (strtolower($weather[16]) == 'in') {
            $rain_unit = 2;
        }
        else {
            $rain_unit = 0;
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
        $updates['measure_type'] = 'loc_city';
        $updates['measure_value'] = $station['loc_city'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'loc_country';
        $updates['measure_value'] = $station['loc_country_code'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'loc_timezone';
        $updates['measure_value'] = $station['loc_timezone'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'loc_altitude';
        $updates['measure_value'] = $station['loc_altitude'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'loc_latitude';
        $updates['measure_value'] = $station['loc_latitude'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'loc_longitude';
        $updates['measure_value'] = $station['loc_longitude'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'pressure';
        $updates['measure_value'] = $this->get_reverse_pressure($weather[10], $pressure_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'pressure_min';
        $updates['measure_value'] = $this->get_reverse_pressure($weather[36], $pressure_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'pressure_max';
        $updates['measure_value'] = $this->get_reverse_pressure($weather[34], $pressure_unit);
        $this->update_data_table($updates);
        $trend = 'stable';
        if ($weather[18] > 0) {
            $trend = 'up';
        }
        if ($weather[18] < 0) {
            $trend = 'down';
        }
        $updates['measure_type'] = 'pressure_trend';
        $updates['measure_value'] = $trend;
        $this->update_data_table($updates);
        $updates['measure_type'] = 'battery';
        $updates['measure_value'] = 100;
        $this->update_data_table($updates);
        $updates['measure_type'] = 'signal';
        $updates['measure_value'] = 9999;
        $this->update_data_table($updates);
        $updates['measure_type'] = 'firmware';
        $updates['measure_value'] = $weather[38] . ' / ' . $weather[39];
        $this->update_data_table($updates);
        $station['last_refresh'] = date('Y-m-d H:i:s');
        $station['last_seen'] = $timestamp;
        $this->update_table(self::live_weather_station_stations_table(), $station);
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');


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
        $updates['measure_type'] = 'temperature';
        $updates['measure_value'] = $this->get_reverse_temperature($weather[2], $temperature_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'temperature_min';
        $updates['measure_value'] = $this->get_reverse_temperature($weather[28], $temperature_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'temperature_max';
        $updates['measure_value'] = $this->get_reverse_temperature($weather[26], $temperature_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'humidity';
        $updates['measure_value'] = $weather[3];
        $this->update_data_table($updates);
        $trend = 'stable';
        if ($weather[25] > 0) {
            $trend = 'up';
        }
        if ($weather[25] < 0) {
            $trend = 'down';
        }
        $updates['measure_type'] = 'temperature_trend';
        $updates['measure_value'] = $trend;
        $this->update_data_table($updates);
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');


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
        $updates['measure_type'] = 'windangle';
        $updates['measure_value'] = $weather[7];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'windstrength';
        $updates['measure_value'] = $this->get_reverse_wind_speed($weather[5], $wind_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'gustangle';
        $updates['measure_value'] = $weather[7];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'guststrength';
        $updates['measure_value'] = $this->get_reverse_wind_speed($weather[40], $wind_unit);
        $this->update_data_table($updates);
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

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
        $updates['measure_type'] = 'rain';
        $updates['measure_value'] = $this->get_reverse_rain($weather[8], $rain_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_hour_aggregated';
        $updates['measure_value'] = $this->get_reverse_rain($weather[47], $rain_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_day_aggregated';
        $updates['measure_value'] = $this->get_reverse_rain($weather[9], $rain_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_month_aggregated';
        $updates['measure_value'] = $this->get_reverse_rain($weather[19], $rain_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_year_aggregated';
        $updates['measure_value'] = $this->get_reverse_rain($weather[20], $rain_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_yesterday_aggregated';
        $updates['measure_value'] = $this->get_reverse_rain($weather[21], $rain_unit);
        $this->update_data_table($updates);
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

        // NAModule4
        $type = 'NAModule4';
        $updates['device_id'] = $station['station_id'];
        $updates['device_name'] = $station['station_name'];
        $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 4);
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
        $updates['measure_type'] = 'temperature';
        $updates['measure_value'] = $this->get_reverse_temperature($weather[22], $temperature_unit);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'humidity';
        $updates['measure_value'] = $weather[23];
        $this->update_data_table($updates);
        $health = $this->compute_health_index($this->get_reverse_temperature($weather[22], $temperature_unit), $weather[23], null, null);
        foreach ($health as $key => $idx) {
            $updates['measure_type'] = $key;
            $updates['measure_value'] = $idx;
            $this->update_data_table($updates);
        }
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');


        // NAModule5
        $type = 'NAModule5';
        $updates['device_id'] = $station['station_id'];
        $updates['device_name'] = $station['station_name'];
        $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 5);
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
        $updates['measure_type'] = 'uv_index';
        if (is_numeric($weather[43])) {
            $updates['measure_value'] = $weather[43];
        }
        else {
            $updates['measure_value'] = 0;
        }
        $this->update_data_table($updates);
        $updates['measure_timestamp'] = $timestamp;
        $updates['measure_type'] = 'irradiance';
        if (is_numeric($weather[45])) {
            $updates['measure_value'] = $weather[45];
        }
        else {
            $updates['measure_value'] = 0;
        }
        $this->update_data_table($updates);
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

        // NAModule6
        $type = 'NAModule6';
        $updates['device_id'] = $station['station_id'];
        $updates['device_name'] = $station['station_name'];
        $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 6);
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
        $updates['measure_type'] = 'evapotranspiration';
        if (is_numeric($weather[44])) {
            $updates['measure_value'] = $weather[44];
        }
        else {
            $updates['measure_value'] = 0;
        }
        $this->update_data_table($updates);
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
    }

    /**
     * Explode raw data.
     *
     * @param string $raw_data Raw data to explode.
     * @return array|boolean Exploded data array if all is ok, false otherwise.
     * @since 3.0.0
     */
    public function explode_data($raw_data) {
        $weather = false;
        $separators = array(' ', '|');
        try {
            $weather = explode(' ', $raw_data);
            if (count($weather) < 58) {
                return false;
            }
            else {
                if (($weather[57] != 0) && ($weather[57] != 1)) {
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
     * Test the resource (is it accessible, is it correctly formated?).
     *
     * @param integer $connection_type Type of connection.
     * @param string $resource The resource to query.
     * @return string Empty string if all is ok, a string message explaining error otherwise.
     * @since 3.0.0
     */
    public function test($connection_type, $resource) {
        $result = '';
        $raw_data = $this->get_data($connection_type, $resource);
        $weather = $this->explode_data($raw_data);
        if (!is_array($weather)) {
            $result = __('Bad file format.', 'live-weather-station');
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
            $result = $ex->getMessage();
            Logger::warning($this->facility, $this->service, $device_id, $device_name, null, null, $ex->getCode(), $ex->getMessage());
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
        $stations = $this->get_all_realtime_id_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_realtime_id($station['guid']);
                $s = $this->get_station_informations_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $this->update_stations_table($s);
                $list[] = $device_id;
            }
            $this->clean_realtime_from_table($list);
        }
    }

    /**
     * Get and store station's data.
     *
     * @since 3.0.0
     */
    public function get_and_store_data(){
        $this->synchronize_station();
        $stations = $this->get_all_realtime_id_stations();
        foreach ($stations as $station) {
            $raw_data = $this->get_data($station['connection_type'], $station['service_id'], $station['station_id'], $station['station_name']);
            $this->format_and_store($raw_data, $station);
        }
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$real_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather from Realtime file';
            $this->get_and_store_data();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_REAL_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_REAL_SID);
            Logger::info($system, $this->service, null, null, null, null, 0, 'Job done: collecting from Realtime file and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}