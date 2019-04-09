<?php

namespace WeatherStation\Engine\Module\Climat;

/**
 * Class to generate parameter climat calendar heatmap form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class CalendarHM extends \WeatherStation\Engine\Module\Maintainer {

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station informations.
     * @since 3.8.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'climat';
        $this->module_type = 'calendarhm';
        $this->module_name = ucfirst(__('calendar heatmap', 'live-weather-station'));
        $this->module_hint = __('Display long-term deviation as a heatmap. Allows to view the climatological average-based deviation of a single dataset from a single type of measurement.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-calendar-time';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.8.0
     */
    protected function enqueue_resources() {
        wp_enqueue_style('lws-nvd3');
        wp_enqueue_style('lws-cal-heatmap');
        wp_enqueue_script('lws-cal-heatmap');
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
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-period-type-'. $this->station_guid, __('Period type', 'live-weather-station'), $this->get_period_type_js_array(false, true), true, 'fixed-month');
        $content .= $this->get_neutral_option_select('climat-calendarhm-datas-period-value-'. $this->station_guid, __('Period', 'live-weather-station'));
        $content .= $this->get_assoc_option_select('climat-calendarhm-datas-module-1-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('climat-calendarhm-datas-measurement-1-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_neutral_option_select('climat-calendarhm-datas-set-1-'. $this->station_guid, __('Dataset', 'live-weather-station'));
        $content .= $this->get_placeholder_option_select();
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
     * @since 3.8.0
     */
    protected function get_parameters() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array(true, true, true, false));
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-interpolation-'. $this->station_guid, __('Color thresholds', 'live-weather-station'), $this->get_color_threshold_js_array(), true, 'color-step-5');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-timescale-'. $this->station_guid, __('Day format', 'live-weather-station'), $this->get_day_format_js_array(), true, 'rdsquare');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-label-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_multi_label_js_array(), true, 'simple');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-guideline-'. $this->station_guid, __('Legend', 'live-weather-station'), $this->get_legend_position_js_array(), true, 'center');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(), true, '200px');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(true, true), true, 'auto');
        $content .= $this->get_key_value_option_select('climat-calendarhm-datas-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(false), true, 'inline');
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
        $content = $this->get_standard_script();
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

