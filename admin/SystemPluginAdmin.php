<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\SDK\Clientraw\Plugin\StationCollector as ClientrawCollector;
use WeatherStation\SDK\Realtime\Plugin\StationCollector as RealtimeCollector;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Environment\Manager;
use WeatherStation\System\Schedules\Handling as Schedule;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\UI\Dashboard\Handling as Dashboard;
use WeatherStation\UI\Services\Handling as Services;
use WeatherStation\UI\Analytics\Handling as Analytics;
use WeatherStation\UI\Station\Handling as Station;
use WeatherStation\System\Help\InlineHelp;
use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\Data\Arrays\Generator as Arrays;
use WeatherStation\UI\Forms\Handling as FormsRenderer;
use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\System\I18N\Handling as Intl;
use WeatherStation\SDK\Netatmo\Plugin\Collector as Netatmo_Collector;
use WeatherStation\SDK\Netatmo\Plugin\Initiator as Netatmo_Initiator;
use WeatherStation\SDK\Netatmo\Plugin\HCCollector as Netatmo_HCCollector;
use WeatherStation\SDK\Netatmo\Plugin\HCInitiator as Netatmo_HCInitiator;
use WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector as OWM_Base_Collector;
use WeatherStation\SDK\WeatherUnderground\Plugin\BaseCollector as WUG_Base_Collector;
use WeatherStation\SDK\WeatherUnderground\Plugin\StationCollector as WUG_Station_Collector;
use WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentCollector as OWM_Current_Collector;
use WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentInitiator as OpenWeatherMap_Current_Initiator;
use WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionInitiator as OpenWeatherMap_Pollution_Initiator;
use WeatherStation\SDK\WeatherUnderground\Plugin\StationInitiator as WeatherUnderground_Station_Initiator;
use WeatherStation\SDK\Clientraw\Plugin\StationInitiator as Clientraw_Station_Initiator;
use WeatherStation\SDK\Realtime\Plugin\StationInitiator as Realtime_Station_Initiator;



/**
 * The admin-specific functionality of the plugin.
 *
 * @package Admin
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Admin {

    use Schedule, Options, Arrays, FormsRenderer {
        FormsRenderer::get_service_name insteadof Arrays;
        FormsRenderer::get_module_type insteadof Arrays;
        FormsRenderer::get_fake_module_name insteadof Arrays;
        FormsRenderer::get_measurement_type insteadof Arrays;
    }

	private $Live_Weather_Station;
	private $version;

	private $reload = false;

    private $settings = array('general', 'services', 'display', 'thresholds', 'system');
    private $services = array('Netatmo', 'NetatmoHC', 'OpenWeatherMap', 'WeatherUnderground');
    private $service = 'Backend';

    private $_station = null;
    private $_dashboard = null;
    private $_services = null;
    private $_analytics = null;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 1.0.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Enqueues the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->Live_Weather_Station, LWS_ADMIN_URL.'css/live-weather-station-admin.min.css', array(), $this->version);
        wp_enqueue_style('live-weather-station-public.css', LWS_PUBLIC_URL.'css/live-weather-station-public.min.css', array(), $this->version);
        wp_enqueue_style('font-awesome.css', LWS_PUBLIC_URL.'css/font-awesome.min.css', array(), $this->version);
        wp_enqueue_style('weather-icons.css', LWS_PUBLIC_URL . 'css/weather-icons.min.css', array(), $this->version);
        wp_enqueue_style('weather-icons-wind.css', LWS_PUBLIC_URL . 'css/weather-icons-wind.min.css', array(), $this->version);
        wp_enqueue_style('thickbox');
        wp_enqueue_style('nv.d3.css', LWS_PUBLIC_URL.'css/nv.d3.min.css', array(), $this->version);
        wp_enqueue_style('cal-heatmap.css', LWS_PUBLIC_URL.'css/cal-heatmap.min.css', array(), $this->version);
    }

    /**
     * Enqueues the javascripts for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->Live_Weather_Station, LWS_ADMIN_URL.'js/live-weather-station-admin.min.js', array('jquery', 'postbox', 'thickbox'), $this->version, false);
        wp_enqueue_script('clipboard.js', LWS_ADMIN_URL.'js/clipboard.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('lws-lcd.js', LWS_PUBLIC_URL.'js/lws-lcd.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('raphael.js', LWS_PUBLIC_URL.'js/raphael.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('justgage.js', LWS_PUBLIC_URL.'js/justgage.min.js', array('raphael.js'), $this->version, false);
        wp_enqueue_script('tween.js', LWS_PUBLIC_URL.'js/tween.min.js', array(), $this->version, true);
        wp_enqueue_script('steelseries.js', LWS_PUBLIC_URL.'js/steelseries.min.js', array('tween.js'), $this->version, true);
        wp_enqueue_script('d3.v3.js', LWS_PUBLIC_URL.'js/d3.v3.min.js', array('jquery'), $this->version, true );
        wp_enqueue_script('nv.d3.v3.js', LWS_PUBLIC_URL.'js/nv.d3.v3.min.js', array('d3.v3.js'), $this->version, true );
        wp_enqueue_script('cal-heatmap.js', LWS_PUBLIC_URL.'js/cal-heatmap.min.js', array('d3.v3.js'), $this->version, true );
        wp_enqueue_script('colorbrewer.js', LWS_PUBLIC_URL.'js/colorbrewer.min.js', array(), $this->version, true );

    }

    /**
     * Initializes settings sections and fields.
     *
     * @since 3.0.0
     */
    public function init_settings() {
        add_settings_section('lws_general_section', null, array($this, 'general_section_callback'), 'lws_general');
        add_settings_section('lws_services_section', null, array($this, 'services_section_callback'), 'lws_services');
        add_settings_section('lws_display_section', null, array($this, 'display_section_callback'), 'lws_display');
        add_settings_section('lws_thresholds_section', null, array($this, 'thresholds_section_callback'), 'lws_thresholds');
        add_settings_section('lws_system_section', null, array($this, 'system_section_callback'), 'lws_system');
        add_settings_section('lws_maintenance_section', null, array($this, 'maintenance_section_callback'), 'lws_maintenance');
        add_settings_section('lws_tasks_section', null, array($this, 'tasks_section_callback'), 'lws_tasks');
        $this->init_system_settings();
        $this->init_display_settings();
        $this->init_thresholds_settings();

    }

    /**
     * Force resync data if it is needed (i.e. by a migration).
     *
     * @since 3.0.0
     */
    public function force_resync_if_needed() {
        if (get_option('live_weather_station_force_resync') == 'yes') {
            update_option('live_weather_station_force_resync', 'no');
            $this->sync_data(true);
        }
    }

    public function general_section_callback() {
        //echo '<p>General.</p>';
    }

    public function services_section_callback() {
        $h = InlineHelp::get(2, __('You can find help on these settings on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
        echo '<p>' . sprintf(__('In order to work properly, %s has to be connected to some services. You can manage here these connections.', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ' . $h . '</p>';
    }

    public function display_section_callback() {
        $h = InlineHelp::get(3, __('You can find help on these settings on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
        echo '<p>' . __('You can set here all the units and display options for controls and widgets.', 'live-weather-station') . ' ' . $h . '</p>';
    }

    public function thresholds_section_callback() {
        $h = InlineHelp::get(4, __('You can find help on these settings on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
        echo '<p>' . __('You can set here all the thresholds which define limits and alarms in some controls (LCD panel, gauges, meters, etc.).', 'live-weather-station') . ' ' . $h . '</p>';
    }

    public function system_section_callback() {
        $h = InlineHelp::get(5, __('You can find help on these settings on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
        echo '<p>' . sprintf(__('You can set here all the parameters related to the operation of the %s subsystems.', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ' . $h . '</p>';
    }

    public function maintenance_section_callback() {
        $h = InlineHelp::get(15, __('You can find help on these maintenance operations on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
        echo '<p>' . __('Here, you can make some maintenance operations that are not directly accessible elsewhere.', 'live-weather-station') . ' ' . $h . '</p>';
    }

    public function tasks_section_callback() {
        $h = '';//InlineHelp::get(15, __('You can find help on these maintenance operations on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
        echo '<p>' . __('Here, you can view all scheduled tasks, force their execution or reschedule them.', 'live-weather-station') . ' ' . $h . '</p>';
    }

    /**
     * Initializes system fields.
     *
     * @since 3.0.0
     */
    public function init_system_settings() {
        add_settings_field('lws_system_cache_manage', __('Cache mechanism', 'live-weather-station'),
            array($this, 'lws_system_cache_manage_callback'), 'lws_system', 'lws_system_section',
            array());
        register_setting('lws_system', 'lws_system_cache_manage');
        add_settings_field('lws_system_quota', __('API quota policy', 'live-weather-station'),
            array($this, 'lws_system_quota_callback'), 'lws_system', 'lws_system_section',
            array(__('Operation performed when the API usage exceeds quotas allowed by the services.', 'live-weather-station')));
        register_setting('lws_system', 'lws_system_quota');
        add_settings_field('lws_system_log_level', __('Logging policy', 'live-weather-station'),
            array($this, 'lws_system_log_level_callback'), 'lws_system', 'lws_system_section',
            array(__('Minimum level of severity that will be recorded in the events log.', 'live-weather-station')));
        register_setting('lws_system', 'lws_system_log_level');
        add_settings_field('lws_system_log_retention', '',
            array($this, 'lws_system_log_retention_callback'), 'lws_system', 'lws_system_section',
            array(__('Maximum number and maximum age of events stored in the events log.', 'live-weather-station')));
        register_setting('lws_system', 'lws_system_log_retention');
        add_settings_field('lws_system_auto_manage', __('Automatic management', 'live-weather-station'),
            array($this, 'lws_system_auto_manage_callback'), 'lws_system', 'lws_system_section',
            array());
        register_setting('lws_system', 'lws_system_auto_manage');
        add_settings_field('lws_system_time_shift_threshold', __('Servers time shift', 'live-weather-station'),
            array($this, 'lws_system_time_shift_threshold_callback'), 'lws_system', 'lws_system_section',
            array(__('Maximum allowed servers time shift before warning (useful for Netatmo accuracy).', 'live-weather-station')));
        register_setting('lws_system', 'lws_system_time_shift_threshold');
        add_settings_field('lws_system_show_technical', __('Stations views', 'live-weather-station'),
            array($this, 'lws_system_show_technical_callback'), 'lws_system', 'lws_system_section',
            array(__('If you check this, stations views will display detailed technical information for each module.', 'live-weather-station')));
        register_setting('lws_system', 'lws_system_show_technical');
        add_settings_field('lws_system_redirect_links', __('Links', 'live-weather-station'),
            array($this, 'lws_system_redirect_links_callback'), 'lws_system', 'lws_system_section',
            array());
        register_setting('lws_system', 'lws_system_redirect_links');
        add_settings_field('lws_system_special', __('Special', 'live-weather-station'),
            array($this, 'lws_system_special_callback'), 'lws_system', 'lws_system_section',
            array());
        register_setting('lws_system', 'lws_system_special');
        add_settings_field('lws_system_analytics_cutoff', __('Performance data cutoff', 'live-weather-station'),
            array($this, 'lws_system_analytics_cutoff_callback'), 'lws_system', 'lws_system_section',
            array(__('Maximum age of performance data displayed in statistical reports.', 'live-weather-station')));
        register_setting('lws_system', 'lws_system_analytics_cutoff');
    }

    /**
     * Initializes system fields.
     *
     * @since 3.0.0
     */
    public function init_display_settings() {
        add_settings_field('lws_display_temperature_unit', __('Temperature unit', 'live-weather-station'),
            array($this, 'lws_display_temperature_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Unit of measurement in which temperatures are expressed.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_temperature_unit');
        add_settings_field('lws_display_pressure_unit', __('Pressure unit', 'live-weather-station'),
            array($this, 'lws_display_pressure_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Unit of measurement in which pressures are expressed.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_pressure_unit');
        add_settings_field('lws_display_wind_strength_unit', __('Wind speed unit', 'live-weather-station'),
            array($this, 'lws_display_wind_strength_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Unit of measurement in which wind speeds are expressed.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_wind_strength_unit');
        add_settings_field('lws_display_gas_unit', __('Gases', 'live-weather-station'),
            array($this, 'lws_display_gas_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Way to express the concentrations of gases.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_gas_unit');
        add_settings_field('lws_display_distance_unit', __('Distances', 'live-weather-station'),
            array($this, 'lws_display_distance_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Units system in which distances are expressed.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_distance_unit');
        add_settings_field('lws_display_altitude_unit', __('Altitudes', 'live-weather-station'),
            array($this, 'lws_display_altitude_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Units system in which altitudes are expressed.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_altitude_unit');
        add_settings_field('lws_display_rain_snow_unit', __('Rain & snow', 'live-weather-station'),
            array($this, 'lws_display_rain_snow_unit_callback'), 'lws_display', 'lws_display_section',
            array(__('Units system in which rain and snow quantities are expressed.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_rain_snow_unit');
        add_settings_field('lws_display_viewing_options', __('Computed values', 'live-weather-station'),
            array($this, 'lws_display_viewing_options_callback'), 'lws_display', 'lws_display_section',
            array(__('Check this if you want the controls and widgets display the computed values in addition to the measured data.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_viewing_options');
        add_settings_field('lws_display_windsemantics', __('Wind icon', 'live-weather-station'),
            array($this, 'lws_display_windsemantics_callback'), 'lws_display', 'lws_display_section',
            array(__('Semantics of the icon representing the wind direction in widgets.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_windsemantics');
        add_settings_field('lws_display_moonicons', __('Moon icon set', 'live-weather-station'),
            array($this, 'lws_display_moonicons_callback'), 'lws_display', 'lws_display_section',
            array(__('Type of icons to illustrate moon age and phase in widgets.', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_moonicons');
        add_settings_field('lws_display_minmax', __('Gauges boundaries', 'live-weather-station'),
            array($this, 'lws_display_minmax_callback'), 'lws_display', 'lws_display_section',
            array(sprintf(__('By default, min/max boundaries in controls are fixed. If you check this, %s will try to adapt it to the amplitude of the measures.', 'live-weather-station'), LWS_PLUGIN_NAME)));
        register_setting('lws_display', 'lws_display_minmax');
        add_settings_field('lws_display_obsolescence', __('Data obsolescence', 'live-weather-station'),
            array($this, 'lws_display_obsolescence_callback'), 'lws_display', 'lws_display_section',
            array(__('Duration beyond which a data is considered stale (and will therefore neither shown nor used in computations).', 'live-weather-station')));
        register_setting('lws_display', 'lws_display_obsolescence');
    }

    /**
     * Initializes thresholds fields.
     *
     * @since 3.0.0
     */
    public function init_thresholds_settings() {
        $thresholds = self::get_thresholds();
        foreach ($thresholds as $threshold) {
            add_settings_field('lws_thresholds_' . $threshold, $this->get_measurement_type($threshold, false, ($threshold == 'rain' ? 'namodule3' : 'NAMain')),
                array($this, 'lws_thresholds_callback'), 'lws_thresholds', 'lws_thresholds_section',
                array($threshold));
            register_setting('lws_thresholds', 'lws_thresholds_' . $threshold);
        }
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.2.0
     */
    public function lws_system_quota_callback($args) {
        echo $this->field_select($this->get_quota_js_array(), get_option('live_weather_station_quota_mode'), 'lws_system_quota', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_log_level_callback($args) {
        echo $this->field_select($this->get_log_level_js_array(), get_option('live_weather_station_logger_level'), 'lws_system_log_level', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_log_retention_callback($args) {
        $nmbrs = array();
        $nmbrs[] = array('value' => get_option('live_weather_station_logger_rotate'),
            'id' => 'lws_system_log_rotate',
            'min' => 1000,
            'max' => 100000,
            'step' => 1000,
            'unit' => __('events', 'live-weather-station'));
        $nmbrs[] = array('value' => get_option('live_weather_station_logger_retention'),
            'id' => 'lws_system_log_retention',
            'min' => 2,
            'max' => 400,
            'step' => 1,
            'unit' => __('days', 'live-weather-station'));
        echo $this->field_double_input_number($nmbrs, $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_auto_manage_callback($args) {
        $description = sprintf(__('Check this to let %s manage its own updates (strongly recommended).', 'live-weather-station'), LWS_PLUGIN_NAME);
        if (!Manager::is_updatable()) {
            $description .= '<br/>' . __('Note that your WordPress configuration does not allow you to use this option.', 'live-weather-station');
        }
        $cbxs = array();
        $cbxs[] = array('text' => __('Netatmo provisioning', 'live-weather-station'),
            'id' => 'lws_system_auto_manage_netatmo',
            'checked' => (bool)get_option('live_weather_station_auto_manage_netatmo'),
            'description' => sprintf(__('Check this to let %s manage Netatmo stations for you (add, remove, etc.).', 'live-weather-station'), LWS_PLUGIN_NAME));
        $cbxs[] = array('text' => __('Plugin updates', 'live-weather-station'),
            'id' => 'lws_system_auto_update',
            'checked' => (bool)get_option('live_weather_station_auto_update'),
            'more' => (Manager::is_updatable()?'':'disabled'),
            'description' => $description);
        echo $this->field_multi_checkbox($cbxs);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_cache_manage_callback($args) {
        $cbxs = array();
        $cbxs[] = array('text' => __('Cache controls', 'live-weather-station'),
            'id' => 'lws_system_frontend_cache',
            'checked' => (bool)get_option('live_weather_station_frontend_cache'),
            'description' => sprintf(__('Check this to activate the cache manager of %s for the controls rendering (gauges, meters, etc.).', 'live-weather-station'), LWS_PLUGIN_NAME));
        $cbxs[] = array('text' => __('Cache widgets', 'live-weather-station'),
            'id' => 'lws_system_widget_cache',
            'checked' => (bool)get_option('live_weather_station_widget_cache'),
            'description' => sprintf(__('Check this to activate the cache manager of %s for the widgets rendering.', 'live-weather-station'), LWS_PLUGIN_NAME));
        $cbxs[] = array('text' => __('Cache backend features', 'live-weather-station'),
            'id' => 'lws_system_backend_cache',
            'checked' => (bool)get_option('live_weather_station_backend_cache'),
            'description' => sprintf(__('Check this to activate the cache manager of %s for backend rendering (admin panel, dashboard, station view, etc.).', 'live-weather-station'), LWS_PLUGIN_NAME));
        $cbxs[] = array('text' => __('Do not cache generated text files', 'live-weather-station'),
            'id' => 'lws_system_txt_cache_bypass',
            'checked' => (bool)get_option('live_weather_station_txt_cache_bypass'),
            'description' => __('Check this to prevent caching of the generated text files. Required on some server configurations, particularly when using Varnish.', 'live-weather-station'));
        echo $this->field_multi_checkbox($cbxs);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.2.0
     */
    public function lws_system_special_callback($args) {
        $cbxs = array();
        $cbxs[] = array('text' => __('I love data analytics', 'live-weather-station'),
            'id' => 'lws_system_show_analytics',
            'checked' => (bool)get_option('live_weather_station_show_analytics'),
            'description' => sprintf(__('Check this only if you really love data analytics and visualization.', 'live-weather-station'), LWS_PLUGIN_NAME));
        $cbxs[] = array('text' => __('I want to be a time sorcerer', 'live-weather-station'),
            'id' => 'lws_system_show_tasks',
            'checked' => (bool)get_option('live_weather_station_show_tasks'),
            'description' => sprintf(__('Check this to get access to the scheduled tasks tab.', 'live-weather-station'), LWS_PLUGIN_NAME));
        echo $this->field_multi_checkbox($cbxs);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_time_shift_threshold_callback($args) {
        echo $this->field_input_number(get_option('live_weather_station_time_shift_threshold'), 'lws_system_time_shift_threshold', 0, 300, 1, $args[0], __('seconds', 'live-weather-station'));
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.2.0
     */
    public function lws_system_analytics_cutoff_callback($args) {
        echo $this->field_input_number(get_option('live_weather_station_analytics_cutoff'), 'lws_system_analytics_cutoff', 3, 30, 1, $args[0], __('days', 'live-weather-station'));
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_redirect_links_callback($args) {
        $cbxs = array();
        $cbxs[] = array('text' => __('Open internal links in a new window', 'live-weather-station'),
                        'id' => 'lws_system_redirect_internal_links',
                        'checked' => (bool)get_option('live_weather_station_redirect_internal_links'),
                        'description' => __('Check this to have all auxiliary internal links (like events, etc.) opened in a new window.', 'live-weather-station'));
        $cbxs[] = array('text' => __('Open external links in a new window', 'live-weather-station'),
                        'id' => 'lws_system_redirect_external_links',
                        'checked' => (bool)get_option('live_weather_station_redirect_external_links'),
                        'description' => __('Check this to have all external links (like help, etc.) opened in a new window.', 'live-weather-station'));
        echo $this->field_multi_checkbox($cbxs);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_system_show_technical_callback($args) {
        echo $this->field_checkbox(__('Display technical information', 'live-weather-station'), 'lws_system_show_technical', (bool)get_option('live_weather_station_show_technical'), $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_temperature_unit_callback($args) {
        echo $this->field_select($this->get_temperature_unit_name_array(), get_option('live_weather_station_unit_temperature'), 'lws_display_temperature_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_pressure_unit_callback($args) {
        echo $this->field_select($this->get_pressure_unit_name_array(), get_option('live_weather_station_unit_pressure'), 'lws_display_pressure_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_wind_strength_unit_callback($args) {
        echo $this->field_select($this->get_wind_speed_unit_name_array(), get_option('live_weather_station_unit_wind_strength'), 'lws_display_wind_strength_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_gas_unit_callback($args) {
        echo $this->field_select($this->get_gas_unit_name_array(), get_option('live_weather_station_unit_gas'), 'lws_display_gas_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_distance_unit_callback($args) {
        echo $this->field_radio($this->get_distance_unit_name_array(), get_option('live_weather_station_unit_distance'), 'lws_display_distance_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_altitude_unit_callback($args) {
        echo $this->field_radio($this->get_altitude_unit_name_array(), get_option('live_weather_station_unit_altitude'), 'lws_display_altitude_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_rain_snow_unit_callback($args) {
        echo $this->field_radio($this->get_altitude_unit_name_array(), get_option('live_weather_station_unit_rain_snow'), 'lws_display_rain_snow_unit', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_viewing_options_callback($args) {
        echo $this->field_checkbox(__('Display it in controls and widgets', 'live-weather-station'), 'lws_display_viewing_options', !(bool)get_option('live_weather_station_measure_only'), $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_windsemantics_callback($args) {
        echo $this->field_radio($this->get_windsemantics_array(), get_option('live_weather_station_wind_semantics'), 'lws_display_windsemantics', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_moonicons_callback($args) {
        echo $this->field_radio($this->get_moonicons_array(), get_option('live_weather_station_moon_icons'), 'lws_display_moonicons', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_minmax_callback($args) {
        echo $this->field_checkbox(__('Adjusted whenever possible', 'live-weather-station'), 'lws_display_minmax', (bool)get_option('live_weather_station_min_max_mode'), $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_display_obsolescence_callback($args) {
        echo $this->field_select($this->get_obsolescence_array(), get_option('live_weather_station_obsolescence'), 'lws_display_obsolescence', $args[0]);
    }

    /**
     * Renders the interface elements for the corresponding field.
     *
     * @param array $args An array of arguments which first element is the description to be displayed next to the control.
     * @since 3.0.0
     */
    public function lws_thresholds_callback($args) {
        echo $this->field_thresholds($args[0]);
    }

    /**
     * Save options from a specific section of the settings page.
     *
     * @param string $section The section to save.
     * @return boolean True if it's a success, false otherwise.
     * @since 3.0.0
     */
    private function save_options($section) {
        $result = true;
        $this->reload = false;
        if ($section == 'display') {
            if (array_key_exists('submit', $_POST)) {
                update_option('live_weather_station_unit_temperature', (integer)$_POST['lws_display_temperature_unit']);
                update_option('live_weather_station_unit_pressure', (integer)$_POST['lws_display_pressure_unit']);
                update_option('live_weather_station_unit_wind_strength', (integer)$_POST['lws_display_wind_strength_unit']);
                update_option('live_weather_station_unit_gas', (integer)$_POST['lws_display_gas_unit']);
                update_option('live_weather_station_unit_distance', (integer)$_POST['lws_display_distance_unit']);
                update_option('live_weather_station_unit_altitude', (integer)$_POST['lws_display_altitude_unit']);
                update_option('live_weather_station_unit_rain_snow', (integer)$_POST['lws_display_rain_snow_unit']);
                update_option('live_weather_station_measure_only', (!array_key_exists('lws_display_viewing_options', $_POST) ? 1 : 0));
                update_option('live_weather_station_wind_semantics', (integer)$_POST['lws_display_windsemantics']);
                update_option('live_weather_station_moon_icons', (integer)$_POST['lws_display_moonicons']);
                update_option('live_weather_station_min_max_mode', (array_key_exists('lws_display_minmax', $_POST) ? 1 : 0));
                update_option('live_weather_station_obsolescence', (integer)$_POST['lws_display_obsolescence']);
            }
            else {
                $result = false;
            }
        }
        if ($section == 'thresholds') {
            if (array_key_exists('submit', $_POST)) {
                $thresholds = self::get_thresholds();
                foreach ($thresholds as $threshold) {
                    foreach (array('_min_value', '_max_value', '_min_alarm', '_max_alarm') as $type) {
                        if (array_key_exists('lws_thresholds_' . $threshold . $type, $_POST)) {
                            update_option('live_weather_station_' . $threshold . $type, $this->convert_value($_POST['lws_thresholds_' . $threshold . $type], $threshold));
                        }
                    }
                }
            }
            else {
                $result = false;
            }
        }
        if ($section == 'system') {
            $save_auto = get_option('live_weather_station_auto_manage_netatmo');
            $analytics = get_option('live_weather_station_show_analytics');
            $cutoff = get_option('live_weather_station_analytics_cutoff');
            if (array_key_exists('submit', $_POST)) {
                update_option('live_weather_station_logger_level', (integer)$_POST['lws_system_log_level']);
                update_option('live_weather_station_logger_rotate', (integer)$_POST['lws_system_log_rotate']);
                update_option('live_weather_station_logger_retention', (integer)$_POST['lws_system_log_retention']);
                update_option('live_weather_station_txt_cache_bypass', (array_key_exists('lws_system_txt_cache_bypass', $_POST) ? 1 : 0));
                update_option('live_weather_station_frontend_cache', (array_key_exists('lws_system_frontend_cache', $_POST) ? 1 : 0));
                update_option('live_weather_station_widget_cache', (array_key_exists('lws_system_widget_cache', $_POST) ? 1 : 0));
                update_option('live_weather_station_backend_cache', (array_key_exists('lws_system_backend_cache', $_POST) ? 1 : 0));
                update_option('live_weather_station_redirect_internal_links', (array_key_exists('lws_system_redirect_internal_links', $_POST) ? 1 : 0));
                update_option('live_weather_station_redirect_external_links', (array_key_exists('lws_system_redirect_external_links', $_POST) ? 1 : 0));
                update_option('live_weather_station_auto_manage_netatmo', (array_key_exists('lws_system_auto_manage_netatmo', $_POST) ? 1 : 0));
                update_option('live_weather_station_auto_update', (array_key_exists('lws_system_auto_update', $_POST) ? 1 : 0));
                update_option('live_weather_station_time_shift_threshold', (integer)$_POST['lws_system_time_shift_threshold']);
                update_option('live_weather_station_show_technical', (array_key_exists('lws_system_show_technical', $_POST) ? 1 : 0));
                update_option('live_weather_station_show_analytics', (array_key_exists('lws_system_show_analytics', $_POST) ? 1 : 0));
                update_option('live_weather_station_show_tasks', (array_key_exists('lws_system_show_tasks', $_POST) ? 1 : 0));
                update_option('live_weather_station_analytics_cutoff', (integer)$_POST['lws_system_analytics_cutoff']);
                update_option('live_weather_station_quota_mode', (integer)$_POST['lws_system_quota']);
                if (!$save_auto && get_option('live_weather_station_auto_manage_netatmo')) {
                    $this->get_netatmo(true);
                    $this->get_netatmohc(true);
                }
                if ($analytics  != get_option('live_weather_station_show_analytics')) {
                    $this->reload = true;
                }
                if ($cutoff  != get_option('live_weather_station_analytics_cutoff')) {
                    Cache::flush_performance(false);
                }
            }
            else {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Reset options to defaults from a specific section of the settings page.
     *
     * @param string $section The section to reset.
     * @return boolean True if it's a success, false otherwise.
     * @since 3.0.0
     */
    private function reset_to_defaults($section) {
        $result = true;
        if ($section == 'general') {
            $result = false;
        }
        if ($section == 'services') {
            $result = false;
        }
        if ($section == 'display') {
            self::switch_to_metric(true);
        }
        if ($section == 'thresholds') {
            self::init_thresholds_options();
        }
        if ($section == 'system') {
            self::init_system_options();
        }
        return $result;
    }

    /**
     * Check options and security from settings page of the plugin.
     *
     * @return boolean True if it's a success, false otherwise.
     * @since 3.0.0
     */
    private function check_options() {
        if (empty($_POST)) {
            return false;
        }
        $result = false;
        $sec = false;
        if (array_key_exists('option_page', $_POST)) {
            $section = $_POST['option_page'];
        }
        else {
            $section = 'unknown';
        }
        $action = '';
        if (array_key_exists('action', $_POST)) {
            $action = $_POST['action'];
        }
        if (array_key_exists('reset', $_POST)) {
            $action = 'reset';
        }
        if (array_key_exists('update', $_POST)) {
            $action = 'update';
        }
        if (array_key_exists('_wpnonce', $_POST)) {
            foreach ($this->settings as $s) {
                $sec = wp_verify_nonce($_POST['_wpnonce'], $s . '-options');
                if ($sec) { break;}
            }
        }
        switch ($section) {
            case 'general' : $settings_string = __('General settings', 'live-weather-station'); break;
            case 'services' : $settings_string = __('Services settings', 'live-weather-station'); break;
            case 'display' : $settings_string = __('Display settings', 'live-weather-station'); break;
            case 'thresholds' : $settings_string = __('Thresholds settings', 'live-weather-station'); break;
            case 'system' : $settings_string = __('System settings', 'live-weather-station'); break;
            default: $settings_string = __('Unknown settings', 'live-weather-station');
        }
        if ($sec) {
            if ($action == 'update') {
                if ($result = $this->save_options($section)) {
                    $message = __('%s have been correctly updated.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . lcfirst($settings_string) . '</em>');
                    if ($this->reload) {
                        $current_url = get_admin_page_url('lws-settings', null, $section);
                        $submessage = __('In order for the main menu to reflect the updated settings, please <a href="%s">refresh</a> the page', 'live-weather-station').'&hellip;';
                        $message .= '<br/>' . sprintf($submessage, $current_url);
                        $this->reload = false;
                    }
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::info($this->service, null, null, null, null, null, 0, 'Settings for '. $section . ' category has been correctly updated by an admin.');
                }
                else {
                    $message = __('%s have not been updated. Please try again.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . lcfirst($settings_string) . '</em>');
                    add_settings_error('lws_nonce_error', 200, $message, 'error');
                    Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to correctly update settings for '. $section . ' category.');
                }
            }
            if ($action == 'reset') {
                if ($result = $this->reset_to_defaults($section)) {
                    $message = __('%s have been correctly reset to defaults.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . lcfirst($settings_string) . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::info($this->service, null, null, null, null, null, 0, 'Settings for '. $section . ' category has been correctly reset to defaults by an admin.');
                }
                else {
                    $message = __('%s have not been reset to defaults. Please try again.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . lcfirst($settings_string) . '</em>');
                    add_settings_error('lws_nonce_error', 200, $message, 'error');
                    Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to correctly reset to defaults settings for '. $section . ' category.');
                }
            }

        }
        elseif ($section == 'services' && $action == 'manage-connection') {
            $this->manage_connection();
        }
        else {
            $message = __('%s has not been updated. Please try again.', 'live-weather-station');
            $message = sprintf($message, '<em>' . lcfirst($settings_string) . '</em>');
            add_settings_error('lws_nonce_error', 403, $message, 'error');
            Logger::critical('Security', null, null, null, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
            Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to securely update settings for '. $section . ' category.');
        }
        return $result;
    }

    /**
     * Returns the manage_options cap.
     *
     * @return mixed
     */
    private function get_manage_options_cap() {
        return apply_filters('lws_manage_options_capability', 'manage_options');
    }
    
    /**
     * Set Weather Station admin menu and submenus in the main dashboard menu.
     *
     * @since    3.0.0
     */
    public function lws_admin_menu() {
        $icon_svg = SVG::get_base64_menu_icon();
        $manage_options_cap = $this->get_manage_options_cap();
        if (REQUIREMENTS_OK) {
            add_menu_page(LWS_FULL_NAME . ' - ' . __('Dashboard', 'live-weather-station'), LWS_PLUGIN_NAME, $manage_options_cap, 'lws-dashboard', array($this, 'lws_load_admin_page'), $icon_svg, '99.001357');
            $dashboard = add_submenu_page('lws-dashboard', LWS_FULL_NAME . ' - ' . __('Dashboard', 'live-weather-station'), __('Dashboard', 'live-weather-station'), $manage_options_cap, 'lws-dashboard', array($this, 'lws_load_admin_page'));
            $this->_dashboard = new Dashboard(LWS_PLUGIN_NAME, LWS_VERSION, $dashboard);
            $stations = add_submenu_page('lws-dashboard', LWS_FULL_NAME . ' - ' . __('Stations', 'live-weather-station'), __('Stations', 'live-weather-station'), $manage_options_cap, 'lws-stations', array($this, 'lws_load_admin_page'));
            $this->_station = new Station(LWS_PLUGIN_NAME, LWS_VERSION, $stations);
            $events = add_submenu_page('lws-dashboard', LWS_FULL_NAME . ' - ' . __('Events log', 'live-weather-station'), __('Events', 'live-weather-station'), $manage_options_cap, 'lws-events', array($this, 'lws_load_admin_page'));
            $settings = add_submenu_page('lws-dashboard', LWS_FULL_NAME . ' - ' . __('Settings', 'live-weather-station'), __('Settings', 'live-weather-station'), $manage_options_cap, 'lws-settings', array($this, 'lws_load_admin_page'));
            $this->_services = new Services(LWS_PLUGIN_NAME, LWS_VERSION, $settings);
            if ((bool)get_option('live_weather_station_show_analytics')) {
                $analytics = add_submenu_page('lws-dashboard', LWS_FULL_NAME . ' - ' . __('Analytics', 'live-weather-station'), __('Analytics', 'live-weather-station'), $manage_options_cap, 'lws-analytics', array($this, 'lws_load_admin_page'));
                $this->_analytics = new Analytics(LWS_PLUGIN_NAME, LWS_VERSION, $analytics);
            }

            InlineHelp::set_contextual_help('load-' . $dashboard, 'dashboard');
            InlineHelp::set_contextual_help('load-' . $settings, 'settings');
            InlineHelp::set_contextual_help('load-' . $stations, 'stations');
            InlineHelp::set_contextual_help('load-' . $events, 'events');
        }
        else {
            add_menu_page(LWS_FULL_NAME . ' - ' . __('Requirements', 'live-weather-station'), LWS_PLUGIN_NAME, $manage_options_cap, 'lws-requirements', array($this, 'lws_load_admin_page'), $icon_svg, '99.001357');
            $requirements = add_submenu_page('lws-requirements', LWS_FULL_NAME . ' - ' . __('Requirements', 'live-weather-station'), __('Requirements', 'live-weather-station'), $manage_options_cap, 'lws-requirements', array($this, 'lws_load_admin_page'));
            $events = add_submenu_page('lws-requirements', LWS_FULL_NAME . ' - ' . __('Events log', 'live-weather-station'), __('Events', 'live-weather-station'), $manage_options_cap, 'lws-events', array($this, 'lws_load_admin_page'));
            InlineHelp::set_contextual_help('load-' . $requirements, 'requirements');
            InlineHelp::set_contextual_help('load-' . $events, 'events');
        }
    }

    /**
     * Load the right admin page.
     *
     * @since 3.0.0
     */
    public function lws_load_admin_page() {
        $page = filter_input(INPUT_GET, 'page');
        if (!($tab = filter_input(INPUT_GET, 'tab'))) {
            $tab = filter_input(INPUT_POST, 'tab');
        }
        if (!($action = filter_input(INPUT_GET, 'action'))) {
            $action = filter_input(INPUT_POST, 'action');
        }
        if (!($service = filter_input(INPUT_GET, 'service'))) {
            $service = filter_input(INPUT_POST, 'service');
        }
        $dashboard = false;
        if (!($dashboard = (bool)filter_input(INPUT_GET, 'dashboard'))) {
            $dashboard = (bool)filter_input(INPUT_POST, 'dashboard');
        }
        if (!($id = filter_input(INPUT_GET, 'id'))) {
            $id = filter_input(INPUT_POST, 'id');
        }
        $args = array();

        switch ($page) {
            case 'lws-events':
                $log_entry = filter_input(INPUT_GET, 'log-entry');
                if (isset($log_entry) && $log_entry != 0) {
                    $view = 'log-detail';
                    $log_array = $this->get_log_detail($log_entry);
                    if (is_array($log_array)) {
                        $log = $log_array[0];
                        $log['displayed_timestamp'] = $this->get_date_from_mysql_utc($log['timestamp'], '', 'Y-m-d H:i:s') ;
                        $log['displayed_timestamp'] .= ' (' . $this->get_time_diff_from_mysql_utc($log['timestamp']) .')';
                    }
                    else {
                        $log = array();
                    }
                }
                else {
                    $view = 'list-table-logs';
                    $log = array();
                }
                $args = compact('log');
                break;
            case 'lws-stations':
            case 'lws-dashboard':
                if ($page == 'lws-stations') {
                    $view = 'list-table-stations';
                }
                else {
                    $view = 'dashboard';
                }
                if ($action == 'changelog') {
                    $view = 'changelog';
                }
                if ($action == 'configuration') {
                    $view = 'configuration';
                }
                if ($service == 'station' && ($tab == 'edit' || $tab == 'view') && $action == 'manage') {
                    $view = 'station';
                }
                if ($service != '' && $tab != '' && $action == 'form') {
                    $view = $action . '-' . $tab . '-' . $service ;
                    switch (strtolower($service)) {
                        case 'location':
                            if ($id) {
                                $station = $this->get_station_informations_by_guid($id);
                            }
                            else {
                                $station = $this->get_loc_station();
                            }
                            $countries = $this->get_country_names();
                            $timezones = $this->get_timezones_js_array();
                            $error = 0;
                            $args = compact('station', 'countries', 'timezones', 'error', 'dashboard');
                            break;
                        case 'clientraw':
                            if ($id) {
                                $station = $this->get_station_informations_by_guid($id);
                            }
                            else {
                                $station = $this->get_raw_station();
                            }
                            $countries = $this->get_country_names();
                            $timezones = $this->get_timezones_js_array();
                            $servertypes = $this->get_server_type_array();
                            $models = $this->get_models_array();
                            $error = 0;
                            $error_message = '';
                            $args = compact('station', 'countries', 'timezones', 'error', 'error_message', 'servertypes', 'models', 'dashboard');
                            break;
                        case 'realtime':
                            if ($id) {
                                $station = $this->get_station_informations_by_guid($id);
                            }
                            else {
                                $station = $this->get_real_station();
                            }
                            $countries = $this->get_country_names();
                            $timezones = $this->get_timezones_js_array();
                            $servertypes = $this->get_server_type_array();
                            $models = $this->get_models_array();
                            $error = 0;
                            $error_message = '';
                            $args = compact('station', 'countries', 'timezones', 'error', 'error_message', 'servertypes', 'models', 'dashboard');
                            break;
                        case 'station':
                            $station = $this->get_station_informations_by_guid($id);
                            $station['txt_location'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
                            $station['txt_timezone'] = $this->output_timezone($station['loc_timezone']);
                            $error = array();
                            $args = compact('station', 'error');
                            break;
                        case 'weatherunderground':
                            if ($id) {
                                $station = $this->get_station_informations_by_guid($id);
                            }
                            else {
                                $station = $this->get_wug_station();
                            }
                            $models = $this->get_models_array();
                            $args = compact('station', 'models', 'dashboard');
                            break;
                        default:
                            $args = compact('dashboard');
                    }
                }
                if ($service != '' && ($tab == 'add' || $tab == 'add-edit') && $action == 'do') {
                    switch (strtolower($service)) {
                        case 'netatmo':
                            if (array_key_exists('add-netatmo', $_POST)) {
                                $this->add_netatmo($id);
                                if ($dashboard) {
                                    $view = 'dashboard' ;
                                }
                            }
                            if (array_key_exists('add-netatmohc', $_POST)) {
                                $this->add_netatmo($id, true);
                                if ($dashboard) {
                                    $view = 'dashboard' ;
                                }
                            }
                            break;
                        case 'location':
                            if (array_key_exists('add-edit-loc', $_POST)) {
                                $station = $this->add_loc();
                                $error = 0;
                                $countries = $this->get_country_names();
                                $timezones = $this->get_timezones_js_array();
                                if (array_key_exists('error', $station)) {
                                    $error = $station['error'];
                                    unset($station['error']);
                                }
                                if ($error == 0) {
                                    if ($dashboard) {
                                        $view = 'dashboard' ;
                                    }
                                }
                                else {
                                    $view = 'form-add-edit-location' ;
                                    $args = compact('station', 'countries', 'timezones', 'error', 'dashboard');
                                }
                            }
                            break;
                        case 'clientraw':
                            if (array_key_exists('add-edit-raw', $_POST)) {
                                $station = $this->add_raw();
                                $error = 0;
                                $countries = $this->get_country_names();
                                $timezones = $this->get_timezones_js_array();
                                $servertypes = $this->get_server_type_array();
                                $models = $this->get_models_array();
                                if (array_key_exists('error', $station)) {
                                    $error = $station['error'];
                                    unset($station['error']);
                                }
                                if (array_key_exists('message', $station)) {
                                    $error_message = $station['message'];
                                    unset($station['message']);
                                }
                                if ($error == 0) {
                                    if ($dashboard) {
                                        $view = 'dashboard' ;
                                    }
                                }
                                else {
                                    $view = 'form-add-edit-clientraw' ;
                                    $args = compact('station', 'countries', 'timezones', 'error', 'error_message', 'servertypes', 'models', 'dashboard');
                                }
                            }
                            break;
                        case 'realtime':
                            if (array_key_exists('add-edit-real', $_POST)) {
                                $station = $this->add_real();
                                $error = 0;
                                $countries = $this->get_country_names();
                                $timezones = $this->get_timezones_js_array();
                                $servertypes = $this->get_server_type_array();
                                $models = $this->get_models_array();
                                if (array_key_exists('error', $station)) {
                                    $error = $station['error'];
                                    unset($station['error']);
                                }
                                if (array_key_exists('message', $station)) {
                                    $error_message = $station['message'];
                                    unset($station['message']);
                                }
                                if ($error == 0) {
                                    if ($dashboard) {
                                        $view = 'dashboard' ;
                                    }
                                }
                                else {
                                    $view = 'form-add-edit-realtime' ;
                                    $args = compact('station', 'countries', 'timezones', 'error', 'error_message', 'servertypes', 'models', 'dashboard');
                                }
                            }
                            break;
                        case 'weatherunderground':
                            if (array_key_exists('add-edit-wug', $_POST)) {
                                $this->add_wug();
                                if ($dashboard) {
                                    $view = 'dashboard' ;
                                }
                            }
                            break;
                    }
                }
                if ($service == 'station' && $tab == 'delete' && $action == 'do') {
                    if (array_key_exists('delete-station', $_POST)) {
                        $this->delete_station($id);
                    }
                }
                break;
            case 'lws-settings':
                $view = 'settings';
                $args = array();
                switch ($action) {
                    case 'switch-simplified': $this->switch_simplified(); break;
                    case 'switch-extended': $this->switch_extended(); break;
                    case 'switch-metric': $this->switch_metric(); break;
                    case 'switch-imperial': $this->switch_imperial(); break;
                    case 'switch-full-translation': $this->switch_full_translation(); break;
                    case 'switch-partial-translation': $this->switch_partial_translation(); break;
                    case 'reset-dashboard': $this->reset_dashboard_meta(); break;
                    case 'reset-services': $this->reset_services_meta(); break;
                    case 'reset-stations': $this->reset_stations_meta(); break;
                    case 'reset-analytics': $this->reset_analytics_meta(); break;
                    case 'purge-data': $this->purge_data(); break;
                    case 'sync-data': $this->sync_data(); break;
                    case 'reset-cache': $this->reset_cache(); break;
                    case 'purge-log': $this->reset_log(); break;
                    case 'cron-force': $this->cron_reschedule(true); break;
                    case 'cron-reschedule': $this->cron_reschedule(); break;
                    case 'relaunch-watchdog': $this->relaunch_watchdog(); break;
                    default: $this->check_options();
                }
                break;
            case 'lws-requirements':
                $view = 'requirements';
                break;
            case 'lws-analytics':
                $view = 'analytics';
                break;
            default:
                $view = 'dashboard';
                break;
        }
        if ($view != 'dashboard' && $view != 'station') {
            $this->lws_view_admin_page($view, $args);
        }
        elseif ($view == 'station') {
            $this->_station->get();
        }
        elseif ($view == 'dashboard') {
            $this->_dashboard->get();
        }
    }

    /**
     * Load the named partial with its values.
     *
     * @param   string  $name   The name of the partial to load.
     * @param   array   $args   The values to pass to the view.
     * @since    3.0.0
     */
    protected function lws_view_admin_page($name, array $args = array()) {
        foreach ($args as $key => $val) {
            $$key = $val;
        }
        $n = explode('-', $name);
        $f = '';
        if (count($n) > 0) {
            foreach ($n as $s) {
                $f .= ucfirst($s);
            }
        }
        $s = $f;
        $f = LWS_ADMIN_DIR.'partials/'.$f.'.php';
        if (file_exists($f)) {
            wp_dequeue_script('media-upload');
            include($f);
        }
        else {
            include(LWS_ADMIN_DIR.'partials/404.php');
            Logger::error('Security', null, null, null, null, null, null, 'An attempt was made to load an admin view which does not exists: ' . $s . '.');
        }
    }

    /**
     * Switch to simplified mode.
     *
     * @since 3.0.0
     */
    private function switch_simplified() {
        update_option('live_weather_station_advanced_mode', 0);
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s now runs in simplified mode.', 'live-weather-station'), LWS_PLUGIN_NAME), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Weather Station now runs in simplified mode.');
        $this->switch_metric();
    }

    /**
     * Switch to extended mode.
     *
     * @since 3.0.0
     */
    private function switch_extended() {
        update_option('live_weather_station_advanced_mode', 1);
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s now runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Weather Station now runs in extended mode.');
    }

    /**
     * Switch to metric mode.
     *
     * @since 3.0.0
     */
    private function switch_metric() {
        self::switch_to_metric();
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s now displays its data in the metric system.', 'live-weather-station'), LWS_PLUGIN_NAME), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Weather Station now displays its data in the metric system.');
    }

    /**
     * Switch to imperial mode.
     *
     * @since 3.0.0
     */
    private function switch_imperial() {
        self::switch_to_imperial();
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s now displays its data in the imperial system.', 'live-weather-station'), LWS_PLUGIN_NAME), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Weather Station now displays its data in the imperial system.');
    }

    /**
     * Switch to full translation only.
     *
     * @since 3.0.0
     */
    private function switch_full_translation() {
        update_option('live_weather_station_partial_translation', 0);
        $i18n = new Intl();
        $i18n->delete_mo_files();
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s no longer uses partial translations.', 'live-weather-station'), LWS_PLUGIN_NAME), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Weather Station no longer uses partial translations.');
    }

    /**
     * Switch to partial translation.
     *
     * @since 3.0.0
     */
    private function switch_partial_translation() {
        update_option('live_weather_station_partial_translation', 1);
        $i18n = new Intl();
        $i18n->cron_run();
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s now uses a partial translation.', 'live-weather-station'), LWS_PLUGIN_NAME), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Weather Station now uses a partial translation.');
    }

    /**
     * Reset dashboard meta for current user.
     *
     * @since 3.0.0
     */
    private function reset_dashboard_meta() {
        $this->clean_usermeta('lws-dashboard');
        update_user_meta(get_current_user_id(), 'show_lws_welcome_panel', true);
        add_settings_error('lws_nonce_success', 200, __('Dashboard view has been reset to defaults.', 'live-weather-station'), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Dashboard view has been reset to defaults.');
    }

    /**
     * Reset dashboard meta for current user.
     *
     * @since 3.0.0
     */
    private function reset_analytics_meta() {
        $this->clean_usermeta('lws-analytics');
        add_settings_error('lws_nonce_success', 200, __('Analytics view has been reset to defaults.', 'live-weather-station'), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Analytics view has been reset to defaults.');
    }

    /**
     * Reset services meta for current user.
     *
     * @since 3.0.0
     */
    private function reset_services_meta() {
        $this->clean_usermeta('lws-settings');
        add_settings_error('lws_nonce_success', 200, __('Services view has been reset to defaults.', 'live-weather-station'), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Services view have been reset to defaults.');
    }

    /**
     * Reset stations meta for current user.
     *
     * @since 3.0.0
     */
    private function reset_stations_meta() {
        $this->clean_usermeta('lws-station');
        add_settings_error('lws_nonce_success', 200, __('Stations views have been reset to defaults.', 'live-weather-station'), 'updated');
        Logger::info($this->service, null, null, null, null, null, 0, 'Stations views have been reset to defaults.');
    }

    /**
     * Purge data table.
     * @param boolean $auto Optional. The message to display.
     *
     * @since 3.0.0
     */
    private function purge_data($auto=false) {
        self::truncate_data_table();
        if (!$auto) {
            add_settings_error('lws_nonce_success', 200, __('All stations data have been purged.', 'live-weather-station'), 'updated');
            Logger::notice($this->service, null, null, null, null, null, 0, 'Data table has been truncated.');
        }
        else {
            Logger::notice('Updater', null, null, null, null, null, 0, 'Data table has been truncated.');
        }
    }

    /**
     * Repopulate data table.
     * @param boolean $auto Optional. The message to display.
     *
     * @since 3.0.0
     */
    private function sync_data($auto=false) {
        $this->purge_data($auto);
        $this->get_all();
        if (!$auto) {
            add_settings_error('lws_nonce_success', 200, __('All stations have been resynchronized.', 'live-weather-station'), 'updated');
            Logger::notice($this->service, null, null, null, null, null, 0, 'All stations have been resynchronized.');
        }
        else {
            Logger::notice('Updater', null, null, null, null, null, 0, 'All stations have been resynchronized.');
        }
    }

    /**
     * Reset cache.
     *
     * @since 3.2.0
     */
    private function reset_cache() {
        Cache::reset();
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s has been reset.', 'live-weather-station'), __('Cache', 'live-weather-station')), 'updated');
    }

    /**
     * Reset events log.
     *
     * @since 3.2.0
     */
    private function reset_log() {
        Cache::flush_backend(false);
        Logger::reset();
        add_settings_error('lws_nonce_success', 200, sprintf(__('%s has been reset.', 'live-weather-station'), __('Events log', 'live-weather-station')), 'updated');
    }

    /**
     * Force a immediate execution of e scheduled task.
     *
     * @param boolean $exec Optional. Force execution of the task after reschedule.
     *
     * @since 3.2.0
     */
    private function cron_reschedule($exec=false) {
        $done = false;
        $hook = '';
        $op = 'reschedule';
        if ($exec) {
            $op = 'reschedule & execute';
        }
        if (array_key_exists('hook', $_GET)) {
            $hook = $_GET['hook'];
        }
        $name = self::get_cron_name($hook);
        if (self::is_legitimate_cron($hook)) {
           if ($exec) {
               $done = self::force_and_reschedule_cron($hook, 'Backend');
           }
           else {
               $done = self::reschedule_cron($hook, 'Backend');
           }

        }
        if ($done) {
            if ($op == 'reschedule') {
                add_settings_error('lws_nonce_success', 200, sprintf(__('The task %s has been rescheduled.', 'live-weather-station'), '<em>'.$name.'</em>'), 'updated');
            }
            else {
                add_settings_error('lws_nonce_success', 200, sprintf(__('The task %s has been executed.', 'live-weather-station'), '<em>'.$name.'</em>'), 'updated');
            }
            Logger::info('Backend', null, null, null, null, null, null, sprintf('The operation "%s" has been done for the task named "%s".', $op, $name));
        }
        else {
            add_settings_error('lws_nonce_error', 200, __('This action is not allowed.', 'live-weather-station'), 'error');
            Logger::error('Security', null, null, null, null, null, null, sprintf('An attempt to force an out-of-scope scheduled task was done. The request was not satisfied by %s for security reason. The name of the hook was "%s". The request was "%s".', LWS_PLUGIN_NAME, $hook, $op));
        }
    }

    /**
     * Relaunch the watchdog.
     *
     * @since 3.2.0
     */
    private function relaunch_watchdog() {
        Watchdog::restart();
        add_settings_error('lws_nonce_success', 200, __('The watchdog was successfully restarted.', 'live-weather-station').'<br/>'.__('Please wait a few minutes for all the tasks to be rescheduled.', 'live-weather-station'), 'updated');
    }

    /**
     * Switch to imperial mode.
     *
     * @since 3.0.0
     */
    private function manage_connection() {
        $service = '';
        if (array_key_exists('service', $_POST)) {
            $service = $_POST['service'];
        }
        $action = '';
        if (array_key_exists('connect', $_POST)) {
            $action = 'connect';
        }
        if (array_key_exists('disconnect', $_POST)) {
            $action = 'disconnect';
        }
        $login = '';
        if (array_key_exists('login', $_POST)) {
            $login = $_POST['login'];
        }
        $password = '';
        if (array_key_exists('password', $_POST)) {
            $password = $_POST['password'];
        }
        $key = '';
        if (array_key_exists('key', $_POST)) {
            $key = $_POST['key'];
        }
        $plan = '';
        if (array_key_exists('plan', $_POST)) {
            $plan = $_POST['plan'];
        }
        $result = false;
        $sec = false;
        if (array_key_exists('_wpnonce', $_POST)) {
            foreach ($this->services as $s) {
                $sec = wp_verify_nonce($_POST['_wpnonce'], $s);
                if ($sec) { break;}
            }
        }
        if ($sec) {
            if ($action == 'connect') {
                $s = __('Unkonwn service.', 'live-weather-station');
                if ($service == 'Netatmo') {
                    if ($login == '' || $password == '') {
                        $s = __('the login and password can not be empty', 'live-weather-station');
                    }
                    else {
                        $s = $this->connect_netatmo($login, $password);
                    }
                }
                if ($service == 'NetatmoHC') {
                    if ($login == '' || $password == '') {
                        $s = __('the login and password can not be empty', 'live-weather-station');
                    }
                    else {
                        $s = $this->connect_netatmohc($login, $password);
                    }
                }
                if ($service == 'OpenWeatherMap') {
                    if ($key == '') {
                        $s = __('the API key can not be empty', 'live-weather-station');
                    }
                    else {
                        $s = $s = $this->connect_owm($key, $plan);;
                    }
                }
                if ($service == 'WeatherUnderground') {
                    if ($key == '') {
                        $s = __('the API key can not be empty', 'live-weather-station');
                    }
                    else {
                        $s = $s = $this->connect_wug($key, $plan);;
                    }
                }
                if ($s == '') {
                    $message = __('%s is now connected to %s.', 'live-weather-station');
                    $message = sprintf($message, LWS_PLUGIN_NAME, '<em>' . $service . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::info($this->service, null, null, null, null, null, 0, 'Connection to '. $service . ' has been correctly done by an admin.');
                }
                else {
                    $message = __('Unable to connect %s to %s. Please try again.', 'live-weather-station');
                    $message = sprintf($message, LWS_PLUGIN_NAME, '<em>' . $service . '</em>');
                    $message .= '<br/>' . __('The error message is "%s".', 'live-weather-station');
                    $message = sprintf($message, '<em>' . $s . '</em>');
                    add_settings_error('lws_nonce_error', 200, $message, 'error');
                    Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to correctly connect ' . LWS_PLUGIN_NAME . ' to '. $service . '.');
                }
            }
            if ($action == 'disconnect') {
                if ($service == 'Netatmo') {
                    $this->disconnect_netatmo();
                    $result = true;
                }
                if ($service == 'NetatmoHC') {
                    $this->disconnect_netatmohc();
                    $result = true;
                }
                if ($service == 'OpenWeatherMap') {
                    $this->disconnect_owm();
                    $result = true;
                }
                if ($service == 'WeatherUnderground') {
                    $this->disconnect_wug();
                    $result = true;
                }
                if ($result) {
                    $message = __('%s is now disconnected from %s.', 'live-weather-station');
                    $message = sprintf($message, LWS_PLUGIN_NAME, '<em>' . $service . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::info($this->service, null, null, null, null, null, 0, 'Disconnection from '. $service . ' has been correctly done by an admin.');
                }
                else {
                    $message = __('Unable to disconnect %s from %s. Please try again.', 'live-weather-station');
                    $message = sprintf($message, LWS_PLUGIN_NAME, '<em>' . $service . '</em>');
                    add_settings_error('lws_nonce_error', 200, $message, 'error');
                    Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to correctly disconnect ' . LWS_PLUGIN_NAME . ' from '. $service . '.');
                }

            }
        }
        else {
            $message = __('Connection to %s has not been updated. Please try again.', 'live-weather-station');
            $message = sprintf($message, '<em>' . $service . '</em>');
            add_settings_error('lws_nonce_error', 403, $message, 'error');
            Logger::critical('Security', null, null, null, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
            Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to securely update connection to '. $service . ' service.');
        }
    }

    /**
     * Delete a Netatmo station.
     *
     * @param integer $guid The guid of the station.
     * @since 3.0.0
     */
    protected function delete_station($guid=null) {
        if (isset($guid) && $guid) {
            $station = $this->get_station_informations_by_guid($guid);
            $service = $this->get_service_name($station['station_type']);
            if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), 'delete-station')) {
                if ($res = $this->delete_stations_table(array($guid))) {
                    $res = $this->delete_operational_stations_table(array($station['station_id']));
                }
                if ($res) {
                    $message = __('The station %s has been correctly removed.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::notice($this->service, $service, $station['station_id'], $station['station_name'], null, null, null, 'Station removed.');
                }
                else {
                    $message = __('Unable to remove the station %s.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::error($this->service, $service, $station['station_id'], $station['station_name'], null, null, null, 'Unable to remove this station.');
                }
            }
            else {
                $message = __('Unable to remove the station %s.', 'live-weather-station');
                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                add_settings_error('lws_nonce_error', 403, $message, 'error');
                Logger::critical('Security', $service, $station['station_id'], $station['station_name'], null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                Logger::error($this->service, $service, $station['station_id'], $station['station_name'], null, null, 0, 'It had not been possible to securely delete this station.');
            }
        }
        else {
            add_settings_error('lws_nonce_error', 403, 'No station to remove.', 'error');
            Logger::error('Security', null, null, null, null, null, null, 'An attempt was made to remove a station without ID.');
        }
    }

    /**
     * First getting of data for all station (resynchronization)
     *
     * @since 3.0.0
     */
    private function get_all() {
        $n = new Netatmo_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $n = new Netatmo_HCInitiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $n = new WeatherUnderground_Station_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $n = new Clientraw_Station_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $n = new Realtime_Station_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $this->get_current_and_pollution();
    }

    /**
     * First getting of data for Netatmo station.
     *
     * @param boolean $auto_init Optional. Force creation of stations.
     * @since 3.0.0
     */
    private function get_netatmo($auto_init=false) {
        $n = new Netatmo_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run($auto_init);
        $this->get_current_and_pollution();
    }

    /**
     * First getting of data for Netatmo healthy home coaches.
     *
     * @param boolean $auto_init Optional. Force creation of stations.
     *
     * @since 3.1.0
     */
    private function get_netatmohc($auto_init=false) {
        $n = new Netatmo_HCInitiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run($auto_init);
        $this->get_current_and_pollution();
    }

    /**
     * First getting of data for WeatherUnderground station.
     *
     * @since 3.0.0
     */
    private function get_wug() {
        $n = new WeatherUnderground_Station_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $this->get_current_and_pollution();
    }

    /**
     * First getting of data for Clientraw station.
     *
     * @since 3.0.0
     */
    private function get_raw() {
        $n = new Clientraw_Station_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $this->get_current_and_pollution();
    }

    /**
     * First getting of data for Realtime station.
     *
     * @since 3.0.0
     */
    private function get_real() {
        $n = new Realtime_Station_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $this->get_current_and_pollution();
    }

    /**
     * Connect to an OpenWeatherMap account.
     *
     * @since 3.0.0
     */
    protected function get_current_and_pollution() {
        $n = new OpenWeatherMap_Current_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        $n = new OpenWeatherMap_Pollution_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
        $n->run();
        Cache::flush_query();
        Cache::flush_backend();
    }

    /**
     * Connect to a Netatmo account.
     *
     * @param string $login The login for the account.
     * @param string $password The password for the account.
     * @return string The error string if an error occured, empty string if none.
     *
     * @since    3.0.0
     */
    protected function connect_netatmo($login, $password) {
        $netatmo = new Netatmo_Collector();
        if ($netatmo->authentication($login, $password)) {
            Logger::notice('Authentication', 'Netatmo', null, null, null, null, null, 'Correctly connected to service.');
            if (get_option('live_weather_station_auto_manage_netatmo')) {
                $this->get_netatmo(true);
            }
        }
        else {
            Logger::error('Authentication', 'Netatmo', null, null, null, null, null, 'Unable to connect to service.');
        }
        return $netatmo->last_netatmo_error;
    }

    /**
     * Disconnect from a Netatmo account.
     *
     * @since    2.0.0
     */
    protected function disconnect_netatmo() {
        self::init_netatmo_options();
        Logger::notice('Authentication', 'Netatmo', null, null, null, null, null, 'Correctly disconnected from service.');
        $this->clear_all_netatmo_stations();
        Logger::notice('Backend', 'Netatmo', null, null, null, null, null, 'All stations have been remove from ' . LWS_PLUGIN_NAME . '.');
    }

    /**
     * Connect to a Netatmo HC account.
     *
     * @param string $login The login for the account.
     * @param string $password The password for the account.
     * @return string The error string if an error occured, empty string if none.
     *
     * @since 3.1.0
     */
    protected function connect_netatmohc($login, $password) {
        $netatmohc = new Netatmo_HCCollector();
        if ($netatmohc->authentication($login, $password)) {
            Logger::notice('Authentication', 'Netatmo', null, null, null, null, null, 'Correctly connected to service.');
            if (get_option('live_weather_station_auto_manage_netatmo')) {
                $this->get_netatmohc(true);
            }
        }
        else {
            Logger::error('Authentication', 'Netatmo', null, null, null, null, null, 'Unable to connect to service.');
        }
        return $netatmohc->last_netatmo_error;
    }

    /**
     * Disconnect from a Netatmo HC account.
     *
     * @since 3.1.0
     */
    protected function disconnect_netatmohc() {
        self::init_netatmohc_options();
        Logger::notice('Authentication', 'Netatmo', null, null, null, null, null, 'Correctly disconnected from service.');
        $this->clear_all_netatmo_hc_stations();
        Logger::notice('Backend', 'Netatmo', null, null, null, null, null, 'All stations have been remove from ' . LWS_PLUGIN_NAME . '.');
    }

    /**
     * Connect to an OpenWeatherMap account.
     *
     * @param string $key The API key of the account.
     * @param string $plan The plan of the account.
     * @return string The error string if an error occured, empty string if none.
     *
     * @since    3.0.0
     */
    protected function connect_owm($key, $plan) {
        $owm = new OWM_Base_Collector();
        if ($owm->authentication($key, $plan)) {
            Logger::notice('Authentication', 'OpenWeatherMap', null, null, null, null, null, 'Correctly connected to service.');
            $this->get_current_and_pollution();
        }
        else {
            Logger::error('Authentication', 'OpenWeatherMap', null, null, null, null, null, 'Unable to connect to service.');
        }
        return $owm->last_owm_error;
    }

    /**
     * Disconnect from an OpenWeatherMap API key.
     *
     * @since    2.0.0
     */
    protected function disconnect_owm() {
        self::init_owm_options();
        Logger::notice('Authentication', 'OpenWeatherMap', null, null, null, null, null, 'Correctly disconnected from service.');
        $this->clear_all_owm_stations();
        $this->clear_all_owm_id_stations();
        Logger::notice('Backend', 'OpenWeatherMap', null, null, null, null, null, 'All stations have been remove from ' . LWS_PLUGIN_NAME . '.');
    }

    /**
     * Connect to an WeatherUnderground account.
     *
     * @param string $key The API key of the account.
     * @param string $plan The plan of the account.
     * @return string The error string if an error occured, empty string if none.
     *
     * @since    3.0.0
     */
    protected function connect_wug($key, $plan) {
        $wug = new WUG_Base_Collector();
        if ($wug->authentication($key, $plan)) {
            Logger::notice('Authentication', 'Weather Underground', null, null, null, null, null, 'Correctly connected to service.');
            $this->get_wug();
        }
        else {
            Logger::error('Authentication', 'Weather Underground', null, null, null, null, null, 'Unable to connect to service.');
        }
        return $wug->last_wug_error;
    }

    /**
     * Disconnect from an WeatherUnderground API key.
     *
     * @since 3.0.0
     */
    protected function disconnect_wug() {
        self::init_wug_options();
        Logger::notice('Authentication', 'Weather Underground', null, null, null, null, null, 'Correctly disconnected from service.');
        $this->clear_all_wug_id_stations();
        Logger::notice('Backend', 'Weather Underground', null, null, null, null, null, 'All stations have been remove from ' . LWS_PLUGIN_NAME . '.');
    }

    /**
     * Add a Netatmo station.
     *
     * @param boolean $is_hc Optional. True if it's a healthy home coach;
     * @param string $device_id The id of the station.
     * @since 3.0.0
     */
    protected function add_netatmo($device_id=null, $is_hc=false) {
        if ($device_id) {
            if ($is_hc) {
                $n = new Netatmo_HCInitiator(LWS_PLUGIN_ID, LWS_VERSION);
                $nonce = 'add-netatmohc';
                $station_type = LWS_NETATMOHC_SID;
            }
            else {
                $n = new Netatmo_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
                $nonce = 'add-netatmo';
                $station_type = LWS_NETATMO_SID;
            }
            $stations = $n->detect_stations();
            $station['station_name'] = '<unnamed>';
            $station['station_type'] = $station_type;
            $station['station_id'] = $device_id;
            foreach ($stations as $item) {
                if ($item['device_id'] == $device_id) {
                    $station = $item;
                }
            }
            if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), $nonce)) {
                if ($this->insert_ignore_stations_table($device_id, $station_type)) {
                    $message = __('The station %s has been correctly added.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::notice($this->service, 'Netatmo', $device_id, $station['station_name'], null, null, null, 'Station added.');
                    if ($is_hc) {
                        $this->get_netatmohc();
                    }
                    else {
                        $this->get_netatmo();
                    }
                }
                else {
                    $message = __('Unable to add the station %s.', 'live-weather-station');
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::error($this->service, 'Netatmo', $device_id, $station['station_name'], null, null, null, 'Unable to add this station.');
                }
            }
            else {
                $message = __('Unable to add the station %s.', 'live-weather-station');
                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                add_settings_error('lws_nonce_error', 403, $message, 'error');
                Logger::critical('Security', 'Netatmo', $device_id, $station['station_name'], null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                Logger::error($this->service, 'Netatmo', $device_id, $station['station_name'], null, null, 0, 'It had not been possible to securely add this station.');
            }
        }
        else {
            add_settings_error('lws_nonce_error', 403, 'No station to add.', 'error');
            Logger::error('Security', 'Netatmo', null, null, null, null, null, 'An attempt was made to add a station without ID.');
        }
    }

    /**
     * Add a located OWM station.
     *
     * @since 3.0.0
     */
    public function add_loc() {
        $station = array();
        $error = 0;
        if (array_key_exists('guid', $_POST) &&
            array_key_exists('station_id', $_POST) &&
            array_key_exists('station_name', $_POST) &&
            array_key_exists('loc_city', $_POST) &&
            array_key_exists('loc_country_code', $_POST) &&
            array_key_exists('loc_tz', $_POST) &&
            array_key_exists('loc_altitude', $_POST)) {
            $station['station_type'] = LWS_LOC_SID;
            if (array_key_exists('guid', $_POST)) {
                $station['guid'] = stripslashes(htmlspecialchars_decode($_POST['guid']));
            }
            if (array_key_exists('station_id', $_POST)) {
                $station['station_id'] = stripslashes(htmlspecialchars_decode($_POST['station_id']));
            }
            if (array_key_exists('station_name', $_POST)) {
                $station['station_name'] = stripslashes(htmlspecialchars_decode($_POST['station_name']));
            }
            if (array_key_exists('loc_city', $_POST)) {
                $station['loc_city'] = stripslashes(htmlspecialchars_decode($_POST['loc_city']));
            }
            if (array_key_exists('loc_country_code', $_POST)) {
                $station['loc_country_code'] = $_POST['loc_country_code'];
            }
            if (array_key_exists('loc_tz', $_POST)) {
                $station['loc_timezone'] = $_POST['loc_tz'];
            }
            if (array_key_exists('loc_altitude', $_POST)) {
                $station['loc_altitude'] = (int)stripslashes(htmlspecialchars_decode($_POST['loc_altitude']));
            }
            if (array_key_exists('loc_latitude', $_POST) &&
                array_key_exists('loc_longitude', $_POST)) {
                if (is_numeric($_POST['loc_latitude']) && is_numeric($_POST['loc_longitude'])) {
                    $station['loc_latitude'] = (float)$_POST['loc_latitude'];
                    $station['loc_longitude'] = (float)$_POST['loc_longitude'];
                    if ($station['loc_latitude'] < -90 || $station['loc_latitude'] > 90) {
                        $error = 2;
                    }
                    if ($station['loc_longitude'] < -180 || $station['loc_longitude'] > 180) {
                        $error = 2;
                    }
                }
                else {
                    $station['loc_latitude'] = $_POST['loc_latitude'];
                    $station['loc_longitude'] = $_POST['loc_longitude'];
                    $error = 2;
                }
            }
            else {
                $station['loc_latitude'] = '';
                $station['loc_longitude'] = '';
            }
            if ($station['loc_latitude'] == '' && $station['loc_longitude'] == '') {
                $coord = OWM_Current_Collector::get_coordinates_via_owm($station['loc_city'], $station['loc_country_code']);
                if (count($coord) > 0) {
                    if (array_key_exists('loc_longitude', $coord) && array_key_exists('loc_latitude', $coord)) {
                        $station['loc_longitude'] = $coord['loc_longitude'];
                        $station['loc_latitude'] = $coord['loc_latitude'];
                        $error = 4;
                    }
                    else {
                        $error = 1;
                    }
                }
                else {
                    $error = 1;
                }
            }
        }
        else {
            $error = 3;
        }
        if ($error == 0) {
            $update = true;
            if (array_key_exists('guid', $station)) {
                if ($station['guid'] == 0) {
                    unset($station['guid']);
                    $update = false;
                }
            }
            if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), 'add-edit-loc')) {
                if ($guid = $this->update_stations_table($station, true)) {
                    $st = $this->get_station_informations_by_guid($guid);
                    $station_id = $st['station_id'];
                    $station_name = $st['station_name'];
                    if ($update) {
                        $message = __('The station %s has been correctly updated.', 'live-weather-station');
                        $log = 'Station updated.';
                    }
                    else {
                        $message = __('The station %s has been correctly added.', 'live-weather-station');
                        $log = 'Station added.';
                    }
                    $message = sprintf($message, '<em>' . $station_name . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::notice($this->service, 'OpenWeatherMap', $station_id, $station_name, null, null, null, $log);
                    $this->get_current_and_pollution();
                    $st = $this->get_station_informations_by_guid($guid);
                    $this->modify_table(self::live_weather_station_log_table(), 'device_id', $station_id, $st['station_id']);
                }
                else {
                    if ($update) {
                        $message = $message = __('Unable to update the station %s.', 'live-weather-station');
                        $log = 'Unable to update this station.';
                        $station_id = $station['station_id'];
                        $station_name = $station['station_name'];
                    }
                    else {
                        $message = $message = __('Unable to add the station %s.', 'live-weather-station');
                        $log = 'Unable to add this station.';
                        $station_id = null;
                        $station_name = null;
                    }
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::error($this->service, 'OpenWeatherMap', $station_id, $station_name, null, null, null, $log);
                }
            }
            else {
                if ($update) {
                    $message = $message = __('Unable to update the station %s.', 'live-weather-station');
                    $station_id = $station['station_id'];
                    $station_name = $station['station_name'];
                }
                else {
                    $message = $message = __('Unable to add the station %s.', 'live-weather-station');
                    $station_id = null;
                    $station_name = null;
                }
                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                add_settings_error('lws_nonce_error', 403, $message, 'error');
                Logger::critical('Security', 'OpenWeatherMap', $station_id, $station_name, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                Logger::error($this->service, 'OpenWeatherMap', $station_id, $station_name, null, null, 0, 'It had not been possible to securely add or update this station.');
            }
        }
        else {
            $result['error'] = $error;
        }
        if (!array_key_exists('guid', $station)) {
            $station['guid'] = 0;
        }
        $station['error'] = $error;
        return $station;
    }

    /**
     * Add a Clientraw station.
     *
     * @since 3.0.0
     */
    public function add_raw() {
        $station = array();
        $error = 0;
        $message = '';
        if (array_key_exists('guid', $_POST) &&
            array_key_exists('station_id', $_POST) &&
            array_key_exists('station_name', $_POST) &&
            array_key_exists('loc_city', $_POST) &&
            array_key_exists('loc_country_code', $_POST) &&
            array_key_exists('loc_tz', $_POST) &&
            array_key_exists('connection_type', $_POST) &&
            array_key_exists('service_id', $_POST) &&
            array_key_exists('station_model', $_POST) &&
            array_key_exists('loc_altitude', $_POST)) {
            $station['station_type'] = LWS_RAW_SID;
            if (array_key_exists('guid', $_POST)) {
                $station['guid'] = stripslashes(htmlspecialchars_decode($_POST['guid']));
            }
            if (array_key_exists('station_id', $_POST)) {
                $station['station_id'] = stripslashes(htmlspecialchars_decode($_POST['station_id']));
            }
            if (array_key_exists('station_name', $_POST)) {
                $station['station_name'] = stripslashes(htmlspecialchars_decode($_POST['station_name']));
            }
            if (array_key_exists('loc_city', $_POST)) {
                $station['loc_city'] = stripslashes(htmlspecialchars_decode($_POST['loc_city']));
            }
            if (array_key_exists('loc_country_code', $_POST)) {
                $station['loc_country_code'] = $_POST['loc_country_code'];
            }
            if (array_key_exists('loc_tz', $_POST)) {
                $station['loc_timezone'] = $_POST['loc_tz'];
            }
            if (array_key_exists('connection_type', $_POST)) {
                $station['connection_type'] = $_POST['connection_type'];
            }
            if (array_key_exists('service_id', $_POST)) {
                $station['service_id'] = $_POST['service_id'];
            }
            if (array_key_exists('loc_altitude', $_POST)) {
                $station['loc_altitude'] = (int)stripslashes(htmlspecialchars_decode($_POST['loc_altitude']));
            }
            /*if (array_key_exists('loc_latitude', $_POST)) {
                $station['loc_latitude'] = (int)stripslashes(htmlspecialchars_decode($_POST['loc_latitude']));
            }
            if (array_key_exists('loc_longitude', $_POST)) {
                $station['loc_longitude'] = (int)stripslashes(htmlspecialchars_decode($_POST['loc_longitude']));
            }*/
            $station['station_model'] = stripslashes(htmlspecialchars_decode($_POST['station_model']));
            $collector = new ClientrawCollector();
            if ($message = $collector->test($station['connection_type'], $station['service_id'])) {
                $error = 1;
            }
        }
        else {
            $error = 3;
        }
        if ($error == 0) {
            $update = true;
            if (array_key_exists('guid', $station)) {
                if ($station['guid'] == 0) {
                    unset($station['guid']);
                    $update = false;
                }
            }
            if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), 'add-edit-raw')) {
                if ($guid = $this->update_stations_table($station, true)) {
                    $st = $this->get_station_informations_by_guid($guid);
                    $station_id = $st['station_id'];
                    $station_name = $st['station_name'];
                    if ($update) {
                        $message = __('The station %s has been correctly updated.', 'live-weather-station');
                        $log = 'Station updated.';
                    }
                    else {
                        $message = __('The station %s has been correctly added.', 'live-weather-station');
                        $log = 'Station added.';
                    }
                    $message = sprintf($message, '<em>' . $station_name . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::notice($this->service, null, $station_id, $station_name, null, null, null, $log);
                    $this->get_raw();
                    $st = $this->get_station_informations_by_guid($guid);
                    $this->modify_table(self::live_weather_station_log_table(), 'device_id', $station_id, $st['station_id']);
                }
                else {
                    if ($update) {
                        $message = $message = __('Unable to update the station %s.', 'live-weather-station');
                        $log = 'Unable to update this station.';
                        $station_id = $station['station_id'];
                        $station_name = $station['station_name'];
                    }
                    else {
                        $message = $message = __('Unable to add the station %s.', 'live-weather-station');
                        $log = 'Unable to add this station.';
                        $station_id = null;
                        $station_name = null;
                    }
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::error($this->service, null, $station_id, $station_name, null, null, null, $log);
                }
            }
            else {
                if ($update) {
                    $message = $message = __('Unable to update the station %s.', 'live-weather-station');
                    $station_id = $station['station_id'];
                    $station_name = $station['station_name'];
                }
                else {
                    $message = $message = __('Unable to add the station %s.', 'live-weather-station');
                    $station_id = null;
                    $station_name = null;
                }
                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                add_settings_error('lws_nonce_error', 403, $message, 'error');
                Logger::critical('Security', null, $station_id, $station_name, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                Logger::error($this->service, null, $station_id, $station_name, null, null, 0, 'It had not been possible to securely add or update this station.');
            }
        }
        else {
            $result['error'] = $error;
        }
        if (!array_key_exists('guid', $station)) {
            $station['guid'] = 0;
        }
        $station['error'] = $error;
        $station['message'] = $message;
        return $station;
    }

    /**
     * Add a Realtime station.
     *
     * @since 3.0.0
     */
    public function add_real() {
        $station = array();
        $error = 0;
        $message = '';
        if (array_key_exists('guid', $_POST) &&
            array_key_exists('station_id', $_POST) &&
            array_key_exists('station_name', $_POST) &&
            array_key_exists('loc_city', $_POST) &&
            array_key_exists('loc_country_code', $_POST) &&
            array_key_exists('loc_tz', $_POST) &&
            array_key_exists('connection_type', $_POST) &&
            array_key_exists('service_id', $_POST) &&
            array_key_exists('station_model', $_POST) &&
            array_key_exists('loc_latitude', $_POST) &&
            array_key_exists('loc_longitude', $_POST) &&
            array_key_exists('loc_altitude', $_POST)) {
            $station['station_type'] = LWS_REAL_SID;
            if (array_key_exists('guid', $_POST)) {
                $station['guid'] = stripslashes(htmlspecialchars_decode($_POST['guid']));
            }
            if (array_key_exists('station_id', $_POST)) {
                $station['station_id'] = stripslashes(htmlspecialchars_decode($_POST['station_id']));
            }
            if (array_key_exists('station_name', $_POST)) {
                $station['station_name'] = stripslashes(htmlspecialchars_decode($_POST['station_name']));
            }
            if (array_key_exists('loc_city', $_POST)) {
                $station['loc_city'] = stripslashes(htmlspecialchars_decode($_POST['loc_city']));
            }
            if (array_key_exists('loc_country_code', $_POST)) {
                $station['loc_country_code'] = $_POST['loc_country_code'];
            }
            if (array_key_exists('loc_tz', $_POST)) {
                $station['loc_timezone'] = $_POST['loc_tz'];
            }
            if (array_key_exists('connection_type', $_POST)) {
                $station['connection_type'] = $_POST['connection_type'];
            }
            if (array_key_exists('service_id', $_POST)) {
                $station['service_id'] = $_POST['service_id'];
            }
            if (array_key_exists('loc_altitude', $_POST)) {
                $station['loc_altitude'] = (int)stripslashes(htmlspecialchars_decode($_POST['loc_altitude']));
            }
            if (array_key_exists('loc_latitude', $_POST)) {
                $station['loc_latitude'] = sprintf("%.7F", (float)stripslashes(htmlspecialchars_decode($_POST['loc_latitude'])));
            }
            if (array_key_exists('loc_longitude', $_POST)) {
                $station['loc_longitude'] = sprintf("%.7F", (float)stripslashes(htmlspecialchars_decode($_POST['loc_longitude'])));
            }
            $station['station_model'] = stripslashes(htmlspecialchars_decode($_POST['station_model']));
            $collector = new RealtimeCollector();
            if ($message = $collector->test($station['connection_type'], $station['service_id'])) {
                $error = 1;
            }
        }
        else {
            $error = 3;
        }
        if ($error == 0) {
            $update = true;
            if (array_key_exists('guid', $station)) {
                if ($station['guid'] == 0) {
                    unset($station['guid']);
                    $update = false;
                }
            }
            if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), 'add-edit-real')) {
                if ($guid = $this->update_stations_table($station, true)) {
                    $st = $this->get_station_informations_by_guid($guid);
                    $station_id = $st['station_id'];
                    $station_name = $st['station_name'];
                    if ($update) {
                        $message = __('The station %s has been correctly updated.', 'live-weather-station');
                        $log = 'Station updated.';
                    }
                    else {
                        $message = __('The station %s has been correctly added.', 'live-weather-station');
                        $log = 'Station added.';
                    }
                    $message = sprintf($message, '<em>' . $station_name . '</em>');
                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                    Logger::notice($this->service, null, $station_id, $station_name, null, null, null, $log);
                    $this->get_real();
                    $st = $this->get_station_informations_by_guid($guid);
                    $this->modify_table(self::live_weather_station_log_table(), 'device_id', $station_id, $st['station_id']);
                }
                else {
                    if ($update) {
                        $message = $message = __('Unable to update the station %s.', 'live-weather-station');
                        $log = 'Unable to update this station.';
                        $station_id = $station['station_id'];
                        $station_name = $station['station_name'];
                    }
                    else {
                        $message = $message = __('Unable to add the station %s.', 'live-weather-station');
                        $log = 'Unable to add this station.';
                        $station_id = null;
                        $station_name = null;
                    }
                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::error($this->service, null, $station_id, $station_name, null, null, null, $log);
                }
            }
            else {
                if ($update) {
                    $message = $message = __('Unable to update the station %s.', 'live-weather-station');
                    $station_id = $station['station_id'];
                    $station_name = $station['station_name'];
                }
                else {
                    $message = $message = __('Unable to add the station %s.', 'live-weather-station');
                    $station_id = null;
                    $station_name = null;
                }
                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                add_settings_error('lws_nonce_error', 403, $message, 'error');
                Logger::critical('Security', null, $station_id, $station_name, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                Logger::error($this->service, null, $station_id, $station_name, null, null, 0, 'It had not been possible to securely add or update this station.');
            }
        }
        else {
            $result['error'] = $error;
        }
        if (!array_key_exists('guid', $station)) {
            $station['guid'] = 0;
        }
        $station['error'] = $error;
        $station['message'] = $message;
        return $station;
    }
    
    /**
     * Add a WUG station.
     *
     * @since 3.0.0
     */
    public function add_wug() {
        $station = array();
        $station_id = null;
        $service_id = null;
        $station_name = null;
        if (array_key_exists('guid', $_POST) &&
            array_key_exists('service_id', $_POST) &&
            array_key_exists('station_model', $_POST)) {
                $guid = 0;
                if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), 'add-edit-wug')) {
                    if (($guid = stripslashes(htmlspecialchars_decode($_POST['guid']))) != 0) { // UPDATE
                        $station = $this->get_wug_station($guid);
                        if (array_key_exists('station_id', $station)) {
                            $station_id = $station['station_id'];
                        }
                        if (array_key_exists('station_name', $station)) {
                            $station_name = $station['station_name'];
                        }
                        if (!empty($station)) {
                            if (array_key_exists('station_name', $_POST)) {
                                $station['station_name'] = substr(stripslashes(htmlspecialchars_decode($_POST['station_name'])), 0, 59);
                            }
                            else {
                                $station['station_name'] = '';
                            }
                            $station['station_model'] = stripslashes(htmlspecialchars_decode($_POST['station_model']));
                            $this->update_table(self::live_weather_station_stations_table(), $station);
                            $message = __('The station %s has been correctly updated.', 'live-weather-station');
                            $message = sprintf($message, '<em>' . $station_name . '</em>');
                            add_settings_error('lws_nonce_success', 200, $message, 'updated');
                            Logger::notice($this->service, 'Weather Underground', $station_id, $station_name, null, null, null, 'Station updated.');
                            $this->get_wug();
                        }
                        else {
                            $message = __('Unable to update the station %s.', 'live-weather-station');
                            $message = sprintf($message, '<em>' . $station_name . '</em>');
                            add_settings_error('lws_nonce_error', 403, $message, 'error');
                            Logger::error($this->service, 'Weather Underground', $station_id, $station_name, null, null, null, 'Unable to add this station.');
                        }
                    }
                    else { // ADD NEW
                        $station = $this->get_wug_station();
                        $station['service_id'] = substr(stripslashes(htmlspecialchars_decode($_POST['service_id'])), 0, 19);
                        if (array_key_exists('station_name', $_POST)) {
                            $station['station_name'] = substr(stripslashes(htmlspecialchars_decode($_POST['station_name'])), 0, 59);
                        }
                        else {
                            $station['station_name'] = '';
                        }
                        $station['station_model'] = substr(stripslashes(htmlspecialchars_decode($_POST['station_model'])), 0, 200);
                        unset($station['guid']);
                        $WUG_test = WUG_Station_Collector::test_station($station['service_id']);
                        if ($WUG_test == '') {
                            if (array_key_exists('station_id', $station)) {
                                $station_id = $station['station_id'];
                            }
                            if (array_key_exists('station_name', $station)) {
                                $station_name = $station['station_name'];
                            }
                            if ($guid = $this->update_stations_table($station, true)) {
                                $message = __('The station %s has been correctly updated.', 'live-weather-station');
                                $message = sprintf($message, '<em>' . $station_name . '</em>');
                                add_settings_error('lws_nonce_success', 200, $message, 'updated');
                                Logger::notice($this->service, 'Weather Underground', $station_id, $station_name, null, null, null, 'Station added.');
                                $this->get_wug();
                                $st = $this->get_station_informations_by_guid($guid);
                                $this->modify_table(self::live_weather_station_log_table(), 'device_id', $station_id, $st['station_id']);
                            }
                            else {
                                $message = __('Unable to add the station %s.', 'live-weather-station');
                                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                add_settings_error('lws_nonce_error', 403, $message, 'error');
                                Logger::error($this->service, 'Weather Underground', null, null, null, null, null, 'Unable to add a station, service says: unknown station ID.');
                            }
                        }
                        else {
                            $message = __('Unable to add the station %s.', 'live-weather-station');
                            $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                            add_settings_error('lws_nonce_error', 403, $message, 'error');
                            Logger::error($this->service, 'Weather Underground', null, null, null, null, null, sprintf('Unable to add a station, error message: %s.', $WUG_test));
                        }
                    }
                }
                else {
                    if ($guid == 0) {
                        $message = __('Unable to add the station %s.', 'live-weather-station');
                        $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                        add_settings_error('lws_nonce_error', 403, $message, 'error');
                        Logger::critical('Security', 'Weather Underground', null, null, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                        Logger::error($this->service, 'Weather Underground', null, null, null, null, 0, 'It had not been possible to securely add a station.');

                    }
                    else {
                        $message = __('Unable to update the station %s.', 'live-weather-station');
                        $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                        add_settings_error('lws_nonce_error', 403, $message, 'error');
                        Logger::critical('Security', 'Weather Underground', $station_id, $station_name, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                        Logger::error($this->service, 'Weather Underground', $station_id, $station_name, null, null, 0, 'It had not been possible to securely update this station.');
                    }
                }
        }
    }
}