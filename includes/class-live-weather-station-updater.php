<?php

/**
 * Fired during plugin update.
 *
 * This class defines all code necessary to run during the plugin's update.
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(plugin_dir_path( __FILE__ ) . 'trait-datas-storage.php');

require_once(plugin_dir_path( __FILE__ ) . 'trait-url-manipulation.php');
require_once(plugin_dir_path( __FILE__ ) . 'class-watchdog.php');

class Live_Weather_Station_Updater {

    use Datas_Storage, Url_Manipulation;

    /**
     * Updates the plugin.
     *
     * Creates table if needed and updates existing ones. Activates post update too.
     *
     * @since    2.0.0
     */
    public static function update($oldversion) {
        Logger::notice('Updater',null,null,null,null,null,null,'Starting Live Weather Station update.', $oldversion);
        self::create_tables();
        self::update_tables();
        Logger::notice('Updater',null,null,null,null,null,null,'Restarting Live Weather Station.', $oldversion);
        Logger::notice('Updater',null,null,null,null,null,null,'Live Weather Station successfully updated from version ' . $oldversion . ' to version ' . LWS_VERSION . '.');
        Watchdog::restart();
    }
}
