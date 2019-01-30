<?php

namespace WeatherStation\Engine\Module\Yearly;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter timelapse form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class Timelapse extends \WeatherStation\Engine\Module\Maintainer {

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
     * @since 3.6.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'yearly';
        $this->module_type = 'timelapse';
        $this->module_name = __('Timelapse viewer', 'live-weather-station');
        $this->module_hint = __('Display a recorded timelapse.', 'live-weather-station');
        $this->module_icon = LWS_FAS . (LWS_FA5?' fa-stopwatch':' fa-stopwatch') . ' fa-fw';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.6.0
     */
    protected function enqueue_resources() {
        wp_enqueue_script('lws-lcd');
    }

    /**
     * Prepare the data.
     *
     * @since 3.6.0
     */
    protected function prepare() {
        $js_array_timelapse = $this->get_all_stations_array(false, false, false, false, false, false, false, false, array($this->station_guid), false, false, false, true);
        if (array_key_exists($this->station_guid, $js_array_timelapse)) {
            if (array_key_exists(2, $js_array_timelapse[$this->station_guid])) {
                $this->data = $js_array_timelapse[$this->station_guid][2];
            }
        }
        else {
            $this->data = null;
        }
        $this->period = $this->get_timelapse_period_value_js_array($this->station_information);
    }

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.6.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-period-type-'. $this->station_guid, __('Period type', 'live-weather-station'), $this->get_timelapse_period_type_js_array(), true, 'sliding-month');
        $content .= $this->get_neutral_option_select('yearly-timelapse-datas-period-value-'. $this->station_guid, __('Period', 'live-weather-station'));
        $content .= $this->get_assoc_option_select('yearly-timelapse-datas-module-1-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('yearly-timelapse-datas-measurement-1-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_neutral_option_select('yearly-timelapse-datas-set-1-'. $this->station_guid, __('Dataset', 'live-weather-station'), false, false);
        $content .= '</tbody></table>';
        return $this->get_box('lws-datasource-id', $this->datasource_title, $content);
    }

    /**
     * Print the parameters section of the form.
     *
     * @return string The parameters section, ready to be printed.
     * @since 3.6.0
     */
    protected function get_parameters() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-template-'. $this->station_guid, __('Size', 'live-weather-station'), $this->get_size_js_array(true, true), true, 'large');
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-color-'. $this->station_guid, __('Color scheme', 'live-weather-station'), $this->get_colorbrewer_js_array(false), true, 'Blues', false, false);
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-label-'. $this->station_guid, __('Appearance', 'live-weather-station'), $this->get_video_appearance_js_array(), true, 'full');
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-guideline-'. $this->station_guid, __('Behavior', 'live-weather-station'), $this->get_video_behavior_js_array(), true, 'manual');
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-height-'. $this->station_guid, __('Mode', 'live-weather-station'), $this->get_video_mode_js_array(), true, 'once');
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-timescale-'. $this->station_guid, __('Time scale', 'live-weather-station'), $this->get_x_scale_js_array(true), true, 'auto', false, false);
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-valuescale-'. $this->station_guid, __('Value scale', 'live-weather-station'), $this->get_y_scale_js_array(true), true, 'auto', false, false);
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-interpolation-'. $this->station_guid, __('Interpolation', 'live-weather-station'), $this->get_interpolation_js_array(), true, 'none', false, false);
        $content .= $this->get_key_value_option_select('yearly-timelapse-datas-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(), true, 'inline', false, false);
        $content .= '</tbody></table>';
        return $this->get_box('lws-parameter-id', $this->parameter_title, $content);
    }

    /**
     * Print the script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.6.0
     */
    protected function get_script() {
        $content = $this->get_standard_script();
        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.6.0
     */
    protected function get_preview() {
        $content = '<div id="lws-graph-preview"></div>';
        $content .= '<div id="' . $this->fingerprint . '" style="padding:0px;"></div>';
        $special_footer  = '<span id="yearly-timelapse-info-' . $this->station_guid . '" style="display: none;">';
        $special_footer .= '<div id="major-publishing-actions">';
        $special_footer .= __('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );
        $special_footer .= '</div>';
        $special_footer .= '</span>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content, '', $special_footer);
    }
}

