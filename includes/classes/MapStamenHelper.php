<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Arrays\Generator;
use WeatherStation\System\Output\Guard;

/**
 * This class builds elements of the map view for Stamen maps.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

class StamenHandling extends BaseHandling {

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

    protected $type = 2;
    public $service = 'Stamen';
    protected $maxzoom = 16;

    /**
     * The overlays (Stadia Maps styles) that can still be served.
     *
     * The old "terrain-classic" and "toner-hybrid" styles have no raster version anymore.
     *
     * @since 3.9.0
     */
    private static $overlays = array('terrain', 'terrain-background', 'toner', 'toner-background', 'toner-lite', 'watercolor');

    /**
     * Verify if the map can not use the Stadia Maps tiles and must fall back to OpenStreetMap.
     *
     * It is the case when no Stadia Maps API key is set, or when the saved overlay does not exist anymore
     * (removed style, or a map which was previously a retired Navionics map).
     *
     * @return boolean True if the OpenStreetMap fallback must be used, false otherwise.
     * @since 3.9.0
     */
    private function use_fallback() {
        if ((string)get_option('live_weather_station_stadia_apikey') === '') {
            return true;
        }
        $overlay = (isset($this->map_params['specific']['options']['overlay']) && is_string($this->map_params['specific']['options']['overlay'])) ? $this->map_params['specific']['options']['overlay'] : '';
        return !in_array($overlay, self::$overlays, true);
    }

    /**
     * Initialize the map and set its specific properties.
     *
     * @return array The specific parameters.
     * @since 3.7.0
     */
    protected function specific_params() {
        $result = array();
        $result['controls']['zoom'] = true;
        $result['options']['overlay'] = 'terrain';
        return $result;
    }

    /**
     * Get the post values.
     *
     * @return array The specific parameters.
     * @since 3.7.0
     */
    public function get_specific_post_values() {
        $result = array();
        $result['controls'] = $this->map_params['specific']['controls'];
        $result['options'] = $this->map_params['specific']['options'];
        if (array_key_exists('controls-zoom', $_POST)) {
            $result['controls']['zoom'] = ($_POST['controls-zoom'] == 'on');
        }
        if (array_key_exists('options-overlay', $_POST)) {
            // The caller (MapHelper::edit_map) has already checked the capability and the nonce.
            $overlay = is_string($_POST['options-overlay']) ? wp_unslash($_POST['options-overlay']) : '';
            if (in_array($overlay, self::$overlays, true)) {
                $result['options']['overlay'] = $overlay;
            }
        }
        return $result;
    }

    /**
     * Output the specific resources.
     *
     * @return string The output of the specific resources, ready to print.
     * @since 3.7.0
     */
    protected function specific_resources(){
        $result = '';
        if ($this->use_fallback()) {
            // The notice is only for the administrators, in the admin screens (never for the visitors of the site).
            if (is_admin() && current_user_can(apply_filters('lws_manage_options_capability', 'manage_options'))) {
                $result .= '<p class="notice notice-warning inline" style="padding:8px 12px;">' . esc_html__('This map uses OpenStreetMap: Stamen maps now need a Stadia Maps API key (and an existing style). Please enter your Stadia Maps API key in the Services settings.', 'live-weather-station') . '</p>';
            }
        }
        else {
            wp_enqueue_script('lws-stamen-boot');
        }
        return $result;
    }

    /**
     * Output the specific styles.
     *
     * @return string The output of the specific styles, ready to print.
     * @since 3.7.0
     */
    protected function specific_styles(){
        $result = '';
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "#" . $this->uniq . " #stamen-" . $this->uniq . " .leaflet-control-zoom {display: none !important;}" . PHP_EOL;
        }
        return $result;
    }

    /**
     * Output the specific container.
     *
     * @return string The output of the specific container, ready to print.
     * @since 3.7.0
     */
    protected function specific_container(){
        $result = '<div id="stamen-' . $this->uniq . '" style="width:100%;height:100%;"></div>';
        return $result;
    }

    /**
     * Output the specific script.
     *
     * @return string The output of the specific script, ready to print.
     * @since 3.7.0
     */
    protected function specific_script(){
        $result = '';
        if ($this->use_fallback()) {
            $result .= "var layer = new L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {" . PHP_EOL;
            $result .= '  attribution: "Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap contributors</a>",' . PHP_EOL;
            $result .= '  maxZoom: 19' . PHP_EOL;
            $result .= '});' . PHP_EOL;
        }
        else {
            $result .= "var layer = new L.StamenTileLayer(" . Guard::js($this->map_params['specific']['options']['overlay']) . ", {apiKey: " . Guard::js(get_option('live_weather_station_stadia_apikey')) . "});" . PHP_EOL;
        }
        $result .= "var map = new L.Map('stamen-" . $this->uniq . "', {" . PHP_EOL;
        $result .= "  center: new L.LatLng(" . (float)$this->map_params['common']['loc_latitude'] . ", " . (float)$this->map_params['common']['loc_longitude'] . ")," . PHP_EOL;
        $result .= '  maxZoom: ' . $this->maxzoom . ',' . PHP_EOL;
        $result .= '  minZoom: ' . $this->minzoom . ',' . PHP_EOL;
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "  scrollWheelZoom: false," . PHP_EOL;
        }
        $result .= "  zoom: " . (int)$this->map_params['common']['loc_zoom'] . PHP_EOL;
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
     * @since 3.7.0
     */
    protected function quota_verify() {
        return Quota::verify($this->service, 'GET', 20);
    }

    /**
     * Verify if the map has feature box.
     *
     * @return boolean True if the map has feature box, false otherwise.
     * @since 3.7.0
     */
    public function has_feature() {
        return true;
    }

    /**
     * Output the feature box.
     *
     * @return string The control ready to print.
     * @since 3.7.0
     */
    public function output_feature() {
        $content = '<table cellspacing="0" style="display:table;" class="lws-settings"><tbody>';
        $content .= $this->get_key_value_option_select('options-overlay', __('Overlay', 'live-weather-station'), $this->get_stamenmap_overlay_js_array(), true, $this->map_params['specific']['options']['overlay']);
        $content .= '</tbody></table>';
        return $content;
    }

    /**
     * Verify if the map has control box.
     *
     * @return boolean True if the map has control box, false otherwise.
     * @since 3.7.0
     */
    public function has_control() {
        return true;
    }

    /**
     * Output the control box.
     *
     * @return string The control ready to print.
     * @since 3.7.0
     */
    public function output_control() {
        $content = '<table cellspacing="0" style="display:table;" class="lws-settings"><tbody>';
        $content .= $this->get_key_value_option_select('controls-zoom', __('Zoom', 'live-weather-station'), $this->get_activated_js_array(), true, $this->map_params['specific']['controls']['zoom'] ? 'on' : 'off');
        $content .= '</tbody></table>';
        return $content;
    }

}