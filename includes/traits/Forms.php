<?php

namespace WeatherStation\UI\Forms;

use WeatherStation\Data\Output;

/**
 * Forms & fields management.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
            $html .= '<option value="' . $val[0] . '"' . ( $val[0] == $value ? ' selected="selected"' : '') . '>' . $val[1] . '</option>';
        }
        $html = '<select name="' . $id . '" id="' . $id . '">' . $html . '</select>';
        if (isset($unit)) {
            $html .= '&nbsp;<label for="' . $id . '">' . $unit . '</label>';
        }
        if (isset($description)) {
            $html .= '<p class="description">' . $description . '</p>';
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
            $html .= '<label><input id="' . $id . '" name="' . $id . '" type="radio" value="' . $val[0] . '"' . ( $val[0] == $value ? ' checked="checked"' : '') . '/>' . $val[1] . '</label>';
            if ($val !== end($list)) {
                $html .= '<br/>';
            }
        }
        $html = '<fieldset>' . $html . '</fieldset>';
        if (isset($description)) {
            $html .= '<p class="description">' . $description . '</p>';
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
        $html = '<fieldset><label><input name="' . $id . '" type="checkbox" value="1"' . ($checked ? ' checked="checked"' : '') . '/>' . $text . '</label></fieldset>';
        if (isset($description)) {
            $html .= '<p class="description">' . $description . '</p>';
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
            $html .= '<fieldset><label><input ' . (isset($arg['more'])?$arg['more'].' ':'') . 'name="' . $arg['id'] . '" type="checkbox" value="1"' . ($arg['checked'] ? ' checked="checked"' : '') . '/>' . $arg['text'] . '</label></fieldset>';
            if ($arg['description'] != '') {
                $html .= '<p class="description">' . $arg['description'] . '</p>';
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
        $html = '<input name="' . $id . '" type="number" step="' . $step . '" min="' . $min . '" max="' . $max . '"id="' . $id . '" value="' . $value . '" />';
        if (isset($unit)) {
            $html .= '&nbsp;<label for="' . $id . '">' . $unit . '</label>';
        }
        if (isset($description)) {
            $html .= '<p class="description">' . $description . '</p>';
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
                    $html .= '<label for="' . $arg['id'] . '">' . $arg['label'] . '</label>:&nbsp;';
                }
            }
            $html .= '<input name="' . $arg['id'] . '" type="number" step="' . $arg['step'] . '" min="' . $arg['min'] . '" max="' . $arg['max'] . '"id="' . $arg['id'] . '" value="' . $arg['value'] . '" />';
            if (array_key_exists('unit', $arg)) {
                if (isset($arg['unit'])) {
                    $html .= '&nbsp;<label for="' . $arg['id'] . '">' . $arg['unit'] . '</label>';
                }
            }
            $res[] = $html;
        }
        $html = implode(' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ', $res);
        if (isset($description)) {
            $html .= '<p class="description">' . $description . '</p>';
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
        $id = 'lws_thresholds_' . $type . '_';
        $min_boundary = $this->output_value(get_option('live_weather_station_' . $type . '_min_boundary'), $type);
        $max_boundary = $this->output_value(get_option('live_weather_station_' . $type . '_max_boundary'), $type);
        $decimal = $this->decimal_for_output($type);
        if ($decimal != 0) {
            $step = pow(10, 0-$decimal);
        }
        else {
            $step = pow(10, floor(log10($max_boundary - $min_boundary))-2);
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
        $typetxt = strtolower($this->get_measurement_type($type, false, ($type == 'rain' ? 'namodule3' : 'NAMain')));
        $txt_value = sprintf(__('Limits for %s, values expressed in %s.', 'live-weather-station'), $typetxt, $unitlong);
        $txt_alarm = sprintf(__('Alarms for %s, values expressed in %s.', 'live-weather-station'), $typetxt, $unitlong);
        if ($type == 'humidex' || $type == 'heat_index' || $type == 'cbi' || $type == 'uv_index') {
            $txt_value = sprintf(__('Limits for %s, dimensionless index.', 'live-weather-station'), $typetxt, $unitlong);
            $txt_alarm = sprintf(__('Alarms for %s, dimensionless index.', 'live-weather-station'), $typetxt, $unitlong);
        }
        if ($type == 'strike_count' || $type == 'strike_instant') {
            $txt_value = sprintf(__('Limits for %s.', 'live-weather-station'), $typetxt);
            $txt_alarm = sprintf(__('Alarms for %s.', 'live-weather-station'), $typetxt);
        }
        $html .= __('low:', 'live-weather-station') . ' <input name="' . $id . 'min_value" type="number" step="' . $step . '" min="' . $min_boundary . '" max="' . $max_boundary . '" id="' . $id . 'min_value" value="' . $min_value . '" />';
        $html .= '&nbsp;<label for="' . $id . 'min_value">' . $unit . '</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
        $html .= __('high:', 'live-weather-station') . ' <input name="' . $id . 'max_value" type="number" step="' . $step . '" min="' . $min_boundary . '" max="' . $max_boundary . '" id="' . $id . 'max_value" value="' . $max_value . '" />';
        $html .= '&nbsp;<label for="' . $id . 'max_value">' . $unit . '</label>';
        $html .= '<p class="description">' . $txt_value . '</p>';
        $html .= '<p class="description">&nbsp;</p>';
        $html .= __('low:', 'live-weather-station') . ' <input name="' . $id . 'min_alarm" type="number" step="' . $step . '" min="' . $min_boundary . '" max="' . $max_boundary . '" id="' . $id . 'min_alarm" value="' . $min_alarm . '" />';
        $html .= '&nbsp;<label for="' . $id . 'min_alarm">' . $unit . '</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
        $html .= __('high:', 'live-weather-station') . ' <input name="' . $id . 'max_alarm" type="number" step="' . $step . '" min="' . $min_boundary . '" max="' . $max_boundary . '" id="' . $id . 'max_alarm" value="' . $max_alarm . '" />';
        $html .= '&nbsp;<label for="' . $id . 'max_alarm">' . $unit . '</label>';
        $html .= '<p class="description">' . $txt_alarm . '</p>';
        return $html;
    }
}