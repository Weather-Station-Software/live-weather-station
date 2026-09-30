<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Cache\Cache;

/**
 * Fired during plugin deletion.
 *
 * This class defines all code necessary to run during the plugin's deletion.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
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
            $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->options . " WHERE option_name LIKE %s", $pattern));
        }
    }

}
