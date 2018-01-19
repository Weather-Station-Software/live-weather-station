<?php

namespace WeatherStation\System\Schedules;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Schedules\Handling as Schedules;
use WeatherStation\DB\Storage;

/**
 * The class to monitor and operate cron jobs.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.7.0
 */
class Watchdog {

    use Schedules, Storage;

    private $Live_Weather_Station;
    private $version;

    private static $chrono = array();
    private static $stats = array();

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 2.7.0
     *
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
     * @since 2.7.0
     */
    public static function start() {
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_02_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_03_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_05_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_06_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_10_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_15_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_20_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_30_minutes_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_4_hours_interval'));
        add_filter('cron_schedules', array(get_called_class(), 'add_cron_6_hours_interval'));
        self::define_schedules();
        self::launch();
    }

    /**
     * Delete schedules and stop the watchdog.
     *
     * @since 2.7.0
     */
    public static function stop() {
        self::delete_schedules();
        Logger::notice('Watchdog',null,null,null,null,null,null,'Service stopped.');
    }

    /**
     * Restart the watchdog.
     *
     * @since 2.7.0
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
        $cron_id = Watchdog::init_chrono(Watchdog::$watchdog_name);
        self::init_schedules();
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Delete old records.
     *
     * @since 3.2.0
     */
    public static function rotate() {
        global $wpdb;
        $now = date('Y-m-d H:i:s', time() - MONTH_IN_SECONDS);
        $sql = "DELETE FROM " . $wpdb->prefix.self::live_weather_station_performance_cron_table() . " WHERE ";
        $sql .= "timestamp<'" . $now . "';";
        $wpdb->query($sql);
    }

    /**
     * Init a chrono.
     *
     * @param string $cron_id The cron slug.
     * @return string The unique id for this $cron_id.
     * @since 3.2.0
     *
     */
    public static function init_chrono($cron_id) {
        $fingerprint = uniqid('', true);
        $cron_id .= '*' . substr($fingerprint, count($fingerprint)-6, 80);
        self::$chrono[$cron_id] = microtime(true);
        return $cron_id;
    }

    /**
     * Stop a chrono.
     *
     * @param string $cron_id The cron slug.
     * @since 3.2.0
     *
     */
    public static function stop_chrono($cron_id) {
        if (array_key_exists($cron_id, self::$chrono)) {
            $time = round(1000*(microtime(true) - self::$chrono[$cron_id]), 0);
            unset(self::$chrono[$cron_id]);
            if ($time >=0) {
                $key = substr($cron_id, 0, strpos($cron_id, '*'));
                if (!array_key_exists($key, self::$stats)) {
                    self::$stats[$key]['count'] = 0;
                    self::$stats[$key]['time'] = 0;
                }
                self::$stats[$key]['count'] += 1;
                self::$stats[$key]['time'] += $time;
            }
        }
    }

    /**
     * Write watchdog stats.
     *
     * @since 3.2.0
     */
    public static function write_stats(){
        $now = date('Y-m-d H') . ':00:00';
        global $wpdb;
        $err_bup = $wpdb->show_errors(false);
        $field_insert = array('timestamp', 'cron', 'count', 'time');
        foreach (self::$stats as $key => $values) {
            $value_insert = array();
            $value_update = array();
            $value_insert[] = "'".$now."'";
            $value_insert[] = "'".$key."'";
            $value_insert[] = $values['count'];
            $value_insert[] = $values['time'];
            $value_update[] = 'count=count+' . $values['count'];
            $value_update[] = 'time=time+' . $values['time'];
            $sql = "INSERT INTO " . $wpdb->prefix.self::live_weather_station_performance_cron_table() . " ";
            $sql .= "(" . implode(',', $field_insert) . ") ";
            $sql .= "VALUES (" . implode(',', $value_insert) . ") ";
            $sql .= "ON DUPLICATE KEY UPDATE " . implode(',', $value_update) . ";";
            try {
                $wpdb->query($sql);
            }
            catch (\Exception $ex) {
                Logger::warning('Watchdog',null,null,null,null,null,null,'Table "' . $wpdb->prefix.self::live_weather_station_performance_cron_table() . '" not ready. This is likely a temporary defect.');
            }
        }
        $wpdb->show_errors($err_bup);
    }

}