<?php

namespace WeatherStation\Engine\Module\Daily;

/**
 * Class to generate parameter daily lines form.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */
class Lines extends \WeatherStation\Engine\Module\Maintainer {

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.4.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'daily';
        $this->module_type = 'lines';
        $this->module_name = ucfirst(__('line series', 'live-weather-station'));
        $this->module_hint = __('Display daily data as multiple lines chart. Allows to view, side by side on the same graph, several types of measurement having the same physical dimension.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-line-chart-7';
        $this->layout = '12-3-4';
        $this->series_number = 8;
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.4.0
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
     * @since 3.4.0
     */
    protected function prepare() {
        $js_array_dailyline = $this->get_all_stations_array(false, false, true, true, true, true, false, true, array($this->station_guid));
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
        $content .= $this->get_key_value_option_select('daily-lines-measurements-dimension-' . $this->station_guid, __('Dimension', 'live-weather-station'), $this->get_comparable_dimensions_js_array(), true, 'temperature');
        $a_group = array();
        for ($i=1; $i<=$this->series_number; $i++) {
            $group = $this->get_assoc_option_select('daily-lines-measurements-module-' . $i . '-' . $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
            $group .= $this->get_neutral_option_select('daily-lines-measurements-measurement-' . $i . '-' . $this->station_guid, __('Measurement', 'live-weather-station'));
            $group .= $this->get_key_value_option_select('daily-lines-measurements-line-mode-' . $i . '-' . $this->station_guid, __('Mode', 'live-weather-station'), $this->get_line_mode_js_array(), true, 'line');
            $group .= $this->get_key_value_option_select('daily-lines-measurements-dot-style-' . $i . '-' . $this->station_guid, __('Values display', 'live-weather-station'), $this->get_dot_style_js_array(), true, 'none');
            $group .= $this->get_key_value_option_select('daily-lines-measurements-line-style-' . $i . '-' . $this->station_guid, __('Line style', 'live-weather-station'), $this->get_line_style_js_array(), true, 'solid');
            $group .= $this->get_key_value_option_select('daily-lines-measurements-line-size-' . $i . '-' . $this->station_guid, __('Line size', 'live-weather-station'), $this->get_line_size_js_array(), true, 'regular');
            $a_group[] = array('content' => $group, 'name' => sprintf(__('Measurement %s', 'live-weather-station'), $i));
        }
        $content .= $this->get_group('daily-lines-measurements-measure-group-', $a_group);
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
        $content .= $this->get_key_value_option_select('daily-lines-measurements-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array(true));
        $content .= $this->get_key_value_option_select('daily-lines-measurements-label-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_multi_label_js_array(), true, 'simple');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-guideline-'. $this->station_guid, __('Hint', 'live-weather-station'), $this->get_guideline_js_array(), true, 'standard');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(), true, '300px');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-timescale-'. $this->station_guid, __('Time scale', 'live-weather-station'), $this->get_x_scale_js_array(), true, 'auto');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(true), true, 'auto');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-interpolation-'. $this->station_guid, __('Interpolation', 'live-weather-station'), $this->get_interpolation_js_array(), true, 'none');
        $content .= $this->get_key_value_option_select('daily-lines-measurements-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(), true, 'inline');
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
        $content = $this->get_standard_script();
        $content .= '$("#daily-lines-measurements-dimension-' . $this->station_guid . '").change();';
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

