<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\Data\Type\Description as Type_Description;
use WeatherStation\Data\Unit\Description as Unit_Description;
use WeatherStation\Data\Unit\Conversion as Unit_Conversion;
use WeatherStation\DB\Storage as Storage;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-front site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Core {

	use Unit_Description, Type_Description, Unit_Conversion, Options, Storage;

	protected $loader;
	protected $Live_Weather_Station;
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-front side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->Live_Weather_Station = LWS_PLUGIN_ID;
		$this->version = LWS_VERSION;
        $this->verify_requirements();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Verification of the plugin requirements.
	 *
	 * @since 3.0.0
	 */
	private function verify_requirements() {
	    if (LWS_PHPVERSION_OK && LWS_JSON_LOADED && LWS_CURL_LOADED && LWS_I18N_LOADED) {
            if (!defined('REQUIREMENTS_OK')) {
                define('REQUIREMENTS_OK', true);
            }
        }
        else {
            if (!defined('REQUIREMENTS_OK')) {
                define('REQUIREMENTS_OK', false);
            }
        }
        if (!LWS_PHPVERSION_OK) {
            Logger::emergency('Core', null, null, null, null, null, 666, 'Your PHP version does not comply to plugin requirements. ' . LWS_PLUGIN_NAME . ' can not run!');
        }
        if (!LWS_JSON_LOADED) {
            Logger::emergency('Core', null, null, null, null, null, 666, 'JSON support is not installed on your server. ' . LWS_PLUGIN_NAME . ' can not run!');
        }
        if (!LWS_CURL_LOADED) {
            Logger::emergency('Core', null, null, null, null, null, 666, 'cURL support is not installed on your server. ' . LWS_PLUGIN_NAME . ' can not run!');
        }
        if (!LWS_I18N_LOADED) {
            Logger::emergency('Core', null, null, null, null, null, 666, 'Internationalization support is not installed on your server. ' . LWS_PLUGIN_NAME . ' can not run!');
        }
	}

    /**
     * Load the required dependencies for this plugin.
     *
     * Create an instance of the loader which will be used to register the hooks with WordPress.
     *
     * @since    1.0.0
     */
    private function load_dependencies() {
        $this->loader = new Loader();
    }

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the I18n class in order to set the domain and to register the hook with WordPress.
	 *
	 * @since 1.0.0
	 */
	private function set_locale() {
		$plugin_i18n = new I18n();
		$plugin_i18n->set_domain(LWS_PLUGIN_TEXT_DOMAIN);
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
        $this->loader->add_filter('override_load_textdomain', $plugin_i18n, 'load_local_textdomain_mofile', 10, 2 );
	}

    /**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Admin($this->get_Live_Weather_Station(), $this->get_version() );
        $this->loader->add_action('wp_dashboard_setup', 'WeatherStation\UI\Dashboard\Handling', 'add_wp_dashboard_widget');
        $this->loader->add_action('admin_init', $plugin_admin, 'init_settings' );
        $this->loader->add_action('admin_init', $plugin_admin, 'force_resync_if_needed' );
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action('admin_menu', $plugin_admin, 'lws_admin_menu' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Outdoor', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Ephemeris', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Indoor', 'widget_registering' );
        //$this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Pollution', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Fire', 'widget_registering' );
        $this->loader->add_action('wp_ajax_update_lws_welcome_panel', 'WeatherStation\UI\Dashboard\Handling', 'update_lws_welcome_panel_callback' );
        $this->loader->add_action('shutdown', '\WeatherStation\System\Analytics\Performance', 'store' );
        $this->loader->add_action('auto_update_plugin', '\WeatherStation\System\Environment\Manager', 'lws_auto_update', 10, 2 );
    }

	/**
	 * Register all of the hooks related to the frontend functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	private function define_public_hooks() {
		$plugin_public = new Frontend( $this->get_Live_Weather_Station(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts', 1);
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_styles', 1);
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action( 'wp_ajax_lws_query_lcd_datas', $plugin_public, 'lws_query_lcd_datas_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_lws_query_lcd_datas', $plugin_public, 'lws_query_lcd_datas_callback' );
		$this->loader->add_action( 'wp_ajax_lws_query_justgage_config', $plugin_public, 'lws_query_justgage_config_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_lws_query_justgage_config', $plugin_public, 'lws_query_justgage_config_callback' );
		$this->loader->add_action( 'wp_ajax_lws_query_justgage_datas', $plugin_public, 'lws_query_justgage_datas_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_lws_query_justgage_datas', $plugin_public, 'lws_query_justgage_datas_callback' );
        $this->loader->add_action( 'wp_ajax_lws_query_steelmeter_config', $plugin_public, 'lws_query_steelmeter_config_callback' );
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_steelmeter_config', $plugin_public, 'lws_query_steelmeter_config_callback' );
        $this->loader->add_action( 'wp_ajax_lws_query_steelmeter_datas', $plugin_public, 'lws_query_steelmeter_datas_callback' );
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_steelmeter_datas', $plugin_public, 'lws_query_steelmeter_datas_callback' );
        $this->loader->add_action( 'wp_ajax_lws_query_steelmeter_datas', $plugin_public, 'lws_clientraw_test_callback' );
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_steelmeter_datas', $plugin_public, 'lws_clientraw_test_callback' );
        add_shortcode( 'live-weather-station-textual', array($plugin_public, 'textual_shortcodes') );
        add_shortcode( 'live-weather-station-lcd', array($plugin_public, 'lcd_shortcodes') );
		add_shortcode( 'live-weather-station-justgage', array($plugin_public, 'justgage_shortcodes') );
        add_shortcode( 'live-weather-station-steelmeter', array($plugin_public, 'steelmeter_shortcodes') );
        add_shortcode( 'live-weather-station-admin-analytics', array($plugin_public, 'admin_analytics_shortcodes') );
	}

	/**
	 * Checks if an update is needed and if it the case, performs it.
	 *
	 * @since 2.0.0
	 */
	private function check_and_perfom_update() {
        $old_version = '-';
		if (!get_option('live_weather_station_version')) {
            $option_migrate = false;
        }
        else {
            $old_version = get_option('live_weather_station_version');
            $option_migrate =  ($old_version[0] == '1' || $old_version[0] == '2');
        }
        self::verify_options($option_migrate);
        if ($old_version != LWS_VERSION && $old_version != '-') {
            Updater::update(get_option('live_weather_station_version'));
        }
		update_option('live_weather_station_version', LWS_VERSION);
    }

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
        $this->check_and_perfom_update();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_Live_Weather_Station() {
		return $this->Live_Weather_Station;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
