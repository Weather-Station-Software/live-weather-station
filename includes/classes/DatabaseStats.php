<?php

namespace WeatherStation\DB;

use WeatherStation\DB\Query;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;

/**
 * This class add features for dashboard building.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

class Stats
{

    use Query;

    /**
     * Count operational stations.
     *
     * @return integer The number of stations.
     * @since 3.0.0
     */
    private function count_operational_stations() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_id FROM ".$table_name ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            return (count($query_a));
        }
        catch(\Exception $ex) {
            return 0 ;
        }
    }

    /**
     * Count operational modules.
     *
     * @return integer The number of modules.
     * @since 3.0.0
     */
    private function count_operational_modules() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT module_id FROM ".$table_name ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            return (count($query_a));
        }
        catch(\Exception $ex) {
            return 0 ;
        }
    }

    /**
     * Count operational measures.
     *
     * @return integer The number of measures.
     * @since 3.0.0
     */
    private function count_operational_measures() {
        global $wpdb;
        $result = array();
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "SELECT COUNT(*) FROM ".$table_name . ";";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $result[] = (array)$val;
            }
            if (count($result) == 1) {
                if (array_key_exists('COUNT(*)', $result[0])) {
                    return ($result[0]['COUNT(*)']);
                }
                else {
                    return 0;
                }
            }
            else {
                return 0;
            }
        }
        catch(\Exception $ex) {
            return 0 ;
        }
    }

    /**
     * Get the stats for operational data.
     *
     * @return array The stats for operational data.
     * @since 3.0.0
     */
    public function get_operational() {
        if (false !== ( $result = Cache::get_backend(Cache::$db_stat_operational))) {
            return $result;
        }
        else {
            $value = array();
            $value['station'] = $this->count_operational_stations();
            $value['module'] = $this->count_operational_modules();
            $value['measure'] = $this->count_operational_measures();
            Cache::set_backend(Cache::$db_stat_operational, $value);
            return $value;
        }
    }

    /**
     * Count logged errors.
     *
     * @param integer $interval Optional. The historic range to select (in hours).
     * @return integer The count of the logged errors.
     * @since 3.0.0
     */
    private function count_log_errors($interval = 24) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT COUNT(*) FROM " . $table_name . " WHERE level IN ('emergency','alert','critical','error') AND (timestamp >= NOW() - INTERVAL " . $interval . " HOUR);";
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
     * Count logged emergency.
     *
     * @param integer $interval Optional. The historic range to select (in hours).
     * @return integer The count of the logged errors.
     * @since 3.0.0
     */
    private function count_log_emergency($interval = 72) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_log_table();
        $sql = "SELECT COUNT(*) FROM " . $table_name . " WHERE level IN ('emergency') AND (timestamp >= NOW() - INTERVAL " . $interval . " HOUR);";
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
     * Get the stats for log.
     *
     * @return array The stats for logs.
     * @since 3.0.0
     */
    public function get_log() {
        if (false !== ( $result = Cache::get_backend(Cache::$db_stat_log))) {
            return $result;
        }
        else {
            $value = array();
            $value['emergency'] = $this->count_log_emergency();
            $value['error'] = $this->count_log_errors();
            $value['recent_error'] = $this->count_log_errors(4);
            Cache::set_backend(Cache::$db_stat_log, $value);
            return $value;
        }
    }

}