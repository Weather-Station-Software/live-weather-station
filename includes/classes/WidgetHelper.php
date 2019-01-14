<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\Utilities\CSS;

/**
 * Helper for widgets
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.5
 */
class WidgetHelper {

    /**
     * Get the text shadow.
     *
     * @return string The shadow string, ready to be inserted in css declaration.
     * @since 3.7.5
     */
    public static function text_shadow() {
        $result = '';
        $shadow = CSS::shadow(  get_option('live_weather_station_w_text_shadow_position'),
                                get_option('live_weather_station_w_text_shadow_length'),
                                get_option('live_weather_station_w_text_shadow_diffusion'),
                                get_option('live_weather_station_w_text_shadow_obscurity'),
                                get_option('live_weather_station_w_text_shadow_color'));
        if ($shadow !== '') {
            $result = 'text-shadow:' . $shadow . ';';
        }
        return $result;
    }

    /**
     * Get the text shadow.
     *
     * @return string The shadow string, ready to be inserted in css declaration.
     * @since 3.7.5
     */
    public static function svg_shadow() {
        $result = '';
        $shadow = CSS::shadow(  get_option('live_weather_station_w_text_shadow_position'),
                                get_option('live_weather_station_w_text_shadow_length'),
                                get_option('live_weather_station_w_text_shadow_diffusion'),
                                get_option('live_weather_station_w_text_shadow_obscurity'),
                                get_option('live_weather_station_w_text_shadow_color'));
        if ($shadow !== '') {
            $result = '-webkit-filter: drop-shadow(' . $shadow . ');filter: drop-shadow(' . $shadow . ');';
        }
        return $result;
    }

    /**
     * Get the box shadow.
     *
     * @return string The shadow string, ready to be inserted in css declaration.
     * @since 3.7.5
     */
    public static function box_shadow() {
        $result = '';
        $shadow = CSS::shadow(  get_option('live_weather_station_w_box_shadow_position'),
                                get_option('live_weather_station_w_box_shadow_length'),
                                get_option('live_weather_station_w_box_shadow_diffusion'),
                                get_option('live_weather_station_w_box_shadow_obscurity'),
                                get_option('live_weather_station_w_box_shadow_color'));
        if ($shadow !== '') {
            $result = 'box-shadow:' . $shadow . ';';
        }
        return $result;
    }

    /**
     * Get the box radius.
     *
     * @return string The border radius string, ready to be inserted in css declaration.
     * @since 3.7.5
     */
    public static function box_radius() {
        $result = '';
        switch (get_option('live_weather_station_w_box_radius')) {
            case 'short':
                $size = 2 ;
                break;
            case 'medium':
                $size = 4 ;
                break;
            case 'large':
                $size = 8 ;
                break;
            default:
                $size = 0;
        }
        if ($size > 0) {
            $result = 'border-radius:' . $size . 'px;';
        }
        return $result;
    }
}



