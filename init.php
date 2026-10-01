<?php

/**
 * Initialization of globals.
 *
 * @package Bootstrap
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

// Direct access to this file is forbidden.
if (!defined('ABSPATH')) {
    exit;
}

require_once (__DIR__.'/functions.php');
require_once (__DIR__.'/autoload.php');

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\URL\Client as URL;
use WeatherStation\System\Environment\Manager as EnvManager;
use WeatherStation\System\Storage\Manager as FS;

/**
 * Definition of main constants.
 *
 * @since 1.0.0
 */

//--- E X P E R I M E N T A L -----------------------------------------------------------------------

define('LIVE_WEATHER_STATION_FILE_CACHE', false);

//---------------------------------------------------------------------------------------------------

define('LIVE_WEATHER_STATION_VERSION', '3.9.0');
define('LIVE_WEATHER_STATION_PREVIEW', false);

define('LIVE_WEATHER_STATION_CODENAME', '"Danakil"');
define('LIVE_WEATHER_STATION_WHATSNEW', 'https://weather.station.software/blog/weather-station-3-8-danakil/');
define('LIVE_WEATHER_STATION_SHOW_CHANGELOG', false);

//---------------------------------------------------------------------------------------------------

define('LIVE_WEATHER_STATION_CHANGELOG', 'https://weather.station.software/handbook/changelog/');
define('LIVE_WEATHER_STATION_FULL_NAME', 'Weather Station 3');
define('LIVE_WEATHER_STATION_MINIMUM_WP_VERSION', '4.9');
define('LIVE_WEATHER_STATION_MINIMUM_PHP_VERSION', '7.1');
define('LIVE_WEATHER_STATION_PLUGIN_ID', 'live-weather-station');
define('LIVE_WEATHER_STATION_PLUGIN_SLUG', 'live-weather-station');
define('LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN', 'live-weather-station');
define('LIVE_WEATHER_STATION_PLUGIN_NAME', 'Weather Station');
define('LIVE_WEATHER_STATION_PLUGIN_SIGNATURE', LIVE_WEATHER_STATION_PLUGIN_NAME . ' v' . LIVE_WEATHER_STATION_VERSION);
define('LIVE_WEATHER_STATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LIVE_WEATHER_STATION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LIVE_WEATHER_STATION_RELATIVE_PLUGIN_URL', str_replace(get_site_url().'/', '', plugin_dir_url(__FILE__)));
define('LIVE_WEATHER_STATION_ADMIN_DIR', plugin_dir_path(__FILE__).'admin/');
define('LIVE_WEATHER_STATION_ADMIN_URL', plugin_dir_url(__FILE__).'admin/');
define('LIVE_WEATHER_STATION_PUBLIC_DIR', plugin_dir_path(__FILE__).'public/');
define('LIVE_WEATHER_STATION_PUBLIC_URL', plugin_dir_url(__FILE__).'public/');
define('LIVE_WEATHER_STATION_INCLUDES_DIR', plugin_dir_path(__FILE__).'includes/');
define('LIVE_WEATHER_STATION_LANGUAGES_DIR', plugin_dir_path(__FILE__).'languages/');
define('LIVE_WEATHER_STATION_ADMIN_PHP_URL', EnvManager::admin_dir_relative_url());
define('LIVE_WEATHER_STATION_AJAX_URL', EnvManager::ajax_dir_relative_url());
define('LIVE_WEATHER_STATION_I18N_LOADED', EnvManager::is_i18n_loaded());
define('LIVE_WEATHER_STATION_JSON_LOADED', EnvManager::is_json_loaded());
define('LIVE_WEATHER_STATION_PHPVERSION_OK', EnvManager::is_php_version_ok());
define('LIVE_WEATHER_STATION_PLUGIN_AGENT', LIVE_WEATHER_STATION_FULL_NAME . ' (' . EnvManager::wordpress_version_id() . '; ' . EnvManager::weatherstation_version_id() . '; +https://weather.station.software)');
define('LIVE_WEATHER_STATION_IC_WPROCKET', EnvManager::is_wp_rocket_installed());
define('LIVE_WEATHER_STATION_IC_WPSC', EnvManager::is_wp_super_cache_installed());
define('LIVE_WEATHER_STATION_IC_W3TC', EnvManager::is_w3_total_cache_installed());
define('LIVE_WEATHER_STATION_IC_AUTOPTIMIZE', EnvManager::is_autoptimize_installed());
define('LIVE_WEATHER_STATION_IC_HC', EnvManager::is_hyper_cache_installed());
define('LIVE_WEATHER_STATION_WU_ACTIVE', false);
define('LIVE_WEATHER_STATION_SERVICE_SEPARATOR', '{/LWS_SEP/}');


/**
 * Initialize the Logger class that is responsible for logging.
 *
 * @since 2.8.0
 */
require_once LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Logger.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function live_weather_station_run() {
    URL::init_rewrite_rules();
    FS::init();
    $plugin = new \WeatherStation\System\Plugin\Core();
    $plugin->run();
    Watchdog::start();
}