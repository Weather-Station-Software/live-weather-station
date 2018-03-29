<?php

namespace WeatherStation\Data\History;

use WeatherStation\DB\Query;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\UI\ListTable\Log;
use WeatherStation\Data\DateTime\Conversion;

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
    use Conversion;

    public static $data_to_historize =
        array('health_idx', 'cbi', 'co2', 'humidity', 'cloudiness', 'noise', 'pressure', 'temperature',
            'heat_index', 'humidex', 'wind_chill', 'cloud_ceiling', 'wet_bulb', 'air_density', 'wood_emc',
            'equivalent_temperature', 'potential_temperature', 'equivalent_potential_temperature', 'specific_enthalpy',
            'partial_vapor_pressure', 'saturation_vapor_pressure', 'vapor_pressure', 'absolute_humidity',
            'partial_absolute_humidity', 'saturation_absolute_humidity', 'irradiance', 'uv_index', 'illuminance',
            'soil_temperature', 'leaf_wetness', 'moisture_content', 'moisture_tension', 'evapotranspiration',
            'windangle', 'gustangle', 'windstrength', 'guststrength', 'rain', 'rain_hour_aggregated', 'visibility',
            'rain_day_aggregated', 'strike_count', 'strike_instant', 'weather', 'dew_point', 'frost_point');
    
    public $standard_measurements = 
        array('health_idx', 'co2', 'humidity', 'cloudiness', 'noise', 'pressure', 'temperature', 'irradiance', 
              'uv_index', 'illuminance', 'cloud_ceiling', 'heat_index', 'humidex', 'wind_chill', 'windangle', 
              'windstrength', 'rain_day_aggregated', 'rain', 'weather', 'dew_point', 'frost_point', 'visibility');
    public $extended_measurements = 
        array('cbi', 'wet_bulb', 'air_density', 'wood_emc', 'equivalent_temperature', 'potential_temperature',
              'equivalent_potential_temperature', 'specific_enthalpy', 'partial_vapor_pressure',
              'saturation_vapor_pressure', 'vapor_pressure', 'absolute_humidity', 'partial_absolute_humidity',
              'saturation_absolute_humidity', 'soil_temperature', 'leaf_wetness', 'moisture_content',
              'moisture_tension', 'evapotranspiration', 'gustangle', 'guststrength', 'strike_instant', 'strike_count');

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
     * Indicates, regarding the current settings, if this measurement is part of daily/historical measurements.
     *
     * @param string $measurement The measurement to test.
     * @return boolean True if it's ok, false otherwise.
     * @since 3.4.0
     */
    public function is_allowed_measurement($measurement) {
        $result = false;
        if ((bool)get_option('live_weather_station_collect_history')) {
            if ((bool)get_option('live_weather_station_full_history')) {
                $measure_types = array_merge($this->extended_measurements, $this->standard_measurements);
            }
            else {
                $measure_types = $this->standard_measurements;
            }
            $result = in_array($measurement, $measure_types);
        }
        return $result;
    }

    /**
     * Main process of the builder.
     *
     * @since 3.3.2
     */
    private function __full_build() {
        $stations = $this->get_stations_list();
        foreach ($stations as $station) {
            $this->build_for($station);
        }
    }

    /**
     * Build history for a specific station.
     *
     * @param array $station The station to build for.
     * @since 3.4.0
     */
    public function build_for($station) {
        $device_id = $station['station_id'];
        if ((bool)get_option('live_weather_station_build_history')) {
            if ($this->count_daily_values($device_id, $station['loc_timezone']) > 0) {
                $measures = $this->get_available_measurements($device_id, $station['loc_timezone']);
                if (count($measures) > 0) {
                    foreach ($measures as $measure) {
                        $operations = $this->get_measurements_operations_type($measure['measure_type'], $measure['module_type'], (bool)get_option('live_weather_station_full_history'));
                        if ($this->perform_standard_aggregation($device_id, $measure['module_id'], $measure['module_type'], $measure['measure_type'], $station['loc_timezone'], $operations)) {
                            $this->delete_daily_values($device_id, $measure['module_id'], $measure['measure_type'], $station['loc_timezone']);
                        }
                    }
                    Logger::notice($this->facility, null, $station['station_id'], $station['station_name'], null, null, null, 'Daily data compiled.');
                    $this->delete_remaining_daily_values($device_id, $station['loc_timezone']);
                    Logger::notice($this->facility, null, $station['station_id'], $station['station_name'], null, null, null, 'Old daily data cleaned.');
                }
            }
        }
        else {
            $this->delete_remaining_daily_values($device_id, $station['loc_timezone']);
            Logger::notice($this->facility, null, $station['station_id'], $station['station_name'], null, null, null, 'Old daily data cleaned.');
        }
    }

    /**
     * Get the type of operations to do for a measure_type.
     *
     * @param string $measure_type The type of measurements.
     * @param string $module_type The type of the module.
     * @param bool $full_mode Optional. True if it's in full mode.
     * @param boolean $comparison Optional. The array must contain only the comparison set.
     * @param boolean $distribution Optional. The line must contain only the distribution set.
     * @return array An array of SQL operators and names.
     * @since 3.3.2
     */
    public function get_measurements_operations_type($measure_type, $module_type='', $full_mode=false, $comparison=false, $distribution=false) {
        $result = array();
        if (in_array($measure_type, $this->standard_measurements)) {
            if ($comparison) {
                if ($full_mode) {
                    $result = array('AVG|MED' => 'avg|med', 'AVG|MID' => 'avg|mid', 'MED|AVG' => 'med|avg', 'MED|MID' => 'med|mid', 'MID|AVG' => 'mid|avg', 'MID|MED' => 'mid|med');
                }
                else {
                    $result = array('AVG|MID' => 'avg|mid', 'MID|AVG' => 'mid|avg');
                }
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array();
                }
                if ($measure_type == 'rain') {
                    $result = array();
                }
                if ($measure_type == 'rain' && $module_type != 'NACurrent' && $full_mode) {
                    $result = array('AVG|MED' => 'avg|med', 'AVG|MID' => 'avg|mid', 'MED|AVG' => 'med|avg', 'MED|MID' => 'med|mid', 'MID|AVG' => 'mid|avg', 'MID|MED' => 'mid|med');
                }
                if ($measure_type == 'weather') {
                    $result = array();
                }
            }
            elseif ($distribution) {
                if ($full_mode) {
                    $result = array('AVG' => 'avg', 'MID' => 'mid', 'MED' => 'med');
                }
                else {
                    $result = array('AVG' => 'avg', 'MID' => 'mid');
                }
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array();
                }
                if ($measure_type == 'rain') {
                    $result = array();
                }
                if ($measure_type == 'rain' && $module_type != 'NACurrent' && $full_mode) {
                    $result = array('AVG' => 'avg', 'MID' => 'mid', 'MED' => 'med');
                }
                if ($measure_type == 'weather') {
                    $result = array();
                }
            }
            else {
                if ($full_mode) {
                    $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'STD' => 'dev', 'MID' => 'mid', 'MED' => 'med', 'AMP' => 'amp');
                }
                else {
                    $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'MID' => 'mid');
                }
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array('MAX'=>'agg');
                }
                if ($measure_type == 'rain') {
                    $result = array();
                }
                if ($measure_type == 'rain' && $module_type != 'NACurrent' && $full_mode) {
                    $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'STD' => 'dev', 'MID' => 'mid', 'MED' => 'med', 'AMP' => 'amp');
                }
                if ($measure_type == 'weather') {
                    $result = array('FQC_MAX'=>'dom');
                }
            }
        }
        if (in_array($measure_type, $this->extended_measurements) && $full_mode) {
            if ($comparison) {
                $result = array('AVG|MED' => 'avg|med', 'AVG|MID' => 'avg|mid', 'MED|AVG' => 'med|avg', 'MED|MID' => 'med|mid', 'MID|AVG' => 'mid|avg', 'MID|MED' => 'mid|med');
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array();
                }
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array();
                }
                if ($measure_type == 'rain') {
                    $result = array();
                }
                if ($measure_type == 'rain' && $module_type != 'NACurrent' && $full_mode) {
                    $result = array('AVG|MED' => 'avg|med', 'AVG|MID' => 'avg|mid', 'MED|AVG' => 'med|avg', 'MED|MID' => 'med|mid', 'MID|AVG' => 'mid|avg', 'MID|MED' => 'mid|med');
                }
                if ($measure_type == 'weather') {
                    $result = array();
                }
            }
            elseif ($distribution) {
                $result = array('AVG' => 'avg', 'MID' => 'mid', 'MED' => 'med');
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array();
                }
                if ($measure_type == 'rain_day_aggregated') {
                    $result = array();
                }
                if ($measure_type == 'rain') {
                    $result = array();
                }
                if ($measure_type == 'rain' && $module_type != 'NACurrent' && $full_mode) {
                    $result = array('AVG' => 'avg', 'MID' => 'mid', 'MED' => 'med');
                }
                if ($measure_type == 'weather') {
                    $result = array();
                }
            }
            else {
                $result = array('MAX' => 'max', 'MIN' => 'min', 'AVG' => 'avg', 'STD' => 'dev', 'MID' => 'mid', 'MED' => 'med', 'AMP' => 'amp');
                if ($measure_type == 'strike_count') {
                    $result = array('HR_MAX'=>'maxhr');
                }
            }

        }
        return $result;
    }

    /**
     * Count number of records for a specific (TZ local) date.
     *
     * @param string $device_id The station to count.
     * @param string $tz The timezone.
     * @return int The number of rows.
     * @since 3.3.2
     */
    private function count_daily_values($device_id, $tz) {
        $min = date('Y-m-d H:i:s', self::get_local_today_midnight($tz));
        $max = date('Y-m-d H:i:s', self::get_local_today_noon($tz));
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
     * @param string $tz The timezone.
     * @return array The available measurement types per module.
     * @since 3.3.2
     */
    private function get_available_measurements($device_id, $tz) {
        $min = date('Y-m-d H:i:s', self::get_local_yesterday_midnight($tz));
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
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
     * @param string $tz The timezone.
     * @param array $operations Operations to perform.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function perform_standard_aggregation($device_id, $module_id, $module_type, $measure_type, $tz, $operations) {
        if (count($operations) == 0) {
            return false;
        }
        $sub_result = false;
        $date = self::get_local_date($tz);
        $min = date('Y-m-d H:i:s', self::get_local_yesterday_midnight($tz));
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
        $selects = array();
        foreach ($operations as $operation=>$name) {
            if (($operation == 'FQC_MIN') || ($operation == 'FQC_MAX')) {
                $sub_result = $this->perform_frequency_aggregation($device_id, $module_id, $module_type, $measure_type, $tz, $operation, $name);
            }
            elseif (($operation == 'MED')) {
                $sub_result = $this->perform_median_computation($device_id, $module_id, $module_type, $measure_type, $tz, $name);
            }
            elseif (($operation == 'HR_MAX')) {
                $sub_result = $this->perform_max_per_hour($device_id, $module_id, $module_type, $measure_type, $tz, $operation, 3, $name);
            }
            elseif (($operation == 'AMP') || ($operation == 'MID')) {
                // DO NOTHING FOR NOW, IT WILL BE COMPUTED WHEN SELECTING DATA
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
     * @param string $tz The timezone.
     * @param string $operation Operation to perform.
     * @param string $name Name of the field to generate.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function perform_frequency_aggregation($device_id, $module_id, $module_type, $measure_type, $tz, $operation, $name) {
        $date = date('Y-m-d', self::get_local_yesterday_midnight($tz));
        $min = date('Y-m-d H:i:s', self::get_local_yesterday_midnight($tz));
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
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
        $val['measure_value'] = $values['v_val'];
        $this->update_table(self::live_weather_station_histo_yearly_table(), $val);
        return true;
    }

    /**
     * Performs a frequency aggregation operation.
     *
     * @param string $device_id The station to aggregate.
     * @param string $module_id The module to aggregate.
     * @param string $module_type The type of module to aggregate.
     * @param string $measure_type The measure to aggregate.
     * @param string $tz The timezone.
     * @param string $operation Operation to perform.
     * @param integer $factor The factor to divide by.
     * @param string $name Name of the field to generate.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function perform_max_per_hour($device_id, $module_id, $module_type, $measure_type, $tz, $operation, $factor, $name) {
        $date = date('Y-m-d', self::get_local_yesterday_midnight($tz));
        $min = date('Y-m-d H:i:s', self::get_local_yesterday_midnight($tz));
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT MAX(`measure_value`) as v_max FROM ".$table_name." WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "' AND `module_id`='" . $module_id . "' AND `measure_type`='" . $measure_type . "';";
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
        $val['measure_value'] = $values['v_max'] / $factor;
        $this->update_table(self::live_weather_station_histo_yearly_table(), $val);
        return true;
    }

    /**
     * Performs a median computation.
     *
     * @param string $device_id The station to aggregate.
     * @param string $module_id The module to aggregate.
     * @param string $module_type The type of module to aggregate.
     * @param string $measure_type The measure to aggregate.
     * @param string $tz The timezone.
     * @param string $name Name of the field to generate.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function perform_median_computation($device_id, $module_id, $module_type, $measure_type, $tz, $name) {
        $date = date('Y-m-d', self::get_local_yesterday_midnight($tz));
        $min = date('Y-m-d H:i:s', self::get_local_yesterday_midnight($tz));
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT `measure_value` as v_val FROM ".$table_name." WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "' AND `module_id`='" . $module_id . "' AND `measure_type`='" . $measure_type . "' ORDER BY v_val ASC;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
        }
        catch(\Exception $ex) {
            return false;
        }
        try {
            $count = count($result);
            if ($count == 0) {
                return false;
            }
            if ($count & 1) {
                $med = $result[intval($count / 2)]['v_val'];
            }
            else {
                $med = ($result[intval($count / 2)]['v_val'] + $result[intval($count / 2) + 1]['v_val']) / 2;
            }
            $val = array();
            $val['timestamp'] = $date;
            $val['device_id'] = $device_id;
            $val['module_id'] = $module_id;
            $val['module_type'] = $module_type;
            $val['measure_type'] = $measure_type;
            $val['measure_set'] = $name;
            $val['measure_value'] = $med;
            $this->update_table(self::live_weather_station_histo_yearly_table(), $val);
            return true;
        }
        catch(\Exception $ex) {
            return false;
        }
    }

    /**
     * Delete some daily values.
     *
     * @param string $device_id The station.
     * @param string $module_id The module.
     * @param string $measure_type The measure.
     * @param string $tz The timezone.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function delete_daily_values($device_id, $module_id, $measure_type, $tz) {
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "DELETE FROM ".$table_name." WHERE `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "' AND `module_id`='" . $module_id . "' AND `measure_type`='" . $measure_type . "';";
        return $wpdb->query($sql);
    }

    /**
     * Delete remaining daily values.
     *
     * @param string $device_id The station.
     * @param string $tz The timezone.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function delete_remaining_daily_values($device_id, $tz) {
        $max = date('Y-m-d H:i:s', self::get_local_yesterday_noon($tz));
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "DELETE FROM ".$table_name." WHERE `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "';";
        return $wpdb->query($sql);
    }


}