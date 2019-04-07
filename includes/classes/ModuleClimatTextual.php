<?php

namespace WeatherStation\Engine\Module\Climat;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter textual form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class Textual extends \WeatherStation\Engine\Module\Maintainer {

    use Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_comparable_dimensions insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
        Output::get_dimension_name insteadof Generator;
        Output::get_operation_name insteadof Generator;
        Output::get_extension_description insteadof Generator;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.8.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'climat';
        $this->module_type = 'textual';
        $this->module_name = ucfirst(__('textual data', 'live-weather-station'));
        $this->module_hint = __('Display climatological data as textual values.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-note-2';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.8.0
     */
    protected function enqueue_resources() {
        // No style or script ;)
    }

    /**
     * Prepare the data.
     *
     * @since 3.8.0
     */
    protected function prepare() {
        $js_array_climatline = $this->get_all_stations_array(false, false, true, true, true, false, true, false, array($this->station_guid), false, false, false, false, false, false, true, true, true);
        if (array_key_exists($this->station_guid, $js_array_climatline)) {
            if (array_key_exists(2, $js_array_climatline[$this->station_guid])) {
                $this->data = $js_array_climatline[$this->station_guid][2];
            }
        }
        else {
            $this->data = null;
        }
        $this->period = $this->get_period_value_js_array($this->station_information, false, false);
        $this->computation = $this->get_computation_js_array();
    }

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('climat-textual-datas-period-type-'. $this->station_guid, __('Period type', 'live-weather-station'), $this->get_period_type_js_array(true, false, false, false, false, true), true, 'fixed-month');
        $content .= $this->get_neutral_option_select('climat-textual-datas-period-value-'. $this->station_guid, __('Period', 'live-weather-station'));
        $content .= $this->get_assoc_option_select('climat-textual-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('climat-textual-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_neutral_option_select('climat-textual-datas-set-'. $this->station_guid, __('Dataset', 'live-weather-station'));
        $content .= '</tbody></table>';
        return $this->get_box('lws-datasource-id', $this->datasource_title, $content);
    }

    /**
     * Print the parameters section of the form.
     *
     * @return string The parameters section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_parameters() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_neutral_option_select('climat-textual-datas-computed-'. $this->station_guid, __('Computation', 'live-weather-station'));
        $content .= $this->get_key_value_option_select('climat-textual-datas-condition-'. $this->station_guid, __('Condition', 'live-weather-station'), $this->get_comparison_js_array(), true, 'comp-eq');
        $content .= $this->get_number_input('climat-textual-datas-value1-'. $this->station_guid, __('Threshold 1', 'live-weather-station'), '&nbsp;');
        $content .= $this->get_number_input('climat-textual-datas-value2-'. $this->station_guid, __('Threshold 2', 'live-weather-station'), '&nbsp;');
        $content .= $this->get_placeholder_option_select();
        $content .= '</tbody></table>';
        return $this->get_box('lws-parameter-id', __('2. Choose the computation to perform', 'live-weather-station'), $content);
    }

    /**
     * Print the script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_script() {
        $content = '';
        $content .= '$("#climat-textual-datas-period-type-' . $this->station_guid . '").change(function() {';
        $content .= ' var js_array_climat_textual_p_' . $this->station_guid . ' = null;';
        $content .= ' $(js_array_climat_textual_period_' . $this->station_guid . ').each(function (i) {';
        $content .= '  if (js_array_climat_textual_period_' . $this->station_guid . '[i][0] == $("#climat-textual-datas-period-type-' . $this->station_guid . '").val()) {js_array_climat_textual_p_' . $this->station_guid . '=js_array_climat_textual_period_' . $this->station_guid . '[i][1]}  ;});';
        $content .= ' $("#climat-textual-datas-period-value-' . $this->station_guid . '").html("");';
        $content .= ' $(js_array_climat_textual_p_' . $this->station_guid . ').each(function (i) {';
        $content .= '  $("#climat-textual-datas-period-value-' . $this->station_guid . '").append("<option value="+js_array_climat_textual_p_' . $this->station_guid . '[i][0]+">"+js_array_climat_textual_p_' . $this->station_guid . '[i][1]+"</option>");});';
        $content .= ' $("#climat-textual-datas-period-value-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-period-value-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-module-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_climat_textual_measurement_' . $this->station_guid . ' = js_array_climat_textual_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_climat_textual_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '").append("<option class=\"lws-measurement-' . $this->station_guid . '\" unit="+js_array_climat_textual_measurement_' . $this->station_guid . '[i][5]+" ref="+js_array_climat_textual_measurement_' . $this->station_guid . '[i][6]+" value="+i+">"+js_array_climat_textual_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_climat_textual_set_' . $this->station_guid . ' = js_array_climat_textual_' . $this->station_guid . '[$("#climat-textual-datas-module-' . $this->station_guid . '").val()][2][$(this).val()][4];';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_climat_textual_set_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '").append("<option value="+js_array_climat_textual_set_' . $this->station_guid . '[i][0]+">"+js_array_climat_textual_set_' . $this->station_guid . '[i][1]+"</option>");});';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . ' option[value=\'avg\']").attr("selected", true);';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '" ).change();});';

        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-computed-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_climat_textual_computation_' . $this->station_guid . ').each(function (i) {';
        $content .= ' var ok_period = false;';
        $content .= ' $(js_array_climat_textual_computation_' . $this->station_guid . '[i][1]).each(function (j, s) {';
        $content .= '  if ($("#climat-textual-datas-period-type-' . $this->station_guid . '").val().includes(s)) {ok_period = true;}';
        $content .= ' });';
        $content .= ' var ok_set = false;';
        $content .= ' $(js_array_climat_textual_computation_' . $this->station_guid . '[i][2]).each(function (j, s) {';
        $content .= '  if ($("#climat-textual-datas-set-' . $this->station_guid . '").val().includes(s)) {ok_set = true;}';
        $content .= ' });';
        $content .= ' if (ok_period && ok_set) {';
        $content .= '  $("#climat-textual-datas-computed-' . $this->station_guid . '").append("<option class=\"lws-compute-' . $this->station_guid . '\" value="+js_array_climat_textual_computation_' . $this->station_guid . '[i][0]+" conditional="+js_array_climat_textual_computation_' . $this->station_guid . '[i][3]+">"+js_array_climat_textual_computation_' . $this->station_guid . '[i][4]+"</option>");';
        $content .= ' }';
        $content .= '});';
        $content .= '$("#climat-textual-datas-computed-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-computed-' . $this->station_guid . '").change(function() {';
        $content .= ' if($("option.lws-compute-' . $this->station_guid . ':selected").attr("conditional") == 1) {';
        $content .= '  $("#climat-textual-datas-condition-' . $this->station_guid . '").prop("disabled",false);';
        $content .= '  $("#climat-textual-datas-value1-' . $this->station_guid . '").prop("disabled",false);';
        $content .= '  $("#climat-textual-datas-value1-' . $this->station_guid . '-unit").html($("option.lws-measurement-' . $this->station_guid . ':selected").attr("unit"));';
        $content .= '  $("#climat-textual-datas-value2-' . $this->station_guid . '").prop("disabled",false);';
        $content .= '  $("#climat-textual-datas-value2-' . $this->station_guid . '-unit").html($("option.lws-measurement-' . $this->station_guid . ':selected").attr("unit"));';
        $content .= ' } else {';
        $content .= '  $("#climat-textual-datas-condition-' . $this->station_guid . '").prop("disabled",true);';
        $content .= '  $("#climat-textual-datas-value1-' . $this->station_guid . '").prop("disabled",true);';
        $content .= '  $("#climat-textual-datas-value1-' . $this->station_guid . '-unit").html("&nbsp;");';
        $content .= '  $("#climat-textual-datas-value2-' . $this->station_guid . '").prop("disabled",true);';
        $content .= '  $("#climat-textual-datas-value2-' . $this->station_guid . '-unit").html("&nbsp;");';
        $content .= ' }';
        $content .= '$("#climat-textual-datas-condition-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-condition-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-value1-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-value1-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-value2-' . $this->station_guid . '" ).change();});';

        $content .= '$("#climat-textual-datas-value2-' . $this->station_guid . '").change(function() {';
        $content .= '  var sc_device_id = "' . $this->station_id . '";';
        $content .= '  var sc_module_id = js_array_climat_textual_' . $this->station_guid . '[$("#climat-textual-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= '  var sc_period_type = $("#climat-textual-datas-period-type-' . $this->station_guid . '").val();';
        $content .= '  var sc_period = $("#climat-textual-datas-period-value-' . $this->station_guid . '").val();';
        $content .= '  var sc_measurement = js_array_climat_textual_' . $this->station_guid . '[$("#climat-textual-datas-module-' . $this->station_guid . '").val()][2][$("#climat-textual-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= '  var sc_set = $("#climat-textual-datas-set-' . $this->station_guid . '").val();';
        $content .= '  var sc_computed = $("#climat-textual-datas-computed-' . $this->station_guid . '").val();';
        $content .= '  var sc_ref = $("option.lws-measurement-' . $this->station_guid . ':selected").attr("ref");';
        $content .= '  var sc_condition = $("#climat-textual-datas-condition-' . $this->station_guid . '").val();';
        $content .= '  var sc_value1 = $("#climat-textual-datas-value1-' . $this->station_guid . '").val();';
        $content .= '  var sc_value2 = $("#climat-textual-datas-value2-' . $this->station_guid . '").val();';

        $content .= '  var shortcode = "[live-weather-station-lttextual device_id=\'"+sc_device_id+"\' module_id=\'"+sc_module_id+"\' periodtype=\'"+sc_period_type+"\' period=\'"+sc_period+"\' measurement=\'"+sc_measurement+"\' set=\'"+sc_set+"\' computed=\'"+sc_computed+"\' ref=\'"+sc_ref+"\' condition=\'"+sc_condition+"\' th1=\'"+sc_value1+"\' th2=\'"+sc_value2+"\']";';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$("#climat-textual-datas-shortcode-' . $this->station_guid . '").html(shortcode);';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_query_lttextual_code", cache:"no_cache", device_id:sc_device_id, module_id:sc_module_id, periodtype:sc_period_type, period:sc_period, measurement:sc_measurement, set:sc_set, computed:sc_computed, ref:sc_ref, condition:sc_condition, th1:sc_value1, th2:sc_value2';
        $content .= '}).done(function(data) {$("#climat-textual-datas-output-' . $this->station_guid . '").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';
        $content .= '});';



        $content .= '$("#climat-textual-datas-period-type-' . $this->station_guid . '").change();';
        return $this->get_script_box($content);
    }

    /*protected function get_script() {
        $content = '';
        $content .= '$("#climat-textual-datas-period-type-' . $this->station_guid . '").change(function() {';
        $content .= ' var js_array_climat_textual_p_' . $this->station_guid . ' = null;';
        $content .= ' $(js_array_climat_textual_period_' . $this->station_guid . ').each(function (i) {';
        $content .= '  if (js_array_climat_textual_period_' . $this->station_guid . '[i][0] == $("#climat-textual-datas-period-type-' . $this->station_guid . '").val()) {js_array_climat_textual_p_' . $this->station_guid . '=js_array_climat_textual_period_' . $this->station_guid . '[i][1]}  ;});';
        $content .= ' $("#climat-textual-datas-period-value-' . $this->station_guid . '").html("");';
        $content .= ' $(js_array_climat_textual_p_' . $this->station_guid . ').each(function (i) {';
        $content .= '  $("#climat-textual-datas-period-value-' . $this->station_guid . '").append("<option value="+js_array_climat_textual_p_' . $this->station_guid . '[i][0]+">"+js_array_climat_textual_p_' . $this->station_guid . '[i][1]+"</option>");});';
        $content .= ' $("#climat-textual-datas-period-value-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-period-value-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-data-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_climat_textual_measurement_' . $this->station_guid . ' = js_array_climat_textual_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_climat_textual_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_climat_textual_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_climat_textual_set_' . $this->station_guid . ' = js_array_climat_textual_' . $this->station_guid . '[$("#climat-textual-datas-module-' . $this->station_guid . '").val()][2][$(this).val()][4];';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_climat_textual_set_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '").append("<option value="+js_array_climat_textual_set_' . $this->station_guid . '[i][0]+">"+js_array_climat_textual_set_' . $this->station_guid . '[i][1]+"</option>");});';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . ' option[value=\'avg\']").attr("selected", true);';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-set-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-computed-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_climat_textual_computation_' . $this->station_guid . ').each(function (i) {';
        $content .= ' var ok_period = false;';
        $content .= ' $(js_array_climat_textual_computation_' . $this->station_guid . '[i][1]).each(function (j, s) {';
        $content .= '  if ($("#climat-textual-datas-period-type-' . $this->station_guid . '").val().includes(s)) {ok_period = true;}';
        $content .= ' });';
        $content .= ' var ok_set = false;';
        $content .= ' $(js_array_climat_textual_computation_' . $this->station_guid . '[i][2]).each(function (j, s) {';
        $content .= '  if ($("#climat-textual-datas-set-' . $this->station_guid . '").val().includes(s)) {ok_set = true;}';
        $content .= ' });';
        $content .= ' if (ok_period && ok_set) {';
        $content .= '  $("#climat-textual-datas-computed-' . $this->station_guid . '").append("<option value="+js_array_climat_textual_computation_' . $this->station_guid . '[i][0]+">"+js_array_climat_textual_computation_' . $this->station_guid . '[i][4]+"</option>");';
        $content .= ' }';
        $content .= '});';
        $content .= '$("#climat-textual-datas-computed-' . $this->station_guid . '" ).change();});';
        $content .= '$("#climat-textual-datas-computed-' . $this->station_guid . '").change(function() {';
        $content .= '$("#climat-textual-datas-data-' . $this->station_guid . '" ).change();});';


        $content .= '$("#climat-textual-datas-data-' . $this->station_guid . '").change(function() {';
        $content .= 'if (init) {';
        $content .= ' init = false;';
        $content .= ' $("#climat-textual-datas-module-' . $this->station_guid . '").change();';
        $content .= '}';
        $content .= ' var sc_device_id = "' . $this->station_id . '";';
        $content .= ' var sc_module_id = js_array_climat_textual_' . $this->station_guid . '[$("#climat-textual-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= ' var sc_period_type = $("#climat-textual-datas-period-type-' . $this->station_guid . '").val();';
        $content .= ' var sc_period = $("#climat-textual-datas-period-value-' . $this->station_guid . '").val();';
        $content .= ' var sc_measurement = js_array_climat_textual_' . $this->station_guid . '[$("#climat-textual-datas-module-' . $this->station_guid . '").val()][2][$("#climat-textual-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= ' var sc_set = $("#climat-textual-datas-set-' . $this->station_guid . '").val();';
        $content .= ' var sc_computed = $("#climat-textual-datas-computed-' . $this->station_guid . '").val();';

        $content .= ' var shortcode = "[live-weather-station-lttextual device_id=\'"+sc_device_id+"\' module_id=\'"+sc_module_id+"\' periodtype=\'"+sc_period_type+"\' period=\'"+sc_period+"\' measurement=\'"+sc_measurement+"\' set=\'"+sc_set+"\' computed=\'"+sc_computed+"\']";';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$("#climat-textual-datas-shortcode-' . $this->station_guid . '").html(shortcode);';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_query_lttextual_code", cache:"no_cache", device_id:sc_device_id, module_id:sc_module_id, periodtype:sc_period_type, period:sc_period, measurement:sc_measurement, set:sc_set, computed:sc_computed ';
        $content .= '}).done(function(data) {$("#climat-textual-datas-output-' . $this->station_guid . '").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';
        $content .= '});';


        $content .= 'var init = true;';
        $content .= '$("#climat-textual-datas-period-type-' . $this->station_guid . '").change();';

        return $this->get_script_box($content);
    }*/

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_preview() {
        $id = 'climat-textual-datas-output-'. $this->station_guid;
        $content = '<textarea readonly rows="1" style="width:100%;font-weight:bold;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="' . $id . '"></textarea>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content);
    }
}

