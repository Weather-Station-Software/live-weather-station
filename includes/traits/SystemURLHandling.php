<?php

namespace WeatherStation\System\URL;

use WeatherStation\System\Logs\Logger;

/**
 * URL & rewrites handling for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait Handling {

    /**
     * Add url rewrite for path /get-weather/[ID]/[format]
     * (the captures are restricted to the characters of a station id / a format name: generator.php validates them again).
     *
     * @since 3.0.0
     */
    public static function add_url_station_id_format() {
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/([A-Za-z0-9_]{1,40})\/$', LIVE_WEATHER_STATION_RELATIVE_PLUGIN_URL.'generator.php?type=$matches[2]&station=$matches[1]','top');
    }

    /**
     * Add url rewrite for path /get-weather/[ID]/[subformat]_stickertags.txt and other files
     *
     * @since 3.0.0
     */
    public static function add_url_station_id_subformat() {
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/([A-Z]{0,40})_stickertags\.txt$', LIVE_WEATHER_STATION_RELATIVE_PLUGIN_URL.'generator.php?type=stickertags&station=$matches[1]&subformat=$matches[2]','top');
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/clientraw\.txt$', LIVE_WEATHER_STATION_RELATIVE_PLUGIN_URL.'generator.php?type=clientraw&station=$matches[1]','top');
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/realtime\.txt$', LIVE_WEATHER_STATION_RELATIVE_PLUGIN_URL.'generator.php?type=realtime&station=$matches[1]','top');
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/YoWindow\.xml$', LIVE_WEATHER_STATION_RELATIVE_PLUGIN_URL.'generator.php?type=yowindow&station=$matches[1]','top');
    }

    /**
     * Create rewriterules.
     *
     * @since 3.0.0
     */
    public static function init_rewrite_rules() {
        add_action('init', array(get_called_class(), 'add_url_station_id_format'));
        add_action('init', array(get_called_class(), 'add_url_station_id_subformat'));

    }

    /**
     * Flush the rewrite rules & tags.
     *
     * @since 3.0.0
     */
    public static function apply() {
        flush_rewrite_rules();
        Logger::notice('Core', null, null, null, null, null, null, 'Rewrite rules flushed and regenerated.');
    }
}