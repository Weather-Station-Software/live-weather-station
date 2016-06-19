<?php

/**
 * Data queries functionalities for Live Weather Station plugin
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-storage.php');

trait Datas_Query {
    
    use Datas_Storage;

    private $dont_filter = array('temperature_max', 'temperature_min', 'temperature_trend', 'pressure_trend', 'loc_latitude',
                                 'loc_longitude', 'loc_altitude', 'windstrength_hour_max', 'windstrength_day_max', 'windangle_hour_max', 'windangle_day_max');

    /**
     * Filter data.
     *
     * @param   array   $data   The data to filter.
     * @return  array   An array containing the filtered data.
     * @since    2.0.0
     * @access   protected
     */
    private function obsolescence_filtering($data) {
        $time = 0;
        $time_owm = 0;
        switch (get_option('live_weather_station_settings')[6]) {
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
     * Get stations list.
     *
     * @return  array   An array containing the available stations.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_stations_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(Exception $ex) {
            return array('device_name' => __(LWS_PLUGIN_NAME, 'live-weather-station').' '.__('is not running...', 'live-weather-station'), 'device_id' => 'N/A') ;
        }
    }

    /**
     * Get station informations.
     *
     * @param   integer $station_id     The station id.
     * @return  array   An array containing the station informations.
     * @since    2.3.0
     */
    protected function get_station_informations($station_id=0) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_infos_table();
        $sql = "SELECT * FROM " . $table_name . " WHERE station_id='" . $station_id."'";
        try {
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
        } catch (Exception $ex) {
            return array();
        }
    }

    /**
     * Get stations informations.
     *
     * @return  array   An array containing the stations informations.
     * @since    2.3.0
     */
    protected function get_stations_informations() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_infos_table();
        $sql = "SELECT * FROM " . $table_name ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        } catch (Exception $ex) {
            return array();
        }
    }

    /**
     * Get the name of a station in infos table.
     *
     * @param   string  $station_id  The station ID.
     * @return  string  The name of the station.
     * @since    2.5.0
     * @access   protected
     */
    protected function get_infos_station_name($station_id) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_infos_table();
        $sql = "SELECT DISTINCT station_name FROM ".$table_name. " WHERE station_id='".$station_id."'" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = (array)$query_a[0];
            $result = $query_t['station_name'];
            return $result;
        }
        catch(Exception $ex) {
            return '';
        }
    }

    /**
     * Verify if a station has a name in info station table.
     *
     * @param   string   $station_id  The id of the station to insert in the table
     * @param   string   $station_name  The name of the station to insert in the table
     * @since    2.5.0
     */
    protected function verify_infos_table($station_id, $station_name) {
        $station = $this->get_station_informations($station_id);
        if ($station['station_name'] == '') {
            $station['station_name'] = $station_name;
            $this->update_infos_table($station);
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
        $table_name = $wpdb->prefix.self::live_weather_station_owm_stations_table();
        $sql = "SELECT * FROM ".$table_name ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(Exception $ex) {
            return array() ;
        }
    }

    /**
     * Get "where" clause for log table.
     *
     * @param   array   $filters    Optional. An array of filters.
     * @return  string   The "where" clause.
     * @since    3.0.0
     */
    private function get_log_where_clause($filters = array()) {
        $result = '';
        if (count($filters) > 0) {
            $w = array();
            foreach ($filters as $key => $filter) {
                if ($key == 'level') {
                    $l = array();
                    foreach (Logger::$severity as $sev => $severity) {
                        if ($severity <= Logger::$severity[$filter]) {
                            $l[] = "'".$sev."'";
                        }
                    }
                    $w[] = $key . ' IN (' . implode(',', $l) . ')';
                }
                else {
                    $w[] = $key . '="' . $filter . '"';
                }
            }
            $result = 'WHERE (' . implode(' AND ', $w) . ')';
        }
        return $result;
    }

    /**
     * Get list of logged errors.
     *
     * @param   array   $filters    Optional. An array of filters.
     * @return  array   An array containing the filtered logged errors.
     * @since    3.0.0
     */
    protected function get_log_list($filters = array(), $offset = null, $rowcount = null) {
        $limit = '';
        if (!is_null($offset) && !is_null($rowcount)) {
            $limit = 'LIMIT ' . $offset . ',' . $rowcount;
        }
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT * FROM " . $table_name . " " . $this->get_log_where_clause($filters) . " ORDER BY id DESC " . $limit;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(Exception $ex) {
            return array() ;
        }
    }

    /**
     * Count logged errors.
     *
     * @param   array   $filters    Optional. An array of filters.
     * @return  integer   The count of the filtered logged errors.
     * @since    3.0.0
     */
    protected function get_log_count($filters = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT COUNT(*) FROM " . $table_name . " " . $this->get_log_where_clause($filters);
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = (array)$query_a[0];
            $result = $query_t['COUNT(*)'];
            return $result;
        }
        catch(Exception $ex) {
            return 0;
        }
    }

    /**
     * Get a specific log line.
     *
     * @param   integer   $log_entry    The specific log entry to load.
     * @return  array   An array containing the filtered logged errors.
     * @since    3.0.0
     */
    protected function get_log_detail($log_entry) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT * FROM " . $table_name . " WHERE id=" . $log_entry ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(Exception $ex) {
            return array() ;
        }
    }

    /**
     * Get an OpenWeatherMap station.
     *
     * @param   integer $station_id     Optional. The station id.
     * @return  array   An array containing the station details.
     * @since    2.0.0
     */
    protected function get_owm_station($station_id=0) {
        if ($station_id == 0) {
            $ccs = '';
            $cc = explode ('_', get_locale());
            if (count($cc) > 1) {
                $ccs = strtoupper($cc[1][0].$cc[1][1]);
            }
            $nothing = array();
            $nothing['station_id'] = 0;
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
            $table_name = $wpdb->prefix . self::live_weather_station_owm_stations_table();
            $sql = "SELECT * FROM " . $table_name . " WHERE station_id=" . $station_id;
            try {
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return $result[0];
            } catch (Exception $ex) {
                return array();
            }
        }
    }

    /**
     * Get an OpenWeatherMap station list.
     *
     * @param   array   $station_id     The array of stations id.
     * @return  array   An array containing the stations details.
     * @since    2.0.0
     */
    protected function get_owm_stations($station_id) {
        if (count($station_id) > 0) {
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_owm_stations_table();
            $sql = "SELECT * FROM " . $table_name . " WHERE station_id IN (".implode(',', $station_id).')';
            try {
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                $result = array();
                foreach ($query_a as $val) {
                    $result[] = (array)$val;
                }
                return $result;
            } catch (Exception $ex) {
                return array();
            }
        }
        else {
            return array();
        }
    }

    /**
     * Get a list of all OpenWeatherMap stations.
     *
     * @return  array   An array containing the details of all stations.
     * @since    2.0.0
     */
    protected function get_all_owm_stations() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_owm_stations_table();
        $sql = "SELECT * FROM ".$table_name;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        } catch (Exception $ex) {
            return array();
        }
    }

    /**
     * Get outdoor datas.
     *
     * @param   string      $device_id                  The device ID.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @param   boolean     $strict_filtering           Don't return not necessary data.
     * @return  array   An array containing the outdoor datas.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_outdoor_datas($device_id, $obsolescence_filtering=false, $strict_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT * FROM ".$table_name. " WHERE device_id='".$device_id."' AND (module_type='NAMain' OR module_type='NAComputed' " . ($strict_filtering ? "OR module_type='NAEphemer' " : "OR module_type='NACurrent' ") . "OR module_type='NAModule1' OR module_type='NAModule2' OR module_type='NAModule3') ORDER BY module_id ASC" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent datas', 'live-weather-station')));
        }
    }

    /**
     * Get pollution datas.
     *
     * @param   string      $device_id                  The device ID.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @return  array   An array containing the pollution datas.
     * @since    2.7.0
     * @access   protected
     */
    protected function get_pollution_datas($device_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT * FROM ".$table_name. " WHERE device_id='".$device_id."' AND (module_type='NAPollution')" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent datas', 'live-weather-station')));
        }
    }

    /**
     * Get ephemeris datas.
     *
     * @param   string  $device_id  The device ID.
     * @return  array   An array containing the ephemeris datas.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_ephemeris_datas($device_id) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT * FROM ".$table_name. " WHERE device_id='".$device_id."' AND (module_type='NAMain' OR module_type='NAEphemer') ORDER BY module_id ASC" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return $result;
        }
        catch(Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent datas', 'live-weather-station')));
        }
    }

    /**
     * Get all datas for a single station.
     *
     * @param   string  $device_id  The device ID.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @return array An array containing all the datas.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_all_datas($device_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT * FROM ".$table_name. " WHERE device_id='".$device_id."' ORDER BY module_id ASC" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch(Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent datas', 'live-weather-station')));
        }
    }

    /**
     * Get the name of a station.
     *
     * @param   string  $device_id  The device ID.
     * @return  string  The name of the station.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_station_name($device_id) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_name FROM ".$table_name. " WHERE device_id='".$device_id."'" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = (array)$query_a[0];
            $result = $query_t['device_name'];
            return $result;
        }
        catch(Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent datas', 'live-weather-station')));
        }
    }

    /**
     * Get id and names of all stations.
     *
     * @return  array  The id and name of all stations.
     * @since    3.0.0
     * @access   protected
     */
    protected function get_stations_array() {
        $result = array();
        $result['00:00:00:00:00:00'] = 'N/A';
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name . " ORDER BY device_name ASC";
        try {
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
        catch(Exception $ex) {
            return array();
        }
        return $result;
    }

    /**
     * Get all services names.
     *
     * @return  array  The names of all services.
     * @since    3.0.0
     * @access   protected
     */
    protected function get_services_array() {
        $result = array();
        $result['N/A'] = 'N/A';
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT DISTINCT service FROM ".$table_name . " ORDER BY service ASC";
        try {
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
        catch(Exception $ex) {
            return array();
        }
        return $result;
    }

    /**
     * Get all system names.
     *
     * @return  array  The names of all systems.
     * @since    3.0.0
     * @access   protected
     */
    protected function get_systems_array() {
        $result = array();
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT DISTINCT system FROM ".$table_name . " ORDER BY system ASC";
        try {
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
        catch(Exception $ex) {
            return array();
        }
        return $result;
    }

    /**
     * Get all datas for a single module.
     *
     * @param   string  $module_id  The module ID.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @return array An array containing all the datas.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_module_datas($module_id, $obsolescence_filtering=false) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT * FROM ".$table_name. " WHERE module_id='".$module_id."'" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering? $this->obsolescence_filtering($result) : $result);
        }
        catch(Exception $ex) {
            return array('condition' => array('value' => 2, 'message' => __('Database contains inconsistent datas', 'live-weather-station')));
        }
    }

    /**
     * Get sub attributes for some measure types.
     *
     * @param   array   $attributes  An array representing the query.
     * @param   boolean     $full_mode  For ful aggregated rendering.
     * @return array An array containing all the sub attributes + the attributes.
     * @since    2.1.0
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
            case 'temperature':
                $sub_attributes[] = 'temperature';
                $sub_attributes[] = 'temperature_min';
                $sub_attributes[] = 'temperature_max';
                if ($full_mode) {
                    $sub_attributes[] = 'temperature_trend';
                }
                break;
            case 'pressure':
                $sub_attributes[] = 'pressure';
                if ($full_mode) {
                    $sub_attributes[] = 'pressure_trend';
                }
                break;
            case 'windangle':
                $sub_attributes[] = 'windangle';
                if ($full_mode) {
                    $sub_attributes[] = 'gustangle';
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
        return $sub_attributes;
    }

    /**
     * Get specific lines.
     *
     * @param   array   $attributes  An array representing the query.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @param   boolean     $full_mode  For ful aggregated rendering.
     * @return array An array containing all the datas.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_line_datas($attributes, $obsolescence_filtering=false, $full_mode=false) {
        global $wpdb;
        $sub_attributes = $this->get_sub_attributes($attributes, $full_mode);
        $measures = "";
        if (count($sub_attributes)>0) {
            $i = 0;
            foreach ($sub_attributes as $att) {
                $measures = $measures . ($i!=0?" OR ":"")."measure_type='" . $att . "'";
                $i++;
            }
        }
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "SELECT * FROM " . $table_name . " WHERE device_id='" . $attributes['device_id'] . "' AND module_id='" . $attributes['module_id'] . "' AND (" . $measures . ")";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            return ($obsolescence_filtering? $this->obsolescence_filtering($result) : $result);
        }
        catch(Exception $ex) {
            return array();
        }
    }

    /**
     * Get specific data.
     *
     * @param   array   $attributes  An array representing the query.
     * @param   boolean     $obsolescence_filtering     Don't return obsolete data.
     * @return array An array containing all the datas.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_specific_datas($attributes, $obsolescence_filtering=false) {
        global $wpdb;
        $sub_attributes = $this->get_sub_attributes($attributes);
        $measures = "";
        if (count($sub_attributes)>0) {
            $i = 0;
            foreach ($sub_attributes as $att) {
                $measures = $measures . ($i!=0?" OR ":"")."measure_type='" . $att . "'";
                $i++;
            }
        }
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "SELECT " . $attributes['element'] . ", module_type" . ($attributes['element']!="measure_type"?", measure_type":"") . " FROM " . $table_name . " WHERE device_id='" . $attributes['device_id'] . "' AND module_id='" . $attributes['module_id'] . "' AND (" . $measures . ")";
        $result = array();
        try {
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
            //ToDo: correct obsolescence filtering for this type of array
            return ($obsolescence_filtering ? $this->obsolescence_filtering($result) : $result);
        }
        catch (Exception $ex) {
            return array();
        }
    }

    /**
     * Get stations list with latitude and longitude set.
     *
     * @return  array   An array containing the located stations.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_located_stations_list() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_id, device_name FROM ".$table_name ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
        }
        catch(Exception $ex) {
            $result = array() ;
        }
        $count = count ($result);
        $rq = '';
        foreach ($result as $res) {
            $count = $count - 1;
            $rq = $rq . "device_id='".$res['device_id']."'" ;
            if ($count > 0) {
                $rq = $rq . ' OR ';
            }
        }
        if ($rq != '') {
            $rq = " AND (".$rq.")" ;
        }
        $sql = "SELECT device_id, device_name, measure_type, measure_value FROM ".$table_name." WHERE (module_type='NAMain') AND (measure_type='loc_latitude' OR measure_type='loc_longitude' OR measure_type='loc_timezone')".$rq ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
        }
        catch(Exception $ex) {
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
     * @return  array   An array containing the stations with reference values.
     * @since    2.0.0
     */
    private function get_reference_values() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT device_id, device_name, module_type, measure_timestamp, measure_type, measure_value FROM ".$table_name." WHERE (module_type='NAModule1' OR module_type='NAModule2' OR module_type='NACurrent') AND (measure_type='temperature' OR measure_type='humidity' OR measure_type='windstrength')" ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $result = array();
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
        }
        catch(Exception $ex) {
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
            foreach ($device as $measure_type => $measure) {
                if (is_array($measure)) {
                    $value = -9999;
                    foreach ($measure as $module_type => $module) {
                        $value = $module['value'];
                        $diff = round ((abs( strtotime(get_date_from_gmt(date('Y-m-d H:i:s'))) - strtotime(get_date_from_gmt($module['timestamp']))))/60);
                        $ts = $module['timestamp'];
                        if ($measure_type == 'temperature' && $module_type == 'NAModule1' && ($diff < $this->delta_time)) {
                            break;
                        }
                        if ($measure_type == 'humidity' && $module_type == 'NAModule1' && ($diff < $this->delta_time)) {
                            break;
                        }
                        if ($measure_type == 'windstrength' && $module_type == 'NAModule2' && ($diff < $this->delta_time)) {
                            break;
                        }
                    }
                    $result[$device_id]['name'] = $device['device_name'];
                    $result[$device_id][$measure_type] = $value;
                }
            }
        }
        return $result;
    }
}