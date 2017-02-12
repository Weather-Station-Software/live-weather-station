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


/**
 * Clientraw station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait StationClient {

    use Id_Manipulation, Conversion, Description, Units, Dashboard_Manipulation;

    protected $facility = 'Weather Collector';

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
        $locat_ts = gmmktime($weather[29], $weather[30], $weather[31], $weather[36], $weather[35], $weather[141]);
        $timestamp = date('Y-m-d H:i:s', $this->get_date_from_tz($locat_ts, $station['loc_timezone']));

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
        $station['loc_latitude'] = $weather[160];
        $updates['measure_type'] = 'loc_latitude';
        $updates['measure_value'] = $station['loc_latitude'];
        $this->update_data_table($updates);
        $station['loc_longitude'] = 0 - $weather[161];
        $updates['measure_type'] = 'loc_longitude';
        $updates['measure_value'] = $station['loc_longitude'];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'pressure';
        $updates['measure_value'] = $weather[6];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'pressure_min';
        $updates['measure_value'] = $weather[132];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'pressure_max';
        $updates['measure_value'] = $weather[131];
        $this->update_data_table($updates);
        $trend = 'stable';
        if ($weather[50] > 0) {
            $trend = 'up';
        }
        if ($weather[50] < 0) {
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
        if (strpos($weather[count($weather)-1], '!!') !== false) {
            $updates['measure_value'] = str_replace('!!', '', $weather[count($weather)-1]);
        }
        else {
            $updates['measure_value'] = 0;
        }
        $this->update_data_table($updates);
        $this->update_table(self::live_weather_station_stations_table(), $station);
        Logger::debug($this->facility, null, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');


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
        $updates['measure_value'] = $weather[4];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'temperature_min';
        $updates['measure_value'] = $weather[47];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'temperature_max';
        $updates['measure_value'] = $weather[46];
        $this->update_data_table($updates);
        $updates['measure_timestamp'] = $timestamp;
        $updates['measure_type'] = 'humidity';
        $updates['measure_value'] = $weather[5];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'humidity_min';
        $updates['measure_value'] = $weather[164];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'humidity_max';
        $updates['measure_value'] = $weather[163];
        $this->update_data_table($updates);
        Logger::debug($this->facility, null, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

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
        $updates['measure_value'] = $weather[3];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'windstrength';
        $updates['measure_value'] = $this->get_reverse_wind_speed($weather[2], 4);
        $this->update_data_table($updates);
        $updates['measure_type'] = 'gustangle';
        $updates['measure_value'] = $weather[3];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'guststrength';
        $updates['measure_value'] = $this->get_reverse_wind_speed($weather[133], 4);
        $this->update_data_table($updates);
        Logger::debug($this->facility, null, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

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
        $updates['measure_type'] = 'rain';
        $updates['measure_value'] = $weather[10];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_day_aggregated';
        $updates['measure_value'] = $weather[165];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_month_aggregated';
        $updates['measure_value'] = $weather[8];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_season_aggregated';
        $updates['measure_value'] = $weather[9];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'rain_yesterday_aggregated';
        $updates['measure_value'] = $weather[19];
        $this->update_data_table($updates);
        Logger::debug($this->facility, null, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

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
        $updates['measure_value'] = $weather[12];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'temperature_min';
        $updates['measure_value'] = $weather[129];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'temperature_max';
        $updates['measure_value'] = $weather[128];
        $this->update_data_table($updates);
        $updates['measure_type'] = 'humidity';
        $updates['measure_value'] = $weather[13];
        $this->update_data_table($updates);
        $health = $this->compute_health_index($weather[12], $weather[13], null, null);
        foreach ($health as $key => $idx) {
            $updates['measure_type'] = $key;
            $updates['measure_value'] = $idx;
            $this->update_data_table($updates);
        }
        Logger::debug($this->facility, null, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
        $tmp = array(16, 20, 21, 22,  23,  24,  25, 120, 121);
        $hum = array(17, 26, 27, 28, 122, 123, 124, 125, 126);
        $max = $weather[18];
        if ($max > 9) {
            $max = 9;
        }
        // NAModule9 - max 9 extra modules
        $type = 'NAModule9';
        $updates['device_id'] = $station['station_id'];
        $updates['device_name'] = $station['station_name'];
        if ($max > 0) {
            for ($i = 0; $i < $max; $i++) {
                $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 9, $i);
                $updates['module_type'] = $type;
                $updates['module_name'] = $this->get_fake_module_name($type) . ' #' . $i;
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates);
                $updates['measure_type'] = 'temperature';
                $updates['measure_value'] = $weather[$tmp[$i]];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'humidity';
                $updates['measure_value'] = $weather[$hum[$i]];
                $this->update_data_table($updates);
                $health = $this->compute_health_index($weather[$tmp[$i]], $weather[$hum[$i]], null, null);
                foreach ($health as $key => $idx) {
                    $updates['measure_type'] = $key;
                    $updates['measure_value'] = $idx;
                    $this->update_data_table($updates);
                }
                Logger::debug($this->facility, null, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
            }
        }

        /*

        14	Soil: Temperature	Celsius (Optional)	0.0
        156	Leaf: Wetness (0-15)	Number (Optional)	0.0
        157	Soil: Moisture (0-200 centibars)	Number (Optional)	255.0

        33	Lightning: Strikes - in Total	Number	0
        114	Lightning: Strikes - in last Min	Number	0
        118	Lightning: Last Strike - Distance (Nexstorm)	Number	0
        119	Lightning: Last Strike - Bearing (Nexstorm)	Compass	0

        34	Solar: Current reading (0% - 100%)	Number	24
        79	UV: Current reading (0-16 index)	Number (Optional)	0.0			/UV index
        127	Solar: VP Solar W/sqM (0 - 1800 W/sqM)	Number	56.0   				/Irradiance

         */

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
            Logger::debug($this->facility, null, null, null, null, null, null, print_r($weather, true));
            if (count($weather) < 167) {
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
        if (strpos($raw_data, '2345 ') != 1) {
            $result = __('The source you specified is not accessible.', 'live-weather-station');
        }
        else {
            $weather = $this->explode_data($raw_data);
            if (!is_array($weather)) {
                $result = __('Bad file format.', 'live-weather-station');
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
        }
        catch(\Exception $ex)
        {
            $result = $ex->getMessage();
            Logger::warning($this->facility, null, $device_id, $device_name, null, null, $ex->getCode(), $ex->getMessage());
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
                $s = $this->get_station_informations_by_guid($station['guid']);
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
            $raw_data = $this->get_data($station['connection_type'], $station['service_id']);
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
            Logger::info($system, null, null, null, null, null, 0, 'Job done: collecting from clientraw file and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, null, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
    }
}