<?php

namespace WeatherStation\System\Analytics;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Handling as Schedules;
use WeatherStation\System\Data\Data;

/**
 * The class to compute and maintain consistency of performance statistics.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Performance {

    use Schedules, Storage;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Analytics';


    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Delete old records.
     *
     * @since 3.1.0
     */
    public function rotate() {
        $cron_id = Watchdog::init_chrono(Watchdog::$stats_clean_name);
        Cache::rotate();
        Watchdog::rotate();
        Quota::rotate();
        Logger::notice($this->facility,null,null,null,null,null,null,'Performance data cleaned.');
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Write pending stats in database.
     *
     * @since 3.2.0
     */
    public static function store() {
        Cache::write_stats();
        Watchdog::write_stats();
        Quota::write_stats();
    }

    /**
     * Get all stats values for cache.
     *
     * @since 3.1.0
     */
    public static function get_cache_values() {
        if ($result = Cache::get_backend(Cache::$db_stat_perf_cache)) {
            return $result;
        }
        $fields = array('backend', 'frontend', 'widget', 'dgraph', 'ygraph');
        $dimensions = array('miss', 'hit');
        $field_names = array('backend' => __('backend', 'live-weather-station'),
                            'frontend' => __('control', 'live-weather-station'),
                            'widget' => __('widget', 'live-weather-station'),
                            'dgraph' => __('daily graph', 'live-weather-station'),
                            'ygraph' => __('historical graph', 'live-weather-station'));
        $dimension_names = array('miss' => __('miss', 'live-weather-station'), 'hit' => __('hit', 'live-weather-station'));
        $metrics = array('count', 'time');
        $aggregates = array('efficiency', 'time_saving');
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix.Cache::live_weather_station_performance_cache_table() . " ;";
        $cutoff = time() - (get_option('live_weather_station_analytics_cutoff', 7)*DAY_IN_SECONDS);
        $cutoff24 = time() - (DAY_IN_SECONDS);
        $cutoff30 = time() - (30*DAY_IN_SECONDS);
        $sum24 = array();
        $agr24 = array();
        $sum30 = array();
        $agr30 = array();
        $jsonable = array();
        foreach ($fields as $field) {
            foreach ($aggregates as $aggregate) {
                $jsonable[$field . '_' . $aggregate] = array();
            }
            foreach ($dimensions as $dimension) {
                foreach ($metrics as $metric) {
                    $jsonable[$field . '_' . $dimension . '_' . $metric] = array();
                }
            }
        }
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $subresult = array();
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $subresult[] = $detail;
                if (strtotime($detail['timestamp']) > $cutoff24) {
                    foreach ($detail as $key=>$cpt) {
                        if ($key != 'timestamp') {
                            if (array_key_exists($key, $sum24)) {
                                $sum24[$key] += $cpt;
                            } else {
                                $sum24[$key] = $cpt;
                            }
                        }
                    }
                }
                if (strtotime($detail['timestamp']) > $cutoff30) {
                    foreach ($detail as $key=>$cpt) {
                        if ($key != 'timestamp') {
                            if (array_key_exists($key, $sum30)) {
                                $sum30[$key] += $cpt;
                            } else {
                                $sum30[$key] = $cpt;
                            }
                        }
                    }
                }
                if (strtotime($detail['timestamp']) > $cutoff) {
                    $datetime = new \DateTime($detail['timestamp']);
                    $time = $datetime->getTimestamp() . '000';
                    foreach ($fields as $field) {
                        $eff = -1;
                        $tim = -1;
                        if ($detail[$field . '_hit_count'] + $detail[$field . '_miss_count'] > 0) {
                            $val = $detail[$field . '_hit_count'] / ($detail[$field . '_hit_count'] + $detail[$field . '_miss_count']);
                            $jsonable[$field . '_efficiency'][] = array($time, $val);
                            $eff = round($val * 100, 0);
                        } else {
                            $jsonable[$field . '_efficiency'][] = array($time, 0);
                        }
                        if ($detail[$field . '_hit_count'] > 0 && $detail[$field . '_miss_count'] > 0) {
                            $avr_hit = $detail[$field . '_hit_time'] / $detail[$field . '_hit_count'];
                            $avr_miss = $detail[$field . '_miss_time'] / $detail[$field . '_miss_count'];
                            $val = ($avr_miss - $avr_hit) * $detail[$field . '_hit_count'];
                            $jsonable[$field . '_time_saving'][] = array($time, $val);
                            $tim = round($avr_miss - $avr_hit, 0) * $detail[$field . '_hit_count'];
                        } else {
                            $jsonable[$field . '_time_saving'][] = array($time, 0);
                        }
                        foreach ($dimensions as $dimension) {
                            foreach ($metrics as $metric) {
                                $val = $detail[$field . '_' . $dimension . '_' . $metric];
                                if ($metric == 'time') {
                                    $val = 1;
                                    if ($detail[$field . '_' . $dimension . '_count'] > 0) {
                                        $val = round($detail[$field . '_' . $dimension . '_' . $metric] / $detail[$field . '_' . $dimension . '_count'], 0);
                                        if ($val < 1) {
                                            $val = 1;
                                        }
                                    }
                                }
                                $jsonable[$field . '_' . $dimension . '_' . $metric][] = array($time, $val);
                            }
                        }
                    }
                }
            }
            $jsoned = array();
            $data_r = array();
            foreach ($fields as $field) {
                foreach ($aggregates as $aggregate) {
                    $jsoned[$field . '_' . $aggregate] = json_encode($jsonable[$field . '_' . $aggregate]);
                    $jsoned[$field . '_' . $aggregate] = str_replace('"', '', $jsoned[$field . '_' . $aggregate]);
                    $name = $field_names[$field];
                    $data_r[$aggregate][] = '{"key":"' . $name . '", "values":' . $jsoned[$field . '_' . $aggregate] . '}';
                }
                foreach ($dimensions as $dimension) {
                    foreach ($metrics as $metric) {
                        $jsoned[$field.'_'.$dimension.'_'.$metric] = json_encode($jsonable[$field.'_'.$dimension.'_'.$metric]);
                        $jsoned[$field.'_'.$dimension.'_'.$metric] = str_replace('"', '', $jsoned[$field.'_'.$dimension.'_'.$metric]);
                        $name = $field_names[$field] . ' / ' . $dimension_names[$dimension];
                        $data_r[$metric][] = '{"key":"' . $name . '", "values":'.$jsoned[$field.'_'.$dimension.'_'.$metric].'}';
                    }
                }
            }
            $data = array();
            foreach ($metrics as $metric) {
                $data[$metric] = '[' . implode(',', $data_r[$metric]) . ']';
            }
            foreach ($aggregates as $aggregate) {
                $data[$aggregate] = '[' . implode(',', $data_r[$aggregate]) . ']';
            }
            foreach ($fields as $field) {
                if (count($sum24) > 0) {
                    if (($sum24[$field . '_hit_count'] + $sum24[$field . '_miss_count']) > 0) {
                        $agr24[$field . '_success'] = round((100 * $sum24[$field . '_hit_count'] / ($sum24[$field . '_hit_count'] + $sum24[$field . '_miss_count'])), 1);
                        if ($sum24[$field . '_hit_count'] > 0 && $sum24[$field . '_miss_count'] > 0) {
                            $avr_hit = round($sum24[$field . '_hit_time'] / $sum24[$field . '_hit_count'], 0);
                            if ($avr_hit < 1) {
                                $avr_hit = 1;
                            }
                            $avr_miss = round($sum24[$field . '_miss_time'] / $sum24[$field . '_miss_count'], 0);
                            if ($avr_miss < 1) {
                                $avr_miss = 1;
                            }
                            $agr24[$field . '_time_saving'] = $avr_miss - $avr_hit;
                        } else {
                            $agr24[$field . '_time_saving'] = 0;
                        }
                    } else {
                        $agr24[$field . '_success'] = 0;
                        $agr24[$field . '_time_saving'] = 0;
                    }
                } else {
                    $agr24[$field . '_success'] = 0;
                    $agr24[$field . '_time_saving'] = 0;
                }
                if (count($sum30) > 0) {
                    if (($sum30[$field . '_hit_count'] + $sum30[$field . '_miss_count']) > 0) {
                        $agr30[$field . '_success'] = round((100 * $sum30[$field . '_hit_count'] / ($sum30[$field . '_hit_count'] + $sum30[$field . '_miss_count'])), 1);
                        if ($sum30[$field . '_hit_count'] > 0 && $sum30[$field . '_miss_count'] > 0) {
                            $avr_hit = round($sum30[$field . '_hit_time'] / $sum30[$field . '_hit_count'], 0);
                            if ($avr_hit < 1) {
                                $avr_hit = 1;
                            }
                            $avr_miss = round($sum30[$field . '_miss_time'] / $sum30[$field . '_miss_count'], 0);
                            if ($avr_miss < 1) {
                                $avr_miss = 1;
                            }
                            $agr30[$field . '_time_saving'] = $avr_miss - $avr_hit;
                        } else {
                            $agr30[$field . '_time_saving'] = 0;
                        }
                    } else {
                        $agr30[$field . '_success'] = 0;
                        $agr30[$field . '_time_saving'] = 0;
                    }
                } else {
                    $agr30[$field . '_success'] = 0;
                    $agr30[$field . '_time_saving'] = 0;
                }
            }
            $result = array('agr24' => $agr24, 'agr30' => $agr30, 'dat' => $data);
            Cache::set_backend(Cache::$db_stat_perf_cache, $result);
        }
        catch(\Exception $ex) {
            foreach ($fields as $field) {
                foreach ($dimensions as $dimension) {
                    foreach ($metrics as $metric) {
                        $agr24[$field . '_' . $dimension . '_' . $metric] = 0;
                        $agr30[$field . '_' . $dimension . '_' . $metric] = 0;
                    }
                }
                $agr24[$field . '_success'] = 0;
                $agr24[$field . '_time_saving'] = 0;
                $agr30[$field . '_success'] = 0;
                $agr30[$field . '_time_saving'] = 0;
            }
            $data = array();
            foreach ($metrics as $metric) {
                $data[$metric] = '[]';
            }
            foreach ($aggregates as $aggregate) {
                $data[$aggregate] = '[]';
            }
            $result = array('agr24' => $agr24, 'agr30' => $agr30, 'dat' => $data);
        }
        return $result;
    }
    

    /**
     * Get all stats values for croned tasks.
     *
     * @since 3.2.0
     */
    public static function get_cron_values() {
        if ($result = Cache::get_backend(Cache::$db_stat_perf_cron)) {
            return $result;
        }
        $fields = array('system', 'push', 'pull', 'history');
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix.Cache::live_weather_station_performance_cron_table() . " ;";
        $cutoff = time() - (get_option('live_weather_station_analytics_cutoff', 7)*DAY_IN_SECONDS);
        $cutoff24 = time() - (DAY_IN_SECONDS);
        $cutoff30 = time() - (30*DAY_IN_SECONDS);
        $sum24 = array();
        $sum30 = array();
        $values = array();
        $raw = array();
        $jsonable = array();
        $jsoned = array();
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $cron = $detail['cron'];
                $time = $detail['time'];
                $count = $detail['count'];
                $pool = self::get_cron_pool($cron);
                if (strtotime($detail['timestamp']) > $cutoff24) {
                    if (array_key_exists($pool, $sum24)) {
                        $sum24[$pool]['time'] += $time;
                        $sum24[$pool]['count'] += $count;
                    } else {
                        $sum24[$pool]['time'] = $time;
                        $sum24[$pool]['count'] = $count;
                    }
                }
                if (strtotime($detail['timestamp']) > $cutoff30) {
                    if (array_key_exists($pool, $sum30)) {
                        $sum30[$pool]['time'] += $time;
                        $sum30[$pool]['count'] += $count;
                    } else {
                        $sum30[$pool]['time'] = $time;
                        $sum30[$pool]['count'] = $count;
                    }
                    if (array_key_exists($cron, $raw)) {
                        $raw[$cron]['time'] += $time;
                        $raw[$cron]['count'] += $count;
                    } else {
                        $raw[$cron]['time'] = $time;
                        $raw[$cron]['count'] = $count;
                    }
                }
                if (strtotime($detail['timestamp']) > $cutoff) {
                    $datetime = new \DateTime($detail['timestamp']);
                    $ts = $datetime->getTimestamp() . '000';
                    if (!isset($values[$ts]['pools'][$pool])) {
                        $values[$ts]['pools'][$pool]['time'] = 0;
                        $values[$ts]['pools'][$pool]['count'] = 0;
                    }
                    $values[$ts]['pools'][$pool]['time'] += $time;
                    $values[$ts]['pools'][$pool]['count'] += $count;
                    if (!isset($values[$ts]['crons'][$cron])) {
                        $values[$ts]['crons'][$cron]['time'] = 0;
                        $values[$ts]['crons'][$cron]['count'] = 0;
                    }
                    $values[$ts]['crons'][$cron]['time'] += $time;
                    $values[$ts]['crons'][$cron]['count'] += $count;
                }
            }
            foreach ($fields as $field) {
                if (count($sum24) > 0) {
                    if (array_key_exists($field, $sum24)) {
                        $sum24[$field]['avr_time'] = round ($sum24[$field]['time'] / $sum24[$field]['count'], 0);
                    } else {
                        $sum24[$field]['time'] = 0;
                        $sum24[$field]['count'] = 0;
                        $sum24[$field]['avr_time'] = 0;
                    }
                    
                } else {
                    $sum24[$field]['time'] = 0;
                    $sum24[$field]['count'] = 0;
                    $sum24[$field]['avr_time'] = 0;
                }
                $sum24[$field]['name'] = self::get_pool_name($field);
                if (count($sum30) > 0) {
                    if (array_key_exists($field, $sum30)) {
                        $sum30[$field]['avr_time'] = round ($sum30[$field]['time'] / $sum30[$field]['count'], 0);
                    } else {
                        $sum30[$field]['time'] = 0;
                        $sum30[$field]['count'] = 0;
                        $sum30[$field]['avr_time'] = 0;
                    }

                } else {
                    $sum30[$field]['time'] = 0;
                    $sum30[$field]['count'] = 0;
                    $sum30[$field]['avr_time'] = 0;
                }
                $sum30[$field]['name'] = self::get_pool_name($field);
            }
            foreach ($values as $ts=>$serie) {
                foreach ($fields as $field) {
                    if (!array_key_exists($field, $serie['pools'])) {
                        $serie['pools'][$field]['count'] = 0;
                        $serie['pools'][$field]['time'] = 0;
                    }
                    if ($serie['pools'][$field]['count'] > 0) {
                        $avr = round($serie['pools'][$field]['time']/$serie['pools'][$field]['count'], 0);
                    }
                    else {
                        $avr = 0;
                    }
                    $jsonable['by_pool'][$field . '_count'][] = array($ts, $serie['pools'][$field]['count']);
                    $jsonable['by_pool'][$field . '_time'][] = array($ts, $avr);
                }
                foreach ($serie['crons'] as $key=>$cron) {
                    if ($cron['count'] > 0) {
                        $avr = round($cron['time']/$cron['count'], 0);
                    }
                    else {
                        $avr = 0;
                    }
                    $jsonable['by_cron'][$key][] = array($ts, $avr);
                }
            }
            $data = array();
            $data_r = array();
            foreach ($fields as $field) {
                $jsoned['by_pool'][$field . '_count'] = json_encode($jsonable['by_pool'][$field . '_count']);
                $jsoned['by_pool'][$field . '_count'] = str_replace('"', '', $jsoned['by_pool'][$field . '_count']);
                $data_r['by_pool']['count'][] = '{"key":"' . ucfirst(self::get_pool_name($field)) . '", "values":' . $jsoned['by_pool'][$field . '_count'] . '}';
                $jsoned['by_pool'][$field . '_time'] = json_encode($jsonable['by_pool'][$field . '_time']);
                $jsoned['by_pool'][$field . '_time'] = str_replace('"', '', $jsoned['by_pool'][$field . '_time']);
                $data_r['by_pool']['time'][] = '{"key":"' . ucfirst(self::get_pool_name($field)) . '", "values":' . $jsoned['by_pool'][$field . '_time'] . '}';
            }
            foreach ($jsonable['by_cron'] as $key=>$cron) {
                $jsoned['by_cron'][$key] = json_encode($cron);
                $jsoned['by_cron'][$key] = str_replace('"', '', $jsoned['by_cron'][$key]);
                $data_r['by_cron'][self::get_cron_pool($key)][] = '{"key":"' . ucfirst(self::get_cron_name($key)) . '", "values":' . $jsoned['by_cron'][$key] . '}';
            }
            $data['count_by_pool'] = '[' . implode(',', $data_r['by_pool']['count']) . ']';
            $data['time_by_pool'] = '[' . implode(',', $data_r['by_pool']['time']) . ']';
            foreach ($fields as $field) {
                $data['time_for_'.$field] = '[' . implode(',', $data_r['by_cron'][$field]) . ']';
            }
            $result = array('agr24' => $sum24, 'agr30' => $sum30, 'raw' => $raw, 'dat' => $data);
            Cache::set_backend(Cache::$db_stat_perf_cron, $result);
        }
        catch(\Exception $ex) {
            foreach ($fields as $field) {
                $sum24[$field]['time'] = 0;
                $sum24[$field]['count'] = 0;
                $sum24[$field]['avr_time'] = 0;
                $sum24[$field]['name'] = self::get_pool_name($field);
                $sum30[$field]['time'] = 0;
                $sum30[$field]['count'] = 0;
                $sum30[$field]['avr_time'] = 0;
                $sum30[$field]['name'] = self::get_pool_name($field);
            }
            $data = array();
            $data['count_by_pool'] = '[]';
            $data['time_by_pool'] = '[]';
            foreach ($fields as $field) {
                $data['time_for_'.$field] = '[]';
            }
            $result = array('agr24' => $sum24, 'agr30' => $sum30,  'dat' => $data);
        }
        return $result;
    }

    /**
     * Get all stats values for database.
     *
     * @since 3.5.0
     */
    public static function get_database_values() {
        if ($result = Cache::get_backend(Cache::$db_stat_perf_database)) {
            return $result;
        }
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix.Cache::live_weather_station_data_year_table() . " ;";
        $cutoff = time() - (get_option('live_weather_station_analytics_cutoff', 7)*DAY_IN_SECONDS);
        $values = array();
        $jsoned = array();
        $database = new Data(LWS_PLUGIN_NAME, LWS_VERSION);
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $tablename = $database->get_table_name($detail['table_name']);
                if (strtotime($detail['timestamp']) > $cutoff) {
                    $datetime = new \DateTime($detail['timestamp']);
                    $ts = $datetime->getTimestamp() . '000';
                    $values['table_size'][$ts][$tablename] = $detail['table_size'];
                    $values['row_size'][$ts][$tablename] = $detail['row_size'];
                    $values['row_count'][$ts][$tablename] = $detail['row_count'];
                }
            }
            $data = array();
            $data_r = array();
            foreach ($values as $type=>$tabledetail) {
                foreach ($tabledetail as $ts=>$table) {
                    foreach ($table as $t=>$v) {
                        $jsoned[$type][$t][] = str_replace('"', '', json_encode(array($ts,$v)));
                    }
                }
                foreach ($jsoned[$type] as $t=>$v) {
                    $data_r[$type][] = '{"key":"' . $t . '", "values":[' . implode(',', $jsoned[$type][$t]) . ']}';
                }
                $data[$type] = '[' . implode(',', $data_r[$type]) . ']';
            }
            $result = array('dat' => $data);
            Cache::set_backend(Cache::$db_stat_perf_database, $result);
        }
        catch(\Exception $ex) {
            $data = array();
            $data['table_size'] = '[]';
            $data['row_size'] = '[]';
            $data['row_count'] = '[]';
            $result = array('dat' => $data);
        }
        return $result;
    }

    /**
     * Get all stats values for events.
     *
     * @since 3.2.0
     */
    public static function get_event_values() {
        if ($result = Cache::get_backend(Cache::$db_stat_perf_event)) {
            return $result;
        }
        global $wpdb;
        $fields = array('system', 'service', 'device_name');
        $counts = array(24, 30);
        $values = array();
        $cutoff = array();
        $cutoff[24] = date('Y-m-d H:i:s',time() - (DAY_IN_SECONDS));
        $cutoff[30] = date('Y-m-d H:i:s',time() - (30*DAY_IN_SECONDS));
        $sum = array();
        $sum[24] = array();
        $sum[30] = array();
        $pre_jsonable = array();
        $jsonable = array();
        foreach (Logger::$ordered_severity as $severity) {
            foreach ($counts as $count) {
                $sum[$count][$severity] = 0;
            }
        }
        foreach ($counts as $count) {
            $sql = "SELECT `level`, count(*) as cpt FROM " . $wpdb->prefix . Cache::live_weather_station_log_table() . " WHERE timestamp > '" . $cutoff[$count] . "'GROUP BY `level`;";
            try {
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                foreach ($query_a as $val) {
                    $detail = (array)$val;
                    $sum[$count][$detail['level']] = $detail['cpt'];
                }
            } catch (\Exception $ex) {
                $sum[$count] = array();
            }
        }
        foreach ($fields as $field) {
            $field_list = array();
            $sql = "SELECT `" . $field . "`, `level`, count(*) as cpt FROM " . $wpdb->prefix . Cache::live_weather_station_log_table() . " GROUP BY `" . $field . "`, `level` ORDER BY `" . $field . "`, `level` DESC";
            try {
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                foreach ($query_a as $val) {
                    $detail = (array)$val;
                    $values[$field][$detail['level']][$detail[$field]] = $detail['cpt'];
                    if (!in_array($detail[$field], $field_list)) {
                        $field_list[] = $detail[$field];
                    }
                }
            }
            catch(\Exception $ex) {
                $values[$field]=array();
            }
            foreach (Logger::$ordered_severity as $severity) {
                foreach ($field_list as $element) {
                    $pre_jsonable[$field][$severity][$element] = 0;
                    //$pre_jsonable[$field.'_values'][] = '"' . $element . '"';
                }
            }
        }
        $density = array();
        $sql = "SELECT COUNT(*) as cpt, YEAR(timestamp) as year, MONTH(timestamp) as month, DAY(timestamp) as day, HOUR(timestamp) as hour FROM " . $wpdb->prefix . Cache::live_weather_station_log_table() . " GROUP BY year, month, day, hour";
        $density_max = 0;
        $density_datemin = time();
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $ts = mktime($detail['hour'], 0, 0, $detail['month'], $detail['day'], $detail['year']);
                if ($ts < $density_datemin) {
                    $density_datemin = $ts;
                }
                $density[] = $ts.':'.$detail['cpt'];
                if ($detail['cpt'] > $density_max) {
                    $density_max = $detail['cpt'];
                }
            }
        }
        catch(\Exception $ex) {
            $density = array();
        }
        $criticality = array();
        $sql = "SELECT COUNT(*) as cpt, YEAR(timestamp) as year, MONTH(timestamp) as month, DAY(timestamp) as day, HOUR(timestamp) as hour, level FROM " . $wpdb->prefix . Cache::live_weather_station_log_table() . " GROUP BY year, month, day, hour, level";
        $criticality_max = 0;
        $criticality_datemin = time();
        $tmp_date = 0;
        $tmp_criticality = 0;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $ts = mktime($detail['hour'], 0, 0, $detail['month'], $detail['day'], $detail['year']);
                if ($ts < $criticality_datemin) {
                    $criticality_datemin = $ts;
                }
                if ($ts != $tmp_date) {
                    $tmp_criticality = round($tmp_criticality, 0);
                    if ($tmp_criticality > 0) {
                        $criticality[] = $ts.':'.$tmp_criticality;
                        if ($tmp_criticality > $criticality_max) {
                            $criticality_max = $tmp_criticality;
                        }
                    }
                    $tmp_date = $ts;
                    $tmp_criticality = 0;
                }
                $tmp_criticality += $detail['cpt'] * Logger::get_criticality($detail['level']);
            }
            $tmp_criticality = round($tmp_criticality, 0);
            if ($tmp_criticality > 0) {
                $criticality[] = $ts.':'.$tmp_criticality;
                if ($tmp_criticality > $criticality_max) {
                    $criticality_max = $tmp_criticality;
                }
            }
        }
        catch(\Exception $ex) {
            $criticality = array();
        }
        $data = array();
        $data_r = array();
        foreach ($values as $key=>$field) {
            foreach ($field as $level => $serie) {
                foreach ($serie as $element => $cpt) {
                    $pre_jsonable[$key][$level][$element] = $cpt;
                }
            }
        }
        foreach ($pre_jsonable as $key=>$field) {
            foreach ($field as $level=>$serie) {
                foreach ($serie as $element => $cpt) {
                    $jsonable[$key][$level][] = array('x' => '$' . $element . '$', 'y' => $pre_jsonable[$key][$level][$element]);
                }
            }
            foreach (Logger::$ordered_severity as $severity) {
                $s = json_encode($jsonable[$key][$severity]);
                $s = str_replace('"', '', $s);
                $s = str_replace('$', '"', $s);
                $data_r[$key][] = '{"key":"' . ucfirst(Logger::get_name($severity)) . '", "color":"' . Logger::get_color($severity) . '", "values":' . $s . '}';
            }
            $data[$key] = '[' . implode(',', $data_r[$key]) . ']';
        }
        $data['density'] = '{' . implode(',', $density) . '}';
        $data['density_max'] = $density_max;
        $data['density_datemin'] = $density_datemin;
        $data['criticality'] = '{' . implode(',', $criticality) . '}';
        $data['criticality_max'] = $criticality_max;
        $data['criticality_datemin'] = $criticality_datemin;
        $result = array('agr24' => $sum[24], 'agr30' => $sum[30], 'dat' => $data);
        Cache::set_backend(Cache::$db_stat_perf_event, $result);
        return $result;
    }

    /**
     * Get all stats values for quotas.
     *
     * @since 3.2.0
     */
    public static function get_quota_values() {
        if ($result = Cache::get_backend(Cache::$db_stat_perf_quota)) {
            return $result;
        }
        global $wpdb;
        $verbs = array('post', 'get', 'put', 'patch', 'delete');
        $idx = array('sum', 'max');
        $sum = array();
        $data = array();

        // General values
        $service24 = array();
        $sql = "SELECT DISTINCT(service) FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . ' ORDER BY service ASC';
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $service24[] = $detail['service'];

            }
        } catch (\Exception $ex) {
            $service24 = array();
        }
        $data['service24'] = $service24;

        $service30 = array();
        $sql = "SELECT DISTINCT(service) FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table() . ' ORDER BY service ASC';
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $service30[] = $detail['service'];

            }
        } catch (\Exception $ex) {
            $service30 = array();
        }
        $data['service30'] = $service30;
        
        // 24H dashboard
        $sum[24] = array();
        $fields = array();
        foreach ($idx as $id) {
            foreach ($verbs as $verb) {
                $fields[] = $id . '(`' . $verb . '`) as ' . $id . '_' . $verb;
            }
        }
        $select = "service, " . implode(', ', $fields);
        $cutoff = date('Y-m-d H:i:s',time() - (DAY_IN_SECONDS));
        $where = "timestamp>='" . $cutoff . "'";
        $sql = "SELECT " . $select . " FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " WHERE ";
        $sql .= $where . " GROUP BY service;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                foreach ($verbs as $verb) {
                    $sum[24][$detail['service']][$verb]['has_quota'] = false;
                    $q = Quota::get_count_quota($detail['service'], $verb);
                    if ($q > 0) {
                        $v = 100 * $detail['sum_'.$verb] / $q;
                        $sum[24][$detail['service']][$verb]['has_quota'] = true;
                    }
                    else {
                        $v = 0;
                    }
                    $sum[24][$detail['service']][$verb]['count'] = round($v, 1);
                    $q = Quota::get_rate_quota($detail['service'], $verb);
                    if ($q > 0) {
                        $rate = $detail['max_'.$verb] / 10;
                        if ($detail['max_'.$verb] % 10 > 0) {
                            $rate += 1;
                        }
                        $v = 100 * $rate / $q;
                        $sum[24][$detail['service']][$verb]['has_quota'] = true;
                    }
                    else {
                        $v = 0;
                    }
                    $sum[24][$detail['service']][$verb]['rate'] = round($v, 1);
                }
            }
        } catch (\Exception $ex) {
            $sum[24] = array();
        }

        // 30D dashboard
        $sum[30] = array();
        $fields = array();
        foreach ($verbs as $verb) {
            $fields[] = 'avg(100*(if (`' . $verb . '_q`=0,0,`' . $verb . '`/`'. $verb . '_q`))) as avg_' . $verb ;
            $fields[] = '(if (`' . $verb . '_q`=0,0,1)) as avg_' . $verb .'_has_quota';
            $fields[] = 'max(100*(if (`' . $verb . '_rate_q`=0,0,`' . $verb . '_rate`/`'. $verb . '_rate_q`))) as max_' . $verb . '_rate' ;
            $fields[] = '(if (`' . $verb . '_rate_q`=0,0,1)) as max_' . $verb . '_rate_has_quota' ;
        }
        $select = "service, " . implode(', ', $fields);
        $cutoff = date('Y-m-d',time() - (31*DAY_IN_SECONDS)) . ' 00:00:00';
        $today = date('Y-m-d') . ' 00:00:00';
        $where = "timestamp>='" . $cutoff . "' AND timestamp<'" . $today . "'";
        $sql = "SELECT " . $select . " FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table() . " WHERE ";
        $sql .= $where . " GROUP BY service;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                foreach ($verbs as $verb) {
                    $has_quota = false;
                    $sum[30][$detail['service']][$verb]['count'] = round($detail['avg_' . $verb], 1);
                    $sum[30][$detail['service']][$verb]['rate'] = round($detail['max_' . $verb . '_rate'], 1);
                    if ($detail['avg_' . $verb . '_has_quota'] == 1) {
                        $has_quota = true;
                    }
                    if ($detail['max_' . $verb . '_rate_has_quota'] == 1) {
                        $has_quota = true;
                    }
                    $sum[30][$detail['service']][$verb]['has_quota'] = $has_quota;
                }
            }
        } catch (\Exception $ex) {
            $sum[30] = array();
        }

        // 24H verbs breakdown
        $sql = "SELECT COUNT(DISTINCT timestamp) as cpt FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table();
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = (array)$query_a[0];
            $cpt = ($query_t['cpt']-1)/144;
        } catch (\Exception $ex) {
            $cpt = 1;
        }
        if ($cpt == 0) {
            $cpt = 1;
        }
        $fields = array();
        $values = array();
        foreach ($verbs as $verb) {
            $fields[] = '(sum(`' . $verb . '`)) as cpt_' . $verb;
            foreach ($service24 as $service) {
                $values[$verb][$service] = 0;
            }
        }
        $select = "service, " . implode(', ', $fields);
        $sql = "SELECT " . $select . " FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " GROUP BY service;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                foreach ($verbs as $verb) {
                    $values[$verb][$detail['service']] = $detail['cpt_'.$verb]/$cpt;
                }
            }
        } catch (\Exception $ex) {
            //
        }
        $data_r = array();
        foreach ($verbs as $verb) {
            $jsonable = array();
            foreach ($service24 as $service) {
                $s = json_encode(array('x' => '$' . $service . '$', 'y' => round($values[$verb][$service], 0)));
                $s = str_replace('"', '', $s);
                $s = str_replace('$', '"', $s);
                $jsonable[] = $s;
            }
            $s = '[' . implode(',', $jsonable) . ']';
            $data_r[] = '{"key":"' . strtoupper($verb) . '", "values":' . $s . '}';
        }
        $data['count']['service_short'] = '[' . implode(',', $data_r) . ']';
        
        // 24H CALLS & RATES
        $values = array();
        $cutoff = date('Y-m-d H:i:s',time() - (DAY_IN_SECONDS));
        $where = "timestamp>='" . $cutoff . "'";
        $sql = "SELECT DISTINCT(timestamp) FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " WHERE ". $where ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $datetime = new \DateTime($detail['timestamp']);
                $time = $datetime->getTimestamp() . '000';
                foreach ($service24 as $service) {
                    foreach ($verbs as $verb) {
                        $values['call'][$service][$verb][$time] = 0;
                        $values['call'][$service][$verb.'_q'][$time] = 0;
                        $values['rate'][$service][$verb][$time] = 0;
                        $values['rate'][$service][$verb.'_q'][$time] = 0;
                    }
                }
            }
        }
        catch (\Exception $ex) {
            //
        }
        $sql = "SELECT * FROM " . $wpdb->prefix.self::live_weather_station_quota_day_table() . " WHERE ". $where ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $datetime = new \DateTime($detail['timestamp']);
                $time = $datetime->getTimestamp() . '000';
                foreach ($verbs as $verb) {
                    $values['call'][$detail['service']][$verb][$time] = $detail[$verb];
                    $values['call'][$detail['service']][$verb.'_q'][$time] = Quota::get_count_quota($detail['service'], $verb);
                    $rate = (integer)($detail[$verb] / 10);
                    if ($detail[$verb] % 10 > 0) {
                        $rate += 1;
                    }
                    $values['rate'][$detail['service']][$verb][$time] = $rate;
                    $values['rate'][$detail['service']][$verb.'_q'][$time] = Quota::get_rate_quota($detail['service'], $verb);
                }
            }
        }
        catch (\Exception $ex) {
            //
        }
        foreach ($service24 as $service) {
            $data_r = array();
            foreach ($verbs as $verb) {
                $jsonable = array();
                foreach ($values['call'][$service][$verb] as $t => $v) {
                    $s = json_encode(array($t, $v));
                    $s = str_replace('"', '', $s);
                    $jsonable[] = $s;
                }
                $s = '[' . implode(',', $jsonable) . ']';
                $data_r[] = '{"key":"' . strtoupper($verb) . '", "values":' . $s . '}';
            }
            $data['call_short'][$service] = '[' . implode(',', $data_r) . ']';
        }
        foreach ($service24 as $service) {
            $data_r = array();
            foreach ($verbs as $verb) {
                $jsonable = array();
                foreach ($values['rate'][$service][$verb] as $t => $v) {
                    $s = json_encode(array($t, $v));
                    $s = str_replace('"', '', $s);
                    $jsonable[] = $s;
                }
                $s = '[' . implode(',', $jsonable) . ']';
                $data_r[] = '{"key":"' . strtoupper($verb) . '", "values":' . $s . '}';
            }
            $data['rate_short'][$service] = '[' . implode(',', $data_r) . ']';
        }

        // 30H CALLS & RATES
        $values = array();
        $cutoff = date('Y-m-d',time()). ' 00:00:00';
        $where = "timestamp<'" . $cutoff . "'";
        $sql = "SELECT DISTINCT(timestamp) FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table() . " WHERE ". $where;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $datetime = new \DateTime($detail['timestamp']);
                $time = $datetime->getTimestamp() . '000';
                foreach ($service30 as $service) {
                    foreach ($verbs as $verb) {
                        $values['call'][$service][$verb][$time] = 0;
                        $values['call'][$service][$verb.'_q'][$time] = Quota::get_count_quota($service, $verb);
                        $values['rate'][$service][$verb][$time] = 0;
                        $values['rate'][$service][$verb.'_q'][$time] = Quota::get_rate_quota($service, $verb);
                    }
                }
            }
        }
        catch (\Exception $ex) {
            //
        }
        $cutoff = date('Y-m-d',time()). ' 00:00:00';
        $where = "timestamp<'" . $cutoff . "'";
        $sql = "SELECT * FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table() . " WHERE ". $where;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                $datetime = new \DateTime($detail['timestamp']);
                $time = $datetime->getTimestamp() . '000';
                foreach ($verbs as $verb) {
                    $values['call'][$detail['service']][$verb][$time] = $detail[$verb];
                    $values['call'][$detail['service']][$verb.'_q'][$time] = $detail[$verb.'_q'];
                    $values['rate'][$detail['service']][$verb][$time] = $detail[$verb.'_rate'];
                    $values['rate'][$detail['service']][$verb.'_q'][$time] = $detail[$verb.'_rate_q'];
                }
            }
        }
        catch (\Exception $ex) {
            //
        }
        foreach ($service30 as $service) {
            $data_r = array();
            foreach ($verbs as $verb) {
                $jsonable = array();
                $jsonable['values'] = array();
                $jsonable['quotas'] = array();
                $hidden_values = true;
                $hidden_quotas = true;
                if (isset($values['rate'][$service][$verb]) && is_array($values['rate'][$service][$verb])) {
                    foreach ($values['call'][$service][$verb] as $t => $v) {
                        $s = json_encode(array($t, $values['call'][$service][$verb][$t]));
                        $s = str_replace('"', '', $s);
                        $jsonable['values'][] = $s;
                        $s = json_encode(array($t, $values['call'][$service][$verb . '_q'][$t]));
                        $s = str_replace('"', '', $s);
                        $jsonable['quotas'][] = $s;
                        if ($hidden_values) {
                            if ($values['call'][$service][$verb][$t] > 0) {
                                $hidden_values = false;
                            }
                        }
                        if ($hidden_quotas) {
                            if ($values['call'][$service][$verb . '_q'][$t] > 0) {
                                $hidden_quotas = false;
                            }
                        }
                    }
                }
                $class_value = '';
                if ($hidden_values) {
                    $class_value = ', "classed":"hidden-line"';
                }
                $class_quota = ', "classed":"dashed-line"';
                if ($hidden_quotas) {
                    $class_quota = ', "classed":"hidden-line"';
                }
                $s = '[' . implode(',', $jsonable['values']) . ']';
                $data_r[] = '{"key":"' . strtoupper($verb) . '"' . $class_value . ', "values":' . $s . '}';
                $s = '[' . implode(',', $jsonable['quotas']) . ']';
                $data_r[] = '{"key":"' . strtoupper($verb) . ' - quotas"' . $class_quota . ', "strokeWidth":3, "values":' . $s . '}';
            }
            $data['call_long'][$service] = '[' . implode(',', $data_r) . ']';
        }

        foreach ($service30 as $service) {
            $data_r = array();
            foreach ($verbs as $verb) {
                $jsonable = array();
                $jsonable['values'] = array();
                $jsonable['quotas'] = array();
                $hidden_values = true;
                $hidden_quotas = true;
                if (isset($values['rate'][$service][$verb]) && is_array($values['rate'][$service][$verb])) {
                    foreach ($values['rate'][$service][$verb] as $t => $v) {
                        $s = json_encode(array($t, $values['rate'][$service][$verb][$t]));
                        $s = str_replace('"', '', $s);
                        $jsonable['values'][] = $s;
                        $s = json_encode(array($t, $values['rate'][$service][$verb . '_q'][$t]));
                        $s = str_replace('"', '', $s);
                        $jsonable['quotas'][] = $s;
                        if ($hidden_values) {
                            if ($values['rate'][$service][$verb][$t] > 0) {
                                $hidden_values = false;
                            }
                        }
                        if ($hidden_quotas) {
                            if ($values['rate'][$service][$verb . '_q'][$t] > 0) {
                                $hidden_quotas = false;
                            }
                        }
                    }
                }
                $class_value = '';
                if ($hidden_values) {
                    $class_value = ', "classed":"hidden-line"';
                }
                $class_quota = ', "classed":"dashed-line"';
                if ($hidden_quotas) {
                    $class_quota = ', "classed":"hidden-line"';
                }
                $s = '[' . implode(',', $jsonable['values']) . ']';
                $data_r[] = '{"key":"' . strtoupper($verb) . '"' . $class_value . ', "values":' . $s . '}';
                $s = '[' . implode(',', $jsonable['quotas']) . ']';
                $data_r[] = '{"key":"' . strtoupper($verb) . ' - quotas"' . $class_quota . ', "strokeWidth":3, "values":' . $s . '}';
            }
            $data['rate_long'][$service] = '[' . implode(',', $data_r) . ']';
        }


        // 30D verbs breakdown
        $sql = "SELECT COUNT(DISTINCT timestamp) as cpt FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table();
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $query_t = (array)$query_a[0];
            $cpt = $query_t['cpt'];
        } catch (\Exception $ex) {
            $cpt = 1;
        }
        if ($cpt == 0) {
            $cpt = 1;
        }
        $fields = array();
        $values = array();
        foreach ($verbs as $verb) {
            $fields[] = '(sum(`' . $verb . '`)) as cpt_' . $verb;
            foreach ($service30 as $service) {
                $values[$verb][$service] = 0;
            }
        }
        $cutoff = date('Y-m-d',time()). ' 00:00:00';
        $where = "timestamp<'" . $cutoff . "'";
        $select = "service, " . implode(', ', $fields);
        $sql = "SELECT " . $select . " FROM " . $wpdb->prefix.self::live_weather_station_quota_year_table() . " WHERE ". $where . " GROUP BY service;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $detail = (array)$val;
                foreach ($verbs as $verb) {
                    $values[$verb][$detail['service']] = $detail['cpt_'.$verb]/$cpt;
                }
            }
        } catch (\Exception $ex) {
            //
        }
        $data_r = array();
        foreach ($verbs as $verb) {
            $jsonable = array();
            foreach ($service30 as $service) {
                $s = json_encode(array('x' => '$' . $service . '$', 'y' => round($values[$verb][$service], 0)));
                $s = str_replace('"', '', $s);
                $s = str_replace('$', '"', $s);
                $jsonable[] = $s;
            }
            $s = '[' . implode(',', $jsonable) . ']';
            $data_r[] = '{"key":"' . strtoupper($verb) . '", "values":' . $s . '}';
        }
        $data['count']['service_long'] = '[' . implode(',', $data_r) . ']';

        $result = array('agr24' => $sum[24], 'agr30' => $sum[30], 'dat' => $data);
        Cache::set_backend(Cache::$db_stat_perf_quota, $result);
        return $result;
    }
}