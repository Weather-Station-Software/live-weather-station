<?php

namespace WeatherStation\Engine\Module\Daily;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter daily line form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */
class Line extends \WeatherStation\Engine\Module\Maintainer {

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
        $this->module_id = 'daily-line';
        $this->module_name = ucfirst(__('single line', 'live-weather-station'));
        $this->module_hint = __('Display daily data as a line chart. Allows to view a single type of measurement.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-line-chart-5';
        $this->layout = '12-3-4';
        parent::__construct($station_guid, $station_id, $station_name);
    }

    /**
     * Prepare the data.
     *
     * @since 3.4.0
     */
    protected function prepare() {
        $js_array_dailyline = $this->get_all_stations_array(false, false, true, true, true, true, false, false, array($this->station_guid));
        if (array_key_exists($this->station_guid, $js_array_dailyline)) {
            if (array_key_exists(2, $js_array_dailyline[$this->station_guid])) {
                $this->data = $js_array_dailyline[$this->station_guid][2];
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
        $content .= $this->get_assoc_option_select('daily-line-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('daily-line-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_key_value_option_select('daily-line-datas-line-mode-'. $this->station_guid, __('Mode', 'live-weather-station'), $this->get_line_mode_js_array(), true, 'line');
        $content .= $this->get_key_value_option_select('daily-line-datas-dot-style-'. $this->station_guid, __('Values display', 'live-weather-station'), $this->get_dot_style_js_array(), true, 'none');
        $content .= $this->get_key_value_option_select('daily-line-datas-line-style-'. $this->station_guid, __('Line style', 'live-weather-station'), $this->get_line_style_js_array(), true, 'solid');
        $content .= $this->get_key_value_option_select('daily-line-datas-line-size-'. $this->station_guid, __('Line size', 'live-weather-station'), $this->get_line_size_js_array(), true, 'regular');
        $content .= $this->get_placeholder_option_select();
        $content .= $this->get_placeholder_option_select();
        $content .= $this->get_placeholder_option_select();
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
        $content .= $this->get_key_value_option_select('daily-line-datas-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('daily-line-datas-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array());
        $content .= $this->get_key_value_option_select('daily-line-datas-label-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_label_js_array(), true, 'standard');
        $content .= $this->get_key_value_option_select('daily-line-datas-guideline-'. $this->station_guid, __('Hint', 'live-weather-station'), $this->get_guideline_js_array(), true, 'standard');
        $content .= $this->get_key_value_option_select('daily-line-datas-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(), true, '300px');
        $content .= $this->get_key_value_option_select('daily-line-datas-timescale-'. $this->station_guid, __('Time scale', 'live-weather-station'), $this->get_x_scale_js_array(), true, 'auto');
        $content .= $this->get_key_value_option_select('daily-line-datas-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(), true, 'auto');
        $content .= $this->get_key_value_option_select('daily-line-datas-interpolation-'. $this->station_guid, __('Interpolation', 'live-weather-station'), $this->get_interpolation_js_array(), true, 'none');
        $content .= $this->get_key_value_option_select('daily-line-datas-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(), true, 'inline');
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
        $content .= '$("#daily-line-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_daily_line_measurement_' . $this->station_guid . ' = js_array_daily_line_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#daily-line-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_daily_line_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#daily-line-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_daily_line_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#daily-line-datas-measurement-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-line-mode-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-line-mode-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-dot-style-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-dot-style-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-line-style-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-line-style-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-line-size-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-line-size-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-template-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-template-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-interpolation-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-interpolation-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-timescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-timescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-valuescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-valuescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-guideline-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-guideline-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-height-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-height-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-label-' . $this->station_guid . '" ).change();});';
        $content .= '$("#daily-line-datas-label-' . $this->station_guid . '").change(function() {';
        $content .= '$("#daily-line-datas-data-' . $this->station_guid . '" ).change();});';

        $content .= '$("#daily-line-datas-data-' . $this->station_guid . '").change(function() {';
        $content .= 'var sc_device_1 = "' . $this->station_id . '";';
        $content .= 'var sc_module_1 = js_array_daily_line_' . $this->station_guid . '[$("#daily-line-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_measurement_1 = js_array_daily_line_' . $this->station_guid . '[$("#daily-line-datas-module-' . $this->station_guid . '").val()][2][$("#daily-line-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_line_mode_1 = $("#daily-line-datas-line-mode-' . $this->station_guid . '").val();';
        $content .= 'var sc_dot_style_1 = $("#daily-line-datas-dot-style-' . $this->station_guid . '").val();';
        $content .= 'var sc_line_style_1 = $("#daily-line-datas-line-style-' . $this->station_guid . '").val();';
        $content .= 'var sc_line_size_1 = $("#daily-line-datas-line-size-' . $this->station_guid . '").val();';

        $content .= 'if ($("#daily-line-datas-line-mode-' . $this->station_guid . '").val() == "transparent") {';
        $content .= '$("#daily-line-datas-line-style-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#daily-line-datas-line-size-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#daily-line-datas-line-style-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#daily-line-datas-line-size-' . $this->station_guid . '").prop("disabled", false);}';

        $content .= 'var sc_template = $("#daily-line-datas-template-' . $this->station_guid . '").val();';
        $content .= 'var sc_color = $("#daily-line-datas-color-' . $this->station_guid . '").val();';
        $content .= 'var sc_interpolation = $("#daily-line-datas-interpolation-' . $this->station_guid . '").val();';
        $content .= 'var sc_timescale = $("#daily-line-datas-timescale-' . $this->station_guid . '").val();';
        $content .= 'var sc_valuescale = $("#daily-line-datas-valuescale-' . $this->station_guid . '").val();';
        $content .= 'var sc_guideline = $("#daily-line-datas-guideline-' . $this->station_guid . '").val();';
        $content .= 'var sc_height = $("#daily-line-datas-height-' . $this->station_guid . '").val();';
        $content .= 'var sc_label = $("#daily-line-datas-label-' . $this->station_guid . '").val();';
        $content .= 'var sc_data = $("#daily-line-datas-data-' . $this->station_guid . '").val();';

        $content .= 'var shortcode = "[live-weather-station-graph mode=\'daily\' type=\'line\' template=\'"+sc_template+"\' data=\'"+sc_data+"\' color=\'"+sc_color+"\' label=\'"+sc_label+"\' interpolation=\'"+sc_interpolation+"\' timescale=\'"+sc_timescale+"\' valuescale=\'"+sc_valuescale+"\' guideline=\'"+sc_guideline+"\' height=\'"+sc_height+"\' device_id_1=\'"+sc_device_1+"\' module_id_1=\'"+sc_module_1+"\' measurement_1=\'"+sc_measurement_1+"\' line_mode_1=\'"+sc_line_mode_1+"\' dot_style_1=\'"+sc_dot_style_1+"\' line_style_1=\'"+sc_line_style_1+"\' line_size_1=\'"+sc_line_size_1+"\']";';

        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_query_graph_code", data:sc_data, cache:"no_cache", mode:"daily", type:"line", template:sc_template, label:sc_label, color:sc_color, interpolation:sc_interpolation, timescale:sc_timescale, valuescale:sc_valuescale, guideline:sc_guideline, height:sc_height, device_id_1:sc_device_1, module_id_1:sc_module_1, measurement_1:sc_measurement_1, line_mode_1:sc_line_mode_1, dot_style_1:sc_dot_style_1, line_style_1:sc_line_style_1, line_size_1:sc_line_size_1}).done(function(data) {$("#lws-graph-preview").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';

        $content .= '$("#daily-line-datas-shortcode-' . $this->station_guid . '").html(shortcode);});';
        $content .= '$("#daily-line-datas-module-' . $this->station_guid . '" ).change();';
        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_preview() {
        $content = '<div id="lws-graph-preview"></div>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content);
    }
}

