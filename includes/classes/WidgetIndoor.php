<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Indoor weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Indoor extends \WP_Widget {

    use Output, ID;

    /**
     * Register the widget.
     *
     * @since 3.1.0
     */
    public static function widget_registering() {
        register_widget('\WeatherStation\UI\Widget\Indoor');
    }

    /**
     * Initialize the widget.
     *
     * @since 3.1.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Indoor',
            '<>🛏 ' . __( 'Indoor comfort' , 'live-weather-station'),
            array('description' => sprintf(__('Display indoor comfort for a module of a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
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
                'module' => 'N/A',
                'bg_color' => '#444444',
                'bg_opacity' => 0,
                'width' => 300,
                'txt_color' => '#ffffff',
                'show_tooltip' => false,
                'show_borders' => false,
                'show_status' => false,
                'hide_obsolete' => false,
                'show_current' => false,
                'show_temperature' => false,
                'show_humidity' => false,
                'show_co2' => false,
                'show_noise' => false,
                'follow_quality' => false,
                'fixed_background' => false,
                'good_url' => '',
                'medium_url' => '',
                'bad_url' => '',
                'flat_design' => false));
        $result['show_status'] = !empty($result['show_status']) ? 1 : 0;
        $result['show_tooltip'] = !empty($result['show_tooltip']) ? 1 : 0;
        $result['show_borders'] = !empty($result['show_borders']) ? 1 : 0;
        $result['hide_obsolete'] = !empty($result['hide_obsolete']) ? 1 : 0;
        $result['show_current'] = !empty($result['show_current']) ? 1 : 0;
        $result['show_temperature'] = !empty($result['show_temperature']) ? 1 : 0;
        $result['show_humidity'] = !empty($result['show_humidity']) ? 1 : 0;
        $result['show_co2'] = !empty($result['show_co2']) ? 1 : 0;
        $result['show_noise'] = !empty($result['show_noise']) ? 1 : 0;
        $result['flat_design'] = !empty($result['flat_design']) ? 1 : 0;
        $result['follow_quality'] = !empty($result['follow_quality']) ? 1 : 0;
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
        $module = $instance['module'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_status = (bool)$instance['show_status'];
        $show_borders = (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_co2 = (bool)$instance['show_co2'] ;
        $show_noise = (bool)$instance['show_noise'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_quality = (bool)$instance['follow_quality'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $good_url = $instance['good_url'];
        $medium_url = $instance['medium_url'];
        $bad_url = $instance['bad_url'];
        $modules = $this->get_operational_indoor_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetIndoorSettings.php');
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
        $instance['module'] = $new_instance['module'];
        $instance['bg_color'] = $new_instance['bg_color'];
        $instance['bg_opacity'] = $new_instance['bg_opacity'];
        $instance['width'] = $new_instance['width'];
        $instance['txt_color'] = $new_instance['txt_color'];
        $instance['show_tooltip'] = !empty($new_instance['show_tooltip']) ? 1 : 0;
        $instance['show_status'] = !empty($new_instance['show_status']) ? 1 : 0;
        $instance['show_borders'] = !empty($new_instance['show_borders']) ? 1 : 0;
        $instance['hide_obsolete'] = !empty($new_instance['hide_obsolete']) ? 1 : 0;
        $instance['show_current'] = !empty($new_instance['show_current']) ? 1 : 0;
        $instance['show_temperature'] = !empty($new_instance['show_temperature']) ? 1 : 0;
        $instance['show_humidity'] = !empty($new_instance['show_humidity']) ? 1 : 0;
        $instance['show_co2'] = !empty($new_instance['show_co2']) ? 1 : 0;
        $instance['show_noise'] = !empty($new_instance['show_noise']) ? 1 : 0;
        $instance['flat_design'] = !empty($new_instance['flat_design']) ? 1 : 0;
        $instance['follow_quality'] = !empty($new_instance['follow_quality']) ? 1 : 0;
        $instance['fixed_background'] = !empty($new_instance['fixed_background']) ? 1 : 0;
        $instance['good_url'] = $new_instance['good_url'];
        $instance['medium_url'] = $new_instance['medium_url'];
        $instance['bad_url'] = $new_instance['bad_url'];
        return $instance;
    }

    /**
     * Set the (inline) css for the widget rendering.
     *
     * @param array $instance An array containing settings for the widget.
     * @param string $uid Identifier of the widget.
     * @param boolean $flat_design Enabling flat design mode.
     * @param integer $health_idx Health index from 0%(poor) to 100%(good).
     * @param string $background Optional. CSS for background Image URL.
     * @param string $attachment Optional. CSS for background-attachment.
     * @since 3.1.0
     */
    public function css($instance, $uid, $flat_design, $health_idx, $background='', $attachment='local') {
        $health_idx_color = '#1DADEA';
        if ($health_idx < 80) {
            $health_idx_color = '#7CBE4D';
        }
        if ($health_idx < 60) {
            $health_idx_color = '#EFE032';
        }
        if ($health_idx < 40) {
            $health_idx_color = '#F69738';
        }
        if ($health_idx < 20) {
            $health_idx_color = '#EB302E';
        }
        try {
            $maxwidth = round ($instance['width']);

        }
        catch(\Exception $ex) {
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
        if (($health_idx < 40) && $instance['follow_quality']){
            $l = $color->getHsl()['L'];
            $c = new Color($color->mix('342500', -((100-$health_idx)/2)));
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
        include(LWS_PUBLIC_DIR.'partials/WidgetIndoorDisplayCSS.php');
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
        wp_enqueue_style('lws-font-awesome');
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $show_title = !($title=='');
        $subtitle = $instance['subtitle'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_status = (bool)$instance['show_status'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_temperature = (bool)$instance['show_temperature'] ;
        $show_humidity = (bool)$instance['show_humidity'] ;
        $show_co2 = (bool)$instance['show_co2'] ;
        $show_noise = (bool)$instance['show_noise'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_quality = (bool)$instance['follow_quality'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $background_attachment = 'local';
        if ($fixed_background) {
            $background_attachment = 'fixed';
        }
        $good_url = $instance['good_url'];
        $medium_url = $instance['medium_url'];
        $bad_url = $instance['bad_url'];
        $bg_url = '';
        $health_idx = 100;
        $temp_multipart = false;
        $NAMain = false;
        $NAModule1 = false;
        $NAModule4 = false;
        $NACurrent = false;
        $modules = $this->get_widget_data($instance['module'], 'indoor', true);
        $timestamp = '';
        $tz = '';
        $datas = array();
        $factor = 111111;
        $factor_text = '';
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                    case 'NAModule4':
                    case 'NAModule9':
                        if (array_key_exists('loc_timezone', $module['datas'])) {
                            $tz = $module['datas']['loc_timezone']['value'];
                        }
                        if (array_key_exists('health_idx', $module['datas'])) {
                            $health_idx = $module['datas']['health_idx']['value'];
                        }
                        else {
                            $show_current = false;
                        }
                        if (array_key_exists('humidity', $module['datas'])) {
                            $datas['humidity'] = array();
                            $datas['humidity']['value'] = $module['datas']['humidity']['value'];
                            $datas['humidity']['unit'] = $module['datas']['humidity']['unit']['unit'];
                        }
                        else {
                            $show_humidity = false;
                        }
                        if (array_key_exists('co2', $module['datas'])) {
                            $datas['co2'] = array();
                            $datas['co2']['value'] = $module['datas']['co2']['value'];
                            $datas['co2']['unit'] = $module['datas']['co2']['unit']['unit'];
                        }
                        else {
                            $show_co2 = false;
                        }
                        if (array_key_exists('noise', $module['datas'])) {
                            $datas['noise'] = array();
                            $datas['noise']['value'] = $module['datas']['noise']['value'];
                            $datas['noise']['unit'] = $module['datas']['noise']['unit']['unit'];
                        }
                        else {
                            $show_noise = false;
                        }
                        if (array_key_exists('temperature', $module['datas'])) {
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
                        if (array_key_exists('hi_temperature', $module['datas'])) {
                            if ($module['datas']['hi_temperature']['value'] < $factor) {
                                $factor = $module['datas']['hi_temperature']['value'];
                                $factor_text = strtolower($this->get_measurement_type('temperature'));
                            }
                        }
                        if (array_key_exists('hi_humidity', $module['datas'])) {
                            if ($module['datas']['hi_humidity']['value'] < $factor) {
                                $factor = $module['datas']['hi_humidity']['value'];
                                $factor_text = strtolower($this->get_measurement_type('humidity'));
                            }
                        }
                        if (array_key_exists('hi_noise', $module['datas'])) {
                            if ($module['datas']['hi_noise']['value'] < $factor) {
                                $factor = $module['datas']['hi_noise']['value'];
                                $factor_text = strtolower($this->get_measurement_type('noise'));
                            }
                        }
                        if (array_key_exists('hi_dew', $module['datas'])) {
                            if ($module['datas']['hi_dew']['value'] < $factor) {
                                $factor = $module['datas']['hi_dew']['value'];
                                $factor_text = strtolower($this->get_measurement_type('dew_point'));
                            }
                        }
                        if (array_key_exists('hi_humidex', $module['datas'])) {
                            if ($module['datas']['hi_humidex']['value'] < $factor) {
                                $factor = $module['datas']['hi_humidex']['value'];
                                $factor_text = strtolower($this->get_measurement_type('humidex'));
                            }
                        }
                        if (array_key_exists('hi_co2', $module['datas'])) {
                            if ($module['datas']['hi_co2']['value'] < $factor) {
                                $factor = $module['datas']['hi_co2']['value'];
                                $factor_text = $this->get_measurement_type('co2');
                            }
                        }
                        break;
                }
            }
            $timestamp = self::get_date_from_utc($modules['timestamp'], $tz).', '.self::get_time_from_utc($modules['timestamp'], $tz);
        }
        if ($health_idx < 34) {
            if ($bad_url != '') {
                $bg_url = 'background-image: url("' . $bad_url . '");';
            }
        }
        elseif ($health_idx < 67) {
            if ($medium_url != '') {
                $bg_url = 'background-image: url("' . $medium_url . '");';
            }
        }
        else {
            if ($good_url != '') {
                $bg_url = 'background-image: url("' . $good_url . '");';
            }
        }
        $status = __('Comfort: ', 'live-weather-station') . strtolower($this->get_health_index_text($health_idx));
        if (($factor_text != '') && ($health_idx < 40)) {
            $status .= ' (' . $factor_text . ')';
        }
        echo $args['before_widget'];
        $id = uniqid();
        $this->css($instance, $id, $flat_design, $health_idx, $bg_url, $background_attachment);
        include(LWS_PUBLIC_DIR.'partials/WidgetIndoorDisplay.php');
        echo $args['after_widget'];
    }
}
