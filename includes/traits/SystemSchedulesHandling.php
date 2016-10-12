<?php

namespace WeatherStation\System\Schedules;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Netatmo\Plugin\Pusher as Netatmo_Pusher;
use WeatherStation\SDK\Netatmo\Plugin\Updater as Netatmo_Updater;
use WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentUpdater as Owm_Current_Updater;
use WeatherStation\SDK\OpenWeatherMap\Plugin\StationUpdater as Owm_Station_Updater;
use WeatherStation\SDK\WeatherUnderground\Plugin\StationUpdater as Wug_Station_Updater;
use WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionUpdater as Owm_Pollution_Updater;
use WeatherStation\System\I18N\Handling as i18n;

/**
 * Functionalities for schedules & cron handling.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Handling {

    protected static $watchdog_name = 'lws_watchdog';
    protected static $translation_update_name = 'lws_translation_update';
    protected static $netatmo_update_schedule_name = 'lws_netatmo_update';
    protected static $owm_update_schedule_name = 'lws_owm_update';
    protected static $owm_update_current_schedule_name = 'lws_owm_current_update';
    protected static $owm_update_station_schedule_name = 'lws_owm_station_update';
    protected static $wug_update_station_schedule_name = 'lws_wug_station_update';
    protected static $owm_update_pollution_schedule_name = 'lws_owm_pollution_update';
    protected static $netatmo_push_schedule_name = 'lws_netatmo_push';
    protected static $log_rotate_name = 'lws_log_rotate';

    /**
     * Define Netatmo Updater cron job.
     *
     * @since    2.7.0
     */
    protected static function define_netatmo_update_cron() {
        $plugin_netatmo_update_cron = new Netatmo_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$netatmo_update_schedule_name, array($plugin_netatmo_update_cron, 'cron_run'));
    }

    /**
     * Launch the Netatmo Updater cron job if needed.
     *
     * @since    2.7.0
     */
    protected static function launch_netatmo_update_cron() {
        if (!wp_next_scheduled(self::$netatmo_update_schedule_name)) {
            wp_schedule_event(time() + 10, 'five_minutes', self::$netatmo_update_schedule_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$netatmo_update_schedule_name.' cron job.');
        }
    }

    /**
     * Define Netatmo Pusher cron job.
     *
     * @since    2.7.0
     */
    protected static function define_netatmo_push_cron() {
        $plugin_netatmo_push_cron = new Netatmo_Pusher(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$netatmo_push_schedule_name, array($plugin_netatmo_push_cron, 'cron_run'));
    }

    /**
     * Launch the Netatmo Pusher cron job if needed.
     *
     * @since    2.7.0
     */
    protected static function launch_netatmo_push_cron() {
        if (!wp_next_scheduled(self::$netatmo_push_schedule_name)) {
            wp_schedule_event(time() + 20, 'ten_minutes', self::$netatmo_push_schedule_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$netatmo_push_schedule_name.' cron job.');
        }
    }

    /**
     * Define OWM Current Updater cron job.
     *
     * @since    2.7.0
     */
    protected static function define_owm_current_update_cron() {
        $plugin_owm_current_cron = new Owm_Current_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$owm_update_current_schedule_name, array($plugin_owm_current_cron, 'cron_run'));
    }

    /**
     * Launch the OWM Current Updater cron job if needed.
     *
     * @since    2.7.0
     */
    protected static function launch_owm_current_update_cron() {
        if (!wp_next_scheduled(self::$owm_update_current_schedule_name)) {
            wp_schedule_event(time() + 30, 'fifteen_minutes', self::$owm_update_current_schedule_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$owm_update_current_schedule_name.' cron job.');
        }
    }

    /**
     * Define OWM Station Updater cron job.
     *
     * @since 3.0.0
     */
    protected static function define_owm_station_update_cron() {
        $plugin_owm_station_cron = new Owm_Station_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$owm_update_station_schedule_name, array($plugin_owm_station_cron, 'cron_run'));
    }

    /**
     * Launch the OWM Station Updater cron job if needed.
     *
     * @since 3.0.0
     */
    protected static function launch_owm_station_update_cron() {
        if (!wp_next_scheduled(self::$owm_update_station_schedule_name)) {
            wp_schedule_event(time() + 40, 'twelve_minutes', self::$owm_update_station_schedule_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$owm_update_station_schedule_name.' cron job.');
        }
    }

    /**
     * Define WUG Station Updater cron job.
     *
     * @since 3.0.0
     */
    protected static function define_wug_station_update_cron() {
        $plugin_wug_station_cron = new Wug_Station_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$wug_update_station_schedule_name, array($plugin_wug_station_cron, 'cron_run'));
    }

    /**
     * Launch the WUG Station Updater cron job if needed.
     *
     * @since 3.0.0
     */
    protected static function launch_wug_station_update_cron() {
        if (!wp_next_scheduled(self::$wug_update_station_schedule_name)) {
            wp_schedule_event(time() + 40, 'eleven_minutes', self::$wug_update_station_schedule_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$wug_update_station_schedule_name.' cron job.');
        }
    }

    /**
     * Define OWM Pollution Updater cron job.
     *
     * @since    2.7.0
     */
    protected static function define_owm_pollution_update_cron() {
        $plugin_owm_pollution_cron = new Owm_Pollution_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$owm_update_pollution_schedule_name, array($plugin_owm_pollution_cron, 'cron_run'));
    }

    /**
     * Launch the OWM Pollution Updater cron job if needed.
     *
     * @since    2.7.0
     */
    protected static function launch_owm_pollution_update_cron() {
        if (!wp_next_scheduled(self::$owm_update_pollution_schedule_name)) {
            wp_schedule_event(time() + 40, 'thirty_minutes', self::$owm_update_pollution_schedule_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$owm_update_pollution_schedule_name.' cron job.');
        }
    }

    /**
     * Define log rotate cron job.
     *
     * @since    2.8.0
     */
    protected static function define_log_rotate_cron() {
        $logger = new Logger(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$log_rotate_name, array($logger, 'rotate'));
    }

    /**
     * Launch the log rotate cron job if needed.
     *
     * @since    2.8.0
     */
    protected static function launch_log_rotate_cron() {
        if (!wp_next_scheduled(self::$log_rotate_name)) {
            wp_schedule_event(time() + 5, 'daily', self::$log_rotate_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$log_rotate_name.' cron job.');
        }
    }

    /**
     * Define log rotate cron job.
     *
     * @since 3.0.0
     */
    protected static function define_translation_update_cron() {
        $i18n = new i18n();
        add_action(self::$translation_update_name, array($i18n, 'cron_run'));
    }

    /**
     * Launch the log rotate cron job if needed.
     *
     * @since    2.8.0
     */
    protected static function launch_translation_update_cron() {
        if (!wp_next_scheduled(self::$translation_update_name)) {
            wp_schedule_event(time() + 15, 'hourly', self::$translation_update_name);
            Logger::info('Watchdog',null,null,null,null,null,null,'Recycling '.self::$translation_update_name.' cron job.');
        }
    }

    /**
     * Delete schedules.
     *
     * @since    1.0.0
     */
    protected static function delete_schedules() {
        wp_clear_scheduled_hook(self::$netatmo_update_schedule_name);
        wp_clear_scheduled_hook(self::$owm_update_schedule_name);
        wp_clear_scheduled_hook(self::$owm_update_current_schedule_name);
        wp_clear_scheduled_hook(self::$owm_update_station_schedule_name);
        wp_clear_scheduled_hook(self::$wug_update_station_schedule_name);
        wp_clear_scheduled_hook(self::$owm_update_pollution_schedule_name);
        wp_clear_scheduled_hook(self::$netatmo_push_schedule_name);
        wp_clear_scheduled_hook(self::$log_rotate_name);
        wp_clear_scheduled_hook(self::$translation_update_name);
    }

    /**
     * Init schedules.
     *
     * @since    2.0.0
     */
    protected static function init_schedules() {
        self::launch_netatmo_update_cron();
        self::launch_netatmo_push_cron();
        self::launch_owm_current_update_cron();
        self::launch_owm_station_update_cron();
        self::launch_wug_station_update_cron();
        self::launch_owm_pollution_update_cron();
        self::launch_log_rotate_cron();
        self::launch_translation_update_cron();
    }

    /**
     * Re-init schedules.
     *
     * @since    2.0.0
     */
    protected static function reinit_schedules() {
        self::delete_schedules();
        self::init_schedules();
    }

    /**
     * Add a new 3 minutes interval capacity to the WP cron feature.
     *
     * @since    2.7.0
     */
    public static function add_cron_03_minutes_interval($schedules) {
        $schedules['three_minutes'] = array(
            'interval' => 180,
            'display'  => __( 'Every three minutes', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 5 minutes interval capacity to the WP cron feature.
     *
     * @since    2.4.0
     */
    public static function add_cron_05_minutes_interval($schedules) {
        $schedules['five_minutes'] = array(
            'interval' => 300,
            'display'  => __( 'Every five minutes', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 10 minutes interval capacity to the WP cron feature.
     *
     * @since    1.0.0
     */
    public static function add_cron_10_minutes_interval($schedules) {
        $schedules['ten_minutes'] = array(
            'interval' => 600,
            'display'  => __( 'Every ten minutes', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 11 minutes interval capacity to the WP cron feature.
     *
     * @since 3.0.0
     */
    public static function add_cron_11_minutes_interval($schedules) {
        $schedules['eleven_minutes'] = array(
            'interval' => 660,
            'display'  => __( 'Every eleven minutes', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 11 minutes interval capacity to the WP cron feature.
     *
     * @since 3.0.0
     */
    public static function add_cron_12_minutes_interval($schedules) {
        $schedules['twelve_minutes'] = array(
            'interval' => 720,
            'display'  => __( 'Every twelve minutes', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 15 minutes interval capacity to the WP cron feature.
     *
     * @since    2.0.0
     */
    public static function add_cron_15_minutes_interval($schedules) {
        $schedules['fifteen_minutes'] = array(
            'interval' => 900,
            'display'  => __( 'Every fifteen minutes', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 30 minutes interval capacity to the WP cron feature.
     *
     * @since    2.4.0
     */
    public static function add_cron_30_minutes_interval($schedules) {
        $schedules['thirty_minutes'] = array(
            'interval' => 1800,
            'display'  => __( 'Every thirty minutes', 'live-weather-station' ),
        );
        return $schedules;
    }
}