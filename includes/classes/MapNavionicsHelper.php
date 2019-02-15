<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Arrays\Generator;

/**
 * This class builds elements of the map view for Navionics maps.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */

class NavionicsHandling extends BaseHandling {

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

    protected $type = 7;
    public $service = 'Navionics';
    protected $minzoom = 6;
    protected $maxzoom = 18;

    /**
     * Initialize the map and set its specific properties.
     *
     * @return array The specific parameters.
     * @since 3.8.0
     */
    protected function specific_params() {
        $result = array();
        $result['controls']['zoom'] = true;
        $result['options']['overlay'] = 'JNC.NAVIONICS_CHARTS.NAUTICAL';
        $result['options']['basemap'] = 'none';
        $result['options']['depth'] = 'JNC.SAFETY_DEPTH_LEVEL.LEVEL4';
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
            if (in_array($_POST['options-overlay'], array('JNC.NAVIONICS_CHARTS.NAUTICAL', 'JNC.NAVIONICS_CHARTS.SONARCHART', 'JNC.NAVIONICS_CHARTS.SKI'))) {
                $result['options']['overlay'] = $_POST['options-overlay'];
            }
        }
        if (array_key_exists('options-depth', $_POST)) {
            if (in_array($_POST['options-depth'], array('JNC.SAFETY_DEPTH_LEVEL.LEVEL0', 'JNC.SAFETY_DEPTH_LEVEL.LEVEL1', 'JNC.SAFETY_DEPTH_LEVEL.LEVEL2', 'JNC.SAFETY_DEPTH_LEVEL.LEVEL3', 'JNC.SAFETY_DEPTH_LEVEL.LEVEL4'))) {
                $result['options']['depth'] = $_POST['options-depth'];
            }
        }
        if (array_key_exists('options-basemap', $_POST)) {
            if (in_array($_POST['options-basemap'], array('none', 'carto:light_all', 'carto:light_nolabels', 'carto:light_only_labels', 'carto:dark_all', 'carto:dark_nolabels', 'carto:dark_only_labels'))) {
                $result['options']['basemap'] = $_POST['options-basemap'];
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
        wp_enqueue_script('lws-navionics');
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
        wp_enqueue_style('lws-navionics');
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "#" . $this->uniq . " #navionics-" . $this->uniq . " .leaflet-control-zoom {display: none !important;}" . PHP_EOL;
        }
        $result .= "#" . $this->uniq . " .g-navionics-overlay-logo {bottom: 5px !important;}" . PHP_EOL;
        return $result;
    }

    /**
     * Output the specific container.
     *
     * @return string The output of the specific container, ready to print.
     * @since 3.8.0
     */
    protected function specific_container(){
        $result = '<div id="navionics-' . $this->uniq . '" style="width:100%;height:100%;"></div>';
        return $result;
    }

    /**
     * Output the specific script.
     *
     * @return string The output of the specific script, ready to print.
     * @since 3.8.0
     */
    protected function specific_script(){
        $result = '';
        $quota = true;
        if ($this->map_params['specific']['options']['basemap'] != 'none') {
            $m = explode(':', $this->map_params['specific']['options']['basemap']);
            if (count($m) === 2) {
                if (strtolower($m[0]) === 'carto') {
                    $quota = Quota::verify('Carto', 'GET', 20);
                    $result .= "var bg = new L.tileLayer('https://{s}.basemaps.cartocdn.com/" . $m[1] . "/{z}/{x}/{y}.png', {" . PHP_EOL;
                    $result .= '  attribution: "Ground maps &copy; <a href=\"https://carto.com/attributions\">CARTO</a>. Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap contributors</a>",' . PHP_EOL;
                    $result .= '});' . PHP_EOL;
                }
            }
            else {
                $result .= "var bg = new L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {" . PHP_EOL;
                $result .= '  attribution: "Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap contributors</a>",' . PHP_EOL;
                $result .= '});' . PHP_EOL;
            }
        }
        $result .= "var map = new L.Map('navionics-" . $this->uniq . "');" . PHP_EOL;
        $result .= "var overlay = new JNC.Leaflet.NavionicsOverlay({" . PHP_EOL;
        $result .= '  navKey: \'' . get_option('live_weather_station_navionics_apikey') . '\',' . PHP_EOL;
        $result .= '  chartType: ' . $this->map_params['specific']['options']['overlay'] . ',' . PHP_EOL;
        if (get_option('live_weather_station_unit_distance') == 1) {
            $result .= '  depthUnit: JNC.DEPTH_UNIT.FEET,' . PHP_EOL;
        }
        else {
            $result .= '  depthUnit: JNC.DEPTH_UNIT.METERS,' . PHP_EOL;
        }
        $result .= '  depthLevel: ' . $this->map_params['specific']['options']['depth'] . ',' . PHP_EOL;
        $result .= '  maxZoom: ' . $this->maxzoom . ',' . PHP_EOL;
        $result .= '  minZoom: ' . $this->minzoom . ',' . PHP_EOL;
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "  scrollWheelZoom: false," . PHP_EOL;
        }
        $result .= '  isTransparent: true,' . PHP_EOL;
        $result .= '  zIndex: 1' . PHP_EOL;
        $result .= "});" . PHP_EOL;
        $result .= "map.setView([" . $this->map_params['common']['loc_latitude'] . ", " . $this->map_params['common']['loc_longitude'] . "], " . $this->map_params['common']['loc_zoom'] . ");" . PHP_EOL;
        $result .= "map.attributionControl.setPrefix('');";
        if ($quota && $this->map_params['specific']['options']['basemap'] != 'none') {
            $result .= "map.addLayer(bg);" . PHP_EOL;
        }
        $result .= "map.addLayer(overlay);" . PHP_EOL;
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
        return Quota::verify($this->service, 'GET', 20);
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
        $content .= $this->get_key_value_option_select('options-basemap', __('Base map', 'live-weather-station'), $this->get_basemap_js_array(), true, $this->map_params['specific']['options']['basemap']);
        $content .= $this->get_key_value_option_select('options-overlay', __('Overlay', 'live-weather-station'), $this->get_navionicsmap_overlay_js_array(), true, $this->map_params['specific']['options']['overlay']);
        $content .= $this->get_key_value_option_select('options-depth', lws__('Safety depth', 'live-weather-station'), $this->get_navionicsmap_depth_js_array(), true, $this->map_params['specific']['options']['depth']);
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