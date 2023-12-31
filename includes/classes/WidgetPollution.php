<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Data\Output;
use WeatherStation\Utilities\ColorsManipulation as Color;
use WeatherStation\Data\ID\Handling as ID;

/**
 * Pollution widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Pollution extends Base {

    use Output, ID;

    /**
     * Register the widget.
     *
     * @since 3.1.0
     */
    public static function widget_registering() {
        register_widget('\WeatherStation\UI\Widget\Pollution');
    }

    /**
     * Initialize the widget.
     *
     * @since 3.1.0
     */
    public function __construct() {
        load_plugin_textdomain( 'live-weather-station' );
        parent::__construct(
            'Live_Weather_Station_Widget_Pollution',
            '<>☁ ' .__( 'Atmospheric pollution' , 'live-weather-station'),
            array( 'description' => sprintf(__('Display pollution measurements of a station added to %s.' , 'live-weather-station'), LWS_PLUGIN_NAME))
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
                'hide_probe_distance' => false,
                'show_current' => false,
                'show_co' => false,
                'show_o3' => false,
                'show_so2' => false,
                'show_no2' => false,
                'show_wind' => false,
                'follow_quality' => false,
                'fixed_background' => false,
                'good_url' => '',
                'medium_url' => '',
                'bad_url' => '',
                'flat_design' => false));
        $result['show_tooltip'] = !empty($result['show_tooltip']) ? 1 : 0;
        $result['show_borders'] = !empty($result['show_borders']) ? 1 : 0;
        $result['hide_obsolete'] = !empty($result['hide_obsolete']) ? 1 : 0;
        $result['hide_probe_distance'] = !empty($result['hide_probe_distance']) ? 1 : 0;
        $result['show_current'] = !empty($result['show_current']) ? 1 : 0;
        $result['show_co'] = !empty($result['show_co']) ? 1 : 0;
        $result['show_o3'] = !empty($result['show_o3']) ? 1 : 0;
        $result['show_so2'] = !empty($result['show_so2']) ? 1 : 0;
        $result['show_no2'] = !empty($result['show_no2']) ? 1 : 0;
        $result['show_wind'] = !empty($result['show_wind']) ? 1 : 0;
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
        $station = $instance['station'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $hide_probe_distance = (bool)$instance['hide_probe_distance'];
        $show_co = (bool)$instance['show_co'] ;
        $show_o3 = (bool)$instance['show_o3'] ;
        $show_so2 = (bool)$instance['show_so2'] ;
        $show_no2 = (bool)$instance['show_no2'] ;
        $show_wind = (bool)$instance['show_wind'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $follow_quality = (bool)$instance['follow_quality'] ;
        $fixed_background = (bool)$instance['fixed_background'] ;
        $good_url = $instance['good_url'];
        $medium_url = $instance['medium_url'];
        $bad_url = $instance['bad_url'];
        $stations = $this->get_operational_stations_list();
        include(LWS_ADMIN_DIR.'partials/WidgetPollutionSettings.php');
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
        $instance['hide_probe_distance'] = !empty($new_instance['hide_probe_distance']) ? 1 : 0;
        $instance['show_current'] = !empty($new_instance['show_current']) ? 1 : 0;
        $instance['show_co'] = !empty($new_instance['show_co']) ? 1 : 0;
        $instance['show_o3'] = !empty($new_instance['show_o3']) ? 1 : 0;
        $instance['show_so2'] = !empty($new_instance['show_so2']) ? 1 : 0;
        $instance['show_no2'] = !empty($new_instance['show_no2']) ? 1 : 0;
        $instance['show_wind'] = !empty($new_instance['show_wind']) ? 1 : 0;
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
     * @param string $uid Identifiant of the widget.
     * @param boolean $flat_design Enabling flat design mode.
     * @param integer $quality Quality factor from 0%(poor) to 100%(good).
     * @param string $background Optional. CSS for background Image URL.
     * @param string $attachment Optional. CSS for background-attachment.
     * @since 3.1.0
     */
    public function css($instance, $uid, $flat_design, $quality=100, $background='', $attachment) {
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
        $color = $c;
        /*if ($dawndusk < 100) {
            $color = new Color($c->darken(round(($fact * $c->getHsl()['L']) * (1 - ($dawndusk / 100)), 0)));
        }
        else {
            $color = $c;
        }*/
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
        $instance = $this->_get_instance($instance);
        $title = $instance['title'];
        $subtitle = $instance['subtitle'];
        $show_title = !($title=='');
        $station = $instance['station'];
        $bg_color = $instance['bg_color'];
        $bg_opacity = $instance['bg_opacity'];
        $width = $instance['width'];
        $txt_color = $instance['txt_color'];
        $show_tooltip = (bool)$instance['show_tooltip'];
        $show_borders = (bool)$instance['show_borders'];
        $show_current = (bool)$instance['show_current'];
        $hide_obsolete = (bool)$instance['hide_obsolete'];
        $hide_probe_distance = (bool)$instance['hide_probe_distance'];
        $show_co = (bool)$instance['show_co'] ;
        $show_o3 = (bool)$instance['show_o3'] ;
        $show_so2 = (bool)$instance['show_so2'] ;
        $show_no2 = (bool)$instance['show_no2'] ;
        $show_wind = (bool)$instance['show_wind'] ;
        $flat_design = (bool)$instance['flat_design'] ;
        $shadows = !$flat_design;
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
        $wind_multipart = false;
        $NAMain = false;
        $NAModule2 = false;
        $NACurrent = false;
        $NAPollution = false;
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
                    case 'NAModule2': // Wind gauge
                        if (array_key_exists('windangle', $module['measurements']) && array_key_exists('windstrength', $module['measurements'])) {
                            $NAModule2 = true;
                            $measurements['windangle'] = array();
                            $measurements['windangle']['value'] = $module['measurements']['windangle']['value'];
                            $measurements['windangle']['from'] = $this->get_angle_full_text($module['measurements']['windangle']['value']);
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
                    case 'NACurrent': // Current weather -> OpenWeatherMap
                        $NACurrent = true;
                        $current = $module;
                        break;
                    case 'NAPollution': // Pollution values
                        $NAComputed = true;
                        if (array_key_exists('cbi', $module['measurements'])) {
                            $measurements['cbi'] = array();
                            $measurements['cbi']['value'] = $module['measurements']['cbi']['value'];
                            $cbi = $module['measurements']['cbi']['value'];
                            $measurements['cbi']['unit'] = $this->get_cbi_text($cbi);
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
        if (!$NAModule2 && $has_current) {
            $NAModule2 = true;
            if (array_key_exists('windangle', $current['measurements']) && array_key_exists('windstrength', $current['measurements'])) {
                $measurements['windangle'] = array();
                $measurements['windangle']['value'] = $current['measurements']['windangle']['value'];
                $measurements['windangle']['from'] = $this->get_angle_full_text($current['measurements']['windangle']['value']);
                $measurements['windstrength'] = array();
                $measurements['windstrength']['value'] = $current['measurements']['windstrength']['value'];
                $measurements['windstrength']['unit'] = $current['measurements']['windstrength']['unit']['unit'];
            } else {
                $show_wind = false;
            }
        }
        if (!$NAModule2) {
            $show_wind = false ;
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
        if (!$follow_risk) {
            $cbi = -99999;
        }
        $result = $args['before_widget'];
        $result .= $this->css($instance, $id, $flat_design, $health_idx, $bg_url, $background_attachment);
        ob_start();
        include LWS_PUBLIC_DIR.'partials/WidgetIndoorDisplay.php';
        $result .= ob_get_clean();
        $result .= $args['after_widget'];
        return $result;
    }
}
