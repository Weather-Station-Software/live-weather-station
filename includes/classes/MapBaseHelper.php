<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;
use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\Utilities\ColorBrewer;

/**
 * This class is the base class for all map handler.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

abstract class BaseHandling {

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

    protected $type = 0;
    public $service = '';
    protected $set = false;
    protected $map_id;
    protected $map_name;
    protected $map_type;
    protected $map_information;
    protected $map_params;
    protected $uniq;
    protected $size = 'auto';
    protected $minzoom = 2;
    protected $maxzoom = 22;

    /**
     * Initialize the class and set its properties.
     *
     * @param array $map Optional. The parameters of the map.
     * @param string $size Optional. Forced height.
     * @since 3.7.0
     */
    public function __construct($map=null, $size='auto') {
        $this->set_map($map, $size);
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param array $map The parameters of the map.
     * @param string $size Forced height.
     * @since 3.7.0
     */
    public function set_map($map, $size) {
        if (isset($map)) {
            $this->map_information = $map;
            $this->map_id = $map['id'];
            $this->map_name = $map['name'];
            $this->map_params = unserialize($map['params']);
            $this->set = true;
        }
        $fingerprint = uniqid('', true);
        $uuid = substr ($fingerprint, strlen($fingerprint)-6, 80);
        $this->uniq = 'map' . $uuid;
        $this->size = $size;
    }

    /**
     * Initialize the map and set its specific properties.
     *
     * @return array The specific parameters.
     * @since 3.7.0
     */
    abstract protected function specific_params();

    /**
     * Initialize the map and set its properties.
     *
     * @param array $common The common parameters to add.
     * @return integer The new map ID.
     * @since 3.7.0
     */
    public function new_map($common) {
        $params = array();
        $params['common'] = $common;
        $params['stations'] = array();
        $params['marker'] = array();
        $params['marker']['type'] = 'pin';
        $params['marker']['data'] = 'current';
        $params['marker']['style'] = 'standard';
        $params['marker']['contrast'] = 'medium';
        $params['marker']['shadow'] = 'medium';
        $params['specific'] = $this->specific_params();
        return $this->add_new_map($this->type, sprintf(__('New %s map', 'live-weather-station'), $this->service), $params);
    }

    /**
     * Initialize the map and set its properties.
     *%
     * @since 3.7.0
     */
    public function save_map() {
        $params = $this->map_params;
        if (array_key_exists('common-name', $_POST)) {
            $this->map_name = sanitize_text_field($_POST['common-name']);
        }
        if (array_key_exists('common-width', $_POST)) {
            $params['common']['width'] = lws_sanitize_width_field($_POST['common-width']);
        }
        if (array_key_exists('common-height', $_POST)) {
            $params['common']['height'] = lws_sanitize_height_field($_POST['common-height']);
        }
        if (array_key_exists('common-loc_zoom', $_POST)) {
            $i = (int)sanitize_text_field($_POST['common-loc_zoom']);
            if ($i < $this->minzoom) {
                $i = $this->minzoom;
            }
            if ($i > $this->maxzoom) {
                $i = $this->maxzoom;
            }
            $params['common']['loc_zoom'] = $i;
        }
        if (array_key_exists('common-station-selector', $_POST)) {
            if (in_array($_POST['common-station-selector'], array('all', 'select'))) {
                $params['common']['all'] = $_POST['common-station-selector'] == 'all';
            }
        }
        if (array_key_exists('stations-selector', $_POST)) {
            try {
                $tab = array();
                foreach ($_POST['stations-selector'] as $sid) {
                    if (is_numeric($sid)) {
                        $tab[] = (int)round($sid);
                    }
                }
                $params['stations'] = $tab;
            }
            catch (\Exception $ex) {
                //$tab = array();
            }
        }
        if (array_key_exists('marker-type', $_POST)) {
            if (in_array($_POST['marker-type'], array('none', 'pin', 'old', 'logo', 'brand', 'weather:current', 'weather:temp', 'weather:colortemp', 'weather:wind'))) {
                $params['marker']['type'] = $_POST['marker-type'];
            }
        }
        if (array_key_exists('marker-data', $_POST)) {
            if (in_array($_POST['marker-data'], array('current', 'calendar', 'station'))) {
                $params['marker']['data'] = $_POST['marker-data'];
            }
        }
        if (array_key_exists('marker-style', $_POST)) {
            if (in_array($_POST['marker-style'], array('minimalist', 'standard', 'extended'))) {
                $params['marker']['style'] = $_POST['marker-style'];
            }
        }
        if (array_key_exists('marker-contrast', $_POST)) {
            if (in_array($_POST['marker-contrast'], array('light', 'medium', 'dark'))) {
                $params['marker']['contrast'] = $_POST['marker-contrast'];
            }
        }
        if (array_key_exists('marker-shadow', $_POST)) {
            if (in_array($_POST['marker-shadow'], array('none', 'medium', 'dark'))) {
                $params['marker']['shadow'] = $_POST['marker-shadow'];
            }
        }
        $params['specific'] = $this->get_specific_post_values();
        $this->update_map($this->map_id, $this->type, $this->map_name, $params);
    }

    /**
     * Output the specific resources.
     *
     * @return string The output of the specific resources, ready to print.
     * @since 3.7.0
     */
    abstract protected function specific_resources();

    /**
     * Output the resources.
     *
     * @return string The output of the resources, ready to print.
     * @since 3.7.0
     */
    protected function output_resources() {
        $result = '';
        wp_enqueue_script('jquery');
        wp_enqueue_script('lws-leaflet');
        wp_enqueue_style('lws-leaflet');
        if ($this->map_params['marker']['type'] != 'none') {
            wp_enqueue_style('lws-weather-icons');
            wp_enqueue_style('lws-weather-icons-wind');
            lws_font_awesome();
        }
        $result .= $this->specific_resources();
        return $result;
    }

    /**
     * Output the specific styles.
     *
     * @return string The output of the specific styles, ready to print.
     * @since 3.7.0
     */
    abstract protected function specific_styles();

    /**
     * Output the styles.
     *
     * @param integer $id Id of the color.
     * @return string The color of the id.
     * @since 3.7.0
     */
    protected function color($id) {
        switch ($this->map_params['marker']['contrast']) {
            case 'light':
                if ($id === 1) {return '#FFFFFF';}
                if ($id === 2) {return '#ffd200';}
                if ($id === 3) {return '#2d7dd2';}
                if ($id === 4) {return '#FFFFFF';}
                break;
            case 'dark':
                if ($id === 1) {return '#273043';}
                if ($id === 2) {return '#ffd200';}
                if ($id === 3) {return '#ffd200';}
                if ($id === 4) {return '#273043';}
                break;
            default:
                if ($id === 1) {return '#2d7dd2';}
                if ($id === 2) {return '#ffd200';}
                if ($id === 3) {return '#ffffff';}
                if ($id === 4) {return '#FFFFFF';}
        }
    }

    /**
     * Output the styles.
     *
     * @return string The output of the styles, ready to print.
     * @since 3.7.0
     */
    protected function output_styles() {
        $shadow_shadow_layer = '';
        $shadow_marker_layer = '';
        if ($this->map_params['marker']['shadow'] == 'medium') {
            $shadow_shadow_layer = 'filter: drop-shadow(3px 3px 2px rgba(0,0,0,.5));';
        }
        if ($this->map_params['marker']['shadow'] == 'dark') {
            $shadow_shadow_layer = 'filter: drop-shadow(3px 3px 2px rgba(0,0,0,.75));';
        }
        if ($this->map_params['marker']['type'] != 'brand') {
            $shadow_marker_layer = $shadow_shadow_layer;
        }
        $shadow_popup_layer = $shadow_shadow_layer;
        $result = '<style>';
        $result .= $this->specific_styles();
        $result .= '#' . $this->uniq .' .leaflet-shadow-pane {';
        $result .= $shadow_shadow_layer;
        $result .= '}';
        $result .= '#' . $this->uniq .' .leaflet-marker-pane {';
        $result .= $shadow_marker_layer;
        $result .= '}';
        $result .= '#' . $this->uniq .' .leaflet-popup {';
        $result .= $shadow_popup_layer;
        $result .= '}';
        $result .= '#' . $this->uniq .' .leaflet-popup-content-wrapper {';
        $result .= 'opacity: 1;';
        $result .= 'border-radius: 3px;';
        $result .= 'background: ' . $this->color(1) . ';';
        $result .= 'color: ' . $this->color(3) . ';';
        $result .= '}';
        $result .= '#' . $this->uniq .' .leaflet-popup-tip {';
        $result .= 'opacity: 1;';
        $result .= 'background: ' . $this->color(1) . ';';
        $result .= '}';
        $result .= '#' . $this->uniq .' .leaflet-div-icon {';
        $result .= 'background: ' . $this->color(1) . ' !important;';
        $result .= 'border: none !important;';
        $result .= 'border-radius: 50%;';
        $result .= 'text-align: center;';
        $result .= '}';
        $result .= '#' . $this->uniq .' .leaflet-control-container a {';
        $result .= 'text-decoration: none;';
        $result .= '}';
        if (strpos($this->map_params['marker']['type'], 'weather:') === 0) {
            $result .= '#' . $this->uniq .' .leaflet-popup-pane {';
            $result .= 'top: -24px;';
            $result .= '}';
        }
        if ($this->map_params['marker']['style'] === 'minimalist') {
            $result .= '#' . $this->uniq .' .leaflet-popup-content {';
            $result .= 'margin: 6px;';
            $result .= 'text-align: center;';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .title {';
            $result .= 'font-size:10px;';
            $result .= 'padding-bottom:4px;';
            $result .= 'border-bottom: 1px solid ' . $this->color(3) . ';';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .values {';
            $result .= 'font-size:10px;';
            $result .= 'padding-top:4px;';
            $result .= '}';
        }
        if ($this->map_params['marker']['style'] === 'standard' || $this->map_params['marker']['style'] === 'extended') {
            $result .= '#' . $this->uniq .' .leaflet-popup-content {';
            $result .= 'margin: 10px;';
            $result .= 'text-align: center;';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .title {';
            $result .= 'font-size:14px;';
            $result .= 'padding-bottom:14px;';
            $result .= 'padding-top:4px;';
            $result .= 'border-bottom: 1px solid ' . $this->color(3) . ';';
            $result .= 'font-weight:bold;';
            $result .= 'margin-bottom: 6px;';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .subsubtitle {';
            $result .= 'font-size:12px;';
            $result .= 'padding-bottom:12px;';
            $result .= 'padding-top:4px;';
            $result .= 'border-bottom: 1px solid ' . $this->color(3) . ';';
            $result .= 'margin-bottom: 6px;';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .logo {';
            $result .= 'float:left;';
            $result .= 'margin-top:-6px;';
            $result .= 'width:30px;';
            $result .= 'height:30px;';
            $result .= 'background: #FFF !important;';
            $result .= 'border: none !important;';
            $result .= 'border-radius: 50%;';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .text {';
            $result .= '}';
            $result .= '#' . $this->uniq .' .leaflet-popup-content .values {';
            $result .= 'font-size:14px;';
            $result .= 'padding-top:8px;';
            $result .= 'line-height: 1.8em;';
            $result .= '}';
        }
        $result .= '</style>';
        return $result;
    }

    /**
     * Output the specific container.
     *
     * @return string The output of the specific container, ready to print.
     * @since 3.7.0
     */
    abstract protected function specific_container();

    /**
     * Output the container.
     *
     * @return string The output of the container, ready to print.
     * @since 3.7.0
     */
    protected function output_container() {
        $heigth = ($this->size === 'auto' ? $this->map_params['common']['height'] : $this->size);
        $width = ($this->size === 'auto' ? $this->map_params['common']['width'] : '100%');
        $result = '<div id="' . $this->uniq . '" class="lws-map" style="width:' . $width . ';height:' . $heigth . ';">' . PHP_EOL;
        $result .= $this->specific_container() . PHP_EOL;
        $result .= '</div>';
        return $result;
    }

    /**
     * Output the specific script.
     *
     * @return string The output of the specific script, ready to print.
     * @since 3.7.0
     */
    abstract protected function specific_script();

    /**
     * Output the script.
     *
     * @return string The output of the script, ready to print.
     * @since 3.7.0
     */
    protected function output_script() {
        $result = lws_print_begin_script();
        $result .= 'jQuery(document).ready(function($) {';
        $result .= $this->specific_script();
        $result .= '});';
        $result .= lws_print_end_script();
        return $result;
    }

    /**
     * Output markers.
     *
     * @return string The output of the script, ready to print.
     * @since 3.7.0
     */
    protected function output_markers() {
        $result = '';
        $sep = ' • ';
        $st = array();
        $classname = 'lws-popup-' . $this->map_params['marker']['contrast'] . ' ' . 'lws-popup-' . $this->map_params['marker']['style'];

        if ($this->map_params['common']['all']) {
            $stations = $this->get_ordered_stations_list();
        }
        else {
            $stations = $this->get_ordered_stations_list($this->map_params['stations']);
        }
        foreach ($stations as $station) {
            $s = array();
            $s['id'] = $station['guid'];
            $s['lat'] = $station['loc_latitude'];
            $s['lon'] = $station['loc_longitude'];
            $s['iconUrl'] = SVG::get_base64_station_icon($station['station_type'], $this->color(3));
            $image = "<img style='width:28px;padding-top: 0px' src='" . set_url_scheme(SVG::get_base64_station_color_logo($station['station_type'])) . "' />";
            if ($this->map_params['marker']['data'] == 'current' || $this->map_params['marker']['data'] == 'station' || $this->map_params['marker']['type'] == 'weather:current' || $this->map_params['marker']['type'] == 'weather:wind') {
                $modules = $this->get_widget_data($station['station_id'], 'outdoor');
                $day = null;
                $weather = null;
                $wind_angle = null;
                $wind_force = null;
                $wind_strength = null;
                $temperature = null;
                $temperature_ref = null;
                $humidity = null;
                $pressure = null;
                $params = null;
                $alt = $this->output_value($station['loc_altitude'], 'loc_altitude', true);
                $lat = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 6);
                $lon = $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 6);
                $timezone = $this->output_value($station['loc_timezone'], 'loc_timezone', true);
                if (array_key_exists('modules', $modules)) {
                    foreach ($modules['modules'] as $module) {
                        switch ($module['type']) {
                            case 'NACurrent':
                                if (array_key_exists('is_day', $module['datas'])) {
                                    $day = ($module['datas']['is_day']['raw_value'] == 1 ? 'day' : 'night');
                                }
                                if (array_key_exists('weather', $module['datas'])) {
                                    $weather = $module['datas']['weather']['raw_value'];
                                }
                                if (array_key_exists('windangle', $module['datas']) && array_key_exists('windstrength', $module['datas']) && !isset($wind_force)) {
                                    $wind_angle = $this->get_angle_text($module['datas']['windangle']['raw_value']);
                                    $wind_strength = $this->output_value($module['datas']['windstrength']['raw_value'], 'windstrength', true);
                                    $wind_force = $this->get_wind_speed($module['datas']['windstrength']['raw_value'], 3);
                                }
                                if (array_key_exists('humidity', $module['datas']) && !isset($humidity)) {
                                    $humidity = $this->output_value($module['datas']['humidity']['raw_value'], 'humidity', true);
                                }
                                if (array_key_exists('temperature', $module['datas']) && !isset($temperature)) {
                                    $temperature = $this->output_value($module['datas']['temperature']['raw_value'], 'temperature', true);
                                    $temperature_ref = $module['datas']['temperature']['raw_value'];
                                }
                                if (array_key_exists('pressure_sl', $module['datas']) && !isset($pressure)){
                                    $pressure = $this->output_value($module['datas']['pressure_sl']['raw_value'], 'pressure_sl', true);
                                }
                                if (array_key_exists('rain', $module['datas']) && !isset($rain)) {
                                    $rain = $this->output_value($module['datas']['rain']['raw_value'], 'rain', true, false, 'NACurrent');
                                }
                                break;
                            case 'NAModule3': // Rain gauge
                                if (array_key_exists('rain', $module['datas'])) {
                                    $rain = $this->output_value($module['datas']['rain']['raw_value'], 'rain', true, false, 'NAModule3');
                                }
                                break;
                            case 'NAModule2': // Wind gauge
                                if (array_key_exists('windangle', $module['datas']) && array_key_exists('windstrength', $module['datas'])) {
                                    $wind_angle = $this->get_angle_text($module['datas']['windangle']['raw_value']);
                                    $wind_strength = $this->output_value($module['datas']['windstrength']['raw_value'], 'windstrength', true);
                                    $wind_force = $this->get_wind_speed($module['datas']['windstrength']['raw_value'], 3);
                                }
                                break;
                            case 'NAModule1': // Outdoor module
                                if (array_key_exists('humidity', $module['datas'])) {
                                    $humidity = $this->output_value($module['datas']['humidity']['raw_value'], 'humidity', true);
                                }
                                if (array_key_exists('temperature', $module['datas'])) {
                                    $temperature = $this->output_value($module['datas']['temperature']['raw_value'], 'temperature', true);
                                    $temperature_ref = $module['datas']['temperature']['raw_value'];
                                }
                                break;
                            case 'NAMain':
                                if (array_key_exists('pressure_sl', $module['datas'])){
                                    $pressure = $this->output_value($module['datas']['pressure_sl']['raw_value'], 'pressure_sl', true);
                                }
                                break;
                        }
                    }
                }
            }
            if ($this->map_params['marker']['data'] == 'calendar') {
                $modules = $this->get_widget_data($station['station_id'], 'ephemeris');
                $moonrise = null;
                $moonset = null;
                $sunrise = null;
                $sunset = null;
                if (array_key_exists('modules', $modules)) {
                    foreach ($modules['modules'] as $module) {
                        switch ($module['type']) {
                            case 'NAEphemer':
                                if (array_key_exists('sunrise', $module['datas']) && array_key_exists('sunset', $module['datas'])) {
                                    $sunrise = $this->output_value($module['datas']['sunrise']['raw_value'], 'sunrise', true, false, '', $station['loc_timezone']);
                                    $sunset = $this->output_value($module['datas']['sunset']['raw_value'], 'sunset', true, false, '', $station['loc_timezone']);
                                }
                                if (array_key_exists('moonrise', $module['datas']) && array_key_exists('moonset', $module['datas'])) {
                                    $moonrise = $this->output_value($module['datas']['moonrise']['raw_value'], 'moonrise', true, false, '', $station['loc_timezone']);
                                    $moonset = $this->output_value($module['datas']['moonset']['raw_value'], 'moonset', true, false, '', $station['loc_timezone']);
                                }
                                break;
                        }
                    }
                }
            }
            if (isset($day) && isset($weather)) {
                $s['weatherDiv'] = '<i class=\'wi wi-owm-' . $day .'-' . $weather . '\' style=\'color:' . $this->color(3) . ';font-size:2em;margin-top: 10px;\'></i>';
            }
            else {
                $s['weatherDiv'] = '';
            }
            if (isset($wind_force)) {
                $s['windDiv'] = '<i class=\'wi wi-wind-beaufort-' . $wind_force . '\' style=\'color:' . $this->color(3) . ';font-size:2em;margin-top: 10px;\'></i>';
            }
            else {
                $s['windDiv'] = '';
            }
            if (isset($temperature)) {
                $ref = get_option('live_weather_station_unit_temperature') ;
                $t = (integer)round($this->get_temperature($temperature_ref, $ref), 0);
                $temp = $t . str_replace('&nbsp;', '', $this->unit_espace.$this->get_temperature_unit($ref));
                $colors = ColorBrewer::getGradient('RdYlBu', 8, 56, true);
                if ($t < -15) {
                    $t = -15;
                }
                if ($t > 40) {
                    $t = 40;
                }
                $color = $colors[$t+15];
                $s['tempDiv'] = '<span style=\'color:' . $this->color(3) . ';font-size:12px;font-weight:900;position:relative;top:14px;\'>' . $temp . '</span>';
                $s['tempColDiv'] = '<span style=\'display:block;width:100%;height:100%;border-radius: 50%;background-color:' . $color . ';\'><span style=\'color:' . $this->color(4) . ';font-size:12px;font-weight:900;position:relative;top:14px;\'>' . $temp . '</span></span>';
            }
            else {
                $s['tempDiv'] = '';
                $s['tempColDiv'] = '';
            }
            switch ($this->map_params['marker']['style']) {
                case 'minimalist':
                    $content = '<div class="title">' . $station['station_name'] . '</div>';
                    $minwidth = '';
                    switch ($this->map_params['marker']['data']) {
                        case 'current':
                            $content .= '<div class="values">&nbsp;' . $temperature . $sep . $humidity . '&nbsp;</div>';
                            $content .= '<div class="values">&nbsp;' . $pressure . '&nbsp;</div>';
                            break;
                        case 'calendar':
                            $content .= '<div class="values">' . $sunrise . '&nbsp;<i class="wi fa-fw wi-day-sunny" style="font-size: 10px;"></i>&nbsp;' . $sunset . '</div>';
                            $content .= '<div class="values">' . $moonrise . '&nbsp;<i class="wi fa-fw wi-night-clear" style="font-size: 10px;"></i>&nbsp;' . $moonset . '</div>';
                            break;
                        case 'station':
                            $content .= '<div class="values">&nbsp;' . $lat . '&nbsp;</div>';
                            $content .= '<div class="values">&nbsp;' . $lon . '&nbsp;</div>';
                            $content .= '<div class="values">&nbsp;' . $alt . '&nbsp;</div>';
                            break;
                    }
                    break;
                case 'extended':
                    $minwidth = 'minWidth: 200, ';
                    $content = '<div class="title"><div class="logo">' . $image . '</div><div class="text">' . str_replace(' ', '&nbsp;', $station['station_name']) . '</div></div>';
                    switch ($this->map_params['marker']['data']) {
                        case 'current':
                            $content .= '<div class="values"><i class="wi fa-fw wi-thermometer" style="font-size: 16px;"></i>&nbsp;' . $temperature . '&nbsp; &nbsp;<i class="wi fa-fw wi-humidity" style="font-size: 16px;"></i>&nbsp;' . $humidity . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-barometer" style="font-size: 16px;"></i>&nbsp;' . $pressure . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-strong-wind" style="font-size: 16px;"></i>&nbsp;' . $wind_angle . $sep . $wind_strength . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-umbrella" style="font-size: 16px;"></i>&nbsp;' . $rain . '&nbsp;</div>';
                            break;
                        case 'calendar':
                            $content .= '<div class="values"><i class="wi fa-fw wi-sunrise" style="font-size: 16px;"></i>&nbsp;' . $sunrise . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-sunset" style="font-size: 16px;"></i>&nbsp;' . $sunset . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-moonrise" style="font-size: 16px;"></i>&nbsp;' . $moonrise . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-moonset" style="font-size: 16px;"></i>&nbsp;' . $moonset . '&nbsp;</div>';
                            break;
                        case 'station':
                            $content .= '<div class="subsubtitle">' . $station['station_model'] . '</div>';
                            $content .= '<div class="values">' . $lat . '&nbsp;<i style="font-size: 14px;" class="' . LWS_FAS . ' fa-fw ' . (LWS_FA5?'fa-map-marker-alt':'fa-map-marker') . '"></i>&nbsp;' . $lon . '</div>';
                            $content .= '<div class="values"><i style="font-size: 14px;" class="' . LWS_FAS . ' fa-fw fa-rotate-315 fa-location-arrow"></i>&nbsp;' . $alt . '</div>';
                            $content .= '<div class="values"><i style="font-size: 14px;" class="' . LWS_FAR . ' fa-fw ' . (LWS_FA5?'fa-clock ':'fa-clock-o') . '"></i>&nbsp;' . $timezone . '</div>';
                            break;
                    }

                    break;
                default:
                    $minwidth = 'minWidth: 180, ';
                    $content = '<div class="title"><div class="logo">' . $image . '</div><div class="text">' . str_replace(' ', '&nbsp;', $station['station_name']) . '</div></div>';
                    switch ($this->map_params['marker']['data']) {
                        case 'current':
                            $content .= '<div class="values"><i class="wi fa-fw wi-thermometer" style="font-size: 16px;"></i>&nbsp;' . $temperature . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-humidity" style="font-size: 16px;"></i>&nbsp;' . $humidity . '&nbsp;</div>';
                            $content .= '<div class="values"><i class="wi fa-fw wi-barometer" style="font-size: 16px;"></i>&nbsp;' . $pressure . '&nbsp;</div>';
                            break;
                        case 'calendar':
                            $content .= '<div class="values">' . $sunrise . '&nbsp;<i class="wi fa-fw wi-day-sunny" style="font-size: 16px;"></i>&nbsp;' . $sunset . '</div>';
                            $content .= '<div class="values">' . $moonrise . '&nbsp;<i class="wi fa-fw wi-night-clear" style="font-size: 16px;"></i>&nbsp;' . $moonset . '</div>';
                            break;
                        case 'station':
                            $content .= '<div class="values">' . $lat . '&nbsp;<i style="font-size: 14px;" class="' . LWS_FAS . ' fa-fw ' . (LWS_FA5?'fa-map-marker-alt':'fa-map-marker') . '"></i>&nbsp;' . $lon . '</div>';
                            $content .= '<div class="values"><i style="font-size: 14px;" class="' . LWS_FAS . ' fa-fw fa-rotate-315 fa-location-arrow"></i>&nbsp;' . $alt . '</div>';
                            $content .= '<div class="values"><i style="font-size: 14px;" class="' . LWS_FAR . ' fa-fw ' . (LWS_FA5?'fa-clock ':'fa-clock-o') . '"></i>&nbsp;' . $timezone . '</div>';
                            break;
                    }
                    break;
            }
            if (array_key_exists('marker-style', $_POST)) {
                if (in_array($_POST['marker-style'], array('minimalist', 'standard', 'extended'))) {
                    $params['marker']['style'] = $_POST['marker-style'];
                }
            }
            $s['content'] = str_replace('"', '\'', $content);
            $st[] = $s;
        }
        if (count($st) > 0) {
            $result = 'var stations = {';
            foreach ($st as $s) {
                $result .= $s['id'] . ':{"lat":' . $s['lat'] . ', "lon":' . $s['lon'] . ', "icn":"' . $s['iconUrl'] . '", "tmp":"' . $s['tempDiv'] . '", "tmpcol":"' . $s['tempColDiv'] . '", "wtr":"' . $s['weatherDiv'] . '","wnd":"' . $s['windDiv'] . '","cnt":"' . $s['content'] . '"},';
            }
            $result .= "};";
            if ($this->map_params['marker']['type'] == 'pin') {
                $result .= "  var stationIcon = L.icon({iconUrl: '" . SVG::get_base64_pin_icon($this->color(1)) ."', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor:  [0, -34]});" . PHP_EOL;
            }
            if ($this->map_params['marker']['type'] == 'logo') {
                $result .= "  var stationIcon = L.icon({iconUrl: '" . SVG::get_base64_menu_icon($this->color(1), $this->color(2)) ."', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor:  [0, -34]});" . PHP_EOL;
            }
            $result .= "for (id in stations) {";
            if ($this->map_params['marker']['type'] == 'brand') {
                $result .= "  var stationIcon = L.icon({iconUrl: stations[id].icn, iconSize: [32, 32], iconAnchor: [16, 52], shadowSize: [56, 56], shadowAnchor: [28, 56], popupAnchor:  [0, -58], shadowUrl: '" . SVG::get_base64_marker_icon($this->color(1)) ."',});" . PHP_EOL;
            }
            if ($this->map_params['marker']['type'] == 'weather:current') {
                $result .= "  var stationIcon = L.divIcon({html: stations[id].wtr, iconSize: [44, 44]});" . PHP_EOL;
            }
            if ($this->map_params['marker']['type'] == 'weather:temp') {
                $result .= "  var stationIcon = L.divIcon({html: stations[id].tmp, iconSize: [44, 44]});" . PHP_EOL;
            }
            if ($this->map_params['marker']['type'] == 'weather:colortemp') {
                $result .= "  var stationIcon = L.divIcon({html: stations[id].tmpcol, iconSize: [44, 44]});" . PHP_EOL;
            }
            if ($this->map_params['marker']['type'] == 'weather:wind') {
                $result .= "  var stationIcon = L.divIcon({html: stations[id].wnd, iconSize: [44, 44]});" . PHP_EOL;
            }
            $result .= " var marker = L.marker([stations[id].lat, stations[id].lon], {icon: stationIcon}).addTo(map).addTo(map).bindPopup(stations[id].cnt, {" . $minwidth . "keepInView: true, closeButton: false, autoClose: true, className:'" . $classname . "'});" . PHP_EOL;
            $result .= "}";
        }




        $result .= '';
        return $result;
    }

    /**
     * Verify if quota allows to display the map.
     *
     * @return boolean True if quota allows to output the map, false otherwise.
     * @since 3.7.0
     */
    abstract protected function quota_verify();

    /**
     * Output the map.
     *
     * @return string The output of the map, ready to print.
     * @since 3.7.0
     */
    public function output() {
        if ($this->quota_verify()) {
            return $this->output_resources() . $this->output_styles() . $this->output_container() . $this->output_script();
        }
        else {
            return __('This map can not be displayed due to exceeded quota.', 'live-weather-station');
        }
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param string $options Optional. The options of the control.
     * @param boolean $label Optional. Display the th of the table.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @param boolean $multiple Optional. Display multi select.
     * @return string The control ready to print.
     * @since 3.7.0
     */
    protected function get_option_select($id, $title, $options='', $label=true, $hidden=false, $displayed=true, $multiple=false) {
        $visibility = '';
        if ($multiple) {
            $id = $id . '[]';
        }
        if ($id == '') {
            $visibility = ' class="lws-placeholder" style="visibility:hidden;"';
            $id = 'o' . md5(random_bytes(20));
            $title = '';
        }
        $style = array();
        if ($hidden) {
            $style[] = 'visibility:hidden';
        }
        if (!$displayed) {
            $style[] = 'display:none';
        }
        if (count($style) > 0) {
            $visibility .= ' style="' . implode(';', $style) . '"';
        }
        $result = '';
        $result .= '<tr' . $visibility .'>';
        if ($label) {
            $result .= '<th class="lws-option" width="35%" align="left" scope="row">' . $title . '</th>';
            $result .= '<td width="2%"></td>';
        }
        $result .= '<td align="left" class="lws-option-setting">';
        $result .= '<span class="select-option">';
        if ($multiple) {
            $result .= '<select multiple class="option-select" id="' . $id .'" name="' . $id .'">';
        }
        else {
            $result .= '<select class="option-select" id="' . $id .'" name="' . $id .'">';
        }
        if ($options != '') {
            $result .= $options;
        }
        $result .= '</select>';
        $result .= '</span>';
        $result .= '</td>';
        $result .= '</tr>';
        return $result;
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param string $value The current value.
     * @param boolean $label Optional. Display the th of the table.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.7.0
     */
    protected function get_input_text($id, $title, $value, $label=true, $hidden=false, $displayed=true) {
        $visibility = '';
        if ($id == '') {
            $visibility = ' class="lws-placeholder" style="visibility:hidden;"';
            $id = 'o' . md5(random_bytes(20));
            $title = '';
        }
        $style = array();
        if ($hidden) {
            $style[] = 'visibility:hidden';
        }
        if (!$displayed) {
            $style[] = 'display:none';
        }
        if (count($style) > 0) {
            $visibility .= ' style="' . implode(';', $style) . '"';
        }
        $result = '';
        $result .= '<tr' . $visibility .'>';
        if ($label) {
            $result .= '<th class="lws-option" width="35%" align="left" scope="row">' . $title . '</th>';
            $result .= '<td width="2%"></td>';
        }
        $result .= '<td align="left" class="lws-option-setting">';
        $result .= '<span class="select-option">';
        $result .= '<input id="' . $id . '" name="' . $id . '" type="text" size="60" value="' . $value . '" class="regular-text" />';
        $result .= '</span>';
        $result .= '</td>';
        $result .= '</tr>';
        return $result;
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param array $items The array of items.
     * @param boolean $label Optional. Display the th of the table.
     * @param mixed $selected Optional. Set the selected item.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.7.0
     */
    protected function get_key_value_option_select($id, $title, $items, $label=true, $selected=null, $hidden=false, $displayed=true) {
        $result = '';
        $cpt = 0;
        foreach ($items as $item) {
            if (strlen($item[1]) > $cpt && strpos($item[1], '//->') === false) {
                $cpt = strlen($item[1]);
            }
        }
        $b = '';
        for ($i=1; $i<$cpt/2; $i++) {
            $b .= '█';
        }
        foreach ($items as $item) {
            $sel = '';
            if (!is_null($selected)){
                if ($selected === $item[0]) {
                    $sel = ' SELECTED';
                }
            }
            if (strpos($item[1], '//->') === 0) {
                $sel = ' DISABLED';
                $item[1] = str_replace('//->', '', $item[1]);
                $item[1] = $b . ' ' . $item[1];
            }
            $result .= '<option value="' . $item[0] . '"' . $sel . '>' . $item[1] . '</option>';
        }
        return $this->get_option_select($id, $title, $result, $label, $hidden, $displayed);
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param array $items The array of items.
     * @param boolean $label Optional. Display the th of the table.
     * @param array $selected Optional. Set the selected item.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.7.0
     */
    protected function get_key_value_option_multiselect($id, $title, $items, $label=true, $selected=array(), $hidden=false, $displayed=true) {
        $result = '';
        $cpt = 0;
        foreach ($items as $item) {
            if (strlen($item[1]) > $cpt && strpos($item[1], '//->') === false) {
                $cpt = strlen($item[1]);
            }
        }
        $b = '';
        for ($i=1; $i<$cpt/2; $i++) {
            $b .= '█';
        }
        foreach ($items as $item) {
            $sel = '';
            if (!is_null($selected)){
                if (in_array($item[0], $selected)) {
                    $sel = ' SELECTED';
                }
            }
            if (strpos($item[1], '//->') === 0) {
                $sel = ' DISABLED';
                $item[1] = str_replace('//->', '', $item[1]);
                $item[1] = $b . ' ' . $item[1];
            }
            $result .= '<option value="' . $item[0] . '"' . $sel . '>' . $item[1] . '</option>';
        }
        return $this->get_option_select($id, $title, $result, $label, $hidden, $displayed, true);
    }

    /**
     * Verify if the map has feature box.
     *
     * @return boolean True if the map has feature box, false otherwise.
     * @since 3.7.0
     */
    abstract public function has_feature();

    /**
     * Output the feature box.
     *
     * @return string The control ready to print.
     * @since 3.7.0
     */
    abstract public function output_feature();

    /**
     * Verify if the map has control box.
     *
     * @return boolean True if the map has control box, false otherwise.
     * @since 3.7.0
     */
    abstract public function has_control();

    /**
     * Output the control box.
     *
     * @return string The control ready to print.
     * @since 3.7.0
     */
    abstract public function output_control();

    /**
     * Get the post values.
     *
     * @return array The specific parameters.
     * @since 3.7.0
     */
    abstract public function get_specific_post_values();

    /**
     * Output the map detail box.
     *
     * @return string The control ready to print.
     * @since 3.7.0
     */
    public function output_detail() {
        $content = '<table cellspacing="0" style="display:table;" class="lws-settings"><tbody>';
        $content .= $this->get_input_text('common-name', __('Name', 'live-weather-station'), $this->map_name, true);
        $content .= $this->get_input_text('common-width', __('Width', 'live-weather-station'), $this->map_params['common']['width'], true);
        $content .= $this->get_input_text('common-height', __('Height', 'live-weather-station'), $this->map_params['common']['height'], true);
        $content .= $this->get_key_value_option_select('common-loc_zoom', __('Zoom', 'live-weather-station'), $this->get_zoom_js_array($this->minzoom, $this->maxzoom), true, $this->map_params['common']['loc_zoom']);
        $content .= '</tbody></table>';
        return $content;
    }

    /**
     * Output the station box.
     *
     * @return string The control ready to print.
     * @since 3.7.0
     */
    public function output_stations() {
        $content = '<table cellspacing="0" style="display:table;" class="lws-settings"><tbody>';
        $content .= $this->get_key_value_option_select('common-station-selector', __('Sources', 'live-weather-station'), $this->get_station_selector_js_array(), true, $this->map_params['common']['all'] ? 'all' : 'select');
        $content .= $this->get_key_value_option_multiselect('stations-selector', __('Selection', 'live-weather-station'), $this->get_stations_selector_js_array(), true, $this->map_params['stations']);
        $content .= $this->get_key_value_option_select('marker-type', __('Marker', 'live-weather-station'), $this->get_map_marker_js_array(), true, $this->map_params['marker']['type']);
        $content .= $this->get_key_value_option_select('marker-data', __('Data', 'live-weather-station'), $this->get_map_data_js_array(), true, $this->map_params['marker']['data']);
        $content .= $this->get_key_value_option_select('marker-style', __('Style', 'live-weather-station'), $this->get_map_style_js_array(), true, $this->map_params['marker']['style']);
        $content .= $this->get_key_value_option_select('marker-contrast', __('Contrast', 'live-weather-station'), $this->get_map_contrast_js_array(), true, $this->map_params['marker']['contrast']);
        $content .= $this->get_key_value_option_select('marker-shadow', __('Shadows', 'live-weather-station'), $this->get_map_shadow_js_array(), true, $this->map_params['marker']['shadow']);
        $content .= '</tbody></table>';
        return $content;
    }

}