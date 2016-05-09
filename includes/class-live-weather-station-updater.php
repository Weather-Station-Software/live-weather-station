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
require_once(plugin_dir_path( __FILE__ ) . 'class-watchdog.php');

class Live_Weather_Station_Updater {

    use Datas_Storage;

    /**
     * Updates the plugin.
     *
     * Creates table if needed and updates existing ones. Activates post update too.
     *
     * @since    2.0.0
     */
    public static function update() {
        self::create_tables();
        self::update_tables();
        Watchdog::restart();
    }
}
