<?php

namespace WeatherStation\Engine\Module\Current;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter lcd form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */
class Lcd extends \WeatherStation\Engine\Module\Maintainer {

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
        $this->module_type = 'lcd';
        $this->module_name = __('LCD display', 'live-weather-station');
        $this->module_hint = __('Display current data, cyclically, in a LCD display.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-lcd';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.4.0
     */
    protected function enqueue_resources() {
        wp_enqueue_script('lws-lcd');
    }

    /**
     * Prepare the data.
     *
     * @since 3.4.0
     */
    protected function prepare() {
        $js_array_lcd = $this->get_all_stations_array(false, true, true, false, false, false, false, false, array($this->station_guid), false, false, true);
        if (array_key_exists($this->station_guid, $js_array_lcd)) {
            if (array_key_exists(2, $js_array_lcd[$this->station_guid])) {
                $this->data = $js_array_lcd[$this->station_guid][2];
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
        $content .= $this->get_assoc_option_select('current-lcd-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('current-lcd-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
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
        $content .= $this->get_key_value_option_select('current-lcd-datas-design-'. $this->station_guid, __('Design', 'live-weather-station'), $this->get_lcd_design_js_array());
        $content .= $this->get_key_value_option_select('current-lcd-datas-size-'. $this->station_guid, __('Size', 'live-weather-station'), $this->get_size_js_array());
        $content .= $this->get_key_value_option_select('current-lcd-datas-speed-'. $this->station_guid, __('Speed', 'live-weather-station'), $this->get_lcd_speed_js_array());
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
        $content .= 'var c' . $this->fingerprint . ' = new lws_lcd.LCDPanel({';
        $content .= 'id: "id' . $this->fingerprint . '",';
        $content .= 'parentId: "' . $this->fingerprint . '",';
        $content .= 'upperCenterText: "' . $this->station_name . '",';
        $content .= 'qDevice: "' . $this->station_id . '",';
        $content .= 'qModule: "aggregated",';
        $content .= 'qMeasure: "aggregated",';
        $content .= 'qPostUrl: "' . LWS_AJAX_URL . '"});';
        $content .= '$("#current-lcd-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'c' . $this->fingerprint . '.setModule(js_array_current_lcd_' . $this->station_guid . '[$("#current-lcd-datas-module-' . $this->station_guid . '").val()][1]);';
        $content .= 'var js_array_current_lcd_measurement_' . $this->station_guid . ' = js_array_current_lcd_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#current-lcd-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_current_lcd_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#current-lcd-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_current_lcd_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#current-lcd-datas-measurement-' . $this->station_guid . '" ).change();});';
        
        $content .= '$("#current-lcd-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= 'c' . $this->fingerprint . '.setMeasure(js_array_current_lcd_' . $this->station_guid . '[$("#current-lcd-datas-module-' . $this->station_guid . '").val()][2][$("#current-lcd-datas-measurement-' . $this->station_guid . '").val()][1]);';
        $content .= '$( "#current-lcd-datas-design-' . $this->station_guid . '" ).change();});';

        $content .= '$("#current-lcd-datas-design-' . $this->station_guid . '").change(function() {';
        $content .= 'c' . $this->fingerprint . '.setDesign($("#current-lcd-datas-design-' . $this->station_guid . '").val());';
        $content .= '$("#current-lcd-datas-size-' . $this->station_guid . '" ).change();});';

        $content .= '$("#current-lcd-datas-size-' . $this->station_guid . '").change(function() {';
        $content .= 'if ($("#current-lcd-datas-size-' . $this->station_guid . '").val()=="scalable") {';
        $content .= 'c' . $this->fingerprint . '.setSize("small", false);';
        $content .= '$("#current-lcd-info-' . $this->station_guid . '").show();}';
        $content .= 'else {c' . $this->fingerprint . '.setSize($("#current-lcd-datas-size-' . $this->station_guid . '").val(), false);';
        $content .= '$("#current-lcd-info-' . $this->station_guid . '").hide();}';
        $content .= '$("#current-lcd-datas-speed-' . $this->station_guid . '" ).change();});';

        $content .= '$("#current-lcd-datas-speed-' . $this->station_guid . '").change(function() {';
        $content .= 'c' . $this->fingerprint . '.setCycleSpeed($("#current-lcd-datas-speed-' . $this->station_guid . '").val());';
        $content .= 'var sc_device = "' . $this->station_id . '";';
        $content .= 'var sc_module = js_array_current_lcd_' . $this->station_guid . '[$("#current-lcd-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_measurement = js_array_current_lcd_' . $this->station_guid . '[$("#current-lcd-datas-module-' . $this->station_guid . '").val()][2][$("#current-lcd-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_design = $("#current-lcd-datas-design-' . $this->station_guid . '").val();';
        $content .= 'var sc_size = $("#current-lcd-datas-size-' . $this->station_guid . '").val();';
        $content .= 'var sc_speed = $("#current-lcd-datas-speed-' . $this->station_guid . '").val();';
        $content .= 'var shortcode = "[live-weather-station-lcd device_id=\'"+sc_device+"\' module_id=\'"+sc_module+"\' measure_type=\'"+sc_measurement+"\' design=\'"+sc_design+"\' size=\'"+sc_size+"\' speed=\'"+sc_speed+"\']";';
        $content .= '$("#current-lcd-datas-shortcode-' . $this->station_guid . '").html(shortcode);});';

        $content .= '$("#current-lcd-datas-module-' . $this->station_guid . '" ).change();';

        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_preview() {
        $content = '<div>&nbsp;</div>';
        $content .= '<div id="' . $this->fingerprint . '" style="padding:0px;"></div>';
        $special_footer  = '<span id="current-lcd-info-' . $this->station_guid . '" style="display: none;">';
        $special_footer .= '<div id="major-publishing-actions">';
        $special_footer .= __('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );
        $special_footer .= '</div>';
        $special_footer .= '</span>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content, '', $special_footer);
    }
}

