<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Environment\Manager as Env;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\System\URL\Handling as Url;
use WeatherStation\DB\Storage as Storage;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Activator {

    use Storage, Options, Url;

	/**
	 * Activates the plugin.
	 *
	 * Creates table and initializes options, for the current site or, on a network activation, for every site.
	 *
	 * @param boolean $network_wide Optional. True if the plugin is activated for the whole network.
	 * @since 1.0.0
     * @access public
     * @static
	 */
	public static function activate($network_wide=false) {
		Env::run_on_sites(function() { self::activate_site(); }, $network_wide ? 'all' : null);
	}

	/**
	 * Activates the plugin on a site created after a network activation.
	 *
	 * @param \WP_Site|int $site The new site (an object since WP 5.1, the site id before).
	 * @param string $plugin The basename of the plugin file, used to check the network activation.
	 * @since 3.9.0
	 * @access public
	 * @static
	 */
	public static function activate_new_site($site, $plugin) {
		if (!function_exists('is_plugin_active_for_network')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (is_plugin_active_for_network($plugin)) {
			Env::run_on_sites(function() { self::activate_site(); }, is_object($site) ? $site->blog_id : $site);
		}
	}

	/**
	 * Creates the tables and initializes the options of the current site.
	 *
	 * @since 3.9.0
	 * @access private
	 * @static
	 */
	private static function activate_site() {
		Logger::init();
		Logger::notice('Activator',null,null,null,null,null,null,'Starting ' . LIVE_WEATHER_STATION_PLUGIN_NAME . ' installation and initialization.');
		self::create_tables();
		self::init_options();
		// The feed addresses are rewrite rules: have them flushed (once) on the first request after the activation.
		delete_option('live_weather_station_rewrite_flushed');
		Logger::notice('Activator',null,null,null,null,null,null,LIVE_WEATHER_STATION_PLUGIN_NAME.' successfully installed and initialized.');
	}

}
