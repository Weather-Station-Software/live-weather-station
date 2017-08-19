<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\URL\Handling as Url;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Environment\Manager;

/**
 * Fired during plugin update.
 *
 * This class defines all code necessary to run during the plugin's update.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
            Logger::emergency('Updater',null,null,null,null,null,null,'Unable to update this old version of ' . LWS_PLUGIN_NAME . '... Full reinstallation will be necessary.');
            Logger::notice('Updater',null,null,null,null,null,null,'Starting ' . LWS_PLUGIN_NAME . ' installation.');
            Watchdog::stop();
            self::drop_tables(false);
            self::create_tables();
            Logger::notice('Updater',null,null,null,null,null,null,'Starting ' . LWS_PLUGIN_NAME . '.');
            Logger::notice('Updater',null,null,null,null,null,null, LWS_PLUGIN_NAME . ' successfully installed.');
        }
        else {
            Logger::notice('Updater',null,null,null,null,null,null,'Starting ' . LWS_PLUGIN_NAME . ' update.', $oldversion);
            Watchdog::stop();
            self::create_tables();
            self::update_tables($oldversion);
            Logger::notice('Updater',null,null,null,null,null,null,'Restarting ' . LWS_PLUGIN_NAME . '.', $oldversion);
            Logger::notice('Updater',null,null,null,null,null,null, LWS_PLUGIN_NAME . ' successfully updated from version ' . $oldversion . ' to version ' . LWS_VERSION . '.');
        }
        update_option('live_weather_station_last_update', time());
        Cache::reset();
        self::_clean_usermeta('lws-analytics');
        Logger::notice('Updater', null, null, null, null, null, 0, 'Analytics view has been reset to defaults.');
        Watchdog::start();
        delete_transient(self::$transient_name);
        if (Manager::is_updated($oldversion)) {
            update_option('live_weather_station_show_update', 1);
        }
    }
}
