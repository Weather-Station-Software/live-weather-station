<?php

namespace WeatherStation\System\Quota;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;

/**
 * The class to manage API and files quotas.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */
class Quota {

    use Storage;

    private $Live_Weather_Station;
    private $version;
    private static $facility = 'Quota Manager';
    private static $stats = array();



    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.2.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Verify if it's ok to query *verb* on *service*.
     *
     * @param string $service The service to query.
     * @param string $verb The verb to apply (GET, POST, PUT, etc.).
     * @return boolean True if it's ok to query, false otherwise.
     *
     * @since 3.2.0
     */
    public static function verify($service, $verb){
        //TODO: intelligence here ;)
        $quota = true;

        if ($quota) {
            $verb = strtolower($verb);
            if (!array_key_exists($service, self::$stats)) {
                self::$stats[$service][$verb] = 1;
            }
            else {
                if (!array_key_exists($verb, self::$stats[$service])) {
                    self::$stats[$service][$verb] = 1;
                }
                else {
                    self::$stats[$service][$verb] += 1;
                }
            }
        }
        else {
            Logger::info(self::$facility, null, null, null, null, null, 0, sprintf('Quota manager has denied %s verb on %s.', strtoupper($verb), $service));
        }
        return $quota;
    }

    /**
     * Get the 24 hours quota.
     *
     * @param string $service The service to query.
     * @param string $verb The verb to apply (GET, POST, PUT, etc.).
     * @return integer The value of the quota or 0 if there is no quota.
     *
     * @since 3.2.0
     */
    public static function get_count_quota($service, $verb){
        $quota = 0;
        $verb = strtoupper($verb);
        switch ($service) {
            case 'Netatmo':
                switch ($verb) {
                    case 'GET' :
                        $quota = 12000;
                        break;
                }
                break;
            case 'Weather Underground':
                switch ($verb) {
                    case 'GET' :
                        switch (get_option('live_weather_station_wug_plan', 0)) {
                            case 0:
                            case 4:
                            case 8:
                                $quota = 500;
                                break;
                            case 1:
                            case 5:
                            case 9:
                                $quota = 5000;
                                break;
                            case 2:
                            case 6:
                            case 10:
                                $quota = 100000;
                                break;
                            case 3:
                            case 7:
                            case 11:
                                $quota = 1000000;
                                break;

                        }
                        break;
                }
                break;
            }
        return $quota;
    }

    /**
     * Get the rate quota (queries by minutes).
     *
     * @param string $service The service to query.
     * @param string $verb The verb to apply (GET, POST, PUT, etc.).
     * @return integer The value of the quota or 0 if there is no quota.
     *
     * @since 3.2.0
     */
    public static function get_rate_quota($service, $verb){
        $quota = 0;
        $verb = strtoupper($verb);
        switch ($service) {
            case 'Weather Underground':
                switch ($verb) {
                    case 'GET' :
                        switch (get_option('live_weather_station_wug_plan', 0)) {
                            case 0:
                            case 4:
                            case 8:
                                $quota = 10;
                                break;
                            case 1:
                            case 5:
                            case 9:
                                $quota = 100;
                                break;
                            case 2:
                            case 6:
                            case 10:
                                $quota = 1000;
                                break;
                            case 3:
                            case 7:
                            case 11:
                                $quota = 10000;
                                break;

                        }
                        break;
                    case 'POST' :
                        $quota = 24;
                        break;
                }
                break;
            case 'PWS Weather':
                switch ($verb) {
                    case 'POST' :
                        $quota = 1;
                        break;
                }
                break;
            case 'WOW Met Office':
                switch ($verb) {
                    case 'POST' :
                        $quota = 1;
                        break;
                }
                break;
            case 'OpenWeatherMap':
                switch ($verb) {
                    case 'GET' :
                        switch (get_option('live_weather_station_owm_plan', 0)) {
                            case 0:
                                $quota = 60;
                                break;
                            case 1:
                                $quota = 600;
                                break;
                            case 2:
                                $quota = 3000;
                                break;
                            case 3:
                                $quota = 30000;
                                break;
                            case 4:
                                $quota = 200000;
                                break;

                        }
                        break;
                }
                break;
            case 'Netatmo':
                switch ($verb) {
                    case 'GET' :
                        $quota = 300;
                        break;
                }
                break;
            case 'ip-API':
                switch ($verb) {
                    case 'GET' :
                        $quota = 150;
                        break;
                }
                break;
        }
        return $quota;
    }

    /**
     * Write cache stats.
     *
     * @since 3.2.0
     */
    public static function write_stats(){
        $now = date('Y-m-d H:i');
        $now = substr($now, 0, strlen($now)-1);
        $now .= '0:00';
        global $wpdb;
        foreach (self::$stats as $key => $values) {
            $field_insert = array('timestamp', 'service');
            $value_insert = array();
            $value_update = array();
            $value_insert[] = "'".$now."'";
            $value_insert[] = "'".$key."'";
            foreach ($values as $k => $v) {
                $field_insert[] = $k;
                $value_insert[] = $v;
                $value_update[] = $k.'='.$k.'+'.$v;
            }
            $sql = "INSERT INTO " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " ";
            $sql .= "(" . implode(',', $field_insert) . ") ";
            $sql .= "VALUES (" . implode(',', $value_insert) . ") ";
            $sql .= "ON DUPLICATE KEY UPDATE " . implode(',', $value_update) . ";";
            $wpdb->query($sql);
        }
    }

    /**
     * Compile daily statistics.
     *
     * @since 3.2.0
     */
    private static function compile() {
        $verbs = array('post', 'get', 'put', 'patch', 'delete');
        $idx = array('sum', 'max');
        $fields = array();
        foreach ($idx as $id) {
            foreach ($verbs as $verb) {
                $fields[] = $id . '(`' . $verb . '`) as ' . $id . '_' . $verb;
            }
        }
        $select = "service, " . implode(',', $fields);
        global $wpdb;
        $time = time();
        for ($i=0; $i<3; $i++) {
            $time_min = date('Y-m-d', $time - $i * DAY_IN_SECONDS) . ' 00:00:00';
            $time_max = date('Y-m-d', $time - $i * DAY_IN_SECONDS) . ' 23:59:59';
            $where = "timestamp>='" . $time_min . "' AND timestamp<='" . $time_max . "'";
            $sql = "SELECT " . $select . " FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " WHERE ";
            $sql .= $where . " GROUP BY service;";
            try {
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                foreach ($query_a as $val) {
                    $detail = (array)$val;
                    $replace = array();
                    $replace[] = "'" . $time_min . "'";
                    $replace[] = "'" . $detail['service'] . "'";
                    foreach ($verbs as $verb) {
                        $replace[] = $detail['sum_'.$verb];
                        $rate = $detail['max_'.$verb] / 10;
                        if ($detail['max_'.$verb] % 10 > 0) {
                            $rate += 1;
                        }
                        $replace[] = $rate;
                        $replace[] = self::get_count_quota($detail['service'], $verb);
                        $replace[] = self::get_rate_quota($detail['service'], $verb);
                    }
                    $req = "REPLACE INTO " . $wpdb->prefix.self::live_weather_station_quota_year_table() . " VALUES (" . implode(',', $replace) . ");";
                    $wpdb->query($req);
                }
            } catch (\Exception $ex) {
                //
            }
        }
    }

    /**
     * Delete old records.
     *
     * @since 3.2.0
     */
    public static function rotate() {
        self::compile();
        global $wpdb;
        $now = date('Y-m-d', time() - 4 * DAY_IN_SECONDS) . ' 00:00:00';
        $sql = "DELETE FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " WHERE ";
        $sql .= "timestamp<'" . $now . "';";
        $wpdb->query($sql);
        $now = date('Y-m-d', time() - YEAR_IN_SECONDS - DAY_IN_SECONDS) . ' 00:00:00';
        $sql = "DELETE FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table() . " WHERE ";
        $sql .= "timestamp<'" . $now . "';";
        $wpdb->query($sql);
    }
}