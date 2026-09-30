<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Cache\Cache;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Deactivator {

	use Storage, Options;

	/**
	 * Deactivates the plugin.
	 *
	 * Flush caches and stop scheduler, for the current site or, on a network deactivation, for every site.
	 *
	 * @param boolean $network_wide Optional. True if the plugin is deactivated for the whole network.
	 * @since 1.0.0
	 */
	public static function deactivate($network_wide=false) {
		if ($network_wide && is_multisite()) {
			foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $site_id) {
				switch_to_blog($site_id);
				self::deactivate_site();
				restore_current_blog();
			}
		}
		else {
			self::deactivate_site();
		}
	}

	/**
	 * Flush caches and stop scheduler of the current site.
	 *
	 * @since 3.9.0
	 * @access private
	 * @static
	 */
	private static function deactivate_site() {
		Cache::flush_full(false);
		Watchdog::stop();
	}

}
