<?php

namespace WeatherStation\UI\Forms;

use WeatherStation\Data\Output;
use WeatherStation\System\Output\Guard;

/**
 * Forms & fields management.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait Handling {

    use Output;
    
    /**
     * Get a select form field.
     *
     * @param array $list The list of options.
     * @param int|string $value The selected value.
     * @param string $id The id (and the name) of the control.
     * @param string $description Optional. A description to display.
     * @param string $unit Optional. A unit to display just after the control.
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_select($list, $value, $id, $description=null, $unit=null) {
        $html = '';
        foreach ($list as $val) {
            $html .= '<option value="' . esc_attr($val[0]) . '"' . ( $val[0] == $value ? ' selected="selected"' : '') . '>' . esc_html($val[1]) . '</option>';
        }
        $html = '<select name="' . esc_attr($id) . '" id="' . esc_attr($id) . '">' . $html . '</select>';
        if (isset($unit)) {
            $html .= '&nbsp;<label for="' . esc_attr($id) . '">' . wp_kses_post($unit) . '</label>';
        }
        if (isset($description)) {
            $html .= '<p class="description">' . wp_kses_post($description) . '</p>';
        }
        return $html;
    }

    /**
     * Get a multi-select form field.
     *
     * @param array $args An array which contains array of (text, id, list, value, description).
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_multi_select($args) {
        $html = '';
        foreach ($args as $arg) {
            if ($html != '') {
                $html .= '<br />';
            }
            $thtml = '';
            foreach ($arg['list'] as $val) {
                $thtml .= '<option value="' . esc_attr($val[0]) . '"' . ( $val[0] == $arg['value'] ? ' selected="selected"' : '') . '>' . esc_html($val[1]) . '</option>';
            }
            $html .= '<select name="' . esc_attr($arg['id']) . '" id="' . esc_attr($arg['id']) . '">' . $thtml . '</select>';
            if ($arg['description'] != '') {
                $html .= '<p class="description">' . wp_kses_post($arg['description']) . '</p>';
            }
        }
        return $html;
    }

    /**
     * Get a radio form field.
     *
     * @param array $list The list of options.
     * @param int|string $value The selected value.
     * @param string $id The id (and the name) of the control.
     * @param string $description Optional. A description to display.
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_radio($list, $value, $id, $description=null) {
        $html = '';
        foreach ($list as $val) {
            $html .= '<label><input id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" type="radio" value="' . esc_attr($val[0]) . '"' . ( $val[0] == $value ? ' checked="checked"' : '') . '/>' . wp_kses_post($val[1]) . '</label>';
            if ($val !== end($list)) {
                $html .= '<br/>';
            }
        }
        $html = '<fieldset>' . $html . '</fieldset>';
        if (isset($description)) {
            $html .= '<p class="description">' . wp_kses_post($description) . '</p>';
        }
        return $html;
    }

    /**
     * Get a checkbox form field.
     *
     * @param string $text The text of the checkbox.
     * @param string $id The id (and the name) of the control.
     * @param boolean $checked Is the checkbox on?
     * @param string $description Optional. A description to display.
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_checkbox($text, $id, $checked=false, $description=null) {
        $html = '<fieldset><label><input name="' . esc_attr($id) . '" type="checkbox" value="1"' . ($checked ? ' checked="checked"' : '') . '/>' . wp_kses_post($text) . '</label></fieldset>';
        if (isset($description)) {
            $html .= '<p class="description">' . wp_kses_post($description) . '</p>';
        }
        return $html;
    }

    /**
     * Get a multi-checkbox form field.
     *
     * @param array $args An array which contains array of (text, id, checked, description).
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_multi_checkbox($args) {
        $html = '';
        foreach ($args as $arg) {
            if ($html != '') {
                $html .= '<br />';
            }
            $html .= '<fieldset><label><input ' . (isset($arg['more'])?Guard::token($arg['more']).' ':'') . 'name="' . esc_attr($arg['id']) . '" type="checkbox" value="1"' . ($arg['checked'] ? ' checked="checked"' : '') . '/>' . wp_kses_post($arg['text']) . '</label></fieldset>';
            if ($arg['description'] != '') {
                $html .= '<p class="description">' . wp_kses_post($arg['description']) . '</p>';
            }
        }
        return $html;
    }

    /**
     * Get a input form field for number.
     *
     * @param integer $value The current value.
     * @param string $id The id (and the name) of the control.
     * @param integer $min Optional. Minimal number for input.
     * @param integer $max Optional. Maximal number for input.
     * @param integer $step Optional. Step value for the control.
     * @param string $description Optional. A description to display.
     * @param string $unit Optional. A unit to display just after the control.
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_input_number($value, $id, $min=0, $max=100, $step=1, $description=null, $unit=null) {
        $html = '<input name="' . esc_attr($id) . '" type="number" step="' . esc_attr($step) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '"id="' . esc_attr($id) . '" value="' . esc_attr($value) . '" />';
        if (isset($unit)) {
            $html .= '&nbsp;<label for="' . esc_attr($id) . '">' . wp_kses_post($unit) . '</label>';
        }
        if (isset($description)) {
            $html .= '<p class="description">' . wp_kses_post($description) . '</p>';
        }
        return $html;
    }

    
    /**
     * Get a input form field for number.
     *
     * @param array $args An array which contains array of (value, id, min, max, step, unit).
     * @param string $description Optional. A description to display.
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_multi_horizontal_input_number($args, $description=null) {
        $res = array();
        foreach ($args as $arg) {
            $html = '';
            if (array_key_exists('label', $arg)) {
                if (isset($arg['label'])) {
                    $html .= '<label for="' . esc_attr($arg['id']) . '">' . wp_kses_post($arg['label']) . '</label>:&nbsp;';
                }
            }
            $html .= '<input name="' . esc_attr($arg['id']) . '" type="number" step="' . esc_attr($arg['step']) . '" min="' . esc_attr($arg['min']) . '" max="' . esc_attr($arg['max']) . '"id="' . esc_attr($arg['id']) . '" value="' . esc_attr($arg['value']) . '" />';
            if (array_key_exists('unit', $arg)) {
                if (isset($arg['unit'])) {
                    $html .= '&nbsp;<label for="' . esc_attr($arg['id']) . '">' . wp_kses_post($arg['unit']) . '</label>';
                }
            }
            $res[] = $html;
        }
        $html = implode(' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ', $res);
        if (isset($description)) {
            $html .= '<p class="description">' . wp_kses_post($description) . '</p>';
        }
        return $html;
    }

    /**
     * Get a quad-input form fields for thresholds.
     *
     * @param string $type The measure type.
     * @return string The HTML string ready to print.
     * @since 3.0.0
     */
    protected function field_thresholds($type) {
        $html = '';
        $type = Guard::token($type);
        $id = 'lws_thresholds_' . $type . '_';
        $min_boundary = $this->output_value(get_option('live_weather_station_' . $type . '_min_boundary'), $type);
        $max_boundary = $this->output_value(get_option('live_weather_station_' . $type . '_max_boundary'), $type);
        $decimal = $this->decimal_for_output($type);
        if ($decimal != 0) {
            $step = pow(10, 0 - $decimal);
        }
        else {
            // The step is relative to the range of the boundaries (0.01 if the range is empty or not valid).
            $range = (float)$max_boundary - (float)$min_boundary;
            $step = ($range > 0 ? pow(10, floor(log10($range)) - 2) : 0.01);
        }
        if ($step > 1) {
            $min_value = $step * round($this->output_value(get_option('live_weather_station_' . $type . '_min_value'), $type)/$step);
            $max_value = $step * round($this->output_value(get_option('live_weather_station_' . $type . '_max_value'), $type)/$step);
            $min_alarm = $step * round($this->output_value(get_option('live_weather_station_' . $type . '_min_alarm'), $type)/$step);
            $max_alarm = $step * round($this->output_value(get_option('live_weather_station_' . $type . '_max_alarm'), $type)/$step);
            $min_boundary = $step * round($this->output_value(get_option('live_weather_station_' . $type . '_min_boundary'), $type)/$step);
            $max_boundary = $step * round($this->output_value(get_option('live_weather_station_' . $type . '_max_boundary'), $type)/$step);
        }
        else {
            $min_value = $this->output_value(get_option('live_weather_station_' . $type . '_min_value'), $type);
            $max_value = $this->output_value(get_option('live_weather_station_' . $type . '_max_value'), $type);
            $min_alarm = $this->output_value(get_option('live_weather_station_' . $type . '_min_alarm'), $type);
            $max_alarm = $this->output_value(get_option('live_weather_station_' . $type . '_max_alarm'), $type);
        }

        $unit = $this->output_unit($type, ($type == 'rain' ? 'namodule3' : 'NAMain'))['unit'];
        $unitlong = $this->output_unit($type, ($type == 'rain' ? 'namodule3' : 'NAMain'))['long'];
        $typetxt = live_weather_station_lcfirst($this->get_measurement_type($type, false, ($type == 'rain' ? 'namodule3' : 'NAMain')));
        $txt_value = sprintf(/* translators: 1: type of measurement, 2: unit */ __('Limits for %1$s, values expressed in %2$s.', 'live-weather-station'), live_weather_station_lcfirst($typetxt), $unitlong);
        $txt_alarm = sprintf(/* translators: 1: type of measurement, 2: unit */ __('Alarms for %1$s, values expressed in %2$s.', 'live-weather-station'), live_weather_station_lcfirst($typetxt), $unitlong);
        if ($type == 'humidex' || $type == 'heat_index' || $type == 'cbi' || $type == 'uv_index'  || $type == 'summer_simmer'  || $type == 'steadman') {
            $txt_value = sprintf(/* translators: %s: type of measurement */ __('Limits for %s, dimensionless index.', 'live-weather-station'), live_weather_station_lcfirst($typetxt), $unitlong);
            $txt_alarm = sprintf(/* translators: %s: type of measurement */ __('Alarms for %s, dimensionless index.', 'live-weather-station'), live_weather_station_lcfirst($typetxt), $unitlong);
        }
        if ($type == 'strike_count' || $type == 'strike_instant') {
            $txt_value = sprintf(/* translators: %s: type of measurement */ __('Limits for %s.', 'live-weather-station'), live_weather_station_lcfirst($typetxt));
            $txt_alarm = sprintf(/* translators: %s: type of measurement */ __('Alarms for %s.', 'live-weather-station'), live_weather_station_lcfirst($typetxt));
        }
        $html .= esc_html__('low:', 'live-weather-station') . ' <input name="' . $id . 'min_value" type="number" step="' . esc_attr($step) . '" min="' . esc_attr($min_boundary) . '" max="' . esc_attr($max_boundary) . '" id="' . $id . 'min_value" value="' . esc_attr($min_value) . '" />';
        $html .= '&nbsp;<label for="' . $id . 'min_value">' . wp_kses_post($unit) . '</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
        $html .= esc_html__('high:', 'live-weather-station') . ' <input name="' . $id . 'max_value" type="number" step="' . esc_attr($step) . '" min="' . esc_attr($min_boundary) . '" max="' . esc_attr($max_boundary) . '" id="' . $id . 'max_value" value="' . esc_attr($max_value) . '" />';
        $html .= '&nbsp;<label for="' . $id . 'max_value">' . wp_kses_post($unit) . '</label>';
        $html .= '<p class="description">' . wp_kses_post($txt_value) . '</p>';
        $html .= '<p class="description">&nbsp;</p>';
        $html .= esc_html__('low:', 'live-weather-station') . ' <input name="' . $id . 'min_alarm" type="number" step="' . esc_attr($step) . '" min="' . esc_attr($min_boundary) . '" max="' . esc_attr($max_boundary) . '" id="' . $id . 'min_alarm" value="' . esc_attr($min_alarm) . '" />';
        $html .= '&nbsp;<label for="' . $id . 'min_alarm">' . wp_kses_post($unit) . '</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
        $html .= esc_html__('high:', 'live-weather-station') . ' <input name="' . $id . 'max_alarm" type="number" step="' . esc_attr($step) . '" min="' . esc_attr($min_boundary) . '" max="' . esc_attr($max_boundary) . '" id="' . $id . 'max_alarm" value="' . esc_attr($max_alarm) . '" />';
        $html .= '&nbsp;<label for="' . $id . 'max_alarm">' . wp_kses_post($unit) . '</label>';
        $html .= '<p class="description">' . wp_kses_post($txt_alarm) . '</p>';
        return $html;
    }
}