<?php

namespace WeatherStation\Data;

use WeatherStation\Data\DateTime\Conversion as Datetime_Conversion;
use WeatherStation\Data\Type\Description as Type_Description;
use WeatherStation\Data\Unit\Description as Unit_Description;
use WeatherStation\Data\Unit\Conversion as Unit_Conversion;
use WeatherStation\SDK\Generic\Plugin\Season\Calculator;
use WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector as OWM_Base_Collector;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Utilities\ColorsManipulation;
use WeatherStation\DB\Query;
use WeatherStation\System\Analytics\Performance;
use WeatherStation\Utilities\Markdown;
use WeatherStation\Data\History\Builder as History;
use WeatherStation\System\Environment\Manager as EnvManager;
use WeatherStation\Utilities\ColorBrewer;
use WeatherStation\System\Device\Manager as DeviceManager;

/**
 * Outputing / shortcoding functionalities for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Output {
    
    use Unit_Description, Type_Description, Datetime_Conversion, Unit_Conversion, Query;

    private $unit_espace = '&nbsp;';
    private $showable_measurements = array('co2', 'co', 'o3', 'humidity', 'humint', 'humext', 'humidity_ref',
        'cloudiness', 'noise', 'rain', 'rain_hour_aggregated', 'rain_day_aggregated' , 'rain_yesterday_aggregated',
        'rain_month_aggregated','rain_season_aggregated', 'rain_year_aggregated','snow', 'windangle', 'gustangle',
        'windangle_max', 'windangle_day_max', 'windangle_hour_max', 'windstrength', 'guststrength', 'windstrength_max',
        'windstrength_day_max', 'windstrength_hour_max', 'wind_ref', 'pressure', 'temperature', 'tempint', 'tempext',
        'temperature_min', 'temperature_max', 'temperature_ref', 'dew_point', 'frost_point', 'heat_index', 'humidex',
        'wind_chill', 'cloud_ceiling', 'temperature_trend', 'pressure_trend', 'sunrise', 'sunset', 'moonrise',
        'moonset', 'moon_illumination', 'moon_diameter', 'sun_diameter', 'moon_distance', 'sun_distance', 'moon_phase',
        'moon_age', 'o3_distance', 'co_distance', 'humidity_min', 'humidity_max', 'pressure_min', 'pressure_max',
        'day_length', 'health_idx', 'cbi', 'pressure_ref', 'wet_bulb', 'air_density', 'wood_emc',
        'equivalent_temperature', 'potential_temperature', 'specific_enthalpy', 'partial_vapor_pressure',
        'partial_absolute_humidity', 'irradiance', 'uv_index', 'illuminance', 'soil_temperature', 'leaf_wetness',
        'moisture_content', 'moisture_tension', 'evapotranspiration', 'strike_count', 'strike_instant',
        'strike_distance', 'strike_bearing', 'visibility');
    private $not_showable_measurements = array('battery', 'firmware', 'signal', 'loc_timezone', 'loc_altitude',
        'loc_latitude', 'loc_longitude', 'last_seen', 'last_refresh', 'first_setup', 'last_upgrade', 'last_setup',
        'sunrise_c','sunrise_n','sunrise_a', 'sunset_c','sunset_n', 'sunset_a', 'day_length_c', 'day_length_n',
        'day_length_a', 'dawn_length_a','dawn_length_n', 'dawn_length_c', 'dusk_length_a', 'dusk_length_n',
        'dusk_length_c','saturation_vapor_pressure','saturation_absolute_humidity','equivalent_potential_temperature');
    private $graph_allowed_serie = array('device_id', 'module_id', 'measurement', 'line_mode', 'dot_style', 'line_style', 'line_size');
    private $graph_allowed_parameter = array('cache', 'mode', 'type', 'template', 'color', 'label', 'interpolation', 'guideline', 'height', 'timescale', 'valuescale', 'data', 'periodtype', 'periodvalue');


    /**
     * Get the changelog.
     *
     * @return string $attributes The type of analytics queryed by the shortcode.
     * @since 3.3.0
     */
    public function admin_changelog_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('style' => 'markdown', 'title' => 'h3', 'list' => 'bullet'), $attributes );
        $style = $_attributes['style'];
        $title = $_attributes['title'];
        $list = $_attributes['list'];

        $changelog = LWS_PLUGIN_DIR . 'changelog.txt';
        if (file_exists($changelog)) {
            try {
                $s = file_get_contents($changelog);
                $Markdown = new Markdown();
                $result = $Markdown->text($s);
            }
            catch (\Exception $e) {
                $result = __('Sorry, unable to find or read changelog file.', 'live-weather-station');
            }
        }
        else {
            $result = __('Sorry, unable to find or read changelog file.', 'live-weather-station');
        }

        if ($list == 'icon') {
            $result = str_replace('<ul>', '', $result);
            $result = str_replace('</ul>', '', $result);
            $result = str_replace('<li>', '', $result);
            $result = str_replace('</li>', '<br/>', $result);
            $result = str_replace('New: ', '<i class="fa fa-fw fa-plus-square-o" aria-hidden="true">&nbsp;</i>', $result);
            $result = str_replace('New language: ', '<i class="fa fa-fw fa-language" aria-hidden="true">&nbsp;</i>new translation: ', $result);
            $result = str_replace('Improvement: ', '<i class="fa fa-fw fa-check-square-o" aria-hidden="true">&nbsp;</i>', $result);
            $result = str_replace('Bug fix: ', '<i class="fa fa-fw fa-bug" aria-hidden="true">&nbsp;</i>fixed: ', $result);
        }

        if ($style == 'divi_accordion') {
            $result = str_replace('<h1>',  '</p>' . PHP_EOL .'</div>' . PHP_EOL . '</div>' . PHP_EOL . '<div class="et_pb_module et_pb_toggle et_pb_toggle_close">' . PHP_EOL . '<h1 class="et_pb_toggle_title">', $result);
            $result = str_replace('</h1>',  '</h1>' . PHP_EOL . '<div class="et_pb_toggle_content clearfix"><p>', $result);
            $result = substr($result, 78, 100000);
            $result = '<div class="et_pb_module et_pb_toggle et_pb_toggle_open">' . PHP_EOL . $result;
            $result = '<div class="et_pb_module et_pb_accordion">' . PHP_EOL . $result;
            $result .= '</p></div></div></div>';
        }

        $result = str_replace('<h1', '<'.$title, $result);
        $result = str_replace('</h1', '</'.$title, $result);
        return $result;
    }

    /**
     * Transform in a json string.
     *
     * @param array $info The key fields.
     * @param array $values The values.
     * @param boolean $raw Optional. Get values as raw values.
     * @return string The values jsonified.
     * @since 3.4.0
     */
    private function jsonify($info, $values, $raw=false, $multi=false) {
        $val = array();
        $inf = array();
        foreach ($values as $value) {
            if ($multi) {
                $val[] = $value;
            }
            else {
                $val[] = array($value['timestamp'], $value['measure_value']);
            }
        }
        if ($raw) {
            foreach ($values as $value) {
                $inf[] = '"' . $value['timestamp'] . '":'.$value['measure_value'];
            }
        }
        else {
            if (isset($info)) {
                foreach ($info as $key=>$field) {
                    $inf[] = '"' . $key . '":"' . $field . '"';
                }
            }
            if ($multi) {
                if (isset($info)) {
                    $s = str_replace(':"', ':', json_encode($val));
                    $s = str_replace('",', ',', $s);
                    $s = str_replace('"}', '}', $s);
                    $inf[] = '"values":' . $s;
                }
                else {
                    $inf[] = json_encode($val);
                }
            }
            else {
                if (isset($info)) {
                    $inf[] = '"values":' . str_replace('"', '', json_encode($val));
                }
                else {
                    $inf[] = json_encode($val);
                }
            }
        }
        if (isset($info)) {
            $result = '{' . implode(', ', $inf) . '}';
        }
        else {
            $result = implode(', ', $inf);
            $result = substr($result, 1);
            $result = substr_replace($result, "", -1);
        }
        return $result;
    }

    /**
     * Query values for graph.
     *
     * @param array $attributes The type of values queried.
     * @param boolean $json The result must json encoded.
     * @return array The values.
     * @since 3.4.0
     */
    public function graph_query($attributes, $json=false) {
        $result = null;
        $mode = $attributes['mode'];
        $type = $attributes['type'];
        $args = $attributes['args'];
        $fingerprint = md5(json_encode($attributes));
        if ($attributes['cache'] != 'no_cache') {
            $result = Cache::get_graph($fingerprint, $attributes['mode']);
        }
        $raw_json = false;
        if ($attributes['type'] == 'calendarhm') {
            $raw_json = true;
        }
        if (!$result) {
            $result = array();
            $timescale = $attributes['timescale'];
            $valuescale = $attributes['valuescale'];
            $self_color = $attributes['color'] == 'self';
            if ($self_color) {
                $prop = $this->graph_template($attributes['template']);
            }
            if ($timescale == 'focus') {
                $timescale = 'adaptative';
            }
            $station_id = '';
            $ymin = 0;
            $ymax = 0;
            $cmin = 0;
            $cmax = 0;
            $ydmin = 0;
            $ydmax = 0;
            $yamin = 0;
            $yamax = 0;
            $start = true;
            $similar = false;
            $identical = false;
            $similar_module=false;
            $identical_module=false;
            $similar_set=false;
            $identical_set=false;
            $end_date = '000';
            if ($type == 'calendarhm') {
                $end_date = '';
            }
            $cpt = 0;
            if (count($args) > 0) {
                foreach ($args as $arg) {
                    if ($arg['module_id'] != 'none' && strpos($arg['device_id'], ':') == 2) {
                        $station_id = $arg['device_id'];
                        $cpt += 1;
                    }
                }
                $temp = array();
                if ($cpt > 1) {
                    foreach ($args as $arg) {
                        if ($arg['measurement'] != 'none') {
                            $temp[] = $arg['measurement'];
                        }
                    }
                    $t = array_unique($temp);
                    if (count($t) != count($args)) {
                        $similar = true;
                        if (count($t) == 1) {
                            $identical = true;
                        }
                    }
                }
                if ($mode == 'yearly') {
                    $temp = array();
                    if ($cpt > 1) {
                        foreach ($args as $arg) {
                            if ($arg['module_id'] != 'none') {
                                $temp[] = $arg['module_id'];
                            }
                        }
                        $t = array_unique($temp);
                        if (count($t) != count($args)) {
                            $similar_module = true;
                            if (count($t) == 1) {
                                $identical_module = true;
                            }
                        }
                    }
                    $temp = array();
                    if ($cpt > 1) {
                        foreach ($args as $arg) {
                            if ($arg['set'] != 'none') {
                                $temp[] = $arg['set'];
                            }
                        }
                        $t = array_unique($temp);
                        if (count($t) != count($args)) {
                            $similar_set = true;
                            if (count($t) == 1) {
                                $identical_set = true;
                            }
                        }
                    }
                }
                if ($station_id != '') {
                    global $wpdb;
                    // Get extended station information
                    $station = $this->get_extended_station_informations_by_station_id($station_id);

                    // Compute date limits
                    if ($mode == 'daily') {
                        $min = date('Y-m-d H:i:s', self::get_local_today_midnight($station['loc_timezone']));
                        $max = date('Y-m-d H:i:s', self::get_local_today_noon($station['loc_timezone']));
                        $result['xdomain']['min'] = self::get_js_datetime_from_mysql_utc($min, $station['loc_timezone']);
                        $result['xdomain']['04'] = $result['xdomain']['min'] + 14400000;
                        $result['xdomain']['08'] = $result['xdomain']['min'] + 14400000*2;
                        $result['xdomain']['12'] = $result['xdomain']['min'] + 14400000*3;
                        $result['xdomain']['16'] = $result['xdomain']['min'] + 14400000*4;
                        $result['xdomain']['20'] = $result['xdomain']['min'] + 14400000*5;
                        $result['xdomain']['max'] = (integer)self::get_js_datetime_from_mysql_utc($max, $station['loc_timezone']) + 1000;
                        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
                    }
                    if ($mode == 'yearly') {
                        $d = array('1971-08-01', '1971-08-31');
                        $is_month = $attributes['periodduration'] == 'month';
                        $is_mseason = $attributes['periodduration'] == 'mseason';
                        $is_year = $attributes['periodduration'] == 'year';
                        $v = explode('-', $attributes['periodvalue']);
                        if (strpos($attributes['periodtype'], 'ixed-') > 0) {
                            $d = explode(':', $attributes['periodvalue']);
                        }
                        elseif ($is_month) {
                            $d = explode(':', $this->get_shifted_month(-$v[1], $station['loc_timezone']));
                        }
                        elseif ($is_year) {
                            $d = explode(':', $this->get_shifted_year(-$v[1], $station['loc_timezone']));
                        }
                        elseif ($is_mseason) {
                            $d = explode(':', $this->get_shifted_meteorological_season(-$v[1], $station['loc_timezone']));
                        }
                        $min = $d[0];
                        $max = $d[1];
                        $result['xdomain']['min'] = self::get_js_date_from_mysql_utc($min, $station['loc_timezone']);
                        $result['xdomain']['max'] = self::get_js_date_from_mysql_utc($max, $station['loc_timezone']);
                        $month = substr($min, 5, 2);
                        $year = substr($min, 0, 4);
                        if ($is_month) {
                            $result['xdomain']['01'] = self::get_js_date_from_mysql_utc($year.'-' . $month .'-08', $station['loc_timezone']);
                            $result['xdomain']['02'] = self::get_js_date_from_mysql_utc($year.'-' . $month .'-15', $station['loc_timezone']);
                            $result['xdomain']['03'] = self::get_js_date_from_mysql_utc($year.'-' . $month .'-23', $station['loc_timezone']);
                        }
                        elseif ($is_year) {
                            $result['xdomain']['01'] = self::get_js_date_from_mysql_utc($year.'-04-01', $station['loc_timezone']);
                            $result['xdomain']['02'] = self::get_js_date_from_mysql_utc($year.'-07-01', $station['loc_timezone']);
                            $result['xdomain']['03'] = self::get_js_date_from_mysql_utc($year.'-10-01', $station['loc_timezone']);
                        }
                        elseif ($is_mseason) {
                            $result['xdomain']['01'] = self::get_js_date_from_mysql_utc($year.'-' . $month .'-23', $station['loc_timezone']);
                            if ($month == 12) {
                                $smonth = 0 ;
                                $syear = $year + 1;
                            }
                            else {
                                $smonth = $month ;
                                $syear = $year;
                            }
                            $result['xdomain']['02'] = self::get_js_date_from_mysql_utc($syear.'-' . (string)($smonth+1) .'-15', $station['loc_timezone']);
                            $result['xdomain']['03'] = self::get_js_date_from_mysql_utc($syear.'-' . (string)($smonth+2) .'-08', $station['loc_timezone']);
                        }
                        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
                    }
                    if ($type == 'valuerc') {
                        if (count($args) == 2) {
                            if (strpos($args[1]['dot_style'], 'res-') !== false) {
                                $resolution = substr($args[1]['dot_style'], 4);
                                try {
                                    $resolution = (int)$resolution;
                                } catch (\Exception $ex) {
                                    $resolution = 10;
                                }
                            }
                            else {
                                $resolution = 10;
                            }
                            $sects = str_replace('s', '', $args[1]['line_mode']);
                            $angle_val = (int)(360 / $sects);
                            $sectors = array();
                            for ($i = 0; $i < $sects; $i++) {
                                $a = ($i * $angle_val) - ($angle_val / 2);
                                $b = ($i * $angle_val) + ($angle_val / 2);
                                $sectors[] = array($a, $b);
                            }
                            if ($mode == 'yearly') {
                                $timeshift = 86400;
                            } else {
                                $timeshift = (int)(60 * $resolution);
                            }
                            $dummy='0';
                            $val0 = '`measure_value`';
                            $set0 = '';
                            if ($mode == 'yearly') {
                                $set0 = " AND `measure_set`='" . $args[1]['set'] . "'";
                            }
                            if ($mode == 'yearly' && strtolower($args[1]['set']) == 'amp') {
                                $set0 = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                $val0 = 'ABS(MAX(`measure_value`)-MIN(`measure_value`)) as computed_value';
                            }
                            if ($mode == 'yearly' && strtolower($args[1]['set']) == 'mid') {
                                $set0 = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                $val0 = 'AVG(`measure_value`) as computed_value';
                            }
                            $sql_angle = "SELECT `timestamp`, `module_type`, " . $val0 . " FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $args[1]['device_id'] . "' AND `module_id`='" . $args[1]['module_id'] . "' AND `measure_type`='" . $args[1]['measurement'] . "'" . $set0 . " ORDER BY `timestamp` ASC;";
                            if ($mode == 'yearly') {
                                $sql_value = "SELECT `timestamp`, `module_type`, `measure_set`, `measure_value` FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $args[2]['device_id'] . "' AND `module_id`='" . $args[2]['module_id'] . "' AND `measure_type`='" . $args[2]['measurement'] . "' AND (`measure_set`='max' OR `measure_set`='avg' OR `measure_set`='min') ORDER BY `timestamp` ASC;";
                            }
                            else {
                                $sql_value = "SELECT `timestamp`, `module_type`, `measure_value` FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $args[2]['device_id'] . "' AND `module_id`='" . $args[2]['module_id'] . "' AND `measure_type`='" . $args[2]['measurement'] . "'  ORDER BY `timestamp` ASC;";
                            }
                            try {
                                $angles = $wpdb->get_results($sql_angle, ARRAY_A);
                                $values = $wpdb->get_results($sql_value, ARRAY_A);
                                $measures = array();
                                foreach ($values as $val) {
                                    if (array_key_exists($val['timestamp'], $measures)) {
                                        $t = $measures[$val['timestamp']];
                                    }
                                    else {
                                        $t = array();
                                        if (array_key_exists('measure_set', $val)) {
                                            $t['measure_set'] = $val['measure_set'];
                                        }
                                        $t['max'] = '';
                                        $t['avg'] = '';
                                        $t['min'] = '';
                                    }
                                    if (array_key_exists('measure_set', $val)) {
                                        $t[$val['measure_set']] = $val['measure_value'];
                                    }
                                    else {
                                        $t['avg'] = $val['measure_value'];
                                    }
                                    $measures[$val['timestamp']] = $t;
                                }
                                if (count($values) > 0) {
                                    $module_type = $values[0]['module_type'];
                                } else {
                                    $module_type = 'NAMain';
                                }
                                $t = array();
                                $values = array();
                                $final = array();
                                $ranges = array();
                                for ($i = 0; $i < $sects; $i++) {
                                    $t[$i] = array();
                                    $values[$i] = array();
                                    $final[$i] = array('min'=>null, 'avg'=>null, 'max'=>null);
                                }
                                foreach ($angles as $angle) {
                                    $v = $angle['measure_value'];
                                    $ts = $angle['timestamp'];
                                    $m = null;
                                    if (array_key_exists($ts, $measures)) {
                                        if (is_array($measures[$ts])) {
                                            $m = $measures[$ts];
                                        }
                                    }
                                    for ($i = 0; $i < $sects; $i++) {
                                        $a = $sectors[$i][0];
                                        $b = $sectors[$i][1];
                                        if ($a < 0) {
                                            $v = $v - 360;
                                        }
                                        if ($b > 360) {
                                            $v = $v + 360;
                                        }
                                        if ($v < $b && $v >= $a && !is_null($m)) {
                                            $values[$i][] = $m;
                                        }
                                        if ($a < 0) {
                                            $v = $v + 360;
                                        }
                                        if ($b > 360) {
                                            $v = $v - 360;
                                        }
                                    }
                                }
                                for ($i = 0; $i < $sects; $i++) {
                                    if (count($values[$i]) > 0) {
                                        $start = true;
                                        $min = null;
                                        $max = null;
                                        $avg = null;
                                        foreach ($values[$i] as $val) {
                                            if ($start) {
                                                if (array_key_exists('min', $val)) {
                                                    $min = $val['min'];
                                                }
                                                if (array_key_exists('max', $val)) {
                                                    $max = $val['max'];
                                                }
                                                $start = false;
                                            }
                                            if (array_key_exists('min', $val)) {
                                                if ($min > $val['min']) {
                                                    $min = $val['min'];
                                                }
                                            }
                                            if (array_key_exists('max', $val)) {
                                                if ($max < $val['max']) {
                                                    $max = $val['max'];
                                                }
                                            }
                                            if (array_key_exists('avg', $val)) {
                                                $avg += $val['avg'];
                                            }
                                        }
                                        if (is_null($min)) {
                                            $final[$i]['min'] = null;
                                        }
                                        else {
                                            $final[$i]['min'] = (float)$this->output_value($min, $args[2]['measurement'], false, false, $module_type);
                                        }
                                        if (is_null($max)) {
                                            $final[$i]['min'] = null;
                                        }
                                        else {
                                            $final[$i]['max'] = (float)$this->output_value($max, $args[2]['measurement'], false, false, $module_type);
                                        }
                                        if (is_null($avg)) {
                                            $final[$i]['min'] = null;
                                        }
                                        else {
                                            $final[$i]['avg'] = (float)$this->output_value($avg/count($values[$i]) , $args[2]['measurement'], false, false, $module_type);
                                        }
                                    }
                                }
                                $vmin = array();
                                $vmax = array();
                                $vavg = array();
                                $start = true;
                                $rmin = 0;
                                $rmax = 0;
                                for ($i = 0; $i < $sects; $i++) {
                                    if ($mode == 'yearly') {
                                        if ($start && !is_null($final[$i]['min']) && !is_null($final[$i]['max'])) {
                                            $rmin = $final[$i]['min'];
                                            $rmax = $final[$i]['max'];
                                            $start = false;
                                        }
                                        if (!is_null($final[$i]['min'])) {
                                            if ($rmin > $final[$i]['min']) {
                                                $rmin = $final[$i]['min'];
                                            }
                                        }
                                        if (!is_null($final[$i]['max'])) {
                                            if ($rmax < $final[$i]['max']) {
                                                $rmax = $final[$i]['max'];
                                            }
                                        }
                                    }
                                    else {
                                        if ($start && !is_null($final[$i]['avg'])) {
                                            $rmin = $final[$i]['avg'];
                                            $rmax = $final[$i]['avg'];
                                            $start = false;
                                        }
                                        if (!is_null($final[$i]['avg'])) {
                                            if ($rmin > $final[$i]['avg']) {
                                                $rmin = $final[$i]['avg'];
                                            }
                                            if ($rmax < $final[$i]['avg']) {
                                                $rmax = $final[$i]['avg'];
                                            }
                                        }
                                    }
                                    $anglename = $this->get_angle_text($i * $angle_val);
                                    $a = array();
                                    $a['axis'] = $anglename;
                                    $a['value'] = $final[$i]['min'];
                                    $vmin[] = $a;
                                    $a = array();
                                    $a['axis'] = $anglename;
                                    $a['value'] = $final[$i]['max'];
                                    $vmax[] = $a;
                                    $a = array();
                                    $a['axis'] = $anglename;
                                    $a['value'] = $final[$i]['avg'];
                                    $vavg[] = $a;
                                }
                                $d = $this->decimal_for_output($args[2]['measurement'], $rmin);
                                if ($d > 0 && $rmax - $rmin > 6) {
                                    $d -= 1;
                                }
                                if ($rmin == 0 && $rmax > 6) {
                                    $rmin = -1;
                                }
                                for ($i = 0; $i < $sects; $i++) {
                                    $ranges[$i] = '"' . $this->get_angle_text($i * $angle_val) . '":[' . (float)$rmin . ',' . (float)$rmax . ']';
                                }
                                $range= implode(',', $ranges);
                                $modulename = DeviceManager::get_module_name($args[2]['device_id'], $args[2]['module_id'], 'unknown');
                                $l = array();
                                if ($mode == 'yearly') {
                                    $l['key'] = $this->get_measurement_type($args[2]['measurement']) . ' - ' . ucfirst($this->get_operation_name('max', true)) . ' - ' . $modulename;
                                    $l['values'] = $vmax;
                                    $res[] = $l;
                                    $l['key'] = $this->get_measurement_type($args[2]['measurement']) . ' - ' . ucfirst($this->get_operation_name('avg', true)) . ' - ' . $modulename;
                                    $l['values'] = $vavg;
                                    $res[] = $l;
                                    $l['key'] = $this->get_measurement_type($args[2]['measurement']) . ' - ' . ucfirst($this->get_operation_name('min', true)) . ' - ' . $modulename;
                                    $l['values'] = $vmin;
                                    $res[] = $l;
                                }
                                else {
                                    $l['key'] = $this->get_measurement_type($args[2]['measurement']) . ' - ' . $modulename;
                                    $l['values'] = $vavg;
                                    $res[] = $l;
                                }
                                $set = array_values($res);;
                                $extra = array();
                                $period_name = '';
                                $period_range = 0;
                                if ($mode == 'yearly') {
                                    if ($is_month) {
                                        $now = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
                                        $now->setDate($year, $month, 1);
                                        $period_name = date_i18n('F Y', $now->getTimestamp());
                                        $period_range = 1;
                                    } elseif ($is_year) {
                                        $period_name = sprintf(__('Year %s', 'live-weather-station'), $year);
                                        $period_range = 12;
                                    } elseif ($is_mseason) {
                                        $period_name = ucfirst(Calculator::meteorologicalSeasonName($month, $station['loc_latitude'] > 0)) . ' ' . $year;
                                        if ($month == 12) {
                                            $period_name .= '~' . (string)($year + 1);
                                        }
                                        $period_range = 3;
                                    }
                                }
                                $extra['ydomain']['min'] = 0;
                                $extra['ydomain']['max'] = 0;
                                $extra['ydomain']['dmin'] = 0;
                                $extra['ydomain']['dmax'] = 0;
                                $extra['ydomain']['amin'] = 0;
                                $extra['ydomain']['amax'] = 0;
                                $extra['range'] = $range;
                                $extra['correctadd'] = (float)$rmin;
                                $extra['correctmul'] = (float)($rmax - $rmin);
                                $extra['period_name'] = $period_name;
                                $extra['period_range'] = $period_range;
                                $extra['module_name_generic'] = $this->get_module_type($module_type, false, true);
                                $extra['measurement_type'] = $this->get_measurement_type($args[2]['measurement'], false, $module_type);
                                $extra['station_name'] = $station['station_name'];
                                $extra['station_loc'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                                $extra['station_coord'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6) . ' ⁛ ';
                                $extra['station_coord'] .= $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                                $extra['station_alt'] = str_replace('&nbsp;', ' ', $this->output_value($station['loc_altitude'], 'loc_altitude', true));
                                $extra['unit'] = ' ' . $this->output_unit($args[2]['measurement'], $module_type)['unit'];
                                $extra['format'] = '.' . $d . 'f';
                                $result['extras'][] = $extra;
                                if ($json) {
                                    $result['values'][] = $this->jsonify(null, $set, $raw_json, true);
                                } else {
                                    $info = array();
                                    $info['values'] = $set;
                                    $result['values'][] = $info;
                                }
                            } catch (\Exception $ex) {
                                error_log('Oh, no: ' . $ex->getMessage());
                                $result = array();
                            }
                        }
                        else {
                            $result = array();
                        }
                    }
                    elseif ($type == 'astream' || $type == 'windrose') {
                        if (count($args) == 2) {
                            $subymin = 0;
                            $subymax = 0;
                            $subydmin = 0;
                            $subydmax = 0;
                            $subyamin = 0;
                            $subyamax = 0;
                            $minbreakdown = 0;
                            $maxbreakdown = 0;
                            if (strpos($args[1]['dot_style'], 'res-') !== false) {
                                $resolution = substr($args[1]['dot_style'], 4);
                                try {
                                    $resolution = (int)$resolution;
                                } catch (\Exception $ex) {
                                    $resolution = 10;
                                }
                            }
                            else {
                                $resolution = 10;
                            }
                            $steps = $args[2]['line_mode'];
                            if (strpos($steps, 'olor-step-') > 0) {
                                $steps = str_replace('color-step-', '', $steps);
                            }
                            $sects = str_replace('s', '', $args[1]['line_mode']);
                            $angle_val = (int)(360 / $sects);
                            $sectors = array();
                            for ($i = 0; $i < $sects; $i++) {
                                $a = ($i * $angle_val) - ($angle_val / 2);
                                $b = ($i * $angle_val) + ($angle_val / 2);
                                $sectors[] = array($a, $b);
                            }
                            if ($mode == 'yearly') {
                                $timeshift = 86400;
                            } else {
                                $timeshift = (int)(60 * $resolution);
                            }
                            $dummy='0';
                            if ($type == 'windrose') {
                                $dummy='0.0123456789';
                            }
                            $val0 = '`measure_value`';
                            $val1 = '`measure_value`';
                            $set0 = '';
                            $set1 = '';
                            if ($mode == 'yearly') {
                                $set0 = " AND `measure_set`='" . $args[1]['set'] . "'";
                                $set1 = " AND `measure_set`='" . $args[2]['set'] . "'";
                            }
                            if ($mode == 'yearly' && strtolower($args[1]['set']) == 'amp') {
                                $set0 = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                $val0 = 'ABS(MAX(`measure_value`)-MIN(`measure_value`)) as computed_value';
                            }
                            if ($mode == 'yearly' && strtolower($args[1]['set']) == 'mid') {
                                $set0 = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                $val0 = 'AVG(`measure_value`) as computed_value';
                            }
                            if ($mode == 'yearly' && strtolower($args[2]['set']) == 'amp') {
                                $set1 = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                $val1 = 'ABS(MAX(`measure_value`)-MIN(`measure_value`)) as computed_value';
                            }
                            if ($mode == 'yearly' && strtolower($args[2]['set']) == 'mid') {
                                $set1 = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                $val1 = 'AVG(`measure_value`) as computed_value';
                            }
                            $sql_angle = "SELECT `timestamp`, `module_type`, " . $val0 . " FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $args[1]['device_id'] . "' AND `module_id`='" . $args[1]['module_id'] . "' AND `measure_type`='" . $args[1]['measurement'] . "'" . $set0 . " ORDER BY `timestamp` ASC;";
                            $sql_value = "SELECT `timestamp`, `module_type`, " . $val1 . " FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $args[2]['device_id'] . "' AND `module_id`='" . $args[2]['module_id'] . "' AND `measure_type`='" . $args[2]['measurement'] . "'" . $set1 . " ORDER BY `timestamp` ASC;";
                            try {
                                $angles = $wpdb->get_results($sql_angle, ARRAY_A);
                                $values = $wpdb->get_results($sql_value, ARRAY_A);
                                $measures = array();
                                foreach ($values as $val) {
                                    $measures[$val['timestamp']] = $val;
                                }
                                if (count($values) > 0) {
                                    $module_type = $values[0]['module_type'];
                                } else {
                                    $module_type = 'NAMain';
                                }
                                $t = array();
                                for ($i = 0; $i < $sects; $i++) {
                                    $t[$i] = array();
                                }
                                $start = true;
                                foreach ($angles as $angle) {
                                    $v = $angle['measure_value'];
                                    $ts = $angle['timestamp'];
                                    if (array_key_exists($ts, $measures)) {
                                        if (array_key_exists('measure_value', $measures[$ts])) {
                                            $m = $measures[$ts]['measure_value'];
                                            if ($start) {
                                                if ($this->can_be_negative($args[2]['measurement'])) {
                                                    $minbreakdown = $m;
                                                }
                                                else {
                                                    $minbreakdown = 0;
                                                }
                                                $maxbreakdown = $m;
                                                $start = false;
                                            }
                                            if ($m < $minbreakdown) {
                                                $minbreakdown = $m;
                                            }
                                            if ($m > $maxbreakdown) {
                                                $maxbreakdown = $m;
                                            }
                                        }
                                        elseif (array_key_exists('computed_value', $measures[$ts])) {
                                            $m = $measures[$ts]['computed_value'];
                                            if ($start) {
                                                if ($this->can_be_negative($args[2]['measurement'])) {
                                                    $minbreakdown = $m;
                                                }
                                                else {
                                                    $minbreakdown = 0;
                                                }
                                                $maxbreakdown = $m;
                                                $start = false;
                                            }
                                            if ($m < $minbreakdown) {
                                                $minbreakdown = $m;
                                            }
                                            if ($m > $maxbreakdown) {
                                                $maxbreakdown = $m;
                                            }
                                        }
                                        else {
                                            $m = $dummy;
                                        }
                                    } else {
                                        $m = $dummy;
                                    }
                                    for ($i = 0; $i < $sects; $i++) {
                                        $a = $sectors[$i][0];
                                        $b = $sectors[$i][1];
                                        if ($a < 0) {
                                            $v = $v - 360;
                                        }
                                        if ($b > 360) {
                                            $v = $v + 360;
                                        }
                                        $d = array();
                                        if ($v < $b && $v >= $a) {
                                            $d['measure_value'] = $m;
                                        } else {
                                            $d['measure_value'] = $dummy;
                                        }
                                        if ($a < 0) {
                                            $v = $v + 360;
                                        }
                                        if ($b > 360) {
                                            $v = $v - 360;
                                        }
                                        $d['timestamp'] = $angle['timestamp'];
                                        $d['module_type'] = $module_type;
                                        $t[$i][$d['timestamp']] = $d;
                                    }
                                }
                                if ($timescale != 'adaptative') {
                                    if ($mode == 'daily') {
                                        $__start = (integer)self::get_js_datetime_from_mysql_utc($min, $station['loc_timezone'], '');
                                        $__end = (integer)self::get_js_datetime_from_mysql_utc($max, $station['loc_timezone'], '');
                                    }
                                    if ($mode == 'yearly') {
                                        $__start = (integer)self::get_js_date_from_mysql_utc($min, $station['loc_timezone'], '');
                                        $__end = (integer)self::get_js_date_from_mysql_utc($max, $station['loc_timezone'], '');
                                    }
                                    $d = $__start;
                                    while ($d <= $__end) {
                                        if ($mode == 'yearly') {
                                            $__date = date('Y-m-d', $d);
                                        } else {
                                            $__date = date('Y-m-d H:i:s', $d);
                                        }
                                        for ($i = 0; $i < $sects; $i++) {
                                            if (!array_key_exists($__date, $t[$i])) {
                                                $e = array();
                                                $e['timestamp'] = $__date;
                                                $e['module_type'] = $module_type;
                                                $e['measure_value'] = $dummy;
                                                $t[$i][$e['timestamp']] = $e;
                                            }
                                        }
                                        $d += $timeshift;
                                    }
                                }
                                for ($i = 0; $i < $sects; $i++) {
                                    ksort($t[$i]);
                                }
                                if ($type == 'windrose') {
                                    $mes_typ = $args[2]['measurement'];
                                    $stepval = ($maxbreakdown - $minbreakdown) / $steps;
                                    $step = $this->output_value($stepval, $mes_typ, false, false, $module_type);
                                    $unit = $this->output_unit($mes_typ, $module_type)['unit'];
                                    $adjust = ($step > 4);
                                    $breakdown = array();
                                    $breakdownlegend = array();
                                    $last = $minbreakdown;
                                    for ($i = 1; $i <=$steps; $i++) {
                                        $val = $this->output_value($minbreakdown + ($i * $stepval), $mes_typ, false, false, $module_type);
                                        if ($adjust) {
                                            $val = round($val, 0);
                                        }
                                        $origin = $this->convert_value($val, $this->output_unit($mes_typ, $module_type)['ref']);
                                        $breakdown[] = $origin;
                                        if ($i == 1) {
                                            $breakdownlegend[] = '< ' . $val . $unit;
                                            $last = $val;
                                        }
                                        elseif ($i == $steps) {
                                            $breakdownlegend[] = '> ' . $last . $unit;
                                        }
                                        else {
                                            $breakdownlegend[] = $last . ' / ' . $val . $unit;
                                            $last = $val;
                                        }
                                    }
                                    $values = array();
                                    $cpt = 0;
                                    foreach ($t as $key => $s) {
                                        $sub = array();
                                        foreach ($breakdown as $b) {
                                            $sub[] = 0;
                                        }
                                        foreach ($s as $rec) {
                                            $a = $rec['measure_value'];
                                            if ($a != $dummy) {
                                                $done = false;
                                                foreach ($breakdown as $k=>$b) {
                                                    if ($a <= $b) {
                                                        $sub[$k] += 1;
                                                        $cpt += 1;
                                                        $done = true;
                                                        break;
                                                    }
                                                }
                                                if (!$done) {
                                                    $sub[count($sub)-1] += 1;
                                                    $cpt += 1;
                                                }
                                            }
                                        }
                                        $values[] = array('axis'=>'"' . $this->get_angle_text($key * $angle_val) . '"', 'values'=>$sub);
                                    }
                                    $set = array();
                                    foreach ($values as $key => $val) {
                                        $sub = array();
                                        foreach ($val['values'] as $v) {
                                            if ($cpt > 0) {
                                                $sub[] = $v / $cpt;
                                            }
                                            else {
                                                $sub[] = 0;
                                            }

                                        }
                                        $set[] = array('axis' => $val['axis'], 'values' => $sub);
                                    }
                                    $jset = str_replace('\"', '', $this->jsonify(null, $set, $raw_json, true));
                                    $jlegend = str_replace('\/', '/', $this->jsonify(null, $breakdownlegend, $raw_json, true));
                                    $final = '{' . '"legend":[' . $jlegend . '], ' . '"series":[' . $jset . ']}';
                                    $info = array();
                                    $module_name = DeviceManager::get_module_name($args[2]['device_id'], $args[2]['module_id']);
                                    $info['key'] = $module_name;
                                    $extra = array();
                                    $period_name = '';
                                    $period_range = 0;
                                    if ($mode == 'yearly') {
                                        if ($is_month) {
                                            $now = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
                                            $now->setDate($year, $month, 1);
                                            $period_name = date_i18n('F Y', $now->getTimestamp());
                                            $period_range = 1;
                                        } elseif ($is_year) {
                                            $period_name = sprintf(__('Year %s', 'live-weather-station'), $year);
                                            $period_range = 12;
                                        } elseif ($is_mseason) {
                                            $period_name = ucfirst(Calculator::meteorologicalSeasonName($month, $station['loc_latitude'] > 0)) . ' ' . $year;
                                            if ($month == 12) {
                                                $period_name .= '~' . (string)($year + 1);
                                            }
                                            $period_range = 3;
                                        }
                                        $extra['set_name'] = ucfirst($this->get_operation_name($args[2]['set'], true));
                                    }
                                    $extra['ydomain']['min'] = $subymin;
                                    $extra['ydomain']['max'] = $subymax;
                                    $extra['ydomain']['dmin'] = $subydmin;
                                    $extra['ydomain']['dmax'] = $subydmax;
                                    $extra['ydomain']['amin'] = $subyamin;
                                    $extra['ydomain']['amax'] = $subyamax;
                                    $extra['period_name'] = $period_name;
                                    $extra['info_key'] = $info['key'];
                                    $extra['period_range'] = $period_range;
                                    $extra['raw_measurement_type'] = $args[2]['measurement'];
                                    $extra['measurement_type'] = $this->get_measurement_type($args[2]['measurement'], false, $module_type);
                                    $extra['module_type'] = $module_type;
                                    $extra['module_name_generic'] = $this->get_module_type($module_type, false, true);
                                    $extra['module_name'] = $module_name;
                                    $extra['station_name'] = $station['station_name'];
                                    $extra['station_loc'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                                    $extra['station_coord'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6) . ' ⁛ ';
                                    $extra['station_coord'] .= $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                                    $extra['station_alt'] = str_replace('&nbsp;', ' ', $this->output_value($station['loc_altitude'], 'loc_altitude', true));
                                    $extra['unit'] = $this->output_unit($args[2]['measurement'], $module_type);
                                    $result['extras'][] = $extra;
                                    $result['legend']['unit'] = $this->output_unit($args[2]['measurement'], $module_type);
                                    if ($json) {
                                        $result['values'] = $final;
                                    } else {
                                        $info['values'] = $set;
                                        $result['values'][] = $breakdownlegend;
                                    }
                                }
                                if ($type == 'astream') {
                                    foreach ($t as $key => $s) {
                                        $set = array();
                                        $substart = true;
                                        foreach ($s as $a) {
                                            $module_type = $a['module_type'];
                                            if ($mode == 'daily') {
                                                $a['timestamp'] = self::get_js_datetime_from_mysql_utc($a['timestamp'], $station['loc_timezone'], $end_date);
                                            }
                                            if ($mode == 'yearly') {
                                                $a['timestamp'] = self::get_js_date_from_mysql_utc($a['timestamp'], $station['loc_timezone'], $end_date);
                                            }
                                            $mes_typ = $args[2]['measurement'];
                                            $a['measure_value'] = $this->output_value($a['measure_value'], $mes_typ, false, false, $a['module_type']);
                                            if ($start) {
                                                $ymin = $a['measure_value'];
                                                $ymax = $a['measure_value'];
                                                $ydmin = $this->get_measurement_min($mes_typ, $module_type);
                                                $ydmax = $this->get_measurement_max($mes_typ, $module_type);
                                                $yamax = $this->get_measurement_alarm_max($mes_typ, $module_type);
                                                $yamin = $this->get_measurement_alarm_min($mes_typ, $module_type);
                                                $start = false;
                                            }
                                            if ($a['measure_value'] > $ymax) {
                                                $ymax = $a['measure_value'];
                                            }
                                            if ($a['measure_value'] < $ymin) {
                                                $ymin = $a['measure_value'];
                                            }
                                            if ($substart) {
                                                $subymin = $a['measure_value'];
                                                $subymax = $a['measure_value'];
                                                $subydmin = $this->get_measurement_min($mes_typ, $module_type);
                                                $subydmax = $this->get_measurement_max($mes_typ, $module_type);
                                                $subyamax = $this->get_measurement_alarm_max($mes_typ, $module_type);
                                                $subyamin = $this->get_measurement_alarm_min($mes_typ, $module_type);
                                                $substart = false;
                                            }
                                            if ($a['measure_value'] > $subymax) {
                                                $subymax = $a['measure_value'];
                                            }
                                            if ($a['measure_value'] < $subymin) {
                                                $subymin = $a['measure_value'];
                                            }
                                            $set[] = $a;
                                        }
                                        $info = array();
                                        $module_name = $this->get_angle_full_text($key * $angle_val);
                                        $info['key'] = $module_name;
                                        $extra = array();
                                        $period_name = '';
                                        $period_range = 0;
                                        if ($mode == 'yearly') {
                                            if ($is_month) {
                                                $now = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
                                                $now->setDate($year, $month, 1);
                                                $period_name = date_i18n('F Y', $now->getTimestamp());
                                                $period_range = 1;
                                            } elseif ($is_year) {
                                                $period_name = sprintf(__('Year %s', 'live-weather-station'), $year);
                                                $period_range = 12;
                                            } elseif ($is_mseason) {
                                                $period_name = ucfirst(Calculator::meteorologicalSeasonName($month, $station['loc_latitude'] > 0)) . ' ' . $year;
                                                if ($month == 12) {
                                                    $period_name .= '~' . (string)($year + 1);
                                                }
                                                $period_range = 3;
                                            }
                                            $extra['set_name'] = ucfirst($this->get_operation_name($args[2]['set'], true));
                                        }
                                        $extra['ydomain']['min'] = $subymin;
                                        $extra['ydomain']['max'] = $subymax;
                                        $extra['ydomain']['dmin'] = $subydmin;
                                        $extra['ydomain']['dmax'] = $subydmax;
                                        $extra['ydomain']['amin'] = $subyamin;
                                        $extra['ydomain']['amax'] = $subyamax;
                                        $extra['period_name'] = $period_name;
                                        $extra['info_key'] = $info['key'];
                                        $extra['period_range'] = $period_range;
                                        $extra['raw_measurement_type'] = $args[2]['measurement'];
                                        $extra['measurement_type'] = $this->get_measurement_type($args[2]['measurement'], false, $module_type);
                                        $extra['module_type'] = $module_type;
                                        $extra['module_name_generic'] = $this->get_module_type($module_type, false, true);
                                        $extra['module_name'] = $module_name;
                                        $extra['station_name'] = $station['station_name'];
                                        $extra['station_loc'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                                        $extra['station_coord'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6) . ' ⁛ ';
                                        $extra['station_coord'] .= $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                                        $extra['station_alt'] = str_replace('&nbsp;', ' ', $this->output_value($station['loc_altitude'], 'loc_altitude', true));
                                        $extra['unit'] = $this->output_unit($args[2]['measurement'], $module_type);
                                        $result['extras'][] = $extra;
                                        $result['legend']['unit'] = $this->output_unit($args[2]['measurement'], $module_type);
                                        $classes = array();
                                        $classes[] = 'lws-serie-' . $key;
                                        if (count($classes) > 0) {
                                            $info['classed'] = implode(' ', $classes);
                                        }
                                        if ($json) {
                                            $result['values'][] = $this->jsonify($info, $set, $raw_json);
                                        } else {
                                            $info['values'] = $set;
                                            $result['values'][] = $info;
                                        }
                                    }
                                }
                            } catch (\Exception $ex) {
                                error_log('Oh, no: ' . $ex->getMessage());
                                if ($type == 'windrose') {
                                    $result = '[]';
                                }
                                else {
                                    $result = array();
                                }
                            }
                        }
                        else {
                            $result = array();
                            if ($type == 'windrose') {
                                $result['values'] = '[]';
                            }
                        }
                    }
                    elseif ($type == 'distributionrc') {
                        if (count($args) > 0) {
                            foreach ($args as $arg) {
                                if (strpos($arg['dot_style'], 'res-') !== false) {
                                    $resolution = substr($arg['dot_style'], 4);
                                    try {
                                        $resolution = (int)$resolution;
                                    } catch (\Exception $ex) {
                                        $resolution = 10;
                                    }
                                }
                                else {
                                    $resolution = 10;
                                }
                                $sects = str_replace('s', '', $arg['line_mode']);
                                break;
                            }
                            $angle_val = (int)(360 / $sects);
                            $sectors = array();
                            for ($i = 0; $i < $sects; $i++) {
                                $a = ($i * $angle_val) - ($angle_val / 2);
                                $b = ($i * $angle_val) + ($angle_val / 2);
                                $l = array();
                                $l['min'] = $a;
                                $l['max'] = $b;
                                $l['cnt'] = 0;
                                $sectors[] = $l;
                            }
                            $res = array();
                            $mes = '';
                            foreach ($args as $arg) {
                                $mes .= $arg['measurement'] . ';';
                                $val = '`measure_value`';
                                $set = '';
                                if ($mode == 'yearly') {
                                    $set = " AND `measure_set`='" . $arg['set'] . "'";
                                }
                                if ($mode == 'yearly' && strtolower($arg['set']) == 'mid') {
                                    $set = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                    $val = 'AVG(`measure_value`) as computed_value';
                                }
                                $sql_angle = "SELECT `timestamp`, `module_type`, " . $val . " FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "'" . $set . " ORDER BY `timestamp` ASC;";
                                $angles = $wpdb->get_results($sql_angle, ARRAY_A);
                                if (count($angles) > 0) {
                                    $module_type = $angles[0]['module_type'];
                                }
                                else {
                                    $module_type = 'NAMain';
                                }
                                $arg['line'] = array();
                                foreach ($angles as $angle) {
                                    $v = 0;
                                    if (array_key_exists('measure_value', $angle)) {
                                        $v = $angle['measure_value'];
                                    }
                                    elseif (array_key_exists('computed_value', $angle)) {
                                        $v = $angle['computed_value'];
                                    }
                                    for ($i = 0; $i < $sects; $i++) {
                                        $w = $v;
                                        if ($sectors[$i]['min'] < 0) {
                                            $w = $v - 360;
                                        }
                                        if ($sectors[$i]['max'] > 360) {
                                            $w = $v + 360;
                                        }
                                        if ($w < $sectors[$i]['max'] && $w >= $sectors[$i]['min']) {
                                            $sectors[$i]['cnt'] += 1;
                                            break;
                                        }
                                    }
                                }
                                $total = 0;
                                for ($i = 0; $i < $sects; $i++) {
                                    $total += $sectors[$i]['cnt'];
                                }
                                $t = array();
                                for ($i = 0; $i < $sects; $i++) {
                                    if ($total > 0) {
                                        $v = round($sectors[$i]['cnt'] / $total, 2);
                                    }
                                    else {
                                        $v = 0.00;
                                    }
                                    $a = array();
                                    $a['axis'] = $this->get_angle_text($i * $angle_val);
                                    $a['value'] = $v;
                                    $t[] = $a;
                                }
                                $modulename = DeviceManager::get_module_name($arg['device_id'], $arg['module_id'], 'unknown');
                                $l = array();
                                if ($mode == 'yearly') {
                                    $l['key'] = $this->get_measurement_type($arg['measurement']) . ' - ' . ucfirst($this->get_operation_name($arg['set'], true)) . ' - ' . $modulename;
                                }
                                else {
                                    $l['key'] = $this->get_measurement_type($arg['measurement']) . ' - ' . $modulename;
                                }
                                $l['values'] = $t;
                                $res[] = $l;
                            }
                            $set = array_values($res);;
                            $extra = array();
                            $period_name = '';
                            $period_range = 0;
                            if ($mode == 'yearly') {
                                if ($is_month) {
                                    $now = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
                                    $now->setDate($year, $month, 1);
                                    $period_name = date_i18n('F Y', $now->getTimestamp());
                                    $period_range = 1;
                                } elseif ($is_year) {
                                    $period_name = sprintf(__('Year %s', 'live-weather-station'), $year);
                                    $period_range = 12;
                                } elseif ($is_mseason) {
                                    $period_name = ucfirst(Calculator::meteorologicalSeasonName($month, $station['loc_latitude'] > 0)) . ' ' . $year;
                                    if ($month == 12) {
                                        $period_name .= '~' . (string)($year + 1);
                                    }
                                    $period_range = 3;
                                }
                            }
                            $extra['ydomain']['min'] = 0;
                            $extra['ydomain']['max'] = 0;
                            $extra['ydomain']['dmin'] = 0;
                            $extra['ydomain']['dmax'] = 0;
                            $extra['ydomain']['amin'] = 0;
                            $extra['ydomain']['amax'] = 0;
                            $extra['period_name'] = $period_name;
                            $extra['period_range'] = $period_range;
                            $extra['measurement_type'] = $mes;
                            $extra['station_name'] = $station['station_name'];
                            $extra['station_loc'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                            $extra['station_coord'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6) . ' ⁛ ';
                            $extra['station_coord'] .= $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                            $extra['station_alt'] = str_replace('&nbsp;', ' ', $this->output_value($station['loc_altitude'], 'loc_altitude', true));
                            $extra['module_name_generic'] = $this->get_module_type($module_type, false, true);
                            $extra['unit'] = '';
                            $extra['format'] = '%';
                            $result['extras'][] = $extra;
                            if ($json) {
                                $result['values'][] = $this->jsonify(null, $set, $raw_json, true);
                            } else {
                                $info = array();
                                $info['values'] = $set;
                                $result['values'][] = $info;
                            }
                        }
                        else {
                            $result = array();
                        }
                    }
                    elseif ($type == 'cstick') {
                        if (strpos($args[1]['set'], '|') > 0) {
                            $op = explode('|', $args[1]['set']);
                        }
                        else {
                            $op = array('avg', 'mid');
                        }
                        $subymin = 0;
                        $subymax = 0;
                        $subydmin = 0;
                        $subydmax = 0;
                        $subyamin = 0;
                        $subyamax = 0;
                        $select = " AND (`measure_set`='min' OR `measure_set`='max' OR `measure_set`='avg' OR `measure_set`='med')";
                        $sql = "SELECT `timestamp`, `module_type`, `measure_set`, `measure_value` FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "'" . $select . " ORDER BY `timestamp` ASC;";
                        $rows = $wpdb->get_results($sql, ARRAY_A);
                        $values = array();
                        try {
                            if (count($rows) > 0) {
                                foreach ($rows as $row) {
                                    $values[$row['timestamp']][$row['measure_set']] = $this->output_value($row['measure_value'], $arg['measurement'], false, false, $row['module_type']);
                                }
                                $module_type = $row['module_type'];
                                foreach ($values as $key=>$row) {
                                    if (array_key_exists('max', $row) && array_key_exists('min', $row)) {
                                        $values[$key]['mid'] = $this->output_value($row['min'] + (($row['max'] - $row['min']) / 2), $arg['measurement'], false, false, $module_type);
                                    }
                                }
                            }
                            else {
                                $module_type = 'NAMain';
                            }
                            $set = array();
                            $start = true;
                            foreach ($values as $key=>$row) {
                                $date = self::get_js_datetime_from_mysql_utc($key, $station['loc_timezone'], $end_date);
                                if (array_key_exists('max', $row) && array_key_exists('min', $row) && array_key_exists($op[0], $row) && array_key_exists($op[1], $row)) {
                                    $high = $row['max'];
                                    $low = $row['min'];
                                    $close = $row[$op[1]];
                                    $open = $row[$op[0]];
                                    $set[] = array('date'=>$date, 'open'=>$open, 'high'=>$high, 'low'=>$low, 'close'=>$close);
                                    if ($start) {
                                        $ymin = $low;
                                        $ymax = $high;
                                        $ydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                                        $ydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                                        $yamax = $this->get_measurement_alarm_max($arg['measurement'], $module_type);
                                        $yamin = $this->get_measurement_alarm_min($arg['measurement'], $module_type);
                                        $start = false;
                                    }
                                    if ($high > $ymax) {
                                        $ymax = $high;
                                    }
                                    if ($low < $ymin) {
                                        $ymin = $low;
                                    }
                                    if ($high > $subymax) {
                                        $subymax = $high;
                                    }
                                    if ($low < $subymin) {
                                        $subymin = $low;
                                    }
                                }
                            }
                            $info = array();
                            if (array_key_exists($arg['module_id'], $station['modules_names'])) {
                                $module_name = str_replace(array('[', ']'), array('', ''), $station['modules_names'][$arg['module_id']]);
                            } else {
                                $module_name = $this->get_module_type($module_type, false, true);
                            }
                            $extra = array();
                            $period_name = '';
                            $period_range = 0;
                            if ($mode == 'yearly') {
                                if ($is_month) {
                                    $now = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
                                    $now->setDate($year, $month, 1);
                                    $period_name = date_i18n('F Y', $now->getTimestamp());
                                    $period_range = 1;
                                } elseif ($is_year) {
                                    $period_name = sprintf(__('Year %s', 'live-weather-station'), $year);
                                    $period_range = 12;
                                } elseif ($is_mseason) {
                                    $period_name = ucfirst(Calculator::meteorologicalSeasonName($month, $station['loc_latitude'] > 0)) . ' ' . $year;
                                    if ($month == 12) {
                                        $period_name .= '~' . (string)($year + 1);
                                    }
                                    $period_range = 3;
                                }
                                $extra['set_name'] = ucfirst($this->get_operation_name($arg['set'], true));
                            }
                            $subydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                            $subydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                            $subyamax = $this->get_measurement_alarm_max($arg['measurement'], $module_type);
                            $subyamin = $this->get_measurement_alarm_min($arg['measurement'], $module_type);
                            if ($this->get_measurement_max($arg['measurement'], $module_type) > $ydmax) {
                                $ydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_min($arg['measurement'], $module_type) < $ydmin) {
                                $ydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_alarm_max($arg['measurement'], $module_type) > $yamax) {
                                $yamax = $this->get_measurement_alarm_max($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_alarm_min($arg['measurement'], $module_type) < $yamin) {
                                $yamin = $this->get_measurement_alarm_min($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_max($arg['measurement'], $module_type) > $subydmax) {
                                $subydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_min($arg['measurement'], $module_type) < $subydmin) {
                                $subydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_alarm_max($arg['measurement'], $module_type) > $subyamax) {
                                $subyamax = $this->get_measurement_alarm_max($arg['measurement'], $module_type);
                            }
                            if ($this->get_measurement_alarm_min($arg['measurement'], $module_type) < $subyamin) {
                                $subyamin = $this->get_measurement_alarm_min($arg['measurement'], $module_type);
                            }

                            $extra['ydomain']['min'] = $subymin;
                            $extra['ydomain']['max'] = $subymax;
                            $extra['ydomain']['dmin'] = $subydmin;
                            $extra['ydomain']['dmax'] = $subydmax;
                            $extra['ydomain']['amin'] = $subyamin;
                            $extra['ydomain']['amax'] = $subyamax;
                            $extra['period_name'] = $period_name;
                            $extra['open'] = $this->get_operation_name($op[0]);
                            $extra['close'] = $this->get_operation_name($op[1]);
                            $extra['period_range'] = $period_range;
                            $extra['raw_measurement_type'] = $arg['measurement'];
                            $extra['measurement_type'] = $this->get_measurement_type($arg['measurement'], false, $module_type);
                            $extra['module_type'] = $module_type;
                            $extra['module_name_generic'] = $this->get_module_type($module_type, false, true);
                            $extra['module_name'] = $module_name;
                            $extra['station_name'] = $station['station_name'];
                            $extra['station_loc'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                            $extra['station_coord'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6) . ' ⁛ ';
                            $extra['station_coord'] .= $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                            $extra['station_alt'] = str_replace('&nbsp;', ' ', $this->output_value($station['loc_altitude'], 'loc_altitude', true));
                            $extra['unit'] = $this->output_unit($arg['measurement'], $module_type);
                            $result['extras'][] = $extra;
                            $result['legend']['unit'] = $this->output_unit($arg['measurement'], $module_type);
                            if ($json) {
                                $result['values'][] = $this->jsonify($info, $set, false, true);
                            } else {
                                $info['values'] = $set;
                                $result['values'][] = $info;
                            }

                        } catch (\Exception $ex) {
                            error_log('Oh, no: ' . $ex->getMessage());
                            $result = array();
                        }
                    }
                    else {
                        $i = 1;
                        foreach ($args as $arg) {
                            $subymin = 0;
                            $subymax = 0;
                            $subydmin = 0;
                            $subydmax = 0;
                            $subyamin = 0;
                            $subyamax = 0;
                            if (strpos($arg['module_id'], ':') == 2) {
                                $module_type = 'NAMain';
                                $set = '';
                                $val = '`measure_value`';
                                if ($mode == 'yearly') {
                                    $set = " AND `measure_set`='" . $arg['set'] . "'";
                                }
                                if ($mode == 'yearly' && strtolower($arg['set']) == 'amp') {
                                    $set = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                    $val = 'ABS(MAX(`measure_value`)-MIN(`measure_value`)) as computed_value';
                                }
                                if ($mode == 'yearly' && strtolower($arg['set']) == 'mid') {
                                    $set = " AND (`measure_set`='min' OR `measure_set`='max') GROUP BY `timestamp`";
                                    $val = 'AVG(`measure_value`) as computed_value';
                                }
                                $sql = "SELECT `timestamp`, `module_type`, " . $val . " FROM " . $table_name . " WHERE `timestamp`>='" . $min . "' AND `timestamp`<='" . $max . "' AND `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "'" . $set . " ORDER BY `timestamp` ASC;";
                                try {
                                    $query = (array)$wpdb->get_results($sql);
                                    $query_a = (array)$query;
                                    if ((($type == 'bcline' && $i == 1) || $type == 'bar' || $type == 'bars' || $type == 'sareas') && $mode == 'yearly' && $timescale != 'adaptative') {
                                        $dummy = '0';
                                        if ($type == 'sareas') {
                                            $d = $this->decimal_for_output($arg['measurement']);
                                            if ($d > 0) {
                                                $dummy = '0.';
                                            } else {
                                                $dummy = '1';
                                            }
                                            if ($d > 1) {
                                                for ($i = 1; $i < $d; $i++) {
                                                    $dummy .= '0';
                                                }
                                            }
                                            if ($d > 0) {
                                                $dummy .= '1';
                                            }
                                        }
                                        if (count($query_a) > 0) {
                                            $module_type = (array)($query_a[0]);
                                            $module_type = $module_type['module_type'];
                                        }
                                        $t = array();
                                        foreach ($query_a as $val) {
                                            $a = (array)$val;
                                            $t[$a['timestamp']] = $a;
                                        }
                                        $__start = (integer)self::get_js_date_from_mysql_utc($min, $station['loc_timezone'], '');
                                        $__end = (integer)self::get_js_date_from_mysql_utc($max, $station['loc_timezone'], '');
                                        $d = $__start;
                                        while ($d <= $__end) {
                                            $__date = date('Y-m-d', $d);
                                            if (!array_key_exists($__date, $t)) {
                                                $a = array();
                                                $a['timestamp'] = $__date;
                                                $a['module_type'] = $module_type;
                                                $a['measure_value'] = $dummy;
                                                $t[$a['timestamp']] = $a;
                                            }
                                            $d += 86400;
                                        }
                                        ksort($t);
                                        $query_a = array();
                                        foreach ($t as $key => $val) {
                                            $query_a[] = $val;
                                        }
                                    }
                                    $set = array();
                                    $substart = true;
                                    foreach ($query_a as $val) {
                                        $a = (array)$val;
                                        $module_type = $a['module_type'];
                                        if ($mode == 'daily') {
                                            $a['timestamp'] = self::get_js_datetime_from_mysql_utc($a['timestamp'], $station['loc_timezone'], $end_date);
                                        }
                                        if ($mode == 'yearly') {
                                            $a['timestamp'] = self::get_js_date_from_mysql_utc($a['timestamp'], $station['loc_timezone'], $end_date);
                                        }
                                        if (!array_key_exists('measure_value', $a)) {
                                            if (array_key_exists('computed_value', $a)) {
                                                $a['measure_value'] = $a['computed_value'];
                                            } else {
                                                continue;
                                            }
                                        }
                                        $a['measure_value'] = $this->output_value($a['measure_value'], $arg['measurement'], false, false, $a['module_type']);
                                        if ($start) {
                                            $ymin = $a['measure_value'];
                                            $ymax = $a['measure_value'];
                                            $ydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                                            $ydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                                            $yamax = $this->get_measurement_alarm_max($arg['measurement'], $module_type);
                                            $yamin = $this->get_measurement_alarm_min($arg['measurement'], $module_type);
                                            $start = false;
                                        }
                                        if ($a['measure_value'] > $ymax) {
                                            $ymax = $a['measure_value'];
                                        }
                                        if ($a['measure_value'] < $ymin) {
                                            $ymin = $a['measure_value'];
                                        }
                                        if ($substart) {
                                            $subymin = $a['measure_value'];
                                            $subymax = $a['measure_value'];
                                            $subydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                                            $subydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                                            $subyamax = $this->get_measurement_alarm_max($arg['measurement'], $module_type);
                                            $subyamin = $this->get_measurement_alarm_min($arg['measurement'], $module_type);
                                            $substart = false;
                                        }
                                        if ($a['measure_value'] > $subymax) {
                                            $subymax = $a['measure_value'];
                                        }
                                        if ($a['measure_value'] < $subymin) {
                                            $subymin = $a['measure_value'];
                                        }
                                        $set[] = $a;
                                    }

                                    if (count($query_a) > 0) {
                                        $a = (array)$query_a[0];
                                        if ($this->get_measurement_max($arg['measurement'], $module_type) > $ydmax) {
                                            $ydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                                        }
                                        if ($this->get_measurement_min($arg['measurement'], $module_type) < $ydmin) {
                                            $ydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                                        }
                                        if ($this->get_measurement_alarm_max($arg['measurement'], $module_type) > $yamax) {
                                            $yamax = $this->get_measurement_alarm_max($arg['measurement'], $a['module_type']);
                                        }
                                        if ($this->get_measurement_alarm_min($arg['measurement'], $module_type) < $yamin) {
                                            $yamin = $this->get_measurement_alarm_min($arg['measurement'], $a['module_type']);
                                        }
                                        if ($this->get_measurement_max($arg['measurement'], $module_type) > $subydmax) {
                                            $subydmax = $this->get_measurement_max($arg['measurement'], $module_type);
                                        }
                                        if ($this->get_measurement_min($arg['measurement'], $module_type) < $subydmin) {
                                            $subydmin = $this->get_measurement_min($arg['measurement'], $module_type);
                                        }
                                        if ($this->get_measurement_alarm_max($arg['measurement'], $module_type) > $subyamax) {
                                            $subyamax = $this->get_measurement_alarm_max($arg['measurement'], $a['module_type']);
                                        }
                                        if ($this->get_measurement_alarm_min($arg['measurement'], $module_type) < $subyamin) {
                                            $subyamin = $this->get_measurement_alarm_min($arg['measurement'], $a['module_type']);
                                        }
                                    }
                                } catch (\Exception $ex) {
                                    $set = array();
                                }
                                $info = array();
                                if ($type == 'bcline' && $i == 1) {
                                    $info['bar'] = true;
                                }
                                if ($type == 'doubleline') {
                                    $info['yAxis'] = $i;
                                    $info['type'] = 'line';
                                    $info['unit'] = $this->output_unit($arg['measurement'], $module_type)['unit'];
                                }
                                if (($type == 'bars' || $type == 'sareas') && $arg['line_mode'] == 'single') {
                                    $info['nonStackable'] = true;
                                }
                                if (array_key_exists($arg['module_id'], $station['modules_names'])) {
                                    $module_name = str_replace(array('[', ']'), array('', ''), $station['modules_names'][$arg['module_id']]);
                                } else {
                                    $module_name = $this->get_module_type($module_type, false, true);
                                }
                                if ($mode == 'daily') {
                                    if ($identical) {
                                        $info['key'] = $module_name;
                                    } elseif ($similar) {
                                        if ($module_type != 'NAComputed') {
                                            $info['key'] = $this->get_measurement_type($arg['measurement'], false, $module_type) . ' (' . $module_name . ')';
                                        } else {
                                            $info['key'] = $this->get_measurement_type($arg['measurement'], false, $module_type);
                                        }
                                    } else {
                                        $info['key'] = $this->get_measurement_type($arg['measurement'], false, $module_type);
                                    }
                                }
                                if ($mode == 'yearly') {
                                    if ($identical) {
                                        if ($identical_module) {
                                            $info['key'] = ucfirst($this->get_operation_name($arg['set'], true));
                                        } else {
                                            $info['key'] = $module_name . ' - ' . ucfirst($this->get_operation_name($arg['set'], true));
                                        }
                                    } elseif ($similar) {
                                        if ($module_type != 'NAComputed') {
                                            $info['key'] = $this->get_measurement_type($arg['measurement'], false, $module_type) . ' (' . $module_name . ') - ' . ucfirst($this->get_operation_name($arg['set'], true));
                                        } else {
                                            $info['key'] = $this->get_measurement_type($arg['measurement'], false, $module_type);
                                        }
                                    } else {
                                        $info['key'] = $this->get_measurement_type($arg['measurement'], false, $module_type) . ' - ' . ucfirst($this->get_operation_name($arg['set'], true));
                                    }
                                }

                                $extra = array();
                                $period_name = '';
                                $period_range = 0;
                                if ($mode == 'yearly') {
                                    if ($is_month) {
                                        $now = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
                                        $now->setDate($year, $month, 1);
                                        $period_name = date_i18n('F Y', $now->getTimestamp());
                                        $period_range = 1;
                                    } elseif ($is_year) {
                                        $period_name = sprintf(__('Year %s', 'live-weather-station'), $year);
                                        $period_range = 12;
                                    } elseif ($is_mseason) {
                                        $period_name = ucfirst(Calculator::meteorologicalSeasonName($month, $station['loc_latitude'] > 0)) . ' ' . $year;
                                        if ($month == 12) {
                                            $period_name .= '~' . (string)($year + 1);
                                        }
                                        $period_range = 3;
                                    }
                                    $extra['set_name'] = ucfirst($this->get_operation_name($arg['set'], true));
                                }
                                $extra['ydomain']['min'] = $subymin;
                                $extra['ydomain']['max'] = $subymax;
                                $extra['ydomain']['dmin'] = $subydmin;
                                $extra['ydomain']['dmax'] = $subydmax;
                                $extra['ydomain']['amin'] = $subyamin;
                                $extra['ydomain']['amax'] = $subyamax;
                                $extra['period_name'] = $period_name;
                                $extra['info_key'] = $info['key'];
                                $extra['period_range'] = $period_range;
                                $extra['raw_measurement_type'] = $arg['measurement'];
                                $extra['measurement_type'] = $this->get_measurement_type($arg['measurement'], false, $module_type);
                                $extra['module_type'] = $module_type;
                                $extra['module_name_generic'] = $this->get_module_type($module_type, false, true);
                                $extra['module_name'] = $module_name;
                                $extra['station_name'] = $station['station_name'];
                                $extra['station_loc'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                                $extra['station_coord'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6) . ' ⁛ ';
                                $extra['station_coord'] .= $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                                $extra['station_alt'] = str_replace('&nbsp;', ' ', $this->output_value($station['loc_altitude'], 'loc_altitude', true));
                                $extra['unit'] = $this->output_unit($arg['measurement'], $module_type);
                                $result['extras'][] = $extra;
                                $result['legend']['unit'] = $this->output_unit($arg['measurement'], $module_type);
                                $classes = array();
                                $classes[] = 'lws-serie-' . $i;
                                if (array_key_exists('line_mode', $arg) && $type != 'bar' && $type != 'bars' && $type != 'sareas') {
                                    if ($arg['line_mode'] == 'area' || $arg['line_mode'] == 'arealine') {
                                        $info['area'] = true;
                                    }
                                    if ($arg['line_style'] == 'dashed') {
                                        $classes[] = 'lws-dashed-line';
                                    }
                                    if ($arg['line_style'] == 'dotted') {
                                        $classes[] = 'lws-dotted-line';
                                    }
                                    if ($arg['line_size'] == 'thin') {
                                        $info['strokeWidth'] = 1;
                                        $classes[] = 'lws-thin-line';
                                    }
                                    if ($arg['line_size'] == 'regular') {
                                        $info['strokeWidth'] = 2;
                                        $classes[] = 'lws-regular-line';
                                    }
                                    if ($arg['line_size'] == 'thick') {
                                        $info['strokeWidth'] = 3;
                                        $classes[] = 'lws-thick-line';
                                    }
                                }
                                if (count($classes) > 0) {
                                    $info['classed'] = implode(' ', $classes);
                                }
                                if ($json) {
                                    $result['values'][] = $this->jsonify($info, $set, $raw_json);
                                } else {
                                    $info['values'] = $set;
                                    $result['values'][] = $info;
                                }
                            }
                            $i += 1;
                            $cmin += $ymin;
                            $cmax += $ymax;
                        }
                    }
                }
            }
            if ($valuescale == 'consistent') {
                $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
                if ($type == 'cstick') {
                    $sql = "SELECT MIN(CAST(`measure_value` AS DECIMAL(20,10))) as min_val FROM " . $table_name . " WHERE `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "' AND `measure_set`='min';";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    $ymin = $query[0]['min_val'];
                    $sql = "SELECT MAX(CAST(`measure_value` AS DECIMAL(20,10))) as max_val FROM " . $table_name . " WHERE `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "' AND `measure_set`='max';";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    $ymax = $query[0]['max_val'];
                }
                elseif ($type == 'doubleline' || $type == 'bcline') {
                    $sql = "SELECT MIN(CAST(`measure_value` AS DECIMAL(20,10))) as min_val FROM " . $table_name . " WHERE `device_id`='" . $args[1]['device_id'] . "' AND `module_id`='" . $args[1]['module_id'] . "' AND `measure_type`='" . $args[1]['measurement'] . "' AND `measure_set`='min';";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    $result['extras'][0]['ydomain']['min'] = $query[0]['min_val'];
                    $sql = "SELECT MAX(CAST(`measure_value` AS DECIMAL(20,10))) as max_val FROM " . $table_name . " WHERE `device_id`='" . $args[1]['device_id'] . "' AND `module_id`='" . $args[1]['module_id'] . "' AND `measure_type`='" . $args[1]['measurement'] . "' AND `measure_set`='max';";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    $result['extras'][0]['ydomain']['max'] = $query[0]['max_val'];
                    $sql = "SELECT MIN(CAST(`measure_value` AS DECIMAL(20,10))) as min_val FROM " . $table_name . " WHERE `device_id`='" . $args[2]['device_id'] . "' AND `module_id`='" . $args[2]['module_id'] . "' AND `measure_type`='" . $args[2]['measurement'] . "' AND `measure_set`='min';";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    $result['extras'][1]['ydomain']['min'] = $query[0]['min_val'];
                    $sql = "SELECT MAX(CAST(`measure_value` AS DECIMAL(20,10))) as max_val FROM " . $table_name . " WHERE `device_id`='" . $args[2]['device_id'] . "' AND `module_id`='" . $args[2]['module_id'] . "' AND `measure_type`='" . $args[2]['measurement'] . "' AND `measure_set`='max';";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    $result['extras'][1]['ydomain']['max'] = $query[0]['max_val'];
                }
                else {
                    foreach ($args as $arg) {
                        if (strpos($arg['module_id'], ':') == 2) {
                            try {
                                $sql = "SELECT MIN(CAST(`measure_value` AS DECIMAL(20,10))) as min_val FROM " . $table_name . " WHERE `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "' AND `measure_set`='min';";
                                $query = $wpdb->get_results($sql, ARRAY_A);
                                $min = $query[0]['min_val'];
                                $sql = "SELECT MAX(CAST(`measure_value` AS DECIMAL(20,10))) as max_val FROM " . $table_name . " WHERE `device_id`='" . $arg['device_id'] . "' AND `module_id`='" . $arg['module_id'] . "' AND `measure_type`='" . $arg['measurement'] . "' AND `measure_set`='max';";
                                $query = $wpdb->get_results($sql, ARRAY_A);
                                $max = $query[0]['max_val'];
                                if ($min < $ymin) {
                                    $ymin = $min;
                                }
                                if ($min < $cmin) {
                                    $cmin = $min;
                                }
                                if ($max > $ymax) {
                                    $ymax = $max;
                                }
                                if ($max > $cmax) {
                                    $cmax = $max;
                                }
                            }
                            catch (\Exception $ex) {
                                // keep $y and $c min and max
                            }
                        }
                    }
                    $cmin *= count($args);
                    $cmax *= count($args);
                }
            }
            $result['ydomain']['min'] = $ymin;
            $result['ydomain']['max'] = $ymax;
            if ($type == 'sareas') {
                $result['ydomain']['min'] = $cmin;
                $result['ydomain']['max'] = $cmax;
            }
            $result['ydomain']['dmin'] = $ydmin;
            $result['ydomain']['dmax'] = $ydmax;
            $result['ydomain']['amin'] = $yamin;
            $result['ydomain']['amax'] = $yamax;
            if ($attributes['cache'] != 'no_cache') {
                Cache::set_graph($fingerprint, $attributes['mode'], $result);
            }
        }
        if ($json) {
            if ($raw_json && $type != 'windrose') {
                $result['values'] = implode(', ', $result['values']);
            }
            elseif ($type != 'windrose') {
                if (!is_null($result) && isset($result['values']) && count($result['values']) > 0) {
                    $result['values'] = '[' . implode(', ', $result['values']) . ']';
                }
                else {
                    $result['values'] = '[]';
                }
            }
        }
        return $result;
    }

    /**
     * Get the time format.
     *
     * @param array $values The values.
     * @param string $mode The mode of graph.
     * @param string $period_duration The period duration.
     * @return string The time format (js style).
     * @since 3.4.0
     */
    private function graph_format($values, $mode, $period_duration) {
        $result = '';
        if ($mode == 'daily') {
            $result = '%H:%M';
        }
        if ($mode == 'yearly') {
            $result = '%Y-%m-%d';
            if ($period_duration == 'year') {
                $result = '%m/%Y';
            }
        }
        return $result;
    }

    /**
     * Get the title.
     *
     * @param array $values The values.
     * @param string $type The type of graph.
     * @param string $label The mode of label.
     * @param string $mode The mode of graph.
     * @param string $sep Optional. The separator.
     * @return string The title.
     * @since 3.4.0
     */
    private function graph_title($values, $type, $label, $mode, $sep=' - ') {
        $result = '';
        switch ($type) {
            case 'calendarhm':
            case 'line':
            case 'lines':
            case 'bar':
            case 'bars':
            case 'sareas':
            case 'astream':
            case 'cstick':
            case 'valuerc':
            case 'windrose':
                if (is_null($values)) {
                    return $result;
                }
                $name = '';
                if (!is_null($values) && isset($values['extras'][0]['measurement_type'])) {
                    $name = $values['extras'][0]['measurement_type'];

                }
                if ($type == 'lines' || $type == 'bars' || $type == 'sareas') {
                    $name = $this->get_dimension_name($values['legend']['unit']['dimension'], false, true);
                    $force = true;
                    foreach ($values['extras'] as $w) {
                        if ($w['raw_measurement_type'] != $values['extras'][0]['raw_measurement_type']) {
                            $force = false;
                            break;
                        }
                    }
                    if ($force) {
                        $name = $values['extras'][0]['measurement_type'];
                    }
                    if (strpos($values['legend']['unit']['dimension'], 'oncentration') > 0) {
                        $name .= ' (' . $values['extras'][0]['measurement_type'] . ')';
                    }
                    $wind = false;
                    if ($values['legend']['unit']['dimension'] == 'speed') {
                        $wind = true;
                        foreach ($values['extras'] as $w) {
                            if (strpos($w['raw_measurement_type'], 'strength') === false) {
                                $wind = false;
                                break;
                            }
                        }
                    }
                    if ($wind) {
                        $name = __('Wind', 'live-weather-station');
                    }
                }
                if ($mode == 'yearly' && $type != 'valuerc') {
                    $rain = false;
                    if (array_key_exists('legend', $values) &&  $values['legend']['unit']['dimension'] == 'length') {
                        $rain = true;
                        foreach ($values['extras'] as $w) {
                            if (strpos($w['raw_measurement_type'], 'rain_') === false) {
                                $rain = false;
                                break;
                            }
                        }
                    }
                    if ($rain) {
                        $name = __('Rainfall', 'live-weather-station');
                    }
                }
                break;
            case 'distributionrc':
                $wind = (strpos($values['extras'][0]['measurement_type'], 'windangle') !== false) || (strpos($values['extras'][0]['measurement_type'], 'gustangle')!== false);
                $strike = (strpos($values['extras'][0]['measurement_type'], 'strike_bearing') !== false);
                if ($wind && !$strike) {
                    $name = __('Wind', 'live-weather-station');
                }
                if (!$wind && $strike) {
                    $name = __('Strike', 'live-weather-station');
                }
                if ($wind && $strike) {
                    $name = __('Wind', 'live-weather-station') . ' & ' . __('Strike', 'live-weather-station');
                }
                break;
            case 'bcline':
            case 'doubleline':
                $name = $values['extras'][0]['measurement_type'];
                if (array_key_exists(1, $values['extras'])) {
                    $name .= ' ~ ' . $values['extras'][1]['measurement_type'];
                }
                break;
        }

        if ($mode == 'daily') {
            switch ($label) {
                case 'simple':
                    $result = ucwords($name);
                    break;
                case 'generic':
                    $result = ucwords($values['extras'][0]['measurement_type']) . $sep . ucwords($values['extras'][0]['module_name_generic']);
                    if ($type == 'valuerc' || $type == 'distributionrc') {
                        $result = ucwords($name) . $sep . ucwords(__('Today', 'live-weather-station'));
                    }
                    break;
                case 'named':
                    $result = ucwords($values['extras'][0]['measurement_type']) . $sep . ucwords($values['extras'][0]['module_name']);
                    break;
                case 'station':
                    $result = ucwords($values['extras'][0]['station_name']) . $sep . ucwords($name);
                    break;
                case 'located':
                    $result = ucwords($values['extras'][0]['station_loc']) . $sep . ucwords($name);
                    break;
                case 'coord':
                    $result = ucwords($values['extras'][0]['station_coord']) . $sep . $result = ucwords($values['extras'][0]['station_alt']) . $sep . ucwords($name);
                    break;
                case 'full':
                    $result = ucwords($values['extras'][0]['station_name'])  . $sep . ucwords($values['extras'][0]['module_name']) . $sep . ucwords($name);
                    break;
            }
        }
        if ($mode == 'yearly') {
            switch ($label) {
                case 'simple':
                    $result = ucwords($name);
                    break;
                case 'generic':
                    $result = ucwords($name) . $sep . ucwords($values['extras'][0]['period_name']);
                    break;
                case 'named':
                    $result = ucwords($values['extras'][0]['measurement_type']) . $sep . ucwords($values['extras'][0]['module_name']);
                    break;
                case 'station':
                    $result = ucwords($values['extras'][0]['station_name']) . $sep . ucwords($name);
                    break;
                case 'located':
                    $result = ucwords($values['extras'][0]['station_loc']) . ', ' . ucwords($values['extras'][0]['period_name']) . $sep . ucwords($name);
                    break;
                case 'coord':
                    $result = ucwords($values['extras'][0]['station_coord']) . $sep . $result = ucwords($values['extras'][0]['station_alt']) . $sep . ucwords($name);
                    break;
                case 'full':
                    $result = ucwords($values['extras'][0]['station_name'])  . $sep . ucwords($values['extras'][0]['module_name']) . $sep . ucwords($name);
                    break;
            }
        }
        return $result;
    }

    /**
     * Get the Y domain boundaries.
     *
     * @param array $domain The values domain.
     * @param string $valuescale The type of scale.
     * @return array The domain boundaries.
     * @since 3.5.0
     */
    private function graph_domain_per_domain($domain, $valuescale) {
        $ymin = $domain['min'];
        $ymax = $domain['max'];
        switch ($valuescale) {
            case 'fixed':
                $ymin = $ymin - (($ymax-$ymin)/6);
                $ymax = $ymax + (($ymax-$ymin)/6);
                break;
            case 'boundaries':
                $ymin = $domain['dmin'];
                $ymax = $domain['dmax'];
                break;
            case 'alarm':
                $ymin = $domain['amin'];
                $ymax = $domain['amax'];
                break;
            case 'percentage':
                $ymin = 0;
                $ymax = 100;
                break;
            case 'base-11':
                $ymin = 0;
                $ymax = 11;
                break;
            case 'angle':
                $ymin = 0;
                $ymax = $this->output_value(360, 'angle');
                break;
            case 'top':
                $ymin = 0;
                $ymax = $ymax + (($ymax-$ymin)/4);
                break;
            case 'bottom':
                $ymin = $ymin - (($ymax-$ymin)/4);
                $ymax = 0;
                break;
        }
        $result = array();
        $result['min'] = $ymin;
        $result['max'] = $ymax;
        return $result;
    }

    /**
     * Get the Y domain boundaries.
     *
     * @param array $values The values.
     * @param string $valuescale The type of scale.
     * @return array The domain boundaries.
     * @since 3.4.0
     */
    private function graph_domain($values, $valuescale) {
        return $this->graph_domain_per_domain($values['ydomain'], $valuescale);
    }

    /**
     * Get the Y ticks array.
     *
     * @param string $height The height of the graph.
     * @return string The size id.
     * @since 3.4.0
     */
    private function graph_size($height){
        $result = 'medium';
        $h = str_replace('px', '', $height);
        if (!strpos($h, '%')) {
            if ((int)$h < 250) {
                $result = 'small';
            }
            if ((int)$h > 550) {
                $result = 'large';
            }
        }
        else {
            $h = str_replace('%', '', $h);
            if ((int)$h < 40) {
                $result = 'small';
            }
        }
        return $result;
    }
    /**
     * Get the Y ticks array.
     *
     * @param array $domain The current domain.
     * @param string $valuescale The type of scale.
     * @param string $measurement The measurement.
     * @param string $height The height of the graph.
     * @param int $forcefactor Optional. Forces ticks number.
     * @return array The Y ticks.
     * @since 3.4.0
     */
    private function graph_ticks($domain, $valuescale, $measurement, $height, $forcefactor=0) {
        $amplitude = $domain['max'] - $domain['min'];
        $size = $this->graph_size($height);
        $small = $size == 'small';
        $large = $size == 'large';
        $ticks = array();
        if ($amplitude < 2) {
            $decimal = $this->decimal_for_output($measurement);
            if ($decimal > 2) {
                $decimal = 2;
            }
            if ($small) {
                $ticks[] = round (($domain['min'] + ($amplitude / 2)), $decimal);
            }
            else {
                $ticks[] = round (($domain['min'] + ($amplitude / 3)), $decimal);
                $ticks[] = round (($domain['max'] - ($amplitude / 3)), $decimal);
            }
        }
        else {
            $factor = 5 ;
            if ($small) {
                $factor = 3 ;
            }
            if ($large) {
                $factor = 10 ;
            }
            if ($forcefactor > 0) {
                $factor = $forcefactor;
            }
            $step = (int)(floor($amplitude/$factor));
            if ($step == 0) {
                $step = 1;
            }
            for ($i = 1; $i <= (int)floor($amplitude+1); $i+=$step) {
                $val = (int)floor($domain['min'] + $i);
                if ($i == 1) {
                    if ($domain['min'] + ($amplitude/20) < $val) {
                        $ticks[] = $val;
                    }
                }
                else {
                    if ($domain['max'] + ($amplitude/40) > $val) {
                        $ticks[] = $val;
                    }
                }
            }
        }
        if ($valuescale == 'percentage') {
            $ticks = array();
            if ($small) {
                //$ticks[] = 0;
                $ticks[] = 50;
                $ticks[] = 100;
            }
            else {
                //$ticks[] = 0;
                $ticks[] = 25;
                $ticks[] = 50;
                $ticks[] = 75;
                $ticks[] = 100;
            }
        }
        if ($valuescale == 'base-11') {
            $ticks = array();
            if ($small) {
                //$ticks[] = 0;
                $ticks[] = 6;
                $ticks[] = 10;
            }
            else {
                //$ticks[] = 0;
                $ticks[] = 3;
                $ticks[] = 6;
                $ticks[] = 8;
                $ticks[] = 10;
            }
        }
        if ($valuescale == 'angle') {
            $ticks = array();
            //$ticks[] = 0;
            $ticks[] = $this->output_value(90, 'angle');
            $ticks[] = $this->output_value(180, 'angle');
            $ticks[] = $this->output_value(270, 'angle');
            $ticks[] = $this->output_value(360, 'angle');
        }
        if ($valuescale == 'none') {
            $ticks = array();
        }
        return $ticks;
    }

    /**
     * Get the Y ticks array.
     *
     * @param string $measurement The measurement.
     * @return string The computed value scale.
     * @since 3.4.0
     */
    private function graph_valuescale($measurement) {
        switch ($this->output_unit($measurement)['dimension']) {
            case 'percentage':
                $result = 'percentage';
                break;
            case 'angle':
                $result = 'angle';
                break;
            case 'base-11':
                $result = 'base-11';
                break;
            case 'concentration-m':
            case 'concentration-b':
            case 'area-density':
            case 'speed':
            case 'irradiance':
            case 'illuminance':
            case 'rate':
            case 'length':
                $result = 'top';
                break;
            default:
                $result = 'fixed';
        }
        switch ($measurement) {
            case 'cloud_ceiling':
            case 'rain':
                $result = 'top';
                break;
        }
        if ($measurement == 'noise') {
            $result = 'top';
        }
        return $result;
    }


    /**
     * Get the template properties.
     *
     * @param integer $id The id of the template.
     * @return array The properties of the template.
     * @since 3.4.0
     */
    private function graph_template($id) {
        $prop = array();      // light dark organic mineral
        switch ($id) {
            case 'night':
                $prop['fg_color'] = '#4b4888';
                $prop['bg_color'] = '#141442';
                $prop['container'] = 'background-color:#101030;border-radius: 3px;border: 1px solid #2D2C3F;';
                $prop['nv-axis-line'] = 'stroke: #2D2C40;';
                $prop['nv-axis-domain'] = 'stroke: #4b4888;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 10px \'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif;fill: #4b4888;';
                $prop['spinner'] = '#FFFFFF';
                $prop['separator'] = '  ●  ';
                break;
            case 'modern':
                $prop['fg_color'] = '#666666';
                $prop['bg_color'] = '#F4F4F4';
                $prop['container'] = 'background-color:#EEEEEE;border-radius: 3px;border: 1px solid #CCCCCC;';
                $prop['nv-axis-line'] = 'stroke: #DDDDDD;';
                $prop['nv-axis-domain'] = 'stroke: #666666;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 10px Verdana, Geneva, sans-serif;fill: #666666;';
                $prop['spinner'] = '#666666';
                $prop['separator'] = '  ●  ';
                break;
            case 'light':
                $prop['fg_color'] = '#909090';
                $prop['bg_color'] = '#FEFEFE';
                $prop['container'] = 'background-color:#FAFAFA;border-radius: 3px;border: 1px solid #EAEAEA;';
                $prop['nv-axis-line'] = 'stroke: #F0F0F0;';
                $prop['nv-axis-domain'] = 'stroke: #D0D0D0;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 10px Verdana, Geneva, sans-serif;fill: #909090;';
                $prop['spinner'] = '#909090';
                $prop['separator'] = '  ●  ';
                break;
            case 'ws':
                $prop['fg_color'] = '#F8DC65';
                $prop['bg_color'] = '#565656';
                $prop['container'] = 'background-color:#484848;border-radius: 3px;border: 1px solid #F8DC65;';
                $prop['nv-axis-line'] = 'stroke: #666666;';
                $prop['nv-axis-domain'] = 'stroke: #F8DC65;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 10px Verdana, Geneva, sans-serif;fill: #F8DC65;';
                $prop['spinner'] = '#F8DC65';
                $prop['separator'] = '  ●  ';
                break;
            case 'dark':
                $prop['fg_color'] = '#878A9A';
                $prop['bg_color'] = '#464854';
                $prop['container'] = 'background-color:#40424D;border-radius: 3px;border: 1px solid #303137;';
                $prop['nv-axis-line'] = 'stroke: #515B69;';
                $prop['nv-axis-domain'] = 'stroke: #8792A2;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 10px Verdana, Geneva, sans-serif;fill: #878A9A;';
                $prop['spinner'] = '#878A9A';
                $prop['separator'] = '  ●  ';
                break;
            case 'sand':
                $prop['fg_color'] = '#9B7543';
                $prop['bg_color'] = '#CEB99E';
                $prop['container'] = 'background-color:#D7C2A7;border-radius: 5px;border: 1px solid #897458;';
                $prop['nv-axis-line'] = 'stroke: #A09280;';
                $prop['nv-axis-domain'] = 'stroke: #897458;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 11px \'Palatino Linotype\', \'Book Antiqua\', Palatino, serif;fill: #9B7543;';
                $prop['spinner'] = '#9B7543';
                $prop['separator'] = '  ●  ';
                break;
            case 'organic':
                $prop['fg_color'] = '#605D40';
                $prop['bg_color'] = '#AAC8B0';
                $prop['container'] = 'background-color:#BACEB7;border-radius: 3px;border: 1px solid #506040;';
                $prop['nv-axis-line'] = 'stroke: #A3B293;';
                $prop['nv-axis-domain'] = 'stroke: #506040;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 11px Georgia, serif;fill: #605D40;';
                $prop['spinner'] = '#605D40';
                $prop['separator'] = '  ●  ';
                break;
            case 'mineral':
                $prop['fg_color'] = '#494D5F';
                $prop['bg_color'] = '#B7C3D0';
                $prop['container'] = 'background-color:#C0CED8;border-radius: 3px;border: 1px solid #575A6A;';
                $prop['nv-axis-line'] = 'stroke: #7A8890;';
                $prop['nv-axis-domain'] = 'stroke: #506040;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 11px \'Trebuchet MS\', Helvetica, sans-serif;fill: #494D5F;';
                $prop['spinner'] = '#494D5F';
                $prop['separator'] = '  ●  ';
                break;
            case 'bwi':
                $prop['fg_color'] = '#FFFFFF';
                $prop['bg_color'] = '#000000';
                $prop['container'] = 'background-color:#000000;border-radius: 3px;border: 1px solid #FFFFFF;';
                $prop['nv-axis-line'] = 'stroke: #FFFFFF;';
                $prop['nv-axis-domain'] = 'stroke: #FFFFFF;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 12px Arial, Helvetica, sans-serif;fill: #FFFFFF;';
                $prop['spinner'] = '#FFFFFF';
                $prop['separator'] = '  ●  ';
                break;
            case 'bw':
                $prop['fg_color'] = '#000000';
                $prop['bg_color'] = '#FFFFFF';
                $prop['container'] = 'background-color:#FFFFFF;border-radius: 3px;border: 1px solid #000000;';
                $prop['nv-axis-line'] = 'stroke: #000000;';
                $prop['nv-axis-domain'] = 'stroke: #000000;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;font-variant: small-caps;letter-spacing:2px;';
                $prop['text'] = 'font:normal 12px Arial, Helvetica, sans-serif;fill: #000000;';
                $prop['spinner'] = '#000000';
                $prop['separator'] = '  ●  ';
                break;
            case 'terminal':
                $prop['fg_color'] = '#0000AA';
                $prop['bg_color'] = '#C0C0C0';
                $prop['container'] = 'background-color:#AAAAAA;border: 1px solid #0000AA;';
                $prop['nv-axis-line'] = 'stroke: #AAAAAA;';
                $prop['nv-axis-domain'] = 'stroke: #0000AA;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;';
                $prop['text'] = 'font:normal 10px \'Courier New\', Courier, monospace;fill: #0000AA;';
                $prop['spinner'] = '#0000AA';
                $prop['separator'] = ' / ';
                break;
            case 'console':
                $prop['fg_color'] = '#0099CC';
                $prop['bg_color'] = '#DDDDDD';
                $prop['container'] = 'background-color:#CCCCCC;border: 1px solid #0099CC;';
                $prop['nv-axis-line'] = 'stroke: #0099CC;';
                $prop['nv-axis-domain'] = 'stroke: #0099CC;stroke-opacity: 1;';
                $prop['nv-axislabel'] = 'font-size: 15px;letter-spacing:1px;';
                $prop['text'] = 'font:normal 10px \'Lucida Console\', Monaco, monospace;fill: #0099CC;';
                $prop['spinner'] = '#0099CC';
                $prop['separator'] = ' - ';
                break;
            default:
                $prop['fg_color'] = '#EEEEEE';
                $prop['bg_color'] = '#F4F4F4';
                $prop['container'] = '';
                $prop['nv-axis-line'] = '';
                $prop['nv-axis-domain'] = 'stroke-opacity: .75;';
                $prop['nv-axislabel'] = 'font-size: 15px;letter-spacing:1px;';
                $prop['text'] = 'font:normal 11px Arial, Helvetica, sans-serif;';
                $prop['spinner'] = '#000000';
                $prop['separator'] = '  ●  ';
        }
        return $prop;
    }

    /**
     * Prepare a graph query.
     *
     * @param array $attributes The type of graph queried by the shortcode.
     * @return array The prepared query.
     * @since 3.4.0
     */
    public function graph_prepare($attributes){
        $items = array();
        $noned = false;
        for ($i = 1; $i <= 8; $i++) {
            if (array_key_exists('device_id_'.$i, $attributes)) {
                if ($attributes['measurement_'.$i] == 'none' || $attributes['measurement_'.$i] == 'none:none') {
                    $noned = true;
                    continue;
                }
                $item = array();
                foreach ($this->graph_allowed_serie as $param) {
                    if (array_key_exists($param.'_'.$i, $attributes)) {
                        $item[$param] = $attributes[$param.'_'.$i];
                        if ($param == 'measurement') {
                            if (strpos ($attributes[$param.'_'.$i], ':') > 0) {
                                $s = explode(':', $attributes[$param.'_'.$i]);
                                $item[$param] = $s[1];
                                $item['set'] = $s[0];
                            }
                        }
                    }
                }
                $items[$i] = $item;
            }
        }
        $value_params = array();
        if (array_key_exists('mode', $attributes)) {
            $value_params['mode'] = $attributes['mode'];
        }
        else {
            $value_params['mode'] = '';
        }
        if (array_key_exists('type', $attributes)) {
            $value_params['type'] = $attributes['type'];
        }
        else {
            $value_params['type'] = '';
        }
        $value_params['args'] = $items;
        $value_params['noned'] = $noned;
        if (array_key_exists('cache', $attributes)) {
            $value_params['cache'] = $attributes['cache'];
        }
        else {
            $value_params['cache'] = 'cache';
        }
        if (array_key_exists('periodtype', $attributes)) {
            $value_params['periodtype'] = $attributes['periodtype'];
        }
        else {
            $value_params['periodtype'] = 'none';
        }
        if (array_key_exists('periodvalue', $attributes)) {
            $value_params['periodvalue'] = $attributes['periodvalue'];
        }
        else {
            $value_params['periodvalue'] = 'none';
        }
        if (array_key_exists('timescale', $attributes)) {
            $value_params['timescale'] = $attributes['timescale'];
        }
        else {
            $value_params['timescale'] = 'none';
        }
        if (array_key_exists('valuescale', $attributes)) {
            $value_params['valuescale'] = $attributes['valuescale'];
        }
        else {
            $value_params['valuescale'] = 'none';
        }
        $value_params['periodduration'] = 'none';
        if (strpos($value_params['periodtype'], '-month') > 0) {
            $value_params['periodduration'] = 'month';
        }
        if (strpos($value_params['periodtype'], '-mseason') > 0) {
            $value_params['periodduration'] = 'mseason';
        }
        if (strpos($value_params['periodtype'], '-year') > 0) {
            $value_params['periodduration'] = 'year';
        }
        if (array_key_exists('color', $attributes)) {
            $value_params['color'] = $attributes['color'];
        }
        else {
            $value_params['color'] = 'self';
        }
        if (array_key_exists('template', $attributes)) {
            $value_params['template'] = $attributes['template'];
        }
        else {
            $value_params['template'] = 'neutral';
        }
        return $value_params;
    }

    /**
     * Get a graph.
     *
     * @param array $attributes The type of graph queried by the shortcode.
     * @return string The graph ready to print.
     * @since 3.4.0
     */
    public function graph_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('mode' => '', 'type' => '', 'template' => 'neutral', 'color' => 'Blues', 'label' => 'none', 'interpolation' => 'linear', 'guideline' => 'none', 'height' => '300px', 'timescale' => 'auto', 'valuescale' => 'auto', 'data' => 'inline', 'cache' => 'cache', 'periodtype' => 'none', 'periodvalue' => 'none'), $attributes );
        $mode = $_attributes['mode'];
        $type = $_attributes['type'];
        $color = $_attributes['color'];
        $inverted = false;
        if (strpos($color, '_') > 0) {
            $inverted = true;
            $color = str_replace('i_', '', $color);
        }
        $data = $_attributes['data'];
        $label = $_attributes['label'];
        $interpolation = $_attributes['interpolation'];
        if (strpos($interpolation, 'olor-step-') > 0) {
            $interpolation = str_replace('color-step-', '', $interpolation);
        }
        $type_guideline = $_attributes['guideline'];
        $guideline = ($type_guideline != 'standard' && $type_guideline != 'none');
        $height = $_attributes['height'];
        $fingerprint = uniqid('', true);
        $uuid = substr ($fingerprint, strlen($fingerprint)-6, 80);
        $uniq = 'graph' . $uuid;
        $container = 'lws-container-' . $uuid;
        $svg = 'svg' . $uuid;
        $titl = 'titl' . $uuid;
        $calendar = 'calendar' . $uuid;
        $spinner = 'spinner' . $uuid;
        $inter = 'inter' . $uuid;
        if ($mode == 'daily') {
            $refresh = 120000 + random_int(-30000, 30000);
        }
        else {
            $refresh = 3600000 + random_int(-90000, 90000);
        }

        // prepare query params
        $value_params = $this->graph_prepare($attributes);
        $period_duration = $value_params['periodduration'];
        $items = $value_params['args'];
        $cpt = 0;
        foreach ($items as $item) {
            if ($item['module_id'] != 'none') {
                $cpt += 1;
            }
        }
        $full_cpt = $cpt;
        if ($cpt == 0) {
            if ($value_params['noned']) {
                return __('No Data To Display', 'live-weather-station');
            }
            else {
                return __('Malformed shortcode. Please verify it!', 'live-weather-station');
            }
        }
        if ($cpt < 3) {
        $cpt = 3;
        }
        if ($cpt > 8) {
            $cpt = 8;
        }
        $measurement1 = '';
        $measurement2 = '';
        if ($type != 'distributionrc' && $type != 'valuerc' && $type != 'doubleline') {
            if (isset($items[1]) && array_key_exists('measurement', $items[1])) {
                $measurement1 = $items[1]['measurement'];
            } else {
                $measurement1 = '';
            }
            if (isset($items[2]) && array_key_exists('measurement', $items[2])) {
                $measurement2 = $items[2]['measurement'];
            } else {
                $measurement2 = '';
            }
        }


        // Compute scales
        $timescale = $_attributes['timescale'];
        $focus = false;
        if ($timescale == 'focus') {
            $timescale = 'adaptative';
            $focus = true;
        }
        if ($timescale == 'auto' && $mode == 'daily') {
            $timescale = 'fixed';
        }
        if ($timescale == 'auto' && $mode == 'yearly') {
            $timescale = 'fixed';
        }
        $fixed_timescale = ($timescale != 'adaptative');
        $valuescale = $_attributes['valuescale'];
        $valuescale2 = $valuescale;
        if ($valuescale == 'auto') {
            $valuescale = $this->graph_valuescale($measurement1);
            $valuescale2 = $this->graph_valuescale($measurement2);
        }
        $fixed_valuescale = ($valuescale != 'adaptative');



        // Queries...
        $values = $this->graph_query($value_params, true);
        $domain = $this->graph_domain($values, $valuescale);
        $time_format = $this->graph_format($values, $mode, $period_duration);
        $prop = $this->graph_template($_attributes['template']);
        $label_txt = $this->graph_title($values, $type, $label, $mode, $prop['separator']);



        // Render...
        $result = '';
        $body = '';

        if ($type == 'distributionrc' || $type == 'valuerc') {
            $interpolation = $interpolation == 'cardinal' ? 'true': 'false';
            wp_enqueue_script('lws-d3');
            wp_enqueue_script('lws-radarchart');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $titlestyle = $prop['nv-axislabel'];
            $atitlestyle = $prop['text'];
            $atitlestyle = str_replace(';fill:', ';color:', $atitlestyle);
            $linecolor = $prop['nv-axis-line'];
            $linecolor = str_replace('stroke: ', '', $linecolor);
            $linecolor = str_replace(';', '', $linecolor);
            $inner_height = (int)$height;
            switch ($height) {
                case '150px':
                    $titlestyle = 'display:none;';
                    $clevel=2;
                    $size = 140;
                    $tmargin = 10;
                    $linewidth = 1;
                    break;
                case '200px':
                    $clevel=3;
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 190;
                        $tmargin = 10;
                    }
                    else {
                        $titlestyle .= 'padding-top:10px;';
                        $size = 170;
                        $tmargin = 10;
                    }
                    $linewidth = 1;
                    break;
                case '300px':
                    $clevel=4;
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 260;
                        $tmargin = 38;
                    }
                    else {
                        $titlestyle .= 'padding-top:36px;';
                        $size = 236;
                        $tmargin = 36;
                    }
                    $linewidth = 2;
                    break;
                case '400px':
                    $clevel=5;
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 350;
                        $tmargin = 44;
                    }
                    else {
                        $titlestyle .= 'padding-top:42px;';
                        $size = 320;
                        $tmargin = 42;
                    }
                    $linewidth = 2;
                    break;
                case '555px':
                    $clevel=6;
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 490;
                        $tmargin = 60;
                    }
                    else {
                        $titlestyle .= 'padding-top:60px;';
                        $size = 450;
                        $tmargin = 60;
                    }
                    $linewidth = 2;
                    break;
                default:
                    $titlestyle = '';
                    $clevel=6;
            }
            $inner_height = $inner_height . 'px';
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient($cpt, 50);
                foreach ($col_array as $c) {
                    $legendColors[] = '"#' . $c . '"';
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            $result .= '#' . $titl . ' {' . $titlestyle . '}' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' text {' . $prop['text'] . '}' . PHP_EOL;
            }
            $result .= '</style>' . PHP_EOL;
            // BEGIN MAIN BODY
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[' . $cpt . '].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[' . $cpt . '][0] == color' . $uniq . '[0]) {color' . $uniq . '.reverse();}' . PHP_EOL;
                }
            }
            else {
                if ($inverted) {
                    $body .= '    var color' . $uniq . ' = [' . implode(', ', array_reverse($legendColors)) . '];' . PHP_EOL;
                }
                else {
                    $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
                }
            }

            if (isset($values) && isset($values['extras']) && isset($values['extras'][0])) {
                $body .= '      var chartOption' . $uniq . ' = {' . PHP_EOL;
                $body .= '            width: ' . $size . ',' . PHP_EOL;
                $body .= '            widthMax: ' . $size . ',' . PHP_EOL;
                $body .= '            height: ' . $size . ',' . PHP_EOL;
                $body .= '            heightMax: ' . $size . ',' . PHP_EOL;
                $body .= '            valFormat: "' . $values['extras'][0]['format'] . '",' . PHP_EOL;
                $body .= '            valUnit: "' . $values['extras'][0]['unit'] . '",' . PHP_EOL;
                if ($type == 'valuerc') {
                    $body .= '            correctAdd: ' . $values['extras'][0]['correctadd'] . ',' . PHP_EOL;
                    $body .= '            correctMul: ' . $values['extras'][0]['correctmul'] . ',' . PHP_EOL;
                }
                $body .= '            margins: {top: ' . $tmargin . ',right: 0,bottom: 0,left: 0},' . PHP_EOL;
                $body .= '            circles: {levels: ' . $clevel . ',maxValue: 0,labelFactor: 1.25,opacity: 0.1,fill: "' . $linecolor . '",color: "' . $linecolor . '"},' . PHP_EOL;
                if ($type == 'valuerc') {
                    $body .= '            axes: {display: true,threshold: 90,lineColor: "' . $prop['bg_color'] . '",lineWidth: "' . $linewidth . 'px",wrapWidth: 60,filter: [],invert: [],ranges: {' . $values['extras'][0]['range'] . '}},' . PHP_EOL;
                } else {
                    $body .= '            axes: {display: true,threshold: 90,lineColor: "' . $prop['bg_color'] . '",lineWidth: "' . $linewidth . 'px",wrapWidth: 60,filter: [],invert: [],ranges: {}},' . PHP_EOL;
                }
                $body .= '            areas: {colors: {},opacity: 0.35,borderWidth: ' . (string)($linewidth + 1) . ',rounded: ' . $interpolation . ',dotRadius:' . (string)($linewidth + 1) . ',sort: true,topfilter: []},' . PHP_EOL;
                if ($type_guideline == 'standard') {
                    $body .= '               filter_id: false,' . PHP_EOL;
                    $body .= '               filter: false,' . PHP_EOL;
                }
                $body .= '            color: color' . $uniq . '}' . PHP_EOL;
                $body .= '      var chart' . $uniq . ' = RadarChart();' . PHP_EOL;
                $body .= '        d3.select("#' . $svg . '").call(chart' . $uniq . ');' . PHP_EOL;
                $body .= '        chart' . $uniq . '.options(chartOption' . $uniq . ').data(data' . $uniq . ').update();' . PHP_EOL;
            }
            /// END MAIN BODY
        }

        if ($type == 'cstick') {
            $ticks = $this->graph_ticks($domain, $valuescale, $measurement1, $height);
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient(4, 50);
                $i = 0;
                foreach ($col_array as $c) {
                    if ($i++ > 2) {
                        break;
                    }
                    $legendColors[] = '"#' . $c . '"';
                }
                if ($inverted) {
                    $legendColors = array_reverse($legendColors);
                }
            }
            else {
                if ($inverted) {
                    $legendColors[2] = '"' . ColorBrewer::get($color, 3, 0) . '"';
                    $legendColors[1] = '"' . ColorBrewer::get($color, 3, 1) . '"';
                    $legendColors[0] = '"' . ColorBrewer::get($color, 3, 2) . '"';
                }
                else {
                    $legendColors[0] = '"' . ColorBrewer::get($color, 3, 0) . '"';
                    $legendColors[1] = '"' . ColorBrewer::get($color, 3, 1) . '"';
                    $legendColors[2] = '"' . ColorBrewer::get($color, 3, 2) . '"';
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '#' . $svg . ' .nvd3 .nv-ticks .negative rect {stroke:' . str_replace('"', '', $legendColors[0]) . ' !important;fill:' . str_replace('"', '', $legendColors[0]) . ' !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-ticks .positive rect {stroke:' . str_replace('"', '', $legendColors[2]) . ' !important;fill:' . str_replace('"', '', $legendColors[2]) . '  !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-ticks line {stroke:' . str_replace('"', '', $legendColors[1]) . ' !important;}' . PHP_EOL;
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      function sprintf(format){for( var i=1; i < arguments.length; i++ ) {format = format.replace( /%s/, arguments[i] );}return format;}' . PHP_EOL;
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if ($fixed_timescale) {
                $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none') {
                $body .= '    var h00Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var h01Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['01'] . ');' . PHP_EOL;
                $body .= '    var h02Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['02'] . ');' . PHP_EOL;
                $body .= '    var h03Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['03'] . ');' . PHP_EOL;
                $body .= '    var h04Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '    nv.addGraph(function() {' . PHP_EOL;
            $body .= '       chart'.$uniq.' = nv.models.candlestickBarChart()' . PHP_EOL;
            $body .= '               .x(function(d) {return x' . $uniq . ' + d["date"] + 43200000})' . PHP_EOL;
            $body .= '               .y(function(d) {return d["close"]})' . PHP_EOL;
            $body .= '               .showLegend(false)' . PHP_EOL;
            if ($fixed_timescale) {
                $body .= '               .xDomain([minDomain'.$uniq.'-00000000, maxDomain'.$uniq.'-00000000])' . PHP_EOL;
            }
            if ($fixed_valuescale) {
                $body .= '               .yDomain(['.$domain['min'].', '.$domain['max'].'])' . PHP_EOL;
            }
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'")' . PHP_EOL;
            $body .= '               .useInteractiveGuideline(true);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($fixed_timescale && $timescale != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([h00Tick'.$uniq.', h01Tick'.$uniq.', h02Tick'.$uniq.', h03Tick'.$uniq.', h04Tick'.$uniq.']);' . PHP_EOL;
            }
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            if ($label != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.axisLabelDistance(6);' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.interactiveLayer.tooltip.gravity("s");' . PHP_EOL;
            if ($_attributes['valuescale'] == 'adaptative') {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(true)';
            }
            else {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(false)';
            }
            $body .= '.tickFormat(function(d) { return d + " ' . $values['legend']['unit']['unit'] . '"; });' . PHP_EOL;
            if (!is_null($values) && isset($values['extras']) && array_key_exists(0, $values['extras'])) {
                $close = ucfirst($values['extras'][0]['close']);
                $open = ucfirst($values['extras'][0]['open']);
            }
            else {
                $close = '';
                $open = '';
            }
            $high = ucfirst($this->get_operation_name('max'));
            $low = ucfirst($this->get_operation_name('min'));
            $body .= '      chart' . $uniq . '.interactiveLayer.tooltip.contentGenerator(function(d) {';
            $body .= '      var c=d.series[0].data;' . PHP_EOL;
            $body .= '      var e=c.open<c.close?' . $legendColors[2] . ':' . $legendColors[0] . ';' . PHP_EOL;
            $body .= '      var close = chart' . $uniq . '.yAxis.tickFormat()(c.close);' . PHP_EOL;
            $body .= '      var open = chart' . $uniq . '.yAxis.tickFormat()(c.open);' . PHP_EOL;
            $body .= '      var low = chart' . $uniq . '.yAxis.tickFormat()(c.low);' . PHP_EOL;
            $body .= '      var high = chart' . $uniq . '.yAxis.tickFormat()(c.high);' . PHP_EOL;
            $body .= '      var sth=\'<table><thead><tr><td colspan="3"><strong class="x-value">%s</strong></td></tr></thead><tbody>%s</tbody></table>\';' . PHP_EOL;
            $body .= '      var str1=sprintf(\'<tr><td class="legend-color-guide"><div style="background-color: %s;"></div></td><td class="key">' . $high . '</td><td class="value">%s</td></tr>\', ' . $legendColors[1] . ', high);' . PHP_EOL;
            $body .= '      var str3=sprintf(\'<tr><td class="legend-color-guide"><div style="background-color: %s;"></div></td><td class="key">' . $open . '</td><td class="value">%s</td></tr>\', e, open);' . PHP_EOL;
            $body .= '      var str2=sprintf(\'<tr><td class="legend-color-guide"><div style="background-color: %s;"></div></td><td class="key">' . $close . '</td><td class="value">%s</td></tr>\', e, close);' . PHP_EOL;
            $body .= '      var str4=sprintf(\'<tr><td class="legend-color-guide"><div style="background-color: %s;"></div></td><td class="key">' . $low . '</td><td class="value">%s</td></tr>\', ' . $legendColors[1] . ', low);' . PHP_EOL;
            $body .= '      return sprintf(sth, d.value, str1+str2+str3+str4)});' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis.tickValues([' . implode(', ', $ticks).']);' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'astream') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $cpt = str_replace('s', '', $items[1]['line_mode']);
            if (!is_null($values) && isset($values['extras']) && array_key_exists(0, $values['extras'])) {
                $unit = $values['extras'][0]['unit']['unit'];
            }
            else {
                $unit = '';
            }
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient($cpt-1, 50);
                foreach ($col_array as $c) {
                    $legendColors[] = '"#' . $c . '"';
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }

            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if (isset($values) && array_key_exists('xdomain', $values)) {
                if ($fixed_timescale && $mode == 'daily') {
                    $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                    $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
                }
                if ($fixed_timescale && $mode == 'yearly') {
                    $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                    $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
                }
                if ($fixed_timescale && $timescale != 'none' && $mode == 'daily') {
                    $body .= '    var h00Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                    $body .= '    var h04Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['04'] . ');' . PHP_EOL;
                    $body .= '    var h08Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['08'] . ');' . PHP_EOL;
                    $body .= '    var h12Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['12'] . ');' . PHP_EOL;
                    $body .= '    var h16Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['16'] . ');' . PHP_EOL;
                    $body .= '    var h20Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['20'] . ');' . PHP_EOL;
                    $body .= '    var h24Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
                }
                if ($fixed_timescale && $timescale != 'none' && $mode == 'yearly') {
                    $body .= '    var h00Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                    $body .= '    var h01Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['01'] . ');' . PHP_EOL;
                    $body .= '    var h02Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['02'] . ');' . PHP_EOL;
                    $body .= '    var h03Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['03'] . ');' . PHP_EOL;
                    $body .= '    var h04Tick' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
                }
            }
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[' . $cpt . '].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[' . $cpt . '][0] == color' . $uniq . '[0]) {color' . $uniq . '.reverse();}' . PHP_EOL;
                }
            }
            else {
                if ($inverted) {
                    $body .= '    var color' . $uniq . ' = [' . implode(', ', array_reverse($legendColors)) . '];' . PHP_EOL;
                }
                else {
                    $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
                }
            }

            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '      nv.addGraph(function() {' . PHP_EOL;
            $body .= '         chart'.$uniq.' = nv.models.stackedAreaChart()' . PHP_EOL;
            $body .= '               .x(function(d) {return x' . $uniq . ' + d[0]})' . PHP_EOL;
            $body .= '               .y(function(d) {return d[1]})' . PHP_EOL;
            $body .= '               .clipEdge(true)' . PHP_EOL;
            $body .= '               .interpolate("' . $interpolation . '")' . PHP_EOL;
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'")' . PHP_EOL;
            $body .= '               .color(color' . $uniq . ')' . PHP_EOL;
            $body .= '               .showControls(false);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            if (isset($values) && array_key_exists('xdomain', $values)) {
                if ($fixed_timescale && $timescale != 'none' && $mode == 'daily') {
                    $body .= '      chart' . $uniq . '.xAxis.tickValues([h00Tick' . $uniq . ', h04Tick' . $uniq . ', h08Tick' . $uniq . ', h12Tick' . $uniq . ', h16Tick' . $uniq . ', h20Tick' . $uniq . ', h24Tick' . $uniq . ']);' . PHP_EOL;
                }
                if ($fixed_timescale && $timescale != 'none' && $mode == 'yearly') {
                    $body .= '      chart' . $uniq . '.xAxis.tickValues([h00Tick' . $uniq . ', h01Tick' . $uniq . ', h02Tick' . $uniq . ', h03Tick' . $uniq . ', h04Tick' . $uniq . ']);' . PHP_EOL;
                }
                if ($fixed_timescale) {
                    $body .= '      chart' . $uniq . '.xDomain([minDomain' . $uniq . ', maxDomain' . $uniq . '])' . PHP_EOL;
                }
            }
            if ($label != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.axisLabelDistance(6);' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.yAxis.tickValues([]);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis.showMaxMin(false);';
            $body .= '      chart'.$uniq.'.style("stream-center");' . PHP_EOL;
            $body .= '      chart'.$uniq.'.tooltip.enabled(false);' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'sareas') {
            $ticks = $this->graph_ticks($domain, $valuescale, $measurement1, $height);
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            if (array_key_exists(0, $values['extras'])) {
                $unit = $values['extras'][0]['unit']['unit'];
            }
            else {
                $unit = '';
            }
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient($cpt, 50);
                $i = 0;
                foreach ($col_array as $c) {
                    if ($i++ == $full_cpt) {
                        break;
                    }
                    $legendColors[] = '"#' . $c . '"';
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }

            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[' . $cpt . '].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[' . $cpt . '][0] == color' . $uniq . '[0]) {color' . $uniq . ' = color' . $uniq . '.reverse().slice(' . (string)($cpt - $full_cpt) . ');}' . PHP_EOL;
                }
            }
            else {
                if ($inverted) {
                    $body .= '    var color' . $uniq . ' = [' . implode(', ', array_reverse($legendColors)) . '];' . PHP_EOL;
                }
                else {
                    $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
                }
            }
            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '      nv.addGraph(function() {' . PHP_EOL;
            $body .= '         chart'.$uniq.' = nv.models.stackedAreaChart()' . PHP_EOL;
            $body .= '               .x(function(d) {return x' . $uniq . ' + d[0]})' . PHP_EOL;
            $body .= '               .y(function(d) {return d[1]})' . PHP_EOL;
            $body .= '               .clipEdge(true)' . PHP_EOL;
            $body .= '               .interpolate("' . $interpolation . '")' . PHP_EOL;
            $body .= '               .useInteractiveGuideline(true)' . PHP_EOL;
            if ($fixed_valuescale && $type_guideline == 'stacked') {
                $body .= '               .yDomain(['.$domain['min'].', '.$domain['max'].'])' . PHP_EOL;
            }
            //$body .= '               .showLegend(true)' . PHP_EOL;
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'")' . PHP_EOL;
            $body .= '               .color(color' . $uniq . ')' . PHP_EOL;
            $body .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","grouped":"' . __('Grouped', 'live-weather-station') . '"}).showControls(false);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            if ($label != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.axisLabelDistance(6);' . PHP_EOL;
            }
            if ($_attributes['valuescale'] == 'adaptative' || $type_guideline == 'expanded') {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(true);';
            }
            else {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(false);';
            }
            if ($type_guideline == 'stacked') {
                $body .= '      chart' . $uniq . '.yAxis.tickValues([' . implode(', ', $ticks) . ']);' . PHP_EOL;
            }
            if ($type_guideline == 'stacked') {
                $body .= '      chart'.$uniq.'.style("stack");' . PHP_EOL;
            }
            if ($type_guideline == 'stream') {
                $body .= '      chart'.$uniq.'.style("stream");' . PHP_EOL;
            }
            if ($type_guideline == 'expanded') {
                $body .= '      chart'.$uniq.'.style("expand");' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.interactiveLayer.tooltip.gravity("s");' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis.tickFormat(function(d) { return d + " ' . $unit . '"; });' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'bar' || $type == 'bars') {
            $ticks = $this->graph_ticks($domain, $valuescale, $measurement1, $height);
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            if (array_key_exists(0, $values['extras'])) {
                $unit = $values['extras'][0]['unit']['unit'];
            }
            else {
                $unit = '';
            }
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient($cpt, 50);
                $i = 0;
                foreach ($col_array as $c) {
                    if ($i++ == $full_cpt) {
                        break;
                    }
                    $legendColors[] = '"#' . $c . '"';
                }
                if ($inverted) {
                    $legendColors = array_reverse($legendColors);
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }

            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[' . $cpt . '].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[' . $cpt . '][0] == color' . $uniq . '[0]) {color' . $uniq . ' = color' . $uniq . '.reverse().slice(' . (string)($cpt - $full_cpt) . ');}' . PHP_EOL;
                }
            }
            else{
                $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
            }

            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '      nv.addGraph(function() {' . PHP_EOL;
            $body .= '         chart'.$uniq.' = nv.models.multiBarChart()' . PHP_EOL;
            $body .= '               .x(function(d) {return x' . $uniq . ' + d[0]})' . PHP_EOL;
            $body .= '               .y(function(d) {return d[1]})' . PHP_EOL;
            $body .= '               .reduceXTicks(true)' . PHP_EOL;
            if ($type_guideline == 'stacked') {
                $body .= '               .stacked(true)' . PHP_EOL;
            }
            if ($fixed_valuescale) {
                $body .= '               .yDomain(['.$domain['min'].', '.$domain['max'].'])' . PHP_EOL;
            }
            $body .= '               .showLegend(' . ($type == 'bars'?'true':'false') . ')' . PHP_EOL;
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'")' . PHP_EOL;
            $body .= '               .color(color' . $uniq . ')' . PHP_EOL;
            if ($type == 'bars') {
                $body .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","grouped":"' . __('Grouped', 'live-weather-station') . '"}).showControls(' . ($type_guideline == 'free'?'true':'false') . ');' . PHP_EOL;
            }
            else {
                $body .= '               .controlLabels({}).showControls(false);' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            if ($label != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.axisLabelDistance(6);' . PHP_EOL;
            }
            if ($_attributes['valuescale'] == 'adaptative') {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(true);';
            }
            else {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(false);';
            }
            $body .= '      chart'.$uniq.'.yAxis.tickValues([' . implode(', ', $ticks).']);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis.tickValues([' . implode(', ', $ticks).']);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis.tickFormat(function(d) { return d + " ' . $unit . '"; });' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'line' || $type == 'lines') {
            $ticks = $this->graph_ticks($domain, $valuescale, $measurement1, $height);
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient($cpt, 50);
                $i = 0;
                foreach ($col_array as $c) {
                    if ($i++ == $full_cpt) {
                        break;
                    }
                    $legendColors[] = '"#' . $c . '"';
                }
                if ($inverted) {
                    $legendColors = array_reverse($legendColors);
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-dashed-line {stroke-dasharray:10,10 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-dotted-line {stroke-dasharray:2,2 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-thin-line {stroke-width: 1 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-regular-line {stroke-width: 2 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-thick-line {stroke-width: 3 !important;}' . PHP_EOL;
            $i = 1;
            foreach ($items as $item) {
                if ($item['dot_style'] == 'small-dot') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:1;stroke-opacity:1;stroke-width:1;}' . PHP_EOL;
                }
                if ($item['dot_style'] == 'large-dot') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:1;stroke-opacity:1;stroke-width:3;}' . PHP_EOL;
                }
                if ($item['dot_style'] == 'small-circle') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:0;stroke-opacity:1;stroke-width:12;}' . PHP_EOL;
                }
                if ($item['dot_style'] == 'large-circle') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:0;stroke-opacity:1;stroke-width:16;}' . PHP_EOL;
                }
                if ($item['line_mode'] == 'transparent' || $item['line_mode'] == 'area') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-area {stroke-opacity:0;}' . PHP_EOL;
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-line {stroke-opacity:0;}' . PHP_EOL;
                }
                $i += 1;
            }
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if ($fixed_timescale && $mode == 'daily') {
                $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $mode == 'yearly') {
                $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none' && $mode == 'daily') {
                $body .= '    var h00Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var h04Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['04'] . ');' . PHP_EOL;
                $body .= '    var h08Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['08'] . ');' . PHP_EOL;
                $body .= '    var h12Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['12'] . ');' . PHP_EOL;
                $body .= '    var h16Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['16'] . ');' . PHP_EOL;
                $body .= '    var h20Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['20'] . ');' . PHP_EOL;
                $body .= '    var h24Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none' && $mode == 'yearly') {
                $body .= '    var h00Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var h01Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['01'] . ');' . PHP_EOL;
                $body .= '    var h02Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['02'] . ');' . PHP_EOL;
                $body .= '    var h03Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['03'] . ');' . PHP_EOL;
                $body .= '    var h04Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[' . $cpt . '].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[' . $cpt . '][0] == color' . $uniq . '[0]) {color' . $uniq . ' = color' . $uniq . '.reverse().slice(' . (string)($cpt - $full_cpt) . ');}' . PHP_EOL;
                }
            }
            else {
                $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
            }
            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '    nv.addGraph(function() {' . PHP_EOL;
            $body .= '       chart'.$uniq.' = nv.models.lineChart()' . PHP_EOL;
            $body .= '               .x(function(d) {return x' . $uniq . ' + d[0]})' . PHP_EOL;
            $body .= '               .y(function(d) {return d[1]})' . PHP_EOL;
            $body .= '               .interpolate("' . $interpolation . '")' . PHP_EOL;
            if ($focus) {
                $body .= '               .focusEnable(true)' . PHP_EOL;
                $body .= '               .focusShowAxisX(false)' . PHP_EOL;
            }
            else {
                $body .= '               .focusEnable(false)' . PHP_EOL;
            }
            $body .= '               .showLegend(' . ($type == 'lines'?'true':'false') . ')' . PHP_EOL;
            if ($fixed_timescale) {
                $body .= '               .xDomain([minDomain'.$uniq.', maxDomain'.$uniq.'])' . PHP_EOL;
            }
            if ($fixed_valuescale) {
                $body .= '               .yDomain(['.$domain['min'].', '.$domain['max'].'])' . PHP_EOL;
            }
            $body .= '               .color(color' . $uniq . ')' . PHP_EOL;
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'")' . PHP_EOL;
            if ($guideline) {
                $body .= '               .useInteractiveGuideline(true);' . PHP_EOL;
            }
            else {
                $body .= '               .useInteractiveGuideline(false);' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($fixed_timescale && $timescale != 'none' && $mode == 'daily') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([h00Tick'.$uniq.', h04Tick'.$uniq.', h08Tick'.$uniq.', h12Tick'.$uniq.', h16Tick'.$uniq.', h20Tick'.$uniq.', h24Tick'.$uniq.']);' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none' && $mode == 'yearly') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([h00Tick'.$uniq.', h01Tick'.$uniq.', h02Tick'.$uniq.', h03Tick'.$uniq.', h04Tick'.$uniq.']);' . PHP_EOL;
            }
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            if ($mode == 'daily') {
                $body .= '      chart' . $uniq . '.interactiveLayer.tooltip.headerFormatter(function (d) {if (typeof d === "string") {d=parseFloat(d);};return d3.time.format("%Y-%m-%d %H:%M")(new Date(d));});' . PHP_EOL;
                $body .= '      chart' . $uniq . '.tooltip.headerFormatter(function (d) {if (typeof d === "string") {d=parseFloat(d);};return d3.time.format("%Y-%m-%d %H:%M")(new Date(d));});' . PHP_EOL;
            }
            if ($mode == 'yearly') {
                $body .= '      chart' . $uniq . '.interactiveLayer.tooltip.headerFormatter(function (d) {if (typeof d === "string") {d=parseFloat(d);};return d3.time.format("%Y-%m-%d")(new Date(d));});' . PHP_EOL;
                $body .= '      chart' . $uniq . '.tooltip.headerFormatter(function (d) {if (typeof d === "string") {d=parseFloat(d);};return d3.time.format("%Y-%m-%d")(new Date(d));});' . PHP_EOL;
            }
            if ($label != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.axisLabelDistance(6);' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.interactiveLayer.tooltip.gravity("s");' . PHP_EOL;
            if ($_attributes['valuescale'] == 'adaptative') {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(true)';
            }
            else {
                $body .= '      chart'.$uniq.'.yAxis.showMaxMin(false)';
            }
            $body .= '.tickFormat(function(d) { return d + " ' . $values['legend']['unit']['unit'] . '"; });' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis.tickValues([' . implode(', ', $ticks).']);' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'doubleline') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            wp_enqueue_script('lws-bilinechart');
            $forcefactor = 2;
            if ($height > 300) {
                $forcefactor = 3;
            }
            if ($height > 400) {
                $forcefactor = 4;
            }
            $domain1 = $this->graph_domain_per_domain($values['extras'][0]['ydomain'],$valuescale);
            if (array_key_exists(1, $values['extras'])) {
                $domain2 = $this->graph_domain_per_domain($values['extras'][1]['ydomain'],$valuescale2);
            }
            else {
                $domain2 = $domain1;
            }
            $ticks1 = $this->graph_ticks($domain1, $valuescale, $measurement1, $height, $forcefactor);
            $ticks2 = $this->graph_ticks($domain2, $valuescale2, $measurement2, $height, $forcefactor);
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient(8, 50);
                $i = 0;
                foreach ($col_array as $c) {
                    if ($i++ == $full_cpt) {
                        break;
                    }
                    $legendColors[] = '"#' . $c . '"';
                }
                if ($inverted) {
                    $legendColors = array_reverse($legendColors);
                }
            }
            if ($mode == 'daily') {
                $specialtimeformat = '%Y-%m-%d %H:%M';
            }
            else {
                $specialtimeformat = '%Y-%m-%d';
            }
            if (array_key_exists(1, $values['extras'])) {
                $unit = $values['extras'][1]['unit']['unit'];
            }
            else {
                $unit = '';
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }

            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '#' . $svg . ' .nvd3 .nv-y2 .nv-wrap g .tick text {text-anchor: end !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-dashed-line {stroke-dasharray:10,10 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-dotted-line {stroke-dasharray:2,2 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-thin-line {stroke-width: 1 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-regular-line {stroke-width: 2 !important;}' . PHP_EOL;
            $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-thick-line {stroke-width: 3 !important;}' . PHP_EOL;
            $i = 1;
            foreach ($items as $item) {
                if ($item['dot_style'] == 'small-dot') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:1;stroke-opacity:1;stroke-width:1;}' . PHP_EOL;
                }
                if ($item['dot_style'] == 'large-dot') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:1;stroke-opacity:1;stroke-width:3;}' . PHP_EOL;
                }
                if ($item['dot_style'] == 'small-circle') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:0;stroke-opacity:1;stroke-width:12;}' . PHP_EOL;
                }
                if ($item['dot_style'] == 'large-circle') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-point {fill-opacity:0;stroke-opacity:1;stroke-width:16;}' . PHP_EOL;
                }
                if ($item['line_mode'] == 'transparent' || $item['line_mode'] == 'area') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-area {stroke-opacity:0;}' . PHP_EOL;
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .lws-serie-' . $i . ' .nv-line {stroke-opacity:0;}' . PHP_EOL;
                }
                $i += 1;
            }
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      function sprintf(format){for( var i=1; i < arguments.length; i++ ) {format = format.replace( /%s/, arguments[i] );}return format;}' . PHP_EOL;
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if ($fixed_timescale && $mode == 'daily') {
                $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $mode == 'yearly') {
                $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none' && $mode == 'daily') {
                $body .= '    var h00Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var h04Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['04'] . ');' . PHP_EOL;
                $body .= '    var h08Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['08'] . ');' . PHP_EOL;
                $body .= '    var h12Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['12'] . ');' . PHP_EOL;
                $body .= '    var h16Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['16'] . ');' . PHP_EOL;
                $body .= '    var h20Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['20'] . ');' . PHP_EOL;
                $body .= '    var h24Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none' && $mode == 'yearly') {
                $body .= '    var h00Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var h01Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['01'] . ');' . PHP_EOL;
                $body .= '    var h02Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['02'] . ');' . PHP_EOL;
                $body .= '    var h03Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['03'] . ');' . PHP_EOL;
                $body .= '    var h04Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[3].slice(0);' . PHP_EOL;
                $refcolor = ColorBrewer::get($color, 3, 0);
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[3][0] == color' . $uniq . '[0]) {color' . $uniq . ' = color' . $uniq . '.reverse().slice(' . (string)(1) . ');}' . PHP_EOL;
                    $refcolor = ColorBrewer::get($color, 3, 0, true);
                }
            }
            else{
                $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
                $refcolor = str_replace('"', '', $legendColors[0]);
            }
            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '      nv.addGraph(function() {' . PHP_EOL;
            $body .= '        chart'.$uniq.' = nv.models.bilineChart()' . PHP_EOL;
            $body .= '               .x(function(d,i) {return x' . $uniq . ' + d[0]})' . PHP_EOL;
            $body .= '               .y(function(d,i) {return d[1]})' . PHP_EOL;
            if ($fixed_timescale) {
                $body .= '           .xDomain([minDomain'.$uniq.', maxDomain'.$uniq.'])' . PHP_EOL;
            }
            $body .= '      .yDomain1([' . $domain1['min'] . ',' . $domain1['max'].'])' . PHP_EOL;
            $body .= '      .yDomain2([' . $domain2['min'] . ',' . $domain2['max'].'])' . PHP_EOL;
            $body .= '               .interpolate("' . $interpolation . '")' . PHP_EOL;
            $body .= '               .color(color' . $uniq . ')' . PHP_EOL;
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'");' . PHP_EOL;
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($fixed_timescale && $timescale != 'none' && $mode == 'daily') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([h00Tick'.$uniq.', h04Tick'.$uniq.', h08Tick'.$uniq.', h12Tick'.$uniq.', h16Tick'.$uniq.', h20Tick'.$uniq.', h24Tick'.$uniq.']);' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none' && $mode == 'yearly') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([h00Tick'.$uniq.', h01Tick'.$uniq.', h02Tick'.$uniq.', h03Tick'.$uniq.', h04Tick'.$uniq.']);' . PHP_EOL;
            }
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            $body .= '      chart' . $uniq . '.tooltip.contentGenerator(function(d) {';
            $body .= '      var s=\'<table><thead><tr><td colspan="3"><strong class="x-value">%s</strong></td></tr></thead><tbody><tr><td class="legend-color-guide"><div style="background-color: %s;"></div></td><td class="key">%s</td><td class="value">%s</td></tr></tbody></table>\';' . PHP_EOL;
            $body .= '      var _date = "";' . PHP_EOL;
            $body .= '      var _color = "";' . PHP_EOL;
            $body .= '      var _key = "";' . PHP_EOL;
            $body .= '      var _value = "";' . PHP_EOL;
            $body .= '      if (d.series[0].color=="' . $refcolor . '"){' . PHP_EOL;
            $body .= '        _color = d.series[0].color;' . PHP_EOL;
            $body .= '        _key = d.series[0].key;' . PHP_EOL;
            $body .= '        _value = d.series[0].value+" ' . $values['extras'][0]['unit']['unit'] . '";' . PHP_EOL;
            $body .= '      }' . PHP_EOL;
            $body .= '      else{' . PHP_EOL;
            $body .= '        _color = d.series[0].color;' . PHP_EOL;
            $body .= '        _key = d.series[0].key;' . PHP_EOL;
            $body .= '        _value = d.series[0].value+" ' . $unit . '";' . PHP_EOL;
            $body .= '      }' . PHP_EOL;
            $body .= '      _date = d3.time.format("' . $specialtimeformat . '")(new Date(d.value));' . PHP_EOL;
            $body .= '      return sprintf(s, _date, _color, _key, _value)});' . PHP_EOL;
            $body .= '      chart'.$uniq.'.legendRightAxisHint("");' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis1.tickFormat(function(d) { return d + " ' . $values['extras'][0]['unit']['unit'] . '"; });' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis1.showMaxMin(false);';
            $body .= '      chart'.$uniq.'.yAxis1.tickValues([' . implode(', ', $ticks1).']);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis2.tickFormat(function(d) { return d + " ' . $unit . '"; });' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis2.tickPadding(-6);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.yAxis2.showMaxMin(false);';
            $body .= '      chart'.$uniq.'.yAxis2.tickValues([' . implode(', ', $ticks2).']);' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'bcline') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient(8, 50);
                $i = 0;
                foreach ($col_array as $c) {
                    if ($i++ == $full_cpt) {
                        break;
                    }
                    $legendColors[] = '"#' . $c . '"';
                }
                if ($inverted) {
                    $legendColors = array_reverse($legendColors);
                }
            }
            $forcefactor = 2;
            if ($height > 300) {
                $forcefactor = 3;
            }
            if ($height > 400) {
                $forcefactor = 4;
            }
            $domain1 = $this->graph_domain_per_domain($values['extras'][0]['ydomain'],$valuescale);
            if (array_key_exists(1, $values['extras'])) {
                $domain2 = $this->graph_domain_per_domain($values['extras'][1]['ydomain'],$valuescale2);
            }
            else {
                $domain2 = $domain1;
            }
            $ticks2 = $this->graph_ticks($domain2, $valuescale2, $measurement2, $height, $forcefactor);
            if ($mode == 'daily') {
                $specialtimeformat = '%Y-%m-%d %H:%M';
            }
            else {
                $specialtimeformat = '%Y-%m-%d';
            }
            if (array_key_exists(1, $values['extras'])) {
                $unit = $values['extras'][1]['unit']['unit'];
            }
            else {
                $unit = '';
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .nvd3 text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }

            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis line {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .nvd3 .nv-axis text.nv-axislabel {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            if ($fixed_timescale) {
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:first-of-type text {text-anchor: start !important;}' . PHP_EOL;
                $result .= '#' . $svg . ' .nvd3 .nv-x .nv-wrap g .tick:last-of-type text {text-anchor: end !important;}' . PHP_EOL;
            }
            $result .= '#' . $svg . ' .nvd3 .nv-y2 text {text-anchor: end !important;}' . PHP_EOL;
            if (array_key_exists(2, $items)) {
                if ($items[2]['line_style'] == 'dotted') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-line {stroke-dasharray:2,2;}' . PHP_EOL;
                }
                if ($items[2]['line_style'] == 'dashed') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-line {stroke-dasharray:10,10;}' . PHP_EOL;
                }
                if ($items[2]['dot_style'] == 'small-dot') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-point {fill-opacity:1;stroke-opacity:1;stroke-width:1;}' . PHP_EOL;
                }
                if ($items[2]['dot_style'] == 'large-dot') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-point {fill-opacity:1;stroke-opacity:1;stroke-width:3;}' . PHP_EOL;
                }
                if ($items[2]['dot_style'] == 'small-circle') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-point {fill-opacity:0;stroke-opacity:1;stroke-width:12;}' . PHP_EOL;
                }
                if ($items[2]['dot_style'] == 'large-circle') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-point {fill-opacity:0;stroke-opacity:1;stroke-width:16;}' . PHP_EOL;
                }
                if ($items[2]['line_mode'] == 'transparent' || $items[2]['line_mode'] == 'area') {
                    $result .= '#' . $svg . ' .nvd3 .nv-groups .nv-series-0 .nv-line {stroke-opacity:0;}' . PHP_EOL;
                }
            }
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      function sprintf(format){for( var i=1; i < arguments.length; i++ ) {format = format.replace( /%s/, arguments[i] );}return format;}' . PHP_EOL;
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            if ($fixed_timescale) {
                $body .= '    var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($fixed_timescale && $timescale != 'none') {
                $body .= '    var h00Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
                $body .= '    var h01Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['01'] . ');' . PHP_EOL;
                $body .= '    var h02Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['02'] . ');' . PHP_EOL;
                $body .= '    var h03Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['03'] . ');' . PHP_EOL;
                $body .= '    var h04Tick'.$uniq.' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            }
            if ($color != 'self') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[3].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[3][0] == color' . $uniq . '[0]) {color' . $uniq . ' = color' . $uniq . '.reverse().slice(' . (string)(1) . ');}' . PHP_EOL;
                }
            }
            else{
                $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
            }
            $body .= '      var chart'.$uniq.' = null;' . PHP_EOL;
            $body .= '      nv.addGraph(function() {' . PHP_EOL;
            $body .= '        chart'.$uniq.' = nv.models.linePlusBarChart()' . PHP_EOL;
            if ($label == 'none') {
                $body .= '               .height(' . (integer)($height-14) . ')' . PHP_EOL;
            }
            else {
                $body .= '               .height(' . (integer)($height-34) . ')' . PHP_EOL;
            }
            $body .= '               .x(function(d,i) {return x' . $uniq . ' + d[0]})' . PHP_EOL;
            $body .= '               .y(function(d,i) {return d[1]})' . PHP_EOL;
            $body .= '               .legendRightAxisHint(\'\')' . PHP_EOL;
            $body .= '               .legendLeftAxisHint(\'\')' . PHP_EOL;
            if ($focus) {
                $body .= '               .focusEnable(true)' . PHP_EOL;
                $body .= '               .focusShowAxisX(false)' . PHP_EOL;
            }
            else {
                $body .= '               .focusEnable(false)' . PHP_EOL;
            }
            $body .= '               .interpolate("' . $interpolation . '")' . PHP_EOL;
            if ($fixed_timescale) {
                $body .= '               .xDomain([minDomain'.$uniq.', maxDomain'.$uniq.'])' . PHP_EOL;
            }
            $body .= '      .yDomain([' . $domain2['min'] . ',' . $domain2['max'].'])' . PHP_EOL;
            $body .= '               .color(color' . $uniq . ')' . PHP_EOL;
            $body .= '               .noData("' . __('No Data To Display', 'live-weather-station') .'");' . PHP_EOL;
            $body .= '      chart'.$uniq.'.xAxis.axisLabel("' . $label_txt. '").showMaxMin(false).tickFormat(function(d) {return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
            if ($fixed_timescale && $timescale != 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([h00Tick'.$uniq.', h01Tick'.$uniq.', h02Tick'.$uniq.', h03Tick'.$uniq.', h04Tick'.$uniq.']);' . PHP_EOL;
            }
            if ($timescale == 'none') {
                $body .= '      chart'.$uniq.'.xAxis.tickValues([]);' . PHP_EOL;
            }
            $body .= '      chart' . $uniq . '.tooltip.contentGenerator(function(d) {';
            $body .= '      var s=\'<table><thead><tr><td colspan="3"><strong class="x-value">%s</strong></td></tr></thead><tbody><tr><td class="legend-color-guide"><div style="background-color: %s;"></div></td><td class="key">%s</td><td class="value">%s</td></tr></tbody></table>\';' . PHP_EOL;
            $body .= '      var _date = "";' . PHP_EOL;
            $body .= '      var _color = "";' . PHP_EOL;
            $body .= '      var _key = "";' . PHP_EOL;
            $body .= '      var _value = "";' . PHP_EOL;
            $body .= '      if (d.hasOwnProperty("element")){' . PHP_EOL;
            $body .= '        _color = d.series[0].color;' . PHP_EOL;
            $body .= '        _key = d.series[0].key;' . PHP_EOL;
            $body .= '        _value = d.series[0].value+" ' . $unit . '";' . PHP_EOL;
            $body .= '      }' . PHP_EOL;
            $body .= '      else{' . PHP_EOL;
            $body .= '        _color = d.color;' . PHP_EOL;
            $body .= '        _key = "' . $values['extras'][0]['info_key'] . '";' . PHP_EOL;
            $body .= '        _value = d.series[0].value+" ' . $values['extras'][0]['unit']['unit'] . '";' . PHP_EOL;
            $body .= '      }' . PHP_EOL;
            $body .= '      _date = d3.time.format("' . $specialtimeformat . '")(new Date(d.value));' . PHP_EOL;
            $body .= '      return sprintf(s, _date, _color, _key, _value)});' . PHP_EOL;
            if ($focus) {
                $body .= '      chart' . $uniq . '.focusMargin({"top":20, "bottom":-10});' . PHP_EOL;
            }
            $body .= '      chart'.$uniq.'.y1Axis.tickFormat(function(d) { return d + " ' . $values['extras'][0]['unit']['unit'] . '"; });' . PHP_EOL;
            $body .= '      chart'.$uniq.'.y1Axis.showMaxMin(false);';
            $body .= '      chart'.$uniq.'.y2Axis.tickFormat(function(d) { return d + " ' . $unit . '"; });' . PHP_EOL;
            $body .= '      chart'.$uniq.'.y2Axis.tickPadding(-6);' . PHP_EOL;
            $body .= '      chart'.$uniq.'.y2Axis.showMaxMin(false);';
            $body .= '      chart'.$uniq.'.y2Axis.tickValues([' . implode(', ', $ticks2).']);' . PHP_EOL;
            $body .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
            $body .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
            $body .= '      return chart'.$uniq.';' . PHP_EOL;
            $body .= '    });'.PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'calendarhm') {
            $step = (integer)$interpolation;
            $col = new ColorsManipulation($prop['fg_color']);
            $amplitude = ($domain['max'] - $domain['min']) / $step;
            $legend = array();
            $legendColors = array();
            $legendColors[] = 'div#'.$calendar.' .graph-rect{background-color: ' . $prop['bg_color'] . ' !important;fill: ' . $prop['bg_color'] . ' !important;}';
            $legendColors[] = 'div#'.$calendar.' .qi{background-color: ' . $prop['bg_color'] . ' !important;fill: ' . $prop['bg_color'] . ' !important;}';
            if ($color == 'self') {
                $col_array = $col->makeSteppedGradient($step, 50);
                if ($inverted) {
                    $col_array = array_reverse($col_array);
                }
                for ($i = 1; $i < $step; $i++) {
                    $legend[] = $domain['min'] + ($i * $amplitude);
                    $c = '#' . $col_array[$i-1];
                    $legendColors[] = 'div#'.$calendar.' .q' . $i . '{background-color: ' . $c . ' !important;fill: ' . $c . ' !important;}';
                }
                $c = '#' . $col_array[$step-1];
                $legendColors[] = 'div#'.$calendar.' .q' . $i . '{background-color: ' . $c . ' !important;fill: ' . $c . ' !important;}';
            }
            else {
                for ($i = 1; $i < $step; $i++) {
                    $legend[] = $domain['min'] + ($i * $amplitude);
                    $c = ColorBrewer::get($color, $step, $i-1, $inverted);
                    $legendColors[] = 'div#'.$calendar.' .q' . $i . '{background-color: ' . $c . ' !important;fill: ' . $c . ' !important;}';
                }
                $c = ColorBrewer::get($color, $step, $step-1, $inverted);
                $legendColors[] = 'div#'.$calendar.' .q' . $i . '{background-color: ' . $c . ' !important;fill: ' . $c . ' !important;}';
            }
            $legend = '[' . implode(',', $legend) . ']';
            $design = $timescale;
            $cRadius = 1;
            switch ($height) {
                case '150px':
                    $cSize = 6;
                    if ($label == 'none') {
                        $cSize = 9;
                    }
                    $ptop = 6;
                    if ($design == 'rdsquare') {$cRadius = 1;}
                    break;
                case '200px':
                    $cSize = 12;
                    if ($label == 'none') {
                        $cSize = 15;
                    }
                    $ptop = 8;
                    if ($design == 'rdsquare') {$cRadius = 2;}
                    break;
                case '300px':
                    $cSize = 22;
                    if ($label == 'none') {
                        $cSize = 26;
                    }
                    $ptop = 10;
                    if ($design == 'rdsquare') {$cRadius = 3;}
                    break;
                case '400px':
                    $cSize = 33;
                    if ($label == 'none') {
                        $cSize = 36;
                    }
                    $ptop = 12;
                    if ($design == 'rdsquare') {$cRadius = 4;}
                    break;
                case '555px':
                    $cSize = 48;
                    if ($label == 'none') {
                        $cSize = 52;
                    }
                    $ptop = 20;
                    if ($design == 'rdsquare') {$cRadius = 5;}
                    break;
                default:
                    $cSize = 10;
                    $ptop = 6;
                    if ($design == 'rdsquare') {$cRadius = 1;}
            }
            if ($design == 'round') {$cRadius = (int)round($cSize/2);}
            if ($design == 'square') {$cRadius = 0;}
            $inner_height = $height - 50;
            if ($label == 'none') {
                $inner_height = (int)$height;
            }
            $inner_height = $inner_height . 'px';
            if ($label_txt != '') {
                $label_txt = '<div style="padding-top:' . $ptop . 'px;' . str_replace('fill', 'color', $prop['text']) . '"><text style="' . $prop['nv-axislabel'] . '">' . $label_txt . '</text></div>';
            }

            wp_enqueue_style('lws-nvd3');
            wp_enqueue_style('lws-cal-heatmap');
            wp_enqueue_script('lws-cal-heatmap');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $result .= '<style type="text/css">' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $calendar . ' text.graph-label {' . $prop['text'] . '}' . PHP_EOL;
            }
            $result .= '.ch-tooltip {width:auto !important;box-shadow: none !important;background: rgba(255,255,255, 0.8) !important;border: 1px solid rgba(0,0,0,0.5) !important;border-radius: 4px !important;position: absolute !important;color: rgba(0,0,0,1.0) !important;padding: 8px !important;z-index: 10000 !important;font-family: Arial !important;font-size: 13px !important;text-align: left !important;pointer-events: none !important;white-space: nowrap !important;-webkit-touch-callout: none !important;-webkit-user-select: none !important;-khtml-user-select: none !important;-moz-user-select: none !important;-ms-user-select: none !important;user-select: none !important;}' . PHP_EOL;
            $result .= '.ch-tooltip::after{display:none !important;}' . PHP_EOL;
            $result .= implode(PHP_EOL, $legendColors) . PHP_EOL;
            $result .= '</style>' . PHP_EOL;

            // BEGIN MAIN BODY
            $body .= '      var shift' . $uniq . ' = new Date();' . PHP_EOL;
            $body .= '      var x' . $uniq . ' = 60000 * shift' . $uniq . '.getTimezoneOffset();' . PHP_EOL;
            $body .= '      var minDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['min'] . ');' . PHP_EOL;
            $body .= '      var maxDomain' . $uniq . ' = new Date(x' . $uniq . ' + ' . $values['xdomain']['max'] . ');' . PHP_EOL;
            $body .= '      var chart'.$uniq.'= new CalHeatMap();' . PHP_EOL;
            $body .= '      var min_date= new Date(minDomain' . $uniq . ');' . PHP_EOL;
            $body .= '      var start_date= new Date(minDomain' . $uniq . ');' . PHP_EOL;
            $body .= '      chart'.$uniq.'.init({' . PHP_EOL;
            $body .= '          itemSelector: "#'.$calendar.'",' . PHP_EOL;
            $body .= '          data: data'.$uniq.',' . PHP_EOL;
            $body .= '          range: '.$values['extras'][0]['period_range'].',' . PHP_EOL;
            $body .= '          domain: "month",' . PHP_EOL;
            $body .= '          subDomain: "day",' . PHP_EOL;
            $body .= '          subDomainDateFormat: "%Y-%m-%d",' . PHP_EOL;
            $body .= '          start: start_date,' . PHP_EOL;
            $body .= '          minDate: min_date,' . PHP_EOL;
            $body .= '          considerMissingDataAsZero: false,' . PHP_EOL;
            $body .= '          legend: ' . $legend . ',' . PHP_EOL;
            $body .= '          cellSize: ' . $cSize . ',' . PHP_EOL;
            $body .= '          cellRadius: ' . $cRadius . ',' . PHP_EOL;
            $body .= '          tooltip: true,' . PHP_EOL;
            if ($guideline) {
                $body .= '          displayLegend: true,' . PHP_EOL;
                $body .= '          legendHorizontalPosition: "' . $type_guideline . '",' . PHP_EOL;
                $body .= '          legendCellPadding: 0,' . PHP_EOL;
            }
            else {
                $body .= '          displayLegend: false,' . PHP_EOL;
            }
            $body .= '          subDomainTitleFormat: {empty: "' . sprintf(__('%s <br/>No data', 'live-weather-station'), '<strong>{date}</strong>'). '", filled: "' . sprintf('<strong>%s</strong> <br/>%s&nbsp; <strong>%s %s</strong>', '{date}', $values['extras'][0]['measurement_type'] . ' - ' . $values['extras'][0]['set_name'], '{count}', $values['legend']['unit']['unit']). '"},' . PHP_EOL;
            $body .= '          legendTitleFormat: {lower: "",inner: "",upper: ""}' . PHP_EOL;
            $body .= '      });' . PHP_EOL;
            // END MAIN BODY
        }

        if ($type == 'windrose') {
            wp_enqueue_script('lws-d4');
            wp_enqueue_script('lws-scale-radial');
            wp_enqueue_script('lws-windrose');
            wp_enqueue_script('lws-colorbrewer');
            wp_enqueue_script('lws-spin');
            $steps = 4;
            if (array_key_exists(2, $value_params['args'])) {
                if (array_key_exists('line_mode', $value_params['args'][2])) {
                    $steps = $value_params['args'][2]['line_mode'];
                    if (strpos($steps, 'olor-step-') > 0) {
                        $steps = str_replace('color-step-', '', $steps);
                    }
                }
            }
            if ($valuescale == 'auto') {
                $valuescale = 'adaptative';
            }

            $titlestyle = $prop['nv-axislabel'];
            $atitlestyle = $prop['text'];
            $atitlestyle = str_replace(';fill:', ';color:', $atitlestyle);
            switch ($height) {
                case '150px':
                    $titlestyle = 'display:none;';
                    $size = 140;
                    break;
                case '200px':
                    $clevel=3;
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 184;
                    }
                    else {
                        $titlestyle .= 'padding-top:2px;';
                        $size = 164;
                    }
                    break;
                case '300px':
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 284;
                    }
                    else {
                        $titlestyle .= 'padding-top:4px;';
                        $size = 258;
                    }
                    break;
                case '400px':
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 384;
                    }
                    else {
                        $titlestyle .= 'padding-top:6px;';
                        $size = 354;
                    }
                    break;
                case '555px':
                    if ($label == 'none') {
                        $titlestyle = 'display:none;';
                        $size = 534;
                    }
                    else {
                        $titlestyle .= 'padding-top:6px;';
                        $size = 510;
                    }
                    break;
                default:
                    $titlestyle = '';
            }
            $legendColors = array();
            if ($color == 'self') {
                $col = new ColorsManipulation($prop['fg_color']);
                $col_array = $col->makeSteppedGradient($steps-1, 50);
                foreach ($col_array as $c) {
                    $legendColors[] = '"#' . $c . '"';
                }
                if ($inverted) {
                    $legendColors = array_reverse($legendColors);
                }
            }
            $result .= '<style type="text/css">' . PHP_EOL;
            $result .= '#' . $titl . ' {' . $titlestyle . '}' . PHP_EOL;
            if ($prop['text'] != '') {
                $result .= '#' . $svg . ' .lwsLegend text {' . $prop['text'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-domain'] != '') {
                $result .= '#' . $svg . ' .lwsAxis path.domain {' . $prop['nv-axis-domain'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axis-line'] != '') {
                $result .= '#' . $svg . ' .lwsAxisWrapper circle {' . $prop['nv-axis-line'] . '}' . PHP_EOL;
            }
            if ($prop['nv-axislabel'] != '') {
                $result .= '#' . $svg . ' .lwsAxisWrapper text {' . $prop['nv-axislabel'] . '}' . PHP_EOL;
            }
            $result .= '</style>' . PHP_EOL;
            // BEGIN MAIN BODY
            if ($color != 'self' && $color != 'std') {
                $body .= '    var color' . $uniq . ' = colorbrewer.' . $color . '[' . $steps . '].slice(0);' . PHP_EOL;
                if ($inverted) {
                    $body .= '    if (colorbrewer.' . $color . '[' . $steps . '][0] == color' . $uniq . '[0]) {color' . $uniq . ' = color' . $uniq . '.reverse().slice(0);}' . PHP_EOL;
                }
            }
            else {
                $body .= '    var color' . $uniq . ' = [' . implode(', ', $legendColors) . '];' . PHP_EOL;
            }
            if (isset($values) && isset($values['extras']) && isset($values['extras'][0])) {
                $body .= '      var chartOption' . $uniq . ' = {' . PHP_EOL;
                if ($timescale != 'linear') {
                    $body .= '            scale: "radial",' . PHP_EOL;
                }
                if ($type_guideline == 'interactive') {
                    $body .= '            legend: true,' . PHP_EOL;
                }
                if ($valuescale == 'fixed') {
                    $body .= '            fixed: true,' . PHP_EOL;
                }
                if ($color != 'std') {
                    $body .= '            color: color' . $uniq . ',' . PHP_EOL;
                }
                $body .= '            size: ' . $size . '}' . PHP_EOL;
                $body .= '      var chart' . $uniq . ' = Windrose();' . PHP_EOL;
                $body .= '        d3.select("#' . $svg . '").call(chart' . $uniq . ');' . PHP_EOL;
                $body .= '        chart' . $uniq . '.options(chartOption' . $uniq . ').data(data' . $uniq . ').update();' . PHP_EOL;
            }
            /// END MAIN BODY
        }


        // FINAL RENDER

        $result .= '<div class="lws-module-chart module-' . $mode . '-' . $type . '" id="' . $container . '">' . PHP_EOL;
        if ($type == 'calendarhm') {
            $result .= '<div id="' . $uniq . '" style="' . $prop['container'] . 'padding:14px 14px 14px 14px;height: ' . $height . ';text-align: center;line-height: 1em;"><div id="' . $calendar . '" style="display: inline-block;"></div>' . $label_txt . '</div>' . PHP_EOL;
        }
        elseif ($type == 'distributionrc' || $type == 'valuerc' || $type == 'windrose') {
            $result .= '<div id="' . $uniq . '" style="' . $prop['container'] . 'padding:8px 14px 8px 14px;height: ' . $height . ';width: ' . $height . ';display:inline-block;text-align:center;overflow: hidden;' . $atitlestyle . '"><div id="' . $svg . '"></div><div id="' . $titl . '">' . $label_txt . '</div></div>' . PHP_EOL;
        }
        else {
            $result .= '<div id="' . $uniq . '" style="' . $prop['container'] . 'padding:8px 14px 8px 14px;height: ' . $height . ';"><svg id="' . $svg . '" style="overflow:hidden;"></svg></div>' . PHP_EOL;
        }
        $result .= '</div>' . PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        if ($data == 'inline') {
            $result .= '    var data'.$uniq.' =' . $values['values'] . ';' . PHP_EOL;
            $result .= $body;
        }
        elseif ($data == 'ajax' || $data == 'ajax_refresh') {
            $scale = '0.4';
            if ($this->graph_size($height) == 'small') {
                $scale = '0.2';
            }
            if ($this->graph_size($height) == 'large') {
                $scale = '0.6';
            }
            $result .= '    var opts = {lines: 15, length: 28, width: 8, radius: 42, scale: ' . $scale . ', corners: 1, color: "' . $prop['spinner'] . '", opacity: 0.2, rotate: 0, direction: 1, speed: 1, trail: 60, fps: 20, zIndex: 2e9, className: "c_' . $spinner .'", top: "50%", left: "50%", shadow: false, hwaccel: false, position: "relative"};' . PHP_EOL;
            $result .= '    var target = document.getElementById("' . $uniq . '");' . PHP_EOL;
            $result .= '    var ' . $spinner . ' = new Spinner(opts).spin(target);' . PHP_EOL;
            $result .= '    var observer' . $uniq . ' = null;' . PHP_EOL;
            $args = array();
            $args[] = 'action:"lws_query_graph_datas"';
            foreach ($this->graph_allowed_parameter as $param) {
                if (array_key_exists($param, $_attributes)) {
                    $args[] = $param . ':"' . $_attributes[$param] . '"';
                }
            }
            for ($i = 1; $i <= 8; $i++) {
                if (array_key_exists('device_id_'.$i, $attributes)) {
                    foreach ($this->graph_allowed_serie as $param) {
                        if (array_key_exists($param.'_'.$i, $attributes)) {
                            $args[] = $param.'_'.$i . ':"' . $attributes[$param.'_'.$i] . '"';
                        }
                    }
                }
            }
            $arg = '{' . implode (', ', $args) . '}';
            $result .= '$.post( "' . LWS_AJAX_URL . '", ' . $arg . ').done(function(data) {';
            $result .= '    var data'.$uniq.' = JSON.parse(data);' . PHP_EOL;
            $result .= $body;
            $result .= '    ' . $spinner . '.stop();' . PHP_EOL;
            if ($data == 'ajax_refresh') {
                $result .= '    var ' . $inter . ' = setInterval(function() {';
                $result .= '    ' . $spinner . '.spin(target);' . PHP_EOL;
                $result .= '$.post( "' . LWS_AJAX_URL . '", ' . $arg . ').done(function(data) {';
                $result .= '    data'.$uniq.' = JSON.parse(data);' . PHP_EOL;
                if ($type == 'distributionrc' || $type == 'valuerc') {
                    $result .= '        chart' . $uniq . '.data(data' . $uniq . ').duration(500).update();' . PHP_EOL;
                }
                else {
                    $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                }
                $result .= '    ' . $spinner . '.stop();})' . PHP_EOL;
                $result .= '}, ' . $refresh . '); ' . PHP_EOL;
            }
            if ($type != 'calendarhm') {
                $result .= 'if (observer' . $uniq . ' === null) { ' . PHP_EOL;
                $result .= '  var target' . $uniq . ' = document.getElementById("' . $uniq . '");' . PHP_EOL;
                $result .= '  var targetNode' . $uniq . ' = target' . $uniq . '.parentElement.parentElement.parentElement.parentElement;' . PHP_EOL;
                $result .= '  var modeStandard = true;' . PHP_EOL;
                $result .= '  var modeElementorPopbox = false;' . PHP_EOL;
                // Is the chart in elementor popup box ?
                $result .= '  var test' . $uniq . ' = target' . $uniq . '.closest(".modal-body");' . PHP_EOL;
                $result .= '    if (test' . $uniq . ' != null) {' . PHP_EOL;
                $result .= '      test' . $uniq . ' = test' . $uniq . '.closest(".modal-content");' . PHP_EOL;
                $result .= '      if (test' . $uniq . ' != null) {' . PHP_EOL;
                $result .= '        test' . $uniq . ' = test' . $uniq . '.closest(".modal");' . PHP_EOL;
                $result .= '        if (test' . $uniq . ' != null) {' . PHP_EOL;
                $result .= '          targetNode' . $uniq . ' = test' . $uniq . ';'. PHP_EOL;
                $result .= '          modeStandard = false;' . PHP_EOL;
                $result .= '          modeElementorPopbox = true;' . PHP_EOL;
                $result .= '        }' . PHP_EOL;
                $result .= '      }' . PHP_EOL;
                $result .= '    }' . PHP_EOL;
                $result .= 'var callback' . $uniq . ' = function(mutationsList) {' . PHP_EOL;
                $result .= '    for(var mutation of mutationsList) {' . PHP_EOL;
                $result .= '        if (modeStandard) {if (mutation.type == "attributes") {if (mutation.attributeName == "style") {if (mutation.target.style.display != "none") {if (mutation.oldValue !== null) {if (mutation.oldValue.indexOf("display: none") != -1) {chart'.$uniq.'.update();}}}}}}' . PHP_EOL;
                $result .= '        if (modeElementorPopbox) {if (mutation.type == "attributes") {if (mutation.attributeName == "style") {if (mutation.target.style.display == "block") {chart'.$uniq.'.update();}}}}' . PHP_EOL;
                $result .= '    }' . PHP_EOL;
                $result .= '};' . PHP_EOL;
                $result .= 'observer' . $uniq . ' = new MutationObserver(callback' . $uniq . ');' . PHP_EOL;
                $result .= 'observer' . $uniq . '.observe(targetNode' . $uniq . ',{attributes: true, subtree: true, attributeOldValue: true});' . PHP_EOL;
                $result .= '}' . PHP_EOL;
                $result .= '' . PHP_EOL;
                $result .= '' . PHP_EOL;
            }

            $result .= '});' . PHP_EOL;
        }
        if ($type != 'calendarhm' && $data != 'ajax' && $data != 'ajax_refresh') {
            $result .= 'var target' . $uniq . ' = document.getElementById("' . $uniq . '");' . PHP_EOL;
            $result .= 'var targetNode' . $uniq . ' = target' . $uniq . '.parentElement.parentElement.parentElement.parentElement;' . PHP_EOL;
            $result .= 'var modeStandard = true;' . PHP_EOL;
            $result .= 'var modeElementorPopbox = false;' . PHP_EOL;
            // Is the chart in elementor popup box ?
            $result .= 'var test' . $uniq . ' = target' . $uniq . '.closest(".modal-body");' . PHP_EOL;
            $result .= '  if (test' . $uniq . ' != null) {' . PHP_EOL;
            $result .= '    test' . $uniq . ' = test' . $uniq . '.closest(".modal-content");' . PHP_EOL;
            $result .= '    if (test' . $uniq . ' != null) {' . PHP_EOL;
            $result .= '      test' . $uniq . ' = test' . $uniq . '.closest(".modal");' . PHP_EOL;
            $result .= '      if (test' . $uniq . ' != null) {' . PHP_EOL;
            $result .= '        targetNode' . $uniq . ' = test' . $uniq . ';'. PHP_EOL;
            $result .= '        modeStandard = false;' . PHP_EOL;
            $result .= '        modeElementorPopbox = true;' . PHP_EOL;
            $result .= '      }' . PHP_EOL;
            $result .= '    }' . PHP_EOL;
            $result .= '  }' . PHP_EOL;
            $result .= 'var callback' . $uniq . ' = function(mutationsList) {' . PHP_EOL;
            $result .= '    for(var mutation of mutationsList) {' . PHP_EOL;
            $result .= '        if (modeStandard) {if (mutation.type == "attributes") {if (mutation.attributeName == "style") {if (mutation.target.style.display != "none") {if (mutation.oldValue !== null) {if (mutation.oldValue.indexOf("display: none") != -1) {chart'.$uniq.'.update();}}}}}}' . PHP_EOL;
            $result .= '        if (modeElementorPopbox) {if (mutation.type == "attributes") {if (mutation.attributeName == "style") {if (mutation.target.style.display == "block") {chart'.$uniq.'.update();}}}}' . PHP_EOL;
            $result .= '    }' . PHP_EOL;
            $result .= '};' . PHP_EOL;
            $result .= 'var observer' . $uniq . ' = new MutationObserver(callback' . $uniq . ');' . PHP_EOL;
            $result .= 'observer' . $uniq . '.observe(targetNode' . $uniq . ',{attributes: true, subtree: true, attributeOldValue: true});' . PHP_EOL;
            $result .= '' . PHP_EOL;
            $result .= '' . PHP_EOL;
        }
        $result .= '  });' . PHP_EOL;
        $result .= '</script>' . PHP_EOL;

        return $result;
    }

    /**
     * Get admin data analytics.
     *
     * @return string $attributes The type of analytics queryed by the shortcode.
     * @since 3.1.0
     */
    public function admin_analytics_shortcodes($attributes) {
        $result = '';
        $_attributes = shortcode_atts( array('item' => '', 'metric' => '', 'height' => ''), $attributes );
        if ($_attributes['item'] == '') {
            return '';
        }
        $fingerprint = uniqid('', true);
        $uniq = $_attributes['item'] . '_' . $_attributes['metric'] . '_' . substr ($fingerprint, count($fingerprint)-6, 80);

        // QUOTA STATISTICS
        if ($_attributes['item'] == 'quota') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            $perf = Performance::get_quota_values();

            if ($_attributes['metric'] == 'service_short' || $_attributes['metric'] == 'service_long') {
                wp_enqueue_script('lws-nvd3');
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg style="overflow:visible;"></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['count'][$_attributes['metric']] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.multiBarChart()' . PHP_EOL;
                $result .= '               .reduceXTicks(false)' . PHP_EOL;
                $result .= '               .color(d3.scale.category10().range())' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true)' . PHP_EOL;
                $result .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","grouped":"' . __('Grouped', 'live-weather-station') . '"});' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .rotateLabels(-30);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(d3.format("s"));' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }

            if ($_attributes['metric'] == 'call_short' || $_attributes['metric'] == 'call_long' || $_attributes['metric'] == 'rate_short' || $_attributes['metric'] == 'rate_long') {
                if ((bool)get_option('live_weather_station_force_frontend_styling')) {
                    wp_enqueue_style('buttons');
                }
                switch ($_attributes['metric']) {
                    case 'call_short':
                        $services = $perf['dat']['service24'];
                        $interpolate = 'linear';
                        $time_format = '%Y-%m-%d %H:%M';
                        $color = true;
                        break;
                    case 'rate_short':
                        $services = $perf['dat']['service24'];
                        $interpolate = 'step-after';
                        $time_format = '%Y-%m-%d %H:%M';
                        $color = true;
                        break;
                    case 'call_long':
                        $services = $perf['dat']['service30'];
                        $interpolate = 'linear';
                        $time_format = '%Y-%m-%d';
                        $color = false;
                        break;
                    case 'rate_long':
                        $services = $perf['dat']['service30'];
                        $interpolate = 'step-after';
                        $time_format = '%Y-%m-%d';
                        $color = false;
                        break;
                }
                if (count($services) == 0) {
                    return '<h4 style="padding:20px;"><span>'. __('No data', 'live-weather-station' ) . '</span></h4>';
                }
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result .= '<style type="text/css">.dashed-line {stroke-dasharray:5,5;}.hidden-line {display:none;}</style>' . PHP_EOL;
                $result .= '<div id="selectors-'.$uniq.'" class="wp-core-ui">' . PHP_EOL;
                foreach ($services as $service) {
                    $s = str_replace(' ', '', strtolower($service));
                    $s = str_replace('.', '', $s);
                    $s = str_replace('-', '', $s);
                    $result .= '<div id="selector-'.$s.'-'.$uniq.'" class="button" style="margin-right: 6px; margin-bottom:10px;">' . $service . '</div>' . PHP_EOL;
                }
                $result .= '<div>' . PHP_EOL;
                $result .= '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                foreach ($services as $service) {
                    $s = str_replace(' ', '', strtolower($service));
                    $s = str_replace('.', '', $s);
                    $s = str_replace('-', '', $s);
                    $result .= '    var data_'.$s.'_'.$uniq.' =' . $perf['dat'][$_attributes['metric']][$service] . ';' . PHP_EOL;
                }
                $result .= '      var chart'.$uniq.' = nv.models.lineChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .interpolate("' . $interpolate . '")' . PHP_EOL;
                if ($color) {
                    $result .= '               .color(d3.scale.category10().range())' . PHP_EOL;
                }
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("' . $time_format . '")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(d3.format("s"));' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                foreach ($services as $service) {
                    $s = str_replace(' ', '', strtolower($service));
                    $s = str_replace('.', '', $s);
                    $s = str_replace('-', '', $s);
                    $result .= '    $("#selector-'.$s.'-'.$uniq.'").click(function() {' . PHP_EOL;
                    $result .= '      $("#selectors-'.$uniq.' > div").removeClass("button-disabled");' . PHP_EOL;
                    $result .= '      $("#selector-'.$s.'-'.$uniq.'").addClass("button-disabled");' . PHP_EOL;
                    $result .= '      d3.select("#'.$uniq.' svg").datum(data_'.$s.'_'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                    $result .= '    });' . PHP_EOL;
                }
                $s = str_replace(' ', '', strtolower($services[0]));
                $s = str_replace('.', '', $s);
                $s = str_replace('-', '', $s);
                $result .= '    $("#selector-'.$s.'-'.$uniq.'").click();' . PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
        }

        // EVENTS STATISTICS
        if ($_attributes['item'] == 'event') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            $perf = Performance::get_event_values();

            if ($_attributes['metric'] == 'system' || $_attributes['metric'] == 'service' || $_attributes['metric'] == 'device_name') {
                wp_enqueue_script('lws-nvd3');
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg style="overflow:visible;"></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat'][$_attributes['metric']] . ';' . PHP_EOL;
                //$result .= '    var xValues'.$uniq.' = ' . $perf['dat'][$_attributes['metric'].'_values'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.multiBarChart()' . PHP_EOL;
                $result .= '               .reduceXTicks(false)' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true)' . PHP_EOL;
                $result .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","grouped":"' . __('Grouped', 'live-weather-station') . '"});' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                //$result .= '                 .tickValues(xValues'.$uniq.')' . PHP_EOL;
                $result .= '                 .rotateLabels(-30);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(d3.format("s"));' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }

            if ($_attributes['metric'] == 'density' || $_attributes['metric'] == 'criticality') {
                wp_enqueue_script('lws-cal-heatmap');
                wp_enqueue_style('lws-cal-heatmap');
                if ((bool)get_option('live_weather_station_force_frontend_styling')) {
                    wp_enqueue_style('buttons');
                }
                if ($_attributes['metric'] == 'density') {
                    $data = $perf['dat']['density'];
                    $data_datemin = $perf['dat']['density_datemin'];
                    $data_densitymax = $perf['dat']['density_max'];
                }
                else {
                    $data = $perf['dat']['criticality'];
                    $data_datemin = $perf['dat']['criticality_datemin'];
                    $data_densitymax = $perf['dat']['criticality_max'];
                }
                $range = 8;
                $start_date = 1000 * (time() - (($range - 1) * 86400));
                $min_date = 1000 * $data_datemin;
                $step = 6;
                $iter = round($data_densitymax / $step, 0);
                $legend = array();
                for ($i = 1; $i < $step; $i++) {
                    $legend[] = $i * $iter;
                }
                $legend = '[' . implode(',', $legend) . ']';
                $result = '<div id="selectors-'.$uniq.'" class="wp-core-ui">' . PHP_EOL;
                $result .= '  <div id="previous-'.$uniq.'" class="button" style="margin-right: 6px; margin-bottom:10px;"><i class="fa fa-caret-left"></i></div>' . PHP_EOL;
                $result .= '  <div id="next-'.$uniq.'" class="button" style="margin-right: 6px; margin-bottom:10px;"><i class="fa fa-caret-right"></i></div>' . PHP_EOL;
                $result .= '</div>' . PHP_EOL;
                $result .= '<div id="' . $uniq . '" ></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '      var chart'.$uniq.'= new CalHeatMap();' . PHP_EOL;
                $result .= '      var today_date= new Date();' . PHP_EOL;
                $result .= '      var min_date= new Date(' . $min_date. ');' . PHP_EOL;
                $result .= '      var start_date= new Date(' . $start_date . ');' . PHP_EOL;
                $result .= '      chart'.$uniq.'.init({' . PHP_EOL;
                $result .= '          data: ' . $data . ',' . PHP_EOL;
                $result .= '          itemSelector: "#'.$uniq.'",' . PHP_EOL;
                $result .= '          previousSelector: "#previous-'.$uniq.'",' . PHP_EOL;
                $result .= '          nextSelector: "#next-'.$uniq.'",' . PHP_EOL;
                $result .= '          range: ' . $range . ',' . PHP_EOL;
                $result .= '          start: start_date,' . PHP_EOL;
                $result .= '          maxDate: today_date,' . PHP_EOL;
                $result .= '          minDate: min_date,' . PHP_EOL;
                $result .= '          legend: ' . $legend . ',' . PHP_EOL;
                $result .= '          subDomainDateFormat: "%H:%M-%H:59",' . PHP_EOL;
                $result .= '          domain: "day",' . PHP_EOL;
                $result .= '          cellSize: 14,' . PHP_EOL;
                $result .= '          cellRadius: 2,' . PHP_EOL;
                $result .= '          tooltip: true,' . PHP_EOL;
                if ($_attributes['metric'] == 'density') {
                    $result .= '          considerMissingDataAsZero: false,' . PHP_EOL;
                    $result .= '          legendColors: ["#BBCCDD", "#122448"],' . PHP_EOL;
                    $result .= '          subDomainTitleFormat: {empty: "' . sprintf(__('%s <br/>No event', 'live-weather-station'), '{date}'). '", filled: "' . sprintf('%s <br/>%s %s', '{date}', '{count}', '{name}'). '"},' . PHP_EOL;
                    $result .= '          itemName: ["' . mb_strtolower(__('Event', 'live-weather-station')) . '", "' . mb_strtolower(__('Events', 'live-weather-station')) . '"],' . PHP_EOL;
                    $result .= '          legendTitleFormat: {lower: "' . sprintf(__('Less than %s %s.', 'live-weather-station'), '{min}', '{name}'). '",inner: "' . sprintf(__('Between %s and %s %s.', 'live-weather-station'), '{down}', '{up}', '{name}'). '",upper: "' . sprintf(__('More than %s %s.', 'live-weather-station'), '{max}', '{name}'). '"}' . PHP_EOL;
                }
                else {
                    $result .= '          considerMissingDataAsZero: true,' . PHP_EOL;
                    $result .= '          legendColors: ["#D2DE76", "#AD001D"],' . PHP_EOL;
                    $result .= '          subDomainTitleFormat: {empty: "' . sprintf(__('%s <br/>No data', 'live-weather-station'), '{date}'). '", filled: "' . sprintf(__('%s <br/>%s at %s', 'live-weather-station'), '{date}', '{name}', '{count}'). '"},' . PHP_EOL;
                    $result .= '          itemName: ["' . __('Criticality', 'live-weather-station') . '", "' . __('Criticality', 'live-weather-station') . '"],' . PHP_EOL;
                    $result .= '          legendTitleFormat: {lower: "' . sprintf(__('%s lower than %s.', 'live-weather-station'), '{name}', '{min}'). '",inner: "' . sprintf(__('%s between %s and %s.', 'live-weather-station'), '{name}', '{down}', '{up}'). '",upper: "' . sprintf(__('%s greater than %s.', 'live-weather-station'), '{name}', '{max}'). '"}' . PHP_EOL;
                }
                $result .= '      });' . PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
        }

        // CRON PERFORMANCE STATISTICS
        if ($_attributes['item'] == 'task') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            $perf = Performance::get_cron_values();
            if ($_attributes['metric'] == 'count_by_pool') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['count_by_pool'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.stackedAreaChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .clipEdge(true)' . PHP_EOL;
                $result .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","stream":"' . __('Stream', 'live-weather-station') . '","expanded":"' . __('Expanded', 'live-weather-station') . '"})' . PHP_EOL;
                $result .= '               .interpolate("cardinal")' . PHP_EOL;
                $result .= '               .color(d3.scale.category10().range())' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(d3.format("s"));' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
            if ($_attributes['metric'] == 'time_by_pool') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['time_by_pool'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.lineChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .interpolate("cardinal")' . PHP_EOL;
                $result .= '               .color(d3.scale.category10().range())' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d + " ms"; });' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
            if ($_attributes['metric'] == 'time_for_history' || $_attributes['metric'] == 'time_for_system' || $_attributes['metric'] == 'time_for_pull' || $_attributes['metric'] == 'time_for_push') {
                wp_enqueue_script('lws-colorbrewer');
                $cpt = substr_count($perf['dat'][$_attributes['metric']], '"key"');
                if ($cpt < 3) {
                    $cpt = 3;
                }
                if ($cpt > 11) {
                    $cpt = 11;
                }
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat'][$_attributes['metric']] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.lineChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .interpolate("linear")' . PHP_EOL;
                $result .= '               .color(colorbrewer.Set3[' . $cpt . '])' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d + " ms"; });' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
        }

        // CACHE PERFORMANCE STATISTICS
        if ($_attributes['item'] == 'cache') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            $perf = Performance::get_cache_values();
            if ($_attributes['metric'] == 'count') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['count'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.stackedAreaChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .clipEdge(true)' . PHP_EOL;
                $result .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","stream":"' . __('Stream', 'live-weather-station') . '","expanded":"' . __('Expanded', 'live-weather-station') . '"})' . PHP_EOL;
                $result .= '               .controlOptions(["Expanded","Stacked"])' . PHP_EOL;
                $result .= '               .interpolate("cardinal")' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(d3.format("s"));' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
            if ($_attributes['metric'] == 'time') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['time'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.multiBarChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","grouped":"' . __('Grouped', 'live-weather-station') . '"});' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d + " ms"; });' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
            if ($_attributes['metric'] == 'efficiency') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['efficiency'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.lineChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .interpolate("cardinal")' . PHP_EOL;
                $result .= '               .color(d3.scale.category10().range())' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickFormat(d3.format(",.1%"));' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
            if ($_attributes['metric'] == 'time_saving') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat']['time_saving'] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.lineChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .interpolate("cardinal")' . PHP_EOL;
                $result .= '               .color(d3.scale.category10().range())' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d %H:%M")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .tickPadding(-21)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%H:%M:%S:%L")(new Date(1971, 8, 21, 0, 0, 0, d)) });' . PHP_EOL;
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
        }

        // DATABASE STATISTICS
        if ($_attributes['item'] == 'database') {
            wp_enqueue_style('lws-nvd3');
            wp_enqueue_script('lws-nvd3');
            $perf = Performance::get_database_values();
            if ($_attributes['metric'] == 'table_size' || $_attributes['metric'] == 'row_count') {
                $height = ($_attributes['height'] == '' ? '500px' : $_attributes['height']);
                $result = '<div id="' . $uniq . '" style="height: ' . $height . ';"><svg></svg></div>' . PHP_EOL;
                $result .= '<script language="javascript" type="text/javascript">' . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    var data'.$uniq.' =' . $perf['dat'][$_attributes['metric']] . ';' . PHP_EOL;
                $result .= '    nv.addGraph(function() {' . PHP_EOL;
                $result .= '      var chart'.$uniq.' = nv.models.stackedAreaChart()' . PHP_EOL;
                $result .= '               .x(function(d) {return d[0]})' . PHP_EOL;
                $result .= '               .y(function(d) {return d[1]})' . PHP_EOL;
                $result .= '               .clipEdge(true)' . PHP_EOL;
                $result .= '               .controlLabels({"stacked":"' . __('Stacked', 'live-weather-station') . '","stream":"' . __('Stream', 'live-weather-station') . '","expanded":"' . __('Expanded', 'live-weather-station') . '"})' . PHP_EOL;
                $result .= '               .interpolate("cardinal")' . PHP_EOL;
                $result .= '               .color(d3.scale.category20().range())' . PHP_EOL;
                $result .= '               .useInteractiveGuideline(true);' . PHP_EOL;
                $result .= '      chart'.$uniq.'.xAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                $result .= '                 .ticks(3)' . PHP_EOL;
                $result .= '                 .tickFormat(function(d) { return d3.time.format("%Y-%m-%d")(new Date(d)) });' . PHP_EOL;
                $result .= '      chart'.$uniq.'.yAxis' . PHP_EOL;
                $result .= '                 .showMaxMin(false)' . PHP_EOL;
                if ($_attributes['metric'] == 'table_size') {
                    $result .= '                 .tickFormat(d3.format(".3s"));' . PHP_EOL;
                }
                if ($_attributes['metric'] == 'row_count') {
                    $result .= '                 .tickFormat(d3.format(".0"));' . PHP_EOL;
                }
                $result .= '      d3.select("#'.$uniq.' svg").datum(data'.$uniq.').transition().duration(500).call(chart'.$uniq.');' . PHP_EOL;
                $result .= '      nv.utils.windowResize(chart'.$uniq.'.update);' . PHP_EOL;
                $result .= '      return chart'.$uniq.';' . PHP_EOL;
                $result .= '    });'.PHP_EOL;
                $result .= '  });' . PHP_EOL;
                $result .= '</script>' . PHP_EOL;
            }
        }
        return $result;
    }

    /**
     * Get value for LCD panel shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    1.0.0
     */
    public function lcd_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','design' => '','size' => '','speed' => ''), $attributes );
        $fingerprint = uniqid('', true);
        $uniq = 'lcd'.substr ($fingerprint, count($fingerprint)-6, 80);
        $name = $this->get_operational_station_name($_attributes['device_id']);
        $scalable='false';
        if ($_attributes['size'] == 'scalable') {
            $_attributes['size'] = 'small';
            $scalable='true';
        }
        if (is_array($name)) {
            return __(LWS_PLUGIN_NAME, 'live-weather-station').' - '.$name['condition']['message'];
        }
        $name = substr($name, 0, 20);
        wp_enqueue_style('lws-lcd');
        wp_enqueue_script('lws-lcd');
        $result  = '<div id="'.$uniq.'"></div>'.PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">'.PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        $result .= '    var c'.$uniq.' = new lws_lcd.LCDPanel({'.PHP_EOL;
        $result .= '                    id              : "id'.$uniq.'",'.PHP_EOL;
        $result .= '                    parentId        : "'.$uniq.'",'.PHP_EOL;
        $result .= '                    upperCenterText : "'.$name.'",'.PHP_EOL;
        $result .= '                    qDevice         : "'.$_attributes['device_id'].'",'.PHP_EOL;
        $result .= '                    qModule         : "'.$_attributes['module_id'].'",'.PHP_EOL;
        $result .= '                    qMeasure        : "'.$_attributes['measure_type'].'",'.PHP_EOL;
        $result .= '                    qPostUrl        : "'.LWS_AJAX_URL.'",'.PHP_EOL;
        $result .= '                    design          : "'.$_attributes['design'].'",'.PHP_EOL;
        $result .= '                    size            : "'.$_attributes['size'].'",'.PHP_EOL;
        $result .= '                    scalable        : '.(string)$scalable.','.PHP_EOL;
        $result .= '                    cycleSpeed      : "'.$_attributes['speed'].'"'.PHP_EOL;
        $result .= '    });'.PHP_EOL;
        $result .= '  });'.PHP_EOL;
        $result .= '</script>'.PHP_EOL;
        return $result;
    }

    /**
     * Get value for lcd.
     *
     * @return array $attributes The value queryed.
     * @since 1.0.0
     */
    public function lcd_value($attributes) {
        $fingerprint = md5(json_encode($attributes));
        $response = Cache::get_frontend($fingerprint);
        if ($response) {
            return $response;
        }
        $device_id = $attributes['device_id'];
        $module_id = $attributes['module_id'];
        $measure_type = $attributes['measure_type'];
        $computed = !(bool)get_option('live_weather_station_measure_only') ;
        if ((strtolower($module_id) == 'outdoor') || (strtolower($measure_type) == 'aggregated' && OWM_Base_Collector::is_owm_current_module($module_id))) {
            $raw_datas = $this->get_outdoor_datas($device_id, true);
            $measure_type = 'outdoor';
        }
        elseif (strtolower($measure_type) == 'aggregated' && OWM_Base_Collector::is_owm_pollution_module($module_id)) {
            $raw_datas = $this->get_pollution_datas($device_id, false);
            $measure_type = 'pollution';
        }
        elseif (strtolower($module_id) == 'aggregated') {
            $raw_datas = $this->get_all_datas($device_id, true);
        }
        elseif (strtolower($module_id) == 'psychrometric') {
            $raw_datas = $this->get_computed_datas($device_id, true);
            $measure_type = 'psychrometric';
        }
        else {
            $raw_datas = $this->get_module_datas($module_id, (OWM_Base_Collector::is_owm_pollution_module($module_id) ? false : true));
        }
        $response = array();
        if (array_key_exists('condition', $raw_datas)) {
            $measure['min'] = 0;
            $measure['max'] = 0;
            $measure['value'] = 0;
            $measure['unit'] = '';
            $measure['decimals'] = 1;
            $measure['sub_unit'] = '';
            $measure['show_sub_unit'] = false;
            $measure['show_min_max'] = false;
            $measure['title'] = __( 'Error code ' , 'live-weather-station').$raw_datas['condition']['value'];
            if ($raw_datas['condition']['value'] == 3 || $raw_datas['condition']['value'] == 4) {
                $save_locale = setlocale(LC_ALL,'');
                setlocale(LC_ALL, lws_get_display_locale());
                $measure['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', __('No data', 'live-weather-station'));
                setlocale(LC_ALL, $save_locale);
            }
            $measure['battery'] = 'full';
            $measure['trend'] = '';
            $measure['show_trend'] = false;
            $measure['show_alarm'] = true;
            $measure['signal'] = 0;
            $response[] = $measure;
        }
        else {
            $datas = $this->format_lcd_datas($raw_datas, $measure_type, $computed);
            if ($datas['condition']['value'] != 0) {
                $measure['min'] = 0;
                $measure['max'] = 0;
                $measure['value'] = 0;
                $measure['unit'] = '';
                $measure['decimals'] = 1;
                $measure['sub_unit'] = '';
                $measure['show_sub_unit'] = false;
                $measure['show_min_max'] = false;
                $measure['title'] = __( 'Error code ' , 'live-weather-station').$datas['condition']['value'];
                if ($datas['condition']['value'] == 3 || $datas['condition']['value'] == 4) {
                    $save_locale = setlocale(LC_ALL,'');
                    setlocale(LC_ALL, lws_get_display_locale());
                    $measure['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', __('No data', 'live-weather-station'));
                    setlocale(LC_ALL, $save_locale);
                }
                $measure['battery'] = 'full';
                $measure['trend'] = '';
                $measure['show_trend'] = false;
                $measure['show_alarm'] = true;
                $measure['signal'] = 0;
                $response[] = $measure;
            }
            else {
                $response = $datas['datas'];
            }
        }
        Cache::set_frontend($fingerprint, $response);
        return $response;
    }

    /**
     * Stringify an array of labels.
     *
     * @return  string  $attributes The attributes of the value queryed by the shortcode.
     * @since    2.1.0
     */
    private function get_txt($source, $val) {
        $r = '';
        if (count($source) > 0 && $source[0] != 'none') {
            foreach ($source as $s) {
                if (strlen($r) > 0) {
                    if ($s == 'unit') {$b_sep = ' ('; $e_sep = ')';}
                    else { $b_sep = ' - '; $e_sep = '';}
                }
                else { $b_sep = ''; $e_sep = '';}
                $r = $r . $b_sep . $val[$s] . $e_sep;
            }
        }
        return $r;
    }

    /**
     * Get value for clean gauge element.
     *
     * @return  string  $attributes The value queryed.
     * @since    2.1.0
     */
    public function justgage_value($attributes, $full=false) {
        $_attributes = shortcode_atts(array('device_id' => '', 'module_id' => '', 'measure_type' => '', 'element' => '', 'format' => ''), $attributes);
        $fingerprint = md5(($full?'full':'partial').json_encode($attributes));
        $result = Cache::get_frontend($fingerprint);
        if ($result) {
            return $result;
        }
        $_result = $this->get_line_datas($_attributes, false, true);
        $result = array();
        $val = array();
        if (count($_result) > 0) {
            foreach ($_result as $line) {
                if ($line['measure_type'] == $_attributes['measure_type']) {
                    $val = $line;
                }
            }
        }
        if (empty($val)) {
            $value = 0;
            $precision = 0;
            $min = 0;
            $max = 0;
            if ($full) {
                $result['type'] = $this->get_measurement_type($_attributes['measure_type']);
                $result['shorttype'] = $this->get_measurement_type($_attributes['measure_type'], true);
                $result['unit'] = $this->output_unit($_attributes['measure_type'])['unit'];
                $_attributes['measure_type'] = 'sos';
                $_result = $this->get_line_datas($_attributes, false, true);
                if (count($_result) > 0) {
                    $master = $_result[0];
                    $result['station'] = $master['device_name'];
                    $result['module'] = DeviceManager::get_module_name($master['device_id'], $master['module_id']);// $master['module_name'];
                } else {
                    $result['station'] = '';
                    $result['module'] = '';
                }
            }
        }
        else {
            $master = $_result[0];
            if (count($_result) > 1) {
                foreach ($_result as $line) {
                    if ($line['measure_type'] == $_attributes['measure_type']) {
                        $master = $line;
                    }
                }
            }
            $module_type = $master['module_type'];
            $measure_type = $master['measure_type'];
            $value = $this->output_value($master['measure_value'], $measure_type);
            $precision = $this->decimal_for_output($measure_type, $value);
            if ($precision < 2) {
                $prec = 0;
            }
            else {
                $prec = 1;
            }
            $min = round($this->get_measurement_min($measure_type, $module_type), $prec);
            $max = round($this->get_measurement_max($measure_type, $module_type), $prec);
            // Adapted min & max for temperature
            if ($measure_type == 'temperature' && (get_option('live_weather_station_min_max_mode') == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'temperature_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'temperature_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 3 ;
                    if (strtolower($module_type)=='namodule1' || strtolower($module_type)=='nacurrent') {
                        $delta = 6 ;
                    }
                    if ($min <= $max) {
                        $min = floor($this->output_value($min - $delta, $measure_type));
                        $max = ceil($this->output_value($max + $delta, $measure_type));
                    }
                }
            }
            // Adapted min & max for pressure
            if ($measure_type == 'pressure' && (get_option('live_weather_station_min_max_mode') == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'pressure_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'pressure_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 5 ;
                    if ($min <= $max) {
                        if ($min - $delta < 980) {
                            $min = 980 + $delta;
                        }
                        if ($max + $delta > 1080) {
                            $max = 1080 - $delta;
                        }
                        $min = floor($this->output_value($min - $delta, $measure_type));
                        $max = ceil($this->output_value($max + $delta, $measure_type));
                    }
                }
            }
            // Adapted min & max for humidity
            if ($measure_type == 'humidity' && (get_option('live_weather_station_min_max_mode') == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'humidity_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'humidity_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 10 ;
                    if ($min <= $max) {
                        $min = round($this->output_value($min - $delta, $measure_type));
                        $max = round($this->output_value($max + $delta, $measure_type));
                        if ($min < 0) {
                            $min = 0;
                        }
                        if ($max > 100) {
                            $max = 100;
                        }
                    }
                }
            }
            if ($full) {
                $result['station'] = $master['device_name'];
                $result['module'] = DeviceManager::get_module_name($master['device_id'], $master['module_id']);
                $result['type'] = $this->get_measurement_type($measure_type, false, $module_type);
                $result['shorttype'] = $this->get_measurement_type($measure_type, true, $module_type);
                $result['unit'] = $this->output_unit($measure_type, $module_type)['unit'];
            }
        }
        $result['value'] = round($value, $precision);
        $result['decimals'] = $precision;
        $result['min'] = $min;
        $result['max'] = $max;
        if ($full && substr($result['module'], 0, 1) == '[') {
            $result['module'] = __('Outdoor', 'live-weather-station');
        }
        Cache::set_frontend($fingerprint, $result);
        return $result;
    }


    /**
     * Get attributes for clean gauge shortcodes.
     *
     * @return array The attributes of the value queryed by the shortcode.
     * @since 2.1.0
     */
    public function justgage_attributes($attributes) {
        $result = array();
        $result['id'] = $attributes['id'];

        // POINTER
        $pointerOptions = array();
        $result['pointer'] = ($attributes['pointer'] != 'none');
        switch ($attributes['pointer']) {
            case 'external':
                $pointerOptions['toplength'] = 4;
                break;
            case 'internal':
                $pointerOptions['toplength'] = -4;
                break;
        }

        // SIZE
        $pb = array();
        switch ($attributes['size']) {
            case 'micro':
                $result['width'] = 75;
                $result['height'] = 75;
                $result['relativeGaugeSize'] = false;
                $result['hideMinMax'] = true;
                break;
            case 'small':
                $result['width'] = 100;
                $result['height'] = 100;
                $result['relativeGaugeSize'] = false;
                $pointerOptions['bottomwidth'] = 2 ;
                $pb['thin']['external'] = -10;
                $pb['thin']['internal'] = 6;
                $pb['standard']['external'] = -14;
                $pb['standard']['internal'] = 2;
                $pb['fat']['external'] = -20;
                $pb['fat']['internal'] = -4;
                $pb['pie']['external'] = -48;
                $pb['pie']['internal'] = -32;
                $pb['full']['external'] = -44;
                $pb['full']['internal'] = -27;
                break;
            case 'medium':
                $result['width'] = 225;
                $result['height'] = 225;
                $result['relativeGaugeSize'] = false;
                $pointerOptions['bottomwidth'] = 5 ;
                $pb['thin']['external'] = -17;
                $pb['thin']['internal'] = 7;
                $pb['standard']['external'] = -26;
                $pb['standard']['internal'] = -1;
                $pb['fat']['external'] = -39;
                $pb['fat']['internal'] = -15;
                $pb['pie']['external'] = -103;
                $pb['pie']['internal'] = -78;
                $pb['full']['external'] = -93;
                $pb['full']['internal'] = -68;
                break;
            case 'large':
                $result['width'] = 350;
                $result['height'] = 350;
                $pointerOptions['bottomwidth'] = 8 ;
                $pb['thin']['external'] = -24;
                $pb['thin']['internal'] = 0;
                $pb['standard']['external'] = -38;
                $pb['standard']['internal'] = -3;
                $pb['fat']['external'] = -59;
                $pb['fat']['internal'] = -25;
                $pb['pie']['external'] = -158;
                $pb['pie']['internal'] = -124;
                $pb['full']['external'] = -142;
                $pb['full']['internal'] = -108;
                break;
            case 'scalable':
                $result['relativeGaugeSize'] = true;
                $pointerOptions['bottomwidth'] = 5 ;
                $pb['thin']['external'] = -16;
                $pb['thin']['internal'] = 8;
                $pb['standard']['external'] = -23;
                $pb['standard']['internal'] = -1;
                $pb['fat']['external'] = -34;
                $pb['fat']['internal'] = -11;
                $pb['pie']['external'] = -87;
                $pb['pie']['internal'] = -64;
                $pb['full']['external'] = -65;
                $pb['full']['internal'] = -41;
                break;
        }

        // DESIGN
        $design = explode('-',$attributes['design']);
        $result['donut'] = (in_array('full', $design));
        if (in_array('thin', $design)) {
            $result['gaugeWidthScale'] = 0.15;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['thin'][$attributes['pointer']];
            }
            if ($attributes['size'] == 'large' && $attributes['pointer'] == 'internal') {
                $pointerOptions['toplength'] = -8;
                $pointerOptions['bottomlength'] = 13;
            }
        }
        if (in_array('standard', $design)) {
            $result['gaugeWidthScale'] = 0.40;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['standard'][$attributes['pointer']];
            }
            if ($attributes['size'] == 'small' && $attributes['pointer'] == 'internal') {
                $pointerOptions['toplength'] = -6;
                $pointerOptions['bottomlength'] = 4;
            }
            if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'internal') {
                $pointerOptions['toplength'] = -2;
            }
            if ($result['donut']) {
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'internal') {
                    $pointerOptions['bottomlength'] = -1;
                    $pointerOptions['toplength'] = 0;
                }
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'external') {
                    $pointerOptions['bottomlength'] = -21;
                }
            }
        }
        if (in_array('fat', $design)) {
            $result['gaugeWidthScale'] = 0.80;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['fat'][$attributes['pointer']];
            }
            if ($result['donut']) {
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'internal') {
                    $pointerOptions['bottomlength'] = -6;
                }
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'external') {
                    $pointerOptions['bottomlength'] = -30;
                }
            }
        }
        if (in_array('pie', $design)) {
            $result['gaugeWidthScale'] = ($result['donut'] ? 2.38 : 2.68);
            $result['hideMinMax'] = true;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['pie'][$attributes['pointer']];
                if ($result['donut']) {
                    $pointerOptions['bottomlength'] = $pb['full'][$attributes['pointer']];
                }
            }
        }
        $result['shadowOpacity'] = (in_array('flat', $design) ? 0 : $result['gaugeWidthScale']/2.5);
        $result['counter'] = 0;

        // COLORS
        $color = explode('-',$attributes['color']);
        // text
        if (in_array('lgt', $color)) {
            $result['titleFontColor'] = '#999999';
            $result['valueFontColor'] = '#010101';
            $result['labelFontColor'] = '#B3B3B3';
        }
        if (in_array('drk', $color)) {
            $result['titleFontColor'] = '#999999';
            if (in_array('pie', $design)) {
                $result['valueFontColor'] = '#333333';
            }
            else {
                $result['valueFontColor'] = '#FEFEFE';
            }
            if ($result['donut']) {
                $result['labelFontColor'] = '#B3B3B3';
            }
            else {
                $result['labelFontColor'] = '#777777';
            }
        }
        if (in_array('transparent', $color)) {
            $result['levelColors'] = ['#EDEBEB'];
        }
        if (in_array('flag', $color)) {
            $result['levelColors'] = ['#0C58AC', '#BCAEFA', '#C30404'];
        }
        if (in_array('pinky', $color)) {
            $result['levelColors'] = ['#970000', '#A100BE', '#FF18EC'];
        }
        if (in_array('aquamarine', $color)) {
            $result['levelColors'] = ['#4E61C3', '#2D7EF7', '#00EDF0'];
        }
        if (in_array('bw', $color)) {
            $result['levelColors'] = ['#EDEBEB', '#000000'];
        }
        if (in_array('solidred', $color)) {
            $result['levelColors'] = ['#C30404'];
        }
        if (in_array('solidorange', $color)) {
            $result['levelColors'] = ['#FE9500'];
        }
        if (in_array('solidyellow', $color)) {
            $result['levelColors'] = ['#F1EE12'];
        }
        if (in_array('solidgreen', $color)) {
            $result['levelColors'] = ['#00C312'];
        }
        if (in_array('solidblue', $color)) {
            $result['levelColors'] = ['#0C58AC'];
        }
        if (in_array('solidpurple', $color)) {
            $result['levelColors'] = ['#6600B4'];
        }
        if (in_array('solidblack', $color)) {
            $result['levelColors'] = ['#000000'];
        }
        $pointerOptions['color'] = $result['valueFontColor'] ;
        if (in_array('lgt', $color)) {
            $pointerOptions['color'] = '#333333' ;
        }
        if (in_array('drk', $color) && $attributes['pointer'] == 'internal') {
            switch ($attributes['size']) {
                case 'small':
                    if (in_array('fat', $design)) {
                        $pointerOptions['color'] = '#333333' ;
                    }
                    break;
                case 'medium':
                case 'large':
                case 'scalable':
                    if (in_array('standard', $design) || in_array('fat', $design)) {
                        $pointerOptions['color'] = '#333333' ;
                    }
                    break;
            }
        }
        if (in_array('drk', $color) && $attributes['pointer'] == 'external' && in_array('pie', $design)) {
            $pointerOptions['color'] = '#FEFEFE' ;
        }

        // FORCED COLORS
        if (array_key_exists('force', $attributes)) {
            if ($attributes['force'] != '') {
                $forced = explode('-',$attributes['force']);
                foreach ($forced as $f) {
                    $col = explode(':',$f);
                    switch ($col[0]) {
                        case 'ptr':
                            $pointerOptions['color'] = $col[1] ;
                            break;
                        case 'ttl':
                            $result['titleFontColor'] = $col[1] ;
                            break;
                        case 'lbl':
                            $result['labelFontColor'] = $col[1] ;
                            break;
                        case 'val':
                            $result['valueFontColor'] = $col[1] ;
                            break;
                    }
                }
            }
        }

        $values = $this->justgage_value($attributes, true);

        // DATAS
        $result['value'] = $values['value'];
        if ($result['donut']) {
            $result['min'] = 0;
        }
        else {
            $result['min'] = $values['min'];
        }
        $result['max'] = $values['max'];
        $result['decimals'] = $values['decimals'];

        // TEXTS
        if ($attributes['unit'] == 'unit') {
            $result['symbol'] = $values['unit'];
        }
        $result['title'] = $this->get_txt(explode('-',$attributes['title']), $values);
        $result['label'] = $this->get_txt(explode('-',$attributes['subtitle']), $values);
        $result['counter'] = true;
        if ($result['pointer']) {
            $result['pointerOptions'] = $pointerOptions;
        }
        if (!$result['donut']) {
            unset($result['donut']);
        }
        return $result;
    }


    /**
     * Get value for clean gauge shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    2.1.0
     */
    public function justgage_shortcodes($attributes) {
        $fingerprint = uniqid('', true);
        $uniq = 'jgg'.substr ($fingerprint, count($fingerprint)-6, 80);
        $time = 1000 * (120 + rand(-20, 20));
        $_attributes = shortcode_atts( array('id' => $uniq,'device_id' => '','module_id' => '','measure_type' => '','design' => '','color' => '','force' => '','pointer' => '','title' => '','subtitle' => '','unit' => '','size' => ''), $attributes );
        $sc_device = $_attributes['device_id'];
        $sc_module = $_attributes['module_id'];
        $sc_measurement = $_attributes['measure_type'];
        $values = json_encode($this->justgage_attributes($_attributes));
        switch ($attributes['size']) {
            case 'small':
                $h = '100px';
                $w = '100px';
                break;
            case 'medium':
                $h = '225px';
                $w = '225px';
                break;
            case 'large':
                $h = '350px';
                $w = '350px';
                break;
            case 'scalable':
                $h = '100%';
                $w = '100%';
                break;
        }
        $style = 'width:'.$w.';height:'.$h.';';
        wp_enqueue_script('lws-justgage');
        $result  = '<div id="'.$uniq.'" style="'.$style.'"></div>'.PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">'.PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        $result .= '    var g'.$uniq.' = new JustGage('.$values.');'.PHP_EOL;
        $result .= '  setInterval(function() {'.PHP_EOL;
        $result .= '    var http = new XMLHttpRequest();'.PHP_EOL;
        $result .= '    var params = "action=lws_query_justgage_datas";'.PHP_EOL;
        $result .= '    params = params+"&device_id='.$sc_device.'";'.PHP_EOL;
        $result .= '    params = params+"&module_id='.$sc_module.'";'.PHP_EOL;
        $result .= '    params = params+"&measure_type='.$sc_measurement.'";'.PHP_EOL;
        $result .= '    http.open("POST", "'.LWS_AJAX_URL.'", true);'.PHP_EOL;
        $result .= '    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");'.PHP_EOL;
        $result .= '    http.onreadystatechange = function () {'.PHP_EOL;
        $result .= '      if (http.readyState == 4 && http.status == 200) {'.PHP_EOL;
        $result .= '        var odatas = JSON.parse(http.responseText);'.PHP_EOL;
        $result .= '        if ( typeof odatas != "undefined") {'.PHP_EOL;
        $result .= '          g'.$uniq.'.refresh(odatas.value, odatas.max);'.PHP_EOL;
        $result .= '        }'.PHP_EOL;
        $result .= '      }'.PHP_EOL;
        $result .= '    }'.PHP_EOL;
        $result .= '    http.send(params);'.PHP_EOL;
        $result .= '  }, '.$time.');'.PHP_EOL;
        $result .= '});'.PHP_EOL;
        $result .= '</script>'.PHP_EOL;
        return $result;
    }

    /**
     * Get value for steel meter element.
     *
     * @return  string  $attributes The value queryed.
     * @since    2.2.0
     */
    public function steelmeter_value($attributes, $full=false)
    {
        $_attributes = shortcode_atts(array('device_id' => '', 'module_id' => '', 'measure_type' => '', 'element' => '', 'format' => ''), $attributes);
        $fingerprint = md5(($full?'full':'partial').json_encode($attributes));
        $result = Cache::get_frontend($fingerprint);
        if ($result) {
            return $result;
        }
        $result = array();
        $value = 0;
        $min = 0;
        $max = 0;
        $precision = 0;
        $value_min = -9999;
        $value_max = -9999;
        $value_trend = 'steady';
        $value_aux = -9999;
        $alarm = false;
        $measure_type = $_attributes['measure_type'];
        $_result = $this->get_line_datas($_attributes, false, true);
        $val = array();
        if (count($_result) > 0) {
            foreach ($_result as $line) {
                if ($line['measure_type'] == $_attributes['measure_type']) {
                    $val = $line;
                }
            }
        }
        if (empty($val)) {
            $result['type'] = $this->get_measurement_type($_attributes['measure_type']);
            $result['unit'] = $this->output_unit($_attributes['measure_type'])['unit'];
            $_result = $this->get_line_datas($_attributes, false, true);
            if (!empty($_result)) {
                $master = $_result[0];
                $module_type = $master['module_type'];
                $measure_type = $master['measure_type'];
                $result['unit'] = $this->output_unit($measure_type, $module_type)['unit'];
                $alarm = true;
                $precision = $this->decimal_for_output($measure_type);
                if ($precision < 2) {
                    $prec = 0;
                }
                else {
                    $prec = 1;
                }
                $min = round($this->get_measurement_min($measure_type, $module_type), $prec);
                $max = round($this->get_measurement_max($measure_type, $module_type), $prec);
                $value = $min;
            }
        }
        else {
            $master = $_result[0];
            if (count($_result) > 1) {
                foreach ($_result as $line) {
                    if ($line['measure_type'] == $_attributes['measure_type']) {
                        $master = $line;
                    }
                    if ($line['measure_type'] == 'temperature_min') {
                        $value_min = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'temperature_max') {
                        $value_max = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'temperature_trend') {
                        $value_trend = strtolower($line['measure_value']);
                        if ($value_trend == 'stable') {
                            $value_trend = 'steady';
                        }
                    }
                    if ($line['measure_type'] == 'humidity_min') {
                        $value_min = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'humidity_max') {
                        $value_max = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'humidity_trend') {
                        $value_trend = strtolower($line['measure_value']);
                        if ($value_trend == 'stable') {
                            $value_trend = 'steady';
                        }
                    }
                    if ($line['measure_type'] == 'pressure_min') {
                        $value_min = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'pressure_max') {
                        $value_max = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'pressure_trend') {
                        $value_trend = strtolower($line['measure_value']);
                        if ($value_trend == 'stable') {
                            $value_trend = 'steady';
                        }
                    }
                    if ($line['measure_type'] == 'gustangle') {
                        $value_aux = $this->output_value($line['measure_value'], $measure_type);
                    }
                }
            }
            $module_type = $master['module_type'];
            $measure_type = $master['measure_type'];
            $result['unit'] = $this->output_unit($measure_type, $module_type)['unit'];
            $result['type'] = $this->get_measurement_type($measure_type, true, $module_type);
            $value = $this->output_value($master['measure_value'], $measure_type);
            $alarm = $this->is_alarm_on($master['measure_value'], $measure_type, $module_type);
            $precision = $this->decimal_for_output($measure_type, $value);
            if ($precision < 2) {
                $prec = 0;
            }
            else {
                $prec = 1;
            }
            $min = round($this->get_measurement_min($measure_type, $module_type), $prec);
            $max = round($this->get_measurement_max($measure_type, $module_type), $prec);
            // Adapted min & max for temperature
            if ($measure_type == 'temperature' && (get_option('live_weather_station_min_max_mode') == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'temperature_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'temperature_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 3 ;
                    if (strtolower($module_type)=='namodule1' || strtolower($module_type)=='nacurrent') {
                        $delta = 6 ;
                    }
                    if ($min <= $max) {
                        $min = floor($this->output_value($min - $delta, $measure_type));
                        $max = ceil($this->output_value($max + $delta, $measure_type));
                    }
                }
            }
            // Adapted min & max for pressure
            if ($measure_type == 'pressure' && (get_option('live_weather_station_min_max_mode') == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'pressure_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'pressure_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 5 ;
                    if ($min <= $max) {
                        if ($min - $delta < 980) {
                            $min = 980 + $delta;
                        }
                        if ($max + $delta > 1080) {
                            $max = 1080 - $delta;
                        }
                        $min = floor($this->output_value($min - $delta, $measure_type));
                        $max = ceil($this->output_value($max + $delta, $measure_type));
                    }
                }
            }
            // Adapted min & max for humidity
            if ($measure_type == 'humidity' && (get_option('live_weather_station_min_max_mode') == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'humidity_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'humidity_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 10 ;
                    if ($min <= $max) {
                        $min = round($this->output_value($min - $delta, $measure_type));
                        $max = round($this->output_value($max + $delta, $measure_type));
                        if ($min < 0) {
                            $min = 0;
                        }
                        if ($max > 100) {
                            $max = 100;
                        }
                    }
                }
            }
        }
        if ($full) {
            $result['decimals'] = $precision;
            $result['min'] = $min;
            $result['max'] = $max;
            if ($result['max'] <= $result['min']) {
                $result['max'] = $result['min'] +1;
            }
        }
        $result['value'] = round($value, $precision);
        $result['value_min'] = $value_min;
        $result['value_max'] = $value_max;
        $result['value_trend'] = $value_trend;
        $result['value_aux'] = ($value_aux != -9999 ? $value_aux : $result['value'] );
        $result['alarm'] = $alarm;
        Cache::set_frontend($fingerprint, $result);
        return $result;
    }


    /**
     * Get attributes for steel meter shortcodes.
     *
     * @return  string  $attributes The attributes of the value queryed by the shortcode.
     * @since    2.2.0
     */
    public function steelmeter_attributes($attributes) {
        $result = array();
        $values = $this->steelmeter_value($attributes, true);
        $result['minValue'] = $values['min'];
        $result['maxValue'] = $values['max'];
        $min = $values['min'];
        $max = $values['max'];
        if (strpos($attributes['design'], 'windcompass') !== false ) {
            $min = 0;
            $max = 360;
        }
        $digital = (strpos($attributes['design'], 'digital') > -1 );
        if (strpos($attributes['design'], '-1-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE1';
        }
        if (strpos($attributes['design'], '-2-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE2';
        }
        if (strpos($attributes['design'], '-3-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE3';
        }
        if (strpos($attributes['design'], '-4-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE4';
        }
        if (strpos($attributes['design'], '-left') !== false ) {
            $result['orientation'] = 'steelseries.Orientation.WEST';
        }
        if (strpos($attributes['design'], '-right') !== false ) {
            $result['orientation'] = 'steelseries.Orientation.EAST';
        }
        if (strpos($attributes['design'], 'windcompass-modern') !== false ) {
            $result['pointSymbolsVisible'] = false;
        }
        $result['frameDesign'] = 'steelseries.FrameDesign.'.$attributes['frame'];
        $result['backgroundColor'] = 'steelseries.BackgroundColor.'.$attributes['background'];
        if ($attributes['orientation'] != 'AUTO') {
            $result['tickLabelOrientation'] = 'steelseries.TickLabelOrientation.'.$attributes['orientation'];
        }
        $result['pointerType'] = 'steelseries.PointerType.'.$attributes['main_pointer_type'];
        $result['pointerTypeLatest'] = 'steelseries.PointerType.'.$attributes['main_pointer_type'];
        $result['pointerColor'] = 'steelseries.ColorDef.'.$attributes['main_pointer_color'];
        $result['pointerTypeAverage'] = 'steelseries.PointerType.'.$attributes['aux_pointer_type'];
        $result['pointerColorAverage'] = 'steelseries.ColorDef.'.$attributes['aux_pointer_color'];
        $knob = explode('-',$attributes['knob']);
        $result['knobType'] = 'steelseries.KnobType.'.$knob[0];
        $result['knobStyle'] = 'steelseries.KnobStyle.'.$knob[1];
        if ($attributes['lcd'] == 'NONE') {
            $result['lcdVisible'] = false;
        }
        else {
            $result['lcdColor'] = 'steelseries.LcdColor.'.$attributes['lcd'];
        }
        if ($attributes['alarm'] != 'NONE') {
            $result['userLedVisible'] = true;
            $result['userLedColor'] = 'steelseries.LedColor.'.$attributes['alarm'];
        }
        if ($attributes['trend'] != 'NONE') {
            $result['trendVisible'] = true;
            $result['trendColors'] = '[steelseries.LedColor.'.$attributes['trend'].', steelseries.LedColor.'.$attributes['trend'].', steelseries.LedColor.'.$attributes['trend'].']';
        }
        if ($attributes['minmax'] == 'cursor') {
            $result['minMeasuredValueVisible'] = true;
            $result['maxMeasuredValueVisible'] = true;
        }
        if ($attributes['index_style'] != 'NONE') {
            $style = explode('-', $attributes['index_style']);
            $tcolor = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
            $alpha = 0;
            $lighten = 1;
            if (isset($style[1])) {
                switch (strtolower($style[1])) {
                    case 'translucent':
                        $alpha = 0.1;
                        if ($tcolor->isVeryDark()) {
                            $lighten = 40;
                        }
                        else {
                            $lighten = 20;
                        }
                        break;
                    case 'liquid':
                        $alpha = 0.25;
                        if ($tcolor->isVeryDark()) {
                            $lighten = 28;
                        }
                        else {
                            $lighten = 15;
                        }
                        break;
                    case 'soft':
                        $alpha = 0.5;
                        if ($tcolor->isVeryDark()) {
                            $lighten = 15;
                        }
                        else {
                            $lighten = 10;
                        }
                        break;
                    case 'hard':
                        $alpha = 0.9;
                        $lighten = 1;
                        break;
                }
            }
            if (isset($style[0])) {
                switch (strtolower($style[0])) {
                    case 'fixed':
                        if ($digital) {
                            $color = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $rgb1 = ColorsManipulation::hexToRgb($color->lighten($lighten));
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $rgb = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $result['section'] = '[steelseries.Section('.$min.', '.$max.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$alpha.')")]';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'fadein':
                        if ($digital) {
                            $color = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $rgb1 = ColorsManipulation::hexToRgb($color->lighten($lighten*2));
                            $rgb2 = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $rgb = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $a = $alpha - $alpha * ($step - $i) * 0.9 / $step;
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$a.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'fadeout':
                        if ($digital) {
                            $color = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $rgb2 = ColorsManipulation::hexToRgb($color->lighten($lighten*2));
                            $rgb1 = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $rgb = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $a = $alpha - $alpha * $i * 0.9 / $step;
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$a.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'complementary':
                        if ($digital) {
                            $color = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $rgb1 = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $rgb2 = ColorsManipulation::hexToRgb($color->complementary());
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $color2 = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $color = new ColorsManipulation($color2->complementary());
                            $rgb2 = $color->complementary();
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $rgb = ColorsManipulation::hexToRgb($color->mix($rgb2, round((200 / $step) * $i)-100));
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$alpha.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'invcomplementary':
                        if ($digital) {
                            $color = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $rgb2 = ColorsManipulation::nameToRgb($attributes['index_color']);
                            $rgb1 = ColorsManipulation::hexToRgb($color->complementary());
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $color = new ColorsManipulation(ColorsManipulation::nameToHex($attributes['index_color']));
                            $rgb2 = $color->complementary();
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $rgb = ColorsManipulation::hexToRgb($color->mix($rgb2, round((200 / $step) * $i)-100));
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$alpha.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                }
            }
        }
        else {
            if ($digital) {
                $rgb = ColorsManipulation::nameToRgb('antiquewhite');
                $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', 1), new steelseries.rgbaColor('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', 1)])';
                $result['useValueGradient'] = true;
            }
        }
        $result['foregroundType'] = 'steelseries.ForegroundType.'.$attributes['glass'];
        switch ($attributes['size']) {
            case 'small':
                $result['size'] = 150;
                break;
            case 'medium':
                $result['size'] = 200;
                break;
            case 'large':
                $result['size'] = 250;
                break;
            case 'macro':
                $result['size'] = 300;
                break;
        }
        $result['thresholdVisible'] = false;
        $result['ledVisible'] = false;
        $result['niceScale'] = false;
        if ($values['decimals'] > 1) {
            $result['labelNumberFormat'] = 'steelseries.LabelNumberFormat.FRACTIONAL';
            $result['fractionalScaleDecimals'] = 1;
            if ($max-$min < 1) {
                $result['fractionalScaleDecimals'] = 2;
            }
        }
        if ($digital) {
            $result['valueColor'] = 'steelseries.ColorDef.WHITE';
        }
        $result['lcdDecimals'] = $values['decimals'];
        $result['titleString'] = '"'.$values['type'].'"';
        $result['unitString'] = '"• '.$values['unit'].' •"';
        $result['digitalFont'] = true;
        if (strpos($attributes['design'], 'digital') !== false ) {
            unset($result['titleString']);
            $result['unitString'] = '"'.$values['type'].' • '.$values['unit'].'"';
        }
        if (strpos($attributes['design'], 'meter-') !== false ) {
            unset($result['titleString']);
            $result['niceScale'] = true;
        }
        if (strpos($attributes['design'], 'windcompass') !== false ) {
            unset($result['titleString']);
            unset($result['unitString']);
            $result['lcdTitleStrings'] = '["'.__('Wind', 'live-weather-station').'", "'.__('Gust', 'live-weather-station').'"]';
        }
        if (strpos($attributes['design'], 'altimeter') !== false ) {
            unset($result['titleString']);
            $result['unitString'] = '"'.$values['type'].'"';
        }

        if (strpos($attributes['design'], 'windcompass-vintage') !== false ) {
            $result['roseVisible'] = true;
            $result['degreeScale'] = false;
        }
        if (strpos($attributes['design'], 'windcompass-standard') !== false ) {
            $result['roseVisible'] = true;
        }
        return $result;
    }


    /**
     * Get value for steel meter shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    2.2.0
     */
    public function steelmeter_shortcodes($attributes) {
        $result = '';
        $fingerprint = uniqid('', true);
        $uniq = 'ssm'.substr ($fingerprint, count($fingerprint)-6, 80);
        $time = 1000 * (120 + rand(-20, 20));
        $_attributes = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','design' => '',
            'frame' => '','background' => '','orientation' => '','main_pointer_type' => '','main_pointer_color' => '',
            'aux_pointer_type' => '','aux_pointer_color' => '','knob' => '','lcd' => '','alarm' => '','trend' => '',
            'minmax' => '','index_style' => '','index_color' => '','glass' => '','size' => ''), $attributes );
        $_attributes['frame'] = strtoupper($_attributes['frame']);
        $_attributes['background'] = strtoupper($_attributes['background']);
        $_attributes['orientation'] = strtoupper($_attributes['orientation']);
        $_attributes['main_pointer_type'] = strtoupper($_attributes['main_pointer_type']);
        $_attributes['main_pointer_color'] = strtoupper($_attributes['main_pointer_color']);
        $_attributes['aux_pointer_type'] = strtoupper($_attributes['aux_pointer_type']);
        $_attributes['aux_pointer_color'] = strtoupper($_attributes['aux_pointer_color']);
        $_attributes['knob'] = strtoupper($_attributes['knob']);
        $_attributes['lcd'] = strtoupper($_attributes['lcd']);
        $_attributes['alarm'] = strtoupper($_attributes['alarm']);
        $_attributes['trend'] = strtoupper($_attributes['trend']);
        $_attributes['index_style'] = strtoupper($_attributes['index_style']);
        $_attributes['index_color'] = strtoupper($_attributes['index_color']);
        $_attributes['glass'] = strtoupper($_attributes['glass']);
        $sc_device = $_attributes['device_id'];
        $sc_module = $_attributes['module_id'];
        $sc_measurement = $_attributes['measure_type'];

        $params = json_encode($this->steelmeter_attributes($_attributes));
        $value = $this->steelmeter_value($_attributes, true);

        $params = str_replace('\"', '!', $params);
        $params = str_replace('"', '', $params);
        $params = str_replace('!', '"', $params);

        switch ($attributes['size']) {
            case 'small':
                $h = '150px';
                $w = '150px';
                break;
            case 'medium':
                $h = '200px';
                $w = '200px';
                break;
            case 'large':
                $h = '250px';
                $w = '250px';
                break;
            case 'macro':
                $h = '300px';
                $w = '300px'; 
                break;
        }
        $control = 'Radial';
        $minmax = false;
        $alarm = false;
        $trend = false;
        $aux = false;
        if (strpos($attributes['design'], 'analog') !== false ) {
            $control = 'Radial';
            $minmax = true;
            $alarm = true;
            $trend = true;
            $aux = false;
        }
        if (strpos($attributes['design'], 'digital') !== false ) {
            $control = 'RadialBargraph';
            $minmax = false;
            $alarm = true;
            $trend = true;
            $aux = false;
        }
        if (strpos($attributes['design'], 'meter-') !== false ) {
            $control = 'RadialVertical';
            $minmax = true;
            $alarm = false;
            $trend = false;
            $aux = false;
        }
        if (strpos($attributes['design'], 'windcompass') !== false ) {
            $control = 'WindDirection';
            $minmax = false;
            $alarm = false;
            $trend = false;
            $aux = true;
        }
        if (strpos($attributes['design'], 'altimeter') !== false ) {
            $control = 'Altimeter';
            $minmax = false;
            $alarm = false;
            $trend = false;
            $aux = false;
        }

        if ($value['value'] <= $value['min']) {
            $value['value'] = $value['value'] + 0.00001;
        }

        $style = 'width:'.$w.';height:'.$h.';';
        wp_enqueue_script('lws-steelseries');
        $result  = '<canvas id="'.$uniq.'" style="'.$style.'"></canvas>'.PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">'.PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        $result .= '    var g'.$uniq.' = new steelseries.'.$control.'('.$uniq.', '.$params.');'.PHP_EOL;
        if ($aux) {
            $result .= '      g'.$uniq.'.setValueAnimatedLatest('.$value['value'].');'.PHP_EOL;
            $result .= '      g'.$uniq.'.setValueAnimatedAverage('.$value['value_aux'].');'.PHP_EOL;
        }
        else {
            $result .= '      g'.$uniq.'.setValueAnimated('.$value['value'].', function() {'.PHP_EOL;
        }
        if ($alarm) {
            $result .= '        g'.$uniq.'.blinkUserLed('.$value['alarm'].');'.PHP_EOL;
        }
        if ($minmax) {
            $result .= '        g'.$uniq.'.resetMinMeasuredValue();'.PHP_EOL;
            $result .= '        g'.$uniq.'.resetMaxMeasuredValue();'.PHP_EOL;
            if ($value['value_min'] > -9999) {
                $result .= '        g'.$uniq.'.setMinMeasuredValue('.$value['value_min'].');'.PHP_EOL;
            }
            if ($value['value_max'] > -9999) {
                $result .= '        g'.$uniq.'.setMaxMeasuredValue('.$value['value_max'].');'.PHP_EOL;
            }
        }
        if ($trend) {
            $result .= '        g'.$uniq.'.setTrend(steelseries.TrendState.'.strtoupper($value['value_trend']).');'.PHP_EOL;
        }
        $result .= '        setInterval(function() {'.PHP_EOL;
        $result .= '          var http = new XMLHttpRequest();'.PHP_EOL;
        $result .= '          var params = "action=lws_query_steelmeter_datas";'.PHP_EOL;
        $result .= '          params = params+"&device_id='.$sc_device.'";'.PHP_EOL;
        $result .= '          params = params+"&module_id='.$sc_module.'";'.PHP_EOL;
        $result .= '          params = params+"&measure_type='.$sc_measurement.'";'.PHP_EOL;
        $result .= '          http.open("POST", "'.LWS_AJAX_URL.'", true);'.PHP_EOL;
        $result .= '          http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");'.PHP_EOL;
        $result .= '          http.onreadystatechange = function () {'.PHP_EOL;
        $result .= '            if (http.readyState == 4 && http.status == 200) {'.PHP_EOL;
        $result .= '              var odatas = JSON.parse(http.responseText);'.PHP_EOL;
        $result .= '              if ( typeof odatas != "undefined") {'.PHP_EOL;
        if ($aux) {
            $result .= '                g'.$uniq.'.setValueAnimatedLatest(odatas.value);'.PHP_EOL;
            $result .= '                g'.$uniq.'.setValueAnimatedAverage(odatas.value_aux);'.PHP_EOL;
        }
        else {
            $result .= '                g'.$uniq.'.setValueAnimated(odatas.value);'.PHP_EOL;
        }
        if ($alarm) {
            $result .= '                g'.$uniq.'.blinkUserLed(odatas.alarm);'.PHP_EOL;
        }
        if ($trend) {
            $result .= '                if (odatas.value_trend == "up") {g'.$uniq.'.setTrend(steelseries.TrendState.UP);}'.PHP_EOL;
            $result .= '                if (odatas.value_trend == "down") {g'.$uniq.'.setTrend(steelseries.TrendState.DOWN);}'.PHP_EOL;
            $result .= '                if (odatas.value_trend == "steady") {g'.$uniq.'.setTrend(steelseries.TrendState.STEADY);}'.PHP_EOL;
        }
        $result .= '              }'.PHP_EOL;
        $result .= '            }'.PHP_EOL;
        $result .= '          }'.PHP_EOL;
        $result .= '          http.send(params);'.PHP_EOL;
        $result .= '        }, '.$time.');'.PHP_EOL;
        if (!$aux) {
            $result .= '      });'.PHP_EOL;
        }
        $result .= '    });'.PHP_EOL;
        $result .= '</script>'.PHP_EOL;
        return $result;
    }

    /**
     * Get value for textual shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    1.0.0
     * @access   public
     */
    public function textual_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','element' => '','format' => ''), $attributes );
        $fingerprint = md5(json_encode($attributes));
        $result = Cache::get_frontend($fingerprint);
        if ($result) {
            return $result;
        }
        $_result = $this->get_specific_datas($_attributes);
        $err = __('Malformed shortcode. Please verify it!', 'live-weather-station') ;
        if (empty($_result)) {
            return $err;
        }
        $tz = '';
        if (($_attributes['format'] == 'local-date') || ($_attributes['format'] == 'local-time') || ($_attributes['format'] == 'local-diff')) {
            $_att = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','element' => '','format' => ''), $attributes );
            $_att['module_id'] = $_attributes['device_id'];
            $_att['measure_type'] = 'loc_timezone';
            $_att['element'] = 'measure_value';
            $tz = $this->get_specific_datas($_att)['result'][$_att['measure_type']];
        }
        $result = $_result['result'][$_attributes['measure_type']];
        $module_type = $_result['module_type'];
        switch ($_attributes['format']) {
            case 'raw':
                break;
            case 'type-formated':
                switch ($_attributes['element']) {
                    case 'module_type':
                        $result = $this->get_module_type($result);
                        break;
                    case 'measure_type':
                        $result = $this->get_measurement_type($result, $module_type);
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-unit':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['unit'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-unit-full':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['full'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-unit-long':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['long'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-raw-dimension':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['dimension'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-formated-dimension':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->get_dimension_name($this->output_unit($result, $module_type)['dimension']);
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'local-date':
                try {
                    if ((strpos($_attributes['measure_type'], 'last_') === 0) || (strpos($_attributes['measure_type'], 'first_') === 0)) {
                        $result = $this->get_date_from_mysql_utc($result, $tz) ;
                    }
                    else {
                        if ($_attributes['element'] == 'measure_timestamp') {
                            $result = $this->get_date_from_mysql_utc($result, $tz) ;
                        }
                        if ($_attributes['element'] == 'measure_value') {
                            $result = $this->get_date_from_utc($result, $tz) ;
                        }
                    }
                }
                catch(\Exception $ex) {
                    $result = $err ;
                }
                break;
            case 'local-time':
                try {
                    if ((strpos($_attributes['measure_type'], 'last_') === 0) || (strpos($_attributes['measure_type'], 'first_') === 0)) {
                        $result = $this->get_time_from_mysql_utc($result, $tz) ;
                    }
                    else {
                        if ($_attributes['element'] == 'measure_timestamp') {
                            $result = $this->get_time_from_mysql_utc($result, $tz) ;
                        }
                        if ($_attributes['element'] == 'measure_value') {
                            $result = $this->get_time_from_utc($result, $tz) ;
                        }
                    }
                }
                catch(\Exception $ex) {
                    $result = $err ;
                }
                break;
            case 'local-diff':
                try {
                    if ((strpos($_attributes['measure_type'], 'last_') === 0) || (strpos($_attributes['measure_type'], 'first_') === 0)) {
                        $result = $this->get_time_diff_from_mysql_utc($result) ;
                    }
                    else {
                        if ($_attributes['element'] == 'measure_timestamp') {
                            $result = $this->get_time_diff_from_mysql_utc($result) ;
                        }
                        if ($_attributes['element'] == 'measure_value') {
                            $result = $this->get_time_diff_from_utc($result) ;
                        }
                    }
                }
                catch(\Exception $ex) {
                    $result = $err ;
                }
                break;
            case 'plain-text':
                switch ($_attributes['measure_type']) {
                    case 'windangle':
                    case 'gustangle':
                    case 'windangle_max':
                    case 'windangle_day_max':
                    case 'windangle_hour_max':
                        $result = $this->get_angle_full_text($result);
                        break;
                    case 'health_idx':
                        $result = $this->get_health_index_text($result);
                        break;
                    case 'cbi':
                        $result = $this->get_cbi_text($result);
                        break;
                    case 'day_length':
                    case 'day_length_c':
                    case 'day_length_n':
                    case 'day_length_a':
                    case 'dawn_length_c':
                    case 'dawn_length_n':
                    case 'dawn_length_a':
                    case 'dusk_length_c':
                    case 'dusk_length_n':
                    case 'dusk_length_a':
                        $result = $this->get_age_hours_from_seconds($result);
                        break;
                    default:
                        $result = $this->output_value($result, $_attributes['measure_type'], false, true, $module_type);
                }
                break;
            case 'short-text':
                switch ($_attributes['measure_type']) {
                    case 'windangle':
                    case 'gustangle':
                    case 'windangle_max':
                    case 'windangle_day_max':
                    case 'windangle_hour_max':
                        $result = $this->get_angle_text($result);
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'computed':
            case 'computed-unit':
                $test = '';
                switch ($_attributes['measure_type']) {
                    case 'dew_point':
                        if (!$this->is_valid_dew_point($_result['result']['temperature_ref'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'frost_point':
                        if (!$this->is_valid_frost_point($_result['result']['temperature_ref'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'heat_index':
                        if (!$this->is_valid_heat_index($_result['result']['temperature_ref'], $_result['result']['humidity_ref'], $_result['result']['dew_point'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'humidex':
                        if (!$this->is_valid_humidex($_result['result']['temperature_ref'], $_result['result']['humidity_ref'], $_result['result']['dew_point'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'wind_chill':
                        if (!$this->is_valid_wind_chill($_result['result']['temperature_ref'], $result)) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                }
                if ($test == '') {
                    $unit = ($_attributes['format']=='computed-unit');
                    $result = $this->output_value( $result, $_attributes['measure_type'], $unit, false, $module_type);
                }
                else {
                    $result = $test;
                }
                if ($result == '') {
                    $result = $err ;
                }
                break;
            case 'computed-wgs84':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 1);
                break;
            case 'computed-wgs84-unit':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 2);
                break;
            case 'computed-dms':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 3);
                break;
            case 'computed-dms-short':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 6);
                break;
            case 'computed-dms-cardinal-start':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 4);
                break;
            case 'computed-dms-cardinal-end':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 5);
                break;
            default:
                $result = esc_html($result);
        }
        Cache::set_frontend($fingerprint, $result);
        return $result;
    }

    /**
     * Get value for icon shortcodes.
     *
     * @param array $attributes The value queryed by the shortcode.
     * @return string The result of the shortcode.
     * @since 3.4.0
     */
    public function icon_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('type'=>'simple', 'measurement'=>'', 'color'=>''), $attributes);
        $result = '';
        switch ($_attributes['type']) {
            case 'simple':
                $style = '';
                $result = $this->output_iconic_value(0, $_attributes['measurement'], null, false, $style);
                break;
        }
        return $result;
    }

    /**
     * Indicates if a measurement can have a negative value.
     *
     * @param string $type The type of the value.
     * @return boolean Return whether this type of measurement can be negative (in meteorology).
     * @since 3.5.0
     */

    protected function can_be_negative($type) {
        $result = false;
        switch (strtolower($type)) {
            case 'temperature':
            case 'tempint':
            case 'tempext':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'heat_index':
            case 'humidex':
            case 'wind_chill':
            case 'soil_temperature':
                $result = true;
        }
        return $result;
    }

    /**
     * Output a value with user's unit.
     *
     * @param   mixed       $value          The value to output.
     * @param   string      $type           The type of the value.
     * @param   boolean     $unit           Optional. Display unit.
     * @param   boolean     $textual        Optional. Display textual value.
     * @param   string      $module_type    Optional. The type of the module.
     * @param   string      $tz             Optional. The timezone.
     * @return  string      The value outputed with the right unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function output_value($value, $type, $unit=false , $textual=false, $module_type='NAMain', $tz='') {
        $result = $value;
        $not = __('N/A', 'live-weather-station');
        if ($value == $not) {
            return $not;
        }
        switch (strtolower($type)) {
            case 'battery':
                $result = $this->get_battery_percentage($value, $module_type);
                $result .= ($unit ? $this->unit_espace.$this->get_battery_unit() : '');
                if ($textual) {
                    $result = $this->get_battery_level_text($value, $module_type);
                }
                break;
            case 'signal':
                $result = $this->get_signal_percentage($value, $module_type);
                $result .= ($unit ? $this->unit_espace.$this->get_signal_unit() : '');
                if ($textual) {
                    $result = $this->get_signal_level_text($value, $module_type);
                }
                break;
            case 'health_idx':
                $result = $this->get_health_index($value);
                $result .= ($unit ? $this->unit_espace.$this->get_health_index_unit() : '');
                if ($textual) {
                    $result = $this->get_health_index_text($value);
                }
                break;
            case 'cbi':
                $result = $this->get_cbi($value);
                $result .= ($unit ? $this->unit_espace.$this->get_cbi_unit() : '');
                if ($textual) {
                    $result = $this->get_cbi_text($value);
                }
                break;
            case 'co2':
                $ref = get_option('live_weather_station_unit_gas');
                $result = $this->get_co2($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_co2_unit($ref) : '');
                break;
            case 'co':
                $ref = get_option('live_weather_station_unit_gas');
                $result = $this->get_co($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_co_unit($ref) : '');
                break;
            case 'o3':
                $result = $this->get_o3($value);
                $result .= ($unit ? $this->unit_espace.$this->get_o3_unit() : '');
                break;
            case 'humidity':
            case 'humint':
            case 'humext':
            case 'humidity_ref':
            case 'humidity_min':
            case 'humidity_max':
                $result = $this->get_humidity($value);
                $result .= ($unit ? $this->unit_espace.$this->get_humidity_unit() : '');
                break;
            case 'cloudiness':
                $result = $this->get_cloudiness($value);
                $result .= ($unit ? $this->unit_espace.$this->get_cloudiness_unit() : '');
                break;
            case 'noise':
                $result = $this->get_noise($value);
                $result .= ($unit ? $this->unit_espace.$this->get_noise_unit() : '');
                break;
            case 'rain':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                $result = $this->get_rain($value, $ref);
                if (strtolower($module_type)=='namodule3') {
                    $ref = $ref + 1;
                }
                $result .= ($unit ? $this->unit_espace.$this->get_rain_unit($ref) : '');
                break;
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
            case 'rain_month_aggregated':
            case 'rain_season_aggregated':
            case 'rain_year_aggregated':
            case 'rain_yesterday_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                $result = $this->get_rain($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_rain_unit($ref) : '');
                break;
            case 'snow':
                $ref = get_option('live_weather_station_unit_rain_snow') ;
                $result = $this->get_snow($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_snow_unit($ref) : '');
                break;
            case 'angle':
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                $result = $this->get_wind_angle($value);
                $result .= ($unit ? $this->unit_espace.$this->get_wind_angle_unit() : '');
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
            case 'wind_ref':
                $ref = get_option('live_weather_station_unit_wind_strength');
                $result = $this->get_wind_speed($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_wind_speed_unit($ref) : '');
                break;
            case 'pressure':
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_ref':
                $ref = get_option('live_weather_station_unit_pressure') ;
                $result = $this->get_pressure($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_pressure_unit($ref) : '');
                break;
            case 'temperature':
            case 'tempint':
            case 'tempext':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
                $ref = get_option('live_weather_station_unit_temperature') ;
                $result = $this->get_temperature($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_temperature_unit($ref) : '');
                break;
            case 'heat_index':
            case 'humidex':
                $ref = get_option('live_weather_station_unit_temperature') ;
                $result = round($this->get_temperature($value, $ref));
                break;
            case 'wind_chill':
                $ref = get_option('live_weather_station_unit_temperature') ;
                $result = $this->get_temperature($value, $ref);
                break;
            case 'loc_altitude':
                $ref = get_option('live_weather_station_unit_altitude');
                $result = $this->get_altitude($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_altitude_unit($ref) : '');
                break;
            case 'cloud_ceiling':
                $ref = get_option('live_weather_station_unit_altitude');
                $result = $this->get_cloud_ceiling($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_altitude_unit($ref) : '');
                break;
            case 'temperature_trend':
            case 'pressure_trend':
                if ($textual) {
                    $result = $this->get_trend_text($value);
                }
                break;
            case 'sunrise':
            case 'sunrise_c':
            case 'sunrise_n':
            case 'sunrise_a':
            case 'sunset':
            case 'sunset_c':
            case 'sunset_n':
            case 'sunset_a':
                if ($value == -1) {
                    $result = $not;
                }
                else {
                    if ($unit) {
                        $result = $this->get_rise_set_short_from_utc($value, $tz);
                    }
                    if ($textual) {
                        $result = $this->get_rise_set_long_from_utc($value, $tz);
                    }
                }
                break;
            case 'dawn_length_c':
            case 'dawn_length_n':
            case 'dawn_length_a':
            case 'dusk_length_c':
            case 'dusk_length_n':
            case 'dusk_length_a':
                if ($value == -1) {
                    $result = $not;
                }
                else {
                    if ($unit) {
                        $result = $this->get_dusk_dawn($value);
                        $result .= ($unit ? $this->unit_espace . $this->get_dusk_dawn_unit() : '');
                    }
                    if ($textual) {
                        $result = $this->get_age_hours_from_seconds($value);
                    }
                }
                break;
            case 'day_length':
            case 'day_length_c':
            case 'day_length_n':
            case 'day_length_a':
                if ($value == -1) {
                    $result = $not;
                }
                else {
                    if ($unit) {
                        $result = $this->get_day_length($value);
                        $result .= ($unit ? $this->unit_espace . $this->get_day_length_unit() : '');
                    }
                    if ($textual) {
                        $result = $this->get_age_hours_from_seconds($value);
                    }
                }
                break;
            case 'moonrise':
            case 'moonset':
                if ($unit) {
                    $result = $this->get_rise_set_short_from_utc($value, $tz, true);
                }
                if ($textual) {
                    $result = $this->get_rise_set_long_from_utc($value, $tz);
                }
                break;
            case 'moon_illumination':
                $result = $this->get_moon_illumination($value);
                $result .= ($unit ? $this->unit_espace.$this->get_moon_illumination_unit() : '');
                break;
            case 'moon_diameter':
            case 'sun_diameter':
                $result = $this->get_degree_diameter($value);
                $result .= ($unit ? $this->unit_espace.$this->get_degree_diameter_unit() : '');
                break;
            case 'moon_distance':
            case 'sun_distance':
                $ref = get_option('live_weather_station_unit_distance');
                $result = $this->get_distance_from_kilometers($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_distance_unit($ref) : '');
                break;
            case 'moon_phase':
                if ($unit || $textual) {
                    $result = $this->get_moon_phase_text($value);
                }
                break;
            case 'moon_age':
                if ($unit || $textual) {
                    $result = $this->get_age_from_days($value);
                }
                break;
            case 'o3_distance':
            case 'co_distance':
                $ref = get_option('live_weather_station_unit_distance');
                $result = $this->get_distance_from_meters($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_distance_unit($ref) : '');
                break;
            case 'loc_timezone':
            case 'timezone':
                if ($unit || $textual) {
                    $result = $this->output_timezone($value);
                }
                break;
            case 'last_seen':
            case 'last_refresh':
            case 'first_setup':
            case 'last_setup':
            case 'last_upgrade':
                $result = self::get_date_from_mysql_utc($value, $tz) . ', ' . self::get_time_from_mysql_utc($value, $tz);
                break;
            case 'oldest_data':
                $result = date_i18n(get_option('date_format'), strtotime(get_date_from_gmt($value)));
                break;
            // PSYCHROMETRY
            case 'wet_bulb':
                $ref = get_option('live_weather_station_unit_temperature') ;
                $result = $this->get_temperature($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_temperature_unit($ref) : '');
                break;
            case 'air_density':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                $result = $this->get_density($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_density_unit($ref) : '');
                break;
            case 'wood_emc':
            case 'emc':
                $result = $this->get_emc($value);
                $result .= ($unit ? $this->unit_espace.$this->get_emc_unit() : '');
                break;
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                $result = $this->get_temperature($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_temperature_unit($ref) : '');
                break;
            case 'specific_enthalpy':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                $result = $this->get_enthalpy($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_enthalpy_unit($ref) : '');
                break;
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
            case 'vapor_pressure':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                $result = $this->get_precise_pressure($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_precise_pressure_unit($ref) : '');
                break;
            case 'absolute_humidity':
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                $result = $this->get_absolute_humidity($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_absolute_humidity_unit($ref) : '');
                break;

            // SOLAR
            case 'irradiance':
                $result = $this->get_irradiance($value);
                $result .= ($unit ? $this->unit_espace.$this->get_irradiance_unit() : '');
                break;
            case 'uv_index':
                $result = $value;
                break;
            case 'illuminance':
                $result = $this->get_illuminance($value);
                $result .= ($unit ? $this->unit_espace.$this->get_illuminance_unit() : '');
                break;
            // SOIL
            case 'soil_temperature':
                $ref = get_option('live_weather_station_unit_temperature') ;
                $result = $this->get_temperature($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_temperature_unit($ref) : '');
                break;
            case 'leaf_wetness':
            case 'moisture_content':
                $result = $this->get_humidity($value);
                $result .= ($unit ? $this->unit_espace.$this->get_humidity_unit() : '');
                break;
            case 'moisture_tension':
                $ref = get_option('live_weather_station_unit_pressure') ;
                $result = $this->get_pressure($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_pressure_unit($ref) : '');
                break;
            case 'evapotranspiration':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                $result = $this->get_rain($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_rain_unit($ref) : '');
                break;
            // THUNDERSTORM
            case 'strike_count':
            case 'strike_instant':
                $result = $value;
                break;
            case 'strike_distance':
                $ref = get_option('live_weather_station_unit_distance');
                $result = $this->get_distance_from_meters($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_distance_unit($ref) : '');
                break;
            case 'strike_bearing':
                $result = $this->get_wind_angle($value);
                $result .= ($unit ? $this->unit_espace.$this->get_wind_angle_unit() : '');
                break;
            case 'visibility':
                $ref = get_option('live_weather_station_unit_distance');
                $result = $this->get_visibility($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_altitude_unit($ref) : '');
                break;
            }
        return $result;
    }

    /**
     * Output a measurement icon.
     *
     * @param mixed $value The value to output.
     * @param string $type The type of the value.
     * @param string $module_type Optional. The type of the module.
     * @param boolean $show_value Optional. The value must represent the true value if possible.
     * @param string $style Optional. The style of the icon.
     * @param string $extra Optional. Class of the icon.
     * @return string The HTML tag for icon.
     * @since 3.0.0
     */
    protected function output_iconic_value($value, $type, $module_type='NAMain', $show_value=false, $style='', $extra='') {
        switch (strtolower($type)) {
            case 'weather':
                $result = '<i %1$s class="wi wi-fw %2$s wi-day-cloudy" aria-hidden="true"></i>';
                break;
            case 'battery':
                $level = $this->get_battery_level($value, $module_type);
                switch ($level) {
                    case 4:
                        if ($show_value) {
                            $result ='<i %1$s class="fa fa-fw %2$s fa-battery-empty" aria-hidden="true"></i>';
                        }
                        else  {
                            $result = '<i %1$s class="fa fa-fw %2$s fa-battery-full" aria-hidden="true"></i>';
                        }
                        break;
                    case 3:
                        if ($show_value) {
                            $result ='<i %1$s class="fa fa-fw %2$s fa-battery-quarter" aria-hidden="true"></i>';
                        }
                        else  {
                            $result = '<i %1$s class="fa fa-fw %2$s fa-battery-full" aria-hidden="true"></i>';
                        }
                        break;
                    case 2:
                        if ($show_value) {
                            $result ='<i %1$s class="fa fa-fw %2$s fa-battery-half" aria-hidden="true"></i>';
                        }
                        else  {
                            $result = '<i %1$s class="fa fa-fw %2$s fa-battery-full" aria-hidden="true"></i>';
                        }
                        break;
                    case 1:
                        if ($show_value) {
                            $result ='<i %1$s class="fa fa-fw %2$s fa-battery-three-quarters" aria-hidden="true"></i>';
                        }
                        else  {
                            $result = '<i %1$s class="fa fa-fw %2$s fa-battery-full" aria-hidden="true"></i>';
                        }
                        break;
                    case 0:
                        $result = '<i %1$s class="fa fa-fw %2$s fa-battery-full" aria-hidden="true"></i>';
                        break;
                    default:
                        $result = '<i %1$s class="fa fa-fw %2$s fa-plug" aria-hidden="true"></i>';
                }
                break;
            case 'signal':
                if (strtolower($module_type) == 'namain') {
                    $result ='<i %1$s class="fa fa-fw %2$s fa-wifi" aria-hidden="true"></i>';
                }
                else  {
                    $result = '<i %1$s class="fa fa-fw %2$s fa-signal" aria-hidden="true"></i>';
                }
                break;
            case 'health_idx':
                $result = '<i %1$s class="fa fa-fw %2$s fa-heartbeat" aria-hidden="true"></i>';
                break;
            case 'cbi':
                $result = '<i %1$s class="wi wi-fw %2$s wi-fire" aria-hidden="true"></i>';
                break;
            case 'co2':
            case 'co':
            case 'so2':
            case 'no2':
                $result = '<i %1$s class="wi wi-fw %2$s wi-smoke" aria-hidden="true"></i>';
                break;
            case 'o3':
                $result = '<i %1$s class="fa fa-fw %2$s fa-circle-o-notch" aria-hidden="true"></i>';
                break;
            case 'humidity':
            case 'humint':
            case 'humext':
            case 'humidity_ref':
                $result = '<i %1$s class="wi wi-fw %2$s wi-humidity" aria-hidden="true"></i>';
                break;
            case 'noise':
                $result = '<i %1$s class="fa fa-fw %2$s fa-volume-down" aria-hidden="true"></i>';
                break;
            case 'pressure':
            case 'pressure_ref':
                $result = '<i %1$s class="wi wi-fw %2$s wi-barometer" aria-hidden="true"></i>';
                break;
            case 'pressure_trend':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-barometer"></i><i %1$s class="fa fa-arrows-v"></i></span>';
                break;
            case 'pressure_max':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-barometer"></i><i %1$s class="fa fa-long-arrow-up"></i></span>';
                break;
            case 'pressure_min':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-barometer"></i><i %1$s class="fa fa-long-arrow-down"></i></span>';
                break;
            case 'temperature':
            case 'tempint':
            case 'tempext':
            case 'temperature_ref':
                $result = '<i %1$s class="wi wi-fw %2$s wi-thermometer" aria-hidden="true"></i>';
                break;
            case 'temperature_trend':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-thermometer"></i><i %1$s class="fa fa-arrows-v"></i></span>';
                break;
            case 'temperature_max':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-thermometer"></i><i %1$s class="fa fa-long-arrow-up"></i></span>';
                break;
            case 'temperature_min':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-thermometer"></i><i %1$s class="fa fa-long-arrow-down"></i></span>';
                break;
            case 'humidity_trend':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-humidity"></i><i %1$s class="fa fa-arrows-v"></i></span>';
                break;
            case 'humidity_max':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-humidity"></i><i %1$s class="fa fa-long-arrow-up"></i></span>';
                break;
            case 'humidity_min':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-humidity"></i><i %1$s class="fa fa-long-arrow-down"></i></span>';
                break;
            case 'cloudiness':
                $result = '<i %1$s class="wi wi-fw %2$s wi-cloud" aria-hidden="true"></i>';
                break;
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
            case 'rain_month_aggregated':
            case 'rain_season_aggregated':
            case 'rain_year_aggregated':
            case 'rain_yesterday_aggregated':
                $result = '<i %1$s class="wi wi-fw %2$s wi-umbrella" aria-hidden="true"></i>';
                break;
            case 'snow':
                $result = '<i %1$s class="wi wi-fw %2$s wi-snowflake-cold" aria-hidden="true"></i>';
                break;
            case 'dew_point':
                $result = '<i %1$s class="wi wi-fw %2$s wi-raindrops" aria-hidden="true"></i>';
                break;
            case 'frost_point':
                $result = '<i %1$s class="wi wi-fw %2$s wi-stars" aria-hidden="true"></i>';
                break;
            case 'heat_index':
            case 'humidex':
            case 'wind_chill':
            /*
             * @fixme find better icons
             */
            $result = '<i %1$s class="wi wi-fw %2$s wi-thermometer-internal" aria-hidden="true"></i>';
            break;
            case 'cloud_ceiling':
                $result = '<i %1$s class="wi wi-fw %2$s wi-cloud-up" aria-hidden="true"></i>';
                break;
            case 'o3_distance':
            case 'co_distance':
                $result = '<i %1$s class="fa fa-fw %2$s fa-crosshairs" aria-hidden="true"></i>';
                break;
            case 'loc_timezone':
            case 'timezone':
                $result = '<i %1$s class="fa fa-fw %2$s fa-clock-o" aria-hidden="true"></i>';
                break;
            case 'city':
            case 'country':
                $result = '<i %1$s class="fa fa-fw %2$s fa-globe" aria-hidden="true"></i>';
                break;
            case 'station_name':
                $result = '<i %1$s class="fa fa-fw %2$s fa-tags" aria-hidden="true"></i>';
                break;
            case 'module':
                $result = '<i %1$s class="fa fa-fw %2$s fa-database" aria-hidden="true"></i>';
                break;
            case 'location':
                $result = '<i %1$s class="fa fa-fw %2$s fa-map-marker" aria-hidden="true"></i>';
                break;
            case 'altitude':
            case 'loc_altitude':
                $result = '<i %1$s class="fa fa-fw %2$s fa-rotate-315 fa-location-arrow" aria-hidden="true"></i>';
                break;
            case 'last_seen':
                $result = '<i %1$s class="fa fa-fw %2$s fa-eye" aria-hidden="true"></i>';
                break;
            case 'refresh':
                $result = '<i %1$s class="fa fa-fw %2$s fa-refresh" aria-hidden="true"></i>';
                break;
            case 'last_upgrade':
            case 'firmware':
                $result = '<i %1$s class="fa fa-fw %2$s fa-cog" aria-hidden="true"></i>';
                break;
            case 'last_setup':
            case 'first_setup':
                $result = '<i %1$s class="fa fa-fw %2$s fa-wrench" aria-hidden="true"></i>';
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
            case 'wind_ref':
                $level = $this->get_wind_speed($value, 3);
                if ($show_value) {
                    $result ='<i %1$s class="wi wi-fw %2$s wi-wind-beaufort-'. $level . '" aria-hidden="true"></i>';
                }
                else {
                    $result ='<i %1$s class="wi wi-fw %2$s wi-strong-wind" aria-hidden="true"></i>';
                }
                break;
            case 'warn_windstrength':
            case 'warn_guststrength':
            case 'warn_windstrength_max':
            case 'warn_windstrength_day_max':
            case 'warn_windstrength_hour_max':
            case 'warn_wind_ref':
                $level = $this->get_wind_state($value);
                if ($show_value) {
                    switch ($level) {
                        case 1:
                            $result ='<i %1$s class="wi wi-fw %2$s wi-small-craft-advisory" aria-hidden="true"></i>';
                            break;
                        case 2:
                            $result ='<i %1$s class="wi wi-fw %2$s wi-gale-warning" aria-hidden="true"></i>';
                            break;
                        case 3:
                            $result ='<i %1$s class="wi wi-fw %2$s wi-storm-warning" aria-hidden="true"></i>';
                            break;
                        case 4:
                            $result ='<i %1$s class="wi wi-fw %2$s wi-hurricane-warning" aria-hidden="true"></i>';
                            break;
                        default:
                            $result ='<i %1$s class="wi wi-fw %2$s wi-strong-wind" aria-hidden="true"></i>';
                    }
                }
                else {
                    $result ='<i %1$s class="wi wi-fw %2$s wi-strong-wind" aria-hidden="true"></i>';
                }
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                if ($show_value) {
                    $s = (get_option('live_weather_station_wind_semantics') == 0 ? 'towards' : 'from') . '-' . $value . '-deg';
                    $result = '<i %1$s class="wi wi-fw %2$s wi-wind ' . $s . '" aria-hidden="true"></i>';
                }
                else {
                    $result = '<i %1$s class="wi wi-fw %2$s wi-wind towards-0-deg" aria-hidden="true"></i>';
                }
                break;
            case 'sunrise':
            case 'sunset':
            case 'moonrise':
            case 'moonset':
                $result = '<i %1$s class="wi wi-fw %2$s wi-' . strtolower($type) . '" aria-hidden="true"></i>';
                break;
            case 'moon_phase':
                if ($show_value) {
                    $result = '<i %1$s class="wi wi-fw %2$s wi-moon-' . $this->get_moon_phase_icon($value) . '" aria-hidden="true"></i>';
                }
                else {
                    $result = '<i %1$s class="wi wi-fw %2$s wi-moon-waxing-crescent-4" aria-hidden="true"></i>';
                }
                break;
            case 'moon_age':
                if ($show_value) {
                    $result = '<i %1$s class="wi wi-fw %2$s wi-moon-' . $this->get_lunation_icon($value) . '" aria-hidden="true"></i>';
                }
                else {
                    $result = '<i %1$s class="wi wi-fw %2$s wi-moon-waxing-crescent-4" aria-hidden="true"></i>';
                }
                break;
            case 'moon_illumination':
            case 'moon_diameter':
            case 'moon_distance':
                $result = '<i %1$s class="wi wi-fw %2$s wi-moon-waxing-crescent-4" aria-hidden="true"></i>';
                break;
            case 'sun_diameter':
            case 'sun_distance':
                $result = '<i %1$s class="wi wi-fw %2$s wi-day-sunny" aria-hidden="true"></i>';
                break;
            case 'sunrise_c':
            case 'sunrise_n':
            case 'sunrise_a':
                $result = '<i %1$s class="wi wi-fw %2$s wi-sunrise" aria-hidden="true"></i>';
                break;
            case 'sunset_c':
            case 'sunset_n':
            case 'sunset_a':
                $result = '<i %1$s class="wi wi-fw %2$s wi-sunset" aria-hidden="true"></i>';
                break;
            case 'day_length':
            case 'day_length_c':
            case 'day_length_n':
            case 'day_length_a':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-day-sunny"></i><i %1$s class="fa fa-arrows-v"></i></span>';
                break;
            case 'dawn_length_a':
            case 'dawn_length_n':
            case 'dawn_length_c':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-sunrise"></i><i %1$s class="fa fa-arrows-v"></i></span>';
                break;
            case 'dusk_length_a':
            case 'dusk_length_n':
            case 'dusk_length_c':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-sunset"></i><i %1$s class="fa fa-arrows-v"></i></span>';
                break;
            // PSYCHROMETRIC
            case 'air_density':
                $result = '<i %1$s class="fa fa-fw %2$s fa-adjust" aria-hidden="true"></i>';
                break;
            case 'wet_bulb':
                $result = '<i %1$s class="wi wi-fw %2$s wi-thermometer" aria-hidden="true"></i>';
                break;
            case 'wood_emc':
            case 'emc':
                $result = '<i %1$s class="fa fa-fw %2$s fa-tree" aria-hidden="true"></i>';
                break;
            case 'specific_enthalpy':
                $result = '<i %1$s class="wi wi-fw %2$s wi-refresh-alt" aria-hidden="true"></i>';
                break;
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
                $result = '<i %1$s class="wi wi-fw %2$s wi-thermometer-exterior" aria-hidden="true"></i>';
                break;
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
            case 'vapor_pressure':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-barometer"></i><i %1$s class="fa fa-ellipsis-v"></i></span>';
                break;
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
            case 'absolute_humidity':
                $result = '<i %1$s class="wi wi-fw %2$s wi-raindrop" aria-hidden="true"></i>';
                break;
            // SOLAR
            case 'irradiance':
                $result = '<i %1$s class="fa fa-fw %2$s fa-arrow-down" aria-hidden="true"></i>';
                break;
            case 'uv_index':
                $result = '<i %1$s class="wi wi-fw %2$s wi-horizon-alt" aria-hidden="true"></i>';
                break;
            case 'illuminance':
                $result = '<i %1$s class="fa fa-fw %2$s fa-long-arrow-down fa-rotate-30" aria-hidden="true"></i>';
                break;
            // SOIL
            case 'soil_temperature':
                $result = '<i %1$s class="wi wi-fw %2$s wi-thermometer" aria-hidden="true"></i>';
                break;
            case 'leaf_wetness':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="fa fa-envira"></i><i %1$s class="wi wi-degrees"></i></span>';
                break;
            case 'moisture_content':
                $result = '<i %1$s class="wi wi-fw %2$s wi-humidity" aria-hidden="true"></i>';
                break;
            case 'moisture_tension':
                $result = '<span class="fa-stack fa-fw %2$s"><i %1$s class="wi wi-barometer"></i><i %1$s class="wi wi-degrees"></i></span>';
                break;
            case 'evapotranspiration':
                $result = '<i %1$s class="wi wi-fw %2$s wi-flood" aria-hidden="true"></i>';
                break;
            // THUNDERSTORM
            case 'strike_count':
            case 'strike_instant':
                $result = '<i %1$s class="wi wi-fw %2$s wi-lightning" aria-hidden="true"></i>';
                break;
            case 'strike_distance':
                $result = '<i %1$s class="fa fa-fw %2$s fa-crosshairs" aria-hidden="true"></i>';
                break;
            case 'visibility':
                $result = '<i %1$s class="fa fa-fw %2$s fa-eye-slash" aria-hidden="true"></i>';
                break;
            case 'strike_bearing':
                if ($show_value) {
                    $s = 'towards-' . $value . '-deg';
                    $result = '<i %1$s class="wi wi-fw %2$s wi-wind ' . $s . '" aria-hidden="true"></i>';
                }
                else {
                    $result = '<i %1$s class="wi wi-fw %2$s wi-wind towards-0-deg" aria-hidden="true"></i>';
                }
                break;
            case 'historical':
                $result = '<i %1$s class="fa fa-fw %2$s fa-history" aria-hidden="true"></i>';
                break;
            default:
                $result = '<i %s class="fa fa-fw %s fa-sun-o" aria-hidden="true"></i>';
    }
        return sprintf($result, $style, $extra);
}

    /**
     * Output a latitude or longitude with user's unit.
     *
     * @param   mixed       $value          The value to output.
     * @param   string      $type           The type of the value.
     * @param   integer     $mode           Optional. The mode in wich to output:
     *                                          1: Geodetic system WGS 84
     *                                          2: Geodetic system WGS 84 with unit
     *                                          3: DMS
     *                                          4: DMS starting with cardinal
     *                                          5: DMS ending with cardinal
     * @param   boolean     $html           Optional. Replace space by &nbsp;
     * @return  string      The value outputed with the right unit.
     * @since    1.1.0
     * @access   protected
     */
    protected function output_coordinate($value, $type, $mode=0, $html=false) {
        switch ($mode) {
            case 1:
                $result = $value;
                break;
            case 2:
                $result = $value.'°';
                break;
            case 3:
            case 4:
            case 5:
            case 6:
                $abs = abs($value);
                $floor = floor($abs);
                $deg = (integer)$floor;
                $min = (integer)floor(($abs-$deg)*60);
                $min_alt = round(($abs-$deg)*60, 1);
                $sec = round(($abs-$deg-($min/60))*3600,1);
                $result = $deg.'° '.$min.'\' '.$sec.'"';
                $result_alt = $deg.'° '.$min_alt.'\' ';
                $fix = ($value >= 0 ? '' : '-') ;
                if ($type=='loc_longitude' && $mode != 3) {
                    if ($fix == '') {
                        $fix = $this->get_angle_text(90) ;
                    }
                    else {
                        $fix = $this->get_angle_text(270) ;
                    }
                }
                if ($type=='loc_latitude' && $mode != 3) {
                    if ($fix == '') {
                        $fix = $this->get_angle_text(0) ;
                    }
                    else {
                        $fix = $this->get_angle_text(180) ;
                    }
                }
                if ($mode == 3) {
                    $result = $fix.$result;
                }
                if ($mode == 4) {
                    $result = $fix.' '.$result;
                }
                if ($mode == 5) {
                    $result = $result.' '.$fix;
                }
                if ($mode == 6) {
                    $result = $result_alt.' '.$fix;
                }
                break;
            default:
                $result = $value;
        }
        if ($html) {
            $result = str_replace(' ', '&nbsp;', $result);
        }
        return $result;
    }

    /**
     * Outputs the right unit.
     *
     * @param   string  $type   The type of the value.
     * @param   string $module_type  Optional. Specify the type of the module.
     * @param   integer $force_ref  Optional. Forces the ref unit.
     * @return  array   The value of the right unit and its complement.
     * @since    1.0.0
     * @access   protected
     */
    protected function output_unit($type, $module_type='NAMain', $force_ref=0) {
        $result = array('unit'=>'', 'comp'=>'', 'full'=>'', 'long'=>'', 'dimension'=>'unknown', 'ref'=>-1);
        $ref = -1;
        switch ($type) {
            case 'loc_altitude':
            case 'cloud_ceiling':
                $ref = get_option('live_weather_station_unit_altitude');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_altitude_unit($ref) ;
                $result['long'] = $this->get_altitude_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'battery':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_battery_unit($ref) ;
                $result['long'] = $this->get_battery_unit_full($ref) ;
                $result['dimension'] = 'percentage';
                break;
            case 'signal':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_signal_unit($ref) ;
                $result['long'] = $this->get_signal_unit_full($ref) ;
                $result['dimension'] = 'percentage';
                break;
            case 'co2':
                $ref = get_option('live_weather_station_unit_gas');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_co2_unit($ref) ;
                $result['long'] = $this->get_co2_unit_full($ref) ;
                $result['dimension'] = 'concentration-m';
                break;
            case 'co':
                $ref = get_option('live_weather_station_unit_gas');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_co_unit($ref) ;
                $result['long'] = $this->get_co_unit_full($ref) ;
                $result['dimension'] = 'concentration-b';
                break;
            case 'o3':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_o3_unit($ref) ;
                $result['long'] = $this->get_o3_unit_full($ref) ;
                $result['dimension'] = 'area-density';
                break;
            case 'humidity':
            case 'humidity_min':
            case 'humidity_max':
            case 'humint':
            case 'humext':
            case 'humidity_ref':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_humidity_unit($ref) ;
                $result['long'] = $this->get_humidity_unit_full($ref) ;
                $result['comp'] = __('hum', 'live-weather-station') ;
                $result['dimension'] = 'percentage';
                break;
            case 'cloudiness':
            case 'cloud_cover':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_cloudiness_unit($ref) ;
                $result['long'] = $this->get_cloudiness_unit_full($ref) ;
                $result['dimension'] = 'percentage';
                break;
            case 'noise':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_noise_unit($ref) ;
                $result['long'] = $this->get_noise_unit_full($ref) ;
                $result['dimension'] = 'dimensionless';
                break;
            case 'health_idx':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_health_index_unit($ref) ;
                $result['long'] = $this->get_health_index_unit_full($ref) ;
                $result['comp'] = __('hlth', 'live-weather-station') ;
                $result['dimension'] = 'percentage';
                break;
            case 'cbi':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_cbi_unit($ref) ;
                $result['long'] = $this->get_cbi_unit_full($ref) ;
                $result['comp'] = __('CBi', 'live-weather-station') ;
                $result['dimension'] = 'dimensionless';
                break;
            case 'rain':
                $result['dimension'] = 'unknown';
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if (strtolower($module_type)=='namodule3') {
                    $ref = $ref + 1;
                    $result['dimension'] = 'rate';
                }
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('now', 'live-weather-station') ;
                if (strtolower($module_type)=='nacurrent') {
                    $result['comp'] = __('/ 3 hr', 'live-weather-station') ;
                    $result['dimension'] = 'rate';
                }
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                break;
            case 'rain_hour_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('/ 1 hr', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'rain_month_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('month', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'rain_year_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('year', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'rain_day_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('today', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'rain_yesterday_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('yda.', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'rain_season_aggregated':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('season', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['dimension'] = 'length';
                break;
            case 'snow':
                $result['dimension'] = 'length';
                $ref = get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                if (strtolower($module_type)=='nacurrent') {
                    $result['comp'] = __('/ 3 hr', 'live-weather-station') ;
                    $result['dimension'] = 'rate';
                }
                $result['unit'] = $this->get_snow_unit($ref) ;
                $result['long'] = $this->get_snow_unit_full($ref) ;
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                if ($type == 'windangle') {
                    $result['comp'] = __('now', 'live-weather-station') ;
                }
                if ($type == 'gustangle') {
                    $result['comp'] = __('gust', 'live-weather-station') ;
                }
                if ($type == 'windangle_day_max') {
                    $result['comp'] = __('today', 'live-weather-station') ;
                }
                if ($type == 'windangle_hour_max') {
                    $result['comp'] = __('/ 1 hr', 'live-weather-station') ;
                }
                $result['unit'] = $this->get_wind_angle_unit($ref);
                $result['long'] = $this->get_wind_angle_unit_full($ref);
                $result['dimension'] = 'angle';
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
                $ref = get_option('live_weather_station_unit_wind_strength');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                if ($type == 'windstrength') {
                    $result['comp'] = __('now', 'live-weather-station') ;
                }
                if ($type == 'guststrength') {
                    $result['comp'] = __('gust', 'live-weather-station') ;
                }
                if ($type == 'windstrength_day_max') {
                    $result['comp'] = __('today', 'live-weather-station') ;
                }
                if ($type == 'windstrength_hour_max') {
                    $result['comp'] = __('/ 1 hr', 'live-weather-station') ;
                }
                $result['unit'] = $this->get_wind_speed_unit($ref);
                $result['long'] = $this->get_wind_speed_unit_full($ref);
                $result['dimension'] = 'speed';
                break;
            case 'pressure':
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_ref':
                $ref = get_option('live_weather_station_unit_pressure') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_pressure_unit($ref);
                $result['long'] = $this->get_pressure_unit_full($ref);
                $result['dimension'] = 'pressure-h';
                break;
            case 'temperature':
            case 'tempint':
            case 'tempext':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
                $ref = get_option('live_weather_station_unit_temperature') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_temperature_unit($ref);
                $result['long'] = $this->get_temperature_unit_full($ref);
                $result['dimension'] = 'temperature';
                break;
            case 'dawn_length_c':
            case 'dawn_length_n':
            case 'dawn_length_a':
            case 'dusk_length_c':
            case 'dusk_length_n':
            case 'dusk_length_a':
                $result['unit'] = $this->get_dusk_dawn_unit();
                $result['long'] = $this->get_dusk_dawn_unit_full();
                $result['dimension'] = 'duration';
                break;
            case 'day_length':
            case 'day_length_c':
            case 'day_length_n':
            case 'day_length_a':
                $result['unit'] = $this->get_day_length_unit();
                $result['long'] = $this->get_day_length_unit_full();
                $result['dimension'] = 'duration';
                break;
            case 'moon_illumination':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_moon_illumination_unit($ref);
                $result['long'] = $this->get_moon_illumination_unit_full($ref);
                $result['dimension'] = 'percentage';
                break;
            case 'moon_diameter':
            case 'sun_diameter':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_degree_diameter_unit($ref);
                $result['long'] = $this->get_degree_diameter_unit_full($ref);
                $result['dimension'] = 'length';
                break;
            case 'moon_distance':
            case 'sun_distance':
                $ref = get_option('live_weather_station_unit_distance');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_distance_unit($ref);
                $result['long'] = $this->get_distance_unit_full($ref);
            $result['dimension'] = 'length';
                break;
            // PSYCHROMETRY
            case 'wet_bulb':
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_temperature_unit($ref);
                $result['long'] = $this->get_temperature_unit_full($ref);
                $result['dimension'] = 'temperature';
                break;
            case 'air_density':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_density_unit($ref) ;
                $result['long'] = $this->get_density_unit_full($ref) ;
                //$result['comp'] = __('air', 'live-weather-station') ;
                $result['dimension'] = 'density';
                break;
            case 'wood_emc':
            case 'emc':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_emc_unit($ref) ;
                $result['long'] = $this->get_emc_unit_full($ref) ;
                $result['dimension'] = 'percentage';
                break;
            case 'specific_enthalpy':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_enthalpy_unit($ref) ;
                $result['long'] = $this->get_enthalpy_unit_full($ref) ;
                $result['dimension'] = 'specific-energy-k';
                break;
            case 'partial_vapor_pressure':
            case 'vapor_pressure':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_precise_pressure_unit($ref) ;
                $result['long'] = $this->get_precise_pressure_unit_full($ref) ;
                $result['dimension'] = 'pressure';
                break;
            case 'saturation_vapor_pressure':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_precise_pressure_unit($ref) ;
                $result['long'] = $this->get_precise_pressure_unit_full($ref) ;
                $result['comp'] = __('sat.', 'live-weather-station') ;
                $result['dimension'] = 'pressure';
                break;
            case 'partial_absolute_humidity':
            case 'absolute_humidity':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_absolute_humidity_unit($ref) ;
                $result['long'] = $this->get_absolute_humidity_unit_full($ref) ;
                $result['dimension'] = 'humidity';
                break;
            case 'saturation_absolute_humidity':
                $ref = get_option('live_weather_station_unit_psychrometry') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_absolute_humidity_unit($ref) ;
                $result['long'] = $this->get_absolute_humidity_unit_full($ref) ;
                $result['comp'] = __('sat.', 'live-weather-station') ;
                $result['dimension'] = 'humidity';
                break;
            // SOLAR
            case 'irradiance':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_irradiance_unit($ref) ;
                $result['long'] = $this->get_irradiance_unit_full($ref) ;
                $result['dimension'] = 'irradiance';
                break;
            case 'illuminance':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_illuminance_unit($ref) ;
                $result['long'] = $this->get_illuminance_unit_full($ref) ;
                $result['dimension'] = 'illuminance';
                break;
            case 'uv_index':
                $result['comp'] = __('UV', 'live-weather-station') ;
                $result['dimension'] = 'base-11';
                break;
            // SOIL
            case 'soil_temperature':
                $ref = get_option('live_weather_station_unit_temperature') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_temperature_unit($ref) ;
                $result['long'] = $this->get_temperature_unit_full($ref) ;
                $result['dimension'] = 'temperature';
                break;
            case 'leaf_wetness':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_humidity_unit($ref) ;
                $result['long'] = $this->get_humidity_unit_full($ref) ;
                $result['comp'] = __('wet', 'live-weather-station') ;
                $result['dimension'] = 'percentage';
                break;
            case 'moisture_content':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_humidity_unit($ref) ;
                $result['long'] = $this->get_humidity_unit_full($ref) ;
                $result['comp'] = __('moist', 'live-weather-station') ;
                $result['dimension'] = 'percentage';
                break;
            case 'moisture_tension':
                $ref = get_option('live_weather_station_unit_pressure') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_pressure_unit($ref) ;
                $result['long'] = $this->get_pressure_unit_full($ref) ;
                $result['comp'] = __('moist', 'live-weather-station') ;
                $result['dimension'] = 'pressure';
                break;
            case 'evapotranspiration':
                $ref = 2 * get_option('live_weather_station_unit_rain_snow') ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                $result['comp'] = __('evap', 'live-weather-station') ;
                $result['dimension'] = 'length';
                break;
            // THUNDERSTORM
            case 'strike_instant':
                $ref = get_option('live_weather_station_unit_distance');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('now', 'live-weather-station') ;
                $result['dimension'] = 'count';
                break;
            case 'strike_count':
                $ref = get_option('live_weather_station_unit_distance');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('total', 'live-weather-station') ;
                $result['dimension'] = 'count';
                break;
            case 'strike_distance':
                $ref = get_option('live_weather_station_unit_distance');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_distance_unit($ref);
                $result['long'] = $this->get_distance_unit_full($ref);
                $result['comp'] = __('last', 'live-weather-station') ;
                $result['dimension'] = 'length';
                break;
            case 'strike_bearing':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_wind_angle_unit($ref);
                $result['long'] = $this->get_wind_angle_unit_full($ref);
                $result['comp'] = __('last', 'live-weather-station') ;
                $result['dimension'] = 'angle';
                break;
            case 'visibility':
                $ref = get_option('live_weather_station_unit_distance');
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_altitude_unit($ref);
                $result['long'] = $this->get_altitude_unit_full($ref);
                $result['dimension'] = 'length';
                break;
        }
        if ($result['comp'] != __('now', 'live-weather-station')) {
            $result['full'] = $result['unit'].' '.$result['comp'];   
        }
        else {
            $result['full'] = $result['unit'].' ('.$result['comp'].')';
        }
        $result['ref'] = $ref;
        return $result;
    }

    /**
     * How decimals to display for this type of value.
     *
     * @param string $type The type of the value.
     * @param integer $value  Optional. The decimal value.
     * @return integer The number of decimals to show.
     * @since 2.1.0
     */
    protected function decimal_for_output($type, $value=0) {
        $result = 0;
        switch ($type) {
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
                $result = 1 + get_option('live_weather_station_unit_rain_snow') ;
                break;
            case 'snow':
                $result = get_option('live_weather_station_unit_rain_snow') ;
                break;
            case 'temperature':
            case 'tempint':
            case 'tempext':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
            case 'humidex':
            case 'heat_index':
            case 'cbi':
            case 'wet_bulb':
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
            case 'wood_emc':
            case 'emc':
                $result = 1 ;
                break;
            case 'pressure':
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_ref':
                if (get_option('live_weather_station_unit_pressure') == 1) {
                    $result = 2 ;
                }
                else {
                    $result = 1 ;
                }
                break;
            /*case 'co':
                $ref = get_option('live_weather_station_unit_co');
                switch ($ref) {
                    case 0 : $result = 6 ; break;
                    case 1 : $result = 4 ; break;
                    case 2 : $result = 5 ; break;
                }
                break;*/
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
                $ref = get_option('live_weather_station_unit_wind_strength');
                if ($ref == 1 || $ref == 2 || $ref == 4 || $ref == 5 || $ref == 6) {
                    if ($value < 5) {
                        $result = 1;
                    }
                }
                break;
            case 'air_density':
                if (get_option('live_weather_station_unit_psychrometry') == 1) {
                    $result = 5 ;
                }
                else {
                    $result = 4 ;
                }
                break;
            case 'specific_enthalpy':
                $result = 2 ;
                break;
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
            case 'vapor_pressure':
                if (get_option('live_weather_station_unit_psychrometry') == 1) {
                    $result = 2 ;
                }
                break;
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
            case 'absolute_humidity':
                if (get_option('live_weather_station_unit_psychrometry') == 1) {
                    $result = 1 ;
                }
                else {
                    $result = 2 ;
                }
                break;
        }
        return $result;
    }

    /**
     * Get a human readable time zone.
     *
     * @param   string  $timezone  Standardized timezone string
     * @return  array  A human readable time zone.
     * @since    2.0.0
     */
    private function output_timezone($timezone) {
        $result = str_replace('/', ' / ', $timezone);
        $result = str_replace('_', ' ', $result);
        $result = str_replace('DU', ' d\'U', $result);
        return $result;
    }

    /**
     * Outputs the abbreviation of a measure type.
     *
     * @param   string  $type   The type of the value.
     * @return  string   The value of the abreviation.
     * @since    1.0.0
     * @access   protected
     */
    protected function output_abbreviation($type) {
        $result = '';
        switch ($type) {
            case 'health_idx':
                $result = __('health', 'live-weather-station') ;
                break;
            case 'cbi':
                $result = __('CBi', 'live-weather-station') ;
                break;
            case 'co2':
                $result = __('CO₂', 'live-weather-station') ;
                break;
            case 'co':
                $result = __('CO', 'live-weather-station') ;
                break;
            case 'o3':
                $result = __('O₃', 'live-weather-station') ;
                break;
            case 'humidity':
            case 'humidity_min':
            case 'humidity_max':
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
            case 'absolute_humidity':
                $result = __('humidity', 'live-weather-station') ;
                break;
            case 'noise':
                $result = __('noise', 'live-weather-station') ;
                break;
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
            case 'rain_yesterday_aggregated':
            case 'rain_month_aggregated':
            case 'rain_season_aggregated':
            case 'rain_year_aggregated':
                $result = __('rain', 'live-weather-station') ;
                break;
            case 'snow':
                $result = __('snow', 'live-weather-station') ;
                break;
            case 'windangle':
            case 'windangle_max':
            case 'windangle_hour_max':
            case 'windangle_day_max':
            case 'gustangle':
            case 'windstrength':
            case 'windstrength_max':
            case 'windstrength_hour_max':
            case 'windstrength_day_max':
            case 'guststrength':
                $result = __('wind', 'live-weather-station') ;
                break;
            case 'pressure':
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_ref':
                $result = __('atm pressure', 'live-weather-station') ;
                break;
            case 'dew_point':
                $result = __('dew point', 'live-weather-station') ;
                break;
            case 'air_density':
                $result = __('air', 'live-weather-station') ;
                break;
            case 'frost_point':
                $result = __('frost point', 'live-weather-station') ;
                break;
            case 'heat_index':
                $result = __('heat-index', 'live-weather-station') ;
                break;
            case 'humidex':
                $result = __('humidex', 'live-weather-station') ;
                break;
            case 'wind_chill':
                $result = __('wind chill', 'live-weather-station') ;
                break;
            case 'cloud_ceiling':
                $result = __('cloud base', 'live-weather-station') ;
                break;
            case 'cloudiness':
                $result = __('cloudiness', 'live-weather-station') ;
                break;
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
            case 'wet_bulb':
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
            case 'soil_temperature':
                $result = __('temperature', 'live-weather-station') ;
                break;
            case 'dawn':
            case 'dawn_c':
            case 'dawn_n':
            case 'dawn_a':
                $result = __('dawn', 'live-weather-station') ;
                break;
            case 'dusk':
            case 'dusk_c':
            case 'dusk_n':
            case 'dusk_a':
                $result = __('dusk', 'live-weather-station') ;
                break;
            case 'wood_emc':
            case 'emc':
                $result = __('EMC', 'live-weather-station') ;
                break;
            case 'specific_enthalpy':
                $result = __('enthalpy', 'live-weather-station') ;
                break;
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
            case 'vapor_pressure':
                $result = __('vapor', 'live-weather-station') ;
                break;
            case 'irradiance':
                $result = __('irradiance', 'live-weather-station') ;
                break;
            case 'illuminance':
                $result = __('illuminance', 'live-weather-station') ;
                break;
            case 'leaf_wetness':
                $result = __('wetness', 'live-weather-station') ;
                break;
            case 'moisture_content':
            case 'moisture_tension':
                $result = __('moisture', 'live-weather-station') ;
                break;
            case 'evapotranspiration':
                $result = __('ET', 'live-weather-station') ;
                break;
            case 'strike_distance':
                $result = __('distance', 'live-weather-station') ;
                break;
            case 'visibility':
                $result = __('visibility', 'live-weather-station') ;
                break;
            case 'strike_bearing':
                $result = __('bearing', 'live-weather-station') ;
                break;
            case 'strike_count':
            case 'strike_instant':
                $result = __('strike', 'live-weather-station') ;
                break;

        }
        return $result;
    }

    /**
     * Get the full country name of an ISO-2 country code.
     *
     * @param   string   $value    The ISO-2 country code.
     * @return  string  The full country name.
     * @since    2.0.0
     */
    protected function get_country_name($value) {
        return \Locale::getDisplayRegion('-'.$value, lws_get_display_locale());
    }

    /**
     * Get an array containing country names associated with their ISO-2 codes.
     *
     * @return  array  An associative array with names and codes.
     * @since    2.0.0
     */
    protected function get_country_names() {

        function compareASCII($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
            return strcmp(strtoupper($at), strtoupper($bt));
        }

        $result = [];
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $continue = array('BU', 'CS', 'DD', 'DY', 'EU', 'HV', 'FX', 'NH', 'QO', 'RH', 'SU', 'TP', 'UK', 'VD', 'YD', 'YU', 'ZR', 'ZZ');
        $locale = lws_get_display_locale();
        for ($i=0; $i<26; $i++) {
            for ($j=0; $j<26; $j++) {
                $s = $letters[$i].$letters[$j];
                if (in_array($s, $continue)) {
                    continue;
                }
                $t = \Locale::getDisplayRegion('-'.$s, $locale);
                if ($s != $t) {
                    $result[$s] = ucfirst($t);
                }
            }
        }
        $save_locale = setlocale(LC_ALL,'');
        setlocale(LC_ALL, $locale);
        uasort($result, 'WeatherStation\Data\compareASCII');
        setlocale(LC_ALL, $save_locale);
        return $result;
    }

    /**
     * Get the battery level in human readable text.
     *
     * @param   integer   $value    The value of the battery.
     * @param   string  $type   The type of the module.
     * @return  string  The battery level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_level_text($value, $type) {
        $level = $this->get_battery_level($value, $type);
        switch ($level) {
            case 4:
                $result = __('Very low', 'live-weather-station') ;
                break;
            case 3:
                $result = __('Low', 'live-weather-station') ;
                break;
            case 2:
                $result = __('Medium', 'live-weather-station') ;
                break;
            case 1:
                $result = __('High', 'live-weather-station') ;
                break;
            case 0:
                $result = __('Full', 'live-weather-station') ;
                break;
            case -1:
                $result = __('AC Power', 'live-weather-station') ;
                break;
            default:
                $result = __('Unknown Power State', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the health index in human readable text.
     *
     * @param integer $value The value of the health index.
     * @return string The health index in human readable text.
     * @since 3.1.0
     */
    protected function get_health_index_text($value) {
        $result = __('Healthy', 'live-weather-station') ;
        if ($value < 80) {
            $result = __('Fine', 'live-weather-station') ;
        }
        if ($value < 60) {
            $result = __('Fair', 'live-weather-station') ;
        }
        if ($value < 40) {
            $result = __('Poor', 'live-weather-station') ;
        }
        if ($value < 20) {
            $result = __('Unhealthy', 'live-weather-station') ;
        }
        return $result;
    }

    /**
     * Get the Chandler Burning index in human readable text.
     *
     * @param integer $value The value of the Chandler Burning index.
     * @return string The Chandler Burning index in human readable text.
     * @since 3.1.0
     */
    protected function get_cbi_text($value) {
        $result = __('Extreme', 'live-weather-station') ;
        if ($value <= 97.5) {
            $result = __('Very high', 'live-weather-station') ;
        }
        if ($value <= 90) {
            $result = __('High', 'live-weather-station') ;
        }
        if ($value <= 75) {
            $result = __('Moderate', 'live-weather-station') ;
        }
        if ($value < 50) {
            $result = __('Low', 'live-weather-station') ;
        }
        return $result;
    }

    /**
     * Get the wind angle in readable text (i.e. N-NW, S, ...).
     *
     * @deprecated 1.1.0 Angle "translation" is not specific to wind.
     * @see get_angle_text
     *
     * @param   integer   $value    The value of the angle.
     * @return  string  The wind angle in readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_windangle_text($value) {
        while ($value < 0) {
            $value = $value + 360;
        }
        $val = round(($value / 22.5) + 0.5);
        $dir = array();
        $dir[] = __('N', 'live-weather-station') ;
        $dir[] = __('N-NE', 'live-weather-station') ;
        $dir[] = __('NE', 'live-weather-station') ; 
        $dir[] = __('E-NE', 'live-weather-station') ;
        $dir[] = __('E', 'live-weather-station') ; 
        $dir[] = __('E-SE', 'live-weather-station') ;
        $dir[] = __('SE', 'live-weather-station') ; 
        $dir[] = __('S-SE', 'live-weather-station') ;
        $dir[] = __('S', 'live-weather-station') ; 
        $dir[] = __('S-SW', 'live-weather-station') ;
        $dir[] = __('SW', 'live-weather-station') ; 
        $dir[] = __('W-SW', 'live-weather-station') ;
        $dir[] = __('W', 'live-weather-station') ; 
        $dir[] = __('W-NW', 'live-weather-station') ;
        $dir[] = __('NW', 'live-weather-station') ; 
        $dir[] = __('N-NW', 'live-weather-station') ;
        return $dir[$val % 16];
    }

    /**
     * Get an angle in readable text (i.e. N-NW, S, ...).
     *
     * @param integer $value The value of the angle.
     * @return string The wind angle in readable text.
     * @since 1.1.0
     */
    protected function get_angle_text($value) {
        while ($value < 0) {
            $value = $value + 360;
        }
        $val = round((($value%360) / 22.5) + 0.4);
        $dir = array();
        $dir[] = __('N', 'live-weather-station') ;
        $dir[] = __('N-NE', 'live-weather-station') ;
        $dir[] = __('NE', 'live-weather-station') ;
        $dir[] = __('E-NE', 'live-weather-station') ;
        $dir[] = __('E', 'live-weather-station') ;
        $dir[] = __('E-SE', 'live-weather-station') ;
        $dir[] = __('SE', 'live-weather-station') ;
        $dir[] = __('S-SE', 'live-weather-station') ;
        $dir[] = __('S', 'live-weather-station') ;
        $dir[] = __('S-SW', 'live-weather-station') ;
        $dir[] = __('SW', 'live-weather-station') ;
        $dir[] = __('W-SW', 'live-weather-station') ;
        $dir[] = __('W', 'live-weather-station') ;
        $dir[] = __('W-NW', 'live-weather-station') ;
        $dir[] = __('NW', 'live-weather-station') ;
        $dir[] = __('N-NW', 'live-weather-station') ;
        $dir[] = __('N', 'live-weather-station') ;
        return $dir[$val];
    }

    /**
     * Get an angle in readable full text (i.e. North-Northwest, South, ...).
     *
     * @param   integer   $value    The value of the angle.
     * @return  string  The wind angle in readable text.
     * @since    1.2.2
     * @access   protected
     */
    protected function get_angle_full_text($value) {
        while ($value < 0) {
            $value = $value + 360;
        }
        $val = round((($value%360) / 22.5) + 0.4);
        $dir = array();
        $dir[] = __('North', 'live-weather-station') ;
        $dir[] = __('North-Northeast', 'live-weather-station') ;
        $dir[] = __('Northeast', 'live-weather-station') ;
        $dir[] = __('East-Northeast', 'live-weather-station') ;
        $dir[] = __('East', 'live-weather-station') ;
        $dir[] = __('East-Southeast', 'live-weather-station') ;
        $dir[] = __('Southeast', 'live-weather-station') ;
        $dir[] = __('South-Southeast', 'live-weather-station') ;
        $dir[] = __('South', 'live-weather-station') ;
        $dir[] = __('South-Southwest', 'live-weather-station') ;
        $dir[] = __('Southwest', 'live-weather-station') ;
        $dir[] = __('West-Southwest', 'live-weather-station') ;
        $dir[] = __('West', 'live-weather-station') ;
        $dir[] = __('West-Northwest', 'live-weather-station') ;
        $dir[] = __('Northwest', 'live-weather-station') ;
        $dir[] = __('North-Northwest', 'live-weather-station') ;
        $dir[] = __('North', 'live-weather-station') ;
        return $dir[$val];
    }

    /**
     * Get the battery level in lcd readable type.
     *
     * @param   integer   $value    The value of the battery.
     * @param   string  $type   The type of the module.
     * @return  string  The battery level in lcd readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_lcd_level_text($value, $type) {
        if ($value == -1) {
            return 'full';
        }
        $pct = $this->get_battery_percentage($value, $type);
        $result = 'full';
        if ($pct < 70) {
            $result = 'twothirds';
        }
        if ($pct < 40) {
            $result = 'onethird';
        }
        if ($pct < 10) {
            $result = 'empty';
        }
        return $result;
    }

    /**
     * Get the signal level in human readable text.
     *
     * @param   integer $value  The value of the battery.
     * @param   string  $type   The type of the module.
     * @return  string  The signal level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_level_text($value, $type) {
        if ($type =='NAMain') {
            $result = $this->get_wifi_level_text($value);
        }
        else {
            $result = $this->get_rf_level_text($value) ;
        }
        return $result;
    }
    
    /**
     * Get the RF level in human readable text.
     *
     * @param   integer $value  The value of the RF.
     * @return  string  The RF level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_rf_level_text($value) {
        $level = $this->get_rf_level($value);
        switch ($level) {
            case 3:
                $result = __('Full', 'live-weather-station') ;
                break;
            case 2:
                $result = __('High', 'live-weather-station') ;
                break;
            case 1:
                $result = __('Medium', 'live-weather-station') ;
                break;
            case 0:
                $result = __('Very low', 'live-weather-station') ;
                break;
            default:
                $result = __('No RF Signal', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the signal level in lcd readable type.
     *
     * @param   integer   $value    The value of the signal.
     * @param   string  $type   The type of the module.
     * @return  float  The signal level in lcd readable value.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_lcd_level_text($value, $type) {
        if ($value == -1) {
            return 0;
        }
        $pct = $this->get_signal_percentage($value, $type);
        $result = ((float)$pct)/100;
        return $result;
    }

    /**
     * Get the wifi level in human readable text.
     *
     * @param   integer $value  The value of the wifi.
     * @return  string   The wifi level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wifi_level_text($value) {
        $level = $this->get_wifi_level($value);
        switch ($level) {
            case 9999:
                $result = __('Unknown', 'live-weather-station') ;
                break;
            case 2:
                $result = __('High', 'live-weather-station') ;
                break;
            case 1:
                $result = __('Medium', 'live-weather-station') ;
                break;
            case 0:
                $result = __('Very low', 'live-weather-station') ;
                break;
            default:
                $result = __('No WiFi Signal', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the trend in human readable text.
     *
     * @param   integer $value  The value of the trend.
     * @return  string   The trend level in human readable text.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_trend_text($value) {
        switch (strtolower($value)) {
            case 'up':
                $result = __('Rising', 'live-weather-station') ;
                break;
            case 'down':
                $result = __('Falling', 'live-weather-station') ;
                break;
            default:
                $result = __('Stable', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the trend in standard readable text.
     *
     * @param   integer $value  The value of the trend.
     * @return  string   The trend level in standard readable text.
     * @since 3.0.0
     */
    protected function get_standard_trend_text($value) {
        switch (strtolower($value)) {
            case 'up':
                $result = 'Rising';
                break;
            case 'down':
                $result = 'Falling';
                break;
            default:
                $result = 'Steady';
        }
        return $result;
    }

    /**
     * Get the trend in numeral text.
     *
     * @param integer $value The value of the trend.
     * @return string The trend level in standard readable text.
     * @since 3.3.0
     */
    protected function get_numeral_trend_text($value) {
        switch (strtolower($value)) {
            case 'up':
                $result = '+0.6';
                break;
            case 'down':
                $result = '-0.6';
                break;
            default:
                $result = '0';
        }
        return $result;
    }

    /**
     * Get the moon phase in human readable text.
     *
     * @param   integer $value  The decimal value of the moon phase.
     * @return  string   The moon phase in human readable text.
     * @since    2.0.0
     */
    protected function get_moon_phase_text($value) {
        $names = array( __('New Moon', 'live-weather-station'),
                        __('Waxing Crescent', 'live-weather-station'),
                        __('First Quarter', 'live-weather-station'),
                        __('Waxing Gibbous', 'live-weather-station'),
                        __('Full Moon', 'live-weather-station'),
                        __('Waning Gibbous', 'live-weather-station'),
                        __('Third Quarter', 'live-weather-station'),
                        __('Waning Crescent', 'live-weather-station'),
                        __('New Moon', 'live-weather-station'));
        return $names[(int)floor(($value + 0.0625) * 8)];
    }

    /**
     * Get the icon moon phase id.
     *
     * @param integer $value The decimal value of the moon phase.
     * @return string The moon phase icon id.
     * @since 2.0.0
     */
    protected function get_moon_phase_icon($value) {
        $id = array('new',
                    'waxing-crescent-1',
                    'waxing-crescent-2',
                    'waxing-crescent-3',
                    'waxing-crescent-4',
                    'waxing-crescent-5',
                    'waxing-crescent-6',
                    'first-quarter',
                    'waxing-gibbous-1',
                    'waxing-gibbous-2',
                    'waxing-gibbous-3',
                    'waxing-gibbous-4',
                    'waxing-gibbous-5',
                    'waxing-gibbous-6',
                    'full',
                    'waning-gibbous-1',
                    'waning-gibbous-2',
                    'waning-gibbous-3',
                    'waning-gibbous-4',
                    'waning-gibbous-5',
                    'waning-gibbous-6',
                    'third-quarter',
                    'waning-crescent-1',
                    'waning-crescent-2',
                    'waning-crescent-3',
                    'waning-crescent-4',
                    'waning-crescent-5',
                    'waning-crescent-6',
                    'new');
        $s = (get_option('live_weather_station_moon_icons')==0 ? '' : 'alt-');
        return $s . $id[(int)floor(($value + 0.01786) * 28)];
    }

    /**
     * Get the icon lunation id.
     *
     * @param integer $value The decimal value of the lunation (moon age).
     * @return string The moon phase icon id.
     * @since 3.0.0
     */
    protected function get_lunation_icon($value) {
        $lunation = 29.56;
        $phase = 1 - (($lunation - $value) / $lunation);
        return $this->get_moon_phase_icon($phase);
    }

    /**
     * Retrieve and format data for widget.
     *
     * @param string $id The device or module id.
     * @param string $type Optional. The type of widget.
     * @param boolean $obsolescence_filtering Optional. True if data must be filtered.
     * @return array An array containing the formated datas, ready to be read by widgets.
     * @since 3.1.0
     */
    protected function get_widget_data($id, $type='outdoor', $obsolescence_filtering=false) {
        $fingerprint = md5($id.$type.($obsolescence_filtering?'filtered':'unfiltered'));
        $result = Cache::get_widget($fingerprint);
        if ($result) {
            return $result;
        }
        $result = array();
        switch ($type) {
            case 'ephemeris' :
                $datas = $this->get_ephemeris_datas($id);
                break;
            case 'indoor':
                $datas = $this->get_indoor_datas($id, $obsolescence_filtering);
                break;
            case 'thunderstorm':
                $datas = $this->get_thunderstorm_datas($id, $obsolescence_filtering);
                break;
            case 'solar':
                $datas = $this->get_solar_datas($id, $obsolescence_filtering);
                break;
            default:
                $datas = $this->get_outdoor_datas($id, $obsolescence_filtering);
        }
        $err = 0 ;
        $ts = 0;
        $msg = __('Successful operation', 'live-weather-station');
        if (count($datas)==0) {
            $err = 3 ;
            $msg = __('Database contains inconsistent datas', 'live-weather-station');
        }
        else {
            $result['name'] = $datas[0]['device_name'];
            $key = '';
            $sub = array();
            foreach ($datas as $data) {
                if ($data['module_id'] != $key) {
                    if (!empty($sub)) {
                        $result['modules'][$key] = $sub;
                    }
                    $key = $data['module_id'];
                    $sub = array();
                    $sub['name'] = DeviceManager::get_module_name($data['device_id'], $data['module_id']);
                    $sub['type'] = $data['module_type'];
                    $sub['id'] = $data['module_id'];
                    $sub['datas'] = array();
                }
                $ssub = array();
                $ssub['raw_value'] = $data['measure_value'];
                $ssub['value'] = $this->output_value($data['measure_value'], $data['measure_type'], false, false, $data['module_type']);
                $ssub['unit'] = $this->output_unit($data['measure_type'], $data['module_type']);
                $sub_ts = strtotime ($data['measure_timestamp']);
                $ssub['timestamp'] = $sub_ts;
                if ($sub_ts>$ts) {$ts=$sub_ts;}
                $sub['datas'][$data['measure_type']] = $ssub;
            }
            if (!empty($sub)) {
                $result['modules'][$key] = $sub;
            }
        }
        $result['condition'] = array('value' => $err, 'message' =>$msg);
        $result['timestamp'] = $ts;
        Cache::set_widget($fingerprint, $result);
        return $result;
    }

    /**
     * Says if value must be shown.
     *
     * @param $measure_type string The type of value.
     * @param $aggregated boolean Display is in aggregated mode.
     * @param $outdoor boolean Display is in outdoor mode.
     * @param $computed boolean Display is in computed mode.
     * @param $pollution boolean Display is in pollution mode.
     * @param $psychrometry boolean Display is in psychrometry mode.
     * @return boolean True if the value must be shown, false otherwise.
     * @since 2.0.0
     */
    private function is_value_ok ($measure_type, $aggregated, $outdoor, $computed, $pollution, $psychrometry) {
        $result = false;
        switch ($measure_type) {
            case 'co2':
            case 'noise':
            case 'health_idx':
                $result = $aggregated && !$outdoor;
                break;
            case 'cloudiness':
            case 'pressure':
            case 'humidity':
            case 'temperature':
            case 'snow':
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
            case 'rain_yesterday_aggregated':
            case 'rain_month_aggregated':
            case 'rain_season_aggregated':
            case 'rain_year_aggregated':
            case 'windstrength':
            case 'windangle':
            case 'guststrength':
            case 'windstrength_hour_max':
            case 'windstrength_day_max':
            case 'irradiance':
            case 'uv_index':
            case 'illuminance':
            case 'soil_temperature':
            case 'leaf_wetness':
            case 'moisture_content':
            case 'moisture_tension':
            case 'evapotranspiration':
            case 'strike_count':
            case 'strike_instant':
            case 'strike_distance':
            case 'strike_bearing':
                $result = $aggregated || $outdoor;
                break;
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
            case 'heat_index':
            case 'cloud_ceiling':
            case 'cbi':
                $result = $computed && $outdoor;
                break;
            case 'wet_bulb':
            case 'air_density':
                $result = ($computed && $outdoor) || $psychrometry;
                break;
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
            case 'wood_emc':
            case 'emc':
            case 'specific_enthalpy':
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
            case 'vapor_pressure':
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
            case 'absolute_humidity':
                $result = $psychrometry;
                break;
            case 'o3':
            case 'co':
                $result = $aggregated || $pollution;
                break;
        }
        return $result;
    }

    /**
     * Format the selected datas for lcd usage.
     *
     * @param   array   $datas  An array containing the selected datas.
     * @param   string   $measure_type  The measure type(s) to include.
     * @param   boolean   $computed  Includes computed measures too.
     * @return  array   An array containing the formated datas, ready to be displayed by lcd controls.
     * @since    1.0.0
     * @access   protected
     */
    protected function format_lcd_datas($datas, $measure_type, $computed=false) {
        $save_locale = setlocale(LC_ALL,'');
        setlocale(LC_ALL, lws_get_display_locale());
        $result = array();
        $response = array ();
        $battery = array();
        $signal = array();
        $min = array();
        $max = array();
        $values = array();
        $value_types = array ('humidity' => 'NAModule1', 'rain' => 'NAModule3', 'windangle' => 'NAModule2', 'windstrength' => 'NAModule2', 'pressure' => 'NAMain', 'temperature' => 'NAModule1');
        $temperature_trend = array();
        $pressure_trend = array();
        $humidity_trend = array();
        $err = 0;
        $aggregated = ($measure_type == 'aggregated');
        $outdoor = ($measure_type == 'outdoor');
        $pollution = ($measure_type == 'pollution');
        $psychrometry = ($measure_type == 'psychrometric');
        $dew_point = 0;
        $has_dew_point = false;
        $temp_ref = 0;
        $has_temp_ref = false;
        $hum_ref = 0;
        $has_hum_ref = false;
        $wind_ref = 0;
        $has_wind_ref = false;
        $temperature_test = 0;
        $msg = __('Successful operation', 'live-weather-station');
        if (count($datas)==0) {
            $err = 3 ;
            $msg = __('Database contains inconsistent datas', 'live-weather-station');
        }
        else {
            foreach ($datas as $data) {
                if ($data['measure_type'] == 'battery') {
                    $battery[$data['module_id']] = $this->get_battery_lcd_level_text($data['measure_value'], $data['module_type']);
                }
                if ($data['measure_type'] == 'signal') {
                    $signal[$data['module_id']] = $this->get_signal_lcd_level_text($data['measure_value'], $data['module_type']);
                }
                if ($data['measure_type'] == 'temperature_max') {
                    $max[$data['module_id']]['temperature'] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'temperature_min') {
                    $min[$data['module_id']]['temperature'] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'temperature_trend') {
                    $temperature_trend[$data['module_id']] = ($data['measure_value'] == 'stable' ? 'steady' : $data['measure_value']);
                }
                if ($data['measure_type'] == 'pressure_max') {
                    $max[$data['module_id']]['pressure'] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'pressure_min') {
                    $min[$data['module_id']]['pressure'] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'pressure_trend') {
                    $pressure_trend[$data['module_id']] = ($data['measure_value'] == 'stable' ? 'steady' : $data['measure_value']);
                }
                if ($data['measure_type'] == 'humidity_max') {
                    $max[$data['module_id']]['humidity'] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'humidity_min') {
                    $min[$data['module_id']]['humidity'] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'humidity_trend') {
                    $humidity_trend[$data['module_id']] = ($data['measure_value'] == 'stable' ? 'steady' : $data['measure_value']);
                }
                if ($data['measure_type'] == 'dew_point') {
                    $dew_point = $data['measure_value'];
                    $has_dew_point = true;
                }
                if ($data['measure_type'] == 'temperature_ref') {
                    $temp_ref = $data['measure_value'];
                    $has_temp_ref = true;
                }
                if ($data['measure_type'] == 'wind_ref') {
                    $wind_ref = $data['measure_value'];
                    $has_wind_ref = true;
                }
                if ($data['measure_type'] == 'humidity_ref') {
                    $hum_ref = $data['measure_value'];
                    $has_hum_ref = true;
                }
                if (array_key_exists($data['measure_type'], $value_types) && $value_types[$data['measure_type']] == $data['module_type']) {
                    $values[$data['measure_type']] = $data['measure_value'] ;
                }
            }
            if ($has_temp_ref ) {
                $temperature_test = $temp_ref;
            }
            elseif (array_key_exists('temperature', $values)) {
                $temperature_test = $values['temperature'];
            }
            foreach ($datas as $data) {
                $unit = $this->output_unit($data['measure_type'], $data['module_type']);
                $measure = array ();
                $measure['min'] = 0;
                $measure['max'] = 0;
                $measure['value'] = $this->output_value($data['measure_value'], $data['measure_type']);
                $measure['unit'] = $unit['unit'];
                $measure['decimals'] = $this->decimal_for_output($data['measure_type'], $measure['value']);
                if ($measure['decimals']>5) {
                    $measure['decimals'] = 5;
                }
                $measure['sub_unit'] = $unit['comp'];
                $measure['show_sub_unit'] = ($unit['comp']!='');
                $measure['show_min_max'] = false;
                if ($outdoor || ($data['module_name'][0] == '[' && $aggregated && $outdoor)) {
                    $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',__('O/DR', 'live-weather-station') . ':' .$this->output_abbreviation($data['measure_type']));
                }
                elseif ($pollution || ($data['measure_type'] == 'o3') || ($data['measure_type'] == 'co')) {
                    $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',$this->get_measurement_type($data['measure_type']));
                }
                elseif ($psychrometry) {
                    $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',$this->get_measurement_type($data['measure_type']));
                }
                else {
                    if ($data['module_name'][0] == '[') {
                        $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',__('O/DR', 'live-weather-station') . ':' .$this->output_abbreviation($data['measure_type']));
                    }
                    else {
                        $measure['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', DeviceManager::get_module_name($data['device_id'], $data['module_id']));
                    }
                }
                if (array_key_exists($data['module_id'], $battery)) {
                    $measure['battery'] = $battery[$data['module_id']];
                }
                else {
                    $measure['battery'] = $this->get_battery_lcd_level_text(-1, 'none');
                }
                $measure['trend'] = '';
                $measure['show_trend'] = false;
                $measure['show_alarm'] = $this->is_alarm_on($data['measure_value'], $data['measure_type'], $data['module_type']);
                if (array_key_exists($data['module_id'], $signal)) {
                    $measure['signal'] = $signal[$data['module_id']];
                }
                else {
                    $measure['signal'] = $this->get_signal_lcd_level_text(-1, 'none');
                }
                if (($data['measure_type'] == $measure_type) || (($data['measure_type'] != $measure_type) && $this->is_value_ok($data['measure_type'], $aggregated, $outdoor, $computed, $pollution, $psychrometry))) {
                    switch (strtolower($data['measure_type'])) {
                        case 'co2':
                        case 'o3':
                        case 'co':
                        case 'noise':
                        case 'health_idx':
                        case 'cbi':
                            $response[] = $measure;
                            break;
                        case 'dew_point':
                            if ($has_temp_ref && $this->is_valid_dew_point($temp_ref)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'frost_point':
                            if ($has_temp_ref && $this->is_valid_frost_point($temp_ref)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'wind_chill':
                            if ($has_temp_ref && $has_wind_ref && $this->is_valid_wind_chill($temp_ref, $wind_ref)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'heat_index':
                            if ($has_temp_ref && $has_hum_ref && $has_dew_point && $this->is_valid_heat_index($temp_ref, $hum_ref, $dew_point)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'humidex':
                            if ($has_temp_ref && $has_hum_ref && $has_dew_point && $this->is_valid_humidex($temp_ref, $hum_ref, $dew_point)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'cloud_ceiling':
                            if ($has_temp_ref && $has_dew_point) {
                                $response[] = $measure;
                            }
                            break;
                        case 'cloudiness':
                            if (!$outdoor) {
                                $measure['sub_unit'] = __('clouds', 'live-weather-station');
                                $measure['show_sub_unit'] = true;
                            }
                            $response[] = $measure;
                            break;
                        case 'humidity':
                            if (array_key_exists($data['module_id'], $humidity_trend)) {
                                $measure['trend'] = $humidity_trend[$data['module_id']];
                                $measure['show_trend'] = true;
                            }
                            if (array_key_exists($data['module_id'], $min) && array_key_exists($data['module_id'], $max)) {
                                if (array_key_exists('humidity', $min[$data['module_id']]) && array_key_exists('humidity', $max[$data['module_id']])) {
                                    $measure['min'] = $min[$data['module_id']]['humidity'];
                                    $measure['max'] = $max[$data['module_id']]['humidity'];
                                    $measure['show_min_max'] = true;
                                }
                            }
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NAModule1') {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'pressure':
                            if (array_key_exists($data['module_id'], $pressure_trend)) {
                                $measure['trend'] = $pressure_trend[$data['module_id']];
                                $measure['show_trend'] = true;
                            }
                            if (array_key_exists($data['module_id'], $min) && array_key_exists($data['module_id'], $max)) {
                                if (array_key_exists('pressure', $min[$data['module_id']]) && array_key_exists('pressure', $max[$data['module_id']])) {
                                    $measure['min'] = $min[$data['module_id']]['pressure'];
                                    $measure['max'] = $max[$data['module_id']]['pressure'];
                                    $measure['show_min_max'] = true;
                                }
                            }
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NAMain') {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'temperature':
                            if (array_key_exists($data['module_id'], $temperature_trend)) {
                                $measure['trend'] = $temperature_trend[$data['module_id']];
                                $measure['show_trend'] = true;
                            }
                            if (array_key_exists($data['module_id'], $min) && array_key_exists($data['module_id'], $max)) {
                                if (array_key_exists('temperature', $min[$data['module_id']]) && array_key_exists('temperature', $max[$data['module_id']])) {
                                    $measure['min'] = $min[$data['module_id']]['temperature'];
                                    $measure['max'] = $max[$data['module_id']]['temperature'];
                                    $measure['show_min_max'] = true;
                                }
                            }
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NAModule1') {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'rain':
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                if ($this->is_valid_rain($temperature_test)) {
                                    $response[] = $measure;
                                }
                            }
                            if ($outdoor && $data['module_type'] == 'NAModule3') {
                                if ($this->is_valid_rain($temperature_test)) {
                                    $response[] = $measure;
                                }
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                if ($this->is_valid_rain($temperature_test)) {
                                    $response[] = $measure;
                                }
                            }
                            break;
                        case 'rain_hour_aggregated':
                        case 'rain_day_aggregated':
                        case 'rain_yesterday_aggregated':
                        case 'rain_month_aggregated':
                        case 'rain_season_aggregated':
                        case 'rain_year_aggregated':
                            if ($this->is_valid_rain($temperature_test)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'snow':
                            if ($this->is_valid_snow($temperature_test)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'windangle':
                        case 'gustangle':
                        case 'windangle_max':
                        case 'windangle_day_max':
                        case 'windangle_hour_max':
                        case 'windstrength':
                        case 'guststrength':
                        case 'windstrength_max':
                        case 'windstrength_day_max':
                        case 'windstrength_hour_max':
                            if (($data['measure_type'] == $measure_type) || $aggregated) {
                                $response[] = $measure;
                            }
                            if (($outdoor && $data['module_type'] == 'NAModule2') || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'irradiance':
                        case 'uv_index':
                        case 'illuminance':
                        case 'strike_count':
                        case 'strike_instant':
                        case 'strike_distance':
                        case 'visibility':
                        case 'strike_bearing':
                        if (($data['measure_type'] == $measure_type) || $aggregated || $outdoor) {
                            $response[] = $measure;
                        }
                        case 'soil_temperature':
                        case 'leaf_wetness':
                        case 'moisture_content':
                        case 'moisture_tension':
                        case 'evapotranspiration':
                            if (($data['measure_type'] == $measure_type) || $aggregated) {
                                $response[] = $measure;
                            }
                            break;
                        case 'wet_bulb':
                        case 'air_density':
                        case 'wood_emc':
                        case 'emc':
                        case 'equivalent_temperature':
                        case 'potential_temperature':
                        case 'equivalent_potential_temperature':
                        case 'specific_enthalpy':
                        case 'partial_vapor_pressure':
                        case 'saturation_vapor_pressure':
                        case 'vapor_pressure':
                        case 'absolute_humidity':
                        case 'partial_absolute_humidity':
                        case 'saturation_absolute_humidity':
                            if ($psychrometry) {
                                $response[] = $measure;
                            }
                            break;

                    }
                }
            }
        }
        if (count($response)==0) {
            $err = 4 ;
            $msg = __('All data have been filtered: nothing to show', 'live-weather-station');
        }
        $result['condition'] = array('value' => $err, 'message' =>$msg);
        $result['datas'] = $response;
        setlocale(LC_ALL, $save_locale);
        return $result;
    }

    /**
     * Format the selected datas for stickertags usage.
     *
     * @param array $datas An array containing the selected datas.
     * @return string The formated datas, ready to be outputed as stickertags.txt file.
     * @since 3.0.0
     *
     */
    protected function format_stickertags_data($datas) {
        $tz = get_option('timezone_string');
        $ts = time();
        $tr = 0;
        $hr = 0;
        $dr = 0;
        $values = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        if (count($datas) == 0) {
            $values[0] = $this->get_time_from_utc($ts, $tz, 'H:i');
            $values[1] = $this->get_date_from_utc($ts, $tz, 'd/m/Y');
        }
        else {
            foreach ($datas as $data) {
                switch ($data['measure_type']) {
                    case 'last_seen':
                        if ($data['module_type'] == 'NAMain') {
                            $ts = strtotime($data['measure_value']);
                        }
                        break;
                    case 'loc_timezone':
                        $tz = $data['measure_value'];
                        break;
                    case 'temperature_ref':
                        $tr = $data['measure_value'];
                        break;
                    case 'humidity_ref':
                        $hr = $data['measure_value'];
                        break;
                    case 'dew_point':
                        $dr = $data['measure_value'];
                        break;
                }
            }
            //if ((time() - $ts > 240) && (time() - $ts < 900)) {
                //$ts = time() - 240;
            //}
            $values[0] = $this->get_time_from_utc($ts, $tz, 'H:i');
            $values[1] = $this->get_date_from_utc($ts, $tz, 'd/m/Y');
            foreach ($datas as $data) {
                switch ($data['measure_type']) {
                    case 'temperature':
                        if (strtolower($data['module_type']) == 'namodule1') {
                            $values[2] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'heat_index':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_heat_index($tr, $hr, $dr)) {
                                $values[3] = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'wind_chill':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_wind_chill($tr, $data['measure_value'])) {
                                $values[4] = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'humidity':
                        if (strtolower($data['module_type']) == 'namodule1') {
                            $values[5] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'dew_point':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_dew_point($tr)) {
                                $values[6] = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'pressure':
                        if (strtolower($data['module_type']) == 'namain') {
                            $values[7] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'pressure_trend':
                        if (strtolower($data['module_type']) == 'namain') {
                            $values[8] = $this->get_standard_trend_text($data['measure_value']);
                        }
                        break;
                    case 'windstrength':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $values[9] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'windangle':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $values[10] = str_replace('-', '', $this->get_angle_text($data['measure_value']));
                        }
                        break;
                    case 'rain_day_aggregated':
                        if (strtolower($data['module_type']) == 'namodule3') {
                            $values[11] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'sunrise':
                        if (strtolower($data['module_type']) == 'naephemer') {
                            $values[13] = $this->get_time_from_utc($data['measure_value'], $tz, 'H:i');
                        }
                        break;
                    case 'sunset':
                        if (strtolower($data['module_type']) == 'naephemer') {
                            $values[14] = $this->get_time_from_utc($data['measure_value'], $tz, 'H:i');
                        }
                        break;
                    case 'guststrength':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $values[16] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                }

            }
        }
        $values[17] = 'C|km/h|hPa|mm';
        return implode(',', $values);
    }

    /**
     * Format the selected datas for YoWindow usage.
     *
     * @param array $datas An array containing the selected datas.
     * @return string The formated datas, ready to be outputed as YoWindow.xml file.
     * @since 3.3.0
     *
     */
    protected function format_yowindow_data($datas) {
        $tr = 0;
        $hr = 0;
        $dr = 0;
        $temp = null;
        $temp_like = null;
        $humidity = null;
        $pressure = null;
        $pressure_trend = null;
        $wind_angle = null;
        $wind_gust = null;
        $wind_strength = null;
        $uv_index = null;
        $irradiance = null;
        $illuminance = null;
        $rain = null;
        $rain_day_aggregated = null;
        $strike = false;
        if (count($datas) > 0) {
            foreach ($datas as $data) {
                switch ($data['measure_type']) {
                    case 'temperature_ref':
                        $tr = $data['measure_value'];
                        break;
                    case 'humidity_ref':
                        $hr = $data['measure_value'];
                        break;
                    case 'dew_point':
                        $dr = $data['measure_value'];
                        break;
                }
            }
            foreach ($datas as $data) {
                switch ($data['measure_type']) {
                    case 'temperature':
                        if (strtolower($data['module_type']) == 'namodule1') {
                            $temp = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'heat_index':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_heat_index($tr, $hr, $dr)) {
                                $temp_like = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'humidex':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_humidex($tr, $hr, $dr)) {
                                $temp_like = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'wind_chill':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_wind_chill($tr, $data['measure_value'])) {
                                $temp_like = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'humidity':
                        if (strtolower($data['module_type']) == 'namodule1') {
                            $humidity = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'pressure':
                        if (strtolower($data['module_type']) == 'namain') {
                            $pressure = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'pressure_trend':
                        if (strtolower($data['module_type']) == 'namain') {
                            $pressure_trend = $this->get_numeral_trend_text($data['measure_value']);
                        }
                        break;
                    case 'uv_index':
                        if (strtolower($data['module_type']) == 'namodule5') {
                            $uv_index = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'irradiance':
                        if (strtolower($data['module_type']) == 'namodule5') {
                            $irradiance = sprintf('%d', round($data['measure_value'],0));
                        }
                        break;
                    case 'illuminance':
                        if (strtolower($data['module_type']) == 'namodule5') {
                            $illuminance = sprintf('%d', round($data['measure_value'] / 1000,0));
                        }
                        break;
                    case 'windstrength':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $wind_strength = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'guststrength':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $wind_gust = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'windangle':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $wind_angle = sprintf('%d', round($data['measure_value'],0));
                        }
                        break;
                    case 'rain':
                        if (strtolower($data['module_type']) == 'namodule3') {
                            $rain = sprintf('%d', round($data['measure_value'],0));
                        }
                        break;
                    case 'rain_day_aggregated':
                        if (strtolower($data['module_type']) == 'namodule3') {
                            $rain_day_aggregated = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'strike_instant':
                        if (strtolower($data['module_type']) == 'namodule7') {
                            if ($data['measure_value'] > 0) {
                                $strike = true;
                            }
                        }
                        break;
                }

            }
        }
        $result = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $result .= '<!-- Generated by ' . LWS_FULL_NAME . ' - https://weather.station.software -->' . PHP_EOL;
        $result .= '<response>' . PHP_EOL;
        $result .= ' <current_weather>' . PHP_EOL;
        if (isset($temp)) {
            $result .= '  <temperature>' . PHP_EOL;
            $result .= '   <current value="' . $temp . '" unit="c"/>' . PHP_EOL;
            if (isset($temp_like)) {
                $result .= '   <feels_like value="' . $temp_like . '" unit="c"/>' . PHP_EOL;
            }
            $result .= '  </temperature>' . PHP_EOL;
        }
        if (isset($humidity)) {
            $result .= '   <humidity value="' . $humidity . '"/>' . PHP_EOL;
        }
        if (isset($pressure)) {
            $s = '';
            if (isset($pressure_trend)) {
                $s = ' trend="' . $pressure_trend . '"';
            }
            $result .= '   <pressure value="' . $pressure . '"' . $s . ' unit="hPa"/>' . PHP_EOL;
        }
        if (isset($uv_index)) {
            $result .= '   <uv value="' . $uv_index . '"/>' . PHP_EOL;
        }
        if (isset($irradiance)) {
            $s = '';
            if (isset($illuminance)) {
                $s = ' radiation="' . $illuminance . '"';
            }
            $result .= '   <solar energy="' . $irradiance . '"' . $s .'/>' . PHP_EOL;
        }
        if (isset($wind_angle) || isset($wind_strength) || isset($wind_gust)) {
            $result .= '  <wind>' . PHP_EOL;
            if (isset($wind_angle)) {
                $result .= '   <direction value="' . $wind_angle . '"/>' . PHP_EOL;
            }
            if (isset($wind_strength)) {
                $result .= '   <speed value="' . $wind_strength . '" unit="km/h"/>' . PHP_EOL;
            }
            if (isset($wind_gust)) {
                $result .= '   <gusts value="' . $wind_gust . '" unit="km/h"/>' . PHP_EOL;
            }
            $result .= '  </wind>' . PHP_EOL;
        }
        if (isset($rain) || isset($rain_day_aggregated)) {
            $result .= '  <sky>' . PHP_EOL;
            $result .= '   <precipitation>' . PHP_EOL;
            $result .= '    <rain>' . PHP_EOL;
            if (isset($rain)) {
                $result .= '      <rate value="' . $rain . '" unit="mm/h"/>' . PHP_EOL;
            }
            if (isset($rain_day_aggregated)) {
                $result .= '      <daily_total value="' . $rain_day_aggregated . '" unit="mm"/>' . PHP_EOL;
            }
            $result .= '    </rain>' . PHP_EOL;
            $result .= '   </precipitation>' . PHP_EOL;
            $result .= '  </sky>' . PHP_EOL;
        }
        if ($strike) {
            $result .= '  <thunderstorm value="yes"/>' . PHP_EOL;
        }
        //$result .= '  <auto_update>' . PHP_EOL;
        //$result .= '   <interval value="300" />' . PHP_EOL;
        //$result .= '  </auto_update>' . PHP_EOL;
        $result .= ' </current_weather>' . PHP_EOL;
        $result .= '</response>' . PHP_EOL;
        return $result;
    }

    /**
     * Indicates if rain is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees.
     * @return  boolean   True if rain is valid, false otherwise.
     * @since    2.0.0
     */
    protected function is_valid_rain($temp_ref) {
        $result = false;
        if ($temp_ref > -10) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if snow is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees.
     * @return  boolean   True if snow is valid, false otherwise.
     * @since    2.0.0
     */
    protected function is_valid_snow($temp_ref) {
        $result = false;
        if ($temp_ref < 10) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if dew point is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if dew point is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_dew_point($temp_ref) {
        $result = false;
        if ($temp_ref >= 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if frost point is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if frost point is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_frost_point($temp_ref) {
        $result = false;
        if ($temp_ref < 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if wind chill is valid (i.e. must be displayed).
     *
     * @param   float   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @param   float   $wind_chill     The wind chill value
     * @return  boolean   True if wind chill is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_wind_chill($temp_ref, $wind_chill=-200.0) {
        $result = false;
        if ($temp_ref < 10 && $temp_ref > $wind_chill) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if heat index is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @param   integer   $hum_ref      Reference humidity in % (reference = as it was at compute time).
     * @param   integer   $dew_ref      Reference dew point in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if heat index is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_heat_index($temp_ref, $hum_ref, $dew_ref) {
        $result = false;
        if ( ($temp_ref >= 27) && ($hum_ref>=40) && ($dew_ref>=12)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if humidex is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @param   integer   $hum_ref      Reference humidity in % (reference = as it was at compute time).
     * @param   integer   $dew_ref      Reference dew point in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if humidex is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_humidex($temp_ref, $hum_ref, $dew_ref) {
        $result = false;
        if ( ($temp_ref >= 15) && ($hum_ref>=20) && ($dew_ref>=10)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Get the measurement minimal rendered value.
     *
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @param string $opt The specific type of option.
     * @return integer The measurement minimal to render in controls.
     * @since 3.0.0
     */
    protected function get_measurement_option ($type, $module_type, $opt) {
        switch (strtolower($type)) {
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
            case 'humidex':
            case 'heat_index':
            case 'wet_bulb':
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
                if (strtolower($module_type)=='namodule4' || strtolower($module_type)=='namain') {
                    $t = 'tempint';
                }
                else {
                    $t = 'tempext';
                }
                break;
            case 'humidity':
            case 'humidity_min':
            case 'humidity_max':
                if (strtolower($module_type)=='namodule4' || strtolower($module_type)=='namain') {
                    $t = 'humint';
                }
                else {
                    $t = 'humext';
                }
                break;
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_ref':
                $t = 'pressure';
                break;
            case 'rain_yesterday_aggregated':
                $t = 'rain_day_aggregated';
                break;
            case 'rain_season_aggregated':
                $t = 'rain_year_aggregated';
                break;
            case 'cloudiness':
                $t = 'cloud_cover';
                break;
            case 'gustangle':
            case 'windangle_hour_max':
            case 'windangle_day_max':
                $t = 'windangle';
                break;
            case 'guststrength':
            case 'windstrength_hour_max':
            case 'windstrength_day_max':
                $t = 'windstrength';
                break;
            case 'wood_emc':
            case 'emc':
                $t = 'emc';
                break;
            case 'vapor_pressure':
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
                $t = 'vapor_pressure';
                break;
            case 'absolute_humidity':
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
                $t = 'absolute_humidity';
                break;
            default:
                $t = $type;
        }
        return $this->output_value(get_option('live_weather_station_' . $t . '_' . $opt), $type);
    }

    /**
     * Get the measurement minimal rendered value.
     *
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @return integer The the measurement minimal to render in controls.
     * @since 2.1.0
     */
    protected function get_measurement_min($type, $module_type) {
        return $this->get_measurement_option($type, $module_type, 'min_value');
    }

    /**
     * Get the measurement maximal rendered value.
     *
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @return integer The the measurement maximal to render in controls.
     * @since 2.1.0
     */
    protected function get_measurement_max($type, $module_type) {
        return $this->get_measurement_option($type, $module_type, 'max_value');
    }

    /**
     * Get the measurement minimal rendered value.
     *
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @return integer The the measurement minimal to render in controls.
     * @since 2.1.0
     */
    protected function get_measurement_alarm_min($type, $module_type) {
        return $this->get_measurement_option($type, $module_type, 'min_alarm');
    }

    /**
     * Get the measurement maximal rendered value.
     *
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @return integer The the measurement maximal to render in controls.
     * @since 2.1.0
     */
    protected function get_measurement_alarm_max($type, $module_type) {
        return $this->get_measurement_option($type, $module_type, 'max_alarm');
    }

    /**
     * Indicates if alarm is on.
     *
     * @param mixed $value The value to test.
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @return boolean True if alarm is on, false otherwise.
     * @since    1.0.0
     */
    protected function is_alarm_on($value, $type, $module_type) {
        $result = (($value < $this->get_measurement_option($type, $module_type, 'min_alarm')) ||
                   ($value > $this->get_measurement_option($type, $module_type, 'max_alarm')));
    }

    /**
     * Get all formated datas for a single station.
     *
     * @param integer $guid The device GUID.
     * @param boolean $obsolescence_filtering Don't return obsolete data.
     * @param boolean $full Optional. Get all data (not just only "writable").
     * @return array An array containing all the formated datas.
     * @since 3.0.0
     */
    protected function get_all_formated_datas($guid, $obsolescence_filtering=false, $full=false) {
        $station = $this->get_station_informations_by_guid($guid);
        $raw_data = $this->get_all_datas($station['station_id'], $obsolescence_filtering);
        $result = array();
        $result['station'] = $station;
        $result['module'] = array();
        $id = '';
        $module = array();
        if (count($raw_data) > 0) {
            $result['device_id'] = $raw_data[0]['device_id'];
            foreach ($raw_data as $data) {
                if ($id != $data['module_id']) {
                    if (count($module) > 0) {
                        $result['module'][] = $module;
                        $module = array();
                        $module['measure'] = array();
                    }
                    $id = $data['module_id'];
                }
                $module['module_id'] = $data['module_id'];
                $module['module_name'] = $data['module_name'];
                $module['module_type'] = $data['module_type'];
                $module['module_type_name'] = $this->get_module_type($data['module_type'], false);
                if (($data['measure_type'] != 'last_refresh') && (strpos($data['measure_type'], '_ref') > 0) && !$full) {
                    continue;
                }
                if ($data['measure_type'] == 'firmware') {
                    $module['firmware'] = $data['measure_value'];
                    $module['firmware_txt'] = __('rev.', 'live-weather-station') . ' ' . $data['measure_value'];
                }
                if ($data['measure_type'] == 'last_refresh') {
                    $module['last_refresh'] = $data['measure_value'];
                    $module['last_refresh_txt'] = $this->output_value($data['measure_timestamp'], 'last_refresh', false, false, $module['module_type'], $station['loc_timezone']);
                    $module['last_refresh_diff_txt'] = self::get_positive_time_diff_from_mysql_utc($module['last_refresh']);
                }
                if ($data['measure_type'] == 'last_seen') {
                    $module['last_seen'] = $data['measure_value'];
                    $module['last_seen_txt'] = $this->output_value($data['measure_value'], $data['measure_type'], false, false, $module['module_type'], $station['loc_timezone']);
                    $module['last_seen_diff_txt'] = self::get_positive_time_diff_from_mysql_utc($module['last_seen']);
                }
                if ($data['measure_type'] == 'first_setup') {
                    $module['first_setup'] = $data['measure_value'];
                    $module['first_setup_txt'] = $this->output_value($data['measure_value'], $data['measure_type'], false, false, $module['module_type'], $station['loc_timezone']);
                    $module['first_setup_diff_txt'] = self::get_positive_time_diff_from_mysql_utc($module['first_setup']);
                }
                if ($data['measure_type'] == 'last_upgrade') {
                    $module['last_upgrade'] = $data['measure_value'];
                    $module['last_upgrade_txt'] = $this->output_value($data['measure_value'], $data['measure_type'], false, false, $module['module_type'], $station['loc_timezone']);
                }
                if ($data['measure_type'] == 'last_setup') {
                    $module['last_setup'] = $data['measure_value'];
                    $module['last_setup_txt'] = $this->output_value($data['measure_value'], $data['measure_type'], false, false, $module['module_type'], $station['loc_timezone']);
                    $module['last_setup_diff_txt'] = self::get_positive_time_diff_from_mysql_utc($module['last_setup']);
                }
                if ($data['measure_type'] == 'battery') {
                    $module['battery'] = $data['measure_value'];
                    $module['battery_txt'] = $this->get_battery_level_text($module['battery'], $module['module_type']);
                    $module['battery_icn'] = $this->output_iconic_value($module['battery'], 'battery', $module['module_type'], false, 'style="color:#999"', 'fa-lg');
                }
                if ($data['measure_type'] == 'signal') {
                    $module['signal'] = $data['measure_value'];
                    $module['signal_txt'] = $this->get_signal_level_text($module['signal'], $module['module_type']);
                    $module['signal_icn'] = $this->output_iconic_value($module['signal'], 'signal', $module['module_type'], false, 'style="color:#999"', 'fa-lg');
                }
                if ((!$full && in_array($data['measure_type'], $this->showable_measurements)) ||
                    ($full && in_array($data['measure_type'], array_merge($this->showable_measurements, $this->not_showable_measurements)))) {
                    $val = array();
                    /*
                     * @fixme how the hell Netatmo windgauge have temperature max/min attributes?
                     */
                    if ((strpos($data['measure_type'], 'perature') > 0) && ($data['module_type'] == 'NAModule2')) {
                        continue;
                    }
                    $val['measure_type'] = $data['measure_type'];
                    $val['measure_type_txt'] = $this->get_measurement_type($val['measure_type'], false, $module['module_type']);
                    $val['measure_value'] = $data['measure_value'];
                    $val['measure_timestamp'] = $data['measure_timestamp'];
                    $textual = (strpos($val['measure_type'], '_trend') > 0);
                    $style = 'style="color:#999"';
                    if (strpos($val['measure_type'], 'angle') > 0) {
                        $extra = 'fa-xlg';
                    }
                    else {
                        $extra = 'fa-lg';

                    }
                    $val['measure_value_txt'] = $this->output_value($val['measure_value'], $val['measure_type'], true, $textual, $module['module_type']);
                    $val['measure_value_icn'] = $this->output_iconic_value($val['measure_value'], $val['measure_type'], $module['module_type'], false, $style, $extra);
                    if (strpos($val['measure_type'], 'angle') > 0) {
                        $val['measure_value_txt'] = $this->get_angle_text($val['measure_value']);
                    }
                    if ($val['measure_type'] == 'co2') {
                        $val['measure_value_txt'] = 'CO₂&nbsp;/&nbsp;' . $val['measure_value_txt'];
                    }
                    if (($val['measure_type'] == 'co') || ($val['measure_type'] == 'co_distance')){
                        $val['measure_value_txt'] = 'CO&nbsp;/&nbsp;' . $val['measure_value_txt'];
                    }
                    if (($val['measure_type'] == 'o3') || ($val['measure_type'] == 'o3_distance')){
                        $val['measure_value_txt'] = 'O₃&nbsp;/&nbsp;' . $val['measure_value_txt'];
                    }
                    $unit = $this->output_unit($val['measure_type'], $module['module_type']);
                    if (array_key_exists('comp', $unit) && ($val['measure_type'] != 'humidity') && ($val['measure_type'] != 'health_idx')) {
                        $val['measure_value_txt'] .= ' ' . $unit['comp'];
                    }
                    $val['measure_value_txt'] = str_replace(' ', '&nbsp;', $val['measure_value_txt']);
                    $module['measure'][] = $val;
                }
            }
            if (count($module) > 0) {
                $result['module'][] = $module;
            }
        }
        return $result;
    }

    /**
     * Get sharing details for a station.
     *
     * @param array $data The station data.
     * @return array An array containing the effective details.
     * @since 3.0.0
     */
    protected function get_sharing_details($data) {
        $result = array();
        $t = ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank"' : '');
        if ($data['pws_sync']) {
            $result[] = '<a href="http://www.pwsweather.com/obs/' . $data['pws_user'] . '.html"' . $t . '>PWS Weather</a>';
        }
        if ($data['wow_sync']) {
            $result[] = '<a href="http://wow.metoffice.gov.uk/weather/view?siteID=' . $data['wow_user'] . '"' . $t . '>WOW Met Office</a>';
        }
        if ($data['wug_sync']) {
            $result[] = '<a href="https://www.wunderground.com/personal-weather-station/dashboard?ID=' . $data['wug_user'] . '&apiref=d97bd03904cd49c5"' . $t . '>Weather Underground</a>';
        }
        return $result;
    }

    /**
     * Get publishing details for a station.
     *
     * @param array $data The station data.
     * @return array An array containing the effective details.
     * @since 3.3.1
     */
    protected function get_publishing_details($data) {
        $result = array();
        $target = ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank"' : '');
        if ($data['txt_sync']) {
            $url = site_url('/get-weather/' . strtolower($data['station_id']) . '/stickertags/');
            $result[] = '<a href="' . $url . '"' . $target . '>Stickertags</a>';
        }
        if ($data['yow_sync']) {
            $url = site_url('/get-weather/' . strtolower($data['station_id']) . '/yowindow/');
            $result[] = '<a href="' . $url . '"' . $target . '>YoWindow</a>';
        }
        return $result;
    }


    /**
     * Get available historical operations.
     *
     * @param array $operations The permitted operations.
     * @param bool $full_mode Optional. Set full mode.
     * @return array An array containing all the available historical operations.
     * @since 3.4.0
     */
    private function get_all_historical_operations($operations, $full_mode=false) {
        $result = array();
        foreach ($operations as $set) {
            $result[$set] = $this->get_operation_name($set);
        }
        if (class_exists('\Collator')) {
            $collator = new \Collator(lws_get_display_locale());
            $collator->asort($result);
        }
        else {
            asort($result, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        }
        return $result;
    }

    /**
     * Get available operations for a type of measurement.
     *
     * @param string $measurement_type The type of measurement.
     * @param string $module_type Optional. The type of the module.
     * @param boolean $comparison Optional. The array must contain only the comparison set.
     * @param boolean $distribution Optional. The line must contain only the distribution set.
     * @return array An array containing the available operations.
     * @since 3.4.0
     */
    public function get_available_operations($measurement_type, $module_type='NAMain', $comparison=false, $distribution=false) {
        $result = array();
        if ((bool)get_option('live_weather_station_collect_history') && (bool)get_option('live_weather_station_build_history')) {
            $history = new History(LWS_PLUGIN_NAME, LWS_VERSION);
            $operations = $history->get_measurements_operations_type($measurement_type, $module_type, (bool)get_option('live_weather_station_full_history'), $comparison, $distribution);
            $set = array();
            foreach ($operations as $operation) {
                $set[] = $operation;
            }
            foreach ($this->get_all_historical_operations($set) as $key=>$operation) {
                $result[] = array($key, ucfirst($operation));
            }
        }
        return $result;
    }

    /**
     * Get available historical measurements.
     *
     * @param bool $current Optional. Get only true current measurements.
     * @param string $force_mode Optional. Forced mode when $current==false.
     * @param bool $show_always Optional. Always show measurement.
     * @return array An array containing the available historical standards/extended measurements.
     * @since 3.4.0
     */
    public function get_historical_measurements($current=true, $force_mode='standard', $show_always=false) {
        $history = new History(LWS_PLUGIN_NAME, LWS_VERSION);
        $names = array();
        $measurements = array();
        $result = array();
        foreach (array_merge($history->extended_measurements, $history->standard_measurements) as $measurement) {
            $names[$measurement] = $this->get_measurement_type($measurement);
            $measurements[$measurement]['standard'] = $this->get_all_historical_operations($history->get_measurements_operations_type($measurement, '', false));
            $measurements[$measurement]['extended'] = $this->get_all_historical_operations($history->get_measurements_operations_type($measurement, '', true));
        }
        if (class_exists('\Collator')) {
            $collator = new \Collator(lws_get_display_locale());
            $collator->asort($names);
        }
        else {
            asort($names, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        }
        foreach ($names as $id=>$name) {
            if (in_array($id, $history->standard_measurements)) {
                $type = 'standard';
                if ($current) {
                    $compiled = (bool)get_option('live_weather_station_collect_history');
                    $aggregated = (bool)get_option('live_weather_station_collect_history') &&
                        (bool)get_option('live_weather_station_build_history');
                }
                else {
                    $compiled = true;
                    $aggregated = true;
                }
            }
            else {
                $type = 'extended';
                if ($current) {
                    $compiled = (bool)get_option('live_weather_station_collect_history') &&
                        (bool)get_option('live_weather_station_full_history');
                    $aggregated = (bool)get_option('live_weather_station_collect_history') &&
                        (bool)get_option('live_weather_station_full_history') &&
                        (bool)get_option('live_weather_station_build_history');
                }
                else {
                    $compiled = $force_mode == 'extended';
                    $aggregated = $force_mode == 'extended';
                }
            }
            if (!$compiled && !$show_always) {
                continue;
            }
            $result[$id]['name'] = $name;
            $result[$id]['icon'] = $this->output_iconic_value(0, $id, false, false, 'style="color:#666666;"');
            $result[$id]['type'] = $type;
            $result[$id]['compiled'] = $compiled;
            $result[$id]['aggregated'] = $aggregated;
            $result[$id]['operations'] = $measurements[$id];
        }
        return $result;
    }

    /**
     * Get the historical capabilities.
     *
     * @return string $attributes The type of capabilities queryed by the shortcode.
     * @since 3.4.0
     */
    public function admin_historical_capabilities_shortcodes($attributes) {
        $result = '';
        $_attributes = shortcode_atts( array('item' => 'daily', 'mode' => 'current', 'style' => 'icon', 'column' => 3, 'border_color' => '#D9D9D9', 'background_color' => '#F4F4F4', 'font_color' => '#FFFFFF'), $attributes );
        $item = $_attributes['item'];
        $column = $_attributes['column'];
        $style = $_attributes['style'];
        $bcol = $_attributes['border_color'];
        $bgcol = $_attributes['background_color'];
        $fcol = $_attributes['font_color'];
        if($_attributes['mode'] == 'current') {
            $type = (bool)get_option('live_weather_station_full_history') ? 'extended' : 'standard';
            $current = true;
        }
        else {
            $type = strtolower($_attributes['mode']);
            $current = false;
        }
        $_measurements = $this->get_historical_measurements($current, $type, $style=='check' || ($item=='yearly' && $column==3));
        $measurements = array_values($_measurements);
        $cnt = count($measurements);
        $itr = 0;
        switch ($item) {
            case 'daily':
                $result .= '<div class="lws-histo-cap-table">';
                while ($itr < $cnt) {
                    if (($itr % $column) == 0) {
                        $result .= '<div class="lws-histo-cap-table-row">';
                    }
                    $result .= '<div class="lws-histo-cap-table-row-item">';
                    if ($style=='icon') {
                        $result .= '<span style="vertical-align:middle">' . $measurements[$itr]['icon'];
                    }
                    elseif ($style=='check') {
                        if ($measurements[$itr]['compiled']) {
                            $result .= '<span style="vertical-align:middle"><i style="color:limegreen;" class="fa fa-fw fa-check-circle" aria-hidden="true"></i>';
                        }
                        else {
                            $result .= '<span style="vertical-align:middle"><i style="color:red;"  class="fa fa-fw fa-times-circle" aria-hidden="true"></i>';
                        }
                    }
                    $result .= '&nbsp;' . $measurements[$itr]['name'].'</span>';
                    $result .= '</div>';
                    $itr += 1;
                    if (($itr % $column) == 0) {
                        $result .= '</div>';
                    }
                }
                while (($itr % $column) != 0) {
                    $result .= '<div class="lws-histo-cap-table-row-item">&nbsp;</div>';
                    $itr += 1;
                    if (($itr % $column) == 0) {
                        $result .= '</div>';
                    }
                }
                $result .= '</div>';
                break;
            case 'yearly':
                if ($column == 1) {
                    $result .= '<div class="lws-histo-cap-table">';
                    while ($itr < $cnt) {
                        if (($itr % $column) == 0) {
                            $result .= '<div class="lws-histo-cap-table-row">';
                        }
                        $result .= '<div class="lws-histo-cap-table-row-item">';
                        if ($style == 'icon') {
                            $result .= '<span style="vertical-align:middle">' . $measurements[$itr]['icon'];
                        } elseif ($style == 'check') {
                            if ($measurements[$itr]['aggregated']) {
                                $result .= '<span style="vertical-align:middle"><i style="color:limegreen;" class="fa fa-fw fa-check-circle" aria-hidden="true"></i>';
                            } else {
                                $result .= '<span style="vertical-align:middle"><i style="color:red;"  class="fa fa-fw fa-times-circle" aria-hidden="true"></i>';
                            }
                        }
                        $cap = '';
                        if ($measurements[$itr]['aggregated']) {
                            $cap = implode(', ', $measurements[$itr]['operations'][$type]);
                        }
                        if ($cap == '') {
                            $cap = '-';
                        }
                        $result .= '&nbsp;' . $measurements[$itr]['name'] . ' / ' . $cap . '.</span>';
                        $result .= '</div>';
                        $itr += 1;
                        if (($itr % $column) == 0) {
                            $result .= '</div>';
                        }
                    }
                    while (($itr % $column) != 0) {
                        $result .= '<div class="lws-histo-cap-table-row-item">&nbsp;</div>';
                        $itr += 1;
                        if (($itr % $column) == 0) {
                            $result .= '</div>';
                        }
                    }
                    $result .= '</div>';
                }
                if ($column == 3) {
                    $result .= '<div class="lws-histo-cap-table-3c">';
                    $result .= '<div class="lws-histo-cap-table-3c-row">';
                    $result .= '<div class="lws-histo-cap-table-3c-row-item" style="border-bottom: 1px solid ' . $bcol . ';">&nbsp;</div>';
                    $result .= '<div class="lws-histo-cap-table-3c-row-item-header" style="background-color: ' . $bcol . ';border-left: 1px solid ' . $bcol . ';">';
                    $result .= '<span style="color: ' . $fcol . ';">' . __('Standard', 'live-weather-station') . '</span>';
                    $result .= '</div>';
                    $result .= '<div class="lws-histo-cap-table-3c-row-item-header" style="background-color: ' . $bcol . ';border-left: 1px solid ' . $bcol . ';">';
                    $result .= '<span style="color: ' . $fcol . ';">' . __('Scientific', 'live-weather-station') . '</span>';
                    $result .= '</div>';
                    $result .= '</div>';
                    $itr = 0;
                    foreach ($measurements as $measurement) {
                        $s = '';
                        if ($itr & 1) {
                            $s = ' style="background-color:' . $bgcol .'"';
                        }
                        $result .= '<div class="lws-histo-cap-table-3c-row"' . $s . '>';
                        $result .= '<div class="lws-histo-cap-table-3c-row-item" style="border-left: 1px solid ' . $bcol . '; border-right: 1px solid ' . $bcol . ';">';
                        if ($style == 'icon') {
                            $result .= '<span style="vertical-align:middle">' . $measurement['icon'];
                        } elseif ($style == 'check') {
                            if ($measurement['aggregated']) {
                                $result .= '<span style="vertical-align:middle"><i style="color:limegreen;" class="fa fa-fw fa-check-circle" aria-hidden="true"></i>';
                            } else {
                                $result .= '<span style="vertical-align:middle"><i style="color:red;"  class="fa fa-fw fa-times-circle" aria-hidden="true"></i>';
                            }
                        }
                        $result .= '&nbsp;' . $measurement['name']. '</span>';
                        $result .= '</div>';
                        $result .= '<div class="lws-histo-cap-table-3c-row-item">';
                        $cap = '';
                        if ($measurements[$itr]['aggregated']) {
                            $cap = implode(', ', $measurement['operations']['standard']);
                        }
                        if ($cap == '') {
                            $cap = '-';
                        }
                        else {
                            $cap = ucfirst($cap) . '.';
                        }
                        $result .= $cap . '</div>';
                        $result .= '<div class="lws-histo-cap-table-3c-row-item" style="border-left: 1px solid ' . $bcol . '; border-right: 1px solid ' . $bcol . ';">';
                        $cap = '';
                        if ($measurements[$itr]['aggregated']) {
                            $cap = implode(', ', $measurement['operations']['extended']);
                        }
                        if ($cap == '') {
                            $cap = '-';
                        }
                        else {
                            $cap = ucfirst($cap) . '.';
                        }
                        $result .= $cap . '</div>';
                        $result .= '</div>';
                        $itr += 1;
                    }
                    $result .= '<div class="lws-histo-cap-table-3c-row" style="padding:0;height: 0;">';
                    $result .= '<div class="lws-histo-cap-table-3c-row-item" style="padding:0;height: 0;border-left: 1px solid ' . $bcol . '; border-right: 1px solid ' . $bcol . ';border-bottom: 1px solid ' . $bcol . ';"></div>';
                    $result .= '<div class="lws-histo-cap-table-3c-row-item" style="padding:0;height: 0;border-bottom: 1px solid ' . $bcol . ';"></div>';
                    $result .= '<div class="lws-histo-cap-table-3c-row-item" style="padding:0;height: 0;border-left: 1px solid ' . $bcol . '; border-right: 1px solid ' . $bcol . ';border-bottom: 1px solid ' . $bcol . ';"></div>';
                    $result .= '</div>';
                    $result .= '</div>';
                }
                break;
            default:
                $result = '<p>' . __('Malformed shortcode. Please verify it!', 'live-weather-station') . '</p>';
        }
        wp_enqueue_style('lws-weather-icons');
        wp_enqueue_style('lws-weather-icons-wind');
        wp_enqueue_style('lws-font-awesome');
        wp_enqueue_style('lws-table');
        return $result;
    }

    /**
     * Get the tranlations.
     *
     * @return string $attributes The type of analytics queryed by the shortcode.
     * @since 3.4.0
     */
    public function admin_translations_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('min' => 0, 'max' => 100, 'style' => 'multi-icon', 'column' => 2), $attributes );
        $min = (int)$_attributes['min'];
        $max = (int)$_attributes['max'];
        $column = $_attributes['column'];
        $style = $_attributes['style'];
        $langs = array_values(EnvManager::stat_translation_by_locale($min, $max));
        $cnt = count($langs);
        $itr = 0;
        $result = '<div class="lws-lang-cap-table">';
        while ($itr < $cnt) {
            if (($itr % $column) == 0) {
                $result .= '<div class="lws-lang-cap-table-row">';
            }
            $result .= '<div class="lws-lang-cap-table-row-item">';
            $link = 'https://translate.wordpress.org/locale/' . $langs[$itr]['locale_code'] . '/default/wp-plugins/live-weather-station';
            if ($style == 'multi-icon') {
                $shadow = 'box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.18), 0 6px 20px 0 rgba(0, 0, 0, 0.15);';
                $result .= '<a href="' . $link . '" style="' . $shadow . 'margin-right:16px; width:80px;" class="flag-icon ' . $langs[$itr]['svg-class'] . '"></a>';
                $result .= '<span>' . $langs[$itr]['name'].'<br/>';
                $result .= '<span style="color:#999">' . __('Translation:', 'live-weather-station') . ' ' . $langs[$itr]['translated'] . '%</span></span>';
            }
            elseif ($style == 'icon') {
                $result .= '<a href="' . $link . '" style="margin-right:10px;" class="flag-icon ' . $langs[$itr]['svg-class'] . '"></a>';
                $result .= '<span>' . $langs[$itr]['name'].'</span>';
            }
            else {
                $result .= '<span style="vertical-align:middle">' . $langs[$itr]['name'].'</span>';
            }
            $result .= '</div>';
            $itr += 1;
            if (($itr % $column) == 0) {
                $result .= '</div>';
            }
        }
        while (($itr % $column) != 0) {
            $result .= '<div class="lws-lang-cap-table-row-item">&nbsp;</div>';
            $itr += 1;
            if (($itr % $column) == 0) {
                $result .= '</div>';
            }
        }
        $result .= '</div>';
        wp_enqueue_style('lws-table');
        if ($style == 'icon' || $style == 'multi-icon') {
            if (EnvManager::is_home_server()) {
                wp_enqueue_style('flags', 'https://media.station.software/flags/css/flag-icon.min.css');
            }
            else {
                wp_enqueue_style('flags', 'https://weather.station.software/extra/flags/css/flag-icon.min.css', null, true);
            }
        }
        return $result;
    }

    /**
     * Get the plugin statistics.
     *
     * @@param array $attributes The type of statistics queryed by the shortcode.
     * @return integer The value of the statistics item.
     * @since 3.4.0
     */
    public function admin_statistics_shortcodes($attributes) {
        $_attributes = shortcode_atts(array('item' => 'downloaded'), $attributes);
        switch ($_attributes['item']) {
            case 'active_installs':
                $result = EnvManager::stat_active_installs();
                break;
            case 'downloaded':
                $result = EnvManager::stat_downloaded();
                break;
            case 'num_ratings':
                $result = EnvManager::stat_num_ratings();
                break;
            case 'rating':
                $result = EnvManager::stat_rating();
                break;
            default:
                $result = 0;
        }
        return $result;
    }

}
