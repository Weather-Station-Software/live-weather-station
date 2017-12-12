<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Fire weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Fire extends \WP_Widget {

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
        $cbi_color = '#EB302E';
        if ($cbi <= 97.5) {
            $cbi_color = '#F69738';
        }
        if ($cbi <= 90) {
            $cbi_color = '#EFE032';
        }
        if ($cbi <= 75) {
            $cbi_color = '#1DADEA';
        }
        if ($cbi < 50) {
            $cbi_color = '#7CBE4D';
        }
        try
        {
            $maxwidth = round ($instance['width']);

        }
        catch(\Exception $ex)
        {
            $maxwidth = 0;
        }
        $txt_color = $instance['txt_color'];
        if ($flat_design) {
            $fact = 80;
        }
        else {
            $fact = 98;
        }
        $color = new Color($instance['bg_color']);

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
        include(LWS_PUBLIC_DIR.'partials/WidgetFireDisplayCSS.php');
    }

    /**
     * Get the widget output.
     *
     * @param array $args An array containing the widget's arguments.
     * @param array $instance An array containing settings for the widget.
     * @since 3.1.0
     */
    public function widget($args, $instance) {
        wp_enqueue_style('lws-weather-icons');
        wp_enqueue_style('lws-weather-icons-wind');
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
        $follow_risk = (bool)$instance['follow_risk'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $background_attachment = 'local';
        if ($fixed_background) {
            $background_attachment = 'fixed';
        }
        $low_url = $instance['low_url'];
        $moderate_url = $instance['moderate_url'];
        $high_url = $instance['high_url'];
        $very_high_url = $instance['very_high_url'];
        $extreme_url = $instance['extreme_url'];
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
        $datas = array();
        $current = array();
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('loc_latitude', $module['datas']) && array_key_exists('loc_longitude', $module['datas']) && array_key_exists('loc_altitude', $module['datas'])) {
                            $location = $this->output_coordinate($module['datas']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                                $this->output_coordinate($module['datas']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                                $this->output_value($module['datas']['loc_altitude']['value'], 'loc_altitude', true) . ')';
                        }
                        if (array_key_exists('loc_timezone', $module['datas'])) {
                            $tz = $module['datas']['loc_timezone']['value'];
                        }
                        break;
                    case 'NAModule1': // Outdoor module
                        if (array_key_exists('humidity', $module['datas'])) {
                            $NAModule1 = true;
                            $datas['humidity'] = array();
                            $datas['humidity']['value'] = $module['datas']['humidity']['value'];
                            $datas['humidity']['unit'] = $module['datas']['humidity']['unit']['unit'];
                        }
                        else {
                            $show_humidity = false;
                        }
                        if (array_key_exists('temperature', $module['datas'])) {
                            $NAModule1 = true;
                            $datas['temperature'] = array();
                            $datas['temperature']['value'] = $module['datas']['temperature']['value'];
                            $datas['temperature']['unit'] = $module['datas']['temperature']['unit']['unit'];
                            if (array_key_exists('temperature_max', $module['datas']) && array_key_exists('temperature_min', $module['datas'])) {
                                $datas['temperature_max'] = array();
                                $datas['temperature_max']['value'] = $module['datas']['temperature_max']['value'];
                                $datas['temperature_max']['unit'] = $module['datas']['temperature_max']['unit']['unit'];
                                $datas['temperature_min'] = array();
                                $datas['temperature_min']['value'] = $module['datas']['temperature_min']['value'];
                                $datas['temperature_min']['unit'] = $module['datas']['temperature_min']['unit']['unit'];
                                $temp_multipart = true;
                            }
                        }
                        else {
                            $show_temperature = false;
                        }
                        break;
                    case 'NAModule3': // Rain gauge
                        //$wug = ID::is_fake_modulex_id($module['id'], 3);
                        if (array_key_exists('rain', $module['datas'])) {
                            $NAModule3 = true;
                            $datas['rain'] = array();
                            $datas['rain']['value'] = $module['datas']['rain']['value'];
                            $datas['rain']['unit'] = $module['datas']['rain']['unit']['unit'];
                            if (array_key_exists('rain_day_aggregated', $module['datas'])) {
                                $datas['rain_day_aggregated'] = array();
                                $datas['rain_day_aggregated']['value'] = $module['datas']['rain_day_aggregated']['value'];
                                $datas['rain_day_aggregated']['unit'] = $module['datas']['rain_day_aggregated']['unit']['unit'];
                                $rain_multipart = true;
                            }
                        }
                        elseif (array_key_exists('rain_day_aggregated', $module['datas'])) {
                            $NAModule3 = true;
                            $datas['rain'] = array();
                            $datas['rain']['value'] = $module['datas']['rain_day_aggregated']['value'];
                            $datas['rain']['unit'] = $module['datas']['rain_day_aggregated']['unit']['unit'];
                        }
                        else {
                            $show_rain = false;
                        }
                        break;
                    case 'NAModule2': // Wind gauge
                        if (array_key_exists('windangle', $module['datas']) && array_key_exists('windstrength', $module['datas'])) {
                            $NAModule2 = true;
                            $datas['windangle'] = array();
                            $datas['windangle']['value'] = $module['datas']['windangle']['value'];
                            $datas['windangle']['from'] = $this->get_angle_full_text($module['datas']['windangle']['value']);
                            $datas['windstrength'] = array();
                            $datas['windstrength']['value'] = $module['datas']['windstrength']['value'];
                            $datas['windstrength']['unit'] = $module['datas']['windstrength']['unit']['unit'];
                            if (array_key_exists('windstrength_day_max', $module['datas'])) {
                                $datas['windstrength_max'] = array();
                                $datas['windstrength_max']['value'] = $module['datas']['windstrength_day_max']['value'];
                                $datas['windstrength_max']['unit'] = $module['datas']['windstrength_day_max']['unit']['unit'];
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
                        if (array_key_exists('cbi', $module['datas'])) {
                            $datas['cbi'] = array();
                            $datas['cbi']['value'] = $module['datas']['cbi']['value'];
                            $cbi = $module['datas']['cbi']['value'];
                            $datas['cbi']['unit'] = $this->get_cbi_text($cbi);
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
            if (array_key_exists('loc_latitude', $current['datas']) && array_key_exists('loc_longitude', $current['datas']) && array_key_exists('loc_altitude', $current['datas'])) {
                $location = $this->output_coordinate($current['datas']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                    $this->output_coordinate($current['datas']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                    $this->output_value($current['datas']['loc_altitude']['value'], 'loc_altitude', true) . ')';
            }
        }
        if (!$NAModule1 && $has_current) {
            $NAModule1 = true;
            if (array_key_exists('humidity', $current['datas'])) {
                $datas['humidity'] = array();
                $datas['humidity']['value'] = $current['datas']['humidity']['value'];
                $datas['humidity']['unit'] = $current['datas']['humidity']['unit']['unit'];
            } else {
                $show_humidity = false;
            }
            if (array_key_exists('temperature', $current['datas'])) {
                $datas['temperature'] = array();
                $datas['temperature']['value'] = $current['datas']['temperature']['value'];
                $datas['temperature']['unit'] = $current['datas']['temperature']['unit']['unit'];
            } else {
                $show_temperature = false;
            }
        }
        if (!$NAModule2 && $has_current) {
            $NAModule2 = true;
            if (array_key_exists('windangle', $current['datas']) && array_key_exists('windstrength', $current['datas'])) {
                $datas['windangle'] = array();
                $datas['windangle']['value'] = $current['datas']['windangle']['value'];
                $datas['windangle']['from'] = $this->get_angle_full_text($current['datas']['windangle']['value']);
                $datas['windstrength'] = array();
                $datas['windstrength']['value'] = $current['datas']['windstrength']['value'];
                $datas['windstrength']['unit'] = $current['datas']['windstrength']['unit']['unit'];
            } else {
                $show_wind = false;
            }
        }
        if (!$NAModule3 && $has_current) {
            $NAModule3 = true;
            if (array_key_exists('rain', $current['datas'])) {
                $datas['rain'] = array();
                $datas['rain']['value'] = $current['datas']['rain']['value'];
                $datas['rain']['unit'] = $current['datas']['rain']['unit']['unit'];
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
        echo $args['before_widget'];
        $id = uniqid();
        $this->css($instance, $id, $flat, $cbi, $bg_url, $background_attachment);
        include(LWS_PUBLIC_DIR.'partials/WidgetFireDisplay.php');
        echo $args['after_widget'];
    }
}
