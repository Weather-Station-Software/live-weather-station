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
     * (the captures are restricted to the characters of a station id / a format name: the request handler validates them again).
     * The rules target index.php, so WordPress handles them itself (nginx and Apache) and stores them in the rewrite_rules option.
     *
     * @since 3.0.0
     */
    public static function add_url_station_id_format() {
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/([A-Za-z0-9_]{1,40})\/?$', 'index.php?live_weather_station_type=$matches[2]&live_weather_station_station=$matches[1]', 'top');
    }

    /**
     * Add url rewrite for path /get-weather/[ID]/[subformat]_stickertags.txt and other files
     *
     * @since 3.0.0
     */
    public static function add_url_station_id_subformat() {
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/([A-Z]{0,40})_stickertags\.txt$', 'index.php?live_weather_station_type=stickertags&live_weather_station_station=$matches[1]&live_weather_station_subformat=$matches[2]', 'top');
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/clientraw\.txt$', 'index.php?live_weather_station_type=clientraw&live_weather_station_station=$matches[1]', 'top');
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/realtime\.txt$', 'index.php?live_weather_station_type=realtime&live_weather_station_station=$matches[1]', 'top');
        add_rewrite_rule('^get-weather\/([A-Za-z0-9:%._-]{1,64})\/YoWindow\.xml$', 'index.php?live_weather_station_type=yowindow&live_weather_station_station=$matches[1]', 'top');
    }

    /**
     * Declare the query variables used by the feed addresses.
     *
     * @param array $vars The public query variables.
     * @return array The public query variables, with the ones of the plugin.
     * @since 3.9.0
     */
    public static function add_query_vars($vars) {
        $vars = (array)$vars;
        $vars[] = 'live_weather_station_station';
        $vars[] = 'live_weather_station_type';
        $vars[] = 'live_weather_station_subformat';
        return $vars;
    }

    /**
     * Flush the rewrite rules once after the plugin has been updated to the version that serves the feeds through WordPress.
     *
     * A stored flag avoids any flush on the next requests. The first flush only rebuilds the rules stored in the database;
     * the rewrite block of .htaccess (which may still contain the rules of the previous versions, targeting a file that no longer exists)
     * is rewritten by the first flush made when the WordPress file helpers are available (administration).
     *
     * @since 3.9.0
     */
    public static function maybe_flush_rewrite_rules() {
        $state = get_option('live_weather_station_rewrite_flushed', '');
        if ($state === 'hard') {
            return;
        }
        $hard = function_exists('save_mod_rewrite_rules');
        if (!$hard && $state === 'soft') {
            return;
        }
        flush_rewrite_rules($hard);
        update_option('live_weather_station_rewrite_flushed', $hard ? 'hard' : 'soft', true);
        Logger::notice('Core', null, null, null, null, null, null, 'Rewrite rules flushed after the feed addresses moved to WordPress.');
    }

    /**
     * Create rewriterules.
     *
     * @since 3.0.0
     */
    public static function init_rewrite_rules() {
        add_action('init', array(get_called_class(), 'add_url_station_id_format'));
        add_action('init', array(get_called_class(), 'add_url_station_id_subformat'));
        add_action('init', array(get_called_class(), 'maybe_flush_rewrite_rules'), 99);
        add_action('admin_init', array(get_called_class(), 'maybe_flush_rewrite_rules'), 99);
        add_filter('query_vars', array(get_called_class(), 'add_query_vars'));
        add_action('template_redirect', array('\WeatherStation\Engine\Page\Standalone\Framework', 'handle_request'), 1);
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