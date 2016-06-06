<?php

/**
 * Options manipulation functionalities for Live Weather Station plugin
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

trait Options_Manipulation {

    private static $live_weather_station_version = '-' ;
    private static $live_weather_station_netatmo_account = array('', '',false) ;
    private static $live_weather_station_owm_account = array('', 0) ;
    private static $live_weather_station_settings = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ;
    private static $live_weather_station_logger_level = 6 ;
    private static $live_weather_station_logger_rotate = 50000 ;

    /**
     * Drop options of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @static
     */
    protected static function delete_options() {
        delete_option('live_weather_station_version');
        delete_option('live_weather_station_netatmo_account');
        delete_option('live_weather_station_owm_account');
        delete_option('live_weather_station_settings');
        delete_option('live_weather_station_logger_installed');
        delete_option('live_weather_station_logger_level');
        delete_option('live_weather_station_logger_rotate');
    }

    /**
     * Init options of the plugin.
     *
     * @since    1.0.0
     * @static
     */
    protected static function init_options() {
        update_option('live_weather_station_version', self::$live_weather_station_version);
        update_option('live_weather_station_netatmo_account', self::$live_weather_station_netatmo_account);
        update_option('live_weather_station_owm_account', self::$live_weather_station_owm_account);
        update_option('live_weather_station_settings', self::$live_weather_station_settings);
        update_option('live_weather_station_logger_level', self::$live_weather_station_logger_level);
        update_option('live_weather_station_logger_rotate', self::$live_weather_station_logger_rotate);
    }

    /**
     * Verify a single option array of the plugin.
     *
     * @param       string      $option_name    Name of the option.
     * @param       array       $val            Default values if not present.
     * @since    2.0.0
     * @static
     */
    private static function _verify_options_array($option_name, $val) {
        $count = count($val);
        $new_option = array();
        for ($i=0; $i<$count; $i++) {
            $new_option[$i] = get_option($option_name)[$i];
            if (!$new_option[$i]) {
                $new_option[$i] = $val[$i];
            }
        }
        update_option($option_name, $new_option);
    }

    /**
     * Verify a single option string of the plugin.
     *
     * @param       string      $option_name    Name of the option.
     * @param       string      $val            Default values if not present.
     * @since    2.0.0
     * @static
     */
    private static function _verify_option_string($option_name, $val) {
        if (!get_option($option_name)) {
            update_option($option_name, $val);
        }
    }

    /**
     * Verify a single option string of the plugin.
     *
     * @param       string      $option_name    Name of the option.
     * @param       integer     $val            Default values if not present.
     * @since    2.8.0
     * @static
     */
    private static function _verify_option_integer($option_name, $val) {
        if (!get_option($option_name)) {
            update_option($option_name, $val);
        }
    }

    /**
     * Verify options of the plugin.
     *
     * @since    1.1.0
     * @access   protected
     * @static
     */
    protected static function verify_options() {
        self::_verify_option_string('live_weather_station_version', self::$live_weather_station_version);
        self::_verify_options_array('live_weather_station_netatmo_account', self::$live_weather_station_netatmo_account);
        self::_verify_options_array('live_weather_station_owm_account', self::$live_weather_station_owm_account);
        self::_verify_options_array('live_weather_station_settings', self::$live_weather_station_settings);
        self::_verify_option_integer('live_weather_station_logger_level', self::$live_weather_station_logger_level);
        self::_verify_option_integer('live_weather_station_logger_rotate', self::$live_weather_station_logger_rotate);
    }

    /**
     * Reset options of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @static
     */
    protected static function reset_options() {
        self::init_options();
        self::delete_options();
    }
}