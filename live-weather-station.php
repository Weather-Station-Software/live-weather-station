<?php

use WeatherStation\System\Plugin\Activator;
use WeatherStation\System\Plugin\Deactivator;
use WeatherStation\System\Plugin\Uninstaller;

/**
 * @package Bootstrap
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:     Weather Station
 * Description:     Display on your WordPress site, in many elegant ways, the meteorological data collected by public or personal weather stations.
 * Author:          Jason Rouet
 * Author URI:      https://www.jasonrouet.com/
 * Plugin URI:      https://weather.station.software/
 * Text Domain:     live-weather-station
 * Domain Path:     /languages
 * License:         GPLv2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Version:         3.9.0
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
function activate_Live_Weather_Station($network_wide = false) {
    Activator::activate($network_wide);
}

/**
 * The code that runs when a site is created on a network where the plugin is network activated.
 *
 * @param WP_Site|int $site The new site (an object since WP 5.1, the site id before).
 * @since 3.9.0
 */
function initialize_site_Live_Weather_Station($site) {
    Activator::activate_new_site($site, plugin_basename(__FILE__));
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
add_action( function_exists('wp_initialize_site') ? 'wp_initialize_site' : 'wpmu_new_blog', 'initialize_site_Live_Weather_Station', 900 );
register_deactivation_hook( __FILE__, 'deactivate_Live_Weather_Station' );
register_uninstall_hook(__FILE__, 'uninstall_Live_Weather_Station');
run_Live_Weather_Station();
