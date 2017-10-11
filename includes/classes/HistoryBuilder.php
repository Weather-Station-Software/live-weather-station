<?php

namespace WeatherStation\Data\History;

use WeatherStation\DB\Query;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\UI\ListTable\Log;

/**
 * This class is responsible of history building.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.2
 */

class Builder
{

    use Query;
    
    public $standard_measurements = 
        array('health_idx', 'co2', 'humidity', 'cloudiness', 'noise', 'pressure', 'temperature', 'irradiance', 
              'uv_index', 'illuminance', 'cloud_ceiling', 'heat_index', 'humidex', 'wind_chill', 'windangle', 
              'windstrength', 'rain_day_aggregated', 'rain');
    public $extended_measurements = 
        array('cbi', 'wet_bulb', 'air_density', 'wood_emc', 'equivalent_temperature', 'potential_temperature',
              'equivalent_potential_temperature', 'specific_enthalpy', 'partial_vapor_pressure',
              'saturation_vapor_pressure', 'vapor_pressure', 'absolute_humidity', 'partial_absolute_humidity',
              'saturation_absolute_humidity', 'soil_temperature', 'leaf_wetness', 'moisture_content',
              'moisture_tension', 'evapotranspiration', 'gustangle', 'guststrength');

    private $Live_Weather_Station;
    private $version;
    private $facility = 'History Builder';


    /**
     * Initialize the class and set its properties.
     *
     * @since 3.3.2
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Cron to execute 6 times a day to execute the full build.
     *
     * @since 3.3.2
     */
    public function cron() {
        $cron_id = Watchdog::init_chrono(Watchdog::$history_build_name);
        $this->__full_build();
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Main process of the builder.
     *
     * @since 3.3.2
     */
    private function __full_build() {
        if ((bool)get_option('live_weather_station_build_history')) {
            $stations = $this->get_stations_list();
            foreach ($stations as $station) {
                $today = new \DateTime('today', new \DateTimeZone($station['loc_timezone']));
                $yesterday = new \DateTime('yesterday', new \DateTimeZone($station['loc_timezone']));
                $device_id = $station['station_id'];
                if ($this->count_daily_values($device_id, $today) > 0) {
                    $measures = $this->get_available_measurements($device_id, $yesterday);
                    if (count($measures) > 0) {
                        foreach ($measures as $measure) {
                            $operations = $this->get_measurements_operations_type($measure['measure_type'], $measure['module_type'], (bool)get_option('live_weather_station_full_history'));
                            if ($this->perform_standard_aggregation($device_id, $measure['module_id'], $measure['module_type'], $measure['measure_type'], $yesterday, $operations)) {
                                $this->delete_daily_values($device_id, $measure['module_id'], $measure['measure_type'], $yesterday);
                            }
                        }
                        $this->delete_remaining_daily_values($device_id, $yesterday);
                        Logger::notice($this->facility, null, $station['station_id'], $station['station_name'], null, null, null, 'Historical data consolidation done.');
                    }
                }
            }
        }
    }

    /**
     * Get the type of operations to do for a measure_type.
     *
     * @param string $measure_type The type of measurements.
     * @param string $module_type The type of the module.
     * @param bool $full_mode True if it's in full mode.
     * @return array An array of SQL operators and names.
     * @since 3.3.2
     */
    private function get_measurements_operations_type($measure_type, $module_type, $full_mode=false) {
        $result = array();
        if (in_array($measure_type, $this->standard_measurements)) {
            if ($full_mode) {
                $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'STD' => 'dev');
            }
            else {
                $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg');
            }
            if ($measure_type == 'rain_day_aggregated') {
                $result = array('MAX'=>'agg');
            }
            if ($measure_type == 'rain' && $module_type != 'NACurrent' && (bool)get_option('live_weather_station_full_history')) {
                $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'STD' => 'dev');
            }
            if ($measure_type == 'weather') {
                $result = array('FQC_MAX'=>'dom');
            }
        }
        if (in_array($measure_type, $this->extended_measurements)) {
            if ($full_mode) {
                $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'STD' => 'dev');
            }
        }
        return $result;


    
        
        /*
        
         '', ''

        'strike_count', 'strike_instant'   'weather-id'*/
        
    }

    /**
     * Count number of records for a specific (TZ local) date.
     *
     * @param string $device_id The station to count.
     * @param \DateTime $day The day to count.
     * @return int The number of rows.
     * @since 3.3.2
     */
    private function count_daily_values($device_id, $day) {
        $date = $day->format('Y-m-d');
        $min = $date . ' 00:00:00';
        $max = $date . ' 23:59:59';
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT COUNT(*) FROM ".$table_name." WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "';";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = (array)$query_a[0];
            $result = $query_t['COUNT(*)'];
            return $result;
        }
        catch(\Exception $ex) {
            return 0;
        }
    }

    /**
     * Count number of records for a specific (TZ local) date.
     *
     * @param string $device_id The station to count.
     * @param \DateTime $day The day to count.
     * @return array The available measurement types per module.
     * @since 3.3.2
     */
    private function get_available_measurements($device_id, $day) {
        $date = $day->format('Y-m-d');
        $min = $date . ' 00:00:00';
        $max = $date . ' 23:59:59';
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT DISTINCT `module_id`, `module_type`, `measure_type` FROM ".$table_name." WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "';";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(\Exception $ex) {
            return array() ;
        }
    }

    /**
     * Performs a standard aggregation operation.
     *
     * @param string $device_id The station to aggregate.
     * @param string $module_id The module to aggregate.
     * @param string $module_type The type of module to aggregate.
     * @param string $measure_type The measure to aggregate.
     * @param \DateTime $day The day of aggregation.
     * @param array $operations Operations to perform.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.3.2
     */
    private function perform_standard_aggregation($device_id, $module_id, $module_type, $measure_type, $day, $operations) {
        if (count($operations) == 0) {
            return false;
        }
        $sub_result = false;
        $date = $day->format('Y-m-d');
        $min = $date . ' 00:00:00';
        $max = $date . ' 23:59:59';
        $selects = array();
        foreach ($operations as $operation=>$name) {
            if (($operation == 'FQC_MIN') || ($operation == 'FQC_MAX')) {
                $sub_result = $this->perform_frequency_aggregation($device_id, $module_id, $module_type, $measure_type, $day, $operation, $name);
            }
            else {
                $selects[] = $operation . '(`measure_value`) as v_' . $name;
            }
        }
        if (count($selects) == 0) {
            return $sub_result;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT " . implode(', ', $selects). " FROM ".$table_name." WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "' AND `module_id`='" . $module_id . "' AND `measure_type`='" . $measure_type . "';";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $values = (array)$query_a[0];
        }
        catch(\Exception $ex) {
            return false;
        }
        foreach ($values as $set=>$value) {
            $val = array();
            $val['timestamp'] = $date;
            $val['device_id'] = $device_id;
            $val['module_id'] = $module_id;
            $val['module_type'] = $module_type;
            $val['measure_type'] = $measure_type;
            $val['measure_set'] = str_replace('v_', '', $set);
            $val['measure_value'] = $value;
            $this->update_table(self::live_weather_station_histo_yearly_table(), $val);
        }
        return true;
    }

    /**
     * Performs a frequency aggregation operation.
     *
     * @param string $device_id The station to aggregate.
     * @param string $module_id The module to aggregate.
     * @param string $module_type The type of module to aggregate.
     * @param string $measure_type The measure to aggregate.
     * @param \DateTime $day The day of aggregation.
     * @param string $operation Operation to perform.
     * @param string $name Name of the field to generate.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.3.2
     */
    private function perform_frequency_aggregation($device_id, $module_id, $module_type, $measure_type, $day, $operation, $name) {
        $date = $day->format('Y-m-d');
        $min = $date . ' 00:00:00';
        $max = $date . ' 23:59:59';
        $select = '`measure_value` as v_val, COUNT(*) as v_fqc';
        $order = 'DESC';
        if ($operation == 'FQC_MIN') {
            $order = 'ASC';
        }
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT " . $select . " FROM ".$table_name." WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "' AND `module_id`='" . $module_id . "' AND `measure_type`='" . $measure_type . "' GROUP BY `measure_value` ORDER BY v_fqc " . $order ." LIMIT 1;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $values = (array)$query_a[0];
        }
        catch(\Exception $ex) {
            return false;
        }
        $val = array();
        $val['timestamp'] = $date;
        $val['device_id'] = $device_id;
        $val['module_id'] = $module_id;
        $val['module_type'] = $module_type;
        $val['measure_type'] = $measure_type;
        $val['measure_set'] = $name;
        $val['measure_value'] = $values['measure_value'];
        $this->update_table(self::live_weather_station_histo_yearly_table(), $val);
        return true;
    }

    /**
     * Delete some daily values.
     *
     * @param string $device_id The station.
     * @param string $module_id The module.
     * @param string $measure_type The measure.
     * @param \DateTime $day The day.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.3.2
     */
    private function delete_daily_values($device_id, $module_id, $measure_type, $day) {
        $max = $day->format('Y-m-d') . ' 23:59:59';
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "DELETE FROM ".$table_name." WHERE `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "' AND `module_id`='" . $module_id . "' AND `measure_type`='" . $measure_type . "';";
        return $wpdb->query($sql);
    }

    /**
     * Delete remaining daily values.
     *
     * @param string $device_id The station.
     * @param \DateTime $day The day.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.3.2
     */
    private function delete_remaining_daily_values($device_id, $day) {
        $max = $day->format('Y-m-d') . ' 23:59:59';
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "DELETE FROM ".$table_name." WHERE `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "';";
        return $wpdb->query($sql);
    }


}