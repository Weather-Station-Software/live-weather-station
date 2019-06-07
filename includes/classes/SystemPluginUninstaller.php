<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Cache\Cache;

/**
 * Fired during plugin deletion.
 *
 * This class defines all code necessary to run during the plugin's deletion.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class Uninstaller {

    use Storage, Options;

    /**
     * Uninstall the plugin.
     *
     * Drop table and delete options.
     *
     * @since 3.8.0
     */
    public static function uninstall() {
        self::delete_options();
        self::drop_tables();
        self::clean_all_usermeta();
    }

}
