<?php

namespace WeatherStation\Data\Arrays;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Type\Description as Type_Description;
use WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector as OWM_Base_Collector;
use WeatherStation\Data\History\Builder as History;
use WeatherStation\SDK\Generic\Plugin\Season\Calculator as Season;
use WeatherStation\System\Device\Manager as DeviceManager;
use WeatherStation\System\Environment\Manager as EnvManager;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\Data\DateTime\Handling as TimeHandling;

/**
 * Arrays generator for javascript conversion.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Generator {

    use Type_Description, TimeHandling;

    /**
     * Get device IDs formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available device IDs formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_device_id_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        return $result;
    }

    /**
     * Get device names formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available device names formats.
     *  @since    1.0.0
     * @access   private
     */
    private function get_td_device_name_format($sample) {
        $result = array();
        $result[0] = array (__('As-is', 'live-weather-station'), 'escaped', $sample[0]);
        return $result;
    }

    /**
     * Get module IDs formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available module IDs formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_module_id_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        return $result;
    }

    /**
     * Get module types formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available module types formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_module_type_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Formated value', 'live-weather-station'), 'type-formated', $sample[1]);
        return $result;
    }

    /**
     * Get module names formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available module names formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_module_name_format($sample) {
        $result = array();
        $result[0] = array (__('As-is', 'live-weather-station'), 'escaped', $sample[0]);
        return $result;
    }

    /**
     * Get times formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available times formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_time_format($sample) {
        $result = array();
        $result[0] = array (__('UTC timestamp', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Formated local date', 'live-weather-station'), 'local-date', $sample[1]);
        $result[2] = array (__('Formated local time', 'live-weather-station'), 'local-time', $sample[2]);
        $result[3] = array (__('Elapsed or remaining approximative time', 'live-weather-station'), 'local-diff', $sample[3]);
        return $result;
    }

    /**
     * Get picture formats for javascript.
     *
     * @param array $sample An array containing sample data.
     * @return array An array containing the available picture formats.
     * @since 3.6.0
     */
    private function get_td_picture_format($sample) {
        $result = array();
        $result[0] = array (__('Name', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('URL', 'live-weather-station'), 'medias:item_url', $sample[1]);
        return $result;
    }

    /**
     * Get video formats for javascript.
     *
     * @param array $sample An array containing sample data.
     * @return array An array containing the available video formats.
     * @since 3.6.0
     */
    private function get_td_video_format($sample) {
        $result = array();
        $result[0] = array (__('Name', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('URL', 'live-weather-station'), 'medias:item_url', $sample[1]);
        return $result;
    }

    /**
     * Get times formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available times formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_std_time_format($sample) {
        $result = array();
        $result[0] = array (__('Unix timestamp', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Formated local date', 'live-weather-station'), 'local-date', $sample[1]);
        $result[2] = array (__('Formated local time', 'live-weather-station'), 'local-time', $sample[2]);
        $result[3] = array (__('Elapsed or remaining approximative time', 'live-weather-station'), 'local-diff', $sample[3]);
        return $result;
    }

    /**
     * Get measure types formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available measure types formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_measure_type_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Formated value', 'live-weather-station'), 'type-formated', $sample[1]);
        $result[2] = array (__('Unit symbol or abbreviation', 'live-weather-station'), 'type-unit', $sample[2]);
        $result[3] = array (__('Unit with complement (if any)', 'live-weather-station'), 'type-unit-full', $sample[3]);
        $result[4] = array (__('Unit name', 'live-weather-station'), 'type-unit-long', $sample[4]);
        $result[5] = array (__('Raw dimension', 'live-weather-station'), 'type-raw-dimension', $sample[5]);
        $result[6] = array (__('Formated dimension', 'live-weather-station'), 'type-formated-dimension', $sample[6]);
        return $result;
    }

    /**
     * Get values formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Converted value', 'live-weather-station'), 'computed', $sample[1]);
        $result[2] = array (__('Converted value with unit', 'live-weather-station'), 'computed-unit', $sample[2]);
        return $result;
    }

    /**
     * Get simple values formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats.
     * @since    1.1.0
     * @access   private
     */
    private function get_td_simple_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Converted value', 'live-weather-station'), 'computed', $sample[1]);
        return $result;
    }

    /**
     * Get values battery and signal formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats for battery and signal.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_special_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Converted value', 'live-weather-station'), 'computed', $sample[1]);
        $result[2] = array (__('Converted value with unit', 'live-weather-station'), 'computed-unit', $sample[2]);
        $result[3] = array (__('Plain text', 'live-weather-station'), 'plain-text', $sample[3]);
        return $result;
    }

    /**
     * Get values duration formats for javascript.
     *
     * @param array $sample An array containing sample data.
     * @return array An array containing the available values formats for duration.
     * @since 3.8.0
     */
    private function get_td_duration_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Converted value', 'live-weather-station'), 'computed', $sample[1]);
        $result[2] = array (__('Converted value with unit', 'live-weather-station'), 'computed-unit', $sample[2]);
        $result[3] = array (__('Plain text', 'live-weather-station'), 'plain-text', $sample[3]);
        if (LWS_PREVIEW) {
            $result[4] = array (lws__('HH:MM format', 'live-weather-station'), 'hh-mm', $sample[4]);
            $result[5] = array (lws__('HH:MM:SS format', 'live-weather-station'), 'hh-mm-ss', $sample[5]);
        }
        return $result;
    }

    /**
     * Get wid angle formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats for wind angle.
     * @since    2.0.0
     * @access   private
     */
    private function get_td_wind_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Converted value', 'live-weather-station'), 'computed', $sample[1]);
        $result[2] = array (__('Converted value with unit', 'live-weather-station'), 'computed-unit', $sample[2]);
        $result[3] = array (__('Abbreviation', 'live-weather-station'), 'short-text', $sample[3]);
        $result[4] = array (__('Plain text', 'live-weather-station'), 'plain-text', $sample[4]);
        return $result;
    }

    /**
     * Get aggregated values formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats for aggregated.
     * @since    1.0.0
     * @access   private
     */
    private function get_td_aggregated_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        return $result;
    }

    /**
     * Get firmware values formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats for firmware.
     * @since    1.1.0
     * @access   private
     */
    private function get_td_firmware_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        return $result;
    }

    /**
     * Get values coordinates formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available values formats for coordinates.
     * @since    1.1.0
     * @access   private
     */
    private function get_td_coordinate_value_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Geodetic system WGS 84', 'live-weather-station'), 'computed-wgs84', $sample[1]);
        $result[2] = array (__('Geodetic system WGS 84 with unit', 'live-weather-station'), 'computed-wgs84-unit', $sample[2]);
        $result[3] = array (__('DMS', 'live-weather-station'), 'computed-dms', $sample[3]);
        $result[4] = array (__('DMS starting with cardinal', 'live-weather-station'), 'computed-dms-cardinal-start', $sample[4]);
        $result[5] = array (__('DMS ending with cardinal', 'live-weather-station'), 'computed-dms-cardinal-end', $sample[5]);
        return $result;
    }

    /**
     * Get trend formats for javascript.
     *
     * @param   array   $sample An array containing sample data.
     * @return  array   An array containing the available trends formats.
     * @since    1.1.0
     * @access   private
     */
    private function get_td_trend_format($sample) {
        $result = array();
        $result[0] = array (__('Raw value', 'live-weather-station'), 'raw', $sample[0]);
        $result[1] = array (__('Plain text', 'live-weather-station'), 'plain-text', $sample[1]);
        return $result;
    }

    /**
     * Get iconized measure.
     *
     * @param array $measure An array containing sample data.
     * @param string $type The type of the measure.
     * @param boolean $is_day Optional. Is it day or night ?
     * @param boolean $mix_day Optional. Are we at less than 4 hours of a sun change ?
     * @return array An array containing the iconized measure.
     * @since 3.8.0
     */
    private function iconize_measure_array($measure, $type, $is_day=null, $mix_day=null) {
        $measure[0] = lws__('Static measurement icon', 'live-weather-station');
        $measure[1] = 'static';
        $icon = $this->output_iconic_value($measure[2][0][2], $type, null, false, null, '', $is_day);
        $pref = '<span class="lws-icon-value" style="font-size:26px;">';
        $suf = '</span>';
        $t = array();
        $t[] = array(__('None', 'live-weather-station'), 'none', $pref . $icon . $suf);
        foreach ($measure[2] as $line) {
            $t[] = array($line[0], $line[1], $pref . $icon . '<span class="lws-text" style="vertical-align: baseline;"> &nbsp;' . $line[2] . '</span>' . $suf);
        }
        $measure[2] = $t;
        return $measure;
    }

    /**
     * Get subiconized measure.
     *
     * @param array $measure An array containing sample data.
     * @param string $type The type of the measure.
     * @param boolean $is_day Optional. Is it day or night ?
     * @param boolean $mix_day Optional. Are we at less than 4 hours of a sun change ?
     * @return array An array containing the iconized measure.
     * @since 3.8.0
     */
    private function subiconize_measure_array($measure, $type, $is_day=null, $mix_day=null) {
        $result = array();
        if (in_array($type, $this->dynamic_icons)) {
            $measure[0] = lws__('Dynamic measurement icon', 'live-weather-station');
            $measure[1] = 'dynamic';
            $icon = $this->output_iconic_value($measure[2][0][2], $type, null, true, null, '', $is_day, $mix_day);
            $pref = '<span class="lws-icon-value" style="font-size:26px;">';
            $suf = '</span>';
            $t = array();
            $t[] = array(__('None', 'live-weather-station'), 'none', $pref . $icon . $suf);
            foreach ($measure[2] as $line) {
                $t[] = array($line[0], $line[1], $pref . $icon . '<span class="lws-text" style="vertical-align: baseline;"> &nbsp;' . $line[2] . '</span>' . $suf);
            }
            $measure[2] = $t;
            $result = $measure;
        }
        return $result;
    }

    /**
     * Get measure's datas array.
     *
     * @param array $ref An array containing value of reference.
     * @param array $data An array containing measures.
     * @param string $mtype The type of the measurement.
     * @param boolean $icon Optional. The array is for icons only.
     * @param boolean $climat Optional. The array is for climate records only.
     * @return array An array containing the measure.
     * @since 3.0.0
     */
    private function get_measure_array($ref, $data, $mtype, $icon=false, $climat=false) {
        $mvalue = 0;
        $ts = 0;
        $found = false;
        $is_day = $this->check_day($ref['loc_latitude'], $ref['loc_longitude'], $ref['loc_timezone']);
        $mix_day = $this->check_mixday($ref['loc_latitude'], $ref['loc_longitude'], $ref['loc_timezone']);
        foreach($data['measure'] as $measure) {
            if ($measure['measure_type'] == $mtype) {
                $mvalue = $measure['measure_value'];
                $ts = $measure['measure_timestamp'];
                $found = true;
            }
        }
        if (!$found) {
            return array();
        }
        $result = array();
        if (!$icon) {
            $result[] = array(__('Station ID', 'live-weather-station'), 'device_id', $this->get_td_device_id_format(array($ref['device_id'])));
            $result[] = array(__('Station name', 'live-weather-station'), 'device_name', $this->get_td_device_name_format(array($ref['device_name'])));
            $result[] = array(__('Station model', 'live-weather-station'), 'device_model', $this->get_td_device_name_format(array($ref['device_model'])));
            $result[] = array(__('Module ID', 'live-weather-station'), 'module_id', $this->get_td_module_id_format(array($ref['module_id'])));
            $result[] = array(__('Module type', 'live-weather-station'), 'module_type', $this->get_td_module_type_format(array($ref['module_type'],$this->get_module_type($ref['module_type']))));
            $result[] = array(__('Module name', 'live-weather-station'), 'module_name', $this->get_td_module_name_format(array($ref['module_name'])));
            $result[] = array(__('Measurement timestamp', 'live-weather-station'), 'measure_timestamp', $this->get_td_time_format(array($ts, $this->get_date_from_mysql_utc($ts, $ref['loc_timezone']), $this->get_time_from_mysql_utc($ts, $ref['loc_timezone']), $this->get_time_diff_from_mysql_utc($ts))));
            $unit = $this->output_unit($mtype, false, $ref['module_type']);
            $result[] = array(__('Measurement type', 'live-weather-station'), 'measure_type', $this->get_td_measure_type_format(array($mtype,$this->get_measurement_type($mtype, false, $ref['module_type']),$unit['unit'],$unit['full'],$unit['long'],$unit['dimension'], $this->get_dimension_name($unit['dimension']))));
        }
        switch ($mtype) {
            case 'battery':
            case 'signal':
            case 'health_idx':
            case 'cbi':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_special_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']), $this->output_value($mvalue, $mtype, false, true, $ref['module_type']))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'sunshine':
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
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_duration_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']), $this->output_value($mvalue, $mtype, false, true, $ref['module_type']), $this->output_value($mvalue, $mtype, false, true, $ref['module_type'], '', 'hh-mm'), $this->output_value($mvalue, $mtype, false, true, $ref['module_type'], '', 'hh-mm-ss'))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'co2_trend':
            case 'humidity_trend':
            case 'absolute_humidity_trend':
            case 'noise_trend':
            case 'pressure_trend':
            case 'pressure_sl_trend':
            case 'temperature_trend':
            case 'irradiance_trend':
            case 'uv_index_trend':
            case 'illuminance_trend':
            case 'soil_temperature_trend':
            case 'moisture_content_trend':
            case 'moisture_tension_trend':
            case 'windstrength_day_trend':
            case 'guststrength_day_trend':
            case 'cloudiness_trend':
            case 'visibility_trend':
            case 'moon_age':
            case 'moon_phase':
            case 'loc_timezone':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_trend_format(array($mvalue, $this->output_value($mvalue, $mtype, false, true, $ref['module_type']))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'aggregated':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_aggregated_value_format(array($mvalue, $this->output_value($mvalue, $mtype))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                    break;
            case 'outdoor':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_aggregated_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'firmware':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_firmware_value_format(array($mvalue)));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'loc_latitude':
            case 'loc_longitude':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_coordinate_value_format(array($mvalue, $this->output_coordinate($mvalue, $mtype, 1), $this->output_coordinate($mvalue, $mtype, 2), $this->output_coordinate($mvalue, $mtype, 3), $this->output_coordinate($mvalue, $mtype, 4), $this->output_coordinate($mvalue, $mtype, 5))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'heat_index':
            case 'humidex':
            case 'wind_chill':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_simple_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
            case 'winddirection':
            case 'gustdirection':
            case 'winddirection_max':
            case 'winddirection_day_max':
            case 'winddirection_day_min':
            case 'gustdirection_day_max':
            case 'gustdirection_day_min':
            case 'winddirection_hour_max':
            case 'strike_bearing':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_wind_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']), $this->get_angle_text($mvalue), $this->get_angle_full_text($mvalue))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
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
            case 'moonrise':
            case 'moonset':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_std_time_format(array($mvalue, $this->get_date_from_utc($mvalue, $ref['loc_timezone']), $this->get_time_from_utc($mvalue, $ref['loc_timezone']), $this->get_time_diff_from_utc($mvalue))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'last_refresh':
            case 'last_seen':
            case 'first_setup':
            case 'last_upgrade':
            case 'last_setup':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_time_format(array($mvalue, $this->get_date_from_mysql_utc($mvalue, $ref['loc_timezone']), $this->get_time_from_mysql_utc($mvalue, $ref['loc_timezone']), $this->get_time_diff_from_mysql_utc($mvalue))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'picture':
                $picturl = self::get_picture($ref['device_id']);
                if (isset($picturl) and !empty($picturl)) {
                    $picturl = $picturl['item_url'];
                }
                else {
                    $picturl = '';
                }
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_picture_format(array($mvalue, $picturl)));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'video':
            case 'video_imperial':
            case 'video_metric':
                $type = str_replace('_', '', str_replace('video', '', $mtype));
                if ($type === '') {
                    $type = 'none';
                }
                $vidurl = self::get_video($ref['device_id'], $type);
                if (isset($vidurl) and !empty($vidurl)) {
                    $vidurl = $vidurl['item_url'];
                }
                else {
                    $vidurl = '';
                }
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_video_format(array($mvalue, $vidurl)));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            case 'zcast_live':
            case 'zcast_best':
            case 'weather':
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_trend_format(array($mvalue, ucfirst($this->output_value($mvalue, $mtype, false, true, $ref['module_type'])))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
                break;
            default:
                $line = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']))));
                if ($icon) {
                    $result[] = $this->iconize_measure_array($line, $mtype, $is_day, $mix_day);
                    $sub = $this->subiconize_measure_array($line, $mtype, $is_day, $mix_day);
                    if (!empty($sub)) {
                        $result[] = $sub;
                    }
                }
                else {
                    $result[] = $line;
                }
        }
        return $result;
    }

    /**
     * Get one line array for a module.
     *
     * @param array $ref An array containing value of reference.
     * @param array $data An array containing measures.
     * @param boolean $reduced The array is reduced. i.e. contains only modules and measures.
     * @param string $module_type The type of the module.
     * @param string $measurement_type The type of the measurement.
     * @param boolean $comparison Optional. The line must contain only the comparison set.
     * @param boolean $distribution Optional. The line must contain only the distribution set.
     * @param boolean $current Optional. The line is for current records only.
     * @param boolean $video Optional. The line is for video records only.
     * @param boolean $picture Optional. The line is for picture records only.
     * @param boolean $icon Optional. The array is for icons only.
     * @param boolean $climat Optional. The array is for climate records only.
     * @return array|null An array containing a single module measure line.
     * @since 3.4.0
     */
    private function get_line_array($ref, $data, $reduced, $module_type, $measurement_type, $comparison=false, $distribution=false, $current=false, $video=false, $picture=false, $icon=false, $climat=false) {
        $unit = $this->output_unit($measurement_type, $module_type);
        $available_operations = $this->get_available_operations($measurement_type, $module_type, $comparison, $distribution);
        if ($video) {
            if (strpos($measurement_type, 'video') !== false) {
                return array($this->get_measurement_type($measurement_type, false, $module_type), $measurement_type, ($reduced ? array() : $this->get_measure_array($ref, $data, $measurement_type, $icon, $climat)), $unit['dimension'], $available_operations);
            }
            else {
                return null;
            }
        }
        elseif ($picture) {
            if (strpos($measurement_type, 'picture') !== false) {
                return array($this->get_measurement_type($measurement_type, false, $module_type), $measurement_type, ($reduced ? array() : $this->get_measure_array($ref, $data, $measurement_type, $icon, $climat)), $unit['dimension'], $available_operations);
            }
            else {
                return null;
            }
        }
        else {
            if (count($available_operations) > 0 || $current) {
                return array($this->get_measurement_type($measurement_type, false, $module_type), $measurement_type, ($reduced ? array() : $this->get_measure_array($ref, $data, $measurement_type, $icon, $climat)), $unit['dimension'], $available_operations);
            }
            else {
                return null;
            }
        }
    }


    /**
     * Get module's datas array.
     *
     * @param array $ref An array containing value of reference.
     * @param array $data An array containing measures.
     * @param boolean $full The array must contain all measured data types including operational datas.
     * @param boolean $aggregated The array must contain aggregated data types.
     * @param boolean $reduced The array is reduced. i.e. contains only modules and measures.
     * @param boolean $computed The array must contain computed data types.
     * @param boolean $mono The array must contain min/max.
     * @param boolean $daily Optional. The array must contain daily data types only.
     * @param boolean $historical Optional. The array must contain historical data types only.
     * @param boolean $noned Optional. The array must contain, for each module, a "none" measurement type.
     * @param boolean $comparison Optional. The array must contain, for each module, only the comparison set (if $historical is true).
     * @param boolean $distribution Optional. The array must contain, for each module, only the distribution set (if $historical is true).
     * @param boolean $current Optional. The array is for current records only.
     * @param boolean $video Optional. The array is for video records only.
     * @param boolean $picture Optional. The array is for picture records only.
     * @param boolean $icon Optional. The array is for icons only.
     * @param boolean $climat Optional. The array is for climate records only.
     * @return array An array containing the module measure lines.
     * @since 3.0.0
     */
    private function get_module_array($ref, $data, $full=false, $aggregated=false, $reduced=false, $computed=false, $mono=false, $daily=false, $historical=false, $noned=false, $comparison=false, $distribution=false, $current=false, $video=false, $picture=false, $icon=false, $climat=false) {
        $result = array();
        $netatmo = OWM_Base_Collector::is_netatmo_station($ref['device_id']);
        $wug = OWM_Base_Collector::is_wug_station($ref['device_id']);
        $raw = OWM_Base_Collector::is_raw_station($ref['device_id']);
        $real = OWM_Base_Collector::is_real_station($ref['device_id']);
        $txt = OWM_Base_Collector::is_txt_station($ref['device_id']);
        $wflw = OWM_Base_Collector::is_wflw_station($ref['device_id']);
        $piou = OWM_Base_Collector::is_piou_station($ref['device_id']);
        $bsky = OWM_Base_Collector::is_bsky_station($ref['device_id']);
        $ambt = OWM_Base_Collector::is_ambt_station($ref['device_id']);
        $wlink = OWM_Base_Collector::is_wlink_station($ref['device_id']);
        $bstorm = false;
        if ($bsky) {
            $bstorm = (strpos($ref['device_model'], '+Storm') !== false);
            $bsky = false;
        }
        if ($noned) {
            $result[] = array('- ' . __('None', 'live-weather-station') . ' -', 'none', array(), 'none', array(array('none', '- ' . __('None', 'live-weather-station') . ' -')));
        }
        switch (strtolower($ref['module_type'])) {
            case 'namain':
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'first_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_upgrade', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($wug || $wflw)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($raw || $real)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $wlink) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $piou) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'loc_timezone', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'loc_altitude', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'loc_latitude', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'loc_longitude', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'co2', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'noise', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_sl', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'health_idx', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($wug || $real || $raw || $txt || $wflw || $wlink || $bsky || $bstorm || $ambt) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_sl', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($netatmo || $real || $raw || $txt)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_trend', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_sl_trend', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if (($full || $mono) && ($real || $raw)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_min', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_sl_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_sl_min', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if (($full || $mono) && $netatmo) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_min', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_trend', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule1': // Outdoor module
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($wug || $real || $raw || $txt || $wflw || $wlink)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if (($full || $mono) && $raw) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity_min', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if (($full || $mono) && !$wug && !$wflw && !$bstorm && !$bsky && !$ambt) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_min', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_trend', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule2': // Wind gauge
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($wug || $real || $raw || $txt || $wflw || $wlink || $piou)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if (!$bsky) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'windangle', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'winddirection', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'windstrength', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'gustangle', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'gustdirection', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'guststrength', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo) {
                    //$result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'windangle_hour_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    //$result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'winddirection_hour_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    //$result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'windstrength_hour_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'windangle_day_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'winddirection_day_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'windstrength_day_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule3': // Rain gauge
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($wug || $real || $raw || $txt || $wflw || $wlink)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo || $raw || $real || $wflw || $bstorm ||$ambt || $wlink) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if (!$raw && !$bsky && !$bstorm && !$ambt) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain_hour_aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain_day_aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if ($raw || $real || $wflw) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain_yesterday_aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($raw || $real || $ambt || $wlink) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain_month_aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain_year_aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule4': // Additional indoor module
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'battery', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'signal', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_setup', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && ($wug || $real || $raw)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'co2', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'health_idx', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if (($full || $mono) && ($netatmo || $raw)) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_max', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_min', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && $netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_trend', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule5': // Solar module
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full && !$bstorm && !$bsky) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'uv_index', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if (!$bstorm && !$bsky) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'irradiance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($wflw || $bstorm || $bsky) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'illuminance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($real) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunshine', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule6': // Soil module
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($raw) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'soil_temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'leaf_wetness', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moisture_tension', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($real) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'evapotranspiration', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodule7': // Thunderstorm module
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($raw) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'strike_count', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'strike_instant', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'strike_distance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'strike_bearing', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($wflw) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'strike_count', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'strike_distance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;

            case 'namodule9': // Additional indoor module
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_seen', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'health_idx', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                break;
            case 'namodulep': // Virtual module for picture
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full || $picture) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'picture', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'namodulev': // Virtual module for video
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full || $video) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'video_metric', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'video_imperial', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'aggregated': // All modules aggregated in one
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'outdoor', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'psychrometric', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'co2', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($netatmo || $raw || $real) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'health_idx', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'nacomputed': // Virtual module for computed values
                if ($computed) {
                    if ($aggregated) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    }
                    if ($full) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    }
                    if ($full) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature_ref', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity_ref', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_ref', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'wind_ref', $comparison, $distribution, $current, $video, $picture, $icon, $climat);

                    }
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dew_point', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'frost_point', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'heat_index', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'steadman', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'summer_simmer', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidex', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'wind_chill', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'cloud_ceiling', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'cbi', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'air_density', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'wet_bulb', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'delta_t', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'wood_emc', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'specific_enthalpy', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'equivalent_temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'potential_temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'equivalent_potential_temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'partial_vapor_pressure', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'saturation_vapor_pressure', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'partial_absolute_humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'saturation_absolute_humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    if (LWS_PREVIEW) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'alt_pressure', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'alt_density', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        if ($full) {
                            $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'zcast_live', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                            $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'zcast_best', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                        }
                    }
                }
                break;
            case 'nacurrent': // Virtual module for current values from OpenWeatherMap.org
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if (LWS_PREVIEW) {
                    if ($full) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'weather', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    }
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'pressure_sl', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'humidity', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'temperature', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if (!$historical) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'rain', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'visibility', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'snow', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'windangle', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'winddirection', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'windstrength', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'cloudiness', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                break;
           case 'naephemer': // Virtual module for ephemeris
                if ($computed) {
                    if ($full) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    }
                    if ($full) {
                        $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    }
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunrise', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunrise_c', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunrise_n', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunrise_a', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dawn_length_c', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dawn_length_n', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dawn_length_a', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunset', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunset_c', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunset_n', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sunset_a', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dusk_length_c', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dusk_length_n', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'dusk_length_a', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'day_length', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'day_length_c', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'day_length_n', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'day_length_a', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sun_distance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'sun_diameter', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moonrise', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moonset', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moon_phase', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moon_age', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moon_illumination', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moon_distance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'],'moon_diameter', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
            case 'napollution': // Virtual module for pollution
                if ($aggregated) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'aggregated', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'last_refresh', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'firmware', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'o3', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'o3_distance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'co', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if ($full) {
                    $result[] = $this->get_line_array($ref, $data, $reduced, $ref['module_type'], 'co_distance', $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
                break;
        }
        if ($daily || $historical) {
            $temp = array();
            $h = new History(LWS_PLUGIN_NAME, LWS_VERSION);
            foreach ($result as $item) {
                if ($h->is_allowed_measurement($item[1]) || $item[1] == 'none') {
                    $temp[] = $item;
                }
            }
            $result = $temp;
        }
        $result = array_values(array_filter($result));
        if (!empty($result)) {
            return array ($ref['module_name'], $ref['module_id'], $result);
        }
        else {
            return array();
        }
    }

    /**
     * Get station's datas array.
     *
     * @param integer $guid The station GUID.
     * @param boolean $full Optional. The array must contain all measured data types including operational datas.
     * @param boolean $aggregated Optional. The array must contain aggregated data types.
     * @param boolean $reduced Optional. The array is reduced. i.e. contains only modules and measures.
     * @param boolean $computed Optional. The array must contain computed data types.
     * @param boolean $mono Optional. The array must contain min/max.
     * @param boolean $daily Optional. The array must contain daily data types only.
     * @param boolean $historical Optional. The array must contain historical data types only.
     * @param boolean $noned Optional. The array must contain, for each module, a "none" measurement type.
     * @param boolean $comparison Optional. The array must contain, for each module, only the comparison set (if $historical is true).
     * @param boolean $distribution Optional. The array must contain, for each module, only the distribution set (if $historical is true).
     * @param boolean $current Optional. The array is for current records only.
     * @param boolean $video Optional. The array is for video records only.
     * @param boolean $picture Optional. The array is for picture records only.
     * @param boolean $icon Optional. The array is for icons only.
     * @param boolean $climat Optional. The array is for climate records only.
     * @return array An array containing the available station's datas ready to convert to a JS array.
     * @since 3.0.0
     */
    protected function get_station_array($guid, $full=true, $aggregated=false, $reduced=false, $computed=false, $mono=false, $daily=false, $historical=false, $noned=false, $comparison=false, $distribution=false, $current=false, $video=false, $picture=false, $icon=false, $climat=false) {
        $data = $this->get_all_formated_datas($guid, false, true);
        $result = array();
        $modules = array();
        if (count($data) > 0) {
            $result[] = $data['station']['station_name'];
            $result[] = $data['station']['station_id'];
            $netatmo = OWM_Base_Collector::is_netatmo_station($data['station']['station_id']);
            $wug = OWM_Base_Collector::is_wug_station($data['station']['station_id']);
            $raw = OWM_Base_Collector::is_raw_station($data['station']['station_id']);
            $real = OWM_Base_Collector::is_real_station($data['station']['station_id']);
            $txt = OWM_Base_Collector::is_txt_station($data['station']['station_id']);
            $wflw = OWM_Base_Collector::is_wflw_station($data['station']['station_id']);
            $piou = OWM_Base_Collector::is_piou_station($data['station']['station_id']);
            $bsky = OWM_Base_Collector::is_bsky_station($data['station']['station_id']);
            $ambt = OWM_Base_Collector::is_ambt_station($data['station']['station_id']);
            $wlink = OWM_Base_Collector::is_ambt_station($data['station']['station_id']);
            $mainbase = array();
            if (count($data['module']) > 0) {
                foreach ($data['module'] as $module) {
                    if (strtolower($module['module_type']) == 'namain') {
                        $mainbase = $module;
                    }
                }
            }
            if (!(count($mainbase) > 0)) {
                Logger::debug('Backend', null, $data['station']['station_id'], $data['station']['station_name'], null, null, null, 'Unable to find a main base for this station.');
                return array();
            }
            if ($noned) {
                $ref = array();
                $ref['device_id'] = $data['station']['station_id'];
                $ref['device_name'] = $data['station']['station_name'];
                $ref['device_model'] = $data['station']['station_model'];
                $ref['module_id'] = 'none';
                $ref['module_type'] = 'none';
                $ref['module_name'] = '- ' . __('None', 'live-weather-station') . ' -';
                $ref['loc_latitude'] = $data['station']['loc_latitude'];
                $ref['loc_longitude'] = $data['station']['loc_longitude'];
                $ref['loc_timezone'] = $data['station']['loc_timezone'];
                $modules[] = array ('- ' . __('None', 'live-weather-station') . ' -', 'none', array(array('- ' . __('None', 'live-weather-station') . ' -', 'none', array(), 'none' , array(array('none', '- ' . __('None', 'live-weather-station') . ' -')))));
            }
            if ($aggregated  && ($netatmo || $wug || $raw || $real || $txt || $wflw || $wlink || $piou || $bsky || $ambt)) {
                $ref = array();
                $ref['device_id'] = $data['station']['station_id'];
                $ref['device_name'] = $data['station']['station_name'];
                $ref['device_model'] = $data['station']['station_model'];
                $ref['module_id'] = 'aggregated';
                $ref['module_type'] = 'aggregated';
                $ref['module_name'] = __('[all modules]', 'live-weather-station');
                $ref['loc_latitude'] = $data['station']['loc_latitude'];
                $ref['loc_longitude'] = $data['station']['loc_longitude'];
                $ref['loc_timezone'] = $data['station']['loc_timezone'];
                $m = $this->get_module_array($ref, $mainbase, $full, $aggregated, $reduced, $computed, $mono, $daily, $historical, $noned, $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                if (!empty($m)){
                    $modules[] = $m;
                }
            }
            foreach ($data['module'] as $module) {
                $ref = array();
                $ref['device_id'] = $data['station']['station_id'];
                $ref['device_name'] = $data['station']['station_name'];
                $ref['device_model'] = $data['station']['station_model'];
                $ref['module_id'] = $module['module_id'];
                $ref['module_type'] = $module['module_type'];
                $ref['module_name'] = DeviceManager::get_module_name($ref['device_id'], $ref['module_id']);
                $ref['loc_latitude'] = $data['station']['loc_latitude'];
                $ref['loc_longitude'] = $data['station']['loc_longitude'];
                $ref['loc_timezone'] = $data['station']['loc_timezone'];
                if (($module['module_type'] == 'NAMain') && ($data['station']['station_type'] == 1) && !$full) {
                    continue;
                }
                if (($module['module_type'] == 'NAEphemer') && !$full) {
                    continue;
                }
                if (($module['module_type'] == 'NAComputed') && !$computed) {
                    continue;
                }
                if (($module['module_type'] == 'NAPollution') && ($daily || $historical)) {
                    continue;
                }
                if (DeviceManager::is_visible($ref['device_id'], $ref['module_id'])) {
                    $m = $this->get_module_array($ref, $module, $full, $aggregated, $reduced, $computed, $mono, $daily, $historical, $noned, $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                    if (!empty($m)){
                        $modules[] = $m;
                    }
                }
            }
        }
        $result[] = $modules;
        return $result;
    }

    /**
     * Get all station's datas array.
     *
     * @param boolean $full Optional. The array must contain all measured data types including operational datas.
     * @param boolean $aggregated Optional. The array must contain aggregated data types.
     * @param boolean $reduced Optional. The array is reduced. i.e. contains only modules and measures.
     * @param boolean $computed Optional. The array must contain computed data types.
     * @param boolean $mono Optional. The array must contain min/max.
     * @param boolean $daily Optional. The array must contain daily data types only.
     * @param boolean $historical Optional. The array must contain historical data types only.
     * @param boolean $noned Optional. The array must contain, for each module, a "none" measurement type.
     * @param array $guids Optional. An array of guids to get. Get data for all guids if not provided.
     * @param boolean $comparison Optional. The array must contain, for each module, only the comparison set (if $historical is true).
     * @param boolean $distribution Optional. The array must contain, for each module, only the distribution set (if $historical is true).
     * @param boolean $current Optional. The array is for current records only.
     * @param boolean $video Optional. The array is for video records only.
     * @param boolean $picture Optional. The array is for picture records only.
     * @param boolean $icon Optional. The array is for icons only.
     * @param boolean $climat Optional. The array is for climate records only.
     * @return array An array containing the available station's datas ready to convert to a JS array.
     * @since 3.0.0
     */
    protected function get_all_stations_array($full=true, $aggregated=false, $reduced=false, $computed=false, $mono=false, $daily=false, $historical=false, $noned=false, $guids=array(), $comparison=false, $distribution=false, $current=false, $video=false, $picture=false, $icon=false, $climat=false) {
        $result = array();
        $stations = $this->get_stations_list();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                if (!empty($guids)) {
                    $todo = in_array($station['guid'], $guids);
                }
                else {
                    $todo = true;
                }
                if ($todo && ($station['comp_bas'] + $station['comp_ext'] + $station['comp_int'] + $station['comp_xtd'] + $station['comp_vrt']) > 0) {
                    $result[$station['guid']] = $this->get_station_array($station['guid'], $full, $aggregated, $reduced, $computed, $mono, $daily, $historical, $noned, $comparison, $distribution, $current, $video, $picture, $icon, $climat);
                }
            }
        }
        return $result;
    }

    /**
     * Get the speed options for the lcd panel.
     *
     * @return  array   An array containing the lcd speed options ready to convert to a JS array.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_lcd_speed_js_array() {
        $result = array();
        $result[] = array('2000',  __('Fast', 'live-weather-station'));
        $result[] = array('4000',  __('Medium', 'live-weather-station'));
        $result[] = array('8000',  __('Slow', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the designs options for the lcd panel.
     *
     * @return  array   An array containing the lcd designs options ready to convert to a JS array.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_lcd_design_js_array() {
        $result = array();
        $result[] = array('standard',  __('Standard - Without backlight', 'live-weather-station'));
        $result[] = array('lightgreen-black',  __('Standard - With backlight', 'live-weather-station'));
        $result[] = array('red',  __('Translucent - Red', 'live-weather-station'));
        $result[] = array('orange',  __('Translucent - Orange', 'live-weather-station'));
        $result[] = array('lcd-beige',  __('Translucent - Beige', 'live-weather-station'));
        $result[] = array('yellow-black',  __('Translucent - Yellow', 'live-weather-station'));
        $result[] = array('yellow',  __('Translucent - Yellow green', 'live-weather-station'));
        $result[] = array('lightgreen',  __('Translucent - Light green', 'live-weather-station'));
        $result[] = array('standard-green',  __('Translucent - Standard green', 'live-weather-station'));
        $result[] = array('blue',  __('Translucent - Light blue', 'live-weather-station'));
        $result[] = array('blue-blue',  __('Translucent - Blue', 'live-weather-station'));
        $result[] = array('lightblue',  __('Translucent - Light purple', 'live-weather-station'));
        $result[] = array('sections',  __('Translucent - Gray', 'live-weather-station'));
        $result[] = array('white',  __('Translucent - White', 'live-weather-station'));
        $result[] = array('red-darkred',  __('Contrasted - Red', 'live-weather-station'));
        $result[] = array('darkamber',  __('Contrasted - Amber', 'live-weather-station'));
        $result[] = array('green-darkgreen',  __('Contrasted - Green', 'live-weather-station'));
        $result[] = array('green',  __('Contrasted - Turquoise', 'live-weather-station'));
        $result[] = array('blue-lightblue2',  __('Contrasted - Light blue', 'live-weather-station'));
        $result[] = array('blue-gray',  __('Contrasted - Blue', 'live-weather-station'));
        $result[] = array('darkblue',  __('Contrasted - Dark blue', 'live-weather-station'));
        $result[] = array('blue-lightblue',  __('Contrasted - Purple', 'live-weather-station'));
        $result[] = array('yoctopuce',  __('Contrasted - Dark purple', 'live-weather-station'));
        $result[] = array('black-yellow',  __('Contrasted - Black & Yellow', 'live-weather-station'));
        $result[] = array('black',  __('Contrasted - Black', 'live-weather-station'));
        $result[] = array('gray',  __('Contrasted - Dark grey', 'live-weather-station'));
        $result[] = array('amber',  __('Soft - Amber', 'live-weather-station'));
        $result[] = array('green-black',  __('Soft - Dark green', 'live-weather-station'));
        $result[] = array('blue-black',  __('Soft - Blue', 'live-weather-station'));
        $result[] = array('blue2',  __('Soft - Dark blue', 'live-weather-station'));
        $result[] = array('blue-darkblue',  __('Soft - Navy', 'live-weather-station'));
        $result[] = array('purple',  __('Soft - Purple', 'live-weather-station'));
        $result[] = array('darkpurple',  __('Soft - Dark purple', 'live-weather-station'));
        $result[] = array('gray-purple',  __('Soft - Grey', 'live-weather-station'));
        $result[] = array('flat-pomegranate',  __('Flat - Pomegranate', 'live-weather-station'));
        $result[] = array('flat-alizarin',  __('Flat - Alizarin', 'live-weather-station'));
        $result[] = array('flat-pumpkin',  __('Flat - Pumpkin', 'live-weather-station'));
        $result[] = array('flat-carrot',  __('Flat - Carrot', 'live-weather-station'));
        $result[] = array('flat-orange',  __('Flat - Orange', 'live-weather-station'));
        $result[] = array('flat-sunflower',  __('Flat - Sunflower', 'live-weather-station'));
        $result[] = array('flat-emerland',  __('Flat - Emerald', 'live-weather-station'));
        $result[] = array('flat-nephritis',  __('Flat - Nephritis', 'live-weather-station'));
        $result[] = array('flat-turqoise',  __('Flat - Turquoise', 'live-weather-station'));
        $result[] = array('flat-peter-river',  __('Flat - Peter river', 'live-weather-station'));
        $result[] = array('blue-black',  __('Flat - Sky', 'live-weather-station'));
        $result[] = array('flat-belize-hole',  __('Flat - Belize hole', 'live-weather-station'));
        $result[] = array('flat-amythyst',  __('Flat - Amethyst', 'live-weather-station'));
        $result[] = array('flat-wisteria',  __('Flat - Wisteria', 'live-weather-station'));
        $result[] = array('flat-wet-asphalt',  __('Flat - Wet asphalt', 'live-weather-station'));
        $result[] = array('flat-midnight-blue',  __('Flat - Midnight blue', 'live-weather-station'));
        $result[] = array('black-red',  __('Flat - Black', 'live-weather-station'));
        $result[] = array('flat-asbestos',  __('Flat - Asbestos', 'live-weather-station'));
        $result[] = array('flat-concrete',  __('Flat - Concrete', 'live-weather-station'));
        $result[] = array('flat-silver',  __('Flat - Silver', 'live-weather-station'));
        $result[] = array('flat-clouds',  __('Flat - Clouds', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the size options for controls.
     *
     * @return  array   An array containing the controls size options ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_size_js_array($micro=false, $macro=false, $scalable=true) {
        $result = array();
        if ($micro) {
            $result[] = array('micro',  __('Miniature', 'live-weather-station'));
        }
        $result[] = array('small',  __('Small', 'live-weather-station'));
        $result[] = array('medium',  __('Medium', 'live-weather-station'));
        $result[] = array('large',  __('Large', 'live-weather-station'));
        if ($scalable) {
            $result[] = array('scalable', __('Scalable', 'live-weather-station'));
        }
        if ($macro) {
            $result[] = array('macro',  __('Gigantic', 'live-weather-station'));
        }
        return $result;
    }

    /**
     * Get the color options for the clean gauges.
     *
     * @return  array   An array containing the clean gauges colors options ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_justgage_color_js_array() {
        $result = array();
        $result[] = array('lgt-standard',  __('Standard (for light background)', 'live-weather-station'));
        $result[] = array('drk-standard',  __('Standard (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-flag',  __('Flag (for light background)', 'live-weather-station'));
        $result[] = array('drk-flag',  __('Flag (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-pinky',  __('Pinky (for light background)', 'live-weather-station'));
        $result[] = array('drk-pinky',  __('Pinky (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-aquamarine',  __('Aquamarine (for light background)', 'live-weather-station'));
        $result[] = array('drk-aquamarine',  __('Aquamarine (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-bw',  __('B&W (for light background)', 'live-weather-station'));
        $result[] = array('drk-bw',  __('B&W (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidred',  __('Solid red (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidred',  __('Solid red (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidorange',  __('Solid orange (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidorange',  __('Solid orange (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidyellow',  __('Solid yellow (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidyellow',  __('Solid yellow (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidgreen',  __('Solid green (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidgreen',  __('Solid green (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidblue',  __('Solid blue (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidblue',  __('Solid blue (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidpurple',  __('Solid purple (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidpurple',  __('Solid purple (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-solidblack',  __('Solid black (for light background)', 'live-weather-station'));
        $result[] = array('drk-solidblack',  __('Solid black (for dark background)', 'live-weather-station'));
        $result[] = array('lgt-transparent',  __('Transparent (for light background)', 'live-weather-station'));
        $result[] = array('drk-transparent',  __('Transparent (for dark background)', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the pointer options for the clean gauges.
     *
     * @return  array   An array containing the clean gauges pointers options ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_justgage_pointer_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('external',  __('External', 'live-weather-station'));
        $result[] = array('internal',  __('Internal', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the designs options for the clean gauges.
     *
     * @return  array   An array containing the clean gauges designs options ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_justgage_design_js_array() {
        $result = array();
        $result[] = array('half-flat-thin',  __('Half - Flat - Thin', 'live-weather-station'));
        $result[] = array('half-flat-standard',  __('Half - Flat - Standard', 'live-weather-station'));
        $result[] = array('half-flat-fat',  __('Half - Flat - Fat', 'live-weather-station'));
        $result[] = array('half-flat-pie',  __('Half - Flat - Pie', 'live-weather-station'));
        $result[] = array('half-3d-thin',  __('Half - 3D - Thin', 'live-weather-station'));
        $result[] = array('half-3d-standard',  __('Half - 3D - Standard', 'live-weather-station'));
        $result[] = array('half-3d-fat',  __('Half - 3D - Fat', 'live-weather-station'));
        $result[] = array('half-3d-pie',  __('Half - 3D - Pie', 'live-weather-station'));
        $result[] = array('full-flat-thin',  __('Full - Flat - Thin', 'live-weather-station'));
        $result[] = array('full-flat-standard',  __('Full - Flat - Standard', 'live-weather-station'));
        $result[] = array('full-flat-fat',  __('Full - Flat - Fat', 'live-weather-station'));
        $result[] = array('full-flat-pie',  __('Full - Flat - Pie', 'live-weather-station'));
        $result[] = array('full-3d-thin',  __('Full - 3D - Thin', 'live-weather-station'));
        $result[] = array('full-3d-standard',  __('Full - 3D - Standard', 'live-weather-station'));
        $result[] = array('full-3d-fat',  __('Full - 3D - Fat', 'live-weather-station'));
        $result[] = array('full-3d-pie',  __('Full - 3D - Pie', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the title/label options for the clean gauges.
     *
     * @return  array   An array containing the clean gauges titles or labels options ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_justgage_title_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('station',  __('Station name', 'live-weather-station'));
        $result[] = array('module',  __('Module name', 'live-weather-station'));
        $result[] = array('shorttype',  __('Short measurement type', 'live-weather-station'));
        $result[] = array('type',  __('Measurement type', 'live-weather-station'));
        $result[] = array('unit',  __('Measurement unit', 'live-weather-station'));
        $result[] = array('station-module',  __('Station name', 'live-weather-station').' - '.__('Module name', 'live-weather-station'));
        $result[] = array('module-type',  __('Module name', 'live-weather-station').' - '.__('Measurement type', 'live-weather-station'));
        $result[] = array('shorttype-unit',  __('Short measurement type', 'live-weather-station').' ('.__('Measurement unit', 'live-weather-station').')');
        $result[] = array('type-unit',  __('Measurement type', 'live-weather-station').' ('.__('Measurement unit', 'live-weather-station').')');
        return $result;
    }

    /**
     * Get the unit options for the clean gauges.
     *
     * @return  array   An array containing the clean gauges titles options ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_justgage_unit_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('unit',  __('Measurement unit', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the background color preview for the clean gauges.
     *
     * @return  array   An array containing the clean gauges background colors preview ready to convert to a JS array.
     * @since    2.1.0
     * @access   protected
     */
    protected function get_justgage_background_js_array() {
        $result = array();
        $result[] = array('transparent',  __('Test with actual background', 'live-weather-station'));
        $result[] = array('#FFFFFF',  __('Test with white background', 'live-weather-station'));
        $result[] = array('#DDDDDD',  __('Test with light background', 'live-weather-station'));
        $result[] = array('#AAAAAA',  __('Test with medium background', 'live-weather-station'));
        $result[] = array('#555555',  __('Test with dark background', 'live-weather-station'));
        $result[] = array('#000000',  __('Test with black background', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the designs options for the steel meter.
     *
     * @return  array   An array containing the steel meter designs options ready to convert to a JS array.
     * @since    2.2.0
     * @access   protected
     */
    protected function get_steelmeter_design_js_array() {
        $result = array();
        $result[] = array('analog-1-4',  __('Analog - 1/4', 'live-weather-station'));
        $result[] = array('analog-2-4',  __('Analog - half', 'live-weather-station'));
        $result[] = array('analog-3-4',  __('Analog - 3/4', 'live-weather-station'));
        $result[] = array('analog-4-4',  __('Analog - full', 'live-weather-station'));
        $result[] = array('digital-1-4',  __('Digital - 1/4', 'live-weather-station'));
        $result[] = array('digital-2-4',  __('Digital - half', 'live-weather-station'));
        $result[] = array('digital-3-4',  __('Digital - 3/4', 'live-weather-station'));
        $result[] = array('digital-4-4',  __('Digital - full', 'live-weather-station'));
        $result[] = array('meter-top',  __('Top meter', 'live-weather-station'));
        $result[] = array('meter-left',  __('Left meter', 'live-weather-station'));
        $result[] = array('meter-right',  __('Right meter', 'live-weather-station'));
        $result[] = array('windcompass-vintage',  __('Vintage wind compass', 'live-weather-station'));
        $result[] = array('windcompass-standard',  __('Standard wind compass', 'live-weather-station'));
        $result[] = array('windcompass-modern',  __('Modern wind compass', 'live-weather-station'));
        $result[] = array('altimeter-classical',  __('Altimeter', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the bezel options for the steel meter.
     *
     * @return  array   An array containing the steel meter bezel options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_frame_js_array() {
        $result = array();
        $result[] = array('BLACK_METAL',  __('Black metal', 'live-weather-station'));
        $result[] = array('METAL',  __('Metal', 'live-weather-station'));
        $result[] = array('SHINY_METAL',  __('Shiny metal', 'live-weather-station'));
        $result[] = array('GLOSSY_METAL',  __('Glossy metal', 'live-weather-station'));
        $result[] = array('ANTHRACITE',  __('Anthracite', 'live-weather-station'));
        $result[] = array('TILTED_GRAY',  __('Tilted gray', 'live-weather-station'));
        $result[] = array('TILTED_BLACK',  __('Tilted black', 'live-weather-station'));
        $result[] = array('BRASS',  __('Brass', 'live-weather-station'));
        $result[] = array('STEEL',  __('Steel', 'live-weather-station'));
        $result[] = array('CHROME',  __('Chrome', 'live-weather-station'));
        $result[] = array('GOLD',  __('Gold', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the face options for the steel meter.
     *
     * @return  array   An array containing the steel meter face options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_background_js_array() {
        $result = array();
        $result[] = array('DARK_GRAY',  __('Dark gray', 'live-weather-station'));
        $result[] = array('SATIN_GRAY',  __('Satin gray', 'live-weather-station'));
        $result[] = array('LIGHT_GRAY',  __('Light gray', 'live-weather-station'));
        $result[] = array('WHITE',  __('White', 'live-weather-station'));
        $result[] = array('BLACK',  __('Black', 'live-weather-station'));
        $result[] = array('BEIGE',  __('Beige', 'live-weather-station'));
        $result[] = array('BROWN',  __('Brown', 'live-weather-station'));
        $result[] = array('RED',  __('Red', 'live-weather-station'));
        $result[] = array('GREEN',  __('Green', 'live-weather-station'));
        $result[] = array('BLUE',  __('Blue', 'live-weather-station'));
        $result[] = array('ANTHRACITE',  __('Anthracite', 'live-weather-station'));
        $result[] = array('MUD',  __('Mud', 'live-weather-station'));
        $result[] = array('PUNCHED_SHEET',  __('Punched sheet', 'live-weather-station'));
        $result[] = array('CARBON',  __('Carbon', 'live-weather-station'));
        $result[] = array('STAINLESS',  __('Stainless', 'live-weather-station'));
        $result[] = array('BRUSHED_METAL',  __('Brushed metal', 'live-weather-station'));
        $result[] = array('BRUSHED_STAINLESS',  __('Brushed stainless', 'live-weather-station'));
        $result[] = array('TURNED',  __('Turned', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the glass options for the steel meter.
     *
     * @return  array   An array containing the steel meter glass options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_glass_js_array() {
        $result = array();
        $result[] = array('TYPE1',  __('Standard glass', 'live-weather-station'));
        $result[] = array('TYPE2',  __('Crystal', 'live-weather-station'));
        $result[] = array('TYPE3',  __('Sapphire crystal', 'live-weather-station'));
        $result[] = array('TYPE4',  __('Plexiglass', 'live-weather-station'));
        $result[] = array('TYPE5',  __('Rhodoid', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the pointer options for the steel meter.
     *
     * @return  array   An array containing the steel meter pointer options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_pointer_type_js_array() {
        $result = array();
        $result[] = array('TYPE12',  __('Thin triangle', 'live-weather-station'));
        $result[] = array('TYPE7',  __('Fat Triangle', 'live-weather-station'));
        $result[] = array('TYPE5',  __('3D triangle', 'live-weather-station'));
        $result[] = array('TYPE2',  __('Clipped', 'live-weather-station'));
        $result[] = array('TYPE9',  __('Double clipped', 'live-weather-station'));
        $result[] = array('TYPE13',  __('Flat clipped', 'live-weather-station'));
        $result[] = array('TYPE3',  __('Thin rod', 'live-weather-station'));
        $result[] = array('TYPE14',  __('Fat rod', 'live-weather-station'));
        $result[] = array('TYPE1',  __('Thin curved triangle', 'live-weather-station'));
        $result[] = array('TYPE8',  __('3D curved triangle', 'live-weather-station'));
        $result[] = array('TYPE6',  __('Double needle', 'live-weather-station'));
        $result[] = array('TYPE10',  __('Culbuto', 'live-weather-station'));
        $result[] = array('TYPE4',  __('Cessna', 'live-weather-station'));
        $result[] = array('TYPE11',  __('Volvo', 'live-weather-station'));
        $result[] = array('TYPE16',  __('Ferrari', 'live-weather-station'));
        $result[] = array('TYPE15',  __('Ferrari arched', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the pointer color options for the steel meter.
     *
     * @return  array   An array containing the steel meter pointer color options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_pointer_color_js_array() {
        $result = array();
        $result[] = array('RED',  __('Red', 'live-weather-station'));
        $result[] = array('ORANGE',  __('Orange', 'live-weather-station'));
        $result[] = array('YELLOW',  __('Yellow', 'live-weather-station'));
        $result[] = array('GREEN',  __('Green', 'live-weather-station'));
        $result[] = array('JUG_GREEN',  __('JUG Green', 'live-weather-station'));
        $result[] = array('GREEN_LCD',  __('Green LCD', 'live-weather-station'));
        $result[] = array('CYAN',  __('Cyan', 'live-weather-station'));
        $result[] = array('RAITH',  __('Raith', 'live-weather-station'));
        $result[] = array('BLUE',  __('Blue', 'live-weather-station'));
        $result[] = array('MAGENTA',  __('Magenta', 'live-weather-station'));
        $result[] = array('WHITE',  __('White', 'live-weather-station'));
        $result[] = array('GRAY',  __('Gray', 'live-weather-station'));
        $result[] = array('BLACK',  __('Black', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the knob  options for the steel meter.
     *
     * @return  array   An array containing the steel meter knob options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_knob_js_array() {
        $result = array();
        $result[] = array('STANDARD_KNOB-BLACK',  __('Plain - Black', 'live-weather-station'));
        $result[] = array('STANDARD_KNOB-BRASS',  __('Plain - Brass', 'live-weather-station'));
        $result[] = array('STANDARD_KNOB-SILVER',  __('Plain - Silver', 'live-weather-station'));
        $result[] = array('METAL_KNOB-BLACK',  __('Embossed - Black', 'live-weather-station'));
        $result[] = array('METAL_KNOB-BRASS',  __('Embossed - Brass', 'live-weather-station'));
        $result[] = array('METAL_KNOB-SILVER',  __('Embossed - Silver', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the led / trend color options for the steel meter.
     *
     * @return  array   An array containing the steel meter led / trend color options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_led_color_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('RED_LED',  __('Red', 'live-weather-station'));
        $result[] = array('ORANGE_LED',  __('Orange', 'live-weather-station'));
        $result[] = array('YELLOW_LED',  __('Yellow', 'live-weather-station'));
        $result[] = array('GREEN_LED',  __('Green', 'live-weather-station'));
        $result[] = array('CYAN_LED',  __('Cyan', 'live-weather-station'));
        $result[] = array('BLUE_LED',  __('Blue', 'live-weather-station'));
        $result[] = array('MAGENTA_LED',  __('Magenta', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the designs options for the lcd steel meter.
     *
     * @return  array   An array containing the lcd designs options for steel meter ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_lcd_design_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array(strtoupper('standard'),  __('Standard - Without backlight', 'live-weather-station'));
        $result[] = array(strtoupper('red'),  __('Translucent - Red', 'live-weather-station'));
        $result[] = array(strtoupper('orange'),  __('Translucent - Orange', 'live-weather-station'));
        $result[] = array(strtoupper('beige'),  __('Translucent - Beige', 'live-weather-station'));
        $result[] = array(strtoupper('yellow'),  __('Translucent - Yellow green', 'live-weather-station'));
        $result[] = array(strtoupper('standard_green'),  __('Translucent - Standard green', 'live-weather-station'));
        $result[] = array(strtoupper('blue'),  __('Translucent - Light blue', 'live-weather-station'));
        $result[] = array(strtoupper('blue_blue'),  __('Translucent - Blue', 'live-weather-station'));
        $result[] = array(strtoupper('white'),  __('Translucent - White', 'live-weather-station'));
        $result[] = array(strtoupper('lightblue'),  __('Translucent - Light purple', 'live-weather-station'));
        $result[] = array(strtoupper('sections'),  __('Translucent - Gray', 'live-weather-station'));
        $result[] = array(strtoupper('red_darkred'),  __('Contrasted - Red', 'live-weather-station'));
        $result[] = array(strtoupper('green'),  __('Contrasted - Turquoise', 'live-weather-station'));
        $result[] = array(strtoupper('gray'),  __('Contrasted - Dark grey', 'live-weather-station'));
        $result[] = array(strtoupper('black'),  __('Contrasted - Black', 'live-weather-station'));
        $result[] = array(strtoupper('blue_gray'),  __('Contrasted - Blue', 'live-weather-station'));
        $result[] = array(strtoupper('darkblue'),  __('Contrasted - Dark blue', 'live-weather-station'));
        $result[] = array(strtoupper('amber'),  __('Soft - Amber', 'live-weather-station'));
        $result[] = array(strtoupper('darkgreen'),  __('Soft - Dark green', 'live-weather-station'));
        $result[] = array(strtoupper('blue_black'),  __('Soft - Blue', 'live-weather-station'));
        $result[] = array(strtoupper('blue2'),  __('Soft - Dark blue', 'live-weather-station'));
        $result[] = array(strtoupper('lila'),  __('Soft - Purple', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the min/max color options for the steel meter.
     *
     * @return  array   An array containing the steel meter min/max colors options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_minmax_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('cursor',  __('Cursors', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the text orientation options for the steel meter.
     *
     * @return  array   An array containing the steel meter text orientation options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_orientation_js_array() {
        $result = array();
        $result[] = array('auto',  __('Automatic', 'live-weather-station'));
        $result[] = array('NORMAL',  __('Normal', 'live-weather-station'));
        $result[] = array('HORIZONTAL',  __('Horizontal', 'live-weather-station'));
        $result[] = array('TANGENT',  __('Tangent', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the radial indicator color options for the steel meter.
     *
     * @return  array   An array containing the steel meter radial indicator color options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_index_color_js_array() {
        $result = array();
        $result[] = array('RED',  __('Red', 'live-weather-station'));
        $result[] = array('MAROON',  __('Maroon', 'live-weather-station'));
        $result[] = array('ORANGERED',  __('Orange', 'live-weather-station'));
        $result[] = array('YELLOW',  __('Yellow', 'live-weather-station'));
        $result[] = array('LIME',  __('Lime', 'live-weather-station'));
        $result[] = array('GREEN',  __('Green', 'live-weather-station'));
        $result[] = array('TEAL',  __('Teal', 'live-weather-station'));
        $result[] = array('AQUA',  __('Aqua', 'live-weather-station'));
        $result[] = array('LIGHTBLUE',  __('Light blue', 'live-weather-station'));
        $result[] = array('BLUE',  __('Blue', 'live-weather-station'));
        $result[] = array('NAVY',  __('Navy', 'live-weather-station'));
        $result[] = array('FUCHSIA',  __('Fuchsia', 'live-weather-station'));
        $result[] = array('PURPLE',  __('Purple', 'live-weather-station'));
        $result[] = array('WHITE',  __('White', 'live-weather-station'));
        $result[] = array('SILVER',  __('Silver', 'live-weather-station'));
        $result[] = array('GRAY',  __('Gray', 'live-weather-station'));
        $result[] = array('BLACK',  __('Black', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the radial indicator style options for the steel meter.
     *
     * @return  array   An array containing the steel meter radial indicator style options ready to convert to a JS array.
     * @since    2.2.0
     */
    protected function get_steelmeter_index_style_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('fixed-translucent',  __('Plain - Translucent', 'live-weather-station'));
        $result[] = array('fixed-liquid',  __('Plain - Liquid', 'live-weather-station'));
        $result[] = array('fixed-soft',  __('Plain - Soft', 'live-weather-station'));
        $result[] = array('fixed-hard',  __('Plain - Hard', 'live-weather-station'));
        $result[] = array('fadein-translucent',  __('Fade-in - Translucent', 'live-weather-station'));
        $result[] = array('fadein-liquid',  __('Fade-in - Liquid', 'live-weather-station'));
        $result[] = array('fadein-soft',  __('Fade-in - Soft', 'live-weather-station'));
        $result[] = array('fadein-hard',  __('Fade-in - Hard', 'live-weather-station'));
        $result[] = array('fadeout-translucent',  __('Fade-out - Translucent', 'live-weather-station'));
        $result[] = array('fadeout-liquid',  __('Fade-out - Liquid', 'live-weather-station'));
        $result[] = array('fadeout-soft',  __('Fade-out - Soft', 'live-weather-station'));
        $result[] = array('fadeout-hard',  __('Fade-out - Hard', 'live-weather-station'));
        $result[] = array('complementary-translucent',  __('Complementary - Translucent', 'live-weather-station'));
        $result[] = array('complementary-liquid',  __('Complementary - Liquid', 'live-weather-station'));
        $result[] = array('complementary-soft',  __('Complementary - Soft', 'live-weather-station'));
        $result[] = array('complementary-hard',  __('Complementary - Hard', 'live-weather-station'));
        $result[] = array('invcomplementary-translucent',  __('Inverted complementary - Translucent', 'live-weather-station'));
        $result[] = array('invcomplementary-liquid',  __('Inverted complementary - Liquid', 'live-weather-station'));
        $result[] = array('invcomplementary-soft',  __('Inverted complementary - Soft', 'live-weather-station'));
        $result[] = array('invcomplementary-hard',  __('Inverted complementary - Hard', 'live-weather-station'));
        return $result;
    }


    /**
     * Get period types array.
     *
     * @param boolean $rolling Optional. The array must contains rolling periods.
     * @return array An array containing the period types ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_period_type_js_array($rolling=true) {
        $result = array();
        if ($rolling) {
            $result[] = array('rolling-days',  __('Rolling days', 'live-weather-station'));
        }
        $result[] = array('fixed-month',  __('Fixed month', 'live-weather-station'));
        $result[] = array('sliding-month',  __('Sliding month', 'live-weather-station'));
        $result[] = array('fixed-mseason',  __('Fixed meteorological season', 'live-weather-station'));
        $result[] = array('sliding-mseason',  __('Sliding meteorological season', 'live-weather-station'));
        //$result[] = array('fixed-aseason',  __('Fixed astronomical season', 'live-weather-station'));
        //$result[] = array('sliding-aseason',  __('Sliding astronomical season', 'live-weather-station'));
        $result[] = array('fixed-year',  __('Fixed year', 'live-weather-station'));
        $result[] = array('sliding-year',  __('Sliding year', 'live-weather-station'));
        return $result;
    }

    /**
     * Get timelapse period types array.
     *
     * @return array An array containing the period types ready to convert to a JS array.
     * @since 3.6.0
     */
    protected function get_timelapse_period_type_js_array() {
        $result = array();
        $result[] = array('sliding-timelapse',  __('Sliding day', 'live-weather-station'));
        $result[] = array('fixed-timelapse',  __('Fixed day', 'live-weather-station'));
        return $result;
    }

    /**
     * Get period values array.
     *
     * @param array $station The station informations.
     * @param boolean $rolling Optional. The array must contains rolling periods.
     * @return array An array containing the period values ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_period_value_js_array($station, $rolling=true) {
        $result = array();
        $oldest_date = $this->get_oldest_data($station) . ' 12:00:00';

        // Rolling days
        if ($rolling) {
            $period = array();
            foreach (array(7, 15, 30, 60, 90) as $i) {
                $period[] = array('rdays-' . $i, sprintf(__('Last %s days', 'live-weather-station'), $i));
            }
            $result[] = array('rolling-days', $period);
        }

        // Sliding month
        $period = array();
        for ($i=0; $i<=12; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'month-'.$i, __('Current month', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-month',  $period);

        // Sliding meteorological season
        $period = array();
        for ($i=0; $i<=4; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'mseason-'.$i, __('Current meteorological season', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-mseason',  $period);

        // Sliding astronomical season
        $period = array();
        for ($i=0; $i<=4; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'aseason-'.$i, __('Current astronomical season', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-aseason',  $period);

        // Sliding year
        $period = array();
        for ($i=0; $i<=1; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'year-'.$i, __('Current year', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-year',  $period);

        // Fixed year & month
        $fixed_month = array();
        $fixed_year = array();
        $start = new \DateTime($oldest_date, new \DateTimeZone($station['loc_timezone']));
        $current = new \DateTime($oldest_date, new \DateTimeZone($station['loc_timezone']));
        $util = new \DateTime($oldest_date, new \DateTimeZone($station['loc_timezone']));
        $year = $start->format('Y');
        $month = $start->format('m');
        $end = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
        while ($year != $end->format('Y') || $month != $end->format('m')) {
            $current->setDate($year, $month, 1);
            $util->setDate($year, $month, $current->format('t'));
            $fixed_month[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y, F', strtotime($current->format('Y-m-d H:i:s'))));
            $month += 1;
            if ($month > 12) {
                $current->setDate($year, 1, 1);
                $util->setDate($year, 12, 31);
                $fixed_year[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y', strtotime($current->format('Y-m-d H:i:s'))));
                $month = 1;
                $year += 1;
            }
        }
        $current->setDate($year, $month, 1);
        $util->setDate($year, $month, $current->format('t'));
        $fixed_month[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y, F', strtotime($end->format('Y-m-d H:i:s'))));
        $current->setDate($year, 1, 1);
        $util->setDate($year, 12, 31);
        $fixed_year[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y', strtotime($end->format('Y-m-d H:i:s'))));
        if (empty($fixed_month)) {
            $fixed_month = array(array('none', 'none'));
        }
        $result[] = array('fixed-month', array_reverse($fixed_month));
        if (empty($fixed_year)) {
            $fixed_year = array(array('none', 'none'));
        }
        $result[] = array('fixed-year', array_reverse($fixed_year));

        // Fixed meteorological season
        $result[] = array('fixed-mseason', Season::matchingMeteorologicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0));

        // Fixed astronomical season
        //$result[] = array('fixed-aseason', Season::matchingAstronomicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0));

        $result[] = array('none',  array(array('none', 'none')));
        return $result;
    }

    /**
     * Get timelapse period values array.
     *
     * @param array $station The station informations.
     * @return array An array containing the period values ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_timelapse_period_value_js_array($station) {
        $result = array();

        // Sliding day
        $period = array();
        for ($i=1; $i<=14; $i++) {
            $period[] = array( 'timelapse-'.$i, sprintf(_n('%s day back', '%s days back', $i, 'live-weather-station'), $i));
        }
        $result[] = array('sliding-timelapse',  $period);

        // Fixed day
        $dates = self::get_video_dates($station['station_id']);
        $period = array();
        if (!empty($dates)) {
            foreach ($dates as $date) {
                $period[] = array(str_replace(' ', '_', $date['timestamp']), date_i18n(get_option('date_format'), strtotime(get_date_from_gmt($date['timestamp']))));
            }
        }
        else {
            $period[] = array('1971-08-21_12:00:00', __('No timelapse for now.', 'live-weather-station'));
        }
        $result[] = array('fixed-timelapse',  $period);
        $result[] = array('none',  array(array('none', 'none')));
        return $result;
    }

    /**
     * Get comparable dimensions array.
     *
     * @return array An array containing the comparable dimensions ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_comparable_dimensions_js_array() {
        $result = array();
        foreach ($this->get_comparable_dimensions() as $dimension) {
            $result[] = array($dimension, $this->get_dimension_name($dimension, true));
        }
        usort($result, 'lws_array_compare_1');
        return $result;
    }

    /**
     * Get guideline array.
     *
     * @return array An array containing the guideline ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_guideline_js_array() {
        $result = array();
        $result[] = array('standard',  __('Standard', 'live-weather-station'));
        $result[] = array('interactive',  __('Interactive', 'live-weather-station'));
        return $result;
    }

    /**
     * Get guideline array.
     *
     * @return array An array containing the guideline ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_legend_js_array() {
        $result = array();
        $result[] = array('standard',  __('Not displayed', 'live-weather-station'));
        $result[] = array('interactive',  __('Displayed', 'live-weather-station'));
        return $result;
    }

    /**
     * Get bar group array.
     *
     * @return array An array containing the groups ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_bar_group_js_array() {
        $result = array();
        $result[] = array('free',  __('Free', 'live-weather-station'));
        $result[] = array('grouped',  __('Grouped', 'live-weather-station'));
        $result[] = array('stacked',  __('Stacked', 'live-weather-station'));
        return $result;
    }

    /**
     * Get stacked areas group array.
     *
     * @return array An array containing the groups ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_sareas_group_js_array() {
        $result = array();
        $result[] = array('stacked',  __('Stacked', 'live-weather-station'));
        //$result[] = array('stream',  __('Stream', 'live-weather-station'));
        $result[] = array('expanded',  __('Expanded', 'live-weather-station'));
        return $result;
    }

    /**
     * Get legend array.
     *
     * @return array An array containing the groups ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_legend_group_js_array() {
        $result = array();
        $result[] = array('stacked',  __('Stacked', 'live-weather-station'));
        //$result[] = array('stream',  __('Stream', 'live-weather-station'));
        $result[] = array('expanded',  __('Expanded', 'live-weather-station'));
        return $result;
    }

    /**
     * Get stacked areas group array.
     *
     * @return array An array containing the groups ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_radarstyle_group_js_array() {
        $result = array();
        $result[] = array('standard',  __('Standard', 'live-weather-station'));
        $result[] = array('glowing',  __('Glowing', 'live-weather-station'));
        return $result;
    }

    /**
     * Get video controls appearance array.
     *
     * @return array An array containing the appearances ready to convert to a JS array.
     * @since 3.6.0
     */
    protected function get_video_appearance_js_array() {
        $result = array();
        $result[] = array('none',  __('No controls', 'live-weather-station'));
        $result[] = array('full',  __('Full controls', 'live-weather-station'));
        return $result;
    }

    /**
     * Get video behavior array.
     *
     * @return array An array containing the behavior ready to convert to a JS array.
     * @since 3.6.0
     */
    protected function get_video_behavior_js_array() {
        $result = array();
        $result[] = array('manual',  __('Manual', 'live-weather-station'));
        $result[] = array('auto',  __('Autoplay', 'live-weather-station'));
        return $result;
    }

    /**
     * Get video mode array.
     *
     * @return array An array containing the mode ready to convert to a JS array.
     * @since 3.6.0
     */
    protected function get_video_mode_js_array() {
        $result = array();
        $result[] = array('once',  __('Once', 'live-weather-station'));
        $result[] = array('loop',  __('Loop', 'live-weather-station'));
        return $result;
    }

    /**
     * Get  array.
     *
     * @return array An array containing the  ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_legend_position_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('left',  __('Left', 'live-weather-station'));
        $result[] = array('center',  __('Center', 'live-weather-station'));
        $result[] = array('right',  __('Right', 'live-weather-station'));
        return $result;
    }

    /**
     * Get label array.
     *
     * @return array An array containing the label types ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_label_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('simple',  __('Simple', 'live-weather-station'));
        $result[] = array('generic',  __('Generic', 'live-weather-station'));
        $result[] = array('named',  __('Name', 'live-weather-station'));
        $result[] = array('station',  __('Station', 'live-weather-station'));
        $result[] = array('located',  __('Location', 'live-weather-station'));
        $result[] = array('coord',  __('Coordinates', 'live-weather-station'));
        $result[] = array('full',  __('Full', 'live-weather-station'));
        return $result;
    }

    /**
     * Get label array.
     *
     * @return array An array containing the label types ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_multi_label_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('simple',  __('Simple', 'live-weather-station'));
        $result[] = array('station',  __('Station', 'live-weather-station'));
        $result[] = array('located',  __('Location', 'live-weather-station'));
        $result[] = array('coord',  __('Coordinates', 'live-weather-station'));
        return $result;
    }

    /**
     * Get label array.
     *
     * @return array An array containing the label types ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_multi_2_label_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('simple',  __('Simple', 'live-weather-station'));
        $result[] = array('generic',  __('Generic', 'live-weather-station'));
        $result[] = array('station',  __('Station', 'live-weather-station'));
        $result[] = array('located',  __('Location', 'live-weather-station'));
        $result[] = array('coord',  __('Coordinates', 'live-weather-station'));
        return $result;
    }

    /**
     * Get time scale array.
     *
     * @return array An array containing the time scale options ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_day_format_js_array() {
        $result = array();
        $result[] = array('square',  __('Square', 'live-weather-station'));
        $result[] = array('rdsquare',  __('Rounded square', 'live-weather-station'));
        $result[] = array('round',  __('Circle', 'live-weather-station'));
        return $result;
    }

    /**
     * Get time scale array.
     *
     * @return array An array containing the time scale options ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_x_scale_js_array($focus=false) {
        $result = array();
        $result[] = array('auto',  __('Automatic', 'live-weather-station'));
        $result[] = array('adaptative',  __('Adaptative', 'live-weather-station'));
        if($focus) {
            $result[] = array('focus',  __('Adaptative with focus', 'live-weather-station'));
        }
        $result[] = array('fixed',  __('Fixed', 'live-weather-station'));
        $result[] = array('none',  __('None', 'live-weather-station'));
        return $result;
    }

    /**
     * Get value scale array.
     *
     * @return array An array containing the value scale options ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_y_scale_js_array($time_consistent=false, $windrose=false) {
        $result = array();
        $result[] = array('auto',  __('Automatic', 'live-weather-station'));
        $result[] = array('adaptative',  __('Adaptative', 'live-weather-station'));
        if ($time_consistent) {
            $result[] = array('consistent',  __('Time consistent', 'live-weather-station'));
        }
        $result[] = array('fixed',  __('Fixed', 'live-weather-station'));
        if (!$windrose) {
            $result[] = array('boundaries',  __('Thresholds limits', 'live-weather-station'));
            $result[] = array('alarm',  __('Thresholds alarms', 'live-weather-station'));
            $result[] = array('top',  __('0-based - top', 'live-weather-station'));
            $result[] = array('bottom',  __('0-based - bottom', 'live-weather-station'));
            $result[] = array('none',  __('None', 'live-weather-station'));
        }
        return $result;
    }

    /**
     * Get windrose scale array.
     *
     * @return array An array containing the windrose scale options ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_windrose_scale_js_array() {
        $result = array();
        $result[] = array('linear',  __('Linear', 'live-weather-station'));
        $result[] = array('radial',  __('Radial', 'live-weather-station'));
        return $result;
    }

    /**
     * Get dot style array.
     *
     * @return array An array containing the dot style ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_stream_resolution_js_array() {
        $result = array();
        $time = array (5, 10, 15, 20, 25, 30);
        foreach ($time as $t) {
            $result[] = array('res-'.$t,  sprintf(__('%s minutes', 'live-weather-station'), $t));
        }
        return $result;
    }

    /**
     * Get stream resolution array.
     *
     * @return array An array containing the stream resolution ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_dot_style_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('small-dot',  __('Small dot', 'live-weather-station'));
        $result[] = array('large-dot',  __('Large dot', 'live-weather-station'));
        $result[] = array('small-circle',  __('Small circle', 'live-weather-station'));
        $result[] = array('large-circle',  __('Large circle', 'live-weather-station'));
        return $result;
    }

    /**
     * Get line style array.
     *
     * @return array An array containing the line style ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_line_style_js_array() {
        $result = array();
        $result[] = array('solid',  __('Solid', 'live-weather-station'));
        $result[] = array('dotted',  __('Dotted', 'live-weather-station'));
        $result[] = array('dashed',  __('Dashed', 'live-weather-station'));
        return $result;
    }

    /**
     * Get line mode array.
     *
     * @return array An array containing the line mode ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_line_mode_js_array() {
        $result = array();
        $result[] = array('transparent',  __('Transparent', 'live-weather-station'));
        $result[] = array('line',  __('Line', 'live-weather-station'));
        $result[] = array('area',  __('Array', 'live-weather-station'));
        $result[] = array('arealine',  __('Array & Line', 'live-weather-station'));
        return $result;
    }

    /**
     * Get stackable mode array.
     *
     * @return array An array containing the stackable mode ready to convert to a JS array.
     * @since 3.5.0
     */
    protected function get_stackable_mode_js_array() {
        $result = array();
        $result[] = array('single',  __('Single', 'live-weather-station'));
        $result[] = array('stackable',  __('Stackable', 'live-weather-station'));
        return $result;
    }

    /**
     * Get allotment  array.
     *
     * @return array An array containing the allotment ready to convert to a JS array.
     * @param int $level Optional. The count of sectors.
     * @since 3.5.0
     */
    protected function get_allotment_js_array($level=2) {
        $result = array();
        for ($i = 0; $i < 5; $i++) {
            if ($level > $i) {
                $n = pow(2, $i + 2) ;
                $result[] = array($n . 's',  sprintf(_n('%s sector', '%s sectors', $n,  'live-weather-station'), $n));
            }
        }
        return $result;
    }

    /**
     * Get line size array.
     *
     * @return array An array containing the line size ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_line_size_js_array() {
        $result = array();
        $result[] = array('thin',  __('Thin', 'live-weather-station'));
        $result[] = array('regular',  __('Regular', 'live-weather-station'));
        $result[] = array('thick',  __('Thick', 'live-weather-station'));
        return $result;
    }

    /**
     * Get graph size array.
     *
     * @param boolean Optional. Add autoscale ability.
     * @return array An array containing the line size ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_graph_size_js_array($autoscale=false) {
        $result = array();
        if ($autoscale) {
            $result[] = array('autoscale',  __('Responsive', 'live-weather-station'));
        }
        $result[] = array('150px',  __('XS', 'live-weather-station'));
        $result[] = array('200px',  __('S', 'live-weather-station'));
        $result[] = array('300px',  __('M', 'live-weather-station'));
        $result[] = array('400px',  __('L', 'live-weather-station'));
        $result[] = array('555px',  __('XL', 'live-weather-station'));
        return $result;
    }

    /**
     * Get graph data array.
     *
     * @param boolean $with_refresh Optional. Add auto refresh ability.
     * @param boolean $with_preload Optional. Add preload ability.
     * @return array An array containing the line size ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_graph_data_js_array($with_refresh=true, $with_preload=true) {
        $result = array();
        $result[] = array('inline',  __('Inline', 'live-weather-station'));
        if ($with_preload) {
            $result[] = array('ajax', __('Ajax preload', 'live-weather-station'));
        }
        if ($with_refresh) {
            $result[] = array('ajax_refresh',  __('Ajax refresh', 'live-weather-station'));
        }
        return $result;
    }

    /**
     * Get textual animation array.
     *
     * @return array An array containing the textual animations ready to convert to a JS array.
     * @since 3.6.0
     */
    protected function get_textual_animation_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('fade-to-initial',  __('Fade', 'live-weather-station'));
        $result[] = array('glow',  __('Glow', 'live-weather-station'));
        $result[] = array('blink',  __('Blink', 'live-weather-station'));
        return $result;
    }

    /**
     * Get picture animation array.
     *
     * @return array An array containing the picture animations ready to convert to a JS array.
     * @since 3.6.0
     */
    protected function get_picture_animation_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('fade-from-to',  __('Fade', 'live-weather-station'));
        $result[] = array('spin',  __('Spinner', 'live-weather-station'));
        return $result;
    }

    /**
     * Get graph template array.
     *
     * @return array An array containing the graph templates ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_graph_template_js_array() {
        $result = array();
        $result[] = array('neutral',  __('Neutral', 'live-weather-station'));
        $result[] = array('light',  __('Light', 'live-weather-station'));
        $result[] = array('modern',  __('Modern', 'live-weather-station'));
        $result[] = array('sand',  __('Sand', 'live-weather-station'));
        $result[] = array('organic',  __('Organic', 'live-weather-station'));
        $result[] = array('mineral',  __('Mineral', 'live-weather-station'));
        $result[] = array('dark',  __('Dark', 'live-weather-station'));
        $result[] = array('ws',  'Weather Station');
        $result[] = array('night',  __('Night', 'live-weather-station'));
        $result[] = array('bw',  __('Black & white', 'live-weather-station'));
        $result[] = array('bwi',  __('Black & white', 'live-weather-station') . ' (' . __('inverted', 'live-weather-station') . ')');
        $result[] = array('terminal',  __('Terminal', 'live-weather-station'));
        $result[] = array('console',  __('Console', 'live-weather-station'));
        return $result;
    }

    /**
     * Get interpolation methods array.
     *
     * @param boolean $simple Optional. Only simple interpolations.
     * @return array An array containing the interpotaion methods ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_interpolation_js_array($simple=false) {
        $result = array();
        $result[] = array('linear',  __('Linear', 'live-weather-station'));
        if (!$simple) {
            $result[] = array('monotone',  __('Monotone', 'live-weather-station'));
            $result[] = array('bundle',  __('Bundle', 'live-weather-station'));
            $result[] = array('step-before',  __('Step before', 'live-weather-station'));
            $result[] = array('step-after',  __('Step after', 'live-weather-station'));
            $result[] = array('basis',  __('Basis', 'live-weather-station'));
            $result[] = array('basis-open',  __('Basis - Open', 'live-weather-station'));
            $result[] = array('basis-closed',  __('Basis - Closed', 'live-weather-station'));
        }
        $result[] = array('cardinal',  __('Cardinal', 'live-weather-station'));
        if (!$simple) {
            $result[] = array('cardinal-open',  __('Cardinal - Open', 'live-weather-station'));
            $result[] = array('cardinal-closed',  __('Cardinal - Closed', 'live-weather-station'));
        }
        return $result;
    }

    /**
     * Get starts stacking  array.
     *
     * @return array An array containing the start stacking ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_stacking_js_array() {
        $result = array();
        $result[] = array('grouped',  __('Grouped', 'live-weather-station'));
        $result[] = array('stacked',  __('Stacked', 'live-weather-station'));
        return $result;
    }

    /**
     * Get color thresholds array.
     *
     * @return array An array containing the interpotaion methods ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_color_threshold_js_array() {
        $result = array();
        for ($i=3; $i<9; $i++) {
            $result[] = array('color-step-' . $i,  sprintf(_n('%s step', '%s steps', $i, 'live-weather-station'), $i));
        }
        return $result;
    }

    /**
     * Get colors array.
     *
     * @return array An array containing ColorBrewer options ready to convert to a JS array.
     * @since 3.4.0
     */
    protected function get_colorbrewer_js_array($self=false, $sequential=true, $diverging=true, $qualitative=true, $inverted=true, $standard=false) {
        $result = array();
        $sep = '-';
        $dsq =  __('sequential', 'live-weather-station');
        $r =  __('reverse order', 'live-weather-station');
        $ddv =  __('diverging', 'live-weather-station');
        $dql =  __('qualitative', 'live-weather-station');
        $tmh =  __('multi-hue', 'live-weather-station');
        $tsh =  __('single hue', 'live-weather-station');
        $yl =  __('Yellow', 'live-weather-station');
        $gn =  __('Green', 'live-weather-station');
        $bu =  __('Blue', 'live-weather-station');
        $pu =  __('Purple', 'live-weather-station');
        $or =  __('Orange', 'live-weather-station');
        $rd =  __('Red', 'live-weather-station');
        $br =  __('Brown', 'live-weather-station');
        $gr =  __('Grey', 'live-weather-station');
        $pi =  __('Pink', 'live-weather-station');
        $sp =  __('Spectral', 'live-weather-station');
        if ($standard) {
            $result[] = array('basic', '//->' . ucfirst(__('basic', 'live-weather-station')));
            $result[] = array('std', __('International wind standard', 'live-weather-station'));
        }
        if ($self) {
            if (count($result) === 0) {
                $result[] = array('basic', '//->' . ucfirst(__('basic', 'live-weather-station')));
            }
            $result[] = array('self', __('Template color', 'live-weather-station'));
            if ($inverted) {
                $result[] = array('i_self',  __('Template color', 'live-weather-station') . ' (' . $r . ')');
            }
        }
        if ($sequential) {
            $result[] = array('dsq', '//->' . ucfirst($dsq) . ' / ' . ucfirst($tsh));
            $result[] = array('Blues', $bu );
            if ($inverted) {$result[] = array('i_Blues', $bu . ' (' . $r . ')');}
            $result[] = array('Greens', $gn );
            if ($inverted) {$result[] = array('i_Greens', $gn . ' (' . $r . ')');}
            $result[] = array('Oranges', $or );
            if ($inverted) {$result[] = array('i_Oranges', $or . ' (' . $r . ')');}
            $result[] = array('Purples', $pu );
            if ($inverted) {$result[] = array('i_Purples', $pu . ' (' . $r . ')');}
            $result[] = array('Reds', $rd );
            if ($inverted) {$result[] = array('i_Reds', $rd . ' (' . $r . ')');}
            $result[] = array('Greys', $gr );
            if ($inverted) {$result[] = array('i_Greys', $gr . ' (' . $r . ')');}
            $result[] = array('dsq', '//->' . ucfirst($dsq) . ' / ' . ucfirst($tmh));
            $result[] = array('BuGn', $bu . $sep . $gn );
            if ($inverted) {$result[] = array('i_BuGn', $bu . $sep . $gn . ' ('   . $r . ')');}
            $result[] = array('BuPu', $bu . $sep . $pu );
            if ($inverted) {$result[] = array('i_BuPu', $bu . $sep . $pu . ' ('   . $r . ')');}
            $result[] = array('GnBu', $gn . $sep . $bu );
            if ($inverted) {$result[] = array('i_GnBu', $gn . $sep . $bu . ' ('   . $r . ')');}
            $result[] = array('OrRd', $or . $sep . $rd );
            if ($inverted) {$result[] = array('i_OrRd', $or . $sep . $rd . ' ('   . $r . ')');}
            $result[] = array('PuBu', $pu . $sep . $bu );
            if ($inverted) {$result[] = array('i_PuBu', $pu . $sep . $bu . ' ('   . $r . ')');}
            $result[] = array('PuBuGn', $pu . $sep . $bu . $sep . $gn );
            if ($inverted) {$result[] = array('i_PuBuGn', $pu . $sep . $bu . $sep . $gn . ' ('   . $r . ')');}
            $result[] = array('PuRd', $pu . $sep . $rd );
            if ($inverted) {$result[] = array('i_PuRd', $pu . $sep . $rd . ' ('   . $r . ')');}
            $result[] = array('RdPu', $rd . $sep . $pu );
            if ($inverted) {$result[] = array('i_RdPu', $rd . $sep . $pu . ' ('   . $r . ')');}
            $result[] = array('YlGn', $yl . $sep . $gn );
            if ($inverted) {$result[] = array('i_YlGn', $yl . $sep . $gn . ' ('   . $r . ')');}
            $result[] = array('YlOrBr', $yl . $sep . $or . $sep . $br );
            if ($inverted) {$result[] = array('i_YlOrBr', $yl . $sep . $or . $sep . $br . ' ('   . $r . ')');}
            $result[] = array('YlOrRd', $yl . $sep . $or . $sep . $rd );
            if ($inverted) {$result[] = array('i_YlOrRd', $yl . $sep . $or . $sep . $rd . ' ('   . $r . ')');}
        }
        if ($diverging) {
            $result[] = array('ddv', '//->' . ucfirst($ddv));
            $result[] = array('PRGn', $pu . $sep . $gn );
            if ($inverted) {$result[] = array('i_PRGn', $pu . $sep . $gn . ' ('  . $r . ')');}
            $result[] = array('PuOr', $or . $sep . $pu );
            if ($inverted) {$result[] = array('i_PuOr', $or . $sep . $pu . ' ('  . $r . ')');}
            $result[] = array('RdBu', $rd . $sep . $bu );
            if ($inverted) {$result[] = array('i_RdBu', $rd . $sep . $bu . ' ('  . $r . ')');}
            $result[] = array('RdGy', $rd . $sep . $gr );
            if ($inverted) {$result[] = array('i_RdGy', $rd . $sep . $gr . ' ('  . $r . ')');}
            $result[] = array('RdYlBu', $rd . $sep . $yl . $sep . $bu );
            if ($inverted) {$result[] = array('i_RdYlBu', $rd . $sep . $yl . $sep . $bu . ' ('  . $r . ')');}
            $result[] = array('BrBG', $br . $sep . $bu . $sep . $gn );
            if ($inverted) {$result[] = array('i_BrBG', $br . $sep . $bu . $sep . $gn . ' ('  . $r . ')');}
            $result[] = array('PiYG', $pi . $sep . $yl . $sep . $gn );
            if ($inverted) {$result[] = array('i_PiYG', $pi . $sep . $yl . $sep . $gn . ' ('  . $r . ')');}
            $result[] = array('Spectral', $sp );
            if ($inverted) {$result[] = array('i_Spectral', $sp . ' ('  . $r . ')');}
        }
        if ($qualitative) {
            $result[] = array('dql', '//->' . ucfirst($dql));
            $result[] = array('Accent', __('Accent', 'live-weather-station') );
            if ($inverted) {$result[] = array('i_Accent', __('Accent', 'live-weather-station') . ' ('  . $r . ')');}
            $result[] = array('Dark2', __('Dark', 'live-weather-station') );
            if ($inverted) {$result[] = array('i_Dark2', __('Dark', 'live-weather-station') . ' ('  . $r . ')');}
            $result[] = array('Paired', __('Paired', 'live-weather-station') );
            if ($inverted) {$result[] = array('i_Paired', __('Paired', 'live-weather-station') . ' ('  . $r . ')');}
            $result[] = array('Pastel1', __('Pastel', 'live-weather-station') . ' - 1');
            if ($inverted) {$result[] = array('i_Pastel1', __('Pastel', 'live-weather-station') . ' - 1 ('  . $r . ')');}
            $result[] = array('Pastel2', __('Pastel', 'live-weather-station') . ' - 2');
            if ($inverted) {$result[] = array('i_Pastel2', __('Pastel', 'live-weather-station') . ' - 2 ('  . $r . ')');}
            $result[] = array('Set1', __('Set', 'live-weather-station') . ' - 1');
            if ($inverted) {$result[] = array('i_Set1', __('Set', 'live-weather-station') . ' - 1 ('  . $r . ')');}
            $result[] = array('Set2', __('Set', 'live-weather-station') . ' - 2');
            if ($inverted) {$result[] = array('i_Set2', __('Set', 'live-weather-station') . ' - 2 ('  . $r . ')');}
            $result[] = array('Set3', __('Set', 'live-weather-station') . ' - 3');
            if ($inverted) {$result[] = array('i_Set3', __('Set', 'live-weather-station') . ' - 3 ('  . $r . ')');}
        }
        if ((bool)get_option('live_weather_station_advanced_mode')) {
            $result[] = array('cst', '//->' . ucfirst(__('Custom', 'live-weather-station')));
            foreach (Options::get_cschemes() as $key=>$cs) {
                $result[] = array($key, $cs['name']);
                if ($inverted) {$result[] = array('i_' . $key, $cs['name'] . ' ('  . $r . ')');}
            }
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
    private function get_readable_timezone($timezone) {
        $result = str_replace('/', ' / ', $timezone);
        $result = str_replace('_', ' ', $result);
        $result = str_replace('DU', ' d\'U', $result);
        return $result;
    }

    /**
     * Get an array containing timezones names.
     *
     * @return  array  An associative array with timezones (with country breakdowns) ready to convert to a JS array.
     * @since    2.0.0
     */
    protected function get_timezones_js_array() {
        $result = [];
        $country_codes = [];
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $continue = array('BU', 'CS', 'DY', 'EU', 'HV', 'FX', 'NH', 'QO', 'RH', 'SU', 'TP', 'YU', 'ZR', 'ZZ');
        $locale = lws_get_display_locale();
        for ($i=0; $i<26; $i++) {
            for ($j=0; $j<26; $j++) {
                $s = $letters[$i].$letters[$j];
                if (in_array($s, $continue)) {
                    continue;
                }
                $t = lws_get_region_name('-'.$s, $locale);
                if ($s != $t || !EnvManager::is_locale_operational()) {
                    $country_codes[] = $s;
                }
            }
        }
        foreach ($country_codes as $cc) {
            $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $cc);
            if (count($timezones) == 0) {
                switch ($cc) {
                    case 'AN':
                        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ATLANTIC);
                        break;
                    case 'CP':
                        $timezones = array('Pacific/Tahiti');
                        break;
                    case 'DG':
                        $timezones = array('Indian/Chagos');
                        break;
                    case 'EA':
                        $timezones = array('Africa/Ceuta');
                        break;
                    case 'HM':
                        $timezones = array('Antarctica/Mawson');
                        break;
                    case 'IC':
                        $timezones = array('Europe/Madrid');
                        break;
                    default: // AC, BV, SH, TA, ...
                        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::UTC);
                }
            }
            $timezone_offsets = array();
            foreach( $timezones as $timezone ) {
                $tz = new \DateTimeZone($timezone);
                $timezone_offsets[$timezone] = $tz->getOffset(new \DateTime);
            }
            $timezone_list = array();
            foreach( $timezone_offsets as $timezone => $offset ) {
                $offset_prefix = $offset < 0 ? '-' : '+';
                $offset_formatted = gmdate( 'H:i', abs($offset) );
                $elem = array();
                $elem[0] = $timezone;
                $elem[1] = '(UTC'.$offset_prefix.$offset_formatted.') '.$this->get_readable_timezone($timezone);
                $timezone_list[] = $elem;
            }
            $result[$cc] = $timezone_list;
        }
        return $result;
    }

    /**
     * Get the available history modes.
     *
     * @return array An array containing the history modes.
     * @since 3.2.0
     */
    protected function get_history_collect_js_array() {
        $result = array();
        $result[] = array(0, __('None', 'live-weather-station'));
        $result[] = array(1, __('Only daily data', 'live-weather-station'));
        $result[] = array(2, __('Daily and historical data', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the available media conservation duration.
     *
     * @return array An array containing the media conservation duration.
     * @since 3.6.0
     */
    protected function get_media_conservation_js_array() {
        $result = array();
        $result[] = array(7, __('One week', 'live-weather-station'));
        $result[] = array(31, __('One month', 'live-weather-station'));
        $result[] = array(365, __('One year', 'live-weather-station'));
        $result[] = array(0, __('Unlimited', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the available history modes.
     *
     * @return array An array containing the history modes.
     * @since 3.2.0
     */
    protected function get_fa_mode_js_array() {
        $theme = wp_get_theme();
        $result = array();
        $result[] = array(0, sprintf(__('%1$s outputs Font Awesome %2$s', 'live-weather-station'), LWS_PLUGIN_NAME, 4));
        $result[] = array(1, sprintf(__('%1$s outputs Font Awesome %2$s as %3$s', 'live-weather-station'), LWS_PLUGIN_NAME, 5, 'CSS'));
        $result[] = array(2, sprintf(__('%1$s outputs Font Awesome %2$s as %3$s', 'live-weather-station'), LWS_PLUGIN_NAME, 5, 'JS+SVG'));
        $result[] = array(3, sprintf(__('%1$s outputs Font Awesome %2$s', 'live-weather-station'), $theme->name, 4));
        $result[] = array(4, sprintf(__('%1$s outputs Font Awesome %2$s as %3$s', 'live-weather-station'), $theme->name, 5, 'CSS'));
        $result[] = array(5, sprintf(__('%1$s outputs Font Awesome %2$s as %3$s', 'live-weather-station'), $theme->name, 5, 'JS+SVG'));
        return $result;
    }

    /**
     * Get the standard/scientific modes.
     *
     * @return array An array containing the standard/scientific modes.
     * @since 3.2.0
     */
    protected function get_history_full_js_array() {
        $result = array();
        $result[] = array(0, __('Standard', 'live-weather-station'));
        $result[] = array(1, __('Scientific', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the available quota management modes.
     *
     * @return array An array containing the quota management modes.
     * @since 3.2.0
     */
    protected function get_quota_js_array() {
        $result = array();
        $result[] = array(0, __('Always perform queries', 'live-weather-station'));
        $result[] = array(1, __('Warn but perform queries anyway', 'live-weather-station'));
        $result[] = array(2, __('Drop queries just before exceeding the quota', 'live-weather-station'));
        $result[] = array(3, __('Distribute queries in the remaining time', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the available log levels.
     *
     * @return array An array containing the available log levels.
     * @since 3.0.0
     */
    protected function get_log_level_js_array() {
        $result = array();
        for ($i=0; $i <= 7; $i++) {
            $result[] = array($i, Logger::get_name_by_id($i));
        }
        return $result;
    }

    /**
     * Get plans for OpenWeatherMap API access.
     *
     * @return array An array containing the available plans for API access.
     * @since 3.0.0
     */
    public function get_owm_plan_array() {
        $result = array();
        $result[] = array(0, 'Free');
        $result[] = array(1, 'Startup');
        $result[] = array(2, 'Developer');
        $result[] = array(3, 'Professional');
        $result[] = array(4, 'Enterprise');
        return $result;
    }

    /**
     * Get plans for WeatherUnderground API access.
     *
     * @return array An array containing the available plans for API access.
     * @since 3.0.0
     */
    public function get_wug_plan_array() {
        $result = array();
        $result[] = array(0, 'Stratus - Developer (free)');
        $result[] = array(1, 'Stratus - Drizzle');
        $result[] = array(2, 'Stratus - Shower');
        $result[] = array(3, 'Stratus - Downpour');
        $result[] = array(4, 'Cumulus - Developer (free)');
        $result[] = array(5, 'Cumulus - Drizzle');
        $result[] = array(6, 'Cumulus - Shower');
        $result[] = array(7, 'Cumulus - Downpour');
        $result[] = array(8, 'Anvil - Developer (free)');
        $result[] = array(9, 'Anvil - Drizzle');
        $result[] = array(10, 'Anvil - Shower');
        $result[] = array(11, 'Anvil - Downpour');
        return $result;
    }

    /**
     * Get plans for Windy API access.
     *
     * @return array An array containing the available plans for API access.
     * @since 3.7.0
     */
    public function get_windy_plan_array() {
        $result = array();
        $result[] = array(0, __('Free', 'live-weather-station'));
        $result[] = array(1, __('Paid', 'live-weather-station'));
        return $result;
    }

    /**
     * Get plans for Thunderforest API access.
     *
     * @return array An array containing the available plans for API access.
     * @since 3.7.0
     */
    public function get_thunderforest_plan_array() {
        $result = array();
        $result[] = array(0, __('Hobby Project (free)', 'live-weather-station'));
        $result[] = array(1, __('Solo Developer', 'live-weather-station'));
        $result[] = array(2, __('Small Business', 'live-weather-station'));
        $result[] = array(3, __('Large Business', 'live-weather-station'));
        return $result;
    }

    /**
     * Get plans for Mapbox API access.
     *
     * @return array An array containing the available plans for API access.
     * @since 3.7.0
     */
    public function get_mapbox_plan_array() {
        $result = array();
        $result[] = array(0, __('Pay-As-You-Go', 'live-weather-station'));
        $result[] = array(1, __('Commercial', 'live-weather-station'));
        $result[] = array(2, __('Enterprise', 'live-weather-station'));
        return $result;
    }

    /**
     * Get plans for Maptiler API access.
     *
     * @return array An array containing the available plans for API access.
     * @since 3.7.4
     */
    public function get_maptiler_plan_array() {
        $result = array();
        $result[] = array(0, __('Free', 'live-weather-station'));
        $result[] = array(1, lws__('Flex', 'live-weather-station'));
        $result[] = array(2, __('Unlimited', 'live-weather-station'));
        return $result;
    }

    /**
     * Get models for stations.
     *
     * @return array An array containing the available models.
     * @since 3.0.0
     */
    public function get_models_array($force = array()) {
        $result = array();
        $models = array();
        $models[] = 'N/A';
        $models[] = '1-Wire - Weather Station';
        $models[] = 'AcuRite - 1025';
        $models[] = 'AcuRite - 1035';
        $models[] = 'AcuRite - 1525';
        $models[] = 'AcuRite - 3-in-1 Pro';
        $models[] = 'AcuRite - 5-in-1 Pro';
        $models[] = 'AcuRite - Internet Bridge';
        $models[] = 'Airmar - 150WX';
        $models[] = 'Airmar - PB100';
        $models[] = 'Argent Data Systems - WS1';
        $models[] = 'Ambient Weather - WS-0262';
        $models[] = 'Ambient Weather - WS-0262A';
        $models[] = 'Ambient Weather - WS-0800-IP';
        $models[] = 'Ambient Weather - WS-0900-IP';
        $models[] = 'Ambient Weather - WS-1001-WiFi';
        $models[] = 'Ambient Weather - WS-1002-WiFi';
        $models[] = 'Ambient Weather - WS-1080';
        $models[] = 'Ambient Weather - WS-1090';
        $models[] = 'Ambient Weather - WS-1200-IP';
        $models[] = 'Ambient Weather - WS-1201-IP';
        $models[] = 'Ambient Weather - WS-1400-IP';
        $models[] = 'Ambient Weather - WS-1401-IP';
        $models[] = 'Ambient Weather - WS-1600-IP';
        $models[] = 'Ambient Weather - WS-2080';
        $models[] = 'Ambient Weather - WS-2090';
        $models[] = 'Ambient Weather - WS-2095';
        $models[] = 'Ambient Weather - WS-2902';
        $models[] = 'Ambient Weather - WS-8478';
        $models[] = 'Ambient Weather - WS-8482';
        $models[] = 'BloomSky - Sky1';
        $models[] = 'BloomSky - Sky1+Storm';
        $models[] = 'BloomSky - Sky2';
        $models[] = 'BloomSky - Sky2+Storm';
        $models[] = 'Campbell Scientific - CR1000 Series';
        $models[] = 'Campbell Scientific - CR200X Series';
        $models[] = 'Campbell Scientific - CR3000 Series';
        $models[] = 'Campbell Scientific - CR800 Series';
        $models[] = 'Columbia - Capricorn';
        $models[] = 'Columbia - Capricorn FLX';
        $models[] = 'Columbia - Magellan';
        $models[] = 'Columbia - Magellan MX';
        $models[] = 'Columbia - Orion';
        $models[] = 'Columbia - Pulsar';
        $models[] = 'Davis Instruments - Vantage Pro';
        $models[] = 'Davis Instruments - Vantage Pro Plus';
        $models[] = 'Davis Instruments - Vantage Pro2';
        $models[] = 'Davis Instruments - Vantage Pro2 Plus';
        $models[] = 'Davis Instruments - Vantage Vue';
        $models[] = 'Davis Instruments - Weather Monitor II';
        $models[] = 'Dyacon - MS-100';
        $models[] = 'EnvironData - Weather Maestro';
        $models[] = 'Fine Offset - HP Series';
        $models[] = 'Fine Offset - WA Series';
        $models[] = 'Fine Offset - WH Series';
        $models[] = 'Fine Offset - WS Series';
        $models[] = 'Hideki - TE923';
        $models[] = 'Honeywell Meade - TFA / TE Series';
        $models[] = 'Honeywell Meade - TN Series';
        $models[] = 'La Crosse - C84612';
        $models[] = 'La Crosse - WS-1500 Series';
        $models[] = 'La Crosse - WS-1600 Series';
        $models[] = 'La Crosse - WS-1900 Series';
        $models[] = 'La Crosse - WS-2000 Series';
        $models[] = 'Luftt - WS600/601';
        $models[] = 'Maede - TE821';
        $models[] = 'Maede - TE827';
        $models[] = 'Maede - TE923';
        $models[] = 'Maede - TE928';
        $models[] = 'Maximum Inc. - Blackwatch';
        $models[] = 'Maximum Inc. - Catalina';
        $models[] = 'Maximum Inc. - Executive';
        $models[] = 'Maximum Inc. - Hatteras';
        $models[] = 'Maximum Inc. - Marconi';
        $models[] = 'Maximum Inc. - Montauk';
        $models[] = 'Maximum Inc. - Newport';
        $models[] = 'Maximum Inc. - Observer';
        $models[] = 'Maximum Inc. - Portland';
        $models[] = 'Maximum Inc. - Professional';
        $models[] = 'Maximum Inc. - Sorcerer';
        $models[] = 'Maximum Inc. - WeatherMaster';
        $models[] = 'MEA - ETO Weather Station';
        $models[] = 'MEA - Feedlot Weather Station';
        $models[] = 'MEA - Junior Weather Station';
        $models[] = 'MEA - Portable Weather Station';
        $models[] = 'MEA - Premium Weather Station';
        $models[] = 'MEA - Spray Drift Weather Station';
        $models[] = 'Netatmo - Personal Weather Station';
        $models[] = 'Netatmo - Healthy Home Coach';
        $models[] = 'New Mountain Innovations - NM100';
        $models[] = 'New Mountain Innovations - NM150';
        $models[] = 'Onset - HOBO';
        $models[] = 'Oregon Scientific - LW301';
        $models[] = 'Oregon Scientific - WMR88';
        $models[] = 'Oregon Scientific - WMR100 Series';
        $models[] = 'Oregon Scientific - WMR200 Series';
        $models[] = 'Oregon Scientific - WMR300 Series';
        $models[] = 'Oregon Scientific - WMR900 Series';
        $models[] = 'Oregon Scientific - WMRS200';
        $models[] = 'Peet Bros - Ultimeter 100 Series';
        $models[] = 'Peet Bros - Ultimeter 800 Series';
        $models[] = 'Peet Bros - Ultimeter 2000 Series';
        $models[] = 'Peet Bros - Ultimeter 2100 Series';
        $models[] = 'Pioupiou V1';
        $models[] = 'Pioupiou V2';
        $models[] = 'Radioshack - Wireless';
        $models[] = 'Radioshack - WX200';
        $models[] = 'RainWise - AgroMET';
        $models[] = 'RainWise - CC3000';
        $models[] = 'RainWise - MKIII';
        $models[] = 'RainWise - System 12 WeatherLog';
        $models[] = 'RainWise - WS-1000CC';
        $models[] = 'RainWise - WS-2000';
        $models[] = 'Reinhardt - 5MVH';
        $models[] = 'Texas Weather Instruments - OneWire';
        $models[] = 'Texas Weather Instruments - WLS';
        $models[] = 'Texas Weather Instruments - WPS';
        $models[] = 'Texas Weather Instruments - WRx';
        $models[] = 'TFA-Dostmann - KlimaLogg Pro';
        $models[] = 'Ventus - W800 Series';
        $models[] = 'Wario - ME11/12';
        $models[] = 'Weather Hawk - 500 Series';
        $models[] = 'Weather Hawk - 600 Series';
        $models[] = 'Weather Hawk - Signature Series';
        $models[] = 'WeatherFlow - Smart Weather Station';
        foreach ($models as $model) {
            if (empty($force)) {
                $result[] = $model;
            }
            else {
                foreach ($force as $f) {
                    if (strpos($model, $f) !== false) {
                        $result[] = $model;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get server types.
     *
     * @return array An array containing the available server types (0=direct API access).
     * @since 3.0.0
     */
    public function get_server_type_array() {
        $result = array();
        $result[] = array(1, __('Local file', 'live-weather-station'));
        $result[] = array(2, __('Web server (HTTP)', 'live-weather-station'));
        $result[] = array(3, __('Web server (HTTPS)', 'live-weather-station'));
        $result[] = array(4, __('File server (FTP)', 'live-weather-station'));
        $result[] = array(5, __('File server (FTPS)', 'live-weather-station'));
        return $result;
    }

    /**
     * Get cron activity types.
     *
     * @return array An array containing the available cron activity types.
     * @since 3.3.0
     */
    public function get_cron_speed_array() {
        $result = array();
        $result[] = array(0, __('Standard', 'live-weather-station'));
        $result[] = array(1, __('Fast', 'live-weather-station'));
        return $result;
    }

    /**
     * Get export formats.
     *
     * @return array An array containing the available export formats.
     * @since 3.7.0
     */
    public static function _get_export_formats_array() {
        $result = array();
        $result['csv'] = array('name' => 'CSV', 'description' => __('A text file format, presenting the data as lines of comma-separated values. This type of format can be read by the majority of spreadsheet software (Calc, Excel, Numbers, etc.) and allows all the data manipulation you want.', 'live-weather-station'));
        $result['dsvp'] = array('name' => 'DSV (pipe)', 'description' => __('A text file format, presenting the data as lines of pipe-separated values. You can use it for plain text processing.', 'live-weather-station'));
        $result['dsvs'] = array('name' => 'DSV (semicolon)', 'description' => __('A text file format, presenting the data as lines of semicolon-separated values. You can use it for plain text processing.', 'live-weather-station'));
        $result['ndjson'] = array('name' => 'ND-JSON', 'description' => sprintf(__('A standard format used by %s to allow export/import between different WordPress instances. If you want to save your historical data so you can import it into another WordPress site (or another station), this is the ideal format.', 'live-weather-station'), LWS_PLUGIN_NAME));
        $result['tsv'] = array('name' => 'TSV', 'description' => __('A text file format, presenting the data as lines of tab-separated values. You can use it for plain text processing.', 'live-weather-station'));
        return $result;
    }

    /**
     * Get export formats.
     *
     * @return array An array containing the available export formats.
     * @since 3.7.0
     */
    public function get_export_formats_array() {
        return self::_get_export_formats_array();
    }

    /**
     * Get import formats.
     *
     * @param string $service Optional. The service for which to generate the array. May be 'none', 'all' or a service name like 'netatmo' or 'weatherflow'.
     * @return array An array containing the available import formats.
     * @since 3.7.0
     */
    public static function _get_import_formats_array($service = 'none') {
        $result = array();
        $result['ndjson'] = array('name' => 'ND-JSON', 'description' => sprintf(__('Import from a file previously exported by %s.', 'live-weather-station'), LWS_PLUGIN_NAME));
        if ($service === 'all' || $service === 'netatmo' || $service === 'netatmohc') {
            $result['netatmo'] = array('name' => __('Netatmo cloud services', 'live-weather-station'), 'description' => __('Import data stored by Netatmo for your station or device.', 'live-weather-station'));
        }
        if ($service === 'all' || $service === 'pioupiou') {
            $result['pioupiou'] = array('name' => __('Pioupiou cloud services', 'live-weather-station'), 'description' => __('Import data stored on Pioupiou servers for this sensor.', 'live-weather-station'));
        }
        return $result;
    }

    /**
     * Get import formats.
     *
     * @param string $service Optional. The service for which to generate the array. May be 'none', 'all' or a service name like 'netatmo' or 'weatherflow'.
     * @return array An array containing the available import formats.
     * @since 3.7.0
     */
    public function get_import_formats_array($service = 'none') {
        return self::_get_import_formats_array($service);
    }

    /**
     * Get map overlay array for Windy.
     *
     * @return array An array containing map overlay for Windy ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_windymap_overlay_js_array() {
        $result = array();
        $result[] = array('wind',  __('Wind', 'live-weather-station'));
        $result[] = array('temp',  __('Temperature', 'live-weather-station'));
        $result[] = array('rain',  __('Rain', 'live-weather-station'));
        $result[] = array('clouds',  __('Clouds', 'live-weather-station'));
        $result[] = array('pressure',  __('Pressure', 'live-weather-station'));
        $result[] = array('currents',  __('Currents', 'live-weather-station'));
        $result[] = array('waves',  __('Waves', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map isolines array for Windy.
     *
     * @return array An array containing map isolines for Windy ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_windymap_isolines_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('pressure',  __('Pressure', 'live-weather-station'));
        //$result[] = array('temp',  __('Temperature', 'live-weather-station'));
        //$result[] = array('deg0',  __('Freezing altitude', 'live-weather-station'));
        //$result[] = array('gh',  __('Geopotential height', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map footer array for Windy.
     *
     * @return array An array containing map footers for Windy ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_windymap_footer_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('legend',  __('Legend', 'live-weather-station'));
        $result[] = array('calendar',  __('Calendar', 'live-weather-station'));
        $result[] = array('both',  __('Full', 'live-weather-station'));
        return $result;
    }

    /**
     * Get activated array for Windy.
     *
     * @return array An array containing activated / deactivated to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_activated_js_array() {
        $result = array();
        $result[] = array('on',  __('Enabled', 'live-weather-station'));
        $result[] = array('off',  __('Disabled', 'live-weather-station'));
        return $result;
    }

    /**
     * Get station selector array for maps.
     *
     * @return array An array containing stations selector to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_station_selector_js_array() {
        $result = array();
        $result[] = array('all',  __('All', 'live-weather-station'));
        $result[] = array('select',  __('Selection', 'live-weather-station'));
        return $result;
    }

    /**
     * Get all stations array for maps.
     *
     * @return array An array containing all stations selector to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_stations_selector_js_array() {
        $result = array();
        $stations = $this->get_ordered_stations_list();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $result[] = array($station['guid'], $station['station_name']);
            }
        }
        return $result;
    }

    /**
     * Get marker type array for maps.
     *
     * @return array An array containing marker type to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_map_marker_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('pin',  __('Pin', 'live-weather-station'));
        //$result[] = array('old',  __('Oldies', 'live-weather-station'));
        $result[] = array('logo',  __('Logo', 'live-weather-station'));
        $result[] = array('brand',  __('Station\'s brand', 'live-weather-station'));
        $result[] = array('weather:current',  __('Weather: current conditions', 'live-weather-station'));
        if (LWS_PREVIEW) {
            $result[] = array('weather:temp',  lws__('Weather: temperature', 'live-weather-station'));
            $result[] = array('weather:colortemp',  lws__('Weather: colored temperature', 'live-weather-station'));
        }
        $result[] = array('weather:wind',  __('Weather: wind', 'live-weather-station'));
        return $result;
    }

    /**
     * Get marker data array for maps.
     *
     * @return array An array containing marker data to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_map_data_js_array() {
        $result = array();
        $result[] = array('current',  __('Current records', 'live-weather-station'));
        $result[] = array('calendar',  __('Calendar', 'live-weather-station'));
        $result[] = array('station',  __('Station\'s information', 'live-weather-station'));
        return $result;
    }

    /**
     * Get marker style array for maps.
     *
     * @return array An array containing marker style to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_map_style_js_array() {
        $result = array();
        $result[] = array('minimalist',  __('Minimalist', 'live-weather-station'));
        $result[] = array('standard',  __('Standard', 'live-weather-station'));
        $result[] = array('extended',  __('Extended', 'live-weather-station'));
        return $result;
    }

    /**
     * Get contrast array for maps.
     *
     * @return array An array containing marker style to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_map_contrast_js_array() {
        $result = array();
        $result[] = array('light',  __('Light', 'live-weather-station'));
        $result[] = array('medium',  __('Medium', 'live-weather-station'));
        $result[] = array('dark',  __('Dark', 'live-weather-station'));
        return $result;
    }

    /**
     * Get shadow array for maps.
     *
     * @return array An array containing marker shadow to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_map_shadow_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('medium',  __('Medium', 'live-weather-station'));
        $result[] = array('dark',  __('Dark', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map overlay array for Stamen.
     *
     * @return array An array containing map overlay for Stamen ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_stamenmap_overlay_js_array() {
        $result = array();
        $result[] = array('terrain',  __('Terrain', 'live-weather-station'));
        $result[] = array('terrain-background',  __('Terrain (background)', 'live-weather-station'));
        $result[] = array('terrain-classic',  __('Terrain (classical)', 'live-weather-station'));
        $result[] = array('toner',  __('Toner', 'live-weather-station'));
        $result[] = array('toner-background',  __('Toner (background)', 'live-weather-station'));
        $result[] = array('toner-lite',  __('Toner (lite)', 'live-weather-station'));
        $result[] = array('watercolor',  __('Watercolor', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map overlay array for Thunderforest.
     *
     * @return array An array containing map overlay for Thunderforest ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_thunderforestmap_overlay_js_array() {
        $result = array();
        $result[] = array('landscape',  __('Landscape', 'live-weather-station'));
        $result[] = array('mobile-atlas',  __('Mobile atlas', 'live-weather-station'));
        $result[] = array('cycle',  __('OpenCycleMap', 'live-weather-station'));
        $result[] = array('outdoors',  __('Outdoors', 'live-weather-station'));
        $result[] = array('pioneer',  __('Pioneer', 'live-weather-station'));
        $result[] = array('neighbourhood',  __('Neighbourhood', 'live-weather-station'));
        $result[] = array('spinal-map',  __('Spinal map', 'live-weather-station'));
        $result[] = array('transport-dark',  __('Transport (dark)', 'live-weather-station'));
        $result[] = array('transport',  __('Transport (light)', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map overlay array for Mapbox.
     *
     * @return array An array containing map overlay for Mapbox ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_mapboxmap_overlay_js_array() {
        $result = array();
        $result[] = array('comic',  __('Comic', 'live-weather-station'));
        $result[] = array('dark',  __('Dark', 'live-weather-station'));
        $result[] = array('emerald',  __('Emerald', 'live-weather-station'));
        $result[] = array('high-contrast',  __('High-contrast', 'live-weather-station'));
        $result[] = array('light',  __('Light', 'live-weather-station'));
        $result[] = array('pencil',  __('Pencil', 'live-weather-station'));
        $result[] = array('pirates',  __('Pirates', 'live-weather-station'));
        $result[] = array('outdoors',  __('Outdoors', 'live-weather-station'));
        $result[] = array('run-bike-hike',  __('Run, bike, hike', 'live-weather-station'));
        $result[] = array('satellite',  __('Satellite', 'live-weather-station'));
        $result[] = array('streets-satellite',  __('Satellite and streets', 'live-weather-station'));
        $result[] = array('streets',  __('Streets', 'live-weather-station'));
        $result[] = array('streets-basic',  __('Streets (basic)', 'live-weather-station'));
        $result[] = array('terrain-rgb',  __('Terrain RGB', 'live-weather-station'));
        $result[] = array('wheatpaste',  __('Wheatpaste', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map overlay array for MapTiler.
     *
     * @return array An array containing map overlay for MapTiler ready to convert to a JS array.
     * @since 3.8.0
     */
    protected function get_maptiler_overlay_js_array() {
        $result = array();
        $result[] = array('styles:basic',  lws__('Basic', 'live-weather-station'));
        $result[] = array('styles:bright',  lws__('Bright', 'live-weather-station'));
        $result[] = array('styles:darkmatter',  lws__('Dark matter', 'live-weather-station'));
        $result[] = array('styles:hybrid',  lws__('Satellite hybrid', 'live-weather-station'));
        $result[] = array('styles:positron',  lws__('Positron', 'live-weather-station'));
        $result[] = array('styles:streets',  lws__('Streets', 'live-weather-station'));
        $result[] = array('styles:topo',  lws__('Topo', 'live-weather-station'));
        $result[] = array('styles:voyager',  lws__('Voyager', 'live-weather-station'));
        $result[] = array('data:hillshades',  lws__('Hillshades', 'live-weather-station'));
        $result[] = array('data:terrain-rgb',  lws__('RGB terrain', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map overlay array for OpenWeatherMap.
     *
     * @return array An array containing map overlay for OpenWeatherMap ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_openweathermapmap_overlay_js_array() {
        $result = array();
        $result[] = array('owm:clouds_new',  __('Weather: clouds', 'live-weather-station'));
        $result[] = array('owm:precipitation_new',  __('Weather: precipitation', 'live-weather-station'));
        $result[] = array('owm:pressure_new',  __('Weather: pressure', 'live-weather-station'));
        $result[] = array('owm:rain',  __('Weather: rain', 'live-weather-station'));
        $result[] = array('owm:temp_new',  __('Weather: temperature', 'live-weather-station'));
        $result[] = array('owm:snow',  __('Weather: snow', 'live-weather-station'));
        $result[] = array('owm:wind_new',  __('Weather: wind', 'live-weather-station'));
        $result[] = array('vane:rgb',  __('Vegetation: RGB', 'live-weather-station'));
        $result[] = array('vane:nir',  __('Vegetation: NIR', 'live-weather-station'));
        $result[] = array('vane:ndvi',  __('Vegetation: NDVI', 'live-weather-station'));
        $result[] = array('vane:ndwi',  __('Vegetation: NDWI', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map overlay array for Navionics.
     *
     * @return array An array containing map overlay for OpenWeatherMap ready to convert to a JS array.
     * @since 3.8.0
     */
    protected function get_navionicsmap_overlay_js_array() {
        $result = array();
        $result[] = array('JNC.NAVIONICS_CHARTS.NAUTICAL',  lws__('Nautical map', 'live-weather-station'));
        $result[] = array('JNC.NAVIONICS_CHARTS.SONARCHART',  lws__('Sonar map', 'live-weather-station'));
        $result[] = array('JNC.NAVIONICS_CHARTS.SKI',  lws__('Ski map', 'live-weather-station'));
        return $result;
    }

    /**
     * Get map safety depth array for Navionics.
     *
     * @return array An array containing map safety depth for OpenWeatherMap ready to convert to a JS array.
     * @since 3.8.0
     */
    protected function get_navionicsmap_depth_js_array() {
        $result = array();
        $result[] = array('JNC.SAFETY_DEPTH_LEVEL.LEVEL0',  lws__('20 meters, 60 feet, 10 fathoms', 'live-weather-station'));
        $result[] = array('JNC.SAFETY_DEPTH_LEVEL.LEVEL1',  lws__('10 meters, 30 feet, 5 fathoms', 'live-weather-station'));
        $result[] = array('JNC.SAFETY_DEPTH_LEVEL.LEVEL2',  lws__('5 meters, 18 feet, 2 fathoms', 'live-weather-station'));
        $result[] = array('JNC.SAFETY_DEPTH_LEVEL.LEVEL3',  lws__('2 meters, 6 feet, 1 fathom', 'live-weather-station'));
        $result[] = array('JNC.SAFETY_DEPTH_LEVEL.LEVEL4',  lws__('None', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the basemaps.
     *
     * @return array An array containing basemaps ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_basemap_js_array() {
        $result = array();
        $result[] = array('none',  __('None', 'live-weather-station'));
        $result[] = array('carto:light_all',  __('CARTO: light', 'live-weather-station'));
        $result[] = array('carto:light_nolabels',  __('CARTO: light - no labels', 'live-weather-station'));
        $result[] = array('carto:light_only_labels',  __('CARTO: light - only labels', 'live-weather-station'));
        $result[] = array('carto:dark_all',  __('CARTO: dark', 'live-weather-station'));
        $result[] = array('carto:dark_nolabels',  __('CARTO: dark - no labels', 'live-weather-station'));
        $result[] = array('carto:dark_only_labels',  __('CARTO: dark - only labels', 'live-weather-station'));
        return $result;
    }

    /**
     * Get the zoom map.
     *
     * @return array An array containing zooms ready to convert to a JS array.
     * @since 3.7.0
     */
    protected function get_zoom_js_array($min, $max) {
        $result = array();
        for ($i=$min; $i<=$max; $i++) {
            $result[] = array($i,  sprintf(__('Level %s', 'live-weather-station'), $i));
        }
        return $result;
    }

    /**
     * Get shadow position.
     *
     * @return array An array containing shadow position ready to convert to a JS array.
     * @since 3.7.5
     */
    protected function get_shadow_position_js_array() {
        $result = array();
        $result[] = array('none',  lws__('None', 'live-weather-station'));
        $result[] = array('top-left',  lws__('Top left', 'live-weather-station'));
        $result[] = array('top-center',  lws__('Top center', 'live-weather-station'));
        $result[] = array('top-right',  lws__('Top right', 'live-weather-station'));
        $result[] = array('middle-left',  lws__('Middle left', 'live-weather-station'));
        $result[] = array('middle-right',  lws__('Middle right', 'live-weather-station'));
        $result[] = array('bottom-left',  lws__('Bottom left', 'live-weather-station'));
        $result[] = array('bottom-center',  lws__('Bottom center', 'live-weather-station'));
        $result[] = array('bottom-right',  lws__('Bottom right', 'live-weather-station'));
        return $result;
    }

    /**
     * Get shadow length.
     *
     * @return array An array containing shadow length ready to convert to a JS array.
     * @since 3.7.5
     */
    protected function get_shadow_length_js_array() {
        $result = array();
        $result[] = array('close',  lws__('Close', 'live-weather-station'));
        $result[] = array('medium',  lws__('Medium', 'live-weather-station'));
        $result[] = array('distant',  lws__('Distant', 'live-weather-station'));
        $result[] = array('faraway',  lws__('Faraway', 'live-weather-station'));
        return $result;
    }

    /**
     * Get shadow diffusion.
     *
     * @return array An array containing shadow diffusion ready to convert to a JS array.
     * @since 3.7.5
     */
    protected function get_shadow_diffusion_js_array() {
        $result = array();
        $result[] = array('precise',  lws__('Precise', 'live-weather-station'));
        $result[] = array('medium',  lws__('Medium', 'live-weather-station'));
        $result[] = array('soft',  lws__('Soft', 'live-weather-station'));
        return $result;
    }

    /**
     * Get shadow obscurity.
     *
     * @return array An array containing shadow obscurity ready to convert to a JS array.
     * @since 3.7.5
     */
    protected function get_shadow_obscurity_js_array() {
        $result = array();
        $result[] = array('light',  lws__('Light', 'live-weather-station'));
        $result[] = array('medium',  lws__('Medium', 'live-weather-station'));
        $result[] = array('strong',  lws__('Strong', 'live-weather-station'));
        return $result;
    }

    /**
     * Get radius.
     *
     * @return array An array containing radius ready to convert to a JS array.
     * @since 3.7.5
     */
    protected function get_radius_js_array() {
        $result = array();
        $result[] = array('none',  lws__('None', 'live-weather-station'));
        $result[] = array('short',  lws__('Short', 'live-weather-station'));
        $result[] = array('medium',  lws__('Medium', 'live-weather-station'));
        $result[] = array('large',  lws__('Large', 'live-weather-station'));
        return $result;
    }
}