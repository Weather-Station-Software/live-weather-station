<?php

namespace WeatherStation\System\Cache;

/**
 * The class to manage backend and frontend cache.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Cache {

    private $Live_Weather_Station;
    private $version;
    private static $backend_expiry = 1800;
    private static $frontend_expiry = 1800;

    public static $db_stat_log = 'db_stat_log';
    public static $db_stat_operational = 'db_stat_operational';

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.0.0
     */
    public function __construct( $Live_Weather_Station, $version ) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
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
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            return get_transient($cache_id);
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
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            return set_transient($cache_id, $value, self::$backend_expiry);
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
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            return delete_transient($cache_id);
        }
    }

    /**
     * Flush all the cached element.
     *
     * @return bool True if successful, false otherwise.
     * @since 3.0.0
     *
     */
    public static function flush_backend() {
        if (!(bool)get_option('live_weather_station_backend_cache')) {
            return false;
        }
        else {
            delete_transient(self::$db_stat_log);
            delete_transient(self::$frontend_expiry);
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
    public static function get_frontend($cache_id) {
        if (!(bool)get_option('live_weather_station_frontend_cache')) {
            return false;
        }
        else {
            return get_transient($cache_id);
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
    public static function set_frontend($cache_id, $value) {
        if (!(bool)get_option('live_weather_station_frontend_cache')) {
            return false;
        }
        else {
            return set_transient($cache_id, $value, self::$frontend_expiry);
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
        if (!(bool)get_option('live_weather_station_frontend_cache')) {
            return false;
        }
        else {
            return delete_transient($cache_id);
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
    public static function get_query($cache_id) {
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
     * @return bool False if value was not set and true if value was set.
     * @since 3.0.0
     *
     */
    public static function set_query($cache_id, $value) {
        if (!(bool)get_option('live_weather_station_query_cache')) {
            return false;
        }
        else {
            return wp_cache_set($cache_id, $value);
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
        if (!(bool)get_option('live_weather_station_query_cache')) {
            return false;
        }
        else {
            return wp_cache_flush();
        }
    }
}