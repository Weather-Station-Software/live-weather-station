<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Arrays\Generator;

/**
 * This class builds elements of the map view for Maptiler maps.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */

class MaptilerHandling extends BaseHandling {

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

    protected $type = 6;
    public $service = 'Maptiler';
    protected $maxzoom = 23;

    /**
     * Initialize the map and set its specific properties.
     *
     * @return array The specific parameters.
     * @since 3.8.0
     */
    protected function specific_params() {
        $result = array();
        $result['controls']['zoom'] = true;
        $result['options']['overlay'] = 'styles:basic';
        return $result;
    }

    /**
     * Get the post values.
     *
     * @return array The specific parameters.
     * @since 3.8.0
     */
    public function get_specific_post_values() {
        $result = array();
        $result['controls'] = $this->map_params['specific']['controls'];
        $result['options'] = $this->map_params['specific']['options'];
        if (array_key_exists('controls-zoom', $_POST)) {
            $result['controls']['zoom'] = ($_POST['controls-zoom'] == 'on');
        }
        if (array_key_exists('options-overlay', $_POST)) {
            if (in_array($_POST['options-overlay'], array('styles:basic', 'styles:bright', 'styles:darkmatter', 'styles:hybrid', 'styles:positron', 'styles:streets', 'styles:topo', 'styles:voyager', 'data:hillshades', 'data:terrain-rgb'))) {
                $result['options']['overlay'] = $_POST['options-overlay'];
            }
        }
        return $result;
    }

    /**
     * Output the specific resources.
     *
     * @return string The output of the specific resources, ready to print.
     * @since 3.8.0
     */
    protected function specific_resources(){
        $result = '';
        return $result;
    }

    /**
     * Output the specific styles.
     *
     * @return string The output of the specific styles, ready to print.
     * @since 3.8.0
     */
    protected function specific_styles(){
        $result = '';
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "#" . $this->uniq . " #maptiler-" . $this->uniq . " .leaflet-control-zoom {display: none !important;}" . PHP_EOL;
        }
        return $result;
    }

    /**
     * Output the specific container.
     *
     * @return string The output of the specific container, ready to print.
     * @since 3.8.0
     */
    protected function specific_container(){
        $result = '<div id="maptiler-' . $this->uniq . '" style="width:100%;height:100%;"></div>';
        return $result;
    }

    /**
     * Output the specific script.
     *
     * @return string The output of the specific script, ready to print.
     * @since 3.8.0
     */
    protected function specific_script(){
        $style = explode(':', $this->map_params['specific']['options']['overlay']);
        $ext = 'png';
        if (count($style) === 2) {
            if ($style[1] == 'hybrid') {
                $ext = 'jpg';
            }
            if ($style[0] == 'styles') {
                $style[0] = 'maps';
            }
            if ($style[0] == 'data') {
                $style[0] = 'tiles';
            }
            $result = '';
            $result .= "var layer = new L.tileLayer('https://api.maptiler.com/" . $style[0] . "/" . $style[1] . "/{z}/{x}/{y}." . $ext . "?key={accessToken}', {" . PHP_EOL;
        }
        else {
            $result = '';
            $result .= "var layer = new L.tileLayer('https://api.maptiler.com/styles/hybrid/{z}/{x}/{y}.jpg?key={accessToken}', {" . PHP_EOL;
        }

        $result .= '  attribution: "Maps &copy; <a href=\"https://www.maptiler.com/\">Maptiler</a>. Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap contributors</a>",' . PHP_EOL;
        $result .= '  maxZoom: ' . $this->maxzoom . ',' . PHP_EOL;
        $result .= '  minZoom: ' . $this->minzoom . ',' . PHP_EOL;
        $result .= '  accessToken: "' . get_option('live_weather_station_maptiler_apikey') . '"' . PHP_EOL;
        $result .= '});' . PHP_EOL;
        $result .= "var map = new L.Map('maptiler-" . $this->uniq . "', {" . PHP_EOL;
        $result .= "  center: new L.LatLng(" . $this->map_params['common']['loc_latitude'] . ", " . $this->map_params['common']['loc_longitude'] . ")," . PHP_EOL;
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "  scrollWheelZoom: false," . PHP_EOL;
        }
        $result .= "  zoom: " . $this->map_params['common']['loc_zoom'] . PHP_EOL;
        $result .= "});" . PHP_EOL;
        $result .= "map.attributionControl.setPrefix('');" . PHP_EOL;
        $result .= "map.addLayer(layer);" . PHP_EOL;
        if ($this->map_params['marker']['type'] != 'none') {
            $result .= "" . PHP_EOL;
            $result .= $this->output_markers();
            $result .= "" . PHP_EOL;
        }
        return $result;
    }


    /**
     * Verify if quota allows to display the map.
     *
     * @return boolean True if quota allows to output the map, false otherwise.
     * @since 3.8.0
     */
    protected function quota_verify() {
        return Quota::verify($this->service, 'GET');
    }

    /**
     * Verify if the map has feature box.
     *
     * @return boolean True if the map has feature box, false otherwise.
     * @since 3.8.0
     */
    public function has_feature() {
        return true;
    }

    /**
     * Output the feature box.
     *
     * @return string The control ready to print.
     * @since 3.8.0
     */
    public function output_feature() {
        $content = '<table cellspacing="0" style="display:table;" class="lws-settings"><tbody>';
        $content .= $this->get_key_value_option_select('options-overlay', __('Overlay', 'live-weather-station'), $this->get_maptiler_overlay_js_array(), true, $this->map_params['specific']['options']['overlay']);
        $content .= '</tbody></table>';
        return $content;
    }

    /**
     * Verify if the map has control box.
     *
     * @return boolean True if the map has control box, false otherwise.
     * @since 3.8.0
     */
    public function has_control() {
        return true;
    }

    /**
     * Output the control box.
     *
     * @return string The control ready to print.
     * @since 3.8.0
     */
    public function output_control() {
        $content = '<table cellspacing="0" style="display:table;" class="lws-settings"><tbody>';
        $content .= $this->get_key_value_option_select('controls-zoom', __('Zoom', 'live-weather-station'), $this->get_activated_js_array(), true, $this->map_params['specific']['controls']['zoom'] ? 'on' : 'off');
        $content .= '</tbody></table>';
        return $content;
    }

}