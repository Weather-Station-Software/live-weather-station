<?php

namespace WeatherStation\Engine\Module\Current;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter textual form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
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
    }


    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.4.0
     */
    public function __construct($station_information) {
        self::$module_mode = 'current';
        $this->module_type = 'textual';
        $this->module_name = ucfirst(__('textual data', 'live-weather-station'));
        $this->module_hint = __('Display current data as textual values.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-note-2';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.4.0
     */
    protected function enqueue_resources() {
        // No style or script ;)
    }

    /**
     * Prepare the data.
     *
     * @since 3.4.0
     */
    protected function prepare() {
        $js_array_textual = $this->get_all_stations_array(true, false, false, true, false, false, false, false, array($this->station_guid), false, false, true);
        if (array_key_exists($this->station_guid, $js_array_textual)) {
            if (array_key_exists(2, $js_array_textual[$this->station_guid])) {
                $this->data = $js_array_textual[$this->station_guid][2];
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
        $content .= $this->get_assoc_option_select('current-textual-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('current-textual-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_neutral_option_select('current-textual-datas-element-'. $this->station_guid, __('Element', 'live-weather-station'));
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
        $content .= $this->get_neutral_option_select('current-textual-datas-format-'. $this->station_guid, __('Format', 'live-weather-station'));
        $content .= $this->get_placeholder_option_select();
        $content .= $this->get_placeholder_option_select();
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
        $content .= '$("#current-textual-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_current_textual_measurement_' . $this->station_guid . ' = js_array_current_textual_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#current-textual-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_current_textual_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#current-textual-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_current_textual_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#current-textual-datas-measurement-' . $this->station_guid . '" ).change();});';
        
        $content .= '$("#current-textual-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_current_textual_element_' . $this->station_guid . ' = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][2][$(this).val()][2];';
        $content .= '$("#current-textual-datas-element-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_current_textual_element_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#current-textual-datas-element-' . $this->station_guid . '").append("<option value="+i+">"+js_array_current_textual_element_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#current-textual-datas-element-' . $this->station_guid . '" ).change();});';

        $content .= '$("#current-textual-datas-element-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_current_textual_format_' . $this->station_guid . ' = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][2][$("#current-textual-datas-measurement-' . $this->station_guid . '").val()][2][$(this).val()][2];';
        $content .= '$("#current-textual-datas-format-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_current_textual_format_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#current-textual-datas-format-' . $this->station_guid . '").append("<option value="+i+">"+js_array_current_textual_format_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#current-textual-datas-format-' . $this->station_guid . '" ).change();});';

        $content .= '$("#current-textual-datas-format-' . $this->station_guid . '").change(function() {';
        $content .= 'var output = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][2][$("#current-textual-datas-measurement-' . $this->station_guid . '").val()][2][$("#current-textual-datas-element-' . $this->station_guid . '").val()][2][$(this).val()][2];';
        $content .= 'var sc_device = "' . $this->station_id . '";';
        $content .= 'var sc_module = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_measurement = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][2][$("#current-textual-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_element = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][2][$("#current-textual-datas-measurement-' . $this->station_guid . '").val()][2][$("#current-textual-datas-element-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_format = js_array_current_textual_' . $this->station_guid . '[$("#current-textual-datas-module-' . $this->station_guid . '").val()][2][$("#current-textual-datas-measurement-' . $this->station_guid . '").val()][2][$("#current-textual-datas-element-' . $this->station_guid . '").val()][2][$("#current-textual-datas-format-' . $this->station_guid . '").val()][1];';
        $content .= 'var shortcode = "[live-weather-station-textual device_id=\'"+sc_device+"\' module_id=\'"+sc_module+"\' measure_type=\'"+sc_measurement+"\' element=\'"+sc_element+"\' format=\'"+sc_format+"\']";';
        $content .= '$("#current-textual-datas-output-' . $this->station_guid . '").html(output);';
        $content .= '$("#current-textual-datas-shortcode-' . $this->station_guid . '").html(shortcode);});';

        $content .= '$("#current-textual-datas-module-' . $this->station_guid . '" ).change();';
        
        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_preview() {
        $id = 'current-textual-datas-output-'. $this->station_guid;
        $content = '<textarea readonly rows="1" style="width:100%;font-weight:bold;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="' . $id . '"></textarea>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content);
    }
}

