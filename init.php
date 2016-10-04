<?php

/**
 * Initialization of globals.
 *
 * @package Bootstrap
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

require_once (__DIR__.'/autoload.php');

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\URL\Client as URL;

/**
 * The detection of this url allows the plugin to make ajax calls when
 * the site has not a standard /wp-admin/ path.
 *
 * @since 2.2.2
 */
function ajax_dir_relative_url() {
    $url = preg_replace('/(http[s]?:\/\/.*\/)/iU', '',  get_admin_url(), 1);
    return (substr($url, 0) == '/' ? '' : '/') . $url . (substr($url, -1) == '/' ? '' : '/') .'admin-ajax.php';
}

/**
 * The detection of this url allows the plugin to be called from
 * WP dashboard when the site has not a standard /wp-admin/ path.
 *
 * @since 2.2.2
 */
function admin_dir_relative_url() {
    $url = preg_replace('/(http[s]?:\/\/.*\/)/iU', '',  get_admin_url(), 1);
    return (substr($url, 0) == '/' ? '' : '/') . $url . (substr($url, -1) == '/' ? '' : '/') .'admin.php';
}

/**
 * Verification of mandatory internationalization extension.
 *
 * @since 2.3.0
 */
function is_i18n_loaded() {
    return (class_exists('Locale') && class_exists('DateTimeZone'));
}

/**
 * Verification of mandatory cURL extension.
 *
 * @since 3.0.0
 */
function is_curl_loaded() {
    return (function_exists('curl_version'));
}

/**
 * Verification of mandatory json extension.
 *
 * @since 3.0.0
 */
function is_json_loaded() {
    return (function_exists('json_decode'));
}

/**
 * Verification of PHP version.
 *
 * @since 3.0.0
 */
function is_php_ok() {
    return (!version_compare(PHP_VERSION, '5.4.0', '<'));
}

/**
 * Get the proper admin page url.
 *
 * @param string $page The main page.
 * @param string $action Optional. The specific action on the page.
 * @param string $tab Optional. The tab if the page is tabbed.
 * @param boolean $dashboard Optional. If set to true, redirects to plugin dashboard.
 * @return string The full url of the admin page.
 * @since 3.0.0
 */
function get_admin_page_url($page='lws-dashboard', $action=null, $tab=null, $service=null, $dashboard=false) {
    $args = array('page' => $page);
    if (isset($tab)) {
        $args['tab'] = $tab;
    }
    if (isset($action)) {
        $args['action'] = $action;
    }
    if (isset($service)) {
        $args['service'] = $service;
    }
    $args['dashboard'] = $dashboard;
    $url = add_query_arg($args, admin_url('admin.php'));
    return $url;
}

/**
 * Definition of main constants.
 *
 * @since 1.0.0
 */
define('LWS_FULL_NAME', 'Weather Station 3');
define('LWS_VERSION', '3.0.0-dev2');
define('LWS_INLINE_HELP', true);
define('LWS_WEBSITE_READY', true);

define('LWS_MINIMUM_WP_VERSION', '4.0');
define('LWS_PLUGIN_ID', 'live-weather-station');
define('LWS_PLUGIN_SLUG', 'live-weather-station');
define('LWS_PLUGIN_TEXT_DOMAIN', 'live-weather-station');
define('LWS_PLUGIN_NAME', 'Weather Station');
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
define('LWS_CURL_LOADED', is_curl_loaded());
define('LWS_JSON_LOADED', is_json_loaded());
define('LWS_PHPVERSION_OK', is_php_ok());

/**
 * Initialize the Logger class that is responsible for logging.
 *
 * @since 2.8.0
 */
require_once LWS_INCLUDES_DIR.'system/Logger.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function run_Live_Weather_Station() {
    URL::init_rewrite_rules();
    $plugin = new \WeatherStation\System\Plugin\Core();
    $plugin->run();
    Watchdog::start();
}