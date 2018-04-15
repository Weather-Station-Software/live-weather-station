<?php

namespace WeatherStation\System\Quota;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Cache\Cache;

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
    private static $ratio_threshold = 0.2;



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
     * Get the actual consumption of the *verb* for *service*.
     *
     * @param string $service The service to query.
     * @param string $verb The verb to apply (GET, POST, PUT, etc.).
     * @return array The values of the verb for the specific service.
     *
     * @since 3.2.0
     */
    private static function get_actual($service, $verb){
        $verbs = array('post', 'get', 'put', 'patch', 'delete');
        $modes = array('rolling', 'strict');
        $result = array();
        foreach ($modes as $mode) {
            $result[$mode] = 0;
        }
        $values = Cache::get_backend(Cache::$db_stat_quota);
        if (!$values) {
            $values = array();
            global $wpdb;
            $fields = array();
            foreach ($verbs as $v) {
                    $fields[] = 'sum(`' . $v . '`) as ' . 'sum_' . $v;
            }
            $cutoff['rolling'] = date('Y-m-d H:i:s',time() - (DAY_IN_SECONDS));
            $cutoff['strict'] = date('Y-m-d',time()) . ' 00:00:00';
            foreach ($modes as $mode) {
                $select = "service, " . implode(', ', $fields);
                $where = "timestamp>='" . $cutoff[$mode] . "'";
                $sql = "SELECT " . $select . " FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " WHERE ";
                $sql .= $where . " GROUP BY service;";
                try {
                    $query = (array)$wpdb->get_results($sql);
                    $query_a = (array)$query;
                    foreach ($query_a as $val) {
                        $detail = (array)$val;
                        foreach ($verbs as $v) {
                            $values[$mode][$detail['service']][$v] = $detail['sum_'.$v];
                        }
                    }
                } catch (\Exception $ex) {
                    $values = array();
                }
            }
            Cache::set_backend(Cache::$db_stat_quota, $values);
        }
        foreach ($modes as $mode) {
            if (array_key_exists($mode, $values)) {
                if (array_key_exists($service, $values[$mode])) {
                    if (array_key_exists($verb, $values[$mode][$service])) {
                        $result[$mode] = $values[$mode][$service][$verb];
                    }
                }
            }
        }
        return $result;
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
        $verified = true;
        $verb = strtolower($verb);
        $quota = self::get_count_quota($service, $verb);
        $mode = get_option('live_weather_station_quota_mode');
        if ($quota != 0 && $mode != 0) {
            $values = self::get_actual($service, $verb);
            $verb = strtolower($verb);
            $delta = 0;
            $admitted = 100;
            if (array_key_exists($service, self::$stats)) {
                if (array_key_exists($verb, self::$stats[$service])) {
                    $delta = self::$stats[$service][$verb];
                }
            }
            $max_potential_consumption = $values['rolling'] * Cache::$backend_expiry / DAY_IN_SECONDS;
            $actual_rolling = round($values['rolling'] + $max_potential_consumption + $delta, 0) + 1;
            $warning = ($actual_rolling > $quota);
            $error = ($actual_rolling > $quota);
            $d1 = new \DateTime(date('Y-m-d H:i:s',time()));
            $d2 = new \DateTime(date('Y-m-d',time()) . ' 00:00:00');
            $t = $d2->diff($d1, true);
            $diff = $t->s + MINUTE_IN_SECONDS * $t->i + HOUR_IN_SECONDS * $t->h;
            $ratio = $diff / DAY_IN_SECONDS;
            $actual_strict = 0;
            if ($ratio > self::$ratio_threshold) {
                $max_potential_consumption = $values['strict'] * Cache::$backend_expiry / $diff;
                $actual_strict = round($values['strict'] + $max_potential_consumption + $delta, 0) + 1;
                $full_strict = round($actual_strict / $ratio, 0);
                $warning = ($full_strict > $quota);
                $error = ($actual_strict > $quota);
                if ($full_strict > $quota) {
                    $remaining = $quota - $actual_strict;
                    $projected = $full_strict - $actual_strict;
                    $admitted = 100 * $remaining / $projected;
                    $s = 'diff='.$diff.' / ';
                    $s .= 'ratio='.$ratio.' / ';
                    $s .= 'actual_strict='.$actual_strict.' / ';
                    $s .= 'full_strict='.$full_strict.' / ';
                    $s .= 'remaining='.$remaining.' / ';
                    $s .= 'projected='.$projected.' / ';
                    $s .= 'admitted='.$admitted;
                    Logger::debug(self::$facility, $service, null, null, null, null, null, $s);
                }
            }
            switch ($mode) {
                case 1:
                    if ($error) {
                        set_transient('live_weather_station_quota_alert', 2, 660);
                        Logger::error(self::$facility, $service, null, null, null, null, 105, sprintf('%s API usage has exceeded quotas.', strtoupper($verb)));
                    }
                    elseif ($warning) {
                        set_transient('live_weather_station_quota_alert', 1, 660);
                        Logger::warning(self::$facility, $service, null, null, null, null, 106, sprintf('%s API usage will soon exceed quotas.', strtoupper($verb)));
                    }
                    break;
                case 2:
                    $verified = ($actual_rolling < $quota);
                    break;
                case 3:
                    $verified = (rand(0, 100) <= $admitted);
                    break;
                default:
            }
            Logger::debug(self::$facility, $service, null, null, null, null, 104, sprintf('%s API call usage was %s (rolling) and %s (strict).', strtoupper($verb), $actual_rolling, $actual_strict));
        }
        if ($verified) {
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
            Logger::warning(self::$facility, $service, null, null, null, null, 104, sprintf('%s API call denied.', strtoupper($verb)));
        }
        return $verified;
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
                        $quota = 0.07;
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
            case 'Pioupiou':
                switch ($verb) {
                    case 'GET' :
                        $quota = 10;
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
        $err_bup = $wpdb->show_errors(false);
        foreach (self::$stats as $key => $values) {
            $field_insert = array('timestamp', 'service');
            $value_insert = array();
            $value_update = array();
            $value_insert[] = "'".$now."'";
            $value_insert[] = "'".$key."'";
            foreach ($values as $k => $v) {
                $field_insert[] = '`'.$k.'`';
                $value_insert[] = $v;
                $value_update[] = '`'.$k.'`=`'.$k.'`+'.$v;
            }
            $sql = "INSERT INTO " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " ";
            $sql .= "(" . implode(',', $field_insert) . ") ";
            $sql .= "VALUES (" . implode(',', $value_insert) . ") ";
            $sql .= "ON DUPLICATE KEY UPDATE " . implode(',', $value_update) . ";";
            $wpdb->query($sql);
        }
        $wpdb->show_errors($err_bup);
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