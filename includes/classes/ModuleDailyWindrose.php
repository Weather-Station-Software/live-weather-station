<?php

namespace WeatherStation\Engine\Module\Daily;

/**
 * Class to generate parameter daily windrose form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */
class Windrose extends \WeatherStation\Engine\Module\Maintainer {

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.5.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'daily';
        $this->module_type = 'windrose';
        $this->module_name = ucfirst(__('radial bar chart', 'live-weather-station'));
        $this->module_hint = __('Display daily data as a radial bar chart. Particularly suitable to render wind data as windrose, this graph allows to view the segmented distribution of a measurement according to an angle.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-pie-chart-7';
        $this->layout = '12-3-4';
        $this->series_number = 2;
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.5.0
     */
    protected function enqueue_resources() {
        wp_enqueue_script('lws-d4');
        wp_enqueue_script('lws-scale-radial');
        wp_enqueue_script('lws-windrose');
        wp_enqueue_script('lws-colorbrewer');
        wp_enqueue_script('lws-spin');
    }

    /**
     * Prepare the data.
     *
     * @since 3.5.0
     */
    protected function prepare() {
        $js_array_dailyline = $this->get_all_stations_array(false, false, true, true, true, false, true, true, array($this->station_guid));
        if (array_key_exists($this->station_guid, $js_array_dailyline)) {
            if (array_key_exists(2, $js_array_dailyline[$this->station_guid])) {
                $this->data = $js_array_dailyline[$this->station_guid][2];
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
        $content .= $this->get_key_value_option_select('daily-windrose-datas-dimension-' . $this->station_guid, __('Dimension', 'live-weather-station'), $this->get_comparable_dimensions_js_array(), true, 'angle', true, false);
        $a_group = array();
        for ($i=1; $i<=$this->series_number; $i++) {
            $group = $this->get_assoc_option_select('daily-windrose-datas-module-' . $i . '-' . $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
            $group .= $this->get_neutral_option_select('daily-windrose-datas-measurement-' . $i . '-' . $this->station_guid, __('Measurement', 'live-weather-station'));
            if ($i == 1) {
                $group .= $this->get_key_value_option_select('daily-windrose-datas-line-mode-' . $i . '-' . $this->station_guid, __('Allotment', 'live-weather-station'), $this->get_allotment_js_array(3), true, '8s');
            }
            else {
                $group .= $this->get_key_value_option_select('daily-windrose-datas-line-mode-' . $i . '-' . $this->station_guid, __('Breakdown', 'live-weather-station'), $this->get_color_threshold_js_array(), true, 'color-step-4');
            }
            $group .= $this->get_key_value_option_select('daily-windrose-datas-dot-style-' . $i . '-' . $this->station_guid, __('Resolution', 'live-weather-station'), $this->get_stream_resolution_js_array(), true, 'res-10', true, false);
            $group .= $this->get_key_value_option_select('daily-windrose-datas-line-style-' . $i . '-' . $this->station_guid, __('Line style', 'live-weather-station'), $this->get_line_style_js_array(), true, 'solid', true, false);
            $group .= $this->get_key_value_option_select('daily-windrose-datas-line-size-' . $i . '-' . $this->station_guid, __('Line size', 'live-weather-station'), $this->get_line_size_js_array(), true, 'regular', true, false);
            if ($i == 1) {
                $a_group[] = array('content' => $group, 'name' => sprintf(__('Angle', 'live-weather-station'), $i));
            }
            else {
                $a_group[] = array('content' => $group, 'name' => sprintf(__('Measurement', 'live-weather-station'), $i));
            }

        }
        $content .= $this->get_group('daily-windrose-datas-measure-group-', $a_group);
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
     * @since 3.5.0
     */
    protected function get_parameters() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('daily-windrose-datas-template-'. $this->station_guid, __('Template', 'live-weather-station'), $this->get_graph_template_js_array(), true, 'neutral');
        $content .= $this->get_key_value_option_select('daily-windrose-datas-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array(true, true, true, false, true, true), true, 'self');
        $content .= $this->get_key_value_option_select('daily-windrose-datas-label-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_multi_2_label_js_array(), true, 'simple');
        $content .= $this->get_key_value_option_select('daily-windrose-datas-guideline-'. $this->station_guid, __('Legend', 'live-weather-station'), $this->get_legend_js_array(), true, 'interactive');
        $content .= $this->get_key_value_option_select('daily-windrose-datas-height-'. $this->station_guid, __('Height', 'live-weather-station'), $this->get_graph_size_js_array(), true, '300px');
        $content .= $this->get_key_value_option_select('daily-windrose-datas-timescale-'. $this->station_guid, __('Time scale', 'live-weather-station'), $this->get_x_scale_js_array(false), true, 'auto', true, false);
        $content .= $this->get_key_value_option_select('daily-windrose-datas-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(false, true), true, 'auto');
        $content .= $this->get_key_value_option_select('daily-windrose-datas-interpolation-'. $this->station_guid, __('Interpolation', 'live-weather-station'), $this->get_color_threshold_js_array(), true, 'color-step-4', true, false);
        $content .= $this->get_key_value_option_select('daily-windrose-datas-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(), true, 'inline');
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
        $content .= '$("#daily-windrose-datas-dimension-' . $this->station_guid . '").change();';
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

