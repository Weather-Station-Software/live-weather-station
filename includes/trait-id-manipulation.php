<?php

/**
 * ID manipulation functionalities for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */



trait Id_Manipulation {

    /**
     * Generate a unique id for a OWM station.
     *
     * @param   integer     $station_id     The numeric id of the station
     * @return  string      The unique id of the station.
     *
     * @since    2.0.0
     */
    public static function get_unique_owm_id($station_id) {
        $st = 'xx'.str_pad(dechex($station_id), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Indicates if the id is the id of an OWM station.
     *
     * @param   integer     $station_id     The numeric id of the station
     * @return  boolean      True if it's an OWM station. False otherwise.
     *
     * @since    2.0.0
     */
    public static function is_owm_station($station_id) {
        return (substr($station_id, 0, 2) == 'xx');
    }

    /**
     * Indicates if the id is the id of an OWM current weather module.
     *
     * @param   integer     $module_id     The numeric id of the module.
     * @return  boolean      True if it's an OWM station. False otherwise.
     *
     * @since    2.0.0
     */
    public static function is_owm_current_module($module_id) {
        return (substr($module_id, 0, 2) == 'wm');
    }

    /**
     * Indicates if the id is the id of an OWM pollution module.
     *
     * @param   integer     $module_id     The numeric id of the module.
     * @return  boolean      True if it's an OWM station. False otherwise.
     *
     * @since    2.0.0
     */
    public static function is_owm_pollution_module($module_id) {
        return (substr($module_id, 0, 2) == 'po');
    }


    /**
     * Get a "virtual" ID for NAComputed module type.
     *
     * @param   string  $device_id      The device ID.
     * @return  string  A virtual ID for a NAComputed module attached to the device.
     *
     * @since    2.0.0
     */
    public static function get_computed_virtual_id($device_id) {
        $result = '00'.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Get a "virtual" ID for NACurrent module type.
     *
     * @param   string  $device_id      The device ID.
     * @return  string  A virtual ID for a NACurrent module attached to the device.
     *
     * @since    2.0.0
     */
    public static function get_owm_current_virtual_id($device_id) {
        $result = 'wm'.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Get a "virtual" ID for NAPollution module type.
     *
     * @param   string  $device_id      The device ID.
     * @return  string  A virtual ID for a NAPollution module attached to the device.
     *
     * @since    2.7.0
     */
    public static function get_owm_pollution_virtual_id($device_id) {
        $result = 'po'.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Get a "virtual" ID for NAEphemer module type.
     *
     * @param   string  $device_id      The device ID.
     * @return  string  A virtual ID for a NAEphemer module attached to the device.
     *
     * @since    2.0.0
     */
    public static function get_ephemeris_virtual_id($device_id) {
        $result = 'ep'.substr($device_id, 2, 40);
        return $result;
    }
}