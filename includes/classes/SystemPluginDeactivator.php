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
	 * Flush caches and stop scheduler.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
	    Cache::flush_full(false);
		Watchdog::stop();
	}

}
