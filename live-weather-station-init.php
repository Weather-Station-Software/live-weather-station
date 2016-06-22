<?php

/**
 * Initialization of globals.
 *
 * @since      3.0.0
 * @package    Live_Weather_Station
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */


/**
 * The detection of this url allows the plugin to make ajax calls when
 * the site has not a standard /wp-admin/ path.
 *
 * @since    2.2.2
 */
function ajax_dir_relative_url() {
    $url = preg_replace('/(http[s]?:\/\/.*\/)/iU', '',  get_admin_url(), 1);
    return (substr($url, 0) == '/' ? '' : '/') . $url . (substr($url, -1) == '/' ? '' : '/') .'admin-ajax.php';
}

/**
 * It is important to know if php5-intl is loaded because it's mandatory
 * to manage OWM stations.
 *
 * @since    2.3.0
 */
function is_i18n_loaded() {
    return (class_exists('Locale') && class_exists('DateTimeZone'));
}


define('LWS_VERSION', '2.9.2');
//define('LWS_BETA', true);




define('LWS_MINIMUM_WP_VERSION', '4.0');
define('LWS_PLUGIN_ID', 'live-weather-station');
define('LWS_PLUGIN_NAME', 'Live Weather Station');
define('LWS_PLUGIN_SIGNATURE', LWS_PLUGIN_NAME . ' v' . LWS_VERSION);
define('LWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LWS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LWS_RELATIVE_PLUGIN_URL', str_replace(get_site_url().'/', '', plugin_dir_url(__FILE__)));
define('LWS_ADMIN_DIR', plugin_dir_path(__FILE__).'admin/');
define('LWS_ADMIN_URL', plugin_dir_url(__FILE__).'admin/');
define('LWS_AJAX_URL', ajax_dir_relative_url());
define('LWS_PUBLIC_DIR', plugin_dir_path(__FILE__).'public/');
define('LWS_PUBLIC_URL', plugin_dir_url(__FILE__).'public/');
define('LWS_INCLUDES_DIR', plugin_dir_path(__FILE__).'includes/');
define('LWS_I18N_LOADED', is_i18n_loaded());

/**
 * The Logger clas that is responsible for logging.
 *
 * @since    2.8.0
 */
require_once LWS_INCLUDES_DIR.'class-logger.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public front site hooks.
 *
 * @since    1.0.0
 */
require_once LWS_INCLUDES_DIR.'class-live-weather-station.php';

/**
 * The watchdog class that is used to watch cron jobs of the plugin.
 *
 * @since    2.7.0
 */
require_once LWS_INCLUDES_DIR.'class-watchdog.php';

/**
 * The URL class that is used to manage rewrites.
 *
 * @since    3.0.0
 */
require_once LWS_INCLUDES_DIR.'class-live-weather-station-url.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_Live_Weather_Station() {
    Live_Weather_Station_Url::init_rewrite_rules();
    $plugin = new Live_Weather_Station();
    $plugin->run();
    Watchdog::start();

}