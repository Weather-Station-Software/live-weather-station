<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Outdoor weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Outdoor extends Base {

    use Output, ID;

    /**
     * Register the widget.
     *
     * @since 1.0.0
     */
    public static function widget_registering() {
        register_widget('\WeatherStation\UI\Widget\Outdoor');
    }

    /**
     * Initialize the widget.
     *
     * @since 1.0.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Outdoor',
            '<>🌤 ' . __( 'Outdoor weather summary' , 'live-weather-station'),
            array( 'description' => sprintf(__('Display outdoor measurements of a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
        );
        if ( is_admin() || is_blog_admin()) {
            add_action( 'admin_enqueue_scripts', function () {wp_enqueue_script( 'wp-color-picker' );});
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'underscore' );
        }
    }

    /**
     * Get the widget settings.
     *
     * @param    array  $instance   An array containing settings for the widget.
     * @return   array  An array containing settings with defaults for the widget.
     * @since    1.2.0
     */
    private function _get_instance($instance)
    {
        $result = wp_parse_args((array)$instance,
            array('title' => '',
                'subtitle' => 1,
                'station' => 'N/A',
                'bg_color' => '#444444',
                'bg_opacity' => 0,
                'width' => 300,
                'txt_color' => '#ffffff',
                'show_tooltip' => false,
                'show_borders' => false,
                'hide_obsolete' => false,
                'show_current' => false,
                'show_temperature' => false,
                'show_pressure' => false,
                'show_humidity' => false,
                'show_dew' => false,
                'show_frost' => false,
                'show_heat' => false,
                'show_humidex' => false,
                'show_summer_simmer' => false,
                'show_steadman' => false,
                'show_rain' => false,
                'show_snow' => false,
                'show_wind' => false,
                'show_windchill' => false,
                'show_location' => false,
                'show_cloud_ceiling' => false,
                'show_cloud_cover' => false,
                'show_strike' => false,
                'show_uv' => false,
                'follow_light' => false,
                'fixed_background' => false,
                'day_url' => '',
                'night_url' => '',
                'dawn_url' => '',
                'dusk_url' => '',
                'flat_design' => false));
        $result['show_tooltip'] = !empty($result['show_tooltip']) ? 1 : 0;
        $result['show_borders'] = !empty($result['show_borders']) ? 1 : 0;
        $result['hide_obsolete'] = !empty($result['hide_obsolete']) ? 1 : 0;
        $result['show_current'] = !empty($result['show_current']) ? 1 : 0;
        $result['show_temperature'] = !empty($result['show_temperature']) ? 1 : 0;
        $result['show_pressure'] = !empty($result['show_pressure']) ? 1 : 0;
        $result['show_humidity'] = !empty($result['show_humidity']) ? 1 : 0;
        $result['show_dew'] = !empty($result['show_dew']) ? 1 : 0;
        $result['show_frost'] = !empty($result['show_frost']) ? 1 : 0;
        $result['show_heat'] = !empty($result['show_heat']) ? 1 : 0;
        $result['show_humidex'] = !empty($result['show_humidex']) ? 1 : 0;
        $result['show_summer_simmer'] = !empty($result['show_summer_simmer']) ? 1 : 0;
        $result['show_steadman'] = !empty($result['show_steadman']) ? 1 : 0;
        $result['show_rain'] = !empty($result['show_rain']) ? 1 : 0;
        $result['show_snow'] = !empty($result['show_snow']) ? 1 : 0;
        $result['show_wind'] = !empty($result['show_wind']) ? 1 : 0;
        $result['show_windchill'] = !empty($result['show_windchill']) ? 1 : 0;
        $result['show_location'] = !empty($result['show_location']) ? 1 : 0;
        $result['show_cloud_ceiling'] = !empty($result['show_cloud_ceiling']) ? 1 : 0;
        $result['show_cloud_cover'] = !empty($result['show_cloud_cover']) ? 1 : 0;
        $result['show_strike'] = !empty($result['show_strike']) ? 1 : 0;
        $result['show_uv'] = !empty($result['show_uv']) ? 1 : 0;
        $result['flat_design'] = !empty($result['flat_design']) ? 1 : 0;
        $result['follow_light'] = !empty($result['follow_light']) ? 1 : 0;
        $result['fixed_background'] = !empty($result['fixed_background']) ? 1 : 0;
        return $result;
    }

    /**
     * Get the settings form.
     *
     * @param    array  $instance   An array containing settings for the widget.
     * @return boolean Nothing.
     * @since    1.0.0
     * @access   public
     */
    public function form($instance) {
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $subtitle = $instance['subtitle'];
        $station = $instance['station'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_pressure = (bool)$instance['show_pressure'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_dew = (bool)$instance['show_dew'] ;
        $show_frost = (bool)$instance['show_frost'] ;
        $show_heat = (bool)$instance['show_heat'] ;
        $show_humidex = (bool)$instance['show_humidex'] ;
        $show_summer_simmer = (bool)$instance['show_summer_simmer'] ;
        $show_steadman = (bool)$instance['show_steadman'] ;
        $show_rain = (bool)$instance['show_rain'] ;
        $show_snow = (bool)$instance['show_snow'] ;
        $show_wind = (bool)$instance['show_wind'] ;
        $show_windchill = (bool)$instance['show_windchill'] ;
        $show_location = (bool)$instance['show_location'] ;
        $show_cloud_ceiling = (bool)$instance['show_cloud_ceiling'] ;
        $show_cloud_cover = (bool)$instance['show_cloud_cover'] ;
        $show_strike = (bool)$instance['show_strike'] ;
        $show_uv = (bool)$instance['show_uv'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_light = (bool)$instance['follow_light'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $day_url = $instance['day_url'];
        $night_url = $instance['night_url'];
        $dawn_url = $instance['dawn_url'];
        $dusk_url = $instance['dusk_url'];
        $stations = $this->get_operational_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetOutdoorSettings.php');
    }

    /**
     * Set the (inline) css for the widget rendering.
     *
     * @param array $instance An array containing settings for the widget.
     * @param string $uid Identifiant of the widget.
     * @param boolean $flat_design Enabling flat design mode.
     * @param integer $dawndusk Luminosity factor from 0% to 100%.
     * @param string $background Optional. CSS for background Image URL.
     * @param string $attachment Optional. CSS for background-attachment.
     * @since 1.0.0
     */
    public function css($instance, $uid, $flat_design, $dawndusk=100, $background='', $attachment) {
        lws_font_awesome();
        try
        {
            $maxwidth = round ($instance['width']);

        }
        catch(\Exception $ex)
        {
            $maxwidth = 0;
        }
        $txt_color = $instance['txt_color'];
        $bg_color = $instance['bg_color'];
        if (!$txt_color) {
            $txt_color = '#444444';
        }
        if (!$bg_color) {
            $txt_color = '#FFFFFF';
        }
        if ($flat_design) {
            $fact = 80;
        }
        else {
            $fact = 98;
        }
        $c = new Color($bg_color);
        if ($dawndusk < 100) {
            $color = new Color($c->darken(round(($fact * $c->getHsl()['L']) * (1 - ($dawndusk / 100)), 0)));
        }
        else {
            $color = $c;
        }
        $opacity = (11 - $instance['bg_opacity'])/11;
        if ($opacity < 0.1) {
            $opacity = 0;
        }
        if ($color->isDark()) {
            $gradient = $color->makeGradient(20);
        }
        else
        {
            $gradient = $color->makeGradient(15);
        }
        $border = new Color($gradient['light']);
        $icon = new Color($txt_color);
        if ($border->isDark()) {
            $ico_color = '#'.$icon->darken(1);
            $unit_color = '#'.$icon->lighten(1);
        }
        else {
            $ico_color = '#'.$icon->lighten(1);
            $unit_color = '#'.$icon->darken(1);
        }
        if ($color->isDark()) {
            if ($icon->isDark()) {
                $bcc = $icon->darken(4);
            }
            else {
                $bcc = $icon->darken(30);
            }
        }
        else {
            if ($icon->isDark()) {
                $bcc = $icon->lighten(4);
            }
            else {
                $bcc = $icon->lighten(20);
            }
        }
        if ($flat_design) {
            $gradient_dark = Color::hexToRgbString('#'.$color->getHex(), $opacity);
            $gradient_light = Color::hexToRgbString('#'.$color->getHex(), $opacity);
            $border_color1 = '#'.$bcc;
            $border_color2 = '#'.$bcc;
        }
        else {
            $gradient_dark = Color::hexToRgbString('#'.$gradient['dark'], $opacity);
            $gradient_light = Color::hexToRgbString('#'.$gradient['light'], $opacity);
            $border_color1 = '#'.$border->darken();
            $border_color2 = '#'.$border->darken(16);
        }
        $id = $uid;
        $shadows = !$flat_design;
        $borders = $instance['show_borders'];
        $background_attachment = $attachment;
        $bg_url = $background;
        $wtype = 'outdoor';
        $text_shadows = WidgetHelper::text_shadow();
        $box_shadows = WidgetHelper::box_shadow();
        $box_radius = WidgetHelper::box_radius();
        if (LWS_FA_SVG) {
            $svg = 'svg{' . WidgetHelper::svg_shadow() . '}';
        }
        else {
            $svg = '';
        }
        ob_start();
        include LWS_PUBLIC_DIR.'partials/WidgetDisplayCSS.php';
        return ob_get_clean();
    }

    /**
     * Update settings of the widget.
     *
     * @param    array  $new_instance   An array containing the new settings for the widget.
     * @param    array  $old_instance   An array containing the old settings for the widget.
     * @return   array  An array containing the validated settings for the widget, ready to store.
     * @since    1.0.0
     * @access   public
     */
    public function update($new_instance, $old_instance) {
        $instance = $this->_get_instance($old_instance);
        $new_instance = $this->_get_instance($new_instance);
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['subtitle'] = $new_instance['subtitle'];
        $instance['station'] = $new_instance['station'];
        $instance['bg_color'] = $new_instance['bg_color'];
        $instance['bg_opacity'] = $new_instance['bg_opacity'];
        $instance['width'] = $new_instance['width'];
        $instance['txt_color'] = $new_instance['txt_color'];
        $instance['show_tooltip'] = !empty($new_instance['show_tooltip']) ? 1 : 0;
        $instance['show_borders'] = !empty($new_instance['show_borders']) ? 1 : 0;
        $instance['hide_obsolete'] = !empty($new_instance['hide_obsolete']) ? 1 : 0;
        $instance['show_current'] = !empty($new_instance['show_current']) ? 1 : 0;
        $instance['show_temperature'] = !empty($new_instance['show_temperature']) ? 1 : 0;
        $instance['show_pressure'] = !empty($new_instance['show_pressure']) ? 1 : 0;
        $instance['show_humidity'] = !empty($new_instance['show_humidity']) ? 1 : 0;
        $instance['show_dew'] = !empty($new_instance['show_dew']) ? 1 : 0;
        $instance['show_frost'] = !empty($new_instance['show_frost']) ? 1 : 0;
        $instance['show_heat'] = !empty($new_instance['show_heat']) ? 1 : 0;
        $instance['show_humidex'] = !empty($new_instance['show_humidex']) ? 1 : 0;
        $instance['show_summer_simmer'] = !empty($new_instance['show_summer_simmer']) ? 1 : 0;
        $instance['show_steadman'] = !empty($new_instance['show_steadman']) ? 1 : 0;
        $instance['show_rain'] = !empty($new_instance['show_rain']) ? 1 : 0;
        $instance['show_snow'] = !empty($new_instance['show_snow']) ? 1 : 0;
        $instance['show_wind'] = !empty($new_instance['show_wind']) ? 1 : 0;
        $instance['show_windchill'] = !empty($new_instance['show_windchill']) ? 1 : 0;
        $instance['show_location'] = !empty($new_instance['show_location']) ? 1 : 0;
        $instance['show_cloud_ceiling'] = !empty($new_instance['show_cloud_ceiling']) ? 1 : 0;
        $instance['show_cloud_cover'] = !empty($new_instance['show_cloud_cover']) ? 1 : 0;
        $instance['show_strike'] = !empty($new_instance['show_strike']) ? 1 : 0;
        $instance['show_uv'] = !empty($new_instance['show_uv']) ? 1 : 0;
        $instance['flat_design'] = !empty($new_instance['flat_design']) ? 1 : 0;
        $instance['follow_light'] = !empty($new_instance['follow_light']) ? 1 : 0;
        $instance['fixed_background'] = !empty($new_instance['fixed_background']) ? 1 : 0;
        $instance['day_url'] = $new_instance['day_url'];
        $instance['night_url'] = $new_instance['night_url'];
        $instance['dawn_url'] = $new_instance['dawn_url'];
        $instance['dusk_url'] = $new_instance['dusk_url'];
        return $instance;
    }

    /**
     * Get the widget output.
     *
     * @param array $args An array containing the widget's arguments.
     * @param array $instance An array containing settings for the widget.
     * @return string The widget content.
     * @since 3.8.0
     */
    public function widget_content($args, $instance) {
        $id = uniqid();
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $subtitle = $instance['subtitle'];
        $show_title = !($title=='');
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders =  (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_pressure = (bool)$instance['show_pressure'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_dew = (bool)$instance['show_dew'] ;
        $show_frost = (bool)$instance['show_frost'] ;
        $show_heat = (bool)$instance['show_heat'] ;
        $show_humidex = (bool)$instance['show_humidex'] ;
        $show_summer_simmer = (bool)$instance['show_summer_simmer'] ;
        $show_steadman = (bool)$instance['show_steadman'] ;
        $show_rain = (bool)$instance['show_rain'] ;
        $show_snow = (bool)$instance['show_snow'] ;
        $show_wind = (bool)$instance['show_wind'] ;
        $show_windchill = (bool)$instance['show_windchill'] ;
        $show_location = (bool)$instance['show_location'] ;
        $show_cloud_ceiling = (bool)$instance['show_cloud_ceiling'] ;
        $show_cloud_cover = (bool)$instance['show_cloud_cover'] ;
        $show_strike = (bool)$instance['show_strike'] ;
        $show_uv = (bool)$instance['show_uv'] ;
        $flat = (bool)$instance['flat_design'] ;
        $shadows = !$flat;
        $follow_light = (bool)$instance['follow_light'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $background_attachment = 'local';
        if ($fixed_background) {
            $background_attachment = 'fixed';
        }
        $day_url = $this->get_picture_url($instance['station'], $instance['day_url']);
        $night_url = $this->get_picture_url($instance['station'], $instance['night_url']);
        $dawn_url = $this->get_picture_url($instance['station'], $instance['dawn_url']);
        $dusk_url = $this->get_picture_url($instance['station'], $instance['dusk_url']);
        $bg_url = '';
        $sunrise_a = 0;
        $sunrise = 0;
        $sunset = 0;
        $sunset_a = 0;
        $isday = false;
        $isnight = false;
        $isdawn = false;
        $isdusk = false;
        $dawndusk = 0;
        $rain_multipart = false;
        $wind_multipart = false;
        $temp_multipart = false;
        $NAMain = false;
        $NAModule1 = false;
        $NAModule2 = false;
        $NAModule3 = false;
        $NAModule5 = false;
        $NAModule7 = false;
        $NACurrent = false;
        $NAComputed = false;
        $modules = $this->get_widget_data($instance['station'], 'outdoor', $hide_obsolete);
        $temp = 0;
        $timestamp = '';
        $tz = '';
        $location = '';
        $measurements = array();
        $current = array();
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('pressure_sl', $module['measurements'])){
                            $NAMain = true;
                            $measurements['pressure_sl'] = array();
                            $measurements['pressure_sl']['value'] = $module['measurements']['pressure_sl']['value'];
                            $measurements['pressure_sl']['unit'] = $module['measurements']['pressure_sl']['unit']['unit'];
                            $measurements['pressure_sl']['icon'] = $this->output_iconic_value($module['measurements']['pressure_sl']['raw_value'], 'pressure_sl', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_pressure = false ;
                        }
                        if (array_key_exists('loc_latitude', $module['measurements']) && array_key_exists('loc_longitude', $module['measurements']) && array_key_exists('loc_altitude', $module['measurements'])) {
                            $location = $this->output_coordinate($module['measurements']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                                $this->output_coordinate($module['measurements']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                                $this->output_value($module['measurements']['loc_altitude']['value'], 'loc_altitude', true) . ')';
                        }
                        if (array_key_exists('loc_timezone', $module['measurements'])) {
                            $tz = $module['measurements']['loc_timezone']['value'];
                        }
                        break;
                    case 'NAEphemer':
                        if (array_key_exists('sunrise', $module['measurements']) && array_key_exists('sunset', $module['measurements'])) {
                            $sunrise = $module['measurements']['sunrise']['raw_value'];
                            $sunset = $module['measurements']['sunset']['raw_value'];
                        }
                        if (array_key_exists('sunrise_a', $module['measurements']) && array_key_exists('sunset_a', $module['measurements'])) {
                            $sunrise_a = $module['measurements']['sunrise_a']['raw_value'];
                            $sunset_a = $module['measurements']['sunset_a']['raw_value'];
                        }
                        break;
                    case 'NAModule1': // Outdoor module
                        if (array_key_exists('humidity', $module['measurements'])) {
                            $NAModule1 = true;
                            $measurements['humidity'] = array();
                            $measurements['humidity']['value'] = $module['measurements']['humidity']['value'];
                            $measurements['humidity']['unit'] = $module['measurements']['humidity']['unit']['unit'];
                            $measurements['humidity']['icon'] = $this->output_iconic_value($module['measurements']['humidity']['raw_value'], 'humidity', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_humidity = false;
                        }
                        if (array_key_exists('temperature', $module['measurements'])) {
                            $NAModule1 = true;
                            $measurements['temperature'] = array();
                            $temp = $module['measurements']['temperature']['raw_value'];
                            $measurements['temperature']['value'] = $module['measurements']['temperature']['value'];
                            $measurements['temperature']['unit'] = $module['measurements']['temperature']['unit']['unit'];
                            $measurements['temperature']['icon'] = $this->output_iconic_value($module['measurements']['temperature']['raw_value'], 'temperature', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            if (array_key_exists('temperature_max', $module['measurements']) && array_key_exists('temperature_min', $module['measurements'])) {
                                $measurements['temperature_max'] = array();
                                $measurements['temperature_max']['value'] = $module['measurements']['temperature_max']['value'];
                                $measurements['temperature_max']['unit'] = $module['measurements']['temperature_max']['unit']['unit'];
                                $measurements['temperature_min'] = array();
                                $measurements['temperature_min']['value'] = $module['measurements']['temperature_min']['value'];
                                $measurements['temperature_min']['unit'] = $module['measurements']['temperature_min']['unit']['unit'];
                                $temp_multipart = true;
                            }
                        }
                        else {
                            $show_temperature = false;
                        }
                        break;
                    case 'NAModule3': // Rain gauge
                        //$wug = ID::is_fake_modulex_id($module['id'], 3);
                        if (array_key_exists('rain', $module['measurements'])) {
                            $NAModule3 = true;
                            $measurements['rain'] = array();
                            $measurements['rain']['value'] = $module['measurements']['rain']['value'];
                            $measurements['rain']['unit'] = $module['measurements']['rain']['unit']['unit'];
                            $measurements['rain']['icon'] = $this->output_iconic_value($module['measurements']['rain']['raw_value'], 'rain', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            if (array_key_exists('rain_day_aggregated', $module['measurements'])) {
                                $measurements['rain_day_aggregated'] = array();
                                $measurements['rain_day_aggregated']['value'] = $module['measurements']['rain_day_aggregated']['value'];
                                $measurements['rain_day_aggregated']['unit'] = $module['measurements']['rain_day_aggregated']['unit']['unit'];
                                $rain_multipart = true;
                            }
                        }
                        elseif (array_key_exists('rain_day_aggregated', $module['measurements'])) {
                            $NAModule3 = true;
                            $measurements['rain'] = array();
                            $measurements['rain']['value'] = $module['measurements']['rain_day_aggregated']['value'];
                            $measurements['rain']['unit'] = $module['measurements']['rain_day_aggregated']['unit']['unit'];
                            $measurements['rain']['icon'] = $this->output_iconic_value($module['measurements']['rain_day_aggregated']['raw_value'], 'rain_day_aggregated', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_rain = false;
                        }
                        break;
                    case 'NAModule2': // Wind gauge
                        if (array_key_exists('windangle', $module['measurements']) && array_key_exists('windstrength', $module['measurements'])) {
                            $NAModule2 = true;
                            $measurements['windangle'] = array();
                            $measurements['windangle']['value'] = $module['measurements']['windangle']['value'];
                            $measurements['windangle']['from'] = $this->get_angle_full_text($module['measurements']['windangle']['value']);
                            $measurements['windangle']['icon'] = $this->output_iconic_value($module['measurements']['windangle']['raw_value'], 'windangle', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['windstrength'] = array();
                            $measurements['windstrength']['value'] = $module['measurements']['windstrength']['value'];
                            $measurements['windstrength']['unit'] = $module['measurements']['windstrength']['unit']['unit'];
                            if (array_key_exists('windstrength_day_max', $module['measurements'])) {
                                $measurements['windstrength_max'] = array();
                                $measurements['windstrength_max']['value'] = $module['measurements']['windstrength_day_max']['value'];
                                $measurements['windstrength_max']['unit'] = $module['measurements']['windstrength_day_max']['unit']['unit'];
                                $wind_multipart = true;
                            }
                        }
                        else {
                            $show_wind = false;
                        }
                        break;
                    case 'NAModule5': // Solar
                        if (array_key_exists('uv_index', $module['measurements'])) {
                            $NAModule5 = true;
                            $measurements['uv_index'] = array();
                            $measurements['uv_index']['value'] = $module['measurements']['uv_index']['value'];
                            $measurements['uv_index']['unit'] = __('UV', 'live-weather-station');
                            $measurements['uv_index']['icon'] = $this->output_iconic_value($module['measurements']['uv_index']['raw_value'], 'uv_index', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_uv = false;
                        }
                        break;
                    case 'NAModule7': // Thunderstorm
                        if (array_key_exists('strike_count', $module['measurements'])) {
                            $NAModule7 = true;
                            $measurements['strike'] = array();
                            $measurements['strike']['value'] = $module['measurements']['strike_count']['value'];
                            $measurements['strike']['unit'] = $module['measurements']['strike_count']['unit']['unit'];
                            $measurements['strike']['icon'] = $this->output_iconic_value($module['measurements']['strike_count']['raw_value'], 'strike_count', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        elseif (array_key_exists('strike_instant', $module['measurements'])) {
                            $NAModule7 = true;
                            $measurements['strike'] = array();
                            $measurements['strike']['value'] = $module['measurements']['strike_instant']['value'];
                            $measurements['strike']['unit'] = $module['measurements']['strike_instant']['unit']['unit'];
                            $measurements['strike']['icon'] = $this->output_iconic_value($module['measurements']['strike']['raw_value'], 'strike', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_strike = false;
                        }
                        if (array_key_exists('strike_bearing', $module['measurements'])) {
                            $bearing = $this->get_angle_text($module['measurements']['strike_bearing']['raw_value']) . ', ';
                        }
                        else {
                            $bearing = '';
                        }
                        if (array_key_exists('strike_distance', $module['measurements'])) {
                            $measurements['strike_distance'] = array();
                            $measurements['strike_distance']['value'] = $bearing . $module['measurements']['strike_distance']['value'];
                            $measurements['strike_distance']['unit'] = $module['measurements']['strike_distance']['unit']['unit'];
                            $measurements['strike_distance']['icon'] = $this->output_iconic_value($module['measurements']['strike_distance']['raw_value'], 'strike_distance', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            if (time() - $module['measurements']['strike_distance']['timestamp'] < 3 * HOUR_IN_SECONDS) {
                                $measurements['strike_distance']['ts'] = self::get_time_from_utc($module['measurements']['strike_distance']['timestamp'], $tz);
                            }
                            else {
                                $measurements['strike_distance']['ts'] = '';
                            }
                        }
                        else {
                            $measurements['strike_distance'] = array();
                            $measurements['strike_distance']['value'] = '';
                            $measurements['strike_distance']['unit'] = '';
                            $measurements['strike_distance']['ts'] = '';
                            $measurements['strike_distance']['icon'] = $this->output_iconic_value(0, 'strike_distance', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        break;
                    case 'NACurrent': // Current weather -> OpenWeatherMap
                        $NACurrent = true;
                        if (array_key_exists('is_day', $module['measurements'])) {
                            $measurements['day']['value'] = ($module['measurements']['is_day']['value'] == 1 ? 'day' : 'night');
                        } else {
                            $measurements['day']['value'] = '';
                        }
                        if (array_key_exists('weather', $module['measurements'])) {
                            $measurements['weather']['value'] = $module['measurements']['weather']['value'];
                            $measurements['weather']['icon'] = $this->output_iconic_value($measurements['day']['value'] . '-' . $measurements['weather']['value'], 'weather', null, true, 'inherit', 'lws-widget-big-wiicon-' . $id);
                        }
                        else {
                            $show_current = false;
                        }
                        if (array_key_exists('cloudiness', $module['measurements'])) {
                            $measurements['cloudcover']['value'] = $module['measurements']['cloudiness']['value'];
                            $measurements['cloudcover']['unit'] = $module['measurements']['cloudiness']['unit']['unit'];
                            $measurements['cloudcover']['icon'] = $this->output_iconic_value($module['measurements']['cloudiness']['raw_value'], 'cloudiness', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_cloud_cover = false;
                        }
                        if (array_key_exists('snow', $module['measurements'])) {
                            $measurements['snow']['value'] = $module['measurements']['snow']['value'];
                            $measurements['snow']['unit'] = $module['measurements']['snow']['unit']['unit'];
                            $measurements['snow']['icon'] = $this->output_iconic_value($module['measurements']['snow']['raw_value'], 'snow', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_snow = false;
                        }
                        $current = $module;
                        break;
                    case 'NAComputed': // Computed values
                        $NAComputed = true;
                        // Dew point & frost point
                        if (array_key_exists('temperature_ref', $module['measurements']) &&
                            array_key_exists('dew_point', $module['measurements']) &&
                            array_key_exists('frost_point', $module['measurements'])) {
                            $temp_ref = $module['measurements']['temperature_ref']['raw_value'];
                            $measurements['dew'] = array();
                            $measurements['dew']['value'] = $module['measurements']['dew_point']['value'];
                            $measurements['dew']['unit'] = $module['measurements']['dew_point']['unit']['unit'];
                            $measurements['frost'] = array();
                            $measurements['frost']['value'] = $module['measurements']['frost_point']['value'];
                            $measurements['frost']['unit'] = $module['measurements']['frost_point']['unit']['unit'];
                            $measurements['dew']['icon'] = $this->output_iconic_value($module['measurements']['dew_point']['raw_value'], 'dew_point', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['frost']['icon'] = $this->output_iconic_value($module['measurements']['frost_point']['raw_value'], 'frost_point', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $show_dew = $show_dew && $this->is_valid_dew_point($temp_ref);
                            $show_frost = $show_frost && $this->is_valid_frost_point($temp_ref);
                        } else {
                            $show_dew = false;
                            $show_frost = false;
                        }
                        // Heat index, humidex, Summer Simmer & Steadman
                        if (array_key_exists('temperature_ref', $module['measurements']) &&
                            array_key_exists('humidity_ref', $module['measurements']) &&
                            array_key_exists('dew_point', $module['measurements']) &&
                            array_key_exists('heat_index', $module['measurements']) &&
                            array_key_exists('summer_simmer', $module['measurements']) &&
                            array_key_exists('steadman', $module['measurements']) &&
                            array_key_exists('humidex', $module['measurements'])
                        ) {
                            $temp_ref = $module['measurements']['temperature_ref']['raw_value'];
                            $hum_ref = $module['measurements']['humidity_ref']['raw_value'];
                            $dew_ref = $module['measurements']['dew_point']['raw_value'];
                            $measurements['heat'] = array();
                            $measurements['heat']['value'] = $module['measurements']['heat_index']['value'];
                            $measurements['humidex'] = array();
                            $measurements['humidex']['value'] = $module['measurements']['humidex']['value'];
                            $measurements['summer_simmer'] = array();
                            $measurements['summer_simmer']['value'] = $module['measurements']['summer_simmer']['value'];
                            $measurements['steadman'] = array();
                            $measurements['steadman']['value'] = $module['measurements']['steadman']['value'];
                            $measurements['heat']['icon'] = $this->output_iconic_value($module['measurements']['heat_index']['raw_value'], 'heat_index', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['humidex']['icon'] = $this->output_iconic_value($module['measurements']['humidex']['raw_value'], 'humidex', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['summer_simmer']['icon'] = $this->output_iconic_value($module['measurements']['summer_simmer']['raw_value'], 'summer_simmer', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['steadman']['icon'] = $this->output_iconic_value($module['measurements']['steadman']['raw_value'], 'steadman', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $show_heat = $show_heat && $this->is_valid_heat_index($temp_ref, $hum_ref, $dew_ref);
                            $show_humidex = $show_humidex && $this->is_valid_humidex($temp_ref, $hum_ref, $dew_ref);
                            $show_steadman = $show_steadman && $this->is_valid_steadman($temp_ref, $hum_ref);
                            $show_summer_simmer = $show_summer_simmer && $this->is_valid_summer_simmer($temp_ref, $hum_ref);
                        } else {
                            $show_heat = false;
                            $show_humidex = false;
                            $show_steadman = false;
                            $show_summer_simmer = false;
                        }
                        // Wind chill
                        if (array_key_exists('temperature_ref', $module['measurements']) &&
                            array_key_exists('wind_chill', $module['measurements'])
                        ) {
                            $temp_ref = $module['measurements']['temperature_ref']['raw_value'];
                            $measurements['windchill'] = array();
                            $measurements['windchill']['value'] = $module['measurements']['wind_chill']['value'];
                            $measurements['windchill']['icon'] = $this->output_iconic_value($module['measurements']['wind_chill']['raw_value'], 'wind_chill', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $show_windchill = $show_windchill && $this->is_valid_wind_chill($temp_ref, $measurements['windchill']['value']);
                        } else {
                            $show_windchill = false;
                        }
                        // Cloud ceiling
                        if (array_key_exists('cloud_ceiling', $module['measurements'])) {
                            $measurements['cloudceiling'] = array();
                            $measurements['cloudceiling']['value'] = $module['measurements']['cloud_ceiling']['value'];
                            $measurements['cloudceiling']['unit'] = $module['measurements']['cloud_ceiling']['unit']['unit'];
                            $measurements['cloudceiling']['icon'] = $this->output_iconic_value($module['measurements']['cloud_ceiling']['raw_value'], 'cloud_ceiling', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        } else {
                            $show_cloud_ceiling = false;
                        }
                        break;
                }
            }
            $timestamp = self::get_date_from_utc($modules['timestamp'], $tz).', '.self::get_time_from_utc($modules['timestamp'], $tz);
        }
        $has_current = (count($current) > 0);
        if (!$NAMain && $has_current) {
            $NAMain = true;
            if (array_key_exists('pressure', $current['measurements'])) {
                $measurements['pressure_sl'] = array();
                $measurements['pressure_sl']['value'] = $current['measurements']['pressure_sl']['value'];
                $measurements['pressure_sl']['unit'] = $current['measurements']['pressure_sl']['unit']['unit'];
                $measurements['pressure_sl']['icon'] = $this->output_iconic_value($current['measurements']['pressure_sl']['raw_value'], 'pressure_sl', null, true, 'inherit', 'lws-widget-icon-' . $id);
            } else {
                $show_pressure = false;
            }
            if (array_key_exists('loc_latitude', $current['measurements']) && array_key_exists('loc_longitude', $current['measurements']) && array_key_exists('loc_altitude', $current['measurements'])) {
                $location = $this->output_coordinate($current['measurements']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                    $this->output_coordinate($current['measurements']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                    $this->output_value($current['measurements']['loc_altitude']['value'], 'loc_altitude', true) . ')';
            }
        }
        if (!$NAModule1 && $has_current) {
            $NAModule1 = true;
            if (array_key_exists('humidity', $current['measurements'])) {
                $measurements['humidity'] = array();
                $measurements['humidity']['value'] = $current['measurements']['humidity']['value'];
                $measurements['humidity']['unit'] = $current['measurements']['humidity']['unit']['unit'];
                $measurements['humidity']['icon'] = $this->output_iconic_value($current['measurements']['humidity']['raw_value'], 'humidity', null, true, 'inherit', 'lws-widget-icon-' . $id);
            } else {
                $show_humidity = false;
            }
            if (array_key_exists('temperature', $current['measurements'])) {
                $measurements['temperature'] = array();
                $temp = $current['measurements']['temperature']['raw_value'];
                $measurements['temperature']['value'] = $current['measurements']['temperature']['value'];
                $measurements['temperature']['unit'] = $current['measurements']['temperature']['unit']['unit'];
                $measurements['temperature']['icon'] = $this->output_iconic_value($current['measurements']['temperature']['raw_value'], 'temperature', null, true, 'inherit', 'lws-widget-icon-' . $id);
            } else {
                $show_temperature = false;
            }
        }
        if (!$NAModule2 && $has_current) {
            $NAModule2 = true;
            if (array_key_exists('windangle', $current['measurements']) && array_key_exists('windstrength', $current['measurements'])) {
                $measurements['windangle'] = array();
                $measurements['windangle']['value'] = $current['measurements']['windangle']['value'];
                $measurements['windangle']['from'] = $this->get_angle_full_text($current['measurements']['windangle']['value']);
                $measurements['windangle']['icon'] = $this->output_iconic_value($current['measurements']['windangle']['raw_value'], 'windangle', null, true, 'inherit', 'lws-widget-icon-' . $id);
                $measurements['windstrength'] = array();
                $measurements['windstrength']['value'] = $current['measurements']['windstrength']['value'];
                $measurements['windstrength']['unit'] = $current['measurements']['windstrength']['unit']['unit'];
            } else {
                $show_wind = false;
            }
        }
        if (!$NAModule3 && $has_current) {
            $NAModule3 = true;
            if (array_key_exists('rain', $current['measurements'])) {
                $measurements['rain'] = array();
                $measurements['rain']['value'] = $current['measurements']['rain']['value'];
                $measurements['rain']['unit'] = $current['measurements']['rain']['unit']['unit'];
                $measurements['rain']['icon'] = $this->output_iconic_value($current['measurements']['rain']['raw_value'], 'rain', null, true, 'inherit', 'lws-widget-icon-' . $id);
            } else {
                $show_rain = false;
            }
        }
        if (!$NAMain) {
            $show_pressure = false ;
        }
        if (!$NAModule1) {
            $show_temperature = false ;
            $show_humidity = false ;
        }
        if (!$NAModule2) {
            $show_wind = false ;
        }
        if (!$NAModule3) {
            $show_rain = false ;
        }
        if (!$NAModule5) {
            $show_uv = false ;
        }
        if (!$NAModule7) {
            $show_strike = false ;
        }
        if (!$NACurrent) {
            $show_current = false ;
            $show_cloud_cover = false ;
            $show_snow = false ;
        }
        if (!$NAComputed) {
            $show_snow = false ;
            $show_dew = false ;
            $show_frost = false ;
            $show_heat = false ;
            $show_humidex = false ;
            $show_steadman = false ;
            $show_summer_simmer = false ;
            $show_windchill = false ;
            $show_cloud_ceiling = false ;
        }
        if (array_key_exists('temperature', $measurements)) {
            $show_snow = ($show_snow && $this->is_valid_snow($temp));
            $show_rain = ($show_rain && $this->is_valid_rain($temp));
        }
        else {
            //
        }
        if ($show_current) {
            $show_current = ($measurements['weather']['value'] != 0);
        }
        if (get_option('live_weather_station_wind_semantics') == 0) {
            $windsemantic = 'towards';
        }
        else {
            $windsemantic = 'from';
        }
        if ($isnight = $this->is_it_night($sunrise_a, $sunset_a)) {
            $dawndusk = 0;
            if ($night_url != '') {
                $bg_url = 'background-image: url("' . $night_url . '");';
            }
        }
        if ($isdawn = $this->is_it_dawn($sunrise, $sunrise_a, $sunset_a)) {
            if ($dawn_url != '') {
                $bg_url = 'background-image: url("' . $dawn_url . '");';
            }
            $dawndusk = $this->dawn_percentage($sunrise, $sunrise_a);
        }
        if ($isdusk = $this->is_it_dusk($sunset, $sunrise_a, $sunset_a)) {
            if ($dusk_url != '') {
                $bg_url = 'background-image: url("' . $dusk_url . '");';
            }
            $dawndusk = 100 - $this->dusk_percentage($sunset, $sunset_a);
        }
        if ($isday = (!$isnight && !$isdawn && !$isdusk)) {
            $dawndusk = 100;
            if ($day_url != '') {
                $bg_url = 'background-image: url("' . $day_url . '");';
            }
        }
        if (!$follow_light) {
            $dawndusk = 100;
        }
        $result = $args['before_widget'];
        $result .= $this->css($instance, $id, $flat, $dawndusk, $bg_url, $background_attachment);
        ob_start();
        include LWS_PUBLIC_DIR.'partials/WidgetOutdoorDisplay.php';
        $result .= ob_get_clean();
        $result .= $args['after_widget'];
        return $result;
    }
}


