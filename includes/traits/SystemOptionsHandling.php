<?php

namespace WeatherStation\System\Options;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Environment\Manager as EnvManager;

/**
 * Functionalities for options handling.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

trait Handling {

    private static $live_weather_station_partial_translation = 0 ;

    private static $live_weather_station_version = '-' ;
    private static $live_weather_station_logger_level = 5 ;
    private static $live_weather_station_logger_rotate = 10000 ;
    private static $live_weather_station_logger_retention = 14 ;

    private static $live_weather_station_advanced_mode = false;
    private static $live_weather_station_txt_cache_bypass = false;
    private static $live_weather_station_backend_cache = true;
    private static $live_weather_station_query_cache = true;
    private static $live_weather_station_frontend_cache = true;
    private static $live_weather_station_widget_cache = true;
    private static $live_weather_station_redirect_internal_links = false;
    private static $live_weather_station_redirect_external_links = true;
    private static $live_weather_station_time_shift_threshold = 30;
    private static $live_weather_station_auto_manage_netatmo = true;
    private static $live_weather_station_overload_hc = false;
    private static $live_weather_station_show_technical = false;
    private static $live_weather_station_show_analytics = false;
    private static $live_weather_station_auto_update = true;

    private static $live_weather_station_map_zoom = 16;
    private static $live_weather_station_map_layer = 'X';

    private static $live_weather_station_netatmo_refresh_token = '';
    private static $live_weather_station_netatmo_access_token = '';
    private static $live_weather_station_netatmo_connected = false;
    private static $live_weather_station_netatmohc_refresh_token = '';
    private static $live_weather_station_netatmohc_access_token = '';
    private static $live_weather_station_netatmohc_connected = false;
    private static $live_weather_station_owm_apikey = '';
    private static $live_weather_station_owm_plan = 0;
    private static $live_weather_station_wug_apikey = '';
    private static $live_weather_station_wug_plan = 0;
    private static $live_weather_station_unit_temperature = 0;  
    private static $live_weather_station_unit_pressure = 0;     
    private static $live_weather_station_unit_wind_strength = 0;
    private static $live_weather_station_unit_altitude = 0;     
    private static $live_weather_station_unit_distance = 0;
    private static $live_weather_station_unit_rain_snow = 0;
    private static $live_weather_station_unit_gas = 0;
    private static $live_weather_station_measure_only = 0;
    private static $live_weather_station_obsolescence = 0;
    private static $live_weather_station_min_max_mode = 0;
    private static $live_weather_station_wind_semantics = 0;
    private static $live_weather_station_moon_icons = 0;

    /**
     * Get the thresholds options of the plugin.
     *
     * @return array The thresholds options of the plugin.
     *
     * @since 3.0.0
     */
    public static function get_thresholds_options() {
        $result = array();
        $thresholds = self::live_weather_station_thresholds();
        foreach ($thresholds as $measure => $threshold) {
            $s = 'live_weather_station_' . $measure;
            foreach ($threshold as $key => $val) {
                $result[$s.'_'.$key] = $val;
            }
        }
        return $result;
    }

    /**
     * Get the thresholds available for the plugin.
     *
     * @return array The thresholds available for the plugin.
     *
     * @since 3.0.0
     */
    public static function get_thresholds() {
        $result = array();
        $thresholds = self::live_weather_station_thresholds();
        foreach ($thresholds as $measure => $threshold) {
            $result[] = $measure;
        }
        return $result;
    }

    /**
     * Get the thresholds for all measurements types.
     *
     * @return array The min, max, low alarm and high alarm values.
     *
     * @since 3.0.0
     */
    protected static function live_weather_station_thresholds() {
        return array (
            'pressure' => array (               'min_value' => 900,
                                                'max_value' => 1080,
                                                'min_alarm' => 1000,
                                                'max_alarm' => 1025,
                                                'min_boundary' => 850,
                                                'max_boundary' => 1100),
            'humint' => array (                 'min_value' => 30,
                                                'max_value' => 90,
                                                'min_alarm' => 35,
                                                'max_alarm' => 55,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'humext' => array (                 'min_value' => 0,
                                                'max_value' => 100,
                                                'min_alarm' => 25,
                                                'max_alarm' => 75,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'tempint' => array (                'min_value' => 10,
                                                'max_value' => 25,
                                                'min_alarm' => 16,
                                                'max_alarm' => 22,
                                                'min_boundary' => -30,
                                                'max_boundary' => 50),
            'tempext' => array (                'min_value' => -20,
                                                'max_value' => 40,
                                                'min_alarm' => 0,
                                                'max_alarm' => 30,
                                                'min_boundary' => -30,
                                                'max_boundary' => 50),
            'dew_point' => array (              'min_value' => 0,
                                                'max_value' => 20,
                                                'min_alarm' => 0,
                                                'max_alarm' => 0,
                                                'min_boundary' => 0,
                                                'max_boundary' => 50),
            'frost_point' => array (            'min_value' => -20,
                                                'max_value' => 0,
                                                'min_alarm' => 0,
                                                'max_alarm' => 0,
                                                'min_boundary' => -30,
                                                'max_boundary' => 5),
            'heat_index' => array (             'min_value' => 21,
                                                'max_value' => 43,
                                                'min_alarm' => 20,
                                                'max_alarm' => 39,
                                                'min_boundary' => 20,
                                                'max_boundary' => 50),
            'humidex' => array (                'min_value' => 21,
                                                'max_value' => 43,
                                                'min_alarm' => 20,
                                                'max_alarm' => 44,
                                                'min_boundary' => 20,
                                                'max_boundary' => 50),
            'wind_chill' => array (             'min_value' => -40,
                                                'max_value' => 10,
                                                'min_alarm' => 0,
                                                'max_alarm' => 0,
                                                'min_boundary' => -90,
                                                'max_boundary' => 10),
            'cloud_ceiling' => array (          'min_value' => 0,
                                                'max_value' => 3000,
                                                'min_alarm' => 30,
                                                'max_alarm' => 3000,
                                                'min_boundary' => 0,
                                                'max_boundary' => 9000),
            'cloud_cover' => array (            'min_value' => 0,
                                                'max_value' => 100,
                                                'min_alarm' => 0,
                                                'max_alarm' => 100,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'rain' => array (                   'min_value' => 0,
                                                'max_value' => 10,
                                                'min_alarm' => 0,
                                                'max_alarm' => 8,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'rain_hour_aggregated' => array (   'min_value' => 0,
                                                'max_value' => 20,
                                                'min_alarm' => 0,
                                                'max_alarm' => 10,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'rain_day_aggregated' => array (    'min_value' => 0,
                                                'max_value' => 40,
                                                'min_alarm' => 0,
                                                'max_alarm' => 20,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'rain_month_aggregated' => array (  'min_value' => 0,
                                                'max_value' => 100,
                                                'min_alarm' => 0,
                                                'max_alarm' => 50,
                                                'min_boundary' => 0,
                                                'max_boundary' => 300),
            'rain_year_aggregated' => array (   'min_value' => 0,
                                                'max_value' => 1000,
                                                'min_alarm' => 0,
                                                'max_alarm' => 500,
                                                'min_boundary' => 0,
                                                'max_boundary' => 3000),
            'snow' => array (                   'min_value' => 0,
                                                'max_value' => 500,
                                                'min_alarm' => 0,
                                                'max_alarm' => 200,
                                                'min_boundary' => 0,
                                                'max_boundary' => 1000),
            'windangle' => array (              'min_value' => 0,
                                                'max_value' => 360,
                                                'min_alarm' => 0,
                                                'max_alarm' => 0,
                                                'min_boundary' => 0,
                                                'max_boundary' => 360),
            'windstrength' => array (           'min_value' => 0,
                                                'max_value' => 100,
                                                'min_alarm' => 0,
                                                'max_alarm' => 70,
                                                'min_boundary' => 0,
                                                'max_boundary' => 250),
            'co2' => array (                    'min_value' => 0,
                                                'max_value' => 2000,
                                                'min_alarm' => 0,
                                                'max_alarm' => 1000,
                                                'min_boundary' => 0,
                                                'max_boundary' => 5000),
            'o3' => array (                     'min_value' => 100,
                                                'max_value' => 500,
                                                'min_alarm' => 200,
                                                'max_alarm' => 1000,
                                                'min_boundary' => 0,
                                                'max_boundary' => 1000),
            'co' => array (                     'min_value' => 0.1,
                                                'max_value' => 0.2,
                                                'min_alarm' => 0,
                                                'max_alarm' => 0.18,
                                                'min_boundary' => 0,
                                                'max_boundary' => 0.5),
            'noise' => array (                  'min_value' => 0,
                                                'max_value' => 90,
                                                'min_alarm' => 0,
                                                'max_alarm' => 55,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'health_idx' => array (             'min_value' => 0,
                                                'max_value' => 100,
                                                'min_alarm' => 40,
                                                'max_alarm' => 100,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
            'cbi' => array (                    'min_value' => -20,
                                                'max_value' => 120,
                                                'min_alarm' => -20,
                                                'max_alarm' => 75,
                                                'min_boundary' => 0,
                                                'max_boundary' => 100),
        );
    }

    /**
     * Delete the thresholds options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function delete_thresholds_options() {
        $thresholds = self::get_thresholds_options();
        foreach ($thresholds as $key => $val) {
            delete_option($key);
        }
    }

    /**
     * Init the thresholds options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function init_thresholds_options() {
        $thresholds = self::get_thresholds_options();
        foreach ($thresholds as $key => $val) {
            update_option($key, $val);
        }
    }

    /**
     * Delete all the options of the plugin.
     *
     * @since 1.0.0
     */
    protected static function delete_options() {
        delete_option('live_weather_station_partial_translation');
        delete_option('live_weather_station_version');
        delete_option('live_weather_station_logger_level');
        delete_option('live_weather_station_logger_rotate');
        delete_option('live_weather_station_logger_retention');
        delete_option('live_weather_station_netatmo_refresh_token');
        delete_option('live_weather_station_netatmo_access_token');
        delete_option('live_weather_station_netatmo_connected');
        delete_option('live_weather_station_netatmohc_refresh_token');
        delete_option('live_weather_station_netatmohc_access_token');
        delete_option('live_weather_station_netatmohc_connected');
        delete_option('live_weather_station_owm_apikey');
        delete_option('live_weather_station_owm_plan');
        delete_option('live_weather_station_wug_apikey');
        delete_option('live_weather_station_wug_plan');
        delete_option('live_weather_station_unit_temperature');
        delete_option('live_weather_station_unit_pressure');
        delete_option('live_weather_station_unit_wind_strength');
        delete_option('live_weather_station_unit_altitude');
        delete_option('live_weather_station_unit_distance');
        delete_option('live_weather_station_unit_rain_snow');
        delete_option('live_weather_station_unit_gas');
        delete_option('live_weather_station_measure_only');
        delete_option('live_weather_station_obsolescence');
        delete_option('live_weather_station_min_max_mode');
        delete_option('live_weather_station_wind_semantics');
        delete_option('live_weather_station_moon_icons');
        delete_option('live_weather_station_logger_installed');
        delete_option('live_weather_station_advanced_mode');
        delete_option('live_weather_station_txt_cache_bypass');
        delete_option('live_weather_station_frontend_cache');
        delete_option('live_weather_station_widget_cache');
        delete_option('live_weather_station_query_cache');
        delete_option('live_weather_station_backend_cache');
        delete_option('live_weather_station_redirect_internal_links');
        delete_option('live_weather_station_redirect_external_links');
        delete_option('live_weather_station_time_shift_threshold');
        delete_option('live_weather_station_map_zoom');
        delete_option('live_weather_station_map_layer');
        delete_option('live_weather_station_auto_manage_netatmo');
        delete_option('live_weather_station_overload_hc');
        delete_option('live_weather_station_show_technical');
        delete_option('live_weather_station_show_analytics');
        delete_option('live_weather_station_auto_update');
        self::delete_thresholds_options();
    }

    /**
     * Init the Netatmo options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function init_netatmo_options() {
        update_option('live_weather_station_netatmo_refresh_token', self::$live_weather_station_netatmo_refresh_token);
        update_option('live_weather_station_netatmo_access_token', self::$live_weather_station_netatmo_access_token);
        update_option('live_weather_station_netatmo_connected', (self::$live_weather_station_netatmo_connected ? 1 : 0));
    }


    /**
     * Init the NetatmoHC options of the plugin.
     *
     * @since 3.1.0
     */
    protected static function init_netatmohc_options() {
        update_option('live_weather_station_netatmohc_refresh_token', self::$live_weather_station_netatmohc_refresh_token);
        update_option('live_weather_station_netatmohc_access_token', self::$live_weather_station_netatmohc_access_token);
        update_option('live_weather_station_netatmohc_connected', (self::$live_weather_station_netatmohc_connected ? 1 : 0));
    }

    /**
     * Init the OpenWeatherMap options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function init_owm_options() {
        update_option('live_weather_station_owm_apikey', self::$live_weather_station_owm_apikey);
        update_option('live_weather_station_owm_plan', self::$live_weather_station_owm_plan);
    }

    /**
     * Init the WeatherUnderground options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function init_wug_options() {
        update_option('live_weather_station_wug_apikey', self::$live_weather_station_wug_apikey);
        update_option('live_weather_station_wug_plan', self::$live_weather_station_wug_plan);
    }

    /**
     * Init the system options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function init_system_options() {
        update_option('live_weather_station_logger_level', self::$live_weather_station_logger_level);
        update_option('live_weather_station_logger_rotate', self::$live_weather_station_logger_rotate);
        update_option('live_weather_station_logger_retention', self::$live_weather_station_logger_retention);
        update_option('live_weather_station_txt_cache_bypass', self::$live_weather_station_txt_cache_bypass);
        update_option('live_weather_station_frontend_cache', self::$live_weather_station_frontend_cache);
        update_option('live_weather_station_widget_cache', self::$live_weather_station_widget_cache);
        update_option('live_weather_station_query_cache', self::$live_weather_station_query_cache);
        update_option('live_weather_station_backend_cache', self::$live_weather_station_backend_cache);
        update_option('live_weather_station_redirect_internal_links', self::$live_weather_station_redirect_internal_links);
        update_option('live_weather_station_redirect_external_links', self::$live_weather_station_redirect_external_links);
        update_option('live_weather_station_time_shift_threshold', self::$live_weather_station_time_shift_threshold);
        update_option('live_weather_station_auto_manage_netatmo', self::$live_weather_station_auto_manage_netatmo);
        update_option('live_weather_station_overload_hc', self::$live_weather_station_overload_hc);
        update_option('live_weather_station_show_technical', self::$live_weather_station_show_technical);
        update_option('live_weather_station_show_analytics', self::$live_weather_station_show_analytics);
        update_option('live_weather_station_auto_update', self::$live_weather_station_auto_update);
    }

    /**
     * Init the mapping options of the plugin.
     *
     * @since 3.0.0
     */
    protected static function init_map_options() {
        update_option('live_weather_station_map_zoom', self::$live_weather_station_map_zoom);
        update_option('live_weather_station_map_layer', self::$live_weather_station_map_layer);
    }


    /**
     * Common base for the switching methods.
     *
     * @since 3.0.0
     */
    protected static function init_display_options() {
        update_option('live_weather_station_measure_only', self::$live_weather_station_measure_only);
        update_option('live_weather_station_obsolescence', self::$live_weather_station_obsolescence);
        update_option('live_weather_station_min_max_mode', self::$live_weather_station_min_max_mode);
        update_option('live_weather_station_wind_semantics', self::$live_weather_station_wind_semantics);
        update_option('live_weather_station_moon_icons', self::$live_weather_station_moon_icons);
        update_option('live_weather_station_unit_gas', self::$live_weather_station_unit_gas);
    }

    /**
     * Init all options of the plugin.
     *
     * @since 1.0.0
     */
    protected static function init_options() {
        self::init_netatmo_options();
        self::init_netatmohc_options();
        self::init_owm_options();
        self::init_wug_options();
        self::init_system_options();
        self::init_display_options();
        self::init_thresholds_options();
        self::init_map_options();
        update_option('live_weather_station_unit_temperature', self::$live_weather_station_unit_temperature);
        update_option('live_weather_station_unit_pressure', self::$live_weather_station_unit_pressure);
        update_option('live_weather_station_unit_wind_strength', self::$live_weather_station_unit_wind_strength);
        update_option('live_weather_station_unit_altitude', self::$live_weather_station_unit_altitude);
        update_option('live_weather_station_unit_distance', self::$live_weather_station_unit_distance);
        update_option('live_weather_station_unit_rain_snow', self::$live_weather_station_unit_rain_snow);
        update_option('live_weather_station_advanced_mode', (self::$live_weather_station_advanced_mode ? 1 : 0));
        update_option('live_weather_station_partial_translation', (self::$live_weather_station_partial_translation ? 1 : 0));
    }

    /**
     * Switch the plugin to metric units and reset misc options.
     *
     * @param boolean $restrict Optional. Restrict to only display options;
     *
     * @since 3.0.0
     */
    protected static function switch_to_metric($restrict=false) {
        update_option('live_weather_station_unit_temperature', self::$live_weather_station_unit_temperature);
        update_option('live_weather_station_unit_pressure', self::$live_weather_station_unit_pressure);
        update_option('live_weather_station_unit_wind_strength', self::$live_weather_station_unit_wind_strength);
        update_option('live_weather_station_unit_altitude', self::$live_weather_station_unit_altitude);
        update_option('live_weather_station_unit_distance', self::$live_weather_station_unit_distance);
        update_option('live_weather_station_unit_rain_snow', self::$live_weather_station_unit_rain_snow);
        self::init_display_options();
        if (!$restrict) {
            self::init_system_options();
            self::init_thresholds_options();
            self::init_map_options();
        }
    }

    /**
     * Switch the plugin to metric units and reset misc options.
     *
     * @since 3.0.0
     */
    protected static function switch_to_imperial($restrict=false) {
        update_option('live_weather_station_unit_temperature', 1);
        update_option('live_weather_station_unit_pressure', 1);
        update_option('live_weather_station_unit_wind_strength', 1);
        update_option('live_weather_station_unit_altitude', 1);
        update_option('live_weather_station_unit_distance', 1);
        update_option('live_weather_station_unit_rain_snow', 1);
        self::init_display_options();
        if (!$restrict) {
            self::init_system_options();
            self::init_thresholds_options();
            self::init_map_options();
        }
    }

    /**
     * Verify (and completes) an array option of the plugin.
     *
     * @param string $option_name Name of the option.
     * @param array $val Default values if option doesn't exists.
     * @since 2.0.0
     */
    private static function verify_options_array($option_name, $val) {
        $count = count($val);
        $new_option = array();
        for ($i=0; $i<$count; $i++) {
            $new_option[$i] = get_option($option_name)[$i];
            if (false === $new_option[$i]) {
                $new_option[$i] = $val[$i];
            }
        }
        update_option($option_name, $new_option);
    }

    /**
     * Verify (and completes) a string option of the plugin.
     *
     * @param string $option_name Name of the option.
     * @param string $val Default value if option doesn't exists.
     * @since 2.0.0
     */
    private static function verify_option_string($option_name, $val) {
        if (false === get_option($option_name)) {
            update_option($option_name, $val);
        }
    }

    /**
     * Verify (and completes) an integer option of the plugin.
     *
     * @param string $option_name Name of the option.
     * @param integer $val Default value if option doesn't exists.
     * @since 2.8.0
     */
    private static function verify_option_integer($option_name, $val) {
        if (false === get_option($option_name)) {
            update_option($option_name, $val);
        }
    }

    /**
     * Verify (and completes) a boolean option of the plugin.
     *
     * @param string $option_name Name of the option.
     * @param boolean $val Default value if option doesn't exists.
     * @since 3.0.0
     */
    private static function verify_option_boolean($option_name, $val) {
        if (false === get_option($option_name)) {
            update_option($option_name, ($val ? 1 : 0));
        }
    }

    /**
     * Init the thresholds options of the plugin.
     *
     * @since 3.0.0
     */
    private static function verify_option_thresholds() {
        $thresholds = self::get_thresholds_options();
        foreach ($thresholds as $key => $val) {
            if (false === get_option($key)) {
                update_option($key, $val);
            }
        }
    }

    /**
     * Verify options of the plugin and, if needed, do a migration to 3.x options system.
     *
     * @param boolean $migrate Force migration to the 3.x options system.
     * @since 1.1.0
     */
    protected static function verify_options($migrate) {
        self::verify_option_string('live_weather_station_version', self::$live_weather_station_version);
        self::verify_option_integer('live_weather_station_logger_level', self::$live_weather_station_logger_level);
        self::verify_option_integer('live_weather_station_logger_rotate', self::$live_weather_station_logger_rotate);
        self::verify_option_integer('live_weather_station_logger_retention', self::$live_weather_station_logger_retention);
        self::verify_option_boolean('live_weather_station_txt_cache_bypass', self::$live_weather_station_txt_cache_bypass);
        self::verify_option_boolean('live_weather_station_frontend_cache', self::$live_weather_station_frontend_cache);
        self::verify_option_boolean('live_weather_station_widget_cache', self::$live_weather_station_widget_cache);
        self::verify_option_boolean('live_weather_station_query_cache', self::$live_weather_station_query_cache);
        self::verify_option_boolean('live_weather_station_backend_cache', self::$live_weather_station_backend_cache);
        self::verify_option_boolean('live_weather_station_redirect_internal_links', self::$live_weather_station_redirect_internal_links);
        self::verify_option_boolean('live_weather_station_redirect_external_links', self::$live_weather_station_redirect_external_links);
        self::verify_option_integer('live_weather_station_time_shift_threshold', self::$live_weather_station_time_shift_threshold);
        self::verify_option_thresholds();
        self::verify_option_integer('live_weather_station_map_zoom', self::$live_weather_station_map_zoom);
        self::verify_option_string('live_weather_station_map_layer', self::$live_weather_station_map_layer);
        self::verify_option_boolean('live_weather_station_auto_manage_netatmo', self::$live_weather_station_auto_manage_netatmo);
        self::verify_option_boolean('live_weather_station_overload_hc', self::$live_weather_station_overload_hc);
        self::verify_option_boolean('live_weather_station_show_technical', self::$live_weather_station_show_technical);
        self::verify_option_boolean('live_weather_station_show_analytics', self::$live_weather_station_show_analytics);
        self::$live_weather_station_auto_update = EnvManager::is_updatable();
        self::verify_option_boolean('live_weather_station_auto_update', self::$live_weather_station_auto_update);
        self::verify_option_boolean('live_weather_station_advanced_mode', self::$live_weather_station_advanced_mode);
        self::verify_option_boolean('live_weather_station_partial_translation', self::$live_weather_station_partial_translation);
        if ($migrate) {
            self::verify_option_string('live_weather_station_netatmo_refresh_token', (get_option('live_weather_station_netatmo_account') ? get_option('live_weather_station_netatmo_account')[0] : self::$live_weather_station_netatmo_refresh_token));
            self::verify_option_string('live_weather_station_netatmo_access_token', (get_option('live_weather_station_netatmo_account') ? get_option('live_weather_station_netatmo_account')[1] : self::$live_weather_station_netatmo_access_token));
            self::verify_option_boolean('live_weather_station_netatmo_connected', (get_option('live_weather_station_netatmo_account') ? get_option('live_weather_station_netatmo_account')[2] : self::$live_weather_station_netatmo_connected));
            delete_option('live_weather_station_netatmo_account');
        }
        else {
            self::verify_option_string('live_weather_station_netatmo_refresh_token', self::$live_weather_station_netatmo_refresh_token);
            self::verify_option_string('live_weather_station_netatmo_access_token', self::$live_weather_station_netatmo_access_token);
            self::verify_option_boolean('live_weather_station_netatmo_connected', self::$live_weather_station_netatmo_connected);
            self::verify_option_string('live_weather_station_netatmohc_refresh_token', self::$live_weather_station_netatmohc_refresh_token);
            self::verify_option_string('live_weather_station_netatmohc_access_token', self::$live_weather_station_netatmohc_access_token);
            self::verify_option_boolean('live_weather_station_netatmohc_connected', self::$live_weather_station_netatmohc_connected);
        }
        if ($migrate) {
            self::verify_option_string('live_weather_station_owm_apikey', (get_option('live_weather_station_owm_account') ? get_option('live_weather_station_owm_account')[0] : self::$live_weather_station_owm_apikey));
            delete_option('live_weather_station_owm_account');
        }
        else {
            self::verify_option_string('live_weather_station_owm_apikey', self::$live_weather_station_owm_apikey);
            self::verify_option_integer('live_weather_station_owm_plan', self::$live_weather_station_owm_plan);
        }
        if ($migrate) {
            //
        }
        else {
            self::verify_option_string('live_weather_station_wug_apikey', self::$live_weather_station_wug_apikey);
            self::verify_option_integer('live_weather_station_wug_plan', self::$live_weather_station_wug_plan);
        }
        if ($migrate) {
            self::verify_option_integer('live_weather_station_unit_temperature', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[0] : self::$live_weather_station_unit_temperature));
            self::verify_option_integer('live_weather_station_unit_pressure', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[1] : self::$live_weather_station_unit_pressure));
            self::verify_option_integer('live_weather_station_unit_wind_strength', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[2] : self::$live_weather_station_unit_wind_strength));
            self::verify_option_integer('live_weather_station_unit_altitude', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[4] : self::$live_weather_station_unit_altitude));
            self::verify_option_integer('live_weather_station_unit_distance', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[5] : self::$live_weather_station_unit_distance));
            self::verify_option_integer('live_weather_station_unit_rain_snow', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[9] : self::$live_weather_station_unit_rain_snow));
            self::verify_option_integer('live_weather_station_unit_gas', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[10] : self::$live_weather_station_unit_gas));
            self::verify_option_integer('live_weather_station_measure_only', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[3] : self::$live_weather_station_measure_only));
            self::verify_option_integer('live_weather_station_obsolescence', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[6] : self::$live_weather_station_obsolescence));
            self::verify_option_integer('live_weather_station_min_max_mode', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[7] : self::$live_weather_station_min_max_mode));
            self::verify_option_integer('live_weather_station_wind_semantics', (get_option('live_weather_station_settings') ? get_option('live_weather_station_settings')[8] : self::$live_weather_station_wind_semantics));
            delete_option('live_weather_station_owm_account');
        }
        else {
            self::verify_option_integer('live_weather_station_unit_temperature', self::$live_weather_station_unit_temperature);
            self::verify_option_integer('live_weather_station_unit_pressure', self::$live_weather_station_unit_pressure);
            self::verify_option_integer('live_weather_station_unit_wind_strength', self::$live_weather_station_unit_wind_strength);
            self::verify_option_integer('live_weather_station_unit_altitude', self::$live_weather_station_unit_altitude);
            self::verify_option_integer('live_weather_station_unit_distance', self::$live_weather_station_unit_distance);
            self::verify_option_integer('live_weather_station_unit_rain_snow', self::$live_weather_station_unit_rain_snow);
            self::verify_option_integer('live_weather_station_unit_gas', self::$live_weather_station_unit_gas);
            self::verify_option_integer('live_weather_station_measure_only', self::$live_weather_station_measure_only);
            self::verify_option_integer('live_weather_station_obsolescence', self::$live_weather_station_obsolescence);
            self::verify_option_integer('live_weather_station_min_max_mode', self::$live_weather_station_min_max_mode);
            self::verify_option_integer('live_weather_station_wind_semantics', self::$live_weather_station_wind_semantics);
            self::verify_option_integer('live_weather_station_moon_icons', self::$live_weather_station_moon_icons);
        }
        if ($migrate) {
            self::verify_option_integer('live_weather_station_logger_rotate', true);
        }
        else {
            self::verify_option_integer('live_weather_station_logger_rotate', self::$live_weather_station_logger_rotate);
        }
        if ($migrate) {
            Logger::info('Updater',null,null,null,null,null,null,'Plugin options correctly migrated to 3.x option system management.');
        }
    }

    /**
     * Reset all options of the plugin.
     *
     * @since 1.0.0
     */
    protected static function reset_options() {
        self::delete_options();
        self::init_options();
    }

}