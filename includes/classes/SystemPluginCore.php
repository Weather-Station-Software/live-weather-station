<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\Data\Type\Description as Type_Description;
use WeatherStation\Data\Unit\Description as Unit_Description;
use WeatherStation\Data\Unit\Conversion as Unit_Conversion;
use WeatherStation\DB\Storage as Storage;
use WeatherStation\System\Environment\Manager as Env;

use WeatherStation\UI\Widget\Outdoor;
use WeatherStation\UI\Widget\Indoor;
use WeatherStation\UI\Widget\Psychrometry;
use WeatherStation\UI\Widget\Solar;
use WeatherStation\UI\Widget\Thunderstorm;
use WeatherStation\UI\Widget\Fire;
use WeatherStation\UI\Widget\Ephemeris;



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
	    $reference = (integer)date('i');
	    // EMERGENCY
	    if (LWS_PHPVERSION_OK && LWS_JSON_LOADED) {
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

        // ERROR
        if ($reference % 30 == 0) {
            if (get_transient('lws_warning_reference') != $reference) {
                if (!Env::is_php_version_uptodate()) {
                    Logger::error('Core', null, null, null, null, null, 122, 'Your PHP version is deprecated. It is no longer supported and can not even receive security fixes from many months. You should seriously consider to update it.');
                }

                if (!Env::is_wp_version_uptodate()) {
                    Logger::error('Core', null, null, null, null, null, 121, 'Your WordPress version is old. You should seriously consider to update it.');
                }
                set_transient('lws_warning_reference', $reference);
            }
        }

        // NOTICE
        if ($reference % 5 == 0) {
            if (get_transient('lws_notice_reference') != $reference) {
                if (!LWS_I18N_LOADED) {
                    Logger::notice('Core', null, null, null, null, null, 333, 'Internationalization support is not installed on your server. ' . LWS_PLUGIN_NAME . ' runs in degraded mode...');
                }
                set_transient('lws_notice_reference', $reference);
            }
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
        $this->loader->add_action('dashboard_glance_items', 'WeatherStation\UI\Dashboard\Handling', 'add_wp_glance_items');
        $this->loader->add_action('admin_init', $plugin_admin, 'init_settings' );
        $this->loader->add_action('admin_init', $plugin_admin, 'force_resync_if_needed' );
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'register_scripts', 1);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'register_styles', 1);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('script_loader_tag', $plugin_admin, 'modify_scripts', 10, 2);
        $this->loader->add_action('admin_menu', $plugin_admin, 'lws_admin_menu' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Outdoor', 'widget_registering');
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Psychrometry', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Ephemeris', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Indoor', 'widget_registering' );
        //$this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Pollution', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Fire', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Thunderstorm', 'widget_registering' );
        $this->loader->add_action('widgets_init', '\WeatherStation\UI\Widget\Solar', 'widget_registering' );
        $this->loader->add_action('wp_ajax_update_lws_welcome_panel', 'WeatherStation\UI\Dashboard\Handling', 'update_lws_welcome_panel_callback' );
        $this->loader->add_action('wp_ajax_delete_notification', 'WeatherStation\UI\Dashboard\Handling', 'delete_notification_callback' );
        $this->loader->add_action('shutdown', '\WeatherStation\System\Analytics\Performance', 'store' );
        $this->loader->add_action('auto_update_plugin', '\WeatherStation\System\Environment\Manager', 'lws_auto_update', 10, 2 );
        if (((bool)get_option('live_weather_station_show_update', 1))) {
            $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notice_update_done');
            $this->loader->add_action('wp_ajax_hide_lws_whatsnew', $plugin_admin, 'hide_lws_whatsnew_callback' );
        }

    }

	/**
	 * Register all of the hooks related to the frontend functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	private function define_public_hooks() {
		$plugin_public = new Frontend($this->get_Live_Weather_Station(), $this->get_version());
		$this->define_conditional_filters($plugin_public);
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts', 1);
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_styles', 1);
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('script_loader_tag', $plugin_public, 'modify_scripts', 10, 2);
		$this->loader->add_action( 'wp_ajax_lws_query_lcd_datas', $plugin_public, 'lws_query_lcd_datas_callback');
		$this->loader->add_action( 'wp_ajax_nopriv_lws_query_lcd_datas', $plugin_public, 'lws_query_lcd_datas_callback');
		$this->loader->add_action( 'wp_ajax_lws_query_justgage_config', $plugin_public, 'lws_query_justgage_config_callback');
		$this->loader->add_action( 'wp_ajax_nopriv_lws_query_justgage_config', $plugin_public, 'lws_query_justgage_config_callback');
		$this->loader->add_action( 'wp_ajax_lws_query_justgage_datas', $plugin_public, 'lws_query_justgage_datas_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_lws_query_justgage_datas', $plugin_public, 'lws_query_justgage_datas_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_steelmeter_config', $plugin_public, 'lws_query_steelmeter_config_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_steelmeter_config', $plugin_public, 'lws_query_steelmeter_config_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_steelmeter_datas', $plugin_public, 'lws_query_steelmeter_datas_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_steelmeter_datas', $plugin_public, 'lws_query_steelmeter_datas_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_steelmeter_datas', $plugin_public, 'lws_clientraw_test_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_steelmeter_datas', $plugin_public, 'lws_clientraw_test_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_graph_datas', $plugin_public, 'lws_graph_data_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_graph_datas', $plugin_public, 'lws_graph_data_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_graph_code', $plugin_public, 'lws_graph_code_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_graph_code', $plugin_public, 'lws_graph_code_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_radial_datas', $plugin_public, 'lws_radial_data_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_radial_datas', $plugin_public, 'lws_radial_data_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_radial_code', $plugin_public, 'lws_radial_code_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_radial_code', $plugin_public, 'lws_radial_code_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_ltgraph_datas', $plugin_public, 'lws_ltgraph_data_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_ltgraph_datas', $plugin_public, 'lws_ltgraph_data_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_ltgraph_code', $plugin_public, 'lws_ltgraph_code_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_ltgraph_code', $plugin_public, 'lws_ltgraph_code_callback');
        $this->loader->add_action( 'wp_ajax_lws_query_lttextual_code', $plugin_public, 'lws_lttextual_code_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_query_lttextual_code', $plugin_public, 'lws_lttextual_code_callback');
        $this->loader->add_action( 'wp_ajax_lws_shortcode', $plugin_public, 'lws_shortcode_callback');
        $this->loader->add_action( 'wp_ajax_nopriv_lws_shortcode', $plugin_public, 'lws_shortcode_callback');
        foreach (array('ephemeris', 'fire', 'indoor', 'outdoor', 'psychrometry', 'solar', 'thunderstorm') as $widget) {
            $this->loader->add_action( 'wp_ajax_lws_w_' . $widget, '\WeatherStation\UI\Widget\\' . ucfirst($widget), 'lws_widget_callback');
            $this->loader->add_action( 'wp_ajax_nopriv_lws_w_' . $widget, '\WeatherStation\UI\Widget\\' . ucfirst($widget), 'lws_widget_callback');
        }
        add_shortcode( 'live-weather-station-icon', array($plugin_public, 'icon_shortcodes'));
        add_shortcode( 'live-weather-station-liveicon', array($plugin_public, 'liveicon_shortcodes'));
        add_shortcode( 'live-weather-station-textual', array($plugin_public, 'textual_shortcodes'));
        add_shortcode( 'live-weather-station-livetextual', array($plugin_public, 'livetextual_shortcodes'));
        add_shortcode( 'live-weather-station-snapshot', array($plugin_public, 'snapshot_shortcodes'));
        add_shortcode( 'live-weather-station-livesnapshot', array($plugin_public, 'livesnapshot_shortcodes'));
        add_shortcode( 'live-weather-station-lcd', array($plugin_public, 'lcd_shortcodes'));
        add_shortcode( 'live-weather-station-timelapse', array($plugin_public, 'timelapse_shortcodes'));
		add_shortcode( 'live-weather-station-justgage', array($plugin_public, 'justgage_shortcodes'));
        add_shortcode( 'live-weather-station-steelmeter', array($plugin_public, 'steelmeter_shortcodes'));
        add_shortcode( 'live-weather-station-graph', array($plugin_public, 'graph_shortcodes'));
        add_shortcode( 'live-weather-station-radial', array($plugin_public, 'radial_shortcodes'));
        add_shortcode( 'live-weather-station-ltgraph', array($plugin_public, 'ltgraph_shortcodes'));
        add_shortcode( 'live-weather-station-lttextual', array($plugin_public, 'lttextual_shortcodes'));
        add_shortcode( 'live-weather-station-admin-analytics', array($plugin_public, 'admin_analytics_shortcodes'));
        add_shortcode( 'live-weather-station-changelog', array($plugin_public, 'admin_changelog_shortcodes'));
        add_shortcode( 'live-weather-station-historical-capabilities', array($plugin_public, 'admin_historical_capabilities_shortcodes'));
        add_shortcode( 'live-weather-station-statistics', array($plugin_public, 'admin_statistics_shortcodes'));
        add_shortcode( 'live-weather-station-translations', array($plugin_public, 'admin_translations_shortcodes'));
        add_shortcode( 'live-weather-station-map', array($plugin_public, 'maps_shortcodes'));
	}

    /**
     * Register all of conditional hooks related to the frontend functionality of the plugin.
     *
     * @since 3.4.0
     */
    private function define_conditional_filters($instance) {
        if ((bool)get_option('live_weather_station_purge_cache')) {
            $cache = new Cache($this->get_Live_Weather_Station(), $this->get_version());
            if (LWS_IC_WPROCKET) {
                $this->loader->add_action('after_rocket_clean_domain', $cache, 'flush');
            }
            if (LWS_IC_WPSC) {
                $this->loader->add_action('wp_cache_gc', $cache, 'flush');
            }
            if (LWS_IC_W3TC) {
                $this->loader->add_action('w3tc_flush_after_fragmentcache', $cache, 'flush');
                $this->loader->add_action('w3tc_flush_after_fragmentcache_group', $cache, 'flush');
                $this->loader->add_action('w3tc_flush_after_minify', $cache, 'flush');
                $this->loader->add_action('w3tc_cdn_purge_all_after', $cache, 'flush');
                $this->loader->add_action('w3tc_cdn_purge_files_after', $cache, 'flush');
                $this->loader->add_action('w3tc_flush_post', $cache, 'flush');
                $this->loader->add_action('w3tc_flush_posts', $cache, 'flush');
                $this->loader->add_action('w3tc_flush_all', $cache, 'flush');
                $this->loader->add_action('w3tc_flush_url', $cache, 'flush');
            }
            if (LWS_IC_AUTOPTIMIZE) {
                $this->loader->add_action('autoptimize_action_cachepurged', $cache, 'flush');
            }
            if (LWS_IC_HC) {
                $this->loader->add_action('hyper_cache_flush', $cache, 'flush');
            }
        }
    }

	/**
	 * Checks if an update is needed and if it the case, performs it.
	 *
	 * @since 2.0.0
	 */
	private function check_and_perfom_update() {
        $old_version = '-';
        $option_overwrite = false;
		if (get_option('live_weather_station_version')) {
            $old_version = get_option('live_weather_station_version');
            $option_overwrite = ($old_version[0] == '1' || $old_version[0] == '2');
        }
        if ($option_overwrite) {
            self::reset_options();
        }
        else {
            self::verify_options();
        }
        if ($old_version != LWS_VERSION && $old_version != '-') {
            Updater::update(get_option('live_weather_station_version'), $option_overwrite);
        }
		update_option('live_weather_station_version', LWS_VERSION);
    }

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
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
