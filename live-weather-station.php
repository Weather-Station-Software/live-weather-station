<?php

use WeatherStation\System\Plugin\Activator;
use WeatherStation\System\Plugin\Deactivator;
use WeatherStation\System\Plugin\Uninstaller;

/**
 * @package Bootstrap
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Weather Station
 * Plugin URI:        https://weather.station.software/
 * Description:       Display on your WordPress site, in many elegant ways, the meteorological data collected by public or personal weather stations.
 * Version:           3.8.11
 * Author:            Pierre Lannoy
 * Author URI:        https://pierre.lannoy.fr
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       live-weather-station
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once(__DIR__ . '/init.php');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/SystemPluginActivator.php
 *
 * @since 1.0.0
 */
function activate_Live_Weather_Station() {
    Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/SystemPluginDeactivator.php
 *
 * @since 1.0.0
 */
function deactivate_Live_Weather_Station() {
    Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 *
 * @since 3.8.0
 */
function uninstall_Live_Weather_Station() {
    Uninstaller::uninstall();
}


register_activation_hook( __FILE__, 'activate_Live_Weather_Station' );
register_deactivation_hook( __FILE__, 'deactivate_Live_Weather_Station' );
register_uninstall_hook(__FILE__, 'uninstall_Live_Weather_Station');
run_Live_Weather_Station();
