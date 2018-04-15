<?php

namespace WeatherStation\System\Schedules;

use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Data\Data;
use WeatherStation\System\Analytics\Performance;
use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\MetOffice\Plugin\Pusher as Wow_Pusher;
use WeatherStation\SDK\OpenWeatherMap\Plugin\Pusher as Owm_Pusher;
use WeatherStation\SDK\PWSWeather\Plugin\Pusher as Pws_Pusher;
use WeatherStation\SDK\WeatherUnderground\Plugin\Pusher as Wug_Pusher;
use WeatherStation\SDK\Netatmo\Plugin\Updater as Netatmo_Updater;
use WeatherStation\SDK\Netatmo\Plugin\HCUpdater as Netatmo_HCUpdater;
use WeatherStation\SDK\Clientraw\Plugin\StationUpdater as Clientraw_Updater;
use WeatherStation\SDK\Realtime\Plugin\StationUpdater as Realtime_Updater;
use WeatherStation\SDK\Stickertags\Plugin\StationUpdater as Stickertags_Updater;
use WeatherStation\SDK\WeatherFlow\Plugin\StationUpdater as WFLW_Updater;
use WeatherStation\SDK\Pioupiou\Plugin\StationUpdater as PIOU_Updater;
use WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentUpdater as Owm_Current_Updater;
use WeatherStation\SDK\OpenWeatherMap\Plugin\StationUpdater as Owm_Station_Updater;
use WeatherStation\SDK\WeatherUnderground\Plugin\StationUpdater as Wug_Station_Updater;
use WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionUpdater as Owm_Pollution_Updater;
use WeatherStation\System\I18N\Handling as i18n;
use WeatherStation\System\Plugin\Stats;
use WeatherStation\Data\History\Builder as HistoryBuilder;
use WeatherStation\Data\History\Cleaner as HistoryCleaner;
use WeatherStation\System\Device\Manager as DeviceManager;

/**
 * Functionalities for schedules & cron handling.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

trait Handling {

    public static $pools = array('system', 'push', 'pull', 'history');


    // WARNING: CRON NAMES MUST NOT HAVE MORE THAN 30 CAR...

    // SYSTEM
    public static $watchdog_name = 'lws_watchdog';
    public static $translation_update_name = 'lws_translation_update';
    public static $log_rotate_name = 'lws_log_rotate';
    public static $cache_flush_name = 'lws_cache_flush';
    public static $stats_clean_name = 'lws_stats_clean';
    public static $integrity_check_name = 'lws_integrity_check';
    public static $plugin_stat_name = 'lws_plugin_stat';
    public static $device_management_name = 'lws_device_management';
    public static $cron_system = array('lws_watchdog', 'lws_translation_update', 'lws_log_rotate', 'lws_cache_flush',
                                        'lws_stats_clean', 'lws_integrity_check', 'lws_plugin_stat', 'lws_device_management');

    // HISTORY
    public static $history_build_name = 'lws_history_build';
    public static $history_clean_name = 'lws_history_clean';
    public static $cron_history = array('lws_history_build', 'lws_history_clean');

    // PULL
    public static $netatmo_update_schedule_name = 'lws_netatmo_update';
    public static $netatmo_hc_update_schedule_name = 'lws_netatmo_hc_update';
    public static $owm_update_current_schedule_name = 'lws_owm_current_update';
    public static $owm_update_station_schedule_name = 'lws_owm_station_update';
    public static $wug_update_station_schedule_name = 'lws_wug_station_update';
    public static $wflw_update_station_schedule_name = 'lws_wflw_station_update';
    public static $piou_update_station_schedule_name = 'lws_piou_station_update';
    public static $raw_update_station_schedule_name = 'lws_raw_station_update';
    public static $real_update_station_schedule_name = 'lws_real_station_update';
    public static $txt_update_station_schedule_name = 'lws_txt_station_update';
    public static $owm_update_pollution_schedule_name = 'lws_owm_pollution_update';
    public static $cron_pull = array('lws_netatmo_update', 'lws_netatmo_hc_update', 'lws_owm_current_update', 
                                    'lws_owm_station_update', 'lws_wug_station_update', 'lws_raw_station_update',
                                    'lws_real_station_update', 'lws_txt_station_update', 'lws_owm_pollution_update',
                                    'lws_wflw_station_update', 'lws_piou_station_update');

    // PUSH
    public static $wow_push_schedule_name = 'lws_wow_current_push';
    public static $owm_push_schedule_name = 'lws_owm_current_push';
    public static $pws_push_schedule_name = 'lws_pws_current_push';
    public static $wug_push_schedule_name = 'lws_wug_current_push';
    public static $cron_push = array('lws_wow_current_push', 'lws_owm_current_push', 'lws_pws_current_push', 'lws_wug_current_push');

    // OLD
    public static $netatmo_push_schedule_name = 'lws_netatmo_push';
    public static $owm_update_schedule_name = 'lws_owm_update';
    public static $push_schedule_name = 'lws_current_push';
    public static $cron_old = array('lws_netatmo_push', 'lws_owm_update', 'lws_current_push');


    /**
     * Force a cron execution now.
     *
     * @param string $cron_id The cron task identifier.
     * @return boolean True if the cron has been executed, false otherwise.
     *
     * @since 3.2.0
     */
    private static function force_cron($cron_id) {
        $result = false;
        if (self::is_legitimate_cron($cron_id)) {
            do_action($cron_id);
            $result = true;
        }
        return $result;
    }

    /**
     * Schedule or reschedule a cron.
     *
     * @param string $cron_id The cron task identifier.
     * @param string $system Optional. The system which have initiated the operation.
     * @param boolean $beforeforce Optional. Will the cron be forced just after?
     * @param boolean $random Optional. Is the time shift random?
     * @return boolean True if the cron has been rescheduled, false otherwise.
     *
     * @since 3.2.0
     */
    public static function reschedule_cron($cron_id, $system='Core', $beforeforce=false, $random=false) {
        $result = false;
        if (self::is_legitimate_cron($cron_id)) {
            $cron = str_replace('lws_', '', $cron_id);
            $launcher = 'launch_' . $cron . '_cron';
            try {
                $schedules = wp_get_schedules();
                if (wp_get_schedule($cron_id) && array_key_exists(wp_get_schedule($cron_id), $schedules)) {
                    $scheduled = $schedules[wp_get_schedule($cron_id)]['interval'];
                }
                else {
                    $scheduled = 0;
                }
            }
            catch (\Exception $e){
                $scheduled = 0;
            }
            if (method_exists(get_called_class(), $launcher)) {
                if (($d = wp_next_scheduled($cron_id)) && ($system != 'Watchdog')) {
                    wp_clear_scheduled_hook($cron_id);
                }
                $now = time();
                if (($d > $now) && !$beforeforce){
                    $dts = $d - $now;
                }
                else {
                    $dts = 0;
                }
                if ($dts < $scheduled) {
                    $dts += $scheduled;
                }
                if ($random) {
                    $dts = random_int(1,180);
                }
                call_user_func(array(get_called_class(), $launcher), $dts, $system);
                $result = true;
            }
        }
        else {
            Logger::alert('Core', null, null, null, null, null, null, 'Trying to (re)schedule an unknown task: '.$cron_id);
        }
        return $result;
    }

    /**
     * Define a cron.
     *
     * @param string $cron_id The cron task identifier.
     * @return boolean True if the cron has been rescheduled, false otherwise.
     *
     * @since 3.2.0
     */
    protected static function define_cron($cron_id) {
        $result = false;
        if (self::is_legitimate_cron($cron_id)) {
            $cron = str_replace('lws_', '', $cron_id);
            $definer = 'define_' . $cron . '_cron';
            if (method_exists(get_called_class(), $definer)) {
                call_user_func(array(get_called_class(), $definer));
                $result = true;
            }
        }
        else {
            Logger::alert('Core', null, null, null, null, null, null, 'Trying to define an unknown task: '.$cron_id);
        }
        return $result;
    }

    /**
     * Delete schedules.
     *
     * @since 3.2.0
     */
    protected static function define_schedules() {
        foreach (array_merge(self::$cron_system, self::$cron_pull, self::$cron_push, self::$cron_history) as $cron) {
            self::define_cron($cron);
        }
    }

    /**
     * Delete schedules.
     *
     * @since 1.0.0
     */
    protected static function delete_schedules() {
        foreach (array_merge(self::$cron_system, self::$cron_pull, self::$cron_push, self::$cron_old, self::$cron_history) as $cron) {
            wp_clear_scheduled_hook($cron);
        }
    }

    /**
     * Init schedules.
     *
     * @since 2.0.0
     */
    protected static function init_schedules() {
        foreach (array_merge(self::$cron_system, self::$cron_pull, self::$cron_push, self::$cron_history) as $cron) {
            if ($cron != self::$watchdog_name) {
                self::reschedule_cron($cron, 'Watchdog', false, true);
            }
        }
    }

    /**
     * Force a cron execution now.
     *
     * @param string $cron_id The cron task identifier.
     * @param string $system Optional. The system which have initiated the operation.
     * @return boolean True if the cron has been executed, false otherwise.
     *
     * @since 3.2.0
     */
    public static function force_and_reschedule_cron($cron_id, $system='Core') {
        if (self::reschedule_cron($cron_id, $system, true)) {
            return self::force_cron($cron_id);
        }
        return false;
    }

    /**
     * Get cron pool id.
     *
     * @param string $cron_id The cron task identifier.
     * @return boolean True if the cron is a Weather Station hook, false otherwise.
     *
     * @since 3.2.0
     */
    public static function is_legitimate_cron($cron_id) {
        return (in_array(self::get_cron_pool($cron_id), self::$pools));
    }

    /**
     * Get cron pool id.
     *
     * @param string $cron_id The cron task identifier.
     * @return string The cron pool id.
     *
     * @since 3.2.0
     */
    public static function get_cron_pool($cron_id) {
        $field_defs = array('system' => self::$cron_system, 'push' => self::$cron_push, 'pull' => self::$cron_pull, 'history' => self::$cron_history);
        foreach (self::$pools as $field) {
            foreach ($field_defs[$field] as $def) {
                if ($def == $cron_id) {
                    return $field;
                }
            }
        }
        return 'unknow';
    }

    /**
     * Get pool name.
     *
     * @param string $pool The pool.
     * @return string The pool name.
     *
     * @since 3.2.0
     */
    public static function get_pool_name($pool) {
        switch ($pool) {
            case 'system' :
                return __('system', 'live-weather-station');
                break;
            case 'push' :
                return __('sharing', 'live-weather-station');
                break;
            case 'pull' :
                return __('collection', 'live-weather-station');
                break;
            case 'history' :
                return __('history', 'live-weather-station');
                break;
            default :
                return __('generic', 'live-weather-station');
        }
    }

    /**
     * Get cron pool name.
     *
     * @param string $cron_id The cron task identifier.
     * @return string The cron pool name.
     *
     * @since 3.2.0
     */
    public static function get_cron_pool_name($cron_id) {
        $field_names = array('system' => __('system', 'live-weather-station'),
                             'push' => __('sharing', 'live-weather-station'),
                             'pull' => __('collection', 'live-weather-station'),
                             'history' => __('history', 'live-weather-station'),
                             'unknow' => __('generic', 'live-weather-station'));
        return $field_names[self::get_cron_pool($cron_id)];
    }

    /**
     * Get cron name.
     *
     * @param string $cron_id The cron.
     * @return string The cron name.
     *
     * @since 3.2.0
     */
    public static function get_cron_name($cron_id) {
        switch ($cron_id) {
            case 'lws_watchdog':
                return __('Watchdog', 'live-weather-station');
                break;
            case 'lws_translation_update':
                return __('Partial translations updates', 'live-weather-station');
                break;
            case 'lws_log_rotate':
                return __('Events log rotation', 'live-weather-station');
                break;
            case 'lws_cache_flush':
                return __('Cache flushing', 'live-weather-station');
                break;
            case 'lws_stats_clean':
                return __('Statistics cleaning', 'live-weather-station');
                break;
            case 'lws_device_management':
                return __('Device management', 'live-weather-station');
                break;
            case 'lws_integrity_check':
                return __('Data integrity checking', 'live-weather-station');
                break;
            case 'lws_history_build':
                return __('Historical data consolidation', 'live-weather-station');
                break;
            case 'lws_history_clean':
                return __('Historical data cleaning', 'live-weather-station');
                break;
            case 'lws_netatmo_update':
                return __('Netatmo - Weather station', 'live-weather-station');
                break;
            case 'lws_netatmo_hc_update':
                return __('Netatmo - Healthy Home Coach', 'live-weather-station');
                break;
            case 'lws_owm_current_update':
                return __('OpenWeatherMap - Current observations', 'live-weather-station');
                break;
            case 'lws_owm_station_update':
                return __('OpenWeatherMap - Weather station', 'live-weather-station');
                break;
            case 'lws_wug_station_update':
                return __('Weather Underground - Weather station', 'live-weather-station');
                break;
            case 'lws_wflw_station_update':
                return __('WeatherFlow - Weather station', 'live-weather-station');
                break;
            case 'lws_piou_station_update':
                return __('Pioupiou - Sensor', 'live-weather-station');
                break;
            case 'lws_raw_station_update':
                return __('Clientraw - Weather station', 'live-weather-station');
                break;
            case 'lws_real_station_update':
                return __('Realtime - Weather station', 'live-weather-station');
                break;
            case 'lws_txt_station_update':
                return __('Stickertags - Weather station', 'live-weather-station');
                break;
            case 'lws_owm_pollution_update':
                return __('OpenWeatherMap - Pollution', 'live-weather-station');
                break;
            case 'lws_wow_current_push':
                return __('Met Office - Outdoor data', 'live-weather-station');
                break;
            case 'lws_owm_current_push':
                return __('OpenWeatherMap - Outdoor data', 'live-weather-station');
                break;
            case 'lws_pws_current_push':
                return __('PWS Weather - Outdoor data', 'live-weather-station');
                break;
            case 'lws_wug_current_push':
                return __('Weather Underground - Outdoor data', 'live-weather-station');
                break;
            case 'lws_plugin_stat':
                return __('Plugin statistics', 'live-weather-station');
                break;
            default :
                return __('unknown', 'live-weather-station');
        }
    }

    /**
     * Define Netatmo Updater cron job.
     *
     * @since 2.7.0
     */
    protected static function define_netatmo_update_cron() {
        $plugin_netatmo_update_cron = new Netatmo_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$netatmo_update_schedule_name, array($plugin_netatmo_update_cron, 'cron_run'));
    }

    /**
     * Launch the Netatmo Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 2.7.0
     */
    protected static function launch_netatmo_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$netatmo_update_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'five_minutes', self::$netatmo_update_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$netatmo_update_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define Netatmo HC Updater cron job.
     *
     * @since 3.1.0
     */
    protected static function define_netatmo_hc_update_cron() {
        $plugin_netatmo_hc_update_cron = new Netatmo_HCUpdater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$netatmo_hc_update_schedule_name, array($plugin_netatmo_hc_update_cron, 'cron_run'));
    }

    /**
     * Launch the Netatmo HC Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.1.0
     */
    protected static function launch_netatmo_hc_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$netatmo_hc_update_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'five_minutes', self::$netatmo_hc_update_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$netatmo_hc_update_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define Clientraw Updater cron job.
     *
     * @since 3.0.0
     */
    protected static function define_raw_station_update_cron() {
        $plugin_raw_update_cron = new Clientraw_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$raw_update_station_schedule_name, array($plugin_raw_update_cron, 'cron_run'));
    }

    /**
     * Launch the Clientraw Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.0.0
     */
    protected static function launch_raw_station_update_cron($timeshift=0, $system='Watchdog') {
        if (get_option('live_weather_station_cron_speed', 0) == 0) {
            $rec = 'five_minutes';
        }
        else {
            $rec = 'two_minutes';
        }
        if (!wp_next_scheduled(self::$raw_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, $rec, self::$raw_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$raw_update_station_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define Realtime Updater cron job.
     *
     * @since 3.0.0
     */
    protected static function define_real_station_update_cron() {
        $plugin_real_update_cron = new Realtime_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$real_update_station_schedule_name, array($plugin_real_update_cron, 'cron_run'));
    }

    /**
     * Launch the Realtime Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.0.0
     */
    protected static function launch_real_station_update_cron($timeshift=0, $system='Watchdog') {
        if (get_option('live_weather_station_cron_speed', 0) == 0) {
            $rec = 'five_minutes';
        }
        else {
            $rec = 'two_minutes';
        }
        if (!wp_next_scheduled(self::$real_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, $rec, self::$real_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$real_update_station_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define Stickertags Updater cron job.
     *
     * @since 3.3.0
     */
    protected static function define_txt_station_update_cron() {
        $plugin_txt_update_cron = new Stickertags_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$txt_update_station_schedule_name, array($plugin_txt_update_cron, 'cron_run'));
    }

    /**
     * Launch the Stickertags Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.3.0
     */
    protected static function launch_txt_station_update_cron($timeshift=0, $system='Watchdog') {
        if (get_option('live_weather_station_cron_speed', 0) == 0) {
            $rec = 'five_minutes';
        }
        else {
            $rec = 'two_minutes';
        }
        if (!wp_next_scheduled(self::$txt_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, $rec, self::$txt_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$txt_update_station_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define WeatherFlow Updater cron job.
     *
     * @since 3.3.0
     */
    protected static function define_wflw_station_update_cron() {
        $plugin_wflw_update_cron = new WFLW_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$wflw_update_station_schedule_name, array($plugin_wflw_update_cron, 'cron_run'));
    }

    /**
     * Launch the WeatherFlow Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.3.0
     */
    protected static function launch_wflw_station_update_cron($timeshift=0, $system='Watchdog') {
        if (get_option('live_weather_station_cron_speed', 0) == 0) {
            $rec = 'five_minutes';
        }
        else {
            $rec = 'two_minutes';
        }
        if (!wp_next_scheduled(self::$wflw_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, $rec, self::$wflw_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$wflw_update_station_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define Pioupiou Updater cron job.
     *
     * @since 3.5.0
     */
    protected static function define_piou_station_update_cron() {
        $plugin_piou_update_cron = new PIOU_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$piou_update_station_schedule_name, array($plugin_piou_update_cron, 'cron_run'));
    }

    /**
     * Launch the Pioupiou Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.5.0
     */
    protected static function launch_piou_station_update_cron($timeshift=0, $system='Watchdog') {
        if (get_option('live_weather_station_cron_speed', 0) == 0) {
            $rec = 'five_minutes';
        }
        else {
            $rec = 'two_minutes';
        }
        if (!wp_next_scheduled(self::$piou_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, $rec, self::$piou_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$piou_update_station_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define OWM Current Updater cron job.
     *
     * @since 2.7.0
     */
    protected static function define_owm_current_update_cron() {
        $plugin_owm_current_cron = new Owm_Current_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$owm_update_current_schedule_name, array($plugin_owm_current_cron, 'cron_run'));
    }

    /**
     * Launch the OWM Current Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since    2.7.0
     */
    protected static function launch_owm_current_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$owm_update_current_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'fifteen_minutes', self::$owm_update_current_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$owm_update_current_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define OWM Station Updater cron job.
     *
     * @since 3.0.0
     */
    /*protected static function define_owm_station_update_cron() {
        $plugin_owm_station_cron = new Owm_Station_Updater(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$owm_update_station_schedule_name, array($plugin_owm_station_cron, 'cron_run'));
    }*/

    /**
     * Launch the OWM Station Updater cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.0.0
     */
    /*protected static function launch_owm_station_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$owm_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'ten_minutes', self::$owm_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$owm_update_station_schedule_name).'" (re)scheduled.');
        }
    }*/

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
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.0.0
     */
    protected static function launch_wug_station_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$wug_update_station_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'ten_minutes', self::$wug_update_station_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$wug_update_station_schedule_name).'" (re)scheduled.');
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
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since    2.7.0
     */
    protected static function launch_owm_pollution_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$owm_update_pollution_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'thirty_minutes', self::$owm_update_pollution_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$owm_update_pollution_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define log rotate cron job.
     *
     * @since 2.8.0
     */
    protected static function define_log_rotate_cron() {
        $logger = new Logger(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$log_rotate_name, array($logger, 'rotate'));
    }

    /**
     * Launch the log rotate cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 2.8.0
     */
    protected static function launch_log_rotate_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$log_rotate_name)) {
            wp_schedule_event(time() + $timeshift, 'daily', self::$log_rotate_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$log_rotate_name).'" (re)scheduled.');
        }
    }

    /**
     * Define historical data consolidation cron job.
     *
     * @since 3.3.2
     */
    protected static function define_history_build_cron() {
        $builder = new HistoryBuilder(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$history_build_name, array($builder, 'cron'));
    }

    /**
     * Launch the historical data consolidation cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.3.2
     */
    protected static function launch_history_build_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$history_build_name)) {
            wp_schedule_event(time() + $timeshift, 'four_hours', self::$history_build_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$history_build_name).'" (re)scheduled.');
        }
    }

    /**
     * Define plugin stats cron job.
     *
     * @since 3.4.0
     */
    protected static function define_plugin_stat_cron() {
        $stats = new Stats(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$plugin_stat_name, array($stats, 'cron'));
    }

    /**
     * Launch the plugin stats cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.4.0
     */
    protected static function launch_plugin_stat_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$plugin_stat_name)) {
            wp_schedule_event(time() + $timeshift, 'daily', self::$plugin_stat_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$plugin_stat_name).'" (re)scheduled.');
        }
    }

    /**
     * Define device management cron job.
     *
     * @since 3.4.0
     */
    protected static function define_device_management_cron() {
        $dm = new DeviceManager(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$device_management_name, array($dm, 'cron'));
    }

    /**
     * Launch the device management cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.4.0
     */
    protected static function launch_device_management_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$device_management_name)) {
            wp_schedule_event(time() + $timeshift, 'daily', self::$device_management_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$device_management_name).'" (re)scheduled.');
        }
    }

    /**
     * Define historical data consolidation cron job.
     *
     * @since 3.4.0
     */
    protected static function define_history_clean_cron() {
        $cleaner = new Historycleaner(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$history_clean_name, array($cleaner, 'cron'));
    }

    /**
     * Launch the historical data consolidation cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.4.0
     */
    protected static function launch_history_clean_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$history_clean_name)) {
            wp_schedule_event(time() + $timeshift, 'daily', self::$history_clean_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$history_clean_name).'" (re)scheduled.');
        }
    }

    /**
     * Define cache flushing cron job.
     *
     * @since 3.1.0
     */
    protected static function define_cache_flush_cron() {
        $cache = new Cache(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$cache_flush_name, array($cache, 'flush'));
    }

    /**
     * Launch the cache flushing cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.1.0
     */
    protected static function launch_cache_flush_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$cache_flush_name)) {
            wp_schedule_event(time() + $timeshift, 'twicedaily', self::$cache_flush_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$cache_flush_name).'" (re)scheduled.');
        }
    }

    /**
     * Define stats cleaning cron job.
     *
     * @since 3.1.0
     */
    protected static function define_stats_clean_cron() {
        $perf = new Performance(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$stats_clean_name, array($perf, 'rotate'));
    }

    /**
     * Launch the stats cleaning cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.1.0
     */
    protected static function launch_stats_clean_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$stats_clean_name)) {
            wp_schedule_event(time() + $timeshift, 'daily', self::$stats_clean_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$stats_clean_name).'" (re)scheduled.');
        }
    }

    /**
     * Define stats cleaning cron job.
     *
     * @since 3.1.0
     */
    protected static function define_integrity_check_cron() {
        $data = new Data(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$integrity_check_name, array($data, 'full_check'));
    }

    /**
     * Launch the data integrity check cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.1.0
     */
    protected static function launch_integrity_check_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$integrity_check_name)) {
            wp_schedule_event(time() + $timeshift, 'twicedaily', self::$integrity_check_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$integrity_check_name).'" (re)scheduled.');
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
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since    2.8.0
     */
    protected static function launch_translation_update_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$translation_update_name)) {
            wp_schedule_event(time() + $timeshift, 'daily', self::$translation_update_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$translation_update_name).'" (re)scheduled.');
        }
    }

    /**
     * Define WOW Pusher cron job.
     *
     * @since 3.2.0
     */
    protected static function define_wow_current_push_cron() {
        $plugin_wow_push_cron = new Wow_Pusher(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$wow_push_schedule_name, array($plugin_wow_push_cron, 'cron_run'));
    }

    /**
     * Launch the WOW Pusher cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.2.0
     */
    protected static function launch_wow_current_push_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$wow_push_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'twenty_minutes', self::$wow_push_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$wow_push_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define OWM Pusher cron job.
     *
     * @since 3.2.0
     */
    /*protected static function define_owm_current_push_cron() {
        $plugin_owm_push_cron = new Owm_Pusher(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$owm_push_schedule_name, array($plugin_owm_push_cron, 'cron_run'));
    }*/

    /**
     * Launch the OWM Pusher cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.2.0
     */
    /*protected static function launch_owm_current_push_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$owm_push_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'ten_minutes', self::$owm_push_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$owm_push_schedule_name).'" (re)scheduled.');
        }
    }*/

    /**
     * Define PWS Pusher cron job.
     *
     * @since 3.2.0
     */
    protected static function define_pws_current_push_cron() {
        $plugin_pws_push_cron = new Pws_Pusher(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$pws_push_schedule_name, array($plugin_pws_push_cron, 'cron_run'));
    }

    /**
     * Launch the PWS Pusher cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.2.0
     */
    protected static function launch_pws_current_push_cron($timeshift=0, $system='Watchdog') {
        if (!wp_next_scheduled(self::$pws_push_schedule_name)) {
            wp_schedule_event(time() + $timeshift, 'ten_minutes', self::$pws_push_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$pws_push_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Define WUG Pusher cron job.
     *
     * @since 3.2.0
     */
    protected static function define_wug_current_push_cron() {
        $plugin_wug_push_cron = new Wug_Pusher(LWS_PLUGIN_NAME, LWS_VERSION);
        add_action(self::$wug_push_schedule_name, array($plugin_wug_push_cron, 'cron_run'));
    }

    /**
     * Launch the WUG Pusher cron job if needed.
     *
     * @param integer $timeshift Optional. The first start for the cron from now on.
     * @param string $system Optional. The system which have initiated the launch.
     *
     * @since 3.2.0
     */
    protected static function launch_wug_current_push_cron($timeshift=0, $system='Watchdog') {
        if (get_option('live_weather_station_cron_speed', 0) == 0) {
            $rec = 'six_minutes';
        }
        else {
            $rec = 'two_minutes';
        }
        if (!wp_next_scheduled(self::$wug_push_schedule_name)) {
            wp_schedule_event(time() + $timeshift, $rec, self::$wug_push_schedule_name);
            Logger::info($system,null,null,null,null,null,null,'Task "'.self::get_cron_name(self::$wug_push_schedule_name).'" (re)scheduled.');
        }
    }

    /**
     * Add a new 3 minutes interval capacity to the WP cron feature.
     *
     * @since 3.3.0
     */
    public static function add_cron_02_minutes_interval($schedules) {
        $schedules['two_minutes'] = array(
            'interval' => 120,
            'display'  => __( 'Every two minutes', 'live-weather-station' ),
        );
        return $schedules;
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
     * Add a new 6 minutes interval capacity to the WP cron feature.
     *
     * @since 3.2.4
     */
    public static function add_cron_06_minutes_interval($schedules) {
        $schedules['six_minutes'] = array(
            'interval' => 360,
            'display'  => __( 'Every six minutes', 'live-weather-station' ),
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
     * Add a new 20 minutes interval capacity to the WP cron feature.
     *
     * @since 3.2.4
     */
    public static function add_cron_20_minutes_interval($schedules) {
        $schedules['twenty_minutes'] = array(
            'interval' => 1200,
            'display'  => __( 'Every twenty minutes', 'live-weather-station' ),
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

    /**
     * Add a new 4 hours interval capacity to the WP cron feature.
     *
     * @since 3.3.2
     */
    public static function add_cron_4_hours_interval($schedules) {
        $schedules['four_hours'] = array(
            'interval' => 14400,
            'display'  => __( 'Every four hours', 'live-weather-station' ),
        );
        return $schedules;
    }

    /**
     * Add a new 6 hours interval capacity to the WP cron feature.
     *
     * @since 3.4.0
     */
    public static function add_cron_6_hours_interval($schedules) {
        $schedules['six_hours'] = array(
            'interval' => 21600,
            'display'  => __( 'Every six hours', 'live-weather-station' ),
        );
        return $schedules;
    }
}