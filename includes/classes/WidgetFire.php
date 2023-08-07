<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Fire weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Fire extends Base {

    use Output, ID;

    /**
     * Register the widget.
     *
     * @since 3.1.0
     */
    public static function widget_registering() {
        register_widget('\WeatherStation\UI\Widget\Fire');
    }

    /**
     * Initialize the widget.
     *
     * @since 3.1.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Fire',
            '<>🔥 ' . __( 'Fire weather' , 'live-weather-station'),
            array( 'description' => sprintf(__('Display fire weather of a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
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
     * @param array $instance An array containing settings for the widget.
     * @return array An array containing settings with defaults for the widget.
     * @since 3.1.0
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
                'show_cbi' => false,
                'show_temperature' => false,
                'show_humidity' => false,
                'show_rain' => false,
                'show_wind' => false,
                'follow_risk' => false,
                'fixed_background' => false,
                'low_url' => '',
                'moderate_url' => '',
                'high_url' => '',
                'very_high_url' => '',
                'extreme_url' => '',
                'flat_design' => false));
        $result['show_tooltip'] = !empty($result['show_tooltip']) ? 1 : 0;
        $result['show_borders'] = !empty($result['show_borders']) ? 1 : 0;
        $result['hide_obsolete'] = !empty($result['hide_obsolete']) ? 1 : 0;
        $result['show_current'] = !empty($result['show_current']) ? 1 : 0;
        $result['show_cbi'] = !empty($result['show_cbi']) ? 1 : 0;
        $result['show_temperature'] = !empty($result['show_temperature']) ? 1 : 0;
        $result['show_humidity'] = !empty($result['show_humidity']) ? 1 : 0;
        $result['show_rain'] = !empty($result['show_rain']) ? 1 : 0;
        $result['show_wind'] = !empty($result['show_wind']) ? 1 : 0;
        $result['flat_design'] = !empty($result['flat_design']) ? 1 : 0;
        $result['follow_risk'] = !empty($result['follow_risk']) ? 1 : 0;
        $result['fixed_background'] = !empty($result['fixed_background']) ? 1 : 0;
        return $result;
    }

    /**
     * Get the settings form.
     *
     * @param array $instance An array containing settings for the widget.
     * @return boolean Nothing.
     * @since 3.1.0
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
        $show_cbi = (bool)$instance['show_cbi'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_rain = (bool)$instance['show_rain'] ;
        $show_wind = (bool)$instance['show_wind'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_risk = (bool)$instance['follow_risk'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $low_url = $instance['low_url'];
        $moderate_url = $instance['moderate_url'];
        $high_url = $instance['high_url'];
        $very_high_url = $instance['very_high_url'];
        $extreme_url = $instance['extreme_url'];
        $stations = $this->get_operational_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetFireSettings.php');
    }


    /**
     * Update settings of the widget.
     *
     * @param array $new_instance An array containing the new settings for the widget.
     * @param array $old_instance An array containing the old settings for the widget.
     * @return array An array containing the validated settings for the widget, ready to store.
     * @since 3.1.0
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
        $instance['show_cbi'] = !empty($new_instance['show_cbi']) ? 1 : 0;
        $instance['show_humidity'] = !empty($new_instance['show_humidity']) ? 1 : 0;
        $instance['show_rain'] = !empty($new_instance['show_rain']) ? 1 : 0;
        $instance['show_wind'] = !empty($new_instance['show_wind']) ? 1 : 0;
        $instance['flat_design'] = !empty($new_instance['flat_design']) ? 1 : 0;
        $instance['follow_risk'] = !empty($new_instance['follow_risk']) ? 1 : 0;
        $instance['fixed_background'] = !empty($new_instance['fixed_background']) ? 1 : 0;
        $instance['low_url'] = $new_instance['low_url'];
        $instance['moderate_url'] = $new_instance['moderate_url'];
        $instance['high_url'] = $new_instance['high_url'];
        $instance['very_high_url'] = $new_instance['very_high_url'];
        $instance['extreme_url'] = $new_instance['extreme_url'];
        return $instance;
    }

    /**
     * Set the (inline) css for the widget rendering.
     *
     * @param array $instance An array containing settings for the widget.
     * @param string $uid Identifiant of the widget.
     * @param boolean $flat_design Enabling flat design mode.
     * @param integer $cbi Chandler Burning index.
     * @param string $background Optional. CSS for background Image URL.
     * @param string $attachment Optional. CSS for background-attachment.
     * @since 3.1.0
     */
    public function css($instance, $uid, $flat_design, $cbi=-99999, $background='', $attachment) {
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
        $color = new Color($bg_color);

        if (($cbi > 90) && $instance['follow_risk']){
            $l = $color->getHsl()['L'];
            $c = new Color($color->mix('FF0000', -100));
            $hsl = $c->getHsl();
            $hsl['L'] = $l-0.05;
            $color = new Color(Color::hslToHex($hsl));
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
        $wtype = 'fire';
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
        $show_title = !($title=='');
        $subtitle = $instance['subtitle'];
        $station = $instance['station'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $show_cbi = (bool)$instance['show_cbi'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_rain = (bool)$instance['show_rain'] ;
        $show_wind = (bool)$instance['show_wind'] ;
        $flat = (bool)$instance['flat_design'] ;
        $shadows = !$flat;
        $follow_risk = (bool)$instance['follow_risk'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $background_attachment = 'local';
        if ($fixed_background) {
            $background_attachment = 'fixed';
        }
        $low_url = $this->get_picture_url($instance['station'], $instance['low_url']);
        $moderate_url = $this->get_picture_url($instance['station'], $instance['moderate_url']);
        $high_url = $this->get_picture_url($instance['station'], $instance['high_url']);
        $very_high_url = $this->get_picture_url($instance['station'], $instance['very_high_url']);
        $extreme_url = $this->get_picture_url($instance['station'], $instance['extreme_url']);
        $bg_url = '';
        $cbi = -99999;
        $rain_multipart = false;
        $wind_multipart = false;
        $temp_multipart = false;
        $NAMain = false;
        $NAModule1 = false;
        $NAModule2 = false;
        $NAModule3 = false;
        $NACurrent = false;
        $NAComputed = false;
        $modules = $this->get_widget_data($instance['station'], 'outdoor', $hide_obsolete);
        $timestamp = '';
        $tz = '';
        $location = '';
        $measurements = array();
        $current = array();
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('loc_latitude', $module['measurements']) && array_key_exists('loc_longitude', $module['measurements']) && array_key_exists('loc_altitude', $module['measurements'])) {
                            $location = $this->output_coordinate($module['measurements']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                                $this->output_coordinate($module['measurements']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                                $this->output_value($module['measurements']['loc_altitude']['value'], 'loc_altitude', true) . ')';
                        }
                        if (array_key_exists('loc_timezone', $module['measurements'])) {
                            $tz = $module['measurements']['loc_timezone']['value'];
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
                            $measurements['windstrength'] = array();
                            $measurements['windstrength']['value'] = $module['measurements']['windstrength']['value'];
                            $measurements['windstrength']['unit'] = $module['measurements']['windstrength']['unit']['unit'];
                            $measurements['windangle']['icon'] = $this->output_iconic_value($module['measurements']['windangle']['raw_value'], 'windangle', null, true, 'inherit', 'lws-widget-icon-' . $id);
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
                    case 'NACurrent': // Current weather -> OpenWeatherMap
                        $NACurrent = true;
                        $current = $module;
                        break;
                    case 'NAComputed': // Computed values
                        $NAComputed = true;
                        if (array_key_exists('cbi', $module['measurements'])) {
                            $measurements['cbi'] = array();
                            $measurements['cbi']['value'] = $module['measurements']['cbi']['value'];
                            $cbi = $module['measurements']['cbi']['value'];
                            $measurements['cbi']['unit'] = $this->get_cbi_text($cbi);
                            $measurements['header_cbi']['icon'] = $this->output_iconic_value($module['measurements']['cbi']['raw_value'], 'cbi', null, true, $this->get_cbi_color($cbi), 'lws-widget-big-icon-' . $id);
                            $measurements['cbi']['icon'] = $this->output_iconic_value($module['measurements']['cbi']['raw_value'], 'cbi', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        } else {
                            $show_cbi = false;
                        }
                        break;
                }
            }
            $timestamp = self::get_date_from_utc($modules['timestamp'], $tz).', '.self::get_time_from_utc($modules['timestamp'], $tz);
        }
        $has_current = (count($current) > 0);
        if (!$NAMain && $has_current) {
            $NAMain = true;
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
                $measurements['windstrength'] = array();
                $measurements['windstrength']['value'] = $current['measurements']['windstrength']['value'];
                $measurements['windstrength']['unit'] = $current['measurements']['windstrength']['unit']['unit'];
                $measurements['windangle']['icon'] = $this->output_iconic_value($current['measurements']['windangle']['raw_value'], 'windangle', null, true, 'inherit', 'lws-widget-icon-' . $id);
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
        if (!$NAComputed || ($cbi == -99999)) {
            $show_cbi = false ;
        }
        if (get_option('live_weather_station_wind_semantics') == 0) {
            $windsemantic = 'towards';
        }
        else {
            $windsemantic = 'from';
        }
        if ($extreme_url != '') {
            $bg_url = 'background-image: url("' . $extreme_url . '");';
        }
        if ($cbi <= 97.5) {
            if ($very_high_url != '') {
                $bg_url = 'background-image: url("' . $very_high_url . '");';
            }
        }
        if ($cbi <= 90) {
            if ($high_url != '') {
                $bg_url = 'background-image: url("' . $high_url . '");';
            }
        }
        if ($cbi <= 75) {
            if ($moderate_url != '') {
                $bg_url = 'background-image: url("' . $moderate_url . '");';
            }
        }
        if ($cbi < 50) {
            if ($low_url != '') {
                $bg_url = 'background-image: url("' . $low_url . '");';
            }
        }
        $result = $args['before_widget'];
        $result .= $this->css($instance, $id, $flat, $cbi, $bg_url, $background_attachment);
        ob_start();
        include LWS_PUBLIC_DIR.'partials/WidgetFireDisplay.php';
        $result .= ob_get_clean();
        $result .= $args['after_widget'];
        return $result;
    }
}
