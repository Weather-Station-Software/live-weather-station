<?php

namespace WeatherStation\Engine\Module\Current;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter meter form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */
class Meter extends \WeatherStation\Engine\Module\Maintainer {

    use Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
        Output::get_dimension_name insteadof Generator;
    }


    /**
     * Initialize the class and set its properties.
     *
     * @param string $station_guid The GUID of the station.
     * @param string $station_id The ID of the device.
     * @param string $station_name The name of the station.
     * @since 3.4.0
     */
    public function __construct($station_guid, $station_id, $station_name) {
        $this->module_type = 'current';
        $this->module_id = 'steelmeter';
        $this->module_name = ucfirst(__('steel meter', 'live-weather-station'));
        $this->module_hint = __('Display current data with a steel meter.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-meter';
        $this->layout = '1-23-4';
        $this->preview_min_height = true;
        parent::__construct($station_guid, $station_id, $station_name);
    }

    /**
     * Prepare the data.
     *
     * @since 3.4.0
     */
    protected function prepare() {
        $js_array_steelmeter = $this->get_all_stations_array(false, false, true, true, false, false, false, array($this->station_guid));
        if (array_key_exists($this->station_guid, $js_array_steelmeter)) {
            if (array_key_exists(2, $js_array_steelmeter[$this->station_guid])) {
                $this->data = $js_array_steelmeter[$this->station_guid][2];
            }
        }
        else {
            $this->data = null;
        }
    }

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_assoc_option_select('steelmeter-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('steelmeter-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= '</tbody></table>';
        return $this->get_box('lws-datasource-id', $this->datasource_title, $content);
    }

    /**
     * Print the parameters section of the form.
     *
     * @return string The parameters section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_parameters() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('steelmeter-datas-design-'. $this->station_guid, __('Design', 'live-weather-station'), $this->get_steelmeter_design_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-frame-'. $this->station_guid, __('Bezel', 'live-weather-station'), $this->get_steelmeter_frame_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-background-'. $this->station_guid, __('Face', 'live-weather-station'), $this->get_steelmeter_background_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-orientation-'. $this->station_guid, __('Labels orientation', 'live-weather-station'), $this->get_steelmeter_orientation_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-main-pointer-type-'. $this->station_guid, __('Main pointer type', 'live-weather-station'), $this->get_steelmeter_pointer_type_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-main-pointer-color-'. $this->station_guid, __('Main pointer color', 'live-weather-station'), $this->get_steelmeter_pointer_color_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-aux-pointer-type-'. $this->station_guid, __('2nd pointer type', 'live-weather-station'), $this->get_steelmeter_pointer_type_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-aux-pointer-color-'. $this->station_guid, __('2nd pointer color', 'live-weather-station'), $this->get_steelmeter_pointer_color_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-knob-'. $this->station_guid, __('Knob', 'live-weather-station'), $this->get_steelmeter_knob_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-lcd-'. $this->station_guid, __('LCD display', 'live-weather-station'), $this->get_steelmeter_lcd_design_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-alarm-'. $this->station_guid, __('Alarm', 'live-weather-station'), $this->get_steelmeter_led_color_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-trend-'. $this->station_guid, __('Trend', 'live-weather-station'), $this->get_steelmeter_led_color_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-minmax-'. $this->station_guid, __('Min/max', 'live-weather-station'), $this->get_steelmeter_minmax_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-index-style-'. $this->station_guid, __('Index style', 'live-weather-station'), $this->get_steelmeter_index_style_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-index-color-'. $this->station_guid, __('Index color', 'live-weather-station'), $this->get_steelmeter_index_color_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-glass-'. $this->station_guid, __('Glass', 'live-weather-station'), $this->get_steelmeter_glass_js_array());
        $content .= $this->get_key_value_option_select('steelmeter-datas-size-'. $this->station_guid, __('Size', 'live-weather-station'), $this->get_size_js_array(false, true, false), true, 'large');
        $content .= '</tbody></table>';
        return $this->get_box('lws-parameter-id', $this->parameter_title, $content);
    }

    /**
     * Print the script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_script() {
        $content = '';
        $content .= '$("#steelmeter-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_steelmeter_measurement_' . $this->station_guid . ' = js_array_steelmeter_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#steelmeter-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_steelmeter_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#steelmeter-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_steelmeter_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#steelmeter-datas-measurement-' . $this->station_guid . '" ).change();});';
        
        $content .= '$("#steelmeter-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= '$( "#steelmeter-datas-design-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-design-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-frame-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-frame-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-background-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-background-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-orientation-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-orientation-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-main-pointer-type-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-main-pointer-type-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-main-pointer-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-main-pointer-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-aux-pointer-type-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-aux-pointer-type-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-aux-pointer-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-aux-pointer-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-knob-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-knob-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-lcd-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-lcd-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-alarm-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-alarm-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-trend-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-trend-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-minmax-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-minmax-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-index-style-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-index-style-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-index-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-index-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-glass-' . $this->station_guid . '" ).change();});';
        $content .= '$("#steelmeter-datas-glass-' . $this->station_guid . '").change(function() {';
        $content .= '$("#steelmeter-datas-size-' . $this->station_guid . '" ).change();});';

        $content .= '$("#steelmeter-datas-size-' . $this->station_guid . '").change(function() {';
        $content .= 'var sc_device = "' . $this->station_id . '";';
        $content .= 'var sc_module = js_array_steelmeter_' . $this->station_guid . '[$("#steelmeter-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_measurement = js_array_steelmeter_' . $this->station_guid . '[$("#steelmeter-datas-module-' . $this->station_guid . '").val()][2][$("#steelmeter-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_design = $("#steelmeter-datas-design-' . $this->station_guid . '").val();';
        $content .= 'var sc_frame = $("#steelmeter-datas-frame-' . $this->station_guid . '").val();';
        $content .= 'var sc_background = $("#steelmeter-datas-background-' . $this->station_guid . '").val();';
        $content .= 'var sc_orientation = $("#steelmeter-datas-orientation-' . $this->station_guid . '").val();';
        $content .= 'var sc_main_pointer_type = $("#steelmeter-datas-main-pointer-type-' . $this->station_guid . '").val();';
        $content .= 'var sc_main_pointer_color = $("#steelmeter-datas-main-pointer-color-' . $this->station_guid . '").val();';
        $content .= 'var sc_aux_pointer_type = $("#steelmeter-datas-aux-pointer-type-' . $this->station_guid . '").val();';
        $content .= 'var sc_aux_pointer_color = $("#steelmeter-datas-aux-pointer-color-' . $this->station_guid . '").val();';
        $content .= 'var sc_knob = $("#steelmeter-datas-knob-' . $this->station_guid . '").val();';
        $content .= 'var sc_lcd = $("#steelmeter-datas-lcd-' . $this->station_guid . '").val();';
        $content .= 'var sc_alarm = $("#steelmeter-datas-alarm-' . $this->station_guid . '").val();';
        $content .= 'var sc_trend = $("#steelmeter-datas-trend-' . $this->station_guid . '").val();';
        $content .= 'var sc_minmax = $("#steelmeter-datas-minmax-' . $this->station_guid . '").val();';
        $content .= 'var sc_index_style = $("#steelmeter-datas-index-style-' . $this->station_guid . '").val();';
        $content .= 'var sc_index_color = $("#steelmeter-datas-index-color-' . $this->station_guid . '").val();';
        $content .= 'var sc_glass = $("#steelmeter-datas-glass-' . $this->station_guid . '").val();';
        $content .= 'var sc_size = $("#steelmeter-datas-size-' . $this->station_guid . '").val();';
        $content .= 'var shortcode = "[live-weather-station-steelmeter device_id=\'"+sc_device+ "\' module_id=\'"+sc_module+ "\' measure_type=\'"+sc_measurement+ "\' design=\'"+sc_design.toLowerCase()+ "\' frame=\'"+sc_frame.toLowerCase()+ "\' background=\'"+sc_background.toLowerCase()+ "\' orientation=\'"+sc_orientation.toLowerCase()+ "\' main_pointer_type=\'"+sc_main_pointer_type.toLowerCase()+ "\' main_pointer_color=\'"+sc_main_pointer_color.toLowerCase()+ "\' aux_pointer_type=\'"+sc_aux_pointer_type.toLowerCase()+ "\' aux_pointer_color=\'"+sc_aux_pointer_color.toLowerCase()+ "\' knob=\'"+sc_knob.toLowerCase()+ "\' lcd=\'"+sc_lcd.toLowerCase()+ "\' alarm=\'"+sc_alarm.toLowerCase()+ "\' trend=\'"+sc_trend.toLowerCase()+ "\' minmax=\'"+sc_minmax.toLowerCase()+ "\' index_style=\'"+sc_index_style.toLowerCase()+ "\' index_color=\'"+sc_index_color.toLowerCase()+ "\' glass=\'"+sc_glass.toLowerCase()+ "\' size=\'"+sc_size.toLowerCase()+"\']";';
        $content .= '$("#steelmeter-datas-shortcode-' . $this->station_guid . '").html(shortcode);';
        $content .= 'if (sc_design.indexOf("meter") > -1 || sc_design.indexOf("windcompass") > -1) {';
        $content .= '$("#steelmeter-datas-orientation-' . $this->station_guid . '").val("auto");';
        $content .= '$("#steelmeter-datas-orientation-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-orientation-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_design.indexOf("windcompass") < 0) {';
        $content .= '$("#steelmeter-datas-aux-pointer-type-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#steelmeter-datas-aux-pointer-color-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-aux-pointer-type-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#steelmeter-datas-aux-pointer-color-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_design.indexOf("digital") > -1) {';
        $content .= '$("#steelmeter-datas-main-pointer-type-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#steelmeter-datas-main-pointer-color-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#steelmeter-datas-knob-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-main-pointer-type-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#steelmeter-datas-main-pointer-color-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#steelmeter-datas-knob-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_design.indexOf("meter") == 0) {';
        $content .= '$("#steelmeter-datas-lcd-' . $this->station_guid . '").val("none");';
        $content .= '$("#steelmeter-datas-lcd-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-lcd-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_design.indexOf("meter") > -1 || sc_design.indexOf("windcompass") > -1) {';
        $content .= '$("#steelmeter-datas-alarm-' . $this->station_guid . '").val("none");';
        $content .= '$("#steelmeter-datas-alarm-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-alarm-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_design.indexOf("4") > -1) {';
        $content .= '$("#steelmeter-datas-trend-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-trend-' . $this->station_guid . '").val("none");';
        $content .= '$("#steelmeter-datas-trend-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'if (sc_design.indexOf("altimeter") > -1 || sc_design.indexOf("windcompass") > -1 || sc_design.indexOf("digital") > -1) {';
        $content .= '$("#steelmeter-datas-minmax-' . $this->station_guid . '").val("none");';
        $content .= '$("#steelmeter-datas-minmax-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-minmax-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_design.indexOf("altimeter") > -1) {';
        $content .= '$("#steelmeter-datas-index-style-' . $this->station_guid . '").val("none");';
        $content .= '$("#steelmeter-datas-index-style-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#steelmeter-datas-index-color-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#steelmeter-datas-index-style-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#steelmeter-datas-index-color-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'if (sc_index_style.indexOf("none") > -1) {';
        $content .= '$("#steelmeter-datas-index-color-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= '$("#steelmeter-bg-' . $this->station_guid . '").addClass("spinner");';
        $content .= '$("#steelmeter-bg-' . $this->station_guid . '").addClass("is-active");';
        $content .= '$("#' . $this->fingerprint . '" ).empty();';
        $content .= 'if (sc_size=="small") {var wsize = 150;}';
        $content .= 'if (sc_size=="medium") {var wsize = 200;}';
        $content .= 'if (sc_size=="large") {var wsize = 250;}';
        $content .= 'if (sc_size=="macro") {var wsize = 300;}';
        $content .= 'var canvas = document.getElementById("' . $this->fingerprint . '");';
        $content .= 'canvas.getContext("2d").clearRect(0, 0, $("#' . $this->fingerprint . '").width(), $("#' . $this->fingerprint . '").height());';
        $content .= '$("#' . $this->fingerprint . '").width(1);';
        $content .= '$("#' . $this->fingerprint . '").width(wsize).height(wsize);';
        $content .= 'var http = new XMLHttpRequest();';
        $content .= 'var params = "action=lws_query_steelmeter_config";';
        $content .= 'params = params+"&id=' . $this->fingerprint . '";';
        $content .= 'params = params+"&device_id="+sc_device;';
        $content .= 'params = params+"&module_id="+sc_module;';
        $content .= 'params = params+"&measure_type="+sc_measurement;';
        $content .= 'params = params+"&design="+sc_design;';
        $content .= 'params = params+"&frame="+sc_frame;';
        $content .= 'params = params+"&background="+sc_background;';
        $content .= 'params = params+"&orientation="+sc_orientation;';
        $content .= 'params = params+"&main_pointer_type="+sc_main_pointer_type;';
        $content .= 'params = params+"&main_pointer_color="+sc_main_pointer_color;';
        $content .= 'params = params+"&aux_pointer_type="+sc_aux_pointer_type;';
        $content .= 'params = params+"&aux_pointer_color="+sc_aux_pointer_color;';
        $content .= 'params = params+"&knob="+sc_knob;';
        $content .= 'params = params+"&lcd="+sc_lcd;';
        $content .= 'params = params+"&alarm="+sc_alarm;';
        $content .= 'params = params+"&trend="+sc_trend;';
        $content .= 'params = params+"&minmax="+sc_minmax;';
        $content .= 'params = params+"&index_style="+sc_index_style;';
        $content .= 'params = params+"&index_color="+sc_index_color;';
        $content .= 'params = params+"&glass="+sc_glass;';
        $content .= 'params = params+"&size="+sc_size;';
        $content .= 'http.open("POST", "' . LWS_AJAX_URL . '", true);';
        $content .= 'http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");';
        $content .= 'http.onreadystatechange = function () {';
        $content .= 'if (http.readyState == 4 && http.status == 200) {';
        $content .= 'if (sc_design.indexOf("analog-") > -1) {var g' . $this->fingerprint . ' = new steelseries.Radial("' . $this->fingerprint . '", JSON.parse(http.responseText, function (k, v) {return eval(v);}));}';
        $content .= 'if (sc_design.indexOf("digital-") > -1) {var g' . $this->fingerprint . ' = new steelseries.RadialBargraph("' . $this->fingerprint . '", JSON.parse(http.responseText, function (k, v) {return eval(v);}));}';
        $content .= 'if (sc_design.indexOf("meter-") > -1) {var g' . $this->fingerprint . ' = new steelseries.RadialVertical("' . $this->fingerprint . '", JSON.parse(http.responseText, function (k, v) {return eval(v);}));}';
        $content .= 'if (sc_design.indexOf("windcompass-") > -1) {var g' . $this->fingerprint . ' = new steelseries.WindDirection("' . $this->fingerprint . '", JSON.parse(http.responseText, function (k, v) {return eval(v);}));}';
        $content .= 'if (sc_design.indexOf("altimeter-") > -1) {var g' . $this->fingerprint . ' = new steelseries.Altimeter("' . $this->fingerprint . '", JSON.parse(http.responseText, function (k, v) {return eval(v);}));}';
        $content .= 'var http2 = new XMLHttpRequest();';
        $content .= 'var params2 = "action=lws_query_steelmeter_datas";';
        $content .= 'params2 = params2+"&id=' . $this->fingerprint . '";';
        $content .= 'params2 = params2+"&device_id="+sc_device;';
        $content .= 'params2 = params2+"&module_id="+sc_module;';
        $content .= 'params2 = params2+"&measure_type="+sc_measurement;';
        $content .= 'http2.open("POST", "' . LWS_AJAX_URL . '", true);';
        $content .= 'http2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");';
        $content .= 'http2.onreadystatechange = function () {';
        $content .= 'if (http2.readyState == 4 && http2.status == 200) {';
        $content .= 'values = JSON.parse(http2.responseText);';
        $content .= 'if (sc_design.indexOf("analog-") > -1) {';
        $content .= 'g' . $this->fingerprint . '.setValue(values.value);';
        $content .= 'g' . $this->fingerprint . '.setUserLedOnOff(values.alarm);';
        $content .= 'if (values.value_min > -9999) {g' . $this->fingerprint . '.setMinMeasuredValue(values.value_min);}';
        $content .= 'if (values.value_max > -9999) {g' . $this->fingerprint . '.setMaxMeasuredValue(values.value_max);}';
        $content .= 'if (values.value_trend == "up") {g' . $this->fingerprint . '.setTrend(steelseries.TrendState.UP);}';
        $content .= 'if (values.value_trend == "down") {g' . $this->fingerprint . '.setTrend(steelseries.TrendState.DOWN);}';
        $content .= 'if (values.value_trend == "steady") {g' . $this->fingerprint . '.setTrend(steelseries.TrendState.STEADY);}}';
        $content .= 'if (sc_design.indexOf("digital-") > -1) {';
        $content .= 'g' . $this->fingerprint . '.setValue(values.value);';
        $content .= 'g' . $this->fingerprint . '.setUserLedOnOff(values.alarm);';
        $content .= 'if (values.value_trend == "up") {g' . $this->fingerprint . '.setTrend(steelseries.TrendState.UP);}';
        $content .= 'if (values.value_trend == "down") {g' . $this->fingerprint . '.setTrend(steelseries.TrendState.DOWN);}';
        $content .= 'if (values.value_trend == "steady") {g' . $this->fingerprint . '.setTrend(steelseries.TrendState.STEADY);}}';
        $content .= 'if (sc_design.indexOf("meter-") > -1) {';
        $content .= 'g' . $this->fingerprint . '.setValue(values.value);';
        $content .= 'if (values.value_min > -9999) {g' . $this->fingerprint . '.setMinMeasuredValue(values.value_min);}';
        $content .= 'if (values.value_max > -9999) {g' . $this->fingerprint . '.setMaxMeasuredValue(values.value_max);}}';
        $content .= 'if (sc_design.indexOf("windcompass-") > -1) {';
        $content .= 'g' . $this->fingerprint . '.setValueLatest(values.value);';
        $content .= 'g' . $this->fingerprint . '.setValueAverage(values.value_aux);}';
        $content .= 'if (sc_design.indexOf("altimeter-") > -1) {';
        $content .= 'g' . $this->fingerprint . '.setValue(values.value);}}};';
        $content .= 'http2.send(params2);';
        $content .= '$("#steelmeter-bg-' . $this->station_guid . '").removeClass("spinner");';
        $content .= '$("#steelmeter-bg-' . $this->station_guid . '").removeClass("is-active");}};';
        $content .= 'http.send(params);}); ';

        $content .= '$("#steelmeter-datas-module-' . $this->station_guid . '" ).change();';

        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_preview() {
        $content = '<div>&nbsp;</div>';
        $content .= '<div id="steelmeter-bg-' . $this->station_guid . '" style="max-width:350px;border-radius: 5px;margin:0;width: 100%;float: inherit;display:inline-flex;justify-content: center;background-position-x:center;background-position-y:center;">';
        $content .= '<canvas id="' . $this->fingerprint . '"></canvas>';
        $content .= '</div>';
        $special_footer  = '<span id="steelmeter-info-' . $this->station_guid . '" style="display: none;">';
        $special_footer .= '<div id="major-publishing-actions">';
        $special_footer .= __('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );
        $special_footer .= '</div>';
        $special_footer .= '</span>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content, '', $special_footer);
    }
}