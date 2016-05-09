<?php

/**
 * Ephemeris widget class for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-query.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');

class Live_Weather_Station_Widget_Ephemeris extends WP_Widget {

    use Datas_Output;

    /**
     * Register the widget.
     *
     * @since    2.0.0
     */
    public static function widget_registering() {
        register_widget( 'Live_Weather_Station_Widget_Ephemeris' );
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
            __( 'Ephemeris' , 'live-weather-station'),
            array( 'description' => __( 'Display ephemeris for sun and moon at the location of a Netatmo and/or OpenWeatherMap weather station.' , 'live-weather-station') )
        );
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', function () {wp_enqueue_script( 'wp-color-picker' );});
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'underscore' );
        }
        //if ( is_active_widget( false, false, $this->id_base ) ) {
        wp_register_style( 'weather-icons.css', LWS_PUBLIC_URL . 'css/weather-icons.min.css', array(), LWS_VERSION );
        wp_enqueue_style( 'weather-icons.css');
        wp_register_style( 'weather-icons-wind.css', LWS_PUBLIC_URL . 'css/weather-icons-wind.min.css', array(), LWS_VERSION );
        wp_enqueue_style( 'weather-icons-wind.css');
        //}
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
                'station' => 'N/A',
                'bg_color' => '#444444',
                'bg_opacity' => 0,
                'width' => 300,
                'txt_color' => '#ffffff',
                'show_tooltip' => false,
                'show_borders' => false,
                'flat_design' => false));
        $result['show_tooltip'] = !empty($result['show_tooltip']) ? 1 : 0;
        $result['show_borders'] = !empty($result['show_borders']) ? 1 : 0;
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
        $station = $instance['station'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $flat_design = (bool)$instance['flat_design'] ;
        $stations = $this->get_stations_list();
        include(LWS_ADMIN_DIR.'partials/live-weather-station-widget-ephemeris-settings.php');
    }

    /**
     * Set the (inline) css for the widget rendering.
     *
     * @param    array  $instance   An array containing settings for the widget.
     * @param    string  $uid   Identifiant of the widget.
     * @param    boolean  $flat_design   Enabling flat design mode.
     * @since    2.0.0
     */
    public function css($instance, $uid, $flat_design) {
        require_once(LWS_INCLUDES_DIR.'class-colors-manipulation.php');
        try
        {
            $maxwidth = round ($instance['width']);

        }
        catch(Exception $ex)
        {
            $maxwidth = 0;
        }
        $txt_color = $instance['txt_color'];
        $color = new Colors_Manipulation($instance['bg_color']);
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
        $border = new Colors_Manipulation($gradient['light']);
        $icon = new Colors_Manipulation($txt_color);
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
            $gradient_dark = Colors_Manipulation::hexToRgbString($instance['bg_color'], $opacity);
            $gradient_light = Colors_Manipulation::hexToRgbString($instance['bg_color'], $opacity);
            $border_color1 = '#'.$bcc;
            $border_color2 = '#'.$bcc;
        }
        else {
            $gradient_dark = Colors_Manipulation::hexToRgbString('#'.$gradient['dark'], $opacity);
            $gradient_light = Colors_Manipulation::hexToRgbString('#'.$gradient['light'], $opacity);
            $border_color1 = '#'.$border->darken();
            $border_color2 = '#'.$border->darken(16);
        }
        $id = $uid;
        $shadows = !$flat_design;
        $borders = $instance['show_borders'];
        include(LWS_PUBLIC_DIR.'partials/live-weather-station-widget-ephemeris-display-css.php');
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
        return $instance;
    }

    /**
     * Get the widget output.
     *
     * @param    array  $args       An array containing the widget's arguments.
     * @param    array  $instance   An array containing settings for the widget.
     * @since    2.0.0
     */
    public function widget($args, $instance) {
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $subtitle = $instance['subtitle'];
        $format = $instance['format'];
        $show_title = !($title=='');
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders =  (bool)$instance['show_borders'];
        $flat = (bool)$instance['flat_design'] ;
        $modules = $this->format_widget_datas($this->get_ephemeris_datas($instance['station']));
        $location = '';
        $show_sun = false;
        $show_moon = false;
        $show_moonphase = false;
        $show_sundetails = false;
        $show_moondetails = false;
        $datas = array();
        $current = array();
        $timestamp = '';
        $tz = '';
        if (array_key_exists('modules', $modules)) {
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('loc_timezone', $module['datas'])) {
                            $tz = $module['datas']['loc_timezone']['value'];
                        }
                        break;
                }
            }
            foreach ($modules['modules'] as $module) {
                switch ($module['type']) {
                    case 'NAMain':
                        if (array_key_exists('loc_latitude', $module['datas']) && array_key_exists('loc_longitude', $module['datas']) && array_key_exists('loc_altitude', $module['datas'])) {
                            $location = $this->output_coordinate($module['datas']['loc_latitude']['value'], 'loc_latitude', 6) . ' / ' .
                                $this->output_coordinate($module['datas']['loc_longitude']['value'], 'loc_longitude', 6) . ' (' .
                                $this->output_value($module['datas']['loc_altitude']['value'], 'loc_altitude', true) . ')';
                        }
                        break;
                    case 'NAEphemer':
                        if (array_key_exists('sunrise', $module['datas']) && array_key_exists('sunset', $module['datas'])) {
                            $datas['sunrise']['value'] = $this->output_value($module['datas']['sunrise']['value'], 'sunrise', true, false, '', $tz);
                            $datas['sunset']['value'] = $this->output_value($module['datas']['sunset']['value'], 'sunset', true, false, '', $tz);
                            $show_sun = true;
                        }
                        if (array_key_exists('sun_distance', $module['datas']) && array_key_exists('sun_diameter', $module['datas'])) {
                            $datas['sun_distance']['value'] = $this->output_value($module['datas']['sun_distance']['value'], 'sun_distance');
                            $datas['sun_distance']['unit'] = $module['datas']['sun_distance']['unit']['unit'];
                            $datas['sun_diameter']['value'] = $this->output_value($module['datas']['sun_diameter']['value'], 'sun_diameter');
                            $datas['sun_diameter']['unit'] = $module['datas']['sun_diameter']['unit']['unit'];
                            $show_sundetails = true;
                        }
                        if (array_key_exists('moonrise', $module['datas']) && array_key_exists('moonset', $module['datas'])) {
                            $datas['moonrise']['value'] = $this->output_value($module['datas']['moonrise']['value'], 'moonrise', true, false, '', $tz);
                            $datas['moonset']['value'] = $this->output_value($module['datas']['moonset']['value'], 'moonset', true, false, '', $tz);
                            $show_moon = true;
                        }
                        if (array_key_exists('moon_phase', $module['datas'])) {
                            $datas['moon_phase']['value'] = $this->get_moon_phase_icon($module['datas']['moon_phase']['value']);
                            $datas['moon_phase']['name'] = $this->output_value($module['datas']['moon_phase']['value'], 'moon_phase', true);
                            $show_moonphase = true;
                        }
                        if (array_key_exists('moon_age', $module['datas'])) {
                            $datas['moon_age']['value'] = $this->output_value($module['datas']['moon_age']['value'], 'moon_age', true, true);
                        } else {
                            $datas['moon_age']['value'] = '';
                        }
                        if (array_key_exists('moon_illumination', $module['datas'])) {
                            $datas['moon_illumination']['value'] = $this->output_value($module['datas']['moon_illumination']['value'], 'moon_illumination');
                            $datas['moon_illumination']['unit'] = $module['datas']['moon_illumination']['unit']['unit'];
                        } else {
                            $datas['moon_illumination']['value'] = '';
                            $datas['moon_illumination']['unit'] = '';
                        }
                        if (array_key_exists('moon_distance', $module['datas']) && array_key_exists('moon_diameter', $module['datas'])) {
                            $datas['moon_distance']['value'] = $this->output_value($module['datas']['moon_distance']['value'], 'sun_distance');
                            $datas['moon_distance']['unit'] = $module['datas']['moon_distance']['unit']['unit'];
                            $datas['moon_diameter']['value'] = $this->output_value($module['datas']['moon_diameter']['value'], 'sun_diameter');
                            $datas['moon_diameter']['unit'] = $module['datas']['moon_diameter']['unit']['unit'];
                            $show_moondetails = true;
                        }
                        break;
                }
            }
            $timestamp = self::get_date_from_utc($modules['timestamp']).', '.self::get_time_from_utc($modules['timestamp'], $tz);
        }
        echo $args['before_widget'];
        $id = uniqid();
        $this->css($instance, $id, $flat);
        include(LWS_PUBLIC_DIR.'partials/live-weather-station-widget-ephemeris-display.php');
        echo $args['after_widget'];
    }
}
