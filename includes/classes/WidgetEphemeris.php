<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\System\Logs\Logger;


/**
 * Ephemeris widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
class Ephemeris extends Base {

    use Output;

    /**
     * Register the widget.
     *
     * @since    2.0.0
     */
    public static function widget_registering() {
        register_widget( '\WeatherStation\UI\Widget\Ephemeris' );
    }

    /**
     * Initialize the widget.
     *
     * @since    2.0.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Ephemeris',
            '<>🌒 ' .  __( 'Ephemeris' , 'live-weather-station'),
            array( 'description' => sprintf(__('Display ephemeris for sun and moon at the location of a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
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
     * @since    2.0.0
     */
    private function _get_instance($instance)
    {
        $result = wp_parse_args((array)$instance,
            array('title' => '',
                'subtitle' => 1,
                'format' => 1,
                'mode' => 0,
                'station' => 'N/A',
                'bg_color' => '#444444',
                'bg_opacity' => 0,
                'width' => 300,
                'txt_color' => '#ffffff',
                'show_tooltip' => false,
                'show_borders' => false,
                'follow_light' => false,
                'fixed_background' => false,
                'day_url' => '',
                'night_url' => '',
                'dawn_url' => '',
                'dusk_url' => '',
                'flat_design' => false));
        $result['show_tooltip'] = !empty($result['show_tooltip']) ? 1 : 0;
        $result['show_borders'] = !empty($result['show_borders']) ? 1 : 0;
        $result['follow_light'] = !empty($result['follow_light']) ? 1 : 0;
        $result['fixed_background'] = !empty($result['fixed_background']) ? 1 : 0;
        $result['flat_design'] = !empty($result['flat_design']) ? 1 : 0;
        return $result;
    }

    /**
     * Get the settings form.
     *
     * @param    array  $instance   An array containing settings for the widget.
     * @return boolean Nothing.
     * @since    2.0.0
     */
    public function form($instance) {
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $subtitle = $instance['subtitle'];
        $format = $instance['format'];
        $mode = $instance['mode'];
        $station = $instance['station'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_light = (bool)$instance['follow_light'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $day_url = $instance['day_url'];
        $night_url = $instance['night_url'];
        $dawn_url = $instance['dawn_url'];
        $dusk_url = $instance['dusk_url'];
        $stations = $this->get_operational_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetEphemerisSettings.php');
    }

    /**
     * Set the (inline) css for the widget rendering.
     *
     * @param array $instance An array containing settings for the widget.
     * @param string $uid Identifiant of the widget.
     * @param boolean $flat_design Enabling flat design mode.
     * @param integer $dawndusk Optional. Luminosity factor from 0% to 100%.
     * @param string $background Optional. CSS for background Image URL.
     * @param string $attachment Optional. CSS for background-attachment.
     * @since 2.0.0
     */
    public function css($instance, $uid, $flat_design, $dawndusk=100, $background='', $attachment='local') {
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
        $wtype = 'ephemeris';
        $text_shadows = WidgetHelper::text_shadow();
        $box_shadows = WidgetHelper::box_shadow();
        $box_radius = WidgetHelper::box_radius();
        $svg = '';
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
     * @since    2.0.0
     */
    public function update($new_instance, $old_instance) {
        $instance = $this->_get_instance($old_instance);
        $new_instance = $this->_get_instance($new_instance);
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['subtitle'] = $new_instance['subtitle'];
        $instance['format'] = $new_instance['format'];
        $instance['station'] = $new_instance['station'];
        $instance['bg_color'] = $new_instance['bg_color'];
        $instance['bg_opacity'] = $new_instance['bg_opacity'];
        $instance['width'] = $new_instance['width'];
        $instance['txt_color'] = $new_instance['txt_color'];
        $instance['show_tooltip'] = !empty($new_instance['show_tooltip']) ? 1 : 0;
        $instance['show_borders'] = !empty($new_instance['show_borders']) ? 1 : 0;
        $instance['flat_design'] = !empty($new_instance['flat_design']) ? 1 : 0;
        $instance['follow_light'] = !empty($new_instance['follow_light']) ? 1 : 0;
        $instance['fixed_background'] = !empty($new_instance['fixed_background']) ? 1 : 0;
        $instance['day_url'] = $new_instance['day_url'];
        $instance['night_url'] = $new_instance['night_url'];
        $instance['dawn_url'] = $new_instance['dawn_url'];
        $instance['dusk_url'] = $new_instance['dusk_url'];
        $instance['mode'] = $new_instance['mode'];
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
        $format = $instance['format'];
        $mode = $instance['mode'];
        $show_title = !($title=='');
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders =  (bool)$instance['show_borders'];
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
        $dawndusk = 100;
        $modules = $this->get_widget_data($instance['station'], 'ephemeris');
        $location = '';
        $show_sun = false;
        $show_moon = false;
        $show_moonphase = false;
        $show_sundetails = false;
        $show_moondetails = false;
        $measurements = array();
        $current = array();
        $timestamp = '';
        $tz = '';
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('loc_timezone', $module['measurements'])) {
                            $tz = $module['measurements']['loc_timezone']['value'];
                        }
                        break;
                }
            }
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('loc_latitude', $module['measurements']) && array_key_exists('loc_longitude', $module['measurements']) && array_key_exists('loc_altitude', $module['measurements'])) {
                            $location = $this->output_coordinate($module['measurements']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                                $this->output_coordinate($module['measurements']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                                $this->output_value($module['measurements']['loc_altitude']['value'], 'loc_altitude', true) . ')';
                        }
                        break;
                    case 'NAEphemer':
                        $measurements['sun']['icon'] = $this->output_iconic_value(0, 'sun', null, true, 'inherit', 'lws-widget-icon-' . $id);
                        if (array_key_exists('sunrise', $module['measurements']) && array_key_exists('sunset', $module['measurements'])) {
                            if ($mode==0) {
                                $measurements['sunrise']['value'] = $this->output_value($module['measurements']['sunrise']['raw_value'], 'sunrise', true, false, '', $tz);
                                $measurements['sunset']['value'] = $this->output_value($module['measurements']['sunset']['raw_value'], 'sunset', true, false, '', $tz);
                                $measurements['sunrise']['icon'] = $this->output_iconic_value($module['measurements']['sunrise']['raw_value'], 'sunrise', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $measurements['sunset']['icon'] = $this->output_iconic_value($module['measurements']['sunset']['raw_value'], 'sunset', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $show_sun = true;
                            }
                            $sunrise = $module['measurements']['sunrise']['raw_value'];
                            $sunset = $module['measurements']['sunset']['raw_value'];
                        }
                        if (array_key_exists('sunrise_c', $module['measurements']) && array_key_exists('sunset_c', $module['measurements'])) {
                            if ($mode==1) {
                                $measurements['sunrise']['value'] = $this->output_value($module['measurements']['sunrise_c']['raw_value'], 'sunrise_c', true, false, '', $tz);
                                $measurements['sunset']['value'] = $this->output_value($module['measurements']['sunset_c']['raw_value'], 'sunset_c', true, false, '', $tz);
                                $measurements['sunrise']['icon'] = $this->output_iconic_value($module['measurements']['sunrise_c']['raw_value'], 'sunrise', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $measurements['sunset']['icon'] = $this->output_iconic_value($module['measurements']['sunset_c']['raw_value'], 'sunset', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $show_sun = true;
                            }
                        }
                        if (array_key_exists('sunrise_n', $module['measurements']) && array_key_exists('sunset_n', $module['measurements'])) {
                            if ($mode==2) {
                                $measurements['sunrise']['value'] = $this->output_value($module['measurements']['sunrise_n']['raw_value'], 'sunrise_n', true, false, '', $tz);
                                $measurements['sunset']['value'] = $this->output_value($module['measurements']['sunset_n']['raw_value'], 'sunset_n', true, false, '', $tz);
                                $measurements['sunrise']['icon'] = $this->output_iconic_value($module['measurements']['sunrise_n']['raw_value'], 'sunrise', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $measurements['sunset']['icon'] = $this->output_iconic_value($module['measurements']['sunset_n']['raw_value'], 'sunset', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $show_sun = true;
                            }
                        }
                        if (array_key_exists('sunrise_a', $module['measurements']) && array_key_exists('sunset_a', $module['measurements'])) {
                            if ($mode==3) {
                                $measurements['sunrise']['value'] = $this->output_value($module['measurements']['sunrise_a']['raw_value'], 'sunrise_a', true, false, '', $tz);
                                $measurements['sunset']['value'] = $this->output_value($module['measurements']['sunset_a']['raw_value'], 'sunset_a', true, false, '', $tz);
                                $measurements['sunrise']['icon'] = $this->output_iconic_value($module['measurements']['sunrise_a']['raw_value'], 'sunrise', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $measurements['sunset']['icon'] = $this->output_iconic_value($module['measurements']['sunset_a']['raw_value'], 'sunset', null, true, 'inherit', 'lws-widget-icon-' . $id);
                                $show_sun = true;
                            }
                            $sunrise_a = $module['measurements']['sunrise_a']['raw_value'];
                            $sunset_a = $module['measurements']['sunset_a']['raw_value'];
                        }
                        if (array_key_exists('sun_distance', $module['measurements']) && array_key_exists('sun_diameter', $module['measurements'])) {
                            $measurements['sun_distance']['value'] = $this->output_value($module['measurements']['sun_distance']['raw_value'], 'sun_distance');
                            $measurements['sun_distance']['unit'] = $module['measurements']['sun_distance']['unit']['unit'];
                            $measurements['sun_diameter']['value'] = $this->output_value($module['measurements']['sun_diameter']['raw_value'], 'sun_diameter');
                            $measurements['sun_diameter']['unit'] = $module['measurements']['sun_diameter']['unit']['unit'];
                            $measurements['sun_distance']['icon'] = $this->output_iconic_value($module['measurements']['sun_distance']['raw_value'], 'sun_distance', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['sun_diameter']['icon'] = $this->output_iconic_value($module['measurements']['sun_diameter']['raw_value'], 'sun_diameter', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $show_sundetails = true;
                        }
                        if (array_key_exists('moonrise', $module['measurements']) && array_key_exists('moonset', $module['measurements'])) {
                            $measurements['moonrise']['value'] = $this->output_value($module['measurements']['moonrise']['raw_value'], 'moonrise', true, false, '', $tz);
                            $measurements['moonset']['value'] = $this->output_value($module['measurements']['moonset']['raw_value'], 'moonset', true, false, '', $tz);
                            $measurements['moonrise']['icon'] = $this->output_iconic_value($module['measurements']['moonrise']['raw_value'], 'moonrise', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $measurements['moonset']['icon'] = $this->output_iconic_value($module['measurements']['moonset']['raw_value'], 'moonset', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $show_moon = true;
                        }
                        if (array_key_exists('moon_phase', $module['measurements'])) {
                            $measurements['moon_phase']['value'] = $this->get_moon_phase_icon($module['measurements']['moon_phase']['raw_value']);
                            $measurements['moon_phase']['name'] = $this->output_value($module['measurements']['moon_phase']['raw_value'], 'moon_phase', true);
                            $measurements['moon_phase']['icon'] = $this->output_iconic_value($module['measurements']['moon_phase']['raw_value'], 'moon_phase', null, true, 'inherit', 'lws-widget-icon-' . $id);
                            $show_moonphase = true;
                        }
                        if (array_key_exists('moon_age', $module['measurements'])) {
                            $measurements['moon_age']['value'] = $this->output_value($module['measurements']['moon_age']['raw_value'], 'moon_age', true, true);
                        } else {
                            $measurements['moon_age']['value'] = '';
                        }
                        if (array_key_exists('moon_illumination', $module['measurements'])) {
                            $measurements['moon_illumination']['value'] = $this->output_value($module['measurements']['moon_illumination']['raw_value'], 'moon_illumination');
                            $measurements['moon_illumination']['unit'] = $module['measurements']['moon_illumination']['unit']['unit'];
                        } else {
                            $measurements['moon_illumination']['value'] = '';
                            $measurements['moon_illumination']['unit'] = '';
                        }
                        if (array_key_exists('moon_distance', $module['measurements']) && array_key_exists('moon_diameter', $module['measurements'])) {
                            $measurements['moon_distance']['value'] = $this->output_value($module['measurements']['moon_distance']['raw_value'], 'sun_distance');
                            $measurements['moon_distance']['unit'] = $module['measurements']['moon_distance']['unit']['unit'];
                            $measurements['moon_diameter']['value'] = $this->output_value($module['measurements']['moon_diameter']['raw_value'], 'sun_diameter');
                            $measurements['moon_diameter']['unit'] = $module['measurements']['moon_diameter']['unit']['unit'];
                            $show_moondetails = true;
                        }
                        break;
                }
            }
            $timestamp = self::get_date_from_utc($modules['timestamp'], $tz).', '.self::get_time_from_utc($modules['timestamp'], $tz);
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
        include LWS_PUBLIC_DIR.'partials/WidgetEphemerisDisplay.php';
        $result .= ob_get_clean();
        $result .= $args['after_widget'];
        return $result;
    }
}
