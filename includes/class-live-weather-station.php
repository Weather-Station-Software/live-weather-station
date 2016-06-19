<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-front site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <pierre@lannoy.frm>
 */

require_once(LWS_INCLUDES_DIR.'trait-unit-description.php');
require_once(LWS_INCLUDES_DIR.'trait-type-description.php');
require_once(LWS_INCLUDES_DIR.'trait-unit-conversion.php');
require_once(LWS_INCLUDES_DIR.'trait-options-manipulation.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-storage.php');
require_once(LWS_INCLUDES_DIR.'class-netatmo-updater.php');
require_once(LWS_INCLUDES_DIR.'class-netatmo-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-owm-current-updater.php');
require_once(LWS_INCLUDES_DIR.'class-live-weather-station-widget-outdoor.php');
require_once(LWS_INCLUDES_DIR.'class-live-weather-station-widget-ephemeris.php');

class Live_Weather_Station {

	use Unit_Description, Type_Description, Unit_Conversion, Options_Manipulation, Datas_Storage;

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
     * @access   public
	 */
	public function __construct() {
		$this->Live_Weather_Station = LWS_PLUGIN_ID;
		$this->version = LWS_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Live_Weather_Station_Loader. Orchestrates the hooks of the plugin.
	 * - Live_Weather_Station_i18n. Defines internationalization functionality.
	 * - Live_Weather_Station_Admin. Defines all hooks for the admin area.
	 * - Live_Weather_Station_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
        require_once LWS_INCLUDES_DIR.'class-live-weather-station-loader.php';
        require_once LWS_INCLUDES_DIR.'class-live-weather-station-i18n.php';
		require_once LWS_ADMIN_DIR.'class-live-weather-station-admin.php';
		require_once LWS_PUBLIC_DIR.'class-live-weather-station-public.php';
		$this->loader = new Live_Weather_Station_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Live_Weather_Station_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Live_Weather_Station_i18n();
		$plugin_i18n->set_domain( $this->get_Live_Weather_Station() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Live_Weather_Station_Admin( $this->get_Live_Weather_Station(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
        $this->loader->add_action( 'widgets_init', 'Live_Weather_Station_Widget_Outdoor', 'widget_registering' );
		$this->loader->add_action( 'widgets_init', 'Live_Weather_Station_Widget_Ephemeris', 'widget_registering' );
        $this->loader->add_filter( 'plugin_action_links_'.plugin_basename(LWS_PLUGIN_DIR.'live-weather-station.php'), $plugin_admin, 'admin_settings_link' );
		if (defined('LWS_BETA')) {
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'lws_admin_menu' );
		}
	}

	/**
	 * Register all of the hooks related to the public front functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Live_Weather_Station_Public( $this->get_Live_Weather_Station(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts' );
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
        add_shortcode( 'live-weather-station-textual', array($plugin_public, 'textual_shortcodes') );
        add_shortcode( 'live-weather-station-lcd', array($plugin_public, 'lcd_shortcodes') );
		add_shortcode( 'live-weather-station-justgage', array($plugin_public, 'justgage_shortcodes') );
        add_shortcode( 'live-weather-station-steelmeter', array($plugin_public, 'steelmeter_shortcodes') );
	}

	/**
	 * Checks if an update is needed and if it the case, performs it.
	 *
	 * @since    2.0.0
	 */
	private function check_and_perfom_update() {
        self::verify_options();
        if (get_option('live_weather_station_version') != LWS_VERSION && get_option('live_weather_station_version') != '-') {
            require_once LWS_INCLUDES_DIR.'class-live-weather-station-updater.php';
            Live_Weather_Station_Updater::update(get_option('live_weather_station_version'));
        }
		update_option('live_weather_station_version', LWS_VERSION);
    }

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
     * @access   public
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
     * @access   public
	 */
	public function get_Live_Weather_Station() {
		return $this->Live_Weather_Station;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Live_Weather_Station_Loader    Orchestrates the hooks of the plugin.
     * @access   public
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
     * @access   public
	 */
	public function get_version() {
		return $this->version;
	}
}
