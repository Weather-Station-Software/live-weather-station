<?php

namespace WeatherStation\Engine\Module\Current;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate snapshot parameter form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class Snapshot extends \WeatherStation\Engine\Module\Maintainer {

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
        self::$module_mode = 'current';
        $this->module_type = 'snapshot';
        $this->module_name = ucfirst(__('snapshot', 'live-weather-station'));
        $this->module_hint = __('Display current snapshot from the station.', 'live-weather-station');
        $this->module_icon = LWS_FAS . (LWS_FA5?' fa-image':' fa-image') . ' fa-fw';
        $this->layout = '12-3-4';
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.6.0
     */
    protected function enqueue_resources() {
        // No style or script ;)
    }

    /**
     * Prepare the data.
     *
     * @since 3.6.0
     */
    protected function prepare() {
        $js_array_snapshot = $this->get_all_stations_array(false, false, false, false, false, false, false, false, array($this->station_guid), false, false, false, false, true);
        if (array_key_exists($this->station_guid, $js_array_snapshot)) {
            if (array_key_exists(2, $js_array_snapshot[$this->station_guid])) {
                $this->data = $js_array_snapshot[$this->station_guid][2];
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
     * @since 3.6.0
     */
    protected function get_datasource() {
        $content = '<table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_assoc_option_select('current-snapshot-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('current-snapshot-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
        $content .= $this->get_placeholder_option_select();
        $content .= $this->get_placeholder_option_select();
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
        $content .= $this->get_key_value_option_select('current-snapshot-datas-size-'. $this->station_guid,__('Size', 'live-weather-station'), $this->get_size_js_array(true, true), true, 'large');
        $content .= $this->get_key_value_option_select('current-snapshot-datas-data-'. $this->station_guid, __('Data', 'live-weather-station'), $this->get_graph_data_js_array(true, false), true, 'inline');
        $content .= $this->get_key_value_option_select('current-snapshot-datas-animation-'. $this->station_guid, __('Animation type', 'live-weather-station'), $this->get_picture_animation_js_array(), true, 'none');
        $content .= $this->get_key_value_option_select('current-snapshot-datas-speed-'. $this->station_guid, __('Animation speed', 'live-weather-station'), $this->get_lcd_speed_js_array(), true, '2000');
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
        $content = '';
        $content .= '$("#current-snapshot-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_current_snapshot_measurement_' . $this->station_guid . ' = js_array_current_snapshot_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#current-snapshot-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_current_snapshot_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#current-snapshot-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_current_snapshot_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$("#current-snapshot-datas-measurement-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-snapshot-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-snapshot-datas-size-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-snapshot-datas-size-' . $this->station_guid . '").change(function() {';
        $content .= 'if ($("#current-snapshot-datas-size-' . $this->station_guid . '").val()=="scalable") {';
        $content .= '$("#current-snapshot-info-' . $this->station_guid . '").show();}';
        $content .= 'else {';
        $content .= '$("#current-snapshot-info-' . $this->station_guid . '").hide();}';
        $content .= '$("#current-snapshot-datas-data-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-snapshot-datas-data-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-snapshot-datas-animation-' . $this->station_guid . '").prop("disabled", ($("#current-snapshot-datas-data-' . $this->station_guid . '").val()=="inline"));';
        $content .= '$("#current-snapshot-datas-speed-' . $this->station_guid . '").prop("disabled", ($("#current-snapshot-datas-data-' . $this->station_guid . '").val()=="inline"));';
        $content .= '$("#current-snapshot-datas-animation-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-snapshot-datas-animation-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-snapshot-datas-speed-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-snapshot-datas-speed-' . $this->station_guid . '").change(function() {';
        $content .= 'var sc_sc = "live-weather-station-snapshot";';
        $content .= 'if ($("#current-snapshot-datas-data-' . $this->station_guid . '").val() == "ajax_refresh") {sc_sc = "live-weather-station-livesnapshot";}';
        $content .= 'var sc_device = "' . $this->station_id . '";';
        $content .= 'var sc_animation = $("#current-snapshot-datas-animation-' . $this->station_guid . '").val();';
        $content .= 'var sc_speed = $("#current-snapshot-datas-speed-' . $this->station_guid . '").val();';
        $content .= 'var sc_module = js_array_current_snapshot_' . $this->station_guid . '[$("#current-snapshot-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_measurement = js_array_current_snapshot_' . $this->station_guid . '[$("#current-snapshot-datas-module-' . $this->station_guid . '").val()][2][$("#current-snapshot-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_size = $("#current-snapshot-datas-size-' . $this->station_guid . '").val();';
        $content .= 'var shortcode = "["+sc_sc+" device_id=\'"+sc_device+"\' module_id=\'"+sc_module+"\' measure_type=\'"+sc_measurement+"\' size=\'"+sc_size+"\' fx=\'"+sc_animation+"\' speed=\'"+sc_speed+"\']";';
        $content .= 'var shortcode_init = "[live-weather-station-snapshot device_id=\'"+sc_device+"\' module_id=\'"+sc_module+"\' measure_type=\'"+sc_measurement+"\' size=\'"+sc_size+"\' fx=\'"+sc_animation+"\' speed=\'"+sc_speed+"\']";';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_shortcode", sc:shortcode_init}).done(function(data) {$("#lws-graph-preview").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';
        $content .= '$("#current-snapshot-datas-shortcode-' . $this->station_guid . '").html(shortcode);});';
        $content .= '$("#current-snapshot-datas-module-' . $this->station_guid . '" ).change();';
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
        $special_footer  = '<span id="current-snapshot-info-' . $this->station_guid . '" style="display: none;">';
        $special_footer .= '<div id="major-publishing-actions">';
        $special_footer .= __('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );
        $special_footer .= '</div>';
        $special_footer .= '</span>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content, '', $special_footer);
    }
}

