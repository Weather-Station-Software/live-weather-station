<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(plugin_dir_path( __FILE__ ) . 'trait-datas-storage.php');
require_once(plugin_dir_path( __FILE__ ) . 'trait-options-manipulation.php');
require_once(plugin_dir_path( __FILE__ ) . 'trait-url-manipulation.php');

class Live_Weather_Station_Activator {

    use Datas_Storage, Options_Manipulation, Url_Manipulation;

	/**
	 * Activates the plugin.
	 *
	 * Creates table and initializes options.
	 *
	 * @since    1.0.0
     * @access   public
     * @static
	 */
	public static function activate() {
		Logger::notice('Activator',null,null,null,null,null,null,'Starting Live Weather Station installation and initialization.');
		self::create_tables();
        self::init_options();
        Logger::notice('Activator',null,null,null,null,null,null,'Live Weather Station successfully installed and initialized.');
	}

}
