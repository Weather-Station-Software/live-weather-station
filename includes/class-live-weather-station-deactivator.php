<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(plugin_dir_path( __FILE__ ) . 'trait-datas-storage.php');
require_once(plugin_dir_path( __FILE__ ) . 'trait-options-manipulation.php');
require_once(plugin_dir_path( __FILE__ ) . 'class-watchdog.php');

class Live_Weather_Station_Deactivator {

	use Datas_Storage, Options_Manipulation;

	/**
	 * Deactivates the plugin.
	 *
	 * Drop table and delete options.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @static
	 */
	public static function deactivate() {
        Watchdog::stop();
        self::delete_options();
		self::drop_tables();
	}

}
