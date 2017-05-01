<?php

namespace WeatherStation\Data\Arrays;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Type\Description as Type_Description;
use WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector as OWM_Base_Collector;

/**
 * Arrays generator for javascript conversion.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Generator {

    use Type_Description;

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
     * Get measure's datas array.
     *
     * @param array $ref An array containing value of reference.
     * @param array $data An array containing measures.
     * @param string $mtype The type of the measurement.
     * @return array An array containing the measure.
     * @since 3.0.0
     */
    private function get_measure_array($ref, $data, $mtype) {
        $mvalue = 0;
        $ts = 0;
        $found = false;
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
        $result[] = array(__('Station ID', 'live-weather-station'), 'device_id', $this->get_td_device_id_format(array($ref['device_id'])));
        $result[] = array(__('Station name', 'live-weather-station'), 'device_name', $this->get_td_device_name_format(array($ref['device_name'])));
        $result[] = array(__('Module ID', 'live-weather-station'), 'module_id', $this->get_td_module_id_format(array($ref['module_id'])));
        $result[] = array(__('Module type', 'live-weather-station'), 'module_type', $this->get_td_module_type_format(array($ref['module_type'],$this->get_module_type($ref['module_type']))));
        $result[] = array(__('Module name', 'live-weather-station'), 'module_name', $this->get_td_module_name_format(array($ref['module_name'])));
        $result[] = array(__('Measurement timestamp', 'live-weather-station'), 'measure_timestamp', $this->get_td_time_format(array($ts, $this->get_date_from_mysql_utc($ts, $ref['loc_timezone']), $this->get_time_from_mysql_utc($ts, $ref['loc_timezone']), $this->get_time_diff_from_mysql_utc($ts))));
        $unit = $this->output_unit($mtype, false, $ref['module_type']);
        $result[] = array(__('Measurement type', 'live-weather-station'), 'measure_type', $this->get_td_measure_type_format(array($mtype,$this->get_measurement_type($mtype, false, $ref['module_type']),$unit['unit'],$unit['full'],$unit['long'])));
        switch ($mtype) {
            case 'battery':
            case 'signal':
            case 'health_idx':
            case 'cbi':
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
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_special_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']), $this->output_value($mvalue, $mtype, false, true, $ref['module_type']))));
                break;
            case 'temperature_trend':
            case 'pressure_trend':
            case 'moon_age':
            case 'moon_phase':
            case 'loc_timezone':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_trend_format(array($mvalue, $this->output_value($mvalue, $mtype, false, true, $ref['module_type']))));
                break;
            case 'aggregated':
            /*    $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_aggregated_value_format(array($mvalue, $this->output_value($mvalue, $mtype))));
                break;*/
            case 'outdoor':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_aggregated_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']))));
                break;
            case 'firmware':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_firmware_value_format(array($mvalue)));
                break;
            case 'loc_latitude':
            case 'loc_longitude':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_coordinate_value_format(array($mvalue, $this->output_coordinate($mvalue, $mtype, 1), $this->output_coordinate($mvalue, $mtype, 2), $this->output_coordinate($mvalue, $mtype, 3), $this->output_coordinate($mvalue, $mtype, 4), $this->output_coordinate($mvalue, $mtype, 5))));
                break;
            case 'heat_index':
            case 'humidex':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_simple_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']))));
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_wind_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']), $this->get_angle_text($mvalue), $this->get_angle_full_text($mvalue))));
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
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_time_format(array($mvalue, $this->get_date_from_utc($mvalue, $ref['loc_timezone']), $this->get_time_from_utc($mvalue, $ref['loc_timezone']), $this->get_time_diff_from_utc($mvalue))));
                break;
            case 'last_seen':
            case 'first_setup':
            case 'last_upgrade':
            case 'last_setup':
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_time_format(array($mvalue, $this->get_date_from_mysql_utc($mvalue, $ref['loc_timezone']), $this->get_time_from_mysql_utc($mvalue, $ref['loc_timezone']), $this->get_time_diff_from_mysql_utc($mvalue))));
                break;
            default:
                $result[] = array(__('Measurement value', 'live-weather-station'), 'measure_value', $this->get_td_value_format(array($mvalue, $this->output_value($mvalue, $mtype, false, false, $ref['module_type']), $this->output_value($mvalue, $mtype, true, false, $ref['module_type']))));
        }
        return $result;
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
     * @return array An array containing the module measure lines.
     * @since 3.0.0
     */
    private function get_module_array($ref, $data, $full=false, $aggregated=false, $reduced=false, $computed=false, $mono=false) {
        $result = array();
        $netatmo = OWM_Base_Collector::is_netatmo_station($ref['device_id']);
        $wug = OWM_Base_Collector::is_wug_station($ref['device_id']);
        $raw = OWM_Base_Collector::is_raw_station($ref['device_id']);
        $real = OWM_Base_Collector::is_real_station($ref['device_id']);
        switch (strtolower($ref['module_type'])) {
            case 'namain':
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('battery'), 'battery', ($reduced ? array() : $this->get_measure_array($ref, $data, 'battery')));
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    $result[] = array($this->get_measurement_type('signal'), 'signal', ($reduced ? array() : $this->get_measure_array($ref, $data, 'signal')));
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                    $result[] = array($this->get_measurement_type('first_setup'), 'first_setup', ($reduced ? array() : $this->get_measure_array($ref, $data, 'first_setup')));
                    $result[] = array($this->get_measurement_type('last_setup'), 'last_setup', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_setup')));
                    $result[] = array($this->get_measurement_type('last_upgrade'), 'last_upgrade', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_upgrade')));
                }
                if ($full && $wug) {
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                }
                if ($full && ($raw || $real)) {
                    $result[] = array($this->get_measurement_type('battery'), 'battery', ($reduced ? array() : $this->get_measure_array($ref, $data, 'battery')));
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    $result[] = array($this->get_measurement_type('signal'), 'signal', ($reduced ? array() : $this->get_measure_array($ref, $data, 'signal')));
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                    $result[] = array($this->get_measurement_type('loc_timezone'), 'loc_timezone', ($reduced ? array() : $this->get_measure_array($ref, $data, 'loc_timezone')));
                    $result[] = array($this->get_measurement_type('loc_altitude'), 'loc_altitude', ($reduced ? array() : $this->get_measure_array($ref, $data, 'loc_altitude')));
                    $result[] = array($this->get_measurement_type('loc_latitude'), 'loc_latitude', ($reduced ? array() : $this->get_measure_array($ref, $data, 'loc_latitude')));
                    $result[] = array($this->get_measurement_type('loc_longitude'), 'loc_longitude', ($reduced ? array() : $this->get_measure_array($ref, $data, 'loc_longitude')));
                }
                if ($netatmo) {
                    $result[] = array($this->get_measurement_type('co2'), 'co2', ($reduced ? array() : $this->get_measure_array($ref, $data, 'co2')));
                    $result[] = array($this->get_measurement_type('humidity'), 'humidity', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity')));
                    $result[] = array($this->get_measurement_type('noise'), 'noise', ($reduced ? array() : $this->get_measure_array($ref, $data, 'noise')));
                    $result[] = array($this->get_measurement_type('pressure'), 'pressure', ($reduced ? array() : $this->get_measure_array($ref, $data, 'pressure')));
                    $result[] = array($this->get_measurement_type('health_idx'), 'health_idx', ($reduced ? array() : $this->get_measure_array($ref, $data, 'health_idx')));
                }
                if ($wug || $real || $raw) {
                    $result[] = array($this->get_measurement_type('pressure'), 'pressure', ($reduced ? array() : $this->get_measure_array($ref, $data, 'pressure')));
                }
                if ($full && ($netatmo || $real || $raw)) {
                    $result[] = array($this->get_measurement_type('pressure_trend'), 'pressure_trend', ($reduced ? array() : $this->get_measure_array($ref, $data, 'pressure_trend')));
                }
                if (($full || $mono) && ($real || $raw)) {
                    $result[] = array($this->get_measurement_type('pressure_max'), 'pressure_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'pressure_max')));
                    $result[] = array($this->get_measurement_type('pressure_min'), 'pressure_min', ($reduced ? array() : $this->get_measure_array($ref, $data, 'pressure_min')));
                }
                if ($netatmo) {
                    $result[] = array($this->get_measurement_type('temperature'), 'temperature', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature')));
                }
                if (($full || $mono) && $netatmo) {
                        $result[] = array($this->get_measurement_type('temperature_max'), 'temperature_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_max')));
                        $result[] = array($this->get_measurement_type('temperature_min'), 'temperature_min', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_min')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('temperature_trend'), 'temperature_trend', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_trend')));
                }
                break;
            case 'namodule1': // Outdoor module
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('battery'), 'battery', ($reduced ? array() : $this->get_measure_array($ref, $data, 'battery')));
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    $result[] = array($this->get_measurement_type('signal'), 'signal', ($reduced ? array() : $this->get_measure_array($ref, $data, 'signal')));
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                    $result[] = array($this->get_measurement_type('last_setup'), 'last_setup', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_setup')));
                }
                if ($full && ($wug || $real || $raw)) {
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                $result[] = array($this->get_measurement_type('humidity'), 'humidity', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity')));
                if (($full || $mono) && $raw) {
                    $result[] = array($this->get_measurement_type('humidity_max'), 'humidity_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity_max')));
                    $result[] = array($this->get_measurement_type('humidity_min'), 'humidity_min', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity_min')));
                }
                $result[] = array($this->get_measurement_type('temperature'), 'temperature', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature')));
                if (($full || $mono) && !$wug) {
                    $result[] = array($this->get_measurement_type('temperature_max'), 'temperature_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_max')));
                    $result[] = array($this->get_measurement_type('temperature_min'), 'temperature_min', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_min')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('temperature_trend'), 'temperature_trend', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_trend')));
                }
                break;
            case 'namodule3': // Rain gauge
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('battery'), 'battery', ($reduced ? array() : $this->get_measure_array($ref, $data, 'battery')));
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    $result[] = array($this->get_measurement_type('signal'), 'signal', ($reduced ? array() : $this->get_measure_array($ref, $data, 'signal')));
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                    $result[] = array($this->get_measurement_type('last_setup'), 'last_setup', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_setup')));
                }
                if ($full && ($wug || $real || $raw)) {
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                if ($netatmo || $raw || $real) {
                    $result[] = array($this->get_measurement_type('rain', false, $ref['module_type']), 'rain', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain')));
                }
                if (!$raw) {
                    $result[] = array($this->get_measurement_type('rain_hour_aggregated', false, $ref['module_type']), 'rain_hour_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_hour_aggregated')));
                }
                $result[] = array($this->get_measurement_type('rain_day_aggregated', false, $ref['module_type']), 'rain_day_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_day_aggregated')));
                if ($raw) {
                    $result[] = array($this->get_measurement_type('rain_yesterday_aggregated', false, $ref['module_type']), 'rain_yesterday_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_yesterday_aggregated')));
                    $result[] = array($this->get_measurement_type('rain_month_aggregated', false, $ref['module_type']), 'rain_month_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_month_aggregated')));
                    $result[] = array($this->get_measurement_type('rain_season_aggregated', false, $ref['module_type']), 'rain_season_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_season_aggregated')));
                }
                if ($real) {
                    $result[] = array($this->get_measurement_type('rain_yesterday_aggregated', false, $ref['module_type']), 'rain_yesterday_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_yesterday_aggregated')));
                    $result[] = array($this->get_measurement_type('rain_month_aggregated', false, $ref['module_type']), 'rain_month_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_month_aggregated')));
                    $result[] = array($this->get_measurement_type('rain_year_aggregated', false, $ref['module_type']), 'rain_year_aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain_year_aggregated')));
                }
                break;
            case 'namodule2': // Wind gauge
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('battery'), 'battery', ($reduced ? array() : $this->get_measure_array($ref, $data, 'battery')));
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    $result[] = array($this->get_measurement_type('signal'), 'signal', ($reduced ? array() : $this->get_measure_array($ref, $data, 'signal')));
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                    $result[] = array($this->get_measurement_type('last_setup'), 'last_setup', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_setup')));
                }
                if ($full && ($wug || $real || $raw)) {
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                $result[] = array($this->get_measurement_type('windangle'), 'windangle', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windangle')));
                $result[] = array($this->get_measurement_type('windstrength'), 'windstrength', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windstrength')));
                $result[] = array($this->get_measurement_type('gustangle'), 'gustangle', ($reduced ? array() : $this->get_measure_array($ref, $data, 'gustangle')));
                $result[] = array($this->get_measurement_type('guststrength'), 'guststrength', ($reduced ? array() : $this->get_measure_array($ref, $data, 'guststrength')));
                if ($netatmo) {
                    $result[] = array($this->get_measurement_type('windangle_hour_max'), 'windangle_hour_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windangle_hour_max')));
                    $result[] = array($this->get_measurement_type('windstrength_hour_max'), 'windstrength_hour_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windstrength_hour_max')));
                    $result[] = array($this->get_measurement_type('windangle_day_max'), 'windangle_day_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windangle_day_max')));
                    $result[] = array($this->get_measurement_type('windstrength_day_max'), 'windstrength_day_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windstrength_day_max')));
                }
                break;
            case 'namodule4': // Additional indoor module
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('battery'), 'battery', ($reduced ? array() : $this->get_measure_array($ref, $data, 'battery')));
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    $result[] = array($this->get_measurement_type('signal'), 'signal', ($reduced ? array() : $this->get_measure_array($ref, $data, 'signal')));
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                    $result[] = array($this->get_measurement_type('last_setup'), 'last_setup', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_setup')));
                }
                if ($full && ($wug || $real || $raw)) {
                    $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                if ($netatmo) {
                    $result[] = array($this->get_measurement_type('co2'), 'co2', ($reduced ? array() : $this->get_measure_array($ref, $data, 'co2')));
                }
                $result[] = array($this->get_measurement_type('humidity'), 'humidity', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity')));
                $result[] = array($this->get_measurement_type('temperature'), 'temperature', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature')));
                $result[] = array($this->get_measurement_type('health_idx'), 'health_idx', ($reduced ? array() : $this->get_measure_array($ref, $data, 'health_idx')));
                if (($full || $mono) && ($netatmo || $raw)) {
                    $result[] = array($this->get_measurement_type('temperature_max'), 'temperature_max', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_max')));
                    $result[] = array($this->get_measurement_type('temperature_min'), 'temperature_min', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_min')));
                }
                if ($full && $netatmo) {
                    $result[] = array($this->get_measurement_type('temperature_trend'), 'temperature_trend', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_trend')));
                }
                break;

            case 'namodule9': // Additional indoor module
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                $result[] = array($this->get_measurement_type('last_seen'), 'last_seen', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_seen')));
                $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                $result[] = array($this->get_measurement_type('humidity'), 'humidity', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity')));
                $result[] = array($this->get_measurement_type('temperature'), 'temperature', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature')));
                $result[] = array($this->get_measurement_type('health_idx'), 'health_idx', ($reduced ? array() : $this->get_measure_array($ref, $data, 'health_idx')));
                break;
            case 'aggregated': // All modules aggregated in one
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                    $result[] = array($this->get_measurement_type('outdoor'), 'outdoor', ($reduced ? array() : $this->get_measure_array($ref, $data, 'outdoor')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                if ($netatmo) {
                    $result[] = array($this->get_measurement_type('co2'), 'co2', ($reduced ? array() : $this->get_measure_array($ref, $data, 'co2')));
                }
                if ($netatmo || $raw || $real) {
                    $result[] = array($this->get_measurement_type('humidity'), 'humidity', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity')));
                    $result[] = array($this->get_measurement_type('temperature'), 'temperature', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature')));
                    $result[] = array($this->get_measurement_type('health_idx'), 'health_idx', ($reduced ? array() : $this->get_measure_array($ref, $data, 'health_idx')));
                }
                break;
            case 'nacomputed': // Virtual module for computed values
                if ($computed) {
                    if ($aggregated) {
                        $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                    }
                    if ($full) {
                        $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                    }
                    if ($full) {
                        $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                        $result[] = array($this->get_measurement_type('temperature_ref'), 'temperature_ref', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature_ref')));
                        $result[] = array($this->get_measurement_type('humidity_ref'), 'humidity_ref', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity_ref')));
                        $result[] = array($this->get_measurement_type('wind_ref'), 'wind_ref', ($reduced ? array() : $this->get_measure_array($ref, $data, 'wind_ref')));

                    }
                    $result[] = array($this->get_measurement_type('dew_point'), 'dew_point', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dew_point')));
                    $result[] = array($this->get_measurement_type('frost_point'), 'frost_point', ($reduced ? array() : $this->get_measure_array($ref, $data, 'frost_point')));
                    $result[] = array($this->get_measurement_type('heat_index'), 'heat_index', ($reduced ? array() : $this->get_measure_array($ref, $data, 'heat_index')));
                    $result[] = array($this->get_measurement_type('humidex'), 'humidex', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidex')));
                    $result[] = array($this->get_measurement_type('wind_chill'), 'wind_chill', ($reduced ? array() : $this->get_measure_array($ref, $data, 'wind_chill')));
                    $result[] = array($this->get_measurement_type('cloud_ceiling'), 'cloud_ceiling', ($reduced ? array() : $this->get_measure_array($ref, $data, 'cloud_ceiling')));
                    $result[] = array($this->get_measurement_type('cbi'), 'cbi', ($reduced ? array() : $this->get_measure_array($ref, $data, 'cbi')));
                }
                break;
            case 'nacurrent': // Virtual module for current values from OpenWeatherMap.org
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                }
                $result[] = array($this->get_measurement_type('pressure'), 'pressure', ($reduced ? array() : $this->get_measure_array($ref, $data, 'pressure')));
                $result[] = array($this->get_measurement_type('humidity'), 'humidity', ($reduced ? array() : $this->get_measure_array($ref, $data, 'humidity')));
                $result[] = array($this->get_measurement_type('temperature'), 'temperature', ($reduced ? array() : $this->get_measure_array($ref, $data, 'temperature')));
                $result[] = array($this->get_measurement_type('rain'), 'rain', ($reduced ? array() : $this->get_measure_array($ref, $data, 'rain')));
                $result[] = array($this->get_measurement_type('snow'), 'snow', ($reduced ? array() : $this->get_measure_array($ref, $data, 'snow')));
                $result[] = array($this->get_measurement_type('windangle'), 'windangle', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windangle')));
                $result[] = array($this->get_measurement_type('windstrength'), 'windstrength', ($reduced ? array() : $this->get_measure_array($ref, $data, 'windstrength')));
                $result[] = array($this->get_measurement_type('cloudiness'), 'cloudiness', ($reduced ? array() : $this->get_measure_array($ref, $data, 'cloudiness')));
                break;
           case 'naephemer': // Virtual module for ephemeris
                if ($computed) {
                    if ($full) {
                        $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                    }
                    if ($full) {
                        $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                    }
                    $result[] = array($this->get_measurement_type('sunrise'), 'sunrise', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunrise')));
                    $result[] = array($this->get_measurement_type('sunrise_c'), 'sunrise_c', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunrise_c')));
                    $result[] = array($this->get_measurement_type('sunrise_n'), 'sunrise_n', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunrise_n')));
                    $result[] = array($this->get_measurement_type('sunrise_a'), 'sunrise_a', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunrise_a')));
                    $result[] = array($this->get_measurement_type('dawn_length_c'), 'dawn_length_c', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dawn_length_c')));
                    $result[] = array($this->get_measurement_type('dawn_length_n'), 'dawn_length_n', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dawn_length_n')));
                    $result[] = array($this->get_measurement_type('dawn_length_a'), 'dawn_length_a', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dawn_length_a')));
                    $result[] = array($this->get_measurement_type('sunset'), 'sunset', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunset')));
                    $result[] = array($this->get_measurement_type('sunset_c'), 'sunset_c', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunset_c')));
                    $result[] = array($this->get_measurement_type('sunset_n'), 'sunset_n', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunset_n')));
                    $result[] = array($this->get_measurement_type('sunset_a'), 'sunset_a', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sunset_a')));
                    $result[] = array($this->get_measurement_type('dusk_length_c'), 'dusk_length_c', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dusk_length_c')));
                    $result[] = array($this->get_measurement_type('dusk_length_n'), 'dusk_length_n', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dusk_length_n')));
                    $result[] = array($this->get_measurement_type('dusk_length_a'), 'dusk_length_a', ($reduced ? array() : $this->get_measure_array($ref, $data, 'dusk_length_a')));
                    $result[] = array($this->get_measurement_type('day_length'), 'day_length', ($reduced ? array() : $this->get_measure_array($ref, $data, 'day_length')));
                    $result[] = array($this->get_measurement_type('day_length_c'), 'day_length_c', ($reduced ? array() : $this->get_measure_array($ref, $data, 'day_length_c')));
                    $result[] = array($this->get_measurement_type('day_length_n'), 'day_length_n', ($reduced ? array() : $this->get_measure_array($ref, $data, 'day_length_n')));
                    $result[] = array($this->get_measurement_type('day_length_a'), 'day_length_a', ($reduced ? array() : $this->get_measure_array($ref, $data, 'day_length_a')));
                    $result[] = array($this->get_measurement_type('sun_distance'), 'sun_distance', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sun_distance')));
                    $result[] = array($this->get_measurement_type('sun_diameter'), 'sun_diameter', ($reduced ? array() : $this->get_measure_array($ref, $data, 'sun_diameter')));
                    $result[] = array($this->get_measurement_type('moonrise'), 'moonrise', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moonrise')));
                    $result[] = array($this->get_measurement_type('moonset'), 'moonset', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moonset')));
                    $result[] = array($this->get_measurement_type('moon_phase'), 'moon_phase', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moon_phase')));
                    $result[] = array($this->get_measurement_type('moon_age'), 'moon_age', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moon_age')));
                    $result[] = array($this->get_measurement_type('moon_illumination'), 'moon_illumination', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moon_illumination')));
                    $result[] = array($this->get_measurement_type('moon_distance'), 'moon_distance', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moon_distance')));
                    $result[] = array($this->get_measurement_type('moon_diameter'), 'moon_diameter', ($reduced ? array() : $this->get_measure_array($ref, $data, 'moon_diameter')));
                }
                break;
            case 'napollution': // Virtual module for pollution
                if ($aggregated) {
                    $result[] = array($this->get_measurement_type('aggregated'), 'aggregated', ($reduced ? array() : $this->get_measure_array($ref, $data, 'aggregated')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('last_refresh'), 'last_refresh', ($reduced ? array() : $this->get_measure_array($ref, $data, 'last_refresh')));
                }
                if ($full) {
                    $result[] = array($this->get_measurement_type('firmware'), 'firmware', ($reduced ? array() : $this->get_measure_array($ref, $data, 'firmware')));
                }
                $result[] = array($this->get_measurement_type('o3'), 'o3', ($reduced ? array() : $this->get_measure_array($ref, $data, 'o3')));
                if ($full ) {
                    $result[] = array($this->get_measurement_type('o3_distance'), 'o3_distance', ($reduced ? array() : $this->get_measure_array($ref, $data, 'o3_distance')));
                }
                $result[] = array($this->get_measurement_type('co'), 'co', ($reduced ? array() : $this->get_measure_array($ref, $data, 'co')));
                if ($full ) {
                    $result[] = array($this->get_measurement_type('co_distance'), 'co_distance', ($reduced ? array() : $this->get_measure_array($ref, $data, 'co_distance')));
                }
                break;
        }
        return array ($ref['module_name'], $ref['module_id'], $result);
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
     * @return array An array containing the available station's datas ready to convert to a JS array.
     * @since 3.0.0
     */
    protected function get_station_array($guid, $full=true, $aggregated=false, $reduced=false, $computed=false, $mono=false) {
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
            if ($aggregated  && ($netatmo || $wug || $raw || $real)) {
                $ref = array();
                $ref['device_id'] = $data['station']['station_id'];
                $ref['device_name'] = $data['station']['station_name'];
                $ref['module_id'] = 'aggregated';
                $ref['module_type'] = 'aggregated';
                $ref['module_name'] = __('[all modules]', 'live-weather-station');
                $ref['loc_timezone'] = $data['station']['loc_timezone'];
                $modules[] = $this->get_module_array($ref, $mainbase, $full, $aggregated, $reduced, $computed, $mono);
            }
            foreach ($data['module'] as $module) {
                $ref = array();
                $ref['device_id'] = $data['station']['station_id'];
                $ref['device_name'] = $data['station']['station_name'];
                $ref['module_id'] = $module['module_id'];
                $ref['module_type'] = $module['module_type'];
                $ref['module_name'] = $module['module_name'];
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
                $modules[] = $this->get_module_array($ref, $module, $full, $aggregated, $reduced, $computed, $mono);
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
     * @param array $guids Optional. An array of guids to get. Get data for all guids if not provided.
     * @return array An array containing the available station's datas ready to convert to a JS array.
     * @since 3.0.0
     */
    protected function get_all_stations_array($full=true, $aggregated=false, $reduced=false, $computed=false, $mono=false, $guids=array()) {
        $result = array();
        $stations = $this->get_stations_table_list();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                if (!empty($guids)) {
                    $todo = in_array($station['guid'], $guids);
                }
                else {
                    $todo = true;
                }
                if ($todo && ($station['comp_bas'] + $station['comp_ext'] + $station['comp_int'] + $station['comp_xtd'] + $station['comp_vrt']) > 0) {
                    $result[$station['guid']] = $this->get_station_array($station['guid'], $full, $aggregated, $reduced, $computed, $mono);
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
        $locale = get_display_locale();
        for ($i=0; $i<26; $i++) {
            for ($j=0; $j<26; $j++) {
                $s = $letters[$i].$letters[$j];
                if (in_array($s, $continue)) {
                    continue;
                }
                $t = \Locale::getDisplayRegion('-'.$s, $locale);
                if ($s != $t) {
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
     * Get models for stations.
     *
     * @return array An array containing the available models.
     * @since 3.0.0
     */
    public function get_models_array() {
        $result = array();
        $result[] = 'N/A';
        $result[] = '1-Wire - Weather Station';
        $result[] = 'AcuRite - 3-in-1 Pro';
        $result[] = 'AcuRite - 5-in-1 Pro';
        $result[] = 'Airmar - 150WX';
        $result[] = 'Airmar - PB100';
        $result[] = 'Argent Data Systems - WS1';
        $result[] = 'Ambient Weather - WS-1000 Series';
        $result[] = 'Ambient Weather - WS-2000 Series';
        $result[] = 'Campbell Scientific - CR1000 Series';
        $result[] = 'Campbell Scientific - CR200X Series';
        $result[] = 'Campbell Scientific - CR3000 Series';
        $result[] = 'Campbell Scientific - CR800 Series';
        $result[] = 'Columbia - Capricorn';
        $result[] = 'Columbia - Capricorn FLX';
        $result[] = 'Columbia - Magellan';
        $result[] = 'Columbia - Magellan MX';
        $result[] = 'Columbia - Orion';
        $result[] = 'Columbia - Pulsar';
        $result[] = 'Davis Instruments - Vantage Pro';
        $result[] = 'Davis Instruments - Vantage Pro Plus';
        $result[] = 'Davis Instruments - Vantage Pro2';
        $result[] = 'Davis Instruments - Vantage Pro2 Plus';
        $result[] = 'Davis Instruments - Vantage Vue';
        $result[] = 'Davis Instruments - Weather Monitor II';
        $result[] = 'Dyacon - MS-100';
        $result[] = 'EnvironData - Weather Maestro';
        $result[] = 'Fine Offset - HP Series';
        $result[] = 'Fine Offset - WA Series';
        $result[] = 'Fine Offset - WH Series';
        $result[] = 'Fine Offset - WS Series';
        $result[] = 'Hideki - TE923';
        $result[] = 'Honeywell Meade - TFA / TE Series';
        $result[] = 'Honeywell Meade - TN Series';
        $result[] = 'La Crosse - C84612';
        $result[] = 'La Crosse - WS-1500 Series';
        $result[] = 'La Crosse - WS-1600 Series';
        $result[] = 'La Crosse - WS-1900 Series';
        $result[] = 'La Crosse - WS-2000 Series';
        $result[] = 'Maximum Inc. - Blackwatch';
        $result[] = 'Maximum Inc. - Catalina';
        $result[] = 'Maximum Inc. - Executive';
        $result[] = 'Maximum Inc. - Hatteras';
        $result[] = 'Maximum Inc. - Marconi';
        $result[] = 'Maximum Inc. - Montauk';
        $result[] = 'Maximum Inc. - Newport';
        $result[] = 'Maximum Inc. - Observer';
        $result[] = 'Maximum Inc. - Portland';
        $result[] = 'Maximum Inc. - Professional';
        $result[] = 'Maximum Inc. - Sorcerer';
        $result[] = 'Maximum Inc. - WeatherMaster';
        $result[] = 'MEA - ETO Weather Station';
        $result[] = 'MEA - Feedlot Weather Station';
        $result[] = 'MEA - Junior Weather Station';
        $result[] = 'MEA - Portable Weather Station';
        $result[] = 'MEA - Premium Weather Station';
        $result[] = 'MEA - Spray Drift Weather Station';
        $result[] = 'New Mountain Innovations - NM100';
        $result[] = 'New Mountain Innovations - NM150';
        $result[] = 'Onset - HOBO';
        $result[] = 'Oregon Scientific - LW301';
        $result[] = 'Oregon Scientific - WMR100 Series';
        $result[] = 'Oregon Scientific - WMR200 Series';
        $result[] = 'Oregon Scientific - WMR300 Series';
        $result[] = 'Oregon Scientific - WMR900 Series';
        $result[] = 'Peet Bros - Ultimeter 100 Series';
        $result[] = 'Peet Bros - Ultimeter 800 Series';
        $result[] = 'Peet Bros - Ultimeter 2000 Series';
        $result[] = 'Radioshack - Wireless';
        $result[] = 'Radioshack - WX200';
        $result[] = 'RainWise - AgroMET';
        $result[] = 'RainWise - CC3000';
        $result[] = 'RainWise - MKIII';
        $result[] = 'RainWise - System 12 WeatherLog';
        $result[] = 'RainWise - WS-1000CC';
        $result[] = 'RainWise - WS-2000';
        $result[] = 'Reinhardt - 5MVH';
        $result[] = 'Texas Weather Instruments - OneWire';
        $result[] = 'Texas Weather Instruments - WLS';
        $result[] = 'Texas Weather Instruments - WPS';
        $result[] = 'Texas Weather Instruments - WRx';
        $result[] = 'TFA-Dostmann - KlimaLogg Pro';
        $result[] = 'Ventus - W800 Series';
        $result[] = 'Wario - ME11/12';
        $result[] = 'Weather Hawk - 500 Series';
        $result[] = 'Weather Hawk - 600 Series';
        $result[] = 'Weather Hawk - Signature Series';
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
}