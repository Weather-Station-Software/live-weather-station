<?php

namespace WeatherStation\System\Analytics;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;

/**
 * The class to compute and maintain consistency of performance statistics.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Performance {

    use Storage;

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
        Cache::rotate();
        Logger::notice($this->facility,null,null,null,null,null,null,'Performance data cleaned.');
    }

    /**
     * Get all stats values.
     *
     * @since 3.1.0
     */
    public static function get_cache_values() {
        if ($result = Cache::get_backend(Cache::$db_stat_perf_cache)) {
            return $result;
        }
        $fields = array('backend', 'frontend', 'widget');
        $dimensions = array('miss', 'hit');
        $field_names = array('backend' => __('backend', 'live-weather-station'),
                            'frontend' => __('control', 'live-weather-station'),
                            'widget' => __('widget', 'live-weather-station'));
        $dimension_names = array('miss' => __('miss', 'live-weather-station'), 'hit' => __('hit', 'live-weather-station'));
        $metrics = array('count', 'time');
        $aggregates = array('efficiency', 'time_saving');
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix.Cache::live_weather_station_performance_cache_table() . " ;";
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
                $datetime = new \DateTime($detail['timestamp']);
                $time = $datetime->getTimestamp().'000';
                foreach ($fields as $field) {
                    $eff = -1;
                    $tim = -1;
                    if ($detail[$field.'_hit_count'] + $detail[$field.'_miss_count'] > 0) {
                        $val = $detail[$field.'_hit_count'] / ($detail[$field.'_hit_count'] + $detail[$field.'_miss_count']);
                        $jsonable[$field.'_efficiency'][] = array($time, $val);
                        $eff = round($val*100, 0);
                    }
                    else {
                        $jsonable[$field.'_efficiency'][] = array($time, 0);
                    }
                    if ($detail[$field.'_hit_count'] > 0 && $detail[$field.'_miss_count'] > 0) {
                        $avr_hit = $detail[$field.'_hit_time'] / $detail[$field.'_hit_count'];
                        $avr_miss = $detail[$field.'_miss_time'] / $detail[$field.'_miss_count'];
                        $val = ($avr_miss - $avr_hit)*$detail[$field.'_hit_count'];
                        $jsonable[$field.'_time_saving'][] = array($time, $val);
                        $tim = round($avr_miss - $avr_hit,0)*$detail[$field.'_hit_count'];
                    }
                    else {
                        $jsonable[$field.'_time_saving'][] = array($time, 0);
                    }
                    foreach ($dimensions as $dimension) {
                        foreach ($metrics as $metric) {
                            $val = $detail[$field.'_'.$dimension.'_'.$metric];
                            if ($metric == 'time') {
                                $val = 1;
                                if ($detail[$field.'_'.$dimension.'_count'] > 0) {
                                    $val = round($detail[$field.'_'.$dimension.'_'.$metric] / $detail[$field.'_'.$dimension.'_count'], 0);
                                    if ($val < 1) {
                                        $val = 1;
                                    }
                                }
                            }
                            $jsonable[$field.'_'.$dimension.'_'.$metric][] = array($time, $val);
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
            $data_r = array();
            foreach ($fields as $field) {
                foreach ($aggregates as $aggregate) {
                    $data_r[$aggregate][] = '{"key":"' . $field_names[$field] . '", "values":[]}';
                }
                foreach ($dimensions as $dimension) {
                    foreach ($metrics as $metric) {
                        $data_r[$metric][] = '{"key":"' . $field_names[$field] . ' / ' . $dimension_names[$dimension] . '", "values":[]}';
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
            $result = array('agr24' => $agr24, 'agr30' => $agr30, 'dat' => $data);
        }
        return $result;
    }
}