<?php

namespace WeatherStation\Engine\Module\Yearly;

/**
 * Class to generate parameter yearly bar form.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */
class Bar extends \WeatherStation\Engine\Module\Maintainer {

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.5.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'yearly';
        $this->module_type = 'bar';
        $this->module_name = ucfirst(__('single bar', 'live-weather-station'));
        $this->module_hint = __('Display historical data as a bar chart. Allows to view a single dataset from a single type of measurement.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-bar-chart-up';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.5.0
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
     * @since 3.5.0
     */
    protected function prepare() {
        $js_array_yearlybar = $this->get_all_stations_array(false, false, true, true, true, false, true, false, array($this->station_guid));
        if (array_key_exists($this->station_guid, $js_array_yearlybar)) {
            if (array_key_exists(2, $js_array_yearlybar[$this->station_guid])) {
                $this->data = $js_array_yearlybar[$this->station_guid][2];
            }
        }
        else {
            $this->data = null;
        }
        $this->period = $this->get_period_value_js_array($this->station_information);
    }

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.5.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-period-type-'. $this->station_guid, __('Period type', 'live-weather-station'), $this->get_period_type_js_array(), true, 'sliding-month');
        $content .= $this->get_neutral_option_select('yearly-bar-measurements-period-value-'. $this->station_guid, __('Period', 'live-weather-station'));
        $content .= $this->get_assoc_option_select('yearly-bar-measurements-module-1-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('yearly-bar-measurements-measurement-1-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_neutral_option_select('yearly-bar-measurements-set-1-'. $this->station_guid, __('Dataset', 'live-weather-station'));
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-line-mode-1-'. $this->station_guid, '', $this->get_line_mode_js_array(), true, 'line', true);
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-dot-style-1-'. $this->station_guid, '', $this->get_dot_style_js_array(), true, 'none', true);
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-line-style-1-'. $this->station_guid, '', $this->get_line_style_js_array(), true, 'solid', true, false);
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-line-size-1-'. $this->station_guid, '', $this->get_line_size_js_array(), true, 'regular', true, false);
        $content .= '</tbody></table>';
        return $this->get_box('lws-datasource-id', $this->datasource_title, $content);
    }

    /**
     * Print the parameters section of the form.
     *
     * @return string The parameters section, ready to be printed.
     * @since 3.5.0
     */
    protected function get_parameters() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array(true, true, true, true, false));
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-label-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_label_js_array(), true, 'simple');
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-guideline-'. $this->station_guid, __('Hint', 'live-weather-station'), $this->get_guideline_js_array(), true, 'standard', true, false);
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(), true, '300px');
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-timescale-'. $this->station_guid, __('Time scale', 'live-weather-station'), $this->get_x_scale_js_array(false), true, 'auto');
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(true), true, 'auto');
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-interpolation-'. $this->station_guid, __('Interpolation', 'live-weather-station'), $this->get_interpolation_js_array(), true, 'none', true, false );
        $content .= $this->get_key_value_option_select('yearly-bar-measurements-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(), true, 'inline');
        $content .= '</tbody></table>';
        return $this->get_box('lws-parameter-id', $this->parameter_title, $content);
    }

    /**
     * Print the script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.5.0
     */
    protected function get_script() {
        $content = $this->get_standard_script();
        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.5.0
     */
    protected function get_preview() {
        $content = '<div id="lws-graph-preview"></div>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content);
    }
}

