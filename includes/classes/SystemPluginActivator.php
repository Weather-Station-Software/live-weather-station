<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\System\URL\Handling as Url;
use WeatherStation\DB\Storage as Storage;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Activator {

    use Storage, Options, Url;

	/**
	 * Activates the plugin.
	 *
	 * Creates table and initializes options.
	 *
	 * @since 1.0.0
     * @access public
     * @static
	 */
	public static function activate() {
		Logger::notice('Activator',null,null,null,null,null,null,'Starting ' . LWS_PLUGIN_NAME . ' installation and initialization.');
		self::create_tables();
        self::init_options();
        Logger::notice('Activator',null,null,null,null,null,null,LWS_PLUGIN_NAME.' successfully installed and initialized.');
	}

}
