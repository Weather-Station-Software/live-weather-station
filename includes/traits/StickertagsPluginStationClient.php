<?php

namespace WeatherStation\SDK\Stickertags\Plugin;

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
 * Stickertags station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
trait StationClient {

    use Dashboard_Manipulation, Id_Manipulation, Conversion, Description, Units;

    protected $facility = 'Weather Collector';
    protected $service = 'File Handler - Stickertags';

    /**
     * Format and store data.
     *
     * @param string $raw_data Weather raw data.
     * @param array $station Station array.
     * @throws \Exception
     * @since 3.3.0
     */
    private function format_and_store($raw_data, $station) {
        $weather = $this->explode_data($raw_data);
        if (!$weather) {
            throw new \Exception('Bad file format.');
        }
        Logger::debug($this->facility, $this->service, null, null, null, null, null, print_r($weather, true));
        $locat_ts = gmmktime($weather[0][0].$weather[0][1], $weather[0][3].$weather[0][4], '00', $weather[1][3].$weather[1][4], $weather[1][0].$weather[1][1], '20'.$weather[1][strlen($weather[1])-2].$weather[1][strlen($weather[1])-1]);
        $timestamp = date('Y-m-d H:i:s', $this->get_date_from_tz($locat_ts, $station['loc_timezone']));
        $units = explode('|', strtolower($weather[17]));
        $wind_unit = 0;
        $temperature_unit = 0;
        $pressure_unit = 0;
        $rain_unit = 0;
        if (count($units) == 4) {
            switch ($units[1]) {
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
            switch ($units[0]) {
                case 'f':
                    $temperature_unit = 1;
                    break;
                case 'k':
                    $temperature_unit = 2;
                    break;
            }
            if ($units[2] == 'in') {
                $pressure_unit = 1;
            }
            if ($units[3] == 'in') {
                $rain_unit = 2;
            }

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
        if (isset($weather[7])) {
            $updates['measure_type'] = 'pressure';
            $updates['measure_value'] = $this->get_reverse_pressure($weather[7], $pressure_unit);
            $this->update_data_table($updates);
        }
        if (isset($weather[8])) {
            $trend = 'stable';
            if (strpos(strtolower($weather[8]), 'ris') !== false) {
                $trend = 'up';
            }
            if (strpos(strtolower($weather[8]), 'fall') !== false) {
                $trend = 'down';
            }
            $updates['measure_type'] = 'pressure_trend';
            $updates['measure_value'] = $trend;
            $this->update_data_table($updates);
        }
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
        if (isset($weather[2])) {
            $updates['measure_type'] = 'temperature';
            $updates['measure_value'] = $this->get_reverse_temperature($weather[2], $temperature_unit);
            $this->update_data_table($updates);
        }
        if (isset($weather[5])) {
            $updates['measure_type'] = 'humidity';
            $updates['measure_value'] = $weather[5];
            $this->update_data_table($updates);
        }
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
        if (isset($weather[10])) {
            $angle = $this->get_reverse_wind_angle_text($weather[10]);
            $updates['measure_type'] = 'windangle';
            $updates['measure_value'] = $angle;
            $this->update_data_table($updates);
            $updates['measure_type'] = 'gustangle';
            $updates['measure_value'] = $angle;
            $this->update_data_table($updates);
        }
        if (isset($weather[9])) {
            $updates['measure_type'] = 'windstrength';
            $updates['measure_value'] = $this->get_reverse_wind_speed($weather[9], $wind_unit);
            $this->update_data_table($updates);
        }
        if (isset($weather[16])) {
            $updates['measure_type'] = 'guststrength';
            $updates['measure_value'] = $this->get_reverse_wind_speed($weather[16], $wind_unit);
            $this->update_data_table($updates);
        }
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
        if (isset($weather[11])) {
            $updates['measure_type'] = 'rain_day_aggregated';
            $updates['measure_value'] = $this->get_reverse_rain($weather[11], $rain_unit);
            $this->update_data_table($updates);
        }
        Logger::debug($this->facility, $this->service, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
    }

    /**
     * Explode raw data.
     *
     * @param string $raw_data Raw data to explode.
     * @return array|boolean Exploded data array if all is ok, false otherwise.
     * @since 3.3.0
     */
    public function explode_data($raw_data) {
        try {
            $weather = explode(',', $raw_data);
            if (count($weather) != 18) {
                return false;
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
     * @since 3.3.0
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
     * @since 3.3.0
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
     * @since 3.3.0
     */
    private function synchronize_station() {
        $list = array();
        $stations = $this->get_all_stickertags_id_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_txt_id($station['guid']);
                $s = $this->get_station_informations_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $this->update_stations_table($s);
                $list[] = $device_id;
            }
            $this->clean_stickertags_from_table($list);
        }
    }

    /**
     * Get and store station's data.
     *
     * @since 3.3.0
     */
    public function get_and_store_data(){
        $this->synchronize_station();
        $stations = $this->get_all_stickertags_id_stations();
        foreach ($stations as $station) {
            $raw_data = $this->get_data($station['connection_type'], $station['service_id'], $station['station_id'], $station['station_name']);
            $this->format_and_store($raw_data, $station);
        }
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.3.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$txt_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather from Stickertags file';
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