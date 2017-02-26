<?php

namespace WeatherStation\System\Schedules;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Schedules\Handling as Schedules;

/**
 * The class to monitor and operate cron jobs.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.7.0
 */
class Watchdog {

    use Schedules;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.7.0
     * @param      string    $Live_Weather_Station       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $Live_Weather_Station, $version ) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Define watchdog cron job.
     *
     * @since    2.7.0
     */
    protected static function define_watchdog_cron() {
        add_action(self::$watchdog_name, array(get_called_class(), 'cron_run'));
    }

    /**
     * Init and start the watchdog.
     *
     * @since    2.7.0
     */
    public static function start() {
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_03_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_05_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_10_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_11_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_12_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_15_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_30_minutes_interval'));
        self::define_netatmo_update_cron();
        self::define_netatmo_hc_update_cron();
        self::define_push_cron();
        self::define_owm_current_update_cron();
        self::define_owm_station_update_cron();
        self::define_wug_station_update_cron();
        self::define_raw_station_update_cron();
        self::define_real_station_update_cron();
        self::define_owm_pollution_update_cron();
        self::define_log_rotate_cron();
        self::define_cache_flush_cron();
        self::define_watchdog_cron();
        self::define_stats_clean_cron();
        self::define_translation_update_cron();
        self::launch();
    }

    /**
     * Delete schedules and stop the watchdog.
     *
     * @since    2.7.0
     */
    public static function stop() {
        self::delete_schedules();
        wp_clear_scheduled_hook(self::$watchdog_name);
        Logger::notice('Watchdog',null,null,null,null,null,null,'Service stopped.');
    }

    /**
     * Restart the watchdog.
     *
     * @since    2.7.0
     */
    public static function restart() {
        self::stop();
        self::start();
    }

    /**
     * Launch the watchdog if needed.
     *
     * @since    2.7.0
     */
    public static function launch() {
        if (!wp_next_scheduled(self::$watchdog_name)) {
            wp_schedule_event(time() + 5, 'three_minutes', self::$watchdog_name);
            Logger::notice('Watchdog',null,null,null,null,null,null,'Service started.');
        }
    }

    /**
     * The watchdog.
     *
     * @since    2.7.0
     */
    public static function cron_run() {
        self::init_schedules();
    }

}