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
use WeatherStation\System\Environment\Manager as EnvManager;

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
function get_admin_page_url($page='lws-dashboard', $action=null, $tab=null, $service=null, $dashboard=false, $id=null) {
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
    if (isset($id)) {
        $args['id'] = $id;
    }
    $args['dashboard'] = $dashboard;
    $url = add_query_arg($args, admin_url('admin.php'));
    return $url;
}

/**
 * Get the proper user locale regarding WP version differences.
 *
 * @param int|WP_User $user_id User's ID or a WP_User object. Defaults to current user.
 * @return string The locale of the user.
 * @since 3.0.8
 */
function get_display_locale($user_id = 0) {
    /*
    * @fixme how to manage ajax calls made from frontend?
    */
    if (function_exists('get_user_locale') && (is_admin() || is_blog_admin())) {
        return get_user_locale($user_id);
    }
    else {
        return get_locale();
    }
}

/**
 * Definition of main constants.
 *
 * @since 1.0.0
 */

//---------------------------------------------------------------------------------------------------

define('LWS_FULL_NAME', 'Weather Station 3');
define('LWS_VERSION', '3.2.5');
define('LWS_CODENAME', '"Merzhin"');
define('LWS_OWM_READY', false);
define('LWS_TXT_READY', false);
define('LWS_WFW_READY', false);

//---------------------------------------------------------------------------------------------------

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
define('LWS_PUBLIC_DIR', plugin_dir_path(__FILE__).'public/');
define('LWS_PUBLIC_URL', plugin_dir_url(__FILE__).'public/');
define('LWS_INCLUDES_DIR', plugin_dir_path(__FILE__).'includes/');
define('LWS_LANGUAGES_DIR', plugin_dir_path(__FILE__).'languages/');
define('LWS_ADMIN_PHP_URL', EnvManager::admin_dir_relative_url());
define('LWS_AJAX_URL', EnvManager::ajax_dir_relative_url());
define('LWS_I18N_LOADED', EnvManager::is_i18n_loaded());
define('LWS_CURL_LOADED', EnvManager::is_curl_loaded());
define('LWS_JSON_LOADED', EnvManager::is_json_loaded());
define('LWS_PHPVERSION_OK', EnvManager::is_php_version_ok());
define('LWS_PLUGIN_AGENT', LWS_FULL_NAME . ' (' . EnvManager::wordpress_version_id() . '; ' . EnvManager::weatherstation_version_id() . '; +https://weather.station.software)');
//define('LWS_PLUGIN_AGENT', 'Mozilla/5.0 (Windows NT 5.1; rv:15.0) Gecko/20100101 Firefox/15.0.1');


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