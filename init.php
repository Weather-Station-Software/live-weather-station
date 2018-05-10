<?php

/**
 * Initialization of globals.
 *
 * @package Bootstrap
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

require_once (__DIR__.'/functions.php');
require_once (__DIR__.'/autoload.php');

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\URL\Client as URL;
use WeatherStation\System\Environment\Manager as EnvManager;

/**
 * Definition of main constants.
 *
 * @since 1.0.0
 */

//---------------------------------------------------------------------------------------------------

define('LWS_VERSION', '3.5.2');
define('LWS_CODENAME', '"Les Wampas"');
define('LWS_WATSNEW_EN', 'https://weather.station.software/en/weather-station-3-5-leswampas/');
define('LWS_WATSNEW_FR', 'https://weather.station.software/fr/weather-station-35-les-wampas/');
define('LWS_SHOW_CHANGELOG', false);

//---------------------------------------------------------------------------------------------------

define('LWS_CHANGELOG_EN', 'https://weather.station.software/en/handbook/changelog/');
define('LWS_CHANGELOG_FR', 'https://weather.station.software/fr/documentation/journal-des-versions/');
define('LWS_FULL_NAME', 'Weather Station 3');
define('LWS_OWM_READY', false);
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
define('LWS_ICONV_LOADED', EnvManager::is_iconv_loaded());
define('LWS_PHPVERSION_OK', EnvManager::is_php_version_ok());
define('LWS_PLUGIN_AGENT', LWS_FULL_NAME . ' (' . EnvManager::wordpress_version_id() . '; ' . EnvManager::weatherstation_version_id() . '; +https://weather.station.software)');
define('LWS_IC_WPROCKET', EnvManager::is_wp_rocket_installed());
define('LWS_IC_WPSC', EnvManager::is_wp_super_cache_installed());
define('LWS_IC_W3TC', EnvManager::is_w3_total_cache_installed());
define('LWS_IC_AUTOPTIMIZE', EnvManager::is_autoptimize_installed());
define('LWS_IC_HC', EnvManager::is_hyper_cache_installed());



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