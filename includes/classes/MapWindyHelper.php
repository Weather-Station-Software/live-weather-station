<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Arrays\Generator;

/**
 * This class builds elements of the map view for Windy maps.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

class WindyHandling extends BaseHandling {

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

    protected $type = 1;
    public $service = 'Windy';
    protected $maxzoom = 11;

    /**
     * Initialize the map and set its specific properties.
     *
     * @return array The specific parameters.
     * @since 3.7.0
     */
    protected function specific_params() {
        $result = array();
        $result['controls']['zoom'] = true;
        $result['controls']['selector'] = true;
        $result['controls']['picker'] = true;
        $result['controls']['footer'] = 'both';  // 'none', 'legend', 'calendar' or 'both'
        $result['options']['overlay'] = 'wind';  // 'wind', 'temp', 'rain', 'clouds', 'pressure', 'currents' or 'waves'
        $result['options']['isolines'] = 'none'; // 'none', 'pressure', 'temp', 'deg0' (Freezing Altitude) or 'gh' (Geopotential Height)
        $result['options']['animation'] = true;
        $result['options']['graticule'] = true;

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
        if (array_key_exists('controls-selector', $_POST)) {
            $result['controls']['selector'] = ($_POST['controls-selector'] == 'on');
        }
        if (array_key_exists('controls-picker', $_POST)) {
            $result['controls']['picker'] = ($_POST['controls-picker'] == 'on');
        }
        if (array_key_exists('controls-footer', $_POST)) {
            if (in_array($_POST['controls-footer'], array('none', 'legend', 'calendar', 'both'))) {
                $result['controls']['footer'] = $_POST['controls-footer'];
            }
        }
        if (array_key_exists('options-overlay', $_POST)) {
            if (in_array($_POST['options-overlay'], array('wind', 'temp', 'rain', 'clouds', 'pressure', 'currents', 'waves'))) {
                $result['options']['overlay'] = $_POST['options-overlay'];
            }
        }
        if (array_key_exists('options-isolines', $_POST)) {
            if (in_array($_POST['options-isolines'], array('none', 'pressure', 'temp', 'deg0', 'gh'))) {
                $result['options']['isolines'] = $_POST['options-isolines'];
            }
        }
        if (array_key_exists('options-animation', $_POST)) {
            $result['options']['animation'] = ($_POST['options-animation'] == 'on');
        }
        if (array_key_exists('options-graticule', $_POST)) {
            $result['options']['graticule'] = ($_POST['options-graticule'] == 'on');
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
        wp_enqueue_script('lws-windy-boot');
        return $result;
    }

    /**
     * Output the specific styles.
     *
     * @return string The output of the specific styles, ready to print.
     * @since 3.7.0
     */
    protected function specific_styles(){
        $result = "#" . $this->uniq . " #windy #logo-wrapper {left: -18px !important;top: 0px !important;border: none !important;}" . PHP_EOL;
        $result .= "#" . $this->uniq . " #windy #logo {left: 0 !important;margin-left: 0 !important;border: none !important;}" . PHP_EOL;
        $result .= "#" . $this->uniq . " #windy #logo img {pointer-events: auto !important;border: none !important;}" . PHP_EOL;
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "#" . $this->uniq . " #windy #embed-zoom {display: none !important;}" . PHP_EOL;
        }
        if (!$this->map_params['specific']['controls']['selector']) {
            $result .= "#" . $this->uniq . " #windy #mobile-ovr-select {display: none !important;}" . PHP_EOL;
        }
        if (!$this->map_params['specific']['controls']['picker']) {
            $result .= "#" . $this->uniq . " #windy .picker {display: none !important;}" . PHP_EOL;
        }
        if ($this->map_params['specific']['controls']['footer'] === 'legend') {
            $result .= "#" . $this->uniq . " #windy #progress-bar {display: none !important;}" . PHP_EOL;
        }
        if ($this->map_params['specific']['controls']['footer'] === 'calendar') {
            $result .= "#" . $this->uniq . " #windy #legend-mobile {display: none !important;}" . PHP_EOL;
            $result .= "#" . $this->uniq . " #windy #accumulations {margin-bottom: -18px !important;left: 50px !important;}" . PHP_EOL;
        }
        if ($this->map_params['specific']['controls']['footer'] === 'none') {
            $result .= "#" . $this->uniq . " #windy #progress-bar {display: none !important;}" . PHP_EOL;
            $result .= "#" . $this->uniq . " #windy #legend-mobile {display: none !important;}" . PHP_EOL;
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
        $result = '<div id="windy" style="width:100%;height:100%;"></div>';
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
        $result .= "const options = {" . PHP_EOL;
        $result .= "  key: '" . get_option('live_weather_station_windy_apikey') . "'," . PHP_EOL;
        $result .= "  verbose: false," . PHP_EOL;
        $result .= "  lat: " . $this->map_params['common']['loc_latitude'] . "," . PHP_EOL;
        $result .= "  lon: " . $this->map_params['common']['loc_longitude'] . "," . PHP_EOL;
        $result .= "  zoom: " . $this->map_params['common']['loc_zoom'] . "," . PHP_EOL;
        $result .= "  hourFormat: '24h'," . PHP_EOL;
        $result .= "  latlon: true," . PHP_EOL;

        $result .= "  overlay: '" . $this->map_params['specific']['options']['overlay'] . "'," . PHP_EOL;
        if ($this->map_params['specific']['options']['isolines'] !== 'none') {
            $result .= "  isolines: '" . $this->map_params['specific']['options']['isolines'] . "'," . PHP_EOL;
        }
        $result .= "  particlesAnim: '" . ($this->map_params['specific']['options']['animation'] ? 'on' : 'off') . "'," . PHP_EOL;
        $result .= "  graticule: " . ($this->map_params['specific']['options']['graticule'] ? 'true' : 'false') . "," . PHP_EOL;
        $result .= "}" . PHP_EOL;
        $result .= "windyInit(options, windyAPI => {" . PHP_EOL;
        $result .= "  var {map, overlays, picker} = windyAPI" . PHP_EOL;
        $result .= "  overlays.wind.setMetric('" . $this->get_wind_speed_unit(get_option('live_weather_station_unit_wind_strength')) ."')" . PHP_EOL;
        $result .= "  overlays.temp.setMetric('" . $this->get_temperature_unit(get_option('live_weather_station_unit_temperature')) ."')" . PHP_EOL;
        $result .= "  overlays.rain.setMetric('" . $this->get_rain_unit(get_option('live_weather_station_unit_rain_snow')) ."')" . PHP_EOL;
        //$result .= "  overlays.snow.setMetric('" . $this->get_snow_unit(get_option('live_weather_station_unit_rain_snow')) ."')" . PHP_EOL;
        $result .= "  overlays.waves.setMetric('" . $this->get_altitude_unit(get_option('live_weather_station_unit_altitude')) ."')" . PHP_EOL;
        $result .= "  overlays.pressure.setMetric('" . $this->get_pressure_unit(get_option('live_weather_station_unit_pressure')) ."')" . PHP_EOL;
        //$result .= "  overlays.altitude.setMetric('" . $this->get_altitude_unit(get_option('live_weather_station_unit_altitude')) ."')" . PHP_EOL;
        if (!$this->map_params['specific']['controls']['zoom']) {
            $result .= "  map.scrollWheelZoom.disable()" . PHP_EOL;
        }
        if ($this->map_params['marker']['type'] != 'none') {
            $result .= "" . PHP_EOL;
            $result .= $this->output_markers();
            $result .= "" . PHP_EOL;
        }
        $result .= "})" . PHP_EOL;
        return $result;
    }


    /**
     * Verify if quota allows to display the map.
     *
     * @return boolean True if quota allows to output the map, false otherwise.
     * @since 3.7.0
     */
    protected function quota_verify() {
        $count = 1;
        if ($this->map_params['specific']['controls']['selector']) {
            $count = 7;
        }
        return Quota::verify($this->service, 'GET', 20 * $count);
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
        $content .= $this->get_key_value_option_select('options-overlay', __('Overlay', 'live-weather-station'), $this->get_windymap_overlay_js_array(), true, $this->map_params['specific']['options']['overlay']);
        $content .= $this->get_key_value_option_select('options-isolines', __('Isolines', 'live-weather-station'), $this->get_windymap_isolines_js_array(), true, $this->map_params['specific']['options']['isolines']);
        $content .= $this->get_key_value_option_select('options-animation', __('Animation', 'live-weather-station'), $this->get_activated_js_array(), true, $this->map_params['specific']['options']['animation'] ? 'on' : 'off');
        $content .= $this->get_key_value_option_select('options-graticule', __('Graticule', 'live-weather-station'), $this->get_activated_js_array(), true, $this->map_params['specific']['options']['graticule'] ? 'on' : 'off');
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
        $content .= $this->get_key_value_option_select('controls-selector', __('Selector', 'live-weather-station'), $this->get_activated_js_array(), true, $this->map_params['specific']['controls']['selector'] ? 'on' : 'off');
        $content .= $this->get_key_value_option_select('controls-picker', __('Picker', 'live-weather-station'), $this->get_activated_js_array(), true, $this->map_params['specific']['controls']['picker'] ? 'on' : 'off');
        $content .= $this->get_key_value_option_select('controls-footer', __('Footer', 'live-weather-station'), $this->get_windymap_footer_js_array(), true, $this->map_params['specific']['controls']['footer']);
        $content .= '</tbody></table>';
        return $content;
    }

}