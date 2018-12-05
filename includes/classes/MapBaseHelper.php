<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;
use WeatherStation\UI\SVG\Handling as SVG;

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
     * @param array $common The common parameters to add.
     * @since 3.7.0
     */
    public function save_map() {
        $params = $this->map_params;



        //$params['common'] = $common;
        //$params['stations'] = array(94, 96, 125);

        $s = __('Width', 'live-weather-station');
        $s = __('Heigth', 'live-weather-station');
        $s = __('Name', 'live-weather-station');
        $s = __('Zoom', 'live-weather-station');


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
            if (in_array($_POST['marker-type'], array('none', 'pin', 'old', 'logo', 'brand'))) {
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
        wp_enqueue_script('lws-leaflet');
        wp_enqueue_style('lws-leaflet');
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
     * @return string The output of the styles, ready to print.
     * @since 3.7.0
     */
    protected function output_styles() {

        // Popup



        // Marker
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
        $result .= '#' . $this->uniq .' .leaflet-popup-content-wrapper {';
        $result .= $shadow_popup_layer;
        $result .= 'border-radius: 3px;';
        $result .= '}';



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
        $result = '<script language="javascript" type="text/javascript">';
        $result .= 'jQuery(document).ready(function($) {';
        $result .= $this->specific_script();
        $result .= '});';
        $result .= '</script>';
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
        $stations = array();
        $st = array();
        $classname = 'lws-popup-' . $this->map_params['marker']['contrast'] . ' ' . 'lws-popup-' . $this->map_params['marker']['style'];
        switch ($this->map_params['marker']['contrast']) {
            case 'light':
                $color1 = '#FFFFFF';
                $color2 = '#ffd200';
                $color3 = '#2d7dd2';
                break;
            case 'dark':
                $color1 = '#273043';
                $color2 = '#ffd200';
                $color3 = '#ffd200';
                break;
            default:
                $color1 = '#2d7dd2';
                $color2 = '#ffd200';
                $color3 = '#ffffff';
        }
        if ($this->map_params['common']['all']) {
            $stations = $this->get_stations_list();
        }
        else {

        }
        foreach ($stations as $station) {
            $s = array();
            $s['id'] = $station['guid'];
            $s['lat'] = $station['loc_latitude'];
            $s['lon'] = $station['loc_longitude'];
            $s['iconUrl'] = SVG::get_base64_station_icon($station['station_type'], $color3);
            $image = "<img style='width:34px;float:left;padding-right:6px;' src='" . set_url_scheme(SVG::get_base64_station_color_logo($station['station_type'])) . "' />";
            $s['content'] = '<div><div>' . $image . $station['station_name'] . '</div>';

            if ($this->map_params['marker']['data'] == 'current') {
                $data = $this->get_widget_data($station['station_id'], 'outdoor');


            }




            //$s['content'] = '<p>Hello world!<br />This is a nice popup.</p>';//SVG::get_base64_station_color_logo($station['station_type']);

            $st[] = $s;
        }




        if (count($st) > 0) {
            $result = 'var stations = {';
            foreach ($st as $s) {
                $result .= $s['id'] . ':{"lat":' . $s['lat'] . ',"lon":' . $s['lon'] . ',"icn":"' . $s['iconUrl'] . '","cnt":"' . $s['content'] . '"},';
            }
            $result .= "};";
            if ($this->map_params['marker']['type'] == 'pin') {
                $result .= "  var stationIcon = L.icon({iconUrl: '" . SVG::get_base64_pin_icon($color1) ."', iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor:  [0, -18]});" . PHP_EOL;
            }
            if ($this->map_params['marker']['type'] == 'logo') {
                $result .= "  var stationIcon = L.icon({iconUrl: '" . SVG::get_base64_menu_icon($color1, $color2) ."', iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor:  [0, -18]});" . PHP_EOL;
            }
            $result .= "for (id in stations) {";
            if ($this->map_params['marker']['type'] == 'brand') {
                $result .= "";
                $result .= "  var stationIcon = L.icon({iconUrl: stations[id].icn, iconSize: [32, 32], iconAnchor: [16, 24], shadowSize: [56, 56], shadowAnchor: [28, 28], popupAnchor:  [0, -30], shadowUrl: '" . SVG::get_base64_marker_icon($color1) ."',});" . PHP_EOL;
            }
            $result .= " var marker = L.marker([stations[id].lat, stations[id].lon], {icon: stationIcon}).addTo(map).addTo(map).bindPopup(stations[id].cnt, {className:'" . $classname . "'});" . PHP_EOL;
            $result .= "}";
            $result .= "";
            $result .= "";
            $result .= "";
            $result .= "";


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
     * @param array $items The array of items.
     * @param boolean $label Optional. Display the th of the table.
     * @param mixed $selected Optional. Set the selected item.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.4.0
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
     * @since 3.4.0
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