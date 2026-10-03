<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Environment\Manager as Env;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Cache\Cache;

/**
 * Fired during plugin deletion.
 *
 * This class defines all code necessary to run during the plugin's deletion.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class Uninstaller {

    use Storage, Options;

    /**
     * Uninstall the plugin.
     *
     * Drop table and delete options.
     *
     * @since 3.8.0
     */
    public static function uninstall() {
        // WordPress runs the uninstaller once, even when the plugin is deleted from the network admin: clean every site.
        Env::run_on_sites(function() { self::uninstall_site(); }, 'all');
    }

    /**
     * Uninstall the plugin on the current site.
     *
     * @since 3.9.0
     */
    private static function uninstall_site() {
        // Stop the scheduler first, then drop tables BEFORE deleting options: drop_tables() reads the
        // 'keep_tables' option to know if the historical tables must be kept.
        Watchdog::stop();
        self::drop_tables();
        self::delete_options();
        self::clean_all_usermeta();
        self::delete_widgets_and_transients();
    }

    /**
     * Delete the options of the plugin's own widgets (widget_live_weather_station_widget_*) and the rate-limit
     * transients (lws_rl_*) with their timeouts.
     *
     * @since 3.8.15
     */
    protected static function delete_widgets_and_transients() {
        global $wpdb;
        $patterns = array(
            'widget\_live\_weather\_station\_widget\_%',
            '\_transient\_lws\_rl\_%',
            '\_transient\_timeout\_lws\_rl\_%',
            '\_site\_transient\_lws\_rl\_%',
            '\_site\_transient\_timeout\_lws\_rl\_%',
        );
        foreach ($patterns as $pattern) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WordPress options table (core $wpdb->options) cleanup at uninstall, pattern bound by prepare(); write operation, so no caching applies
            $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->options . " WHERE option_name LIKE %s", $pattern));
        }
    }

    /**
     * Add the tables of a site to the ones WordPress drops when this site is deleted from the network.
     * Hooked on wpmu_drop_tables.
     *
     * @param array $tables The tables to drop (name => name).
     * @param int $site_id The id of the deleted site.
     * @return array The tables to drop.
     * @since 3.9.0
     */
    public static function site_tables($tables, $site_id) {
        global $wpdb;
        $like = $wpdb->esc_like($wpdb->get_blog_prefix($site_id) . 'live_weather_station') . '%';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema lookup (SHOW TABLES) of the plugin tables of a deleted site on wpmu_drop_tables, pattern bound by prepare(); runs once per site deletion, so caching is pointless
        foreach ((array)$wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $like)) as $table) {
            $tables[$table] = $table;
        }
        return $tables;
    }

}
