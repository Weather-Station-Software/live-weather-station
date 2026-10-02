<?php

namespace WeatherStation\System\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\System\SQL\Guard;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Environment\Manager as Env;

/**
 * The class to manage backend and frontend cache.
 *
 * @package Includes\System
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Cache {

    use Storage;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Cache Manager';
    private static $chrono = array();
    private static $stats = array();
    public static $backend_expiry = 900;   // 15 minutes
    public static $frontend_expiry = 119;  // 2 minutes - 1 second
    public static $widget_expiry = 119;    // 2 minutes - 1 second
    public static $dgraph_expiry = 119;    // 2 minutes - 1 second
    public static $ygraph_expiry = 43200;  // 12 hours
    public static $cgraph_expiry = 172800;  // 48 hours
    public static $wp_expiry = 7200;       // 2 hours
    public static $i18n_expiry = 43200;    // 12 hours
    public static $db_stat = 'stat';
    public static $db_stat_log = 'stat_log';
    public static $db_bg_processes = 'stat_bgp';
    public static $db_stat_quota = 'stat_quota';
    public static $db_stat_perf = 'stat_perf';
    public static $db_stat_perf_cache = 'stat_perf_cache';
    public static $db_stat_perf_cron = 'stat_perf_cron';
    public static $db_stat_perf_database = 'stat_perf_database';
    public static $db_stat_perf_event = 'stat_perf_event';
    public static $db_stat_perf_quota = 'stat_perf_quota';
    public static $db_stat_operational = 'stat_operational';
    public static $widget = 'lws_cache_widget';
    public static $dgraph = 'lws_cache_dgraph';
    public static $ygraph = 'lws_cache_ygraph';
    public static $cgraph = 'lws_cache_cgraph';
    public static $frontend = 'lws_cache_control';
    public static $backend = 'lws_cache_backend';
    public static $i18n = 'lws_i18n';

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Flush all the specified transient element.
     *
     * @param string $pref Optional. Prefix of transients to delete.
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.1.0
     *
     */
    private static function _flush($pref='lws_', $expired=true) {
    	$cron_id = Watchdog::init_chrono(Watchdog::$cache_flush_name);
        global $wpdb;
        $result = 0;
        if ($expired) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WordPress options table: expired transients of this plugin are purged live (a cached list would defeat the purge); the LIKE pattern and the timestamp go through $wpdb->prepare()
            $delete = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d;", $wpdb->esc_like('_transient_timeout_' . $pref) . '%', time()));
        }
        else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WordPress options table: expired transients of this plugin are purged live (a cached list would defeat the purge); the LIKE pattern and the timestamp go through $wpdb->prepare()
            $delete = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s;", $wpdb->esc_like('_transient_timeout_' . $pref) . '%'));
        }
        foreach($delete as $transient) {
            $key = str_replace('_transient_timeout_', '', $transient);
            /*if (delete_transient($key)) {
                $result += 1;
            }*/
        }
        Watchdog::stop_chrono($cron_id);
        return $result;
    }

    /**
     * Init a chrono.
     *
     * @param string $cache_id The cached element slug.
     * @since 3.1.0
     *
     */
    private static function _init_chrono($cache_id) {
        self::$chrono[$cache_id] = microtime(true);
    }

    /**
     * Stop a chrono.
     *
     * @param string $cache_id The cached element slug.
     * @param boolean $hit Optional. True if it was in cache, false otherwise.
     * @since 3.1.0
     *
     */
    private static function _stop_chrono($cache_id, $hit=true) {
        if (array_key_exists($cache_id, self::$chrono)) {
            $time = round(1000*(microtime(true) - self::$chrono[$cache_id]), 0);
            unset(self::$chrono[$cache_id]);
            if ($time >=0) {
                $key = 'unknown';
                if ($hit) {
                    $pref = 'hit_';
                }
                else {
                    $pref = 'miss_';
                }
                if ((strpos($cache_id, self::$i18n)!==false) || (strpos($cache_id, self::$backend)!==false)) {
                    $key = 'backend';
                }
                if (strpos($cache_id, self::$widget)!==false) {
                    $key = 'widget';
                }
                if (strpos($cache_id, self::$frontend)!==false) {
                    $key = 'frontend';
                }
                if (strpos($cache_id, self::$dgraph)!==false) {
                    $key = 'dgraph';
                }
                if (strpos($cache_id, self::$ygraph)!==false) {
                    $key = 'ygraph';
                }
                if (strpos($cache_id, self::$cgraph)!==false) {
                    $key = 'cgraph';
                }
                if (!array_key_exists($key, self::$stats)) {
                    self::$stats[$key]['hit_count'] = 0;
                    self::$stats[$key]['hit_time'] = 0;
                    self::$stats[$key]['miss_count'] = 0;
                    self::$stats[$key]['miss_time'] = 0;
                }
                self::$stats[$key][$pref.'count'] += 1;
                self::$stats[$key][$pref.'time'] += $time;
            }
        }
    }

    /**
     * Get the value of a cached element.
     *
     * If the element does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return mixed Value of element.
     * @since 3.0.0
     *
     */
    public static function get_backend($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        self::_init_chrono(self::$backend.'_'.$cache_id);
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            if ($r = live_weather_station_meta_uncache(self::$backend.'_'.$cache_id, self::$backend_expiry)) {
                self::_stop_chrono(self::$backend.'_'.$cache_id);
            }
            return $r;
        }
    }

    /**
     * Set/update the value of a cached element.
     *
     * You do not need to serialize values. If the value needs to be serialized, then
     * it will be serialized before it is set.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param mixed $value Cached element value, must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @return bool False if value was not set and true if value was set.
     * @since 3.0.0
     *
     */
    public static function set_backend($cache_id, $value) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            $r = live_weather_station_meta_cache(self::$backend.'_'.$cache_id, $value, self::$backend_expiry);
            self::_stop_chrono(self::$backend.'_'.$cache_id, false);
            return $r;
        }
    }

    /**
     * Delete the cached element.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return bool True if successful, false otherwise.
     * @since 3.0.0
     *
     */
    public static function invalidate_backend($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            return live_weather_station_meta_rmcache(self::$backend.'_'.$cache_id);
        }
    }

    /**
     * Flush all the cached element.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.0.0
     *
     */
    public static function flush_backend($expired=true) {
        return self::_flush(self::$backend.'_'.self::$db_stat, $expired);
    }

    /**
     * Flush the cached performance element.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.2.0
     *
     */
    public static function flush_performance($expired=true) {
        return self::_flush(self::$backend.'_'.self::$db_stat_perf, $expired);
    }

    /**
     * Get the value of a cached element.
     *
     * If the element does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return mixed Value of element.
     * @since 3.0.0
     *
     */
    public static function get_frontend($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        self::_init_chrono(self::$frontend.'_'.$cache_id);
        if (!(bool)get_option('live_weather_station_frontend_cache')) {
            return false;
        }
        else {
            if ($r = live_weather_station_meta_uncache(self::$frontend.'_'.$cache_id, self::$frontend_expiry)) {
                self::_stop_chrono(self::$frontend.'_'.$cache_id);
            }
            return $r;
        }
    }

    /**
     * Check that a write to the public caches (frontend, graph, widget) stays bounded.
     *
     * The name must fit in a transient name (172 characters), and the number of entries written per hour is capped
     * (same mechanism as the budget of the lttextual cache in DataOutput). The cap is far above what a site needs
     * (entries are shared between visitors and live 2 minutes); when it is reached, the value is just not cached.
     *
     * @param string $name The full name of the cache item.
     * @param string $bucket The name of the counter.
     * @param integer $max Optional. The maximum number of writes per hour (default: the option, 6000; 0 removes the limit).
     * @return boolean True if the item can be written.
     * @since 3.9.0
     */
    private static function public_write_allowed($name, $bucket, $max=null) {
        if (strlen($name) > 150) {
            return false;
        }
        if ($max === null) {
            $max = (int)apply_filters('live_weather_station_cache_budget', (int)get_option('live_weather_station_cache_budget', 6000), $bucket);
        }
        if ($max <= 0) {
            return true;
        }
        $state = get_transient($bucket);
        $now = time();
        if (!is_array($state) || !isset($state['start'], $state['count']) || ($now - (int)$state['start']) >= HOUR_IN_SECONDS) {
            $state = array('start' => $now, 'count' => 0);
        }
        if ((int)$state['count'] >= $max) {
            return false;
        }
        $state['count'] = (int)$state['count'] + 1;
        set_transient($bucket, $state, HOUR_IN_SECONDS);
        return true;
    }

    /**
     * Set/update the value of a cached element.
     *
     * You do not need to serialize values. If the value needs to be serialized, then
     * it will be serialized before it is set.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param mixed $value Cached element value, must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @return bool False if value was not set and true if value was set.
     * @since 3.0.0
     *
     */
    public static function set_frontend($cache_id, $value) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_frontend_cache')) {
            return false;
        }
        else {
            if (!self::public_write_allowed(self::$frontend.'_'.$cache_id, 'lws_frontend_cache_budget')) {
                return false;
            }
            $r = live_weather_station_meta_cache(self::$frontend.'_'.$cache_id, $value, self::$frontend_expiry);
            self::_stop_chrono(self::$frontend.'_'.$cache_id, false);
            return $r;
        }
    }

    /**
     * Delete the cached element.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return bool True if successful, false otherwise.
     * @since 3.0.0
     *
     */
    public static function invalidate_frontend($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_frontend_cache')) {
            return false;
        }
        else {
            return live_weather_station_meta_rmcache(self::$frontend.'_'.$cache_id);
        }
    }

    /**
     * Flush all the cached element.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.1.0
     *
     */
    public static function flush_frontend($expired=true) {
        return self::_flush(self::$frontend, $expired);
    }

    /**
     * Get the value of a cached element.
     *
     * If the element does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param string $mode Optional. The mode in which searching for.
     * @return mixed Value of element.
     * @since 3.4.0
     *
     */
    public static function get_graph($cache_id, $mode='daily') {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if ($mode == 'climat') {
            $id = self::$cgraph.'_'.$cache_id;
            $expiry = self::$cgraph_expiry;
            $cache = (bool)get_option('live_weather_station_cgraph_cache');
        }
        elseif ($mode == 'yearly') {
            $id = self::$ygraph.'_'.$cache_id;
            $expiry = self::$ygraph_expiry;
            $cache = (bool)get_option('live_weather_station_ygraph_cache');
        }
        else {
            $id = self::$dgraph.'_'.$cache_id;
            $expiry = self::$dgraph_expiry;
            $cache = (bool)get_option('live_weather_station_dgraph_cache');
        }
        self::_init_chrono($id);
        if (!$cache) {
            return false;
        }
        else {
            if ($r = live_weather_station_meta_uncache($id, $expiry)) {
                self::_stop_chrono($id);
            }
            return $r;
        }
    }

    /**
     * Set/update the value of a cached element.
     *
     * You do not need to serialize values. If the value needs to be serialized, then
     * it will be serialized before it is set.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param string $mode The mode in which searching for.
     * @param mixed $value Cached element value, must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @return bool False if value was not set and true if value was set.
     * @since 3.4.0
     *
     */
    public static function set_graph($cache_id, $mode, $value) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if ($mode == 'climat') {
            $id = self::$cgraph.'_'.$cache_id;
            $expiry = self::$cgraph_expiry;
            $cache = (bool)get_option('live_weather_station_cgraph_cache');
        }
        elseif ($mode == 'yearly') {
            $id = self::$ygraph.'_'.$cache_id;
            $expiry = self::$ygraph_expiry;
            $cache = (bool)get_option('live_weather_station_ygraph_cache');
        }
        else {
            $id = self::$dgraph.'_'.$cache_id;
            $expiry = self::$dgraph_expiry;
            $cache = (bool)get_option('live_weather_station_dgraph_cache');
        }
        if (!$cache) {
            return false;
        }
        else {
            if (!self::public_write_allowed($id, 'lws_graph_cache_budget')) {
                return false;
            }
            $r = live_weather_station_meta_cache($id, $value, $expiry);
            self::_stop_chrono($id, false);
            return $r;
        }
    }

    /**
     * Delete the cached element.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param string $mode Optional. The mode in which searching for.
     * @return bool True if successful, false otherwise.
     * @since 3.4.0
     *
     */
    public static function invalidate_graph($cache_id, $mode='daily') {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if ($mode == 'yearly') {
            $id = self::$ygraph.'_'.$cache_id;
            $cache = (bool)get_option('live_weather_station_ygraph_cache');
        }
        else {
            $id = self::$dgraph.'_'.$cache_id;
            $cache = (bool)get_option('live_weather_station_dgraph_cache');
        }
        if (!$cache) {
            return false;
        }
        else {
            return live_weather_station_meta_rmcache($id);
        }
    }

    /**
     * Flush all the cached element.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.4.0
     *
     */
    public static function flush_graph($expired=true) {
        return self::_flush(self::$dgraph, $expired) + self::_flush(self::$ygraph, $expired) + self::_flush(self::$cgraph, $expired);
    }

    /**
     * Get the value of a cached element.
     *
     * If the element does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return mixed Value of element.
     * @since 3.1.0
     *
     */
    public static function get_widget($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        self::_init_chrono(self::$widget.'_'.$cache_id);
        if (!(bool)get_option('live_weather_station_widget_cache')) {
            return false;
        }
        else {
            if ($r = live_weather_station_meta_uncache(self::$widget.'_'.$cache_id, self::$widget_expiry)) {
                self::_stop_chrono(self::$widget.'_'.$cache_id);
            }
            return $r;
        }
    }

    /**
     * Set/update the value of a cached element.
     *
     * You do not need to serialize values. If the value needs to be serialized, then
     * it will be serialized before it is set.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param mixed $value Cached element value, must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @return bool False if value was not set and true if value was set.
     * @since 3.1.0
     *
     */
    public static function set_widget($cache_id, $value) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_widget_cache')) {
            return false;
        }
        else {
            if (!self::public_write_allowed(self::$widget.'_'.$cache_id, 'lws_widget_cache_budget')) {
                return false;
            }
            $r = live_weather_station_meta_cache(self::$widget.'_'.$cache_id, $value, self::$widget_expiry);
            self::_stop_chrono(self::$widget.'_'.$cache_id, false);
            return $r;
        }
    }

    /**
     * Delete the cached element.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return bool True if successful, false otherwise.
     * @since 3.1.0
     *
     */
    public static function invalidate_widget($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_widget_cache')) {
            return false;
        }
        else {
            return live_weather_station_meta_rmcache(self::$widget.'_'.$cache_id);
        }
    }

    /**
     * Flush all the cached element.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.1.0
     *
     */
    public static function flush_widget($expired=true) {
        return self::_flush(self::$widget, $expired);
    }

    /**
     * Get the value of a cached element.
     *
     * If the element does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return mixed Value of element.
     * @since 3.1.0
     *
     */
    public static function get_i18n($cache_id) {
        self::_init_chrono(self::$i18n.'_'.$cache_id);
        if ($r = live_weather_station_meta_uncache(self::$i18n.'_'.$cache_id, self::$i18n_expiry)) {
            self::_stop_chrono(self::$i18n.'_'.$cache_id);
        }
        return $r;
    }

    /**
     * Set/update the value of a cached element.
     *
     * You do not need to serialize values. If the value needs to be serialized, then
     * it will be serialized before it is set.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param mixed $value Cached element value, must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @return bool False if value was not set and true if value was set.
     * @since 3.1.0
     *
     */
    public static function set_i18n($cache_id, $value) {
        $r = live_weather_station_meta_cache(self::$i18n.'_'.$cache_id, $value, self::$i18n_expiry);
        self::_stop_chrono(self::$i18n.'_'.$cache_id, false);
        return $r;
    }

    /**
     * Delete the cached element.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return bool True if successful, false otherwise.
     * @since 3.1.0
     *
     */
    public static function invalidate_i18n($cache_id) {
        return live_weather_station_meta_rmcache(self::$i18n.'_'.$cache_id);
    }

    /**
     * Flush all the cached element.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.1.0
     *
     */
    public static function flush_i18n($expired=true) {
        return self::_flush(self::$i18n, $expired);
    }

    /**
     * Get the value of a cached element.
     *
     * If the element does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return mixed Value of element.
     * @since 3.0.0
     *
     */
    public static function get_query($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_query_cache')) {
            return false;
        }
        else {
            return wp_cache_get($cache_id);
        }
    }

    /**
     * Set/update the value of a cached element.
     *
     * You do not need to serialize values. If the value needs to be serialized, then
     * it will be serialized before it is set.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @param mixed $value Cached element value, must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @param integer $expiry Optional. Override default cache expiry time.
     * @return bool False if value was not set and true if value was set.
     * @since 3.0.0
     *
     */
    public static function set_query($cache_id, $value, $expiry=null) {
        if (!$expiry) {
            $expiry = self::$wp_expiry;
        }
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_query_cache')) {
            return false;
        }
        else {
            return wp_cache_set($cache_id, $value, '', $expiry);
        }
    }

    /**
     * Delete the cached element.
     *
     * @param string $cache_id The cached element slug. Expected to not be SQL-escaped.
     * @return bool True if successful, false otherwise.
     * @since 3.0.0
     *
     */
    public static function invalidate_query($cache_id) {
        $cache_id = Env::get_cache_prefix() . $cache_id;
        if (!(bool)get_option('live_weather_station_query_cache')) {
            return false;
        }
        else {
            return wp_cache_delete($cache_id);
        }
    }

    /**
     * Flush the cache.
     *
     * @return bool True if successful, false otherwise.
     * @since 3.0.0
     *
     */
    public static function flush_query() {
    	return true;
        if (!(bool)get_option('live_weather_station_query_cache')) {
            return false;
        }
        else {
            return wp_cache_flush();
        }
    }

    /**
     * Flush all cached elements.
     *
     * @param bool $expired Optional. Delete only expired transients.
     * @return integer Count of deleted transients.
     * @since 3.1.0
     *
     */
    public static function flush_full($expired=true) {
        $result = 0;
        $result += self::flush_backend($expired);
        $result += self::flush_frontend($expired);
        $result += self::flush_performance($expired);
        $result += self::flush_widget($expired);
        $result += self::flush_graph($expired);
        if (!$expired) {
            $result += self::flush_i18n($expired);
        }
        return $result;
    }

    /**
     * Reset the plugin cache.
     *
     * @since 3.2.0
     */
    public static function reset(){
        self::_flush('lws_', false);
        Logger::notice('Cache Manager',null,null,null,null,null,null,'Cache has been reset.');
    }

    /**
     * Flush all obsolete cached items.
     *
     * @since 3.1.0
     */
    public function flush(){
        $result = self::flush_full();
        if ($result > 0) {
            if ($result == 1) {
                Logger::notice($this->facility,null,null,null,null,null,null,'1 obsolete item flushed.');
            }
            if ($result > 1) {
                Logger::notice($this->facility,null,null,null,null,null,null,$result . ' obsolete items flushed.');
            }
        }
        else {
            Logger::info($this->facility,null,null,null,null,null,null,'No obsolete item to flush.');
        }
    }

    /**
     * Write cache stats.
     *
     * @since 3.1.0
     */
    public static function write_stats(){
        $now = gmdate('Y-m-d H') . ':00:00';
        global $wpdb;
        $err_bup = $wpdb->show_errors(false);
        $fields = array ('hit_count', 'hit_time', 'miss_count', 'miss_time');
        $field_insert = array('timestamp');
        $value_insert = array('%s');
        $value_update = array();
        $args_insert = array($now);
        $args_update = array();
        foreach (self::$stats as $key => $values) {
            foreach ($fields as $field) {
                if (self::$stats[$key][$field] >0) {
                    $column = Guard::ident($key.'_'.$field);
                    if ($column === null) {
                        continue;
                    }
                    $type = (substr($field, -5) === '_time') ? '%f' : '%d';
                    $field_insert[] = $column;
                    $value_insert[] = $type;
                    $args_insert[] = self::$stats[$key][$field];
                    $value_update[] = $column . '=' . $column . '+' . $type;
                    $args_update[] = self::$stats[$key][$field];
                }
            }
        }
        if (count($value_update) > 0) {
            $sql = "INSERT INTO " . $wpdb->prefix.self::live_weather_station_performance_cache_table() . " ";
            $sql .= "(" . implode(',', $field_insert) . ") ";
            $sql .= "VALUES (" . implode(',', $value_insert) . ") ";
            $sql .= "ON DUPLICATE KEY UPDATE " . implode(',', $value_update) . ";";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table live_weather_station_performance_cache: column names are checked by Guard::ident(), values are bound through the %d/%f placeholders of $wpdb->prepare(); write path, cannot be cached
            $wpdb->query($wpdb->prepare($sql, array_merge($args_insert, $args_update)));
        }
        $wpdb->show_errors($err_bup);
    }

    /**
     * Delete old records.
     *
     * @since 3.1.0
     */
    public static function rotate() {
        global $wpdb;
        $now = gmdate('Y-m-d H:i:s', time() - MONTH_IN_SECONDS);
        $sql = "DELETE FROM " . $wpdb->prefix.self::live_weather_station_performance_cache_table() . " WHERE ";
        $sql .= "timestamp<%s;";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table live_weather_station_performance_cache: table name from self::live_weather_station_performance_cache_table(), the date is bound by $wpdb->prepare(); write path, cannot be cached
        $wpdb->query($wpdb->prepare($sql, $now));
    }
}