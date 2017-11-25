<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Psychrometric weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
class Psychrometry extends \WP_Widget {

    use Output, ID;

    /**
     * Register the widget.
     *
     * @since 3.3.0
     * @static
     */
    public static function widget_registering() {
        register_widget('\WeatherStation\UI\Widget\Psychrometry');
    }

    /**
     * Initialize the widget.
     *
     * @since 3.3.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Psychrometry',
            '<>🌡 ' .__( 'Psychrometry' , 'live-weather-station'),
            array( 'description' => sprintf(__('Display psychrometric values of a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
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
     * @since 3.3.0
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
                'show_temperature' => false,
                'show_pressure' => false,
                'show_humidity' => false,
                'show_dew' => false,
                'show_absolute_humidity' => false,
                'show_vapor_pressure' => false,
                'show_wet_bulb' => false,
                'show_air_density' => false,
                'show_emc' => false,
                'show_enthalpy' => false,
                'show_location' => false,
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
        $result['show_temperature'] = !empty($result['show_temperature']) ? 1 : 0;
        $result['show_pressure'] = !empty($result['show_pressure']) ? 1 : 0;
        $result['show_humidity'] = !empty($result['show_humidity']) ? 1 : 0;
        $result['show_dew'] = !empty($result['show_dew']) ? 1 : 0;
        $result['show_absolute_humidity'] = !empty($result['show_absolute_humidity']) ? 1 : 0;
        $result['show_vapor_pressure'] = !empty($result['show_vapor_pressure']) ? 1 : 0;
        $result['show_wet_bulb'] = !empty($result['show_wet_bulb']) ? 1 : 0;
        $result['show_air_density'] = !empty($result['show_air_density']) ? 1 : 0;
        $result['show_emc'] = !empty($result['show_emc']) ? 1 : 0;
        $result['show_enthalpy'] = !empty($result['show_enthalpy']) ? 1 : 0;
        $result['show_location'] = !empty($result['show_location']) ? 1 : 0;
        $result['flat_design'] = !empty($result['flat_design']) ? 1 : 0;
        $result['follow_light'] = !empty($result['follow_light']) ? 1 : 0;
        $result['fixed_background'] = !empty($result['fixed_background']) ? 1 : 0;
        return $result;
    }

    /**
     * Get the settings form.
     *
     * @param array $instance An array containing settings for the widget.
     * @return boolean Nothing.
     * @since 3.3.0
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
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_pressure = (bool)$instance['show_pressure'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_dew = (bool)$instance['show_dew'] ;
        $show_absolute_humidity = (bool)$instance['show_absolute_humidity'] ;
        $show_vapor_pressure = (bool)$instance['show_vapor_pressure'] ;
        $show_wet_bulb = (bool)$instance['show_wet_bulb'] ;
        $show_air_density = (bool)$instance['show_air_density'] ;
        $show_emc = (bool)$instance['show_emc'] ;
        $show_enthalpy = (bool)$instance['show_enthalpy'] ;
        $show_location = (bool)$instance['show_location'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_light = (bool)$instance['follow_light'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $day_url = $instance['day_url'];
        $night_url = $instance['night_url'];
        $dawn_url = $instance['dawn_url'];
        $dusk_url = $instance['dusk_url'];
        $stations = $this->get_operational_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetPsychrometrySettings.php');
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
     * @since 3.3.0
     */
    public function css($instance, $uid, $flat_design, $dawndusk=100, $background='', $attachment) {
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
        $c = new Color($instance['bg_color']);
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
        include(LWS_PUBLIC_DIR.'partials/WidgetPsychrometryDisplayCSS.php');
    }

    /**
     * Update settings of the widget.
     *
     * @param array $new_instance An array containing the new settings for the widget.
     * @param array $old_instance An array containing the old settings for the widget.
     * @return array An array containing the validated settings for the widget, ready to store.
     * @since 3.3.0
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
        $instance['show_temperature'] = !empty($new_instance['show_temperature']) ? 1 : 0;
        $instance['show_pressure'] = !empty($new_instance['show_pressure']) ? 1 : 0;
        $instance['show_humidity'] = !empty($new_instance['show_humidity']) ? 1 : 0;
        $instance['show_dew'] = !empty($new_instance['show_dew']) ? 1 : 0;
        $instance['show_absolute_humidity'] = !empty($new_instance['show_absolute_humidity']) ? 1 : 0;
        $instance['show_vapor_pressure'] = !empty($new_instance['show_vapor_pressure']) ? 1 : 0;
        $instance['show_wet_bulb'] = !empty($new_instance['show_wet_bulb']) ? 1 : 0;
        $instance['show_air_density'] = !empty($new_instance['show_air_density']) ? 1 : 0;
        $instance['show_emc'] = !empty($new_instance['show_emc']) ? 1 : 0;
        $instance['show_enthalpy'] = !empty($new_instance['show_enthalpy']) ? 1 : 0;
        $instance['show_location'] = !empty($new_instance['show_location']) ? 1 : 0;
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
     * @since 3.3.0
     */
    public function widget($args, $instance) {
        wp_enqueue_style('lws-weather-icons');
        wp_enqueue_style('lws-weather-icons-wind');
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $subtitle = $instance['subtitle'];
        $show_title = !($title=='');
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders =  (bool)$instance['show_borders'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_pressure = (bool)$instance['show_pressure'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_dew = (bool)$instance['show_dew'] ;
        $show_vapor_pressure = (bool)$instance['show_vapor_pressure'] ;
        $show_absolute_humidity = (bool)$instance['show_absolute_humidity'] ;
        $show_wet_bulb = (bool)$instance['show_wet_bulb'] ;
        $show_air_density = (bool)$instance['show_air_density'] ;
        $show_emc = (bool)$instance['show_emc'] ;
        $show_enthalpy = (bool)$instance['show_enthalpy'] ;
        $show_location = (bool)$instance['show_location'] ;
        $flat = (bool)$instance['flat_design'] ;
        $follow_light = (bool)$instance['follow_light'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $background_attachment = 'local';
        if ($fixed_background) {
            $background_attachment = 'fixed';
        }
        $day_url = $instance['day_url'];
        $night_url = $instance['night_url'];
        $dawn_url = $instance['dawn_url'];
        $dusk_url = $instance['dusk_url'];
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
        $NAMain = false;
        $NAModule1 = false;
        $NACurrent = false;
        $NAComputed = false;
        $modules = $this->get_widget_data($instance['station'], 'psychrometry', $hide_obsolete);
        $timestamp = '';
        $tz = '';
        $location = '';
        $datas = array();
        $current = array();
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('pressure', $module['datas'])){
                            $NAMain = true;
                            $datas['pressure'] = array();
                            $datas['pressure']['value'] = $module['datas']['pressure']['value'];
                            $datas['pressure']['unit'] = $module['datas']['pressure']['unit']['unit'];
                        }
                        else {
                            $show_pressure = false ;
                        }
                        if (array_key_exists('loc_latitude', $module['datas']) && array_key_exists('loc_longitude', $module['datas']) && array_key_exists('loc_altitude', $module['datas'])) {
                            $location = $this->output_coordinate($module['datas']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                                $this->output_coordinate($module['datas']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                                $this->output_value($module['datas']['loc_altitude']['value'], 'loc_altitude', true) . ')';
                        }
                        if (array_key_exists('loc_timezone', $module['datas'])) {
                            $tz = $module['datas']['loc_timezone']['value'];
                        }
                        break;
                    case 'NAEphemer':
                        if (array_key_exists('sunrise', $module['datas']) && array_key_exists('sunset', $module['datas'])) {
                            $sunrise = $module['datas']['sunrise']['value'];
                            $sunset = $module['datas']['sunset']['value'];
                        }
                        if (array_key_exists('sunrise_a', $module['datas']) && array_key_exists('sunset_a', $module['datas'])) {
                            $sunrise_a = $module['datas']['sunrise_a']['value'];
                            $sunset_a = $module['datas']['sunset_a']['value'];
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
                        }
                        else {
                            $show_temperature = false;
                        }
                        break;
                    case 'NACurrent': // Current weather -> OpenWeatherMap
                        $NACurrent = true;
                        if (array_key_exists('is_day', $module['datas'])) {
                            $datas['day']['value'] = ($module['datas']['is_day']['value'] == 1 ? '-day' : '-night');
                        } else {
                            $datas['day']['value'] = '';
                        }
                        $current = $module;
                        break;
                    case 'NAComputed': // Computed values
                        $NAComputed = true;
                        // Dew point
                        if (array_key_exists('temperature_ref', $module['datas']) &&
                            array_key_exists('dew_point', $module['datas']) &&
                            array_key_exists('frost_point', $module['datas'])) {
                            $temp_ref = $module['datas']['temperature_ref']['value'];
                            $datas['dew'] = array();
                            $datas['dew']['value'] = $module['datas']['dew_point']['value'];
                            $datas['dew']['unit'] = $module['datas']['dew_point']['unit']['unit'];
                            $show_dew = $show_dew && $this->is_valid_dew_point($temp_ref);
                        } else {
                            $show_dew = false;
                        }
                        // Equivalent & Potential temperatures
                        if (array_key_exists('equivalent_temperature', $module['datas']) &&
                            array_key_exists('potential_temperature', $module['datas'])) {
                            $datas['equivalent_temperature'] = array();
                            $datas['equivalent_temperature']['value'] = $module['datas']['equivalent_temperature']['value'];
                            $datas['equivalent_temperature']['unit'] = $module['datas']['equivalent_temperature']['unit']['unit'] . ' ' . __('equiv.', 'live-weather-station');
                            $datas['potential_temperature'] = array();
                            $datas['potential_temperature']['value'] = $module['datas']['potential_temperature']['value'];
                            $datas['potential_temperature']['unit'] = $module['datas']['potential_temperature']['unit']['unit'] . ' ' . __('pot.', 'live-weather-station');
                        }
                        // Wet Bulb
                        if (array_key_exists('wet_bulb', $module['datas'])) {
                            $datas['wet_bulb'] = array();
                            $datas['wet_bulb']['value'] = $module['datas']['wet_bulb']['value'];
                            $datas['wet_bulb']['unit'] = $module['datas']['wet_bulb']['unit']['unit'];
                        } else {
                            $show_wet_bulb = false;
                        }
                        // Absolute Humidity
                        if (array_key_exists('partial_absolute_humidity', $module['datas']) &&
                            array_key_exists('saturation_absolute_humidity', $module['datas'])) {
                            $datas['partial_absolute_humidity'] = array();
                            $datas['partial_absolute_humidity']['value'] = $module['datas']['partial_absolute_humidity']['value'];
                            $datas['partial_absolute_humidity']['unit'] = $module['datas']['partial_absolute_humidity']['unit']['unit'];
                            $datas['saturation_absolute_humidity'] = array();
                            $datas['saturation_absolute_humidity']['value'] = $module['datas']['saturation_absolute_humidity']['value'];
                            $datas['saturation_absolute_humidity']['unit'] = $module['datas']['saturation_absolute_humidity']['unit']['unit'];
                        } else {
                            $show_absolute_humidity = false;
                        }
                        // Vapor Pressure
                        if (array_key_exists('partial_vapor_pressure', $module['datas']) &&
                            array_key_exists('saturation_vapor_pressure', $module['datas'])) {
                            $datas['partial_vapor_pressure'] = array();
                            $datas['partial_vapor_pressure']['value'] = $module['datas']['partial_vapor_pressure']['value'];
                            $datas['partial_vapor_pressure']['unit'] = $module['datas']['partial_vapor_pressure']['unit']['unit'];
                            $datas['saturation_vapor_pressure'] = array();
                            $datas['saturation_vapor_pressure']['value'] = $module['datas']['saturation_vapor_pressure']['value'];
                            $datas['saturation_vapor_pressure']['unit'] = $module['datas']['saturation_vapor_pressure']['unit']['unit'];
                        } else {
                            $show_vapor_pressure = false;
                        }
                        // Air Density
                        if (array_key_exists('air_density', $module['datas'])) {
                            $datas['air_density'] = array();
                            $datas['air_density']['value'] = $module['datas']['air_density']['value'];
                            $datas['air_density']['unit'] = $module['datas']['air_density']['unit']['unit'];
                        } else {
                            $show_air_density = false;
                        }
                        // Specific Enthalpy
                        if (array_key_exists('specific_enthalpy', $module['datas'])) {
                            $datas['specific_enthalpy'] = array();
                            $datas['specific_enthalpy']['value'] = $module['datas']['specific_enthalpy']['value'];
                            $datas['specific_enthalpy']['unit'] = $module['datas']['specific_enthalpy']['unit']['unit'];
                        } else {
                            $show_enthalpy = false;
                        }
                        // EMC
                        if (array_key_exists('wood_emc', $module['datas'])) {
                            $datas['wood_emc'] = array();
                            $datas['wood_emc']['value'] = $module['datas']['wood_emc']['value'];
                            $datas['wood_emc']['unit'] = $module['datas']['wood_emc']['unit']['unit'];
                        } else {
                            $show_emc = false;
                        }
                        break;
                }
            }
            $timestamp = self::get_date_from_utc($modules['timestamp'], $tz).', '.self::get_time_from_utc($modules['timestamp'], $tz);
        }
        $has_current = (count($current) > 0);
        if (!$NAMain && $has_current) {
            $NAMain = true;
            if (array_key_exists('pressure', $current['datas'])) {
                $datas['pressure'] = array();
                $datas['pressure']['value'] = $current['datas']['pressure']['value'];
                $datas['pressure']['unit'] = $current['datas']['pressure']['unit']['unit'];
            } else {
                $show_pressure = false;
            }
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
        if (!$NAMain) {
            $show_pressure = false ;
        }
        if (!$NAModule1) {
            $show_temperature = false ;
            $show_humidity = false ;
        }
        if (!$NACurrent) {
            $show_current = false ;
        }
        if (!$NAComputed) {
            $show_snow = false ;
            $show_dew = false ;

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
        if (!$isnight && !$isdawn && !$isdusk) {
            $isday = true;
            $dawndusk = 100;
            if ($day_url != '') {
                $bg_url = 'background-image: url("' . $day_url . '");';
            }
        }
        if (!$follow_light) {
            $dawndusk = 100;
        }
        echo $args['before_widget'];
        $id = uniqid();
        $this->css($instance, $id, $flat, $dawndusk, $bg_url, $background_attachment);
        include(LWS_PUBLIC_DIR.'partials/WidgetPsychrometryDisplay.php');
        echo $args['after_widget'];
    }
}
