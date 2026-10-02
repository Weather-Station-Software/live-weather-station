<?php

namespace WeatherStation\DB;

use WeatherStation\DB\Storage;
use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Device\Manager as DeviceManager;
use WeatherStation\System\SQL\Guard;

/**
 * Query management.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Query {
    
    use Storage;

    protected $dont_filter = array('loc_latitude', 'loc_longitude', 'loc_altitude', 'windstrength_hour_max', 'windangle_hour_max',
                                 'windangle_day_max', 'winddirection_hour_max', 'winddirection_day_max','last_seen', 'date_setup', 'last_upgrade',
                                 'co2_min', 'co2_max', 'co2_trend', 'humidity_min', 'humidity_max', 'humidity_trend', 'noise_min', 'noise_max', 'noise_trend',
                                 'pressure_min', 'pressure_max', 'pressure_trend', 'pressure_sl_min', 'pressure_sl_max', 'pressure_sl_trend', 'temperature_min',
                                 'temperature_max', 'temperature_trend', 'irradiance_min', 'irradiance_max', 'irradiance_trend', 'uv_index_min', 'uv_index_max',
                                 'uv_index_trend', 'illuminance_min', 'illuminance_max', 'illuminance_trend', 'soil_temperature_min', 'soil_temperature_max',
                                 'soil_temperature_trend', 'moisture_content_min', 'moisture_content_max', 'moisture_content_trend', 'moisture_tension_min',
                                 'moisture_tension_max', 'moisture_tension_trend', 'windstrength_day_trend', 'absolute_humidity_min', 'absolute_humidity_max',
                                 'absolute_humidity_trend', 'loc_city', 'loc_country', 'loc_timezone', 'cloudiness_min', 'cloudiness_max', 'cloudiness_trend',
                                 'guststrength_day_min', 'guststrength_day_max', 'guststrength_day_trend', 'visibility_min', 'visibility_max', 'visibility_trend',
                                 'windstrength_day_min', 'windstrength_day_max', 'windstrength_day_trend');
    protected $min_max_trend = array('absolute_humidity', 'cloudiness', 'co2', 'guststrength', 'humidity', 'illuminance', 'irradiance', 'moisture_content',
        'moisture_tension', 'noise', 'pressure', 'pressure_sl', 'temperature', 'soil_temperature', 'uv_index', 'visibility', 'windstrength');

    /**
     * Filter data regarding its timestamp.
     *
     * @param array $data The data to filter.
     * @param integer|null $level Optional. The obsolescence level to apply (the plugin option if null).
     * @return array An array containing the filtered data.
     * @since 2.0.0
     */
    private function obsolescence_filtering($data, $level=null) {
        $time = 0;
        $time_owm = 0;
        switch ($level === null ? get_option('live_weather_station_obsolescence') : $level) {
            case 1 :
                $time = 30 * 60;
                $time_owm = floor(2 * 60 * 60);
                break;
            case 2 :
                $time = 60 * 60;
                $time_owm = floor(2 * 60 * 60);
                break;
            case 3 :
                $time = 2 * 60 * 60;
                $time_owm = $time;
                break;
            case 4 :
                $time = 4 * 60 * 60;
                $time_owm = $time;
                break;
            case 5 :
                $time = 12 * 60 * 60;
                $time_owm = $time;
                break;
            case 6 :
                $time = 24 * 60 * 60;
                $time_owm = $time;
                break;
            case 99:
                $time = 20 * 60;
                $time_owm = floor(2 * 60 * 60);
                break;
        }
        $time_filter = time() - $time;
        $time_filter_owm = time() - $time_owm;
        if ($time == 0) {
            $result = $data;
        }
        else {
            $result = array();
            foreach ($data as $line) {
                if (in_array($line['measure_type'], $this->dont_filter)) {
                    $result[] = $line;
                }
                elseif ($line['module_type'] == 'NACurrent') {
                    if (strtotime($line['measure_timestamp']) > $time_filter_owm) {
                        $result[] = $line;
                    }
                }
                elseif (strtotime($line['measure_timestamp']) > $time_filter) {
                    $result[] = $line;
                }
            }
        }
        return $result;
    }

    /**
     * Get sub attributes for some measure types.
     *
     * @param array $attributes An array representing the query.
     * @param boolean $full_mode For ful aggregated rendering.
     * @return array An array containing all the sub attributes + the attributes.
     * @since 2.1.0
     */
    private function get_sub_attributes($attributes, $full_mode=false) {
        $sub_attributes = array();
        switch ($attributes['measure_type']) {
            case 'dew_point':
                $sub_attributes[] = 'dew_point';
                $sub_attributes[] = 'temperature_ref';
                break;
            case 'frost_point':
                $sub_attributes[] = 'frost_point';
                $sub_attributes[] = 'temperature_ref';
                break;
            case 'heat_index':
                $sub_attributes[] = 'heat_index';
                $sub_attributes[] = 'dew_point';
                $sub_attributes[] = 'temperature_ref';
                $sub_attributes[] = 'humidity_ref';
                break;
            case 'humidex':
                $sub_attributes[] = 'humidex';
                $sub_attributes[] = 'dew_point';
                $sub_attributes[] = 'temperature_ref';
                $sub_attributes[] = 'humidity_ref';
                break;
            case 'wind_chill':
                $sub_attributes[] = 'wind_chill';
                $sub_attributes[] = 'temperature_ref';
                break;
            case 'steadman':
                $sub_attributes[] = 'steadman';
                $sub_attributes[] = 'temperature_ref';
                $sub_attributes[] = 'wind_ref';
                $sub_attributes[] = 'humidity_ref';
                break;
            case 'summer_simmer':
                $sub_attributes[] = 'summer_simmer';
                $sub_attributes[] = 'temperature_ref';
                $sub_attributes[] = 'humidity_ref';
                break;
            case 'delta_t':
                $sub_attributes[] = 'delta_t';
                $sub_attributes[] = 'temperature_ref';
                $sub_attributes[] = 'humidity_ref';
                break;
            case 'windangle':
                $sub_attributes[] = 'windangle';
                if ($full_mode) {
                    $sub_attributes[] = 'gustangle';
                }
                break;
            case 'winddirection':
                $sub_attributes[] = 'winddirection';
                if ($full_mode) {
                    $sub_attributes[] = 'gustdirection';
                }
                break;
            case 'sos': // Helper to found names (station and module) in case there's no data from first round
                $sub_attributes[] = 'temperature';
                $sub_attributes[] = 'humidity';
                $sub_attributes[] = 'pressure';
                $sub_attributes[] = 'rain';
                $sub_attributes[] = 'snow';
                $sub_attributes[] = 'cloudiness';
                break;
            default:
                $sub_attributes[] = $attributes['measure_type'];
        }
        if (in_array($attributes['measure_type'], $this->min_max_trend)) {
            $sub_attributes[] = $attributes['measure_type'];
            if (strpos($attributes['measure_type'], 'strength')) {
                $sub_attributes[] = $attributes['measure_type'] . '_day_min';
                $sub_attributes[] = $attributes['measure_type'] . '_day_max';
                if ($full_mode) {
                    $sub_attributes[] = $attributes['measure_type'] . '_day_trend';
                }
            }
            else {
                $sub_attributes[] = $attributes['measure_type'] . '_min';
                $sub_attributes[] = $attributes['measure_type'] . '_max';
                if ($full_mode) {
                    $sub_attributes[] = $attributes['measure_type'] . '_trend';
                }
            }
        }
        return $sub_attributes;
    }

    /**
     * Get stations list.
     *
     * @return array An array containing the available stations.
     * @since 1.0.0
     */
    protected function get_operational_stations_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(\Exception $ex) {
            return array('device_name' => __('Weather Station', 'live-weather-station').' '.__('is not running...', 'live-weather-station'), 'device_id' => 'N/A') ;
        }
    }

    /**
     * Get indoor stations list.
     *
     * @return array An array containing the available stations.
     * @since 3.1.0
     */
    protected function get_operational_indoor_stations_list() {
        $ids = array();
        $main = '';
        foreach ($this->get_all_id_by_type(0) as $id) {
            $ids[] = $id['station_id'];
        }
        foreach ($this->get_all_id_by_type(6) as $id) {
            $ids[] = $id['station_id'];
        }
        global $wpdb;
        if (count($ids) > 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders() (one %s per id), ids are bound by prepare()
            $main = $wpdb->prepare("OR (module_type='NAMain' AND device_id IN (" . Guard::placeholders($ids, '%s') . "))", $ids);
        }
        $table_name = $wpdb->prefix . self::live_weather_station_measurements_table();
        $sql = "SELECT DISTINCT device_id, device_name, module_id, module_name FROM " . $table_name . " WHERE (module_type='NAModule4') OR (module_type='NAModule9') " . $main . ";";
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); $main is built by prepare() above; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        } catch (\Exception $ex) {
            return array('device_name' => __('Weather Station', 'live-weather-station') . ' ' . __('is not running...', 'live-weather-station'), 'device_id' => 'N/A');
        }
    }

    /**
     * Get thunderstorm stations list.
     *
     * @return array An array containing the available stations.
     * @since 3.3.0
     */
    protected function get_operational_thunderstorm_stations_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name . " WHERE module_type='NAModule7'";
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(\Exception $ex) {
            return array('device_name' => __('Weather Station', 'live-weather-station').' '.__('is not running...', 'live-weather-station'), 'device_id' => 'N/A') ;
        }
    }

    /**
     * Get solar stations list.
     *
     * @return array An array containing the available stations.
     * @since 3.3.0
     */
    protected function get_operational_solar_stations_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name . " WHERE module_type='NAModule5'";
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(\Exception $ex) {
            return array('device_name' => __('Weather Station', 'live-weather-station').' '.__('is not running...', 'live-weather-station'), 'device_id' => 'N/A') ;
        }
    }

    /**
     * Get modules array.
     *
     * @return array An array containing modules by stations.
     * @since 3.0.0
     */
    protected function get_operational_modules_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT device_id, module_type FROM `" . $table_name . "` WHERE module_id in (SELECT DISTINCT module_id FROM `" . $table_name . "`) GROUP BY module_id;" ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $qresult = array();
            foreach ($query_a as $val) {
                $qresult[] = (array)$val;
            }
            $result = array();
            foreach ($qresult as $line) {
                if (!array_key_exists($line['device_id'], $result)) {
                    $result[$line['device_id']] = array('comp_bas' => 0, 'comp_ext' => 0, 'comp_int' => 0, 'comp_xtd' => 0, 'comp_vrt' => 0);
                }
                switch (strtolower($line['module_type'])) {
                    case 'namain':
                        $result[$line['device_id']]['comp_bas'] = $result[$line['device_id']]['comp_bas'] + 1;
                        break;
                    case 'namodule1': // Outdoor module
                    case 'namodule3': // Rain gauge
                    case 'namodule2': // Wind gauge
                    case 'namodule5': // Solar module
                    case 'namodule6': // Soil module
                    case 'namodule7': // Thunderstorm module
                    case 'namodulep': // Picture module
                    case 'namodulev': // Video module
                        $result[$line['device_id']]['comp_ext'] = $result[$line['device_id']]['comp_ext'] + 1;
                        break;
                    case 'namodule4': // Additional indoor module
                        $result[$line['device_id']]['comp_int'] = $result[$line['device_id']]['comp_int'] + 1;
                        break;
                    case 'namodule9': // Additional module
                        $result[$line['device_id']]['comp_xtd'] = $result[$line['device_id']]['comp_xtd'] + 1;
                        break;
                    case 'nacomputed': // Computed values virtual module
                    case 'nacurrent': // Current weather (from OWM) virtual module
                    case 'napollution': // Pollution (from OWM) virtual module
                    case 'naforecast': // Forecast (from OWM) virtual module
                    case 'naephemer': // Ephemeris virtual module
                        $result[$line['device_id']]['comp_vrt'] = $result[$line['device_id']]['comp_vrt'] + 1;
                        break;
                }
            }
            return $result;
        }
        catch(\Exception $ex) {
            return array() ;
        }
    }

    /**
     * Filter stations list by type.
     *
     * @param array $values The values to filter
     * @param mixed $station_type Optional. The station type to query.
     * @return array An array containing the filtered values.
     * @since 3.1.0
     */
    private function filter_values_by_stations_type($values, $station_type=false) {
        if ($station_type === false) {
            $result = $values;
        }
        else {
            $result = array();
            $stations_list = $this->get_all_stations_by_type($station_type);
            $stations = array();
            foreach ($stations_list as $station) {
                $stations[] = strtoupper($station['station_id']);
            }
            foreach ($values as $value) {
                if (in_array(strtoupper($value['device_id']), $stations)) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Get stations list with latitude and longitude set.
     *
     * @param mixed $station_type Optional. The station type to query.
     * @return array An array containing the located stations.
     * @since 2.0.0
     */
    protected function get_located_operational_stations_list($station_type=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            $result = $this->filter_values_by_stations_type($result, $station_type);
        }
        catch(\Exception $ex) {
            $result = array() ;
        }
        $rq = '';
        $rq_ids = array();
        foreach ($result as $res) {
            $rq_ids[] = $res['device_id'];
        }
        if (count($rq_ids) > 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders() (one %s per id), ids are bound by prepare()
            $rq = $wpdb->prepare(" AND (device_id IN (" . Guard::placeholders($rq_ids, '%s') . "))", $rq_ids);
        }
        $sql = "SELECT device_id, device_name, measure_type, measure_value FROM ".$table_name." WHERE (module_type='NAMain') AND (measure_type LIKE 'loc_%')".$rq ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); $rq is built by prepare() above; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
        }
        catch(\Exception $ex) {
            $result = array() ;
        }
        $return = array();
        foreach ($result as $res) {
            $return[$res['device_id']]['device_name'] = $res['device_name'] ;
            $return[$res['device_id']][$res['measure_type']] = $res['measure_value'] ;
        }
        return $return;
    }

    /**
     * Get stations list with reference values (to compute dew point, wind chill,...).
     *
     * @param mixed $station_type Optional. The station type to query.
     * @return array An array containing the stations with reference values.
     * @since 2.0.0
     */
    private function get_reference_values($station_type=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT device_id, device_name, module_type, measure_timestamp, measure_type, measure_value FROM ".$table_name." WHERE (module_type='NAMain' OR module_type='NAModule1' OR module_type='NAModule2' OR module_type='NACurrent') AND (measure_type='temperature' OR measure_type='humidity' OR measure_type='windstrength' OR measure_type='winddirection' OR measure_type='pressure' OR measure_type='pressure_trend' OR measure_type='pressure_sl' OR measure_type LIKE 'loc_%')" ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            $result = $this->obsolescence_filtering($this->filter_values_by_stations_type($result, $station_type));
        }
        catch(\Exception $ex) {
            $result = array() ;
        }
        $return = array();
        foreach ($result as $res) {
            $return[$res['device_id']]['device_name'] = $res['device_name'] ;
            $return[$res['device_id']][$res['measure_type']][$res['module_type']]['value'] = $res['measure_value'] ;
            $return[$res['device_id']][$res['measure_type']][$res['module_type']]['timestamp'] = $res['measure_timestamp'] ;
        }
        $result = array();
        foreach ($return as $device_id => $device) {
            $result[$device_id]['name'] = $device['device_name'];
            foreach ($device as $measure_type => $measure) {
                if (is_array($measure)) {
                    $value = -9999;
                    foreach ($measure as $module_type => $module) {
                        $value = $module['value'];
                        if ($measure_type == 'temperature' || $measure_type == 'humidity') {
                            if ($module_type == 'NAModule1') {
                                $result[$device_id][$measure_type] = $value;
                            }
                            if ($module_type == 'NACurrent') {
                                if (!array_key_exists($measure_type, $result[$device_id])) {
                                    $result[$device_id][$measure_type] = $value;
                                }
                            }
                        }
                        if ($measure_type == 'windstrength' || $measure_type == 'winddirection') {
                            if ($module_type == 'NAModule2') {
                                $result[$device_id][$measure_type] = $value;
                            }
                            if ($module_type == 'NACurrent') {
                                if (!array_key_exists($measure_type, $result[$device_id])) {
                                    $result[$device_id][$measure_type] = $value;
                                }
                            }
                        }
                        if ($measure_type == 'pressure' || $measure_type == 'pressure_trend' || $measure_type == 'pressure_sl') {
                            if ($module_type == 'NAMain') {
                                $result[$device_id][$measure_type] = $value;
                            }
                            if ($module_type == 'NACurrent') {
                                if (!array_key_exists($measure_type, $result[$device_id])) {
                                    $result[$device_id][$measure_type] = $value;
                                }
                            }
                        }
                        if (strpos($measure_type, 'loc_') === 0) {
                            $result[$device_id][$measure_type] = $value;
                        }
                    }
                }
            }
            if (array_key_exists('loc_latitude', $result[$device_id]) && $result[$device_id]['loc_latitude'] < 0) {
                $result[$device_id]['north'] = false;
            }
            else {
                $result[$device_id]['north'] = true;
            }
            $ref_min = 950;
            $ref_max = 1050;
            if ((bool)get_option('live_weather_station_build_history')) {
                $ret = get_option('live_weather_station_retention_history');
                if ($ret == 0 || $ret > 52) {
                    $station = $this->get_station_information_by_station_id($device_id);
                    if (array_key_exists('oldest_data', $station) && $station['oldest_data'] != '0000-00-00') {
                        $old = \DateTime::createFromFormat('Y-m-d', $station['oldest_data']);
                        if ($old !== false && time() - $old->getTimestamp() > 60 * 60 * 24 * 365) {
                            $table_name = $wpdb->prefix.self::live_weather_station_histo_yearly_table();
                            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
                            $sql = $wpdb->prepare("SELECT module_type, MAX(measure_value) as max_pressure, MIN(measure_value) as min_pressure FROM " . $table_name . " WHERE device_id=%s AND (module_type='NAMain' OR module_type='NACurrent') AND measure_type='pressure_sl' AND measure_set='avg' GROUP BY module_type", $device_id);
                            $cache_id = 'get_min_max_pressure_'.$device_id;
                            $value = Cache::get_query($cache_id);
                            if ($value === false) {
                                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table yearly history (name from the internal self::live_weather_station_histo_yearly_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                                $query = $wpdb->get_results($sql, ARRAY_A);
                                $min = null;
                                $max = null;
                                foreach ($query as $row) {
                                    if ($row['module_type'] == 'NAMain') {
                                        $min = $row['min_pressure'];
                                        $max = $row['max_pressure'];
                                    }
                                    if ($row['module_type'] == 'NACurrent') {
                                        if (!$min) {
                                            $min = $row['min_pressure'];
                                        }
                                        if (!$max) {
                                            $max = $row['max_pressure'];
                                        }
                                    }
                                }
                                $value = array();
                                $value['min'] = $min;
                                $value['max'] = $max;
                                Cache::set_query($cache_id, $value, 172800); // cache it for 48 hours
                            }
                            if (is_array($value) && array_key_exists('min', $value) && array_key_exists('max', $value)) {
                                $ref_min = $value['min'];
                                $ref_max = $value['max'];
                            }
                        }
                    }
                }
            }
            $result[$device_id]['pressure_sl_min'] = $ref_min;
            $result[$device_id]['pressure_sl_max'] = $ref_max;
        }
        return $result;
    }

    /**
     * Get the name of a station.
     *
     * @param string $device_id The device ID.
     * @return string The name of the station.
     * @since 1.0.0
     */
    protected function get_operational_station_name($device_id) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT DISTINCT device_name FROM ".$table_name. " WHERE device_id=%s", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            if (!isset($query_a[0])) {
                return '';
            }
            $query_t = (array)$query_a[0];
            $result = $query_t['device_name'];
            return $result;
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get all measurements for a single module.
     *
     * @param string $module_id The module ID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @return array An array containing all the measurements.
     * @since  1.0.0
     */
    protected function get_module_measurements($module_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE module_id=%s", $module_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get outdoor measurements.
     *
     * @param   string      $device_id                  The device ID.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @param   boolean     $strict_filtering           Don't return not necessary data.
     * @return  array   An array containing the outdoor measurements.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_outdoor_measurements($device_id, $obsolescence_filtering=false, $strict_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE device_id=%s AND (module_type='NAMain' OR module_type='NAEphemer' OR module_type='NAComputed' OR module_type='NAPollution' " . ($strict_filtering ? "" : "OR module_type='NACurrent' ") . "OR module_type='NAModule1' OR module_type='NAModule2' OR module_type='NAModule3' OR module_type='NAModule5' OR module_type='NAModule6' OR module_type='NAModule7') ORDER BY module_id ASC", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get thunderstorm data.
     *
     * @param string $device_id The device ID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @return array An array containing the outdoor data.
     * @since 3.3.0
     */
    protected function get_thunderstorm_measurements($device_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE device_id=%s AND (module_type='NAMain' OR module_type='NAEphemer' OR module_type='NAModule7') ORDER BY module_id ASC", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get solar data.
     *
     * @param string $device_id The device ID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @return array An array containing the outdoor data.
     * @since 3.3.0
     */
    protected function get_solar_measurements($device_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE device_id=%s AND (module_type='NAMain' OR module_type='NAEphemer' OR module_type='NACurrent' OR module_type='NAModule5') ORDER BY module_id ASC", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get outdoor measurements.
     *
     * @param string $_id The module ID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @return array An array containing the indoor measurements.
     * @since 3.1.0
     */
    protected function get_indoor_measurements($_id, $obsolescence_filtering=false) {
        $a = explode ('-', (string)$_id);
        if (count($a) < 2) {
            return array();
        }
        $device_id = $a[0];
        $module_id = $a[1];
        return $this->get_module_measurements($module_id, $obsolescence_filtering);
    }

    /**
     * Get pollution measurements.
     *
     * @param   string      $device_id                  The device ID.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @return  array   An array containing the pollution measurements.
     * @since    2.7.0
     * @access   protected
     */
    protected function get_pollution_measurements($device_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE device_id=%s AND (module_type='NAPollution')", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get computed measurements.
     *
     * @param string $device_id The device ID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @return array An array containing the pollution measurements.
     * @since 3.3.0
     */
    protected function get_computed_measurements($device_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE device_id=%s AND (module_type='NAComputed')", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get ephemeris measurements.
     *
     * @param   string  $device_id  The device ID.
     * @return  array   An array containing the ephemeris measurements.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_ephemeris_measurements($device_id) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM ".$table_name. " WHERE device_id=%s AND (module_type='NAMain' OR module_type='NAEphemer') ORDER BY module_id ASC", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get all measurements for a single station.
     *
     * @param string $device_id The device ID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @param integer|null $obsolescence_level Optional. The obsolescence level to apply (the plugin option if null).
     * @return array An array containing all the measurements.
     * @since 1.0.0
     */
    protected function get_all_measurements($device_id, $obsolescence_filtering=false, $obsolescence_level=null) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $order = " ORDER BY CASE module_type WHEN 'NAMain' THEN 1 WHEN 'NAModule1' THEN 2 WHEN 'NAModule2' THEN 3 WHEN 'NAModule3' THEN 4 WHEN 'NAModule5' THEN 5 WHEN 'NAModule7' THEN 6 WHEN 'NAModule6' THEN 7 WHEN 'NAComputed' THEN 8 WHEN 'NAModule4' THEN 9 WHEN 'NAEphemer' THEN 10 WHEN 'NACurrent' THEN 11 ELSE 12 END";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $order is a constant ORDER BY CASE clause without external value, device id is bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE device_id=%s" . $order, $device_id);
        try {
            $cache_id = 'get_all_measurements_'.$device_id;
            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                $query = (array)$wpdb->get_results($sql);
                Cache::set_query($cache_id, $query);
            }
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result, $obsolescence_level) : $result);
        }
        catch(\Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent measurements', 'live-weather-station')));
        }
    }

    /**
     * Get all data, ready to push, for a single station.
     *
     * @param string $device_id The device ID.
     * @return array An array containing all the data.
     * @since 3.0.0
     */
    protected function get_all_measurements_for_push($device_id) {
        // Level 99: data to push are filtered with their own obsolescence, without touching the global option.
        $data = $this->get_all_measurements($device_id, true, 99);
        $result = array();
        if (!array_key_exists('condition', $data)) {
            foreach ($data as $line) {
                switch (strtolower($line['module_type'])) {
                    case 'namain': // Main base
                        if ($line['measure_type'] == 'pressure_sl') {
                            $result['pressure'] = $line['measure_value'];
                        }
                        if ($line['measure_type'] == 'last_seen') {
                            $result['timestamp'] = $line['measure_value'];
                        }
                        break;
                    case 'namodule1': // Outdoor module
                        if ($line['measure_type'] == 'temperature') {
                            $result['temperature'] = $line['measure_value'];
                        }
                        if ($line['measure_type'] == 'humidity') {
                            $result['humidity'] = $line['measure_value'];
                        }
                        break;
                    case 'namodule3': // Rain gauge
                        if ($line['measure_type'] == 'rain_hour_aggregated') {
                            $result['rain_hour_aggregated'] = $line['measure_value'];
                        }
                        if ($line['measure_type'] == 'rain_day_aggregated') {
                            $result['rain_day_aggregated'] = $line['measure_value'];
                        }
                        break;
                    case 'namodule2': // Wind gauge
                        if ($line['measure_type'] == 'windangle') {
                            $result['windangle'] = $line['measure_value'];
                        }
                        if ($line['measure_type'] == 'windstrength') {
                            $result['windstrength'] = $line['measure_value'];
                        }
                        if ($line['measure_type'] == 'gustangle') {
                            $result['gustangle'] = $line['measure_value'];
                        }
                        if ($line['measure_type'] == 'guststrength') {
                            $result['guststrength'] = $line['measure_value'];
                        }
                        break;
                    case 'nacomputed': // Computed values virtual module
                        if ($line['measure_type'] == 'dew_point') {
                            $result['dew_point'] = $line['measure_value'];
                        }
                        break;
                }
            }
        }
        return $result;
    }

    /**
     * Get specific lines.
     *
     * @param array $attributes An array representing the query.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @param boolean $full_mode For ful aggregated rendering.
     * @return array An array containing all the measurements.
     * @since 2.1.0
     */
    protected function get_line_measurements($attributes, $obsolescence_filtering=false, $full_mode=false) {
        global $wpdb;
        $sub_attributes = $this->get_sub_attributes($attributes, $full_mode);
        $sub_attributes = array_values($sub_attributes);
        $table_name = $wpdb->prefix . self::live_weather_station_measurements_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- IN() list built by Guard::placeholders(), the merged array holds exactly one value per placeholder
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE device_id=%s AND module_id=%s AND (measure_type IN (" . Guard::placeholders($sub_attributes, '%s') . "))", array_merge(array($attributes['device_id'], $attributes['module_id']), $sub_attributes));
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering? $this->obsolescence_filtering($result) : $result);
        }
        catch(\Exception $ex) {
            return array();
        }
    }

    /**
     * Get specific data.
     *
     * @param   array   $attributes  An array representing the query.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @return array An array containing all the measurements.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_specific_measurements($attributes, $obsolescence_filtering=false) {
        global $wpdb;
        $sub_attributes = $this->get_sub_attributes($attributes);
        $sub_attributes = array_values($sub_attributes);
        $table_name = $wpdb->prefix . self::live_weather_station_measurements_table();
        $element = Guard::ident($attributes['element'], array('device_id', 'device_name', 'module_id', 'module_type', 'module_name', 'measure_timestamp', 'measure_type', 'measure_value'));
        $result = array();
        if ($element === null) {
            return $result;
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- column validated by Guard::ident() against an allowlist, IN() list built by Guard::placeholders(), the merged array holds exactly one value per placeholder
        $sql = $wpdb->prepare("SELECT " . $element . ", module_type" . ($element!="measure_type"?", measure_type":"") . " FROM " . $table_name . " WHERE device_id=%s AND module_id=%s AND (measure_type IN (" . Guard::placeholders($sub_attributes, '%s') . "))", array_merge(array($attributes['device_id'], $attributes['module_id']), $sub_attributes));
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $i = 0;
            foreach ($query as $q) {
                $tmp = (array)$q;
                $result['result'][$tmp['measure_type']] = $tmp[$attributes['element']];
                if ($attributes['measure_type']==$tmp['measure_type']) {
                    $result['module_type'] = $tmp['module_type'];
                }
                $i++;
            }
            // The rows of $result carry no timestamp and obsolescence_filtering() expects a list of rows: filtering this shape would raise a TypeError on PHP 8, so $obsolescence_filtering is not applied here.
            return $result;
        }
        catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get the oldest data array.
     *
     * @param array $station The station.
     * @return mixed The oldest date or false.
     * @since 3.4.0
     */
    public function get_oldest_data($station) {
        $data = $this->_get_oldest_data($station);
        if (count($data) > 0) {
            return $data[0]['timestamp'];
        }
        else {
            return false;
        }
    }

    /**
     * Get the youngest data array.
     *
     * @param array $station The station.
     * @return mixed The youngest date or false.
     * @since 3.8.0
     */
    public function get_youngest_data($station) {
        $data = $this->_get_youngest_data($station);
        if (count($data) > 0) {
            return $data[0]['timestamp'];
        }
        else {
            return false;
        }
    }

    /**
     * Get the oldest data array.
     *
     * @param array $station The station.
     * @return array The 3 oldest dates.
     * @since 3.4.0
     */
    private function _get_oldest_data($station) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT DISTINCT(`timestamp`) FROM ".$table_name. " WHERE device_id=%s ORDER BY `timestamp` ASC LIMIT 3", $station['station_id']);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table yearly history (name from the internal self::live_weather_station_histo_yearly_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $data = array();
            foreach ($query_a as $val) {
                $data[] = (array)$val;
            }
            return $data;
        }
        catch(\Exception $ex) {
            return array();
        }
    }

    /**
     * Get the youngest data array.
     *
     * @param array $station The station.
     * @return array The 3 youngest dates.
     * @since 3.8.0
     */
    private function _get_youngest_data($station) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT DISTINCT(`timestamp`) FROM ".$table_name. " WHERE device_id=%s ORDER BY `timestamp` DESC LIMIT 3", $station['station_id']);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table yearly history (name from the internal self::live_weather_station_histo_yearly_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $data = array();
            foreach ($query_a as $val) {
                $data[] = (array)$val;
            }
            return $data;
        }
        catch(\Exception $ex) {
            return array();
        }
    }

    /**
     * Is there a measurement type in historical data?
     *
     * @param string $type The measurement type to verify.
     * @return boolean True if one or more rows are detected..
     * @since 3.8.0
     */
    public function is_historized($type) {
        global $wpdb;
        $result = 0;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT COUNT(*) as val FROM " . $table_name . " WHERE measure_type=%s", $type);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table yearly history (name from the internal self::live_weather_station_histo_yearly_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
        $count = $wpdb->get_results($sql, ARRAY_A);
        if ($count) {
            if (is_array($count)) {
                if (count($count) == 1) {
                    if (array_key_exists('val', $count[0])) {
                        $result = (int)$count[0]['val'];
                    }
                }
            }
        }
        return (0 < $result);
    }

    /**
     * Update the value 'oldest_data' for a station.
     *
     * @param string $station_id The station id.
     * @since 3.7.0
     */
    public function update_oldest_data($station_id) {
        $station = $this->get_station_information_by_station_id($station_id);
        if (is_array($station) && !empty($station)) {
            if ($date = $this->get_oldest_data($station)) {
                $station['oldest_data'] = $date;
                $this->update_stations_table($station);
            }
        }
    }

    /**
     * Get station information.
     *
     * @param string $station_id The station id.
     * @return array An array containing the station information.
     * @since 2.3.0
     */
    protected function get_station_information_by_station_id($station_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE station_id=%s", $station_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            if (is_array($result) && !empty($result)) {
                return $result[0];
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get extended station information.
     *
     * @param integer $station_id The station id.
     * @return array An array containing the extended station information.
     * @since 3.4.0
     */
    protected function get_extended_station_information_by_station_id($station_id) {
        $result = $this->get_station_information_by_station_id($station_id);
        if (count($result) !== 0) {
            $modules = DeviceManager::get_modules_details($station_id);
            $result['modules_names'] = array();
            foreach ($modules as $module) {
                if ($module['screen_name'] == '') {
                    $result['modules_names'][$module['module_id']] = $module['module_name'];
                }
                else {
                    $result['modules_names'][$module['module_id']] = $module['screen_name'];
                }
            }
        }
        return $result ;
    }

    /**
     * Get station guid.
     *
     * @param integer $station_id The station id.
     * @return integer The guid value.
     */
    protected function get_station_guid_by_station_id($station_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT guid FROM " . $table_name . " WHERE station_id=%s", $station_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            if (is_array($result) && !empty($result)) {
                return $result[0]['guid'];
            }
            else {
                return 0;
            }
        } catch (\Exception $ex) {
            return 0;
        }
    }

    /**
     * Get a list of all maps.
     *
     * @return array The maps list.
     * @since 3.7.0
     */
    protected function get_all_maps() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_maps_table();
        $sql = "SELECT * FROM " . $table_name . ";";
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table maps (name from the internal self::live_weather_station_maps_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, configuration must be current
            return $wpdb->get_results($sql, ARRAY_A);

        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Add a new map.
     *
     * @param integer $type The map type.
     * @param string $name The map name.
     * @param array $params The map parameters.
     * @return integer The map id.
     * @since 3.7.0
     */
    protected function add_new_map($type, $name, $params) {
        $val = array();
        $val['type'] = $type;
        $val['name'] = $name;
        $val['params'] = serialize($params);
        return self::insert_table(self::live_weather_station_maps_table(), $val);
    }

    /**
     * Update a new map.
     *
     * @param integer $id The map id.
     * @param integer $type The map type.
     * @param string $name The map name.
     * @param array $params The map parameters.
     * @since 3.7.0
     */
    protected function update_map($id, $type, $name, $params) {
        if ($params['common']['all']) {
            $barycenter = self::get_all_stations_barycenter();
        }
        else {
            $barycenter = self::get_all_stations_barycenter($params['stations']);
        }
        $params['common']['loc_latitude'] = $barycenter['latitude'];
        $params['common']['loc_longitude'] = $barycenter['longitude'];
        $val = array();
        $val['id'] = $id;
        $val['type'] = $type;
        $val['name'] = $name;
        $val['params'] = serialize($params);
        self::insert_update_table(self::live_weather_station_maps_table(), $val);
    }

    /**
     * Get the detail of a map.
     *
     * @param integer $id The map id.
     * @return array The map details.
     * @since 3.7.0
     */
    protected function get_map_detail($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_maps_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id=%d;", intval($id));
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table maps (name from the internal self::live_weather_station_maps_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $result = $wpdb->get_results($sql, ARRAY_A);
            if (count($result) > 0) {
                return $result[0];
            }

        } catch (\Exception $ex) {
            return array();
        }
        return array();
    }

    /**
     * Synchronizes the count of modules per station.
     *
     * @since 3.0.0
     */
    protected function synchronize_modules_count() {
        $modules = $this->get_operational_modules_list();
        if (count($modules) > 0) {
            foreach ($modules as $key => $module) {
                $station = $this->get_station_information_by_station_id($key);
                if (count($station) > 0) {
                    $station['comp_bas'] = $module['comp_bas'];
                    $station['comp_ext'] = $module['comp_ext'];
                    $station['comp_int'] = $module['comp_int'];
                    $station['comp_xtd'] = $module['comp_xtd'];
                    $station['comp_vrt'] = $module['comp_vrt'];
                    $this->update_table(self::live_weather_station_stations_table(), $station);
                }
            }
        }
        Cache::invalidate_backend(Cache::$db_stat_operational);
    }

    /**
     * Get station information.
     *
     * @param integer $guid The station guid.
     * @return array An array containing the station information.
     * @since 3.0.0
     */
    protected static function get_station($guid) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
        try {
            $cache_id = 'get_station'.$guid;
            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                $query = (array)$wpdb->get_results($sql);
                Cache::set_query($cache_id, $query);
            }
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            if (is_array($result) && !empty($result)) {
                return $result[0];
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Resolve a station identifier coming from a request to the guid of an existing station.
     *
     * Accepts a numeric guid or a "station_id" (MAC address like identifier containing a colon). Anything else
     * (empty, zero, negative, array, free text) or a station that does not exist gives 0.
     *
     * @param mixed $id The raw identifier.
     * @return integer The guid of the existing station, 0 if there is no such station.
     * @since 3.9.0
     */
    protected static function get_existing_station_guid($id) {
        global $wpdb;
        if (!is_scalar($id)) {
            return 0;
        }
        $id = trim((string)$id);
        if ($id === '') {
            return 0;
        }
        if (strpos($id, ':') > 0) {
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, configuration must be current
            $guid = $wpdb->get_var($wpdb->prepare("SELECT guid FROM " . $table_name . " WHERE station_id=%s", $id));
            return ($guid === null ? 0 : (int)$guid);
        }
        if (!ctype_digit($id) || (int)$id <= 0) {
            return 0;
        }
        $station = self::get_station((int)$id);
        return (is_array($station) && !empty($station) ? (int)$id : 0);
    }

    /**
     * Get the barycenter of all the stations coordinates.
     *
     * @param array $guids Optional. The guids to take into account.
     * @return array An array containing the lat & lon of the barycenter.
     * @since 3.0.0
     */
    protected static function get_all_stations_barycenter($guids=array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        if (count($guids) === 0) {
            $sql = "SELECT AVG(loc_latitude) as latitude, AVG(loc_longitude) as longitude FROM " . $table_name . ";";
            $cache_id = 'stations_barycenter';
        }
        else {
            $sql = "SELECT AVG(loc_latitude) as latitude, AVG(loc_longitude) as longitude FROM " . $table_name . " WHERE guid IN (" . Guard::placeholders($guids, '%d') . ");";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders( $guids, %d ) and bound as integers
            $sql = $wpdb->prepare($sql, array_map('intval', array_values($guids)));
            $cache_id = 'stations_barycenter_' . implode('', $guids);
        }
        try {

            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                $query = $wpdb->get_results($sql, ARRAY_A);
                if (count($query) > 0 && isset($query[0]['latitude']) && isset($query[0]['longitude'])) {
                    Cache::set_query($cache_id, $query);
                }
            }
            if (count($query) > 0 && isset($query[0]['latitude']) && isset($query[0]['longitude'])) {
                return $query[0];
            }
            else {
                return array('latitude' => 51.476852, 'longitude' => -0.000500); // Royal Observatory Greenwich, London, UK
            }
        } catch (\Exception $ex) {
            return array('latitude' => 51.476852, 'longitude' => -0.000500); // Royal Observatory Greenwich, London, UK
        }
    }

    /**
     * Get a station picture.
     *
     * @param string $device_id The station device id.
     * @param integer $rank Optional. The rank of the picture (1=the most recent).
     * @return array An array containing the picture details.
     * @since 3.6.0
     */
    protected static function get_picture($device_id, $rank=1) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_media_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE device_id=%s AND module_type='NAModuleP' ORDER BY `timestamp` DESC LIMIT %d,1", $device_id, max(0, (int)($rank-1)));
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table media (name from the internal self::live_weather_station_media_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = $wpdb->get_results($sql, ARRAY_A);
            if (is_array($query) && !empty($query)) {
                return $query[0];
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get a station video.
     *
     * @param string $device_id The station device id.
     * @param string $type Optional. The type of the video ('none', 'imperial', 'metric').
     * @param integer $rank Optional. The rank of the video (1=the most recent).
     * @return array An array containing the video details.
     * @since 3.6.0
     */
    protected static function get_video($device_id, $type='none', $rank=1) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_media_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE device_id=%s AND item_type=%s AND module_type='NAModuleV' ORDER BY `timestamp` DESC LIMIT %d,1", $device_id, $type, max(0, (int)($rank-1)));
        try {
            $cache_id = 'get_video_'.$type . '_' . $device_id . '_' . (int)$rank;
            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table media (name from the internal self::live_weather_station_media_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                $query = $wpdb->get_results($sql, ARRAY_A);
                Cache::set_query($cache_id, $query);
            }
            if (is_array($query) && !empty($query)) {
                return $query[0];
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get a station video knowing its date.
     *
     * @param string $device_id The station device id.
     * @param string $type Optional. The type of the video ('none', 'imperial', 'metric').
     * @param string $date Optional. The date of the video.
     * @return array An array containing the video details.
     * @since 3.6.0
     */
    protected static function get_video_by_date($device_id, $date, $type='none') {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_media_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE device_id=%s AND item_type=%s AND `timestamp`=%s AND module_type='NAModuleV'", $device_id, $type, $date);
        try {
            $cache_id = 'get_video_by_date_'.$type . '_' . $device_id . '_' . md5((string)$date);
            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table media (name from the internal self::live_weather_station_media_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                $query = $wpdb->get_results($sql, ARRAY_A);
                Cache::set_query($cache_id, $query);
            }
            if (is_array($query) && !empty($query)) {
                return $query[0];
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get list of videos dates.
     *
     * @param string $device_id The station device id.
     * @return array An array containing the video dates.
     * @since 3.6.0
     */
    protected static function get_video_dates($device_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_media_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT DISTINCT `timestamp` FROM " . $table_name . " WHERE device_id=%s AND module_type='NAModuleV' ORDER BY `timestamp` DESC", $device_id);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table media (name from the internal self::live_weather_station_media_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = $wpdb->get_results($sql, ARRAY_A);
            if (is_array($query) && !empty($query)) {
                return $query;
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get modules information.
     *
     * @param string $device_id The station device id.
     * @return array An array containing the modules details.
     * @since  3.5.0
     */
    protected static function get_modules_information($device_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_module_detail_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE device_id=%s", $device_id);
        try {
            $cache_id = 'get_modules'.$device_id;
            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table module details (name from the internal self::live_weather_station_module_detail_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; result cached by Cache::get_query()/set_query()
                $query = (array)$wpdb->get_results($sql);
                Cache::set_query($cache_id, $query);
            }
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            if (is_array($result) && !empty($result)) {
                return $result[0];
            }
            else {
                return array();
            }
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get the ordered list of background processes to execute.
     *
     * @param boolean $only_paused Optional. Only the paused processes.
     * @return array An array containing the processes.
     * @since 3.6.0
     */
    protected static function get_ready_background_processes($only_paused=false) {
        if ($only_paused) {
            $states = array('pause');
        }
        else {
            $states = array('init', 'pause', 'schedule');
        }
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_background_process_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders() (one %s per state), states are bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE state IN (" . Guard::placeholders($states, '%s') . ") ORDER BY priority ASC", $states);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table background_process (name from the internal self::live_weather_station_background_process_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the ordered list of active background processes.
     *
     * @return array An array containing the processes.
     * @since 3.7.0
     */
    protected static function get_active_background_processes() {
        $states = array('init', 'pause', 'schedule', 'running');
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_background_process_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders() (one %s per state), states are bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE state IN (" . Guard::placeholders($states, '%s') . ") ORDER BY priority ASC", $states);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table background_process (name from the internal self::live_weather_station_background_process_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the ordered list of active background processes.
     *
     * @return array An array containing the processes.
     * @since 3.7.0
     */
    protected static function get_status_for_active_background_processes() {
        $result = array();
        foreach (self::get_active_background_processes() as $process) {
            $result[$process['uuid']] = array('state' => $process['state'], 'progress' => $process['progress']);
        }
        return $result;
    }

    /**
     * Get station information.
     *
     * @param integer $guid The station guid.
     * @return array An array containing the station information.
     * @since  3.0.0
     */
    protected function get_station_information_by_guid($guid) {
        return self::get_station($guid);
    }

    /**
     * Get stations information.
     *
     * @return array An array containing the stations information.
     * @since 2.3.0
     */
    protected function get_stations_information() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        $sql = "SELECT * FROM " . $table_name ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get the name of a station in infos table.
     *
     * @param integer $guid The station guid.
     * @return string The name of the station.
     * @since 3.0.0
     */
    protected function get_infos_station_name_by_guid($guid) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_stations_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT DISTINCT station_name FROM ".$table_name. " WHERE guid=%s", $guid);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            if (!isset($query_a[0])) {
                return '';
            }
            $query_t = (array)$query_a[0];
            $result = $query_t['station_name'];
            return $result;
        }
        catch(\Exception $ex) {
            return '';
        }
    }

    /**
     * Get OpenWeatherMap stations list.
     *
     * @return  array   An array containing the available stations.
     * @since    2.0.0
     */
    protected function get_owm_stations_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_stations_table();
        $sql = "SELECT * FROM " . $table_name . " WHERE station_type=" . (int)LIVE_WEATHER_STATION_LOC_SID;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, configuration must be current
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
     * Get the full list of stations.
     *
     * @param integer $offset The offset to record.
     * @param integer $rowcount Optional. The number of rows to return.
     * @return array An array containing the stations.
     * @since 3.0.0
     */
    protected function get_stations_list($offset = null, $rowcount = null) {
        global $wpdb;
        $limit = '';
        $id = '';
        if (!is_null($offset) && !is_null($rowcount)) {
            $limit = $wpdb->prepare('LIMIT %d,%d', $offset, $rowcount);
            $id = $offset . '_' . $rowcount;
        }
        $table_name = $wpdb->prefix.self::live_weather_station_stations_table();
        $sql = "SELECT * FROM " . $table_name . " ORDER BY guid DESC " . $limit;
        try {
            $cache_id = 'get_stations_list'.$id;
            $query = Cache::get_query($cache_id);
            if ($query === false) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); $limit is built by prepare() above; result cached by Cache::get_query()/set_query()
                $query = (array)$wpdb->get_results($sql);
                Cache::set_query($cache_id, $query);
            }
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
     * Get the full list of stations - ordered.
     *
     * @param array $guids Optional. The guids to search for.
     * @return array An array containing the stations.
     * @since 3.7.0
     */
    protected function get_ordered_stations_list($guids=array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        if (count($guids) === 0) {
            $sql = "SELECT * FROM " . $table_name . " ORDER BY station_name ASC;";
        }
        else {
            $sql = "SELECT * FROM " . $table_name . " WHERE guid IN (" . Guard::placeholders($guids, '%d') . ") ORDER BY station_name ASC;";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders( $guids, %d ) and bound as integers
            $sql = $wpdb->prepare($sql, array_map('intval', array_values($guids)));
        }
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            return $wpdb->get_results($sql, ARRAY_A);
        }
        catch(\Exception $ex) {
            return array() ;
        }
    }

    /**
     * Get an OpenWeatherMap station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 2.0.0
     */
    protected function get_loc_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_LOC_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get an Clientraw station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.0.0
     */
    protected function get_raw_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_RAW_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['connection_type'] = 1;
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get Pioupiou station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.5.0
     */
    protected function get_piou_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_PIOU_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get a Realtime station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.0.0
     */
    protected function get_real_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_REAL_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['connection_type'] = 1;
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get a WeatherFlow station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.3.0
     */
    protected function get_wflw_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_WFLW_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get a WeatherLink station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.8.0
     */
    protected function get_wlink_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_WLINK_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get a Ambient station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.6.0
     */
    protected function get_ambt_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_AMBT_SID;
            $nothing['station_name'] = '';
            $nothing['station_model'] = 'N/A';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get a Stickertags station.
     *
     * @param integer $guid Optional. The station guid.
     * @return array An array containing the station details.
     * @since 3.3.0
     */
    protected function get_txt_station($guid=0) {
        if ($guid == 0) {
            $ccs = '';
            $cc = explode ('_', live_weather_station_get_display_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper(substr($cc[1], 0, 2));
            }
            $nothing = array();
            $nothing['guid'] = 0;
            $nothing['station_id'] = 'TMP-' . substr(uniqid('', true), 10, 13);
            $nothing['station_type'] = LIVE_WEATHER_STATION_TXT_SID;
            $nothing['station_name'] = '';
            $nothing['loc_city'] = '';
            $nothing['loc_country_code'] = $ccs;
            $nothing['loc_timezone'] = '';
            $nothing['loc_latitude'] = '';
            $nothing['loc_longitude'] = '';
            $nothing['loc_altitude'] = '';
            $nothing['connection_type'] = 1;
            $nothing['service_id'] = '';
            return $nothing;
        }
        else {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
            $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE guid=%s", $guid);
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return isset($result[0]) ? $result[0] : array();
            } catch (\Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get an OpenWeatherMap stations list.
     *
     * @param array $guids The array of stations guid.
     * @return array An array containing the stations details.
     * @since 2.0.0
     */
    protected function get_owm_stations($guids) {
        if (count($guids) > 0) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            $sql = "SELECT * FROM " . $table_name . " WHERE guid IN (" . Guard::placeholders($guids, '%d') . ")";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IN() list built by Guard::placeholders( $guids, %d ) and bound as integers
            $sql = $wpdb->prepare($sql, array_map('intval', array_values($guids)));
            try {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return $result;
            } catch (\Exception $ex) {
                return array();
            }
        }
        else {
            return array();
        }
    }

    /**
     * Get a list of all stations id of a given type.
     *
     * @param integer $type The type of stations.
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function get_all_id_by_type($type) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT station_id FROM " . $table_name . " WHERE station_type=%d", $type);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Get a list of all stations of a given type.
     *
     * @param integer $type The type of stations.
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function get_all_stations_by_type($type) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE station_type=%d", $type);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal self::live_weather_station_stations_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, configuration must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        } catch (\Exception $ex) {
            return array();
        }
    }

    /**
     * Clear a list of all stations of a given type.
     *
     * @param integer $type The type of stations.
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function clear_all_stations_by_type($type) {
        $list = $this->get_all_stations_by_type($type);
        $guid = array();
        $device_id = array();
        foreach ($list as $station) {
            $guid[] = $station['guid'];
            $device_id[] = $station['station_id'];
        }
        if (count($guid) > 0) {
            $this->delete_stations_table($guid);
        }
        if (count($device_id) > 0) {
            $this->delete_operational_stations_table($device_id);
        }
        Cache::invalidate_backend(Cache::$db_stat_operational);
        Cache::flush_query();
    }

    /**
     * Get a list of all Netatmo stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function get_all_netatmo_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_NETATMO_SID);
    }

    /**
     * Delete all Netatmo stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_netatmo_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_NETATMO_SID);
    }

    /**
     * Get a list of all Netatmo healthy home coaches.
     *
     * @return array An array containing the details of all stations.
     * @since 3.1.0
     */
    protected function get_all_netatmo_hc_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_NETATMOHC_SID);
    }

    /**
     * Delete all Netatmo healthy home coaches.
     *
     * @since 3.1.0
     */
    protected function clear_all_netatmo_hc_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_NETATMOHC_SID);
    }

    /**
     * Get a list of all BloomSky stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.6.0
     */
    protected function get_all_bsky_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_BSKY_SID);
    }

    /**
     * Delete all BloomSky stations.
     *
     * @since 3.6.0
     */
    protected function clear_all_bsky_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_BSKY_SID);
    }

    /**
     * Get a list of all Ambient stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.6.0
     */
    protected function get_all_ambt_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_AMBT_SID);
    }

    /**
     * Delete all Ambient stations.
     *
     * @since 3.6.0
     */
    protected function clear_all_ambt_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_AMBT_SID);
    }

    /**
     * Get a list of all OpenWeatherMap (local) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 2.0.0
     */
    protected function get_all_owm_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_LOC_SID);
    }

    /**
     * Delete all OpenWeatherMap stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_owm_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_LOC_SID);
    }

    /**
     * Delete all OpenWeatherMap (by Id) stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_owm_id_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_OWM_SID);
    }

    /**
     * Delete all WeatherUnderground (by Id) stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_wug_id_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_WUG_SID);
    }

    /**
     * Get a list of all WeatherFlow (by Id) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.3.0
     */
    protected function get_all_wflw_id_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_WFLW_SID);
    }

    /**
     * Get a list of all WeatherLink (by Id) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.8.0
     */
    protected function get_all_wlink_id_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_WLINK_SID);
    }

    /**
     * Get a list of all Pioupiou (by Id) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.3.0
     */
    protected function get_all_piou_id_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_PIOU_SID);
    }

    /**
     * Delete all WeatherFlow (by Id) stations.
     *
     * @since 3.3.0
     */
    protected function clear_all_wflw_id_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_WFLW_SID);
    }

    /**
     * Get a list of all clientraw (by Id) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function get_all_clientraw_id_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_RAW_SID);
    }

    /**
     * Delete all clientraw (by Id) stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_clientraw_id_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_RAW_SID);
    }

    /**
     * Get a list of all realtime (by Id) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function get_all_realtime_id_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_REAL_SID);
    }

    /**
     * Delete all realtime (by Id) stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_realtime_id_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_REAL_SID);
    }

    /**
     * Get a list of all stickertags (by Id) stations.
     *
     * @return array An array containing the details of all stations.
     * @since 3.0.0
     */
    protected function get_all_stickertags_id_stations() {
        return $this->get_all_stations_by_type(LIVE_WEATHER_STATION_TXT_SID);
    }

    /**
     * Delete all stickertags (by Id) stations.
     *
     * @since 3.0.0
     */
    protected function clear_all_stickertags_id_stations() {
        $this->clear_all_stations_by_type(LIVE_WEATHER_STATION_TXT_SID);
    }

    /**
     * Get "where" clause for log table.
     *
     * @param array $filters Optional. An array of filters.
     * @return string The "where" clause.
     * @since 3.0.0
     */
    private function get_log_where_clause($filters = array()) {
        global $wpdb;
        $result = '';
        if (count($filters) > 0) {
            $w = array();
            $columns = array('id', 'timestamp', 'level', 'plugin', 'version', 'system', 'service', 'device_id', 'device_name', 'module_id', 'module_name', 'code', 'message');
            foreach ($filters as $key => $filter) {
                $column = Guard::ident($key, $columns);
                if ($column === null) {
                    // Unknown filter: matches nothing
                    $w[] = '1=0';
                }
                elseif ($column == 'level') {
                    $l = array();
                    $max = (is_string($filter) && array_key_exists($filter, Logger::$severity)) ? Logger::$severity[$filter] : null;
                    foreach (Logger::$severity as $sev => $severity) {
                        if ($max !== null && $severity <= $max) {
                            $l[] = $sev;
                        }
                    }
                    if (count($l) > 0) {
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- column validated by Guard::ident() against an allowlist, IN() list built by Guard::placeholders(), values bound by prepare()
                        $w[] = '`' . $column . '` IN (' . $wpdb->prepare(Guard::placeholders($l, '%s'), $l) . ')';
                    }
                    else {
                        $w[] = '1=0';
                    }
                }
                elseif (!is_scalar($filter)) {
                    // A non scalar filter matches nothing (prepare() would read an array as a list of arguments)
                    $w[] = '1=0';
                }
                else {
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- column validated by Guard::ident() against an allowlist, value bound by prepare()
                    $w[] = $wpdb->prepare('`' . $column . '`=%s', $filter);
                }
            }
            $result = 'WHERE (' . implode(' AND ', $w) . ')';
        }
        return $result;
    }

    private function uasort_reorder_by_device_name($a,$b){
        return strcmp(strtolower($a), strtolower($b));
    }

    /**
     * Get id and names of all stations.
     *
     * @return array The id and name of all stations.
     * @since 3.0.0
     */
    protected function get_log_stations_array() {
        $result = array();
        $result['00:00:00:00:00:00'] = 'N/A';
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_measurements_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table measurements (name from the internal self::live_weather_station_measurements_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, live measurements must be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = array();
            foreach ($query_a as $val) {
                $query_t[] = (array)$val;
            }
            foreach ($query_t as $val) {
                $result[$val['device_id']] = $val['device_name'];
            }
        }
        catch(\Exception $ex) {
            return array();
        }
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name ;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table log (name from the internal self::live_weather_station_log_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, the log must always be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = array();
            foreach ($query_a as $val) {
                $query_t[] = (array)$val;
            }
            foreach ($query_t as $val) {
                if (!array_key_exists($val['device_id'], $result)) {
                    $result[$val['device_id']] = $val['device_name'] . ' (' . __('removed station', 'live-weather-station') . ')';
                }
            }
        }
        catch(\Exception $ex) {
            return array();
        }
        uasort($result, array($this, 'uasort_reorder_by_device_name'));
        return $result;
    }

    /**
     * Get list of logged errors.
     *
     * @param array $filters Optional. An array of filters.
     * @param integer $offset The offset to record.
     * @param integer $rowcount Optional. The number of rows to return.
     * @return array An array containing the filtered logged errors.
     * @since 3.0.0
     */
    protected function get_log_list($filters = array(), $offset = null, $rowcount = null) {
        global $wpdb;
        $limit = '';
        if (!is_null($offset) && !is_null($rowcount)) {
            $limit = $wpdb->prepare('LIMIT %d,%d', $offset, $rowcount);
        }
        if (array_key_exists('station', $filters)) {
            $filters['device_id'] = $filters['station'];
            unset($filters['station']);
        }
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT * FROM " . $table_name . " " . $this->get_log_where_clause($filters) . " ORDER BY id DESC " . $limit;
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table log (name from the internal self::live_weather_station_log_table() method prefixed by $wpdb->prefix); where clause built by get_log_where_clause() (columns allowlisted by Guard::ident, values prepared), LIMIT prepared; uncached, the log must always be current
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
     * Count logged errors.
     *
     * @param array $filters Optional. An array of filters.
     * @return integer The count of the filtered logged errors.
     * @since 3.0.0
     */
    protected function get_log_count($filters = array()) {
        global $wpdb;
        if (array_key_exists('station', $filters)) {
            $filters['device_id'] = $filters['station'];
            unset($filters['station']);
        }
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT COUNT(*) FROM " . $table_name . " " . $this->get_log_where_clause($filters);
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table log (name from the internal self::live_weather_station_log_table() method prefixed by $wpdb->prefix); where clause built by get_log_where_clause() (columns allowlisted by Guard::ident, values prepared), LIMIT prepared; uncached, the log must always be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = isset($query_a[0]) ? (array)$query_a[0] : array();
            $result = isset($query_t['COUNT(*)']) ? $query_t['COUNT(*)'] : 0;
            return $result;
        }
        catch(\Exception $ex) {
            return 0;
        }
    }

    /**
     * Get a specific log line.
     *
     * @param integer $log_entry The specific log entry to load.
     * @return array An array containing the filtered logged errors.
     * @since 3.0.0
     */
    protected function get_log_detail($log_entry) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- custom plugin table, name from the internal self::live_weather_station_*_table() method prefixed by $wpdb->prefix, values bound by prepare()
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id=%d", intval($log_entry));
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table log (name from the internal self::live_weather_station_log_table() method prefixed by $wpdb->prefix); SQL prepared above with bound values; uncached, the log must always be current
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
     * Get all services names.
     *
     * @return array The names of all services.
     * @since 3.0.0
     */
    protected function get_log_services_array() {
        $result = array();
        $result['N/A'] = 'N/A';
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT DISTINCT service FROM ".$table_name . " ORDER BY service ASC";
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table log (name from the internal self::live_weather_station_log_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, the log must always be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = array();
            foreach ($query_a as $val) {
                $query_t[] = (array)$val;
            }
            foreach ($query_t as $val) {
                if ($val['service'] != 'N/A') {
                    $result[$val['service']] = $val['service'];
                }
            }
        }
        catch(\Exception $ex) {
            return array();
        }
        return $result;
    }

    /**
     * Get all system names.
     *
     * @return array The names of all systems.
     * @since 3.0.0
     */
    protected function get_log_systems_array() {
        $result = array();
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT DISTINCT `system` FROM ".$table_name . " ORDER BY `system` ASC";
        try {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table log (name from the internal self::live_weather_station_log_table() method prefixed by $wpdb->prefix); static SQL without external value; uncached, the log must always be current
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = array();
            foreach ($query_a as $val) {
                $query_t[] = (array)$val;
            }
            foreach ($query_t as $val) {
                $result[$val['system']] = $val['system'];
            }
        }
        catch(\Exception $ex) {
            return array();
        }
        return $result;
    }

    /**
     * Get options table of the plugin - for backup purpose.
     *
     * @param string $table The table name - without prefix.
     * @return array An array containing all rows of the table.
     * @since 3.8.0
     */
    private static function get_table($table) {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . $table;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table, name passed by internal callers only (self::live_weather_station_*_table() constants), prefixed by $wpdb->prefix; static SQL without external value; uncached because used for backup/export
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Count the number of rows in a table.
     *
     * @param string $table The table name - without prefix.
     * @return integer The number of rows.
     * @since 3.8.0
     */
    private static function get_count($table) {
        $result = 0;
        global $wpdb;
        $sql = "SELECT COUNT(*) as val FROM " . $wpdb->prefix . $table;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table, name passed by internal callers only (self::live_weather_station_*_table() constants), prefixed by $wpdb->prefix; static SQL without external value; uncached because used for backup/export
        $count = $wpdb->get_results($sql, ARRAY_A);
        if ($count) {
            if (is_array($count)) {
                if (count($count) == 1) {
                    if (array_key_exists('val', $count[0])) {
                        $result = (int)$count[0]['val'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get maps count.
     *
     * @return integer The number of maps.
     * @since 3.8.0
     */
    public static function get_maps_count() {
        return self::get_count(self::live_weather_station_maps_table());
    }

    /**
     * Get stations count.
     *
     * @return integer The number of stations.
     * @since 3.8.0
     */
    public static function get_stations_count() {
        return self::get_count(self::live_weather_station_stations_table());
    }

    /**
     * Get maps table of the plugin - for backup purpose.
     *
     * @return array An array containing all rows of the maps table.
     * @since 3.8.0
     */
    public static function get_maps_table() {
        $result = self::get_table(self::live_weather_station_maps_table());
        Logger::notice('Core', null, null, null, null, null, 600, 'Maps table successfully exported.');
        return $result;
    }

    /**
     * Get modules table of the plugin - for backup purpose.
     *
     * @return array An array containing all rows of the modules table.
     * @since 3.8.0
     */
    public static function get_modules_table() {
        $result = self::get_table(self::live_weather_station_module_detail_table());
        Logger::notice('Core', null, null, null, null, null, 600, 'Modules table successfully exported.');
        return $result;
    }

    /**
     * Credential columns of the stations table.
     *
     * @return array The names of the columns containing credentials.
     * @since 3.8.0
     */
    private static function stations_credential_columns() {
        return array('owm_user', 'owm_password', 'pws_user', 'pws_password', 'wow_user', 'wow_password', 'wet_user', 'wet_password', 'wug_user', 'wug_password');
    }

    /**
     * Remove the credentials embedded in a station service_id: WeatherLink stores "id|token|password" (LIVE_WEATHER_STATION_SERVICE_SEPARATOR),
     * file based stations may store "scheme://user:password@host/path".
     *
     * @param mixed $service_id The service_id of a station.
     * @return string The service_id without any credential.
     * @since 3.8.0
     */
    public static function strip_service_credentials($service_id) {
        $service_id = (string)$service_id;
        if (strpos($service_id, LIVE_WEATHER_STATION_SERVICE_SEPARATOR) !== false) {
            $parts = explode(LIVE_WEATHER_STATION_SERVICE_SEPARATOR, $service_id);
            for ($i = 1; $i < count($parts); $i++) {
                $parts[$i] = '';
            }
            return implode(LIVE_WEATHER_STATION_SERVICE_SEPARATOR, $parts);
        }
        $stripped = preg_replace('#^([a-z][a-z0-9+.\-]*://)[^/\s]*@#i', '$1', $service_id);
        return is_string($stripped) ? $stripped : $service_id;
    }

    /**
     * Get stations table of the plugin - for backup purpose.
     *
     * @param boolean $include_credentials Optional. Include the credentials of the stations in the result.
     * @return array An array containing all rows of the stations table.
     * @since 3.8.0
     */
    public static function get_stations_table($include_credentials=false) {
        $result = self::get_table(self::live_weather_station_stations_table());
        if (!$include_credentials && is_array($result)) {
            foreach ($result as &$row) {
                foreach (self::stations_credential_columns() as $column) {
                    unset($row[$column]);
                }
                if (array_key_exists('service_id', $row)) {
                    $row['service_id'] = self::strip_service_credentials($row['service_id']);
                }
            }
            unset($row);
        }
        Logger::notice('Core', null, null, null, null, null, 600, 'Stations table successfully exported' . ($include_credentials ? ' (with credentials).' : '.'));
        return $result;
    }

    /**
     * Does an (unserialized) value contain an object, i.e. a __PHP_Incomplete_Class?
     *
     * @param mixed $value The value to check.
     * @return boolean True if an object is found.
     * @since 3.8.0
     */
    private static function contains_incomplete_object($value) {
        if (is_object($value)) {
            return true;
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                if (self::contains_incomplete_object($item)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Validate the whole structure of rows imported in a table of the plugin. Nothing is written.
     *
     * @param string $table The table name - without prefix.
     * @param mixed $rows The rows to validate.
     * @return boolean True if all rows are acceptable, false otherwise.
     * @since 3.8.0
     */
    public static function validate_table_rows($table, $rows) {
        if (!is_array($rows)) {
            return false;
        }
        $columns = self::get_table_columns($table);
        if (!is_array($columns)) {
            return false;
        }
        foreach ($rows as $row) {
            if (!is_array($row) || count($row) === 0) {
                return false;
            }
            foreach ($row as $key => $value) {
                if (self::resolve_column($key, $columns) === null) {
                    return false;
                }
                if (!is_scalar($value) && $value !== null) {
                    return false;
                }
            }
            if ($table === self::live_weather_station_maps_table() && isset($row['params']) && $row['params'] !== '') {
                // Map parameters are PHP serialized data: only plain arrays (no object) are acceptable.
                $params = (is_string($row['params']) ? @unserialize($row['params'], array('allowed_classes' => false)) : false);
                if (!is_array($params) || self::contains_incomplete_object($params)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Set maps table of the plugin - for restore purpose.
     * The whole structure is validated before the current table is replaced.
     *
     * @@param array $rows An array containing all rows of the maps table.
     * @return boolean True if the table has been replaced, false otherwise.
     * @since 3.8.0
     */
    public static function set_maps_table($rows) {
        global $wpdb;
        if (!self::validate_table_rows(self::live_weather_station_maps_table(), $rows)) {
            Logger::error('Core', null, null, null, null, null, 601, 'Maps table not imported: invalid data.');
            return false;
        }
        $table_name = $wpdb->prefix . self::live_weather_station_maps_table();
        $sql = 'TRUNCATE TABLE `' . $table_name . '`';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table maps (name from the internal ..._table() method, quoted with backticks), static TRUNCATE without external value, write operation so no caching
        $wpdb->query($sql);
        foreach ($rows as $row) {
            self::insert_update_table(self::live_weather_station_maps_table(), $row);
        }
        Logger::notice('Core', null, null, null, null, null, 601, 'Maps table successfully imported.');
        return true;
    }

    /**
     * Set modules table of the plugin - for restore purpose.
     * The whole structure is validated before the current table is replaced.
     *
     * @@param array $rows An array containing all rows of the modules table.
     * @return boolean True if the table has been replaced, false otherwise.
     * @since 3.8.0
     */
    public static function set_modules_table($rows) {
        global $wpdb;
        if (!self::validate_table_rows(self::live_weather_station_module_detail_table(), $rows)) {
            Logger::error('Core', null, null, null, null, null, 601, 'Modules table not imported: invalid data.');
            return false;
        }
        $table_name = $wpdb->prefix . self::live_weather_station_module_detail_table();
        $sql = 'TRUNCATE TABLE `' . $table_name . '`';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table module_detail (name from the internal ..._table() method, quoted with backticks), static TRUNCATE without external value, write operation so no caching
        $wpdb->query($sql);
        foreach ($rows as $row) {
            self::insert_update_table(self::live_weather_station_module_detail_table(), $row);
        }
        Logger::notice('Core', null, null, null, null, null, 601, 'Modules table successfully imported.');
        return true;
    }

    /**
     * Set stations table of the plugin - for restore purpose.
     * The whole structure is validated before the current table is replaced. Credentials which are missing
     * (or empty) in the imported rows are kept from the current stations having the same station_id.
     *
     * @@param array $rows An array containing all rows of the stations table.
     * @return boolean True if the table has been replaced, false otherwise.
     * @since 3.8.0
     */
    public static function set_stations_table($rows) {
        global $wpdb;
        if (!self::validate_table_rows(self::live_weather_station_stations_table(), $rows)) {
            Logger::error('Core', null, null, null, null, null, 601, 'Stations table not imported: invalid data.');
            return false;
        }
        $credentials = array();
        $service_ids = array();
        foreach (self::get_table(self::live_weather_station_stations_table()) as $current) {
            if (isset($current['station_id'])) {
                $credentials[$current['station_id']] = array_intersect_key($current, array_flip(self::stations_credential_columns()));
                $service_ids[$current['station_id']] = isset($current['service_id']) ? (string)$current['service_id'] : '';
            }
        }
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        $sql = 'TRUNCATE TABLE `' . $table_name . '`';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom plugin table stations (name from the internal ..._table() method, quoted with backticks), static TRUNCATE without external value, write operation so no caching
        $wpdb->query($sql);
        foreach ($rows as $row) {
            unset($row['last_refresh']);
            unset($row['last_seen']);
            unset($row['oldest_data']);
            if (isset($row['station_id']) && array_key_exists($row['station_id'], $credentials)) {
                foreach ($credentials[$row['station_id']] as $column => $value) {
                    if (!isset($row[$column]) || $row[$column] === '') {
                        $row[$column] = $value;
                    }
                }
            }
            if (isset($row['service_id'])) {
                $imported = (string)$row['service_id'];
                if (isset($row['station_id']) && array_key_exists($row['station_id'], $service_ids) && $service_ids[$row['station_id']] !== '' && self::strip_service_credentials($imported) === $imported && self::strip_service_credentials($service_ids[$row['station_id']]) === $imported) {
                    // Blanked credentials never overwrite the existing ones.
                    $row['service_id'] = $service_ids[$row['station_id']];
                }
                elseif (self::strip_service_credentials($imported) === $imported && strpos($imported, LIVE_WEATHER_STATION_SERVICE_SEPARATOR) !== false) {
                    Logger::warning('Core', null, isset($row['station_id']) ? $row['station_id'] : null, isset($row['station_name']) ? $row['station_name'] : null, null, null, 601, 'Station imported without its credentials: it must be reconnected.');
                }
            }
            self::insert_update_table(self::live_weather_station_stations_table(), $row);
        }
        Logger::notice('Core', null, null, null, null, null, 601, 'Stations table successfully imported.');
        return true;
    }

}
