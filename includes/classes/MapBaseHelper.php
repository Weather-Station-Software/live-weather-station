<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

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
        $result = '<style>';
        $result .= $this->specific_styles();
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
            $s['name'] = $station['station_name'];

            $st[] = $s;
        }




        if (count($st) > 0) {
            $result = 'var stations = {';
            foreach ($st as $s) {
                $result .= $s['id'] . ':{"lat":' . $s['lat'] . ',"lon":' . $s['lon'] . '},';
            }
            $result .= "};";
            $result .= "for (id in stations) {";
            $result .= " var marker = L.marker([stations[id].lat, stations[id].lon]).addTo(map).addTo(map).bindPopup('<p>Hello world!<br />This is a nice popup.</p>', {className:'test'});" . PHP_EOL;
            $result .= "}";
            $result .= "";
            $result .= "";
            $result .= "";
            $result .= "";


        }







        //$result .= "  var myIcon = L.icon({iconUrl: '" . SVG::get_base64_lws_icon() ."', iconSize: 40});" . PHP_EOL;
        //$result .= "  L.marker([47.8, -2.7], {icon: myIcon}).addTo(map).bindPopup('<p>Hello world!<br />This is a nice popup.</p>', {className:'test'});" . PHP_EOL;

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
        $content .= '</tbody></table>';
        return $content;
    }

}