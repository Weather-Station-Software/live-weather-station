<?php

namespace WeatherStation\Engine\Module\Current;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Class to generate parameter gauge form.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */
class Gauge extends \WeatherStation\Engine\Module\Maintainer {

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
        $this->module_type = 'justgage';
        $this->module_name = ucfirst(__('clean gauge', 'live-weather-station'));
        $this->module_hint = __('Display current data with a clean gauge.', 'live-weather-station');
        $this->module_icon = 'ch fa-lg fa-fw ch-circular-gauge';
        $this->layout = '1-23-4';
        $this->preview_min_height = true;
        parent::__construct($station_information);
    }

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.4.0
     */
    protected function enqueue_resources() {
        wp_enqueue_script('lws-justgage');
    }

    /**
     * Prepare the data.
     *
     * @since 3.4.0
     */
    protected function prepare() {
        $js_array_justgage = $this->get_all_stations_array(false, false, true, true, true, false, false, false, array($this->station_guid), false, false, true);
        if (array_key_exists($this->station_guid, $js_array_justgage)) {
            if (array_key_exists(2, $js_array_justgage[$this->station_guid])) {
                $this->data = $js_array_justgage[$this->station_guid][2];
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
        $content .= $this->get_assoc_option_select('current-justgage-datas-module-'. $this->station_guid, __('Module', 'live-weather-station'), $this->data, 0);
        $content .= $this->get_neutral_option_select('current-justgage-datas-measurement-'. $this->station_guid, __('Measurement', 'live-weather-station'));
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
        $content .= $this->get_key_value_option_select('current-justgage-datas-design-'. $this->station_guid, __('Design', 'live-weather-station'), $this->get_justgage_design_js_array());
        $content .= $this->get_key_value_option_select('current-justgage-datas-color-'. $this->station_guid, __('Colors', 'live-weather-station'), $this->get_justgage_color_js_array());
        $content .= $this->get_key_value_option_select('current-justgage-datas-pointer-'. $this->station_guid, __('Pointer', 'live-weather-station'), $this->get_justgage_pointer_js_array());
        $content .= $this->get_key_value_option_select('current-justgage-datas-title-'. $this->station_guid, __('Title', 'live-weather-station'), $this->get_justgage_title_js_array());
        $content .= $this->get_key_value_option_select('current-justgage-datas-subtitle-'. $this->station_guid, __('Label', 'live-weather-station'), $this->get_justgage_title_js_array());
        $content .= $this->get_key_value_option_select('current-justgage-datas-unit-'. $this->station_guid, __('Unit', 'live-weather-station'), $this->get_justgage_unit_js_array());
        $content .= $this->get_key_value_option_select('current-justgage-datas-size-'. $this->station_guid, __('Size', 'live-weather-station'), $this->get_size_js_array(), true, 'medium');
        $content .= $this->get_color_picker('current-justgage-datas-fc-pointer-'. $this->station_guid, __('Override pointer color', 'live-weather-station'));
        $content .= $this->get_color_picker('current-justgage-datas-fc-title-'. $this->station_guid, __('Override title color', 'live-weather-station'));
        $content .= $this->get_color_picker('current-justgage-datas-fc-label-'. $this->station_guid, __('Override label color', 'live-weather-station'));
        $content .= $this->get_color_picker('current-justgage-datas-fc-value-'. $this->station_guid, __('Override value color', 'live-weather-station'));
        $content .= '<style>.wp-picker-container .wp-color-result.button {width: 100% !important;}</style>';
        $content .= '<script>';
        $content .= '    ( function( $ ){';
        $content .= '        function initColorPicker( widget ) {';
        $content .= '            widget.find( ".wp-color-picker" ).wpColorPicker( {';
        $content .= '                change: _.throttle( function() {';
        $content .= '                    $(this).trigger( "change" );';
        $content .= '                }, 3000 )';
        $content .= '            });';
        $content .= '        }';
        $content .= '       function onFormUpdate( event, widget ) {';
        $content .= '            initColorPicker( widget );';
        $content .= '        }';
        $content .= '        $( document ).on( "widget-added widget-updated", onFormUpdate );';
        $content .= '        $( document ).ready( function() {';
        $content .= '            if ( $( "#widgets-right" ).length ) {';
        $content .= '                $("#widgets-right .widget:has(.wp-color-picker)").each(function () {';
        $content .= '                    initColorPicker($(this));';
        $content .= '                });';
        $content .= '            }';
        $content .= '            else {';
        $content .= '                $(".wp-color-picker").wpColorPicker();';
        $content .= '            }';
        $content .= '        } );';
        $content .= '    }( jQuery ) );';
        $content .= '</script>';
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
        $content .= '$("#current-justgage-datas-fc-pointer-' . $this->station_guid . '").parent().parent().parent().find("button").click(function() {';
        $content .= '$("#current-justgage-datas-module-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-fc-title-' . $this->station_guid . '").parent().parent().parent().find("button").click(function() {';
        $content .= '$("#current-justgage-datas-module-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-fc-label-' . $this->station_guid . '").parent().parent().parent().find("button").click(function() {';
        $content .= '$("#current-justgage-datas-module-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-fc-value-' . $this->station_guid . '").parent().parent().parent().find("button").click(function() {';
        $content .= '$("#current-justgage-datas-module-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-module-' . $this->station_guid . '").change(function() {';
        $content .= 'var js_array_current_justgage_measurement_' . $this->station_guid . ' = js_array_current_justgage_' . $this->station_guid . '[$(this).val()][2];';
        $content .= '$("#current-justgage-datas-measurement-' . $this->station_guid . '").html("");';
        $content .= '$(js_array_current_justgage_measurement_' . $this->station_guid . ').each(function (i) {';
        $content .= '$("#current-justgage-datas-measurement-' . $this->station_guid . '").append("<option value="+i+">"+js_array_current_justgage_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
        $content .= '$( "#current-justgage-datas-measurement-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-measurement-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-design-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-design-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-pointer-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-pointer-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-title-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-title-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-subtitle-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-subtitle-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-unit-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-unit-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-datas-size-' . $this->station_guid . '" ).change();});';
        $content .= '$("#current-justgage-datas-size-' . $this->station_guid . '").change(function() {';
        $content .= 'if ($("#current-justgage-datas-size-' . $this->station_guid . '").val()=="scalable") {';
        $content .= '$("#current-justgage-info-' . $this->station_guid . '").show();}';
        $content .= 'else {';
        $content .= '$("#current-justgage-info-' . $this->station_guid . '").hide();}';
        $content .= 'if ($("#current-justgage-datas-size-' . $this->station_guid . '").val()=="micro") {';
        $content .= '$("#current-justgage-datas-pointer-' . $this->station_guid . '").val("none");';
        $content .= '$("#current-justgage-datas-pointer-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#current-justgage-datas-title-' . $this->station_guid . '").val("none");';
        $content .= '$("#current-justgage-datas-title-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#current-justgage-datas-subtitle-' . $this->station_guid . '").val("none");';
        $content .= '$("#current-justgage-datas-subtitle-' . $this->station_guid . '").prop("disabled", true);';
        $content .= '$("#current-justgage-datas-unit-' . $this->station_guid . '").val("none");';
        $content .= '$("#current-justgage-datas-unit-' . $this->station_guid . '").prop("disabled", true);}';
        $content .= 'else {';
        $content .= '$("#current-justgage-datas-pointer-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#current-justgage-datas-title-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#current-justgage-datas-subtitle-' . $this->station_guid . '").prop("disabled", false);';
        $content .= '$("#current-justgage-datas-unit-' . $this->station_guid . '").prop("disabled", false);}';
        $content .= 'var sc_device = "' . $this->station_id . '";';
        $content .= 'var sc_module = js_array_current_justgage_' . $this->station_guid . '[$("#current-justgage-datas-module-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_measurement = js_array_current_justgage_' . $this->station_guid . '[$("#current-justgage-datas-module-' . $this->station_guid . '").val()][2][$("#current-justgage-datas-measurement-' . $this->station_guid . '").val()][1];';
        $content .= 'var sc_design = $("#current-justgage-datas-design-' . $this->station_guid . '").val();';
        $content .= 'var sc_color = $("#current-justgage-datas-color-' . $this->station_guid . '").val();';
        $content .= 'var sc_pointer = $("#current-justgage-datas-pointer-' . $this->station_guid . '").val();';
        $content .= 'var sc_title = $("#current-justgage-datas-title-' . $this->station_guid . '").val();';
        $content .= 'var sc_subtitle = $("#current-justgage-datas-subtitle-' . $this->station_guid . '").val();';
        $content .= 'var sc_unit = $("#current-justgage-datas-unit-' . $this->station_guid . '").val();';
        $content .= 'var sc_size = $("#current-justgage-datas-size-' . $this->station_guid . '").val();';
        $content .= 'var sc_force = "";';
        $content .= 'if ($("#current-justgage-datas-fc-pointer-' . $this->station_guid . '").val()!= "") {';
        $content .= '  sc_force = "ptr:" + $("#current-justgage-datas-fc-pointer-' . $this->station_guid . '").val();}';
        $content .= 'if ($("#current-justgage-datas-fc-title-' . $this->station_guid . '").val()!= "") {';
        $content .= '  if (sc_force != ""){sc_force=sc_force+"-";}';
        $content .= '  sc_force = sc_force + "ttl:" + $("#current-justgage-datas-fc-title-' . $this->station_guid . '").val();}';
        $content .= 'if ($("#current-justgage-datas-fc-label-' . $this->station_guid . '").val()!= "") {';
        $content .= '  if (sc_force != ""){sc_force=sc_force+"-";}';
        $content .= '  sc_force = sc_force + "lbl:" + $("#current-justgage-datas-fc-label-' . $this->station_guid . '").val();}';
        $content .= 'if ($("#current-justgage-datas-fc-value-' . $this->station_guid . '").val()!= "") {';
        $content .= '  if (sc_force != ""){sc_force=sc_force+"-";}';
        $content .= '  sc_force = sc_force + "val:" + $("#current-justgage-datas-fc-value-' . $this->station_guid . '").val();}';
        $content .= 'var shortcode = "[live-weather-station-justgage device_id=\'"+sc_device+"\' module_id=\'"+sc_module+"\' measure_type=\'"+sc_measurement+"\' design=\'"+sc_design+"\' color=\'"+sc_color+"\' force=\'"+sc_force+"\' pointer=\'"+sc_pointer+"\' title=\'"+sc_title+"\' subtitle=\'"+sc_subtitle+"\' unit=\'"+sc_unit+"\' size=\'"+sc_size+"\']";';
        $content .= '$("#current-justgage-datas-shortcode-' . $this->station_guid . '").html(shortcode);';
        $content .= '$("#current-justgage-bg-' . $this->station_guid . '").css("background-color", "transparent");';
        $content .= '$("#' . $this->fingerprint . '" ).empty();';
        $content .= 'if (sc_size=="micro") {$("#' . $this->fingerprint . '").width(75).height(75);}';
        $content .= 'if (sc_size=="small") {$("#' . $this->fingerprint . '").width(100).height(100);}';
        $content .= 'if (sc_size=="medium") {$("#' . $this->fingerprint . '").width(225).height(225);}';
        $content .= 'if (sc_size=="large" || sc_size=="scalable") {$("#' . $this->fingerprint . '" ).width(350).height(350);}';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= 'var http = new XMLHttpRequest();';
        $content .= 'var params = "action=lws_query_justgage_config";';
        $content .= 'params = params+"&id=' . $this->fingerprint . '";';
        $content .= 'params = params+"&device_id="+sc_device;';
        $content .= 'params = params+"&module_id="+sc_module;';
        $content .= 'params = params+"&measure_type="+sc_measurement;';
        $content .= 'params = params+"&design="+sc_design;';
        $content .= 'params = params+"&color="+sc_color;';
        $content .= 'params = params+"&force="+sc_force;';
        $content .= 'params = params+"&pointer="+sc_pointer;';
        $content .= 'params = params+"&title="+sc_title;';
        $content .= 'params = params+"&subtitle="+sc_subtitle;';
        $content .= 'params = params+"&unit="+sc_unit;';
        $content .= 'params = params+"&size="+sc_size;';
        $content .= 'http.open("POST", "' . LWS_AJAX_URL . '", true);';
        $content .= 'http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");';
        $content .= 'http.onreadystatechange = function () {';
        $content .= 'if (http.readyState == 4 && http.status == 200) {';
        $content .= 'var g' . $this->fingerprint . ' = new JustGage(JSON.parse(http.responseText));';
        $content .= '$(".lws-preview-id-spinner").removeClass("is-active");';
        $content .= '$(".lws-preview-id-spinner").removeClass("spinner");';
        $content .= '$("#current-justgage-bg-color-' . $this->station_guid . '").change();}};';
        $content .= 'http.send(params);});';

        $content .= '$("#current-justgage-bg-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#current-justgage-bg-' . $this->station_guid . '" ).css("background-color", $("#current-justgage-bg-color-' . $this->station_guid . '").val());});';
        $content .= '$("#current-justgage-bg-color-' . $this->station_guid . '" ).change();';
        
        $content .= '$("#current-justgage-datas-module-' . $this->station_guid . '" ).change();';

        return $this->get_script_box($content);
    }

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_preview() {
        $content = '<div><table cellspacing="0" style="display:inline-block;"><tbody>';
        $content .= $this->get_key_value_option_select('current-justgage-bg-color-'. $this->station_guid, '', $this->get_justgage_background_js_array(), false);
        $content .= '</tbody></table></div>';
        $content .= '<div>&nbsp;</div>';
        $content .= '<div id="current-justgage-bg-' . $this->station_guid . '" style="max-width:350px;border-radius: 5px;margin:0;width: 100%;float: inherit;display:inline-flex;justify-content: center;background-position-x:center;background-position-y:center;">';
        $content .= '<div id="' . $this->fingerprint . '"></div>';
        $content .= '</div>';
        $special_footer  = '<span id="current-justgage-info-' . $this->station_guid . '" style="display: none;">';
        $special_footer .= '<div id="major-publishing-actions">';
        $special_footer .= __('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );
        $special_footer .= '</div>';
        $special_footer .= '</span>';
        return $this->get_box('lws-preview-id', $this->preview_title, $content, '', $special_footer);
    }
}

