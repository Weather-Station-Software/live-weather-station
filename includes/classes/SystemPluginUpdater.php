<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\URL\Handling as Url;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Environment\Manager;
use WeatherStation\System\Notifications\Notifier;
use WeatherStation\System\Help\InlineHelp;

/**
 * Fired during plugin update.
 *
 * This class defines all code necessary to run during the plugin's update.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
class Updater {

    use Storage, Url;

    private static $transient_name = 'lws_updating_now' ;
    private static $transient_expiry = 600 ;

    /**
     * Updates the plugin.
     *
     * Creates table if needed and updates existing ones. Activates post update too.
     *
     * @param string $oldversion Version id before migration.
     * @param boolean $overwrite Don't migrate but overwrite.
     * @since 2.0.0
     */
    public static function update($oldversion, $overwrite) {
        if (get_transient(self::$transient_name)) {
            return;
        }
        set_transient(self::$transient_name, 1, self::$transient_expiry);
        if ($overwrite) {
            Logger::emergency('Updater',null,null,null,null,null,null,'Unable to update this old version of ' . LIVE_WEATHER_STATION_PLUGIN_NAME . '... Full reinstallation will be necessary.');
            Logger::notice('Updater',null,null,null,null,null,null,'Starting ' . LIVE_WEATHER_STATION_PLUGIN_NAME . ' installation.');
            Watchdog::stop();
            self::drop_tables(false);
            self::create_tables();
            Logger::notice('Updater',null,null,null,null,null,null,'Starting ' . LIVE_WEATHER_STATION_PLUGIN_NAME . '.');
            Logger::notice('Updater',null,null,null,null,null,null, LIVE_WEATHER_STATION_PLUGIN_NAME . ' successfully installed.');
        }
        else {
            Logger::notice('Updater',null,null,null,null,null,null,'Starting ' . LIVE_WEATHER_STATION_PLUGIN_NAME . ' update.', $oldversion);
            Watchdog::stop();
            self::create_tables();
            self::update_tables($oldversion);
            self::migrate_options($oldversion);
            Logger::notice('Updater',null,null,null,null,null,null,'Restarting ' . LIVE_WEATHER_STATION_PLUGIN_NAME . '.', $oldversion);
            Logger::notice('Updater',null,null,null,null,null,null, LIVE_WEATHER_STATION_PLUGIN_NAME . ' successfully updated from version ' . $oldversion . ' to version ' . LIVE_WEATHER_STATION_VERSION . '.');
        }
        update_option('live_weather_station_last_update', time());
        Cache::reset();
        self::_clean_usermeta('lws-analytics');
        Logger::notice('Updater', null, null, null, null, null, 0, 'Analytics view has been reset to defaults.');
        Watchdog::start();
        delete_transient(self::$transient_name);
        if (Manager::is_updated($oldversion)) {
            update_option('live_weather_station_show_update', 1);
            if (defined('DISABLE_NAG_NOTICES')) {
                if (DISABLE_NAG_NOTICES === true) {
                    update_option('live_weather_station_show_update', 0);
                }
            }
            Notifier::info(sprintf(/* translators: %s: plugin name */ __('%s has been updated.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME), InlineHelp::whats_new_url(), sprintf(/* translators: %s: plugin version number */ __('Your site now uses version %s.', 'live-weather-station'), LIVE_WEATHER_STATION_VERSION));
        }
    }

    /**
     * Migrates the options removed or replaced by a new version.
     *
     * @param string $oldversion Version id before migration.
     * @since 3.9.0
     */
    private static function migrate_options($oldversion) {
        if (version_compare((string)$oldversion, '3.9.0', '>=')) {
            return;
        }
        // The public CDN option (jsDelivr) and the hoster lookup (ip-api.com) were removed.
        delete_option('live_weather_station_use_cdn');
        delete_option('live_weather_station_hoster_lookup');
        // The plugin no longer forces its own automatic update: the sites that relied on it (the default) are added to the
        // list of WordPress itself, where the setting is visible and can be changed on the Plugins screen.
        if (get_option('live_weather_station_auto_update')) {
            $basename = plugin_basename(LIVE_WEATHER_STATION_PLUGIN_DIR . 'live-weather-station.php');
            $enabled = (array)get_site_option('auto_update_plugins', array());
            if (!in_array($basename, $enabled, true)) {
                $enabled[] = $basename;
                update_site_option('auto_update_plugins', $enabled);
            }
        }
        delete_option('live_weather_station_auto_update');
    }
}
