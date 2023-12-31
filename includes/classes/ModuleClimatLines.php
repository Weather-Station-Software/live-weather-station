<?php

namespace WeatherStation\Engine\Module\Climat;

use WeatherStation\Data\Output;

/**
 * Class to generate parameter climat lines form.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class Lines extends \WeatherStation\Engine\Module\Maintainer {

    use Output;

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.8.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'climat';
        $this->module_type = 'lines';
        $this->module_name = ucfirst(__('line series', 'live-weather-station'));
        $this->module_hint = __('Display long-term data as multiple lines chart. Allows to view, side by side on the same graph, same or different dataset of the same measurement for different periods.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-line-chart-7';
        $this->layout = '12-3-4';
        $this->series_number = 8;
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.8.0
     */
    protected function enqueue_resources() {
        wp_enqueue_style('lws-nvd3');
        wp_enqueue_script('lws-nvd3');
        wp_enqueue_script('lws-colorbrewer');
        wp_enqueue_script('lws-spin');
    }

    /**
     * Prepare the data.
     *
     * @since 3.8.0
     */
    protected function prepare() {
        $js_array_climatline = $this->get_all_stations_array(false, false, true, true, true, false, true, false, array($this->station_guid), false, false, false, false, false, false, true);
        if (array_key_exists($this->station_guid, $js_array_climatline)) {
            if (array_key_exists(2, $js_array_climatline[$this->station_guid])) {
                $this->data = $js_array_climatline[$this->station_guid][2];
            }
        }
        else {
            $this->data = null;
        }
        $this->period = $this->get_period_value_js_array($this->station_information, false, true);
    }

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_assoc_option_select('climat-lines-measurements-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('climat-lines-measurements-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_key_value_option_select('climat-lines-measurements-period-type-'. $this->station_guid, __('Period type', 'live-weather-station'), $this->get_period_type_js_array(false, true), true, 'fixed-month');
        $a_group = array();
        for ($i=1; $i<=$this->series_number; $i++) {
            $group = $this->get_neutral_option_select('climat-lines-measurements-set-'. $i . '-'. $this->station_guid, __('Dataset', 'live-weather-station'));
            $group .= $this->get_neutral_option_select('climat-lines-measurements-period-value-'. $i . '-' . $this->station_guid, __('Period', 'live-weather-station'));
            $group .= $this->get_key_value_option_select('climat-lines-measurements-line-mode-' . $i . '-' . $this->station_guid, __('Mode', 'live-weather-station'), $this->get_line_mode_js_array(), true, 'line');
            $group .= $this->get_key_value_option_select('climat-lines-measurements-dot-style-' . $i . '-' . $this->station_guid, __('Values display', 'live-weather-station'), $this->get_dot_style_js_array(), true, 'none');
            $group .= $this->get_key_value_option_select('climat-lines-measurements-line-style-' . $i . '-' . $this->station_guid, __('Line style', 'live-weather-station'), $this->get_line_style_js_array(), true, 'solid');
            $group .= $this->get_key_value_option_select('climat-lines-measurements-line-size-' . $i . '-' . $this->station_guid, __('Line size', 'live-weather-station'), $this->get_line_size_js_array(), true, 'regular');
            $a_group[] = array('content' => $group, 'name' => sprintf(__('Measurement %s', 'live-weather-station'), $i));
        }
        $content .= $this->get_group('climat-lines-measurements-measure-group-', $a_group);
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
        $content .= $this->get_key_value_option_select('climat-lines-measurements-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array(true));
        $content .= $this->get_key_value_option_select('climat-lines-measurements-label-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_multi_label_js_array(), true, 'simple');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-guideline-'. $this->station_guid, __('Hint', 'live-weather-station'), $this->get_guideline_js_array(), true, 'standard');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(), true, '300px');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-timescale-'. $this->station_guid, __('Time scale', 'live-weather-station'), $this->get_x_scale_js_array(false, false), true, 'auto');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(true), true, 'auto');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-interpolation-'. $this->station_guid, __('Interpolation', 'live-weather-station'), $this->get_interpolation_js_array(), true, 'none');
        $content .= $this->get_key_value_option_select('climat-lines-measurements-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(false), true, 'inline');
        $content .= $this->get_placeholder_option_select();
        $content .= '</tbody></table>';
        return $this->get_box('lws-parameter-id', $this->parameter_title, $content);
    }

    /**
     * Print the script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_script() {
        $name = self::$module_mode . '-' . $this->module_type;
        $js_name = self::$module_mode . '_' . $this->module_type;
        $content = '';
        $content .= '$("#' . $name . '-measurements-module-' . $this->station_guid . '").change(function() {';
        $content .= '  var js_array_' . $js_name . '_measurement_' . $this->station_guid . ' = js_array_' . $js_name . '_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '  $("#' . $name . '-measurements-measurement-' . $this->station_guid . '").html("");';
        $content .= '  $(js_array_' . $js_name . '_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '    $("#' . $name . '-measurements-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '  $("#' . $name . '-measurements-measurement-' . $this->station_guid . '").change();';
        $content .= '});';
        $content .= '$("#' . $name . '-measurements-measurement-' . $this->station_guid . '").change(function() {';
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '  var js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . ' = js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-measurements-module-' . $this->station_guid . '").val()][2][$(this).val()][4];';
            $content .= '  $("#' . $name . '-measurements-set-' . $i . '-' . $this->station_guid . '").html("");';
            $content .= '  $(js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . ').each(function (i) {';
            $content .= '    $("#' . $name . '-measurements-set-' . $i . '-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . '[i][1]+"</option>");});';
            $content .= '  $("#' . $name . '-measurements-set-' . $i . '-' . $this->station_guid . ' option[value=\'avg\']").attr("selected", true);';
        }
        $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '" ).change();';



        $content .= '$("#' . $name . '-measurements-period-type-' . $this->station_guid . '").change(function() {';
        $content .= '  var js_array_' . $js_name . '_p_' . $this->station_guid . ' = null;';
        $content .= '  $(js_array_' . $js_name . '_period_' . $this->station_guid . ').each(function (i) {';
        $content .= '    if (js_array_' . $js_name . '_period_' . $this->station_guid . '[i][0] == $("#' . $name . '-measurements-period-type-' . $this->station_guid . '").val()) {js_array_' . $js_name . '_p_' . $this->station_guid . '=js_array_' . $js_name . '_period_' . $this->station_guid . '[i][1]};});';
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '  $("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '").html("");';
            $content .= '  $(js_array_' . $js_name . '_p_' . $this->station_guid . ').each(function (i) {';
            $content .= '    $("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][1]+"</option>");});';
        }
        $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '" ).change();';
        $content .= '});';




        $content .= '});';

        $content .= '$("#' . $name . '-measurements-period-type-' . $this->station_guid . '").change(function() {';
        $content .= '  var js_array_' . $js_name . '_p_' . $this->station_guid . ' = null;';
        $content .= '  $(js_array_' . $js_name . '_period_' . $this->station_guid . ').each(function (i) {';
        $content .= '    if (js_array_' . $js_name . '_period_' . $this->station_guid . '[i][0] == $("#' . $name . '-measurements-period-type-' . $this->station_guid . '").val()) {js_array_' . $js_name . '_p_' . $this->station_guid . '=js_array_' . $js_name . '_period_' . $this->station_guid . '[i][1]};});';
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '  $("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '").html("");';
            $content .= '  $(js_array_' . $js_name . '_p_' . $this->station_guid . ').each(function (i) {';
            $content .= '    $("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][1]+"</option>");});';
        }
        $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '" ).change();';
        $content .= '});';

        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '$("#' . $name . '-measurements-set-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-measurements-line-mode-' . $i . '-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-measurements-line-mode-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-measurements-dot-style-' . $i . '-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-measurements-dot-style-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-measurements-line-style-' . $i . '-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-measurements-line-style-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-measurements-line-size-' . $i . '-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-measurements-line-size-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '" ).change();});';
        }
        $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-interpolation-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-interpolation-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-timescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-timescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-valuescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-valuescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-guideline-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-guideline-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-height-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-height-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-label-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-label-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-data-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-data-' . $this->station_guid . '").change(function() {';
        $content .= '  if (typeof js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-measurements-module-' . $this->station_guid . '").val()] !== "undefined" && typeof js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-measurements-module-' . $this->station_guid . '").val()][2][$("#' . $name . '-measurements-measurement-' . $this->station_guid . '").val()] !== "undefined") {';
        $content .= '    var sc_device = "' . $this->station_id . '";';
        $content .= '    var sc_module = js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-measurements-module-' . $this->station_guid . '").val()][1];';
        $content .= '    var sc_measurement = js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-measurements-module-' . $this->station_guid . '").val()][2][$("#' . $name . '-measurements-measurement-' . $this->station_guid . '").val()][1];';
        $content .= '    var sc_period_type = $("#' . $name . '-measurements-period-type-' . $this->station_guid . '").val();';
        $content .= '    var sc_template = $("#' . $name . '-measurements-template-' . $this->station_guid . '").val();';
        $content .= '    var sc_color = $("#' . $name . '-measurements-color-' . $this->station_guid . '").val();';
        $content .= '    var sc_interpolation = $("#' . $name . '-measurements-interpolation-' . $this->station_guid . '").val();';
        $content .= '    var sc_timescale = $("#' . $name . '-measurements-timescale-' . $this->station_guid . '").val();';
        $content .= '    var sc_valuescale = $("#' . $name . '-measurements-valuescale-' . $this->station_guid . '").val();';
        $content .= '    var sc_guideline = $("#' . $name . '-measurements-guideline-' . $this->station_guid . '").val();';
        $content .= '    var sc_height = $("#' . $name . '-measurements-height-' . $this->station_guid . '").val();';
        $content .= '    var sc_label = $("#' . $name . '-measurements-label-' . $this->station_guid . '").val();';
        $content .= '    var sc_data = $("#' . $name . '-measurements-data-' . $this->station_guid . '").val();';
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= 'var sc_set_' . $i . ' = $("#' . $name . '-measurements-set-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_period_' . $i . ' = $("#' . $name . '-measurements-period-value-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_line_mode_' . $i . ' = $("#' . $name . '-measurements-line-mode-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_dot_style_' . $i . ' = $("#' . $name . '-measurements-dot-style-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_line_style_' . $i . ' = $("#' . $name . '-measurements-line-style-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_line_size_' . $i . ' = $("#' . $name . '-measurements-line-size-' . $i . '-' . $this->station_guid . '").val();';
            $content .= ' if (sc_period_' . $i . ' == 0) { var sc_' . $i . ' = "" } else {';
            $content .= 'var sc_' . $i . ' = " set_' . $i . '=\'"+sc_set_' . $i . '+"\' period_' . $i . '=\'"+sc_period_' . $i . '+"\' line_mode_' . $i . '=\'"+sc_line_mode_' . $i . '+"\' dot_style_' . $i . '=\'"+sc_dot_style_' . $i . '+"\' line_style_' . $i . '=\'"+sc_line_style_' . $i . '+"\' line_size_' . $i . '=\'"+sc_line_size_' . $i . '+"\'";';
            $content .= ' }';
        }
        $content .= '    var shortcode = "[live-weather-station-ltgraph mode=\'' . self::$module_mode . '\' type=\'' . $this->module_type . '\' device_id=\'"+sc_device+"\' module_id=\'"+sc_module+"\' measurement=\'"+sc_measurement+"\' template=\'"+sc_template+"\' data=\'"+sc_data+"\' color=\'"+sc_color+"\' label=\'"+sc_label+"\' interpolation=\'"+sc_interpolation+"\' timescale=\'"+sc_timescale+"\' valuescale=\'"+sc_valuescale+"\' guideline=\'"+sc_guideline+"\' height=\'"+sc_height+"\' periodtype=\'"+sc_period_type+"\'"';
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '+sc_' . $i;
        }
        $content .= '+"]";';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$("#' . $name . '-measurements-shortcode-' . $this->station_guid . '").html(shortcode);';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_query_ltgraph_code", data:sc_data, cache:"no_cache", mode:"' . self::$module_mode . '", type:"' . $this->module_type . '", device_id:sc_device, module_id:sc_module, measurement:sc_measurement, template:sc_template, label:sc_label, color:sc_color, interpolation:sc_interpolation, timescale:sc_timescale, valuescale:sc_valuescale, guideline:sc_guideline, height:sc_height, periodtype:sc_period_type, ';
        $t = array();
        for ($i=1; $i<=$this->series_number; $i++) {
            $u = array();
            foreach ($this->ltgraph_allowed_series as $param) {
                $u[] = $param . '_' . $i . ':sc_' . str_replace('_id', '', $param) . '_' . $i;
            }
            $t[] = implode(', ', $u);
        }
        $content .= implode(', ', $t);
        $content .= '}).done(function(data) {$("#lws-graph-preview").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';
        $content .= '}';
        $content .= '});';
        $content .= '$("#' . $name . '-measurements-module-' . $this->station_guid . '").change();';
        $content .= '$("#' . $name . '-measurements-period-type-' . $this->station_guid . '").change();';
        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_preview() {
        $content = '<div id="lws-graph-preview"></div>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content);
    }
}

