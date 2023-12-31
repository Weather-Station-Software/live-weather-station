<?php

namespace WeatherStation\Engine\Module\Climat;

use WeatherStation\Data\Output;

/**
 * Class to generate parameter climat radial form.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class Radial extends \WeatherStation\Engine\Module\Maintainer {

    use Output;

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.8.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'climat';
        $this->module_type = 'radial';
        $this->module_name = ucfirst(__('radial weather', 'live-weather-station'));
        $this->module_hint = __('Display long-term temperatures and precipitations as a beautiful radial graph.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-pie-chart-11';
        $this->layout = '12-3-4';
        $this->series_number = 1;
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.8.0
     */
    protected function enqueue_resources() {
        wp_enqueue_style('lws-d4');
        wp_enqueue_script('lws-d4');
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
        $this->period = $this->get_period_value_js_array($this->station_information, false, false);
    }

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.8.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('climat-radial-measurements-period-type-'. $this->station_guid, __('Period type', 'live-weather-station'), $this->get_period_type_js_array(false, false, false, true, true), true, 'aggregated-year');
        $content .= $this->get_neutral_option_select('climat-radial-measurements-period-value-' . $this->station_guid, __('Period', 'live-weather-station'));
        $content .= $this->get_key_value_option_select('climat-radial-measurements-values-'. $this->station_guid, __('Values', 'live-weather-station'), $this->get_radial_values_js_array(), true, 'temperature-rain-threshold');
        $content .= $this->get_placeholder_option_select();
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
        $content .= $this->get_key_value_option_select('climat-radial-measurements-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('climat-radial-measurements-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(false, true), true, '400px');
        $content .= $this->get_key_value_option_select('climat-radial-measurements-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(true, false, true), true, 'auto');
        $content .= $this->get_key_value_option_select('climat-radial-measurements-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(false), true, 'inline');
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
        $content .= '$("#' . $name . '-measurements-period-type-' . $this->station_guid . '").change(function() {';
        $content .= '  var js_array_' . $js_name . '_p_' . $this->station_guid . ' = null;';
        $content .= '  $(js_array_' . $js_name . '_period_' . $this->station_guid . ').each(function (i) {';
        $content .= '    if (js_array_' . $js_name . '_period_' . $this->station_guid . '[i][0] == $("#' . $name . '-measurements-period-type-' . $this->station_guid . '").val()) {js_array_' . $js_name . '_p_' . $this->station_guid . '=js_array_' . $js_name . '_period_' . $this->station_guid . '[i][1]};});';
        $content .= '  $("#' . $name . '-measurements-period-value-' . $this->station_guid . '").html("");';
        $content .= '  $(js_array_' . $js_name . '_p_' . $this->station_guid . ').each(function (i) {';
        $content .= '    $("#' . $name . '-measurements-period-value-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][1]+"</option>");});';
        $content .= '$("#' . $name . '-measurements-period-value-' . $this->station_guid . '" ).change();';
        $content .= '});';
        $content .= '$("#' . $name . '-measurements-period-value-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-values-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-values-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-template-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-height-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-height-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-valuescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-valuescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-measurements-data-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-measurements-data-' . $this->station_guid . '").change(function() {';
        $content .= '  var sc_device = "' . $this->station_id . '";';
        $content .= '  var sc_period_type = $("#' . $name . '-measurements-period-type-' . $this->station_guid . '").val();';
        $content .= '  var sc_period = $("#' . $name . '-measurements-period-value-' . $this->station_guid . '").val();';
        $content .= '  var sc_values = $("#' . $name . '-measurements-values-' . $this->station_guid . '").val();';
        $content .= '  var sc_template = $("#' . $name . '-measurements-template-' . $this->station_guid . '").val();';
        $content .= '  var sc_height = $("#' . $name . '-measurements-height-' . $this->station_guid . '").val();';
        $content .= '  var sc_valuescale = $("#' . $name . '-measurements-valuescale-' . $this->station_guid . '").val();';
        $content .= '  var sc_data = $("#' . $name . '-measurements-data-' . $this->station_guid . '").val();';
        $content .= '  var shortcode = "[live-weather-station-radial mode=\'' . self::$module_mode . '\' type=\'' . $this->module_type . '\' device_id=\'"+sc_device+"\' periodtype=\'"+sc_period_type+"\' period=\'"+sc_period+"\' values=\'"+sc_values+"\' template=\'"+sc_template+"\' valuescale=\'"+sc_valuescale+"\' data=\'"+sc_data+"\' height=\'"+sc_height+"\']";';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$("#' . $name . '-measurements-shortcode-' . $this->station_guid . '").html(shortcode);';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_query_radial_code", data:sc_data, cache:"no_cache", mode:"' . self::$module_mode . '", type:"' . $this->module_type . '", device_id:sc_device, periodtype:sc_period_type, period:sc_period, template:sc_template, values:sc_values, valuescale:sc_valuescale, height:sc_height ';
        $content .= '}).done(function(data) {$("#lws-graph-preview").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';
        $content .= '});';
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

