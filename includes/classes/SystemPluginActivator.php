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
		if ($network_wide && is_multisite()) {
			foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $site_id) {
				switch_to_blog($site_id);
				self::activate_site();
				restore_current_blog();
			}
		}
		else {
			self::activate_site();
		}
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
		if (!is_plugin_active_for_network($plugin)) {
			return;
		}
		$site_id = is_object($site) ? (int)$site->blog_id : (int)$site;
		switch_to_blog($site_id);
		self::activate_site();
		restore_current_blog();
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
		Logger::notice('Activator',null,null,null,null,null,null,'Starting ' . LWS_PLUGIN_NAME . ' installation and initialization.');
		self::create_tables();
		self::init_options();
		Logger::notice('Activator',null,null,null,null,null,null,LWS_PLUGIN_NAME.' successfully installed and initialized.');
	}

}
