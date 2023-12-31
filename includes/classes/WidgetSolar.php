<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Solar weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
class Solar extends Base {

    use Output, ID;

    /**
     * Register the widget.
     *
     * @since 3.3.0
     */
    public static function widget_registering() {
        register_widget('\WeatherStation\UI\Widget\Solar');
    }

    /**
     * Initialize the widget.
     *
     * @since 3.3.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Solar',
            '<>☀ ' . __( 'Solar' , 'live-weather-station'),
            array( 'description' => sprintf(__('Display solar conditions recorded by a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
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
                'show_current' => false,
                'show_irradiance' => false,
                'show_sunshine' => false,
                'show_illuminance' => false,
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
        $result['show_irradiance'] = !empty($result['show_irradiance']) ? 1 : 0;
        $result['show_sunshine'] = !empty($result['show_sunshine']) ? 1 : 0;
        $result['show_illuminance'] = !empty($result['show_illuminance']) ? 1 : 0;
        $result['show_uv'] = !empty($result['show_uv']) ? 1 : 0;
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
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $show_irradiance = (bool)$instance['show_irradiance'] ;
        $show_sunshine = (bool)$instance['show_sunshine'] ;
        $show_illuminance = (bool)$instance['show_illuminance'] ;
        $show_uv = (bool)$instance['show_uv'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_light = (bool)$instance['follow_light'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $day_url = $instance['day_url'];
        $night_url = $instance['night_url'];
        $dawn_url = $instance['dawn_url'];
        $dusk_url = $instance['dusk_url'];
        $stations = $this->get_operational_solar_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetSolarSettings.php');
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
        $wtype = 'solar';
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
        $instance['show_current'] = !empty($new_instance['show_current']) ? 1 : 0;
        $instance['show_irradiance'] = !empty($new_instance['show_irradiance']) ? 1 : 0;
        $instance['show_sunshine'] = !empty($new_instance['show_sunshine']) ? 1 : 0;
        $instance['show_illuminance'] = !empty($new_instance['show_illuminance']) ? 1 : 0;
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
        $show_irradiance = (bool)$instance['show_irradiance'] ;
        $show_sunshine = (bool)$instance['show_sunshine'] ;
        $show_illuminance = (bool)$instance['show_illuminance'] ;
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
        $NAMain = false;
        $NAModule5 = false;
        $NACurrent = false;
        $modules = $this->get_widget_data($instance['station'], 'solar', $hide_obsolete);
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
                        if (array_key_exists('irradiance', $module['measurements'])) {
                            $NAModule5 = true;
                            $measurements['irradiance'] = array();
                            $measurements['irradiance']['value'] = $module['measurements']['irradiance']['value'];
                            $measurements['irradiance']['unit'] = $module['measurements']['irradiance']['unit']['unit'];
                            $measurements['irradiance']['icon'] = $this->output_iconic_value($module['measurements']['irradiance']['raw_value'], 'irradiance', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_irradiance = false;
                        }
                        if (array_key_exists('sunshine', $module['measurements'])) {
                            $NAModule5 = true;
                            $v = self::get_age_array_from_seconds($module['measurements']['sunshine']['value']);
                            $measurements['sunshine'] = array();
                            $measurements['sunshine']['hvalue'] = $v[0];
                            $measurements['sunshine']['hunit'] = __('h', 'live-weather-station');
                            $measurements['sunshine']['mvalue'] = $v[1];
                            $measurements['sunshine']['munit'] = __('min.', 'live-weather-station');
                            $measurements['sunshine']['icon'] = $this->output_iconic_value($module['measurements']['sunshine']['raw_value'], 'sunshine', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_sunshine = false;
                        }
                        if (array_key_exists('illuminance', $module['measurements'])) {
                            $NAModule5 = true;
                            $measurements['illuminance'] = array();
                            $measurements['illuminance']['value'] = $module['measurements']['illuminance']['value'];
                            $measurements['illuminance']['unit'] = $module['measurements']['illuminance']['unit']['unit'];
                            $measurements['illuminance']['icon'] = $this->output_iconic_value($module['measurements']['illuminance']['raw_value'], 'illuminance', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        }
                        else {
                            $show_illuminance = false;
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
                        $current = $module;
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
        if (!$NAMain) {
            $show_pressure = false ;
        }
        if (!$NAModule5) {
            $show_uv = false ;
        }
        if (!$NACurrent) {
            $show_current = false ;
        }
        if ($show_current) {
            $show_current = ($measurements['weather']['value'] != 0);
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
        $result = $args['before_widget'];
        $result .= $this->css($instance, $id, $flat, $dawndusk, $bg_url, $background_attachment);
        ob_start();
        include LWS_PUBLIC_DIR.'partials/WidgetSolarDisplay.php';
        $result .= ob_get_clean();
        $result .= $args['after_widget'];
        return $result;
    }
}
