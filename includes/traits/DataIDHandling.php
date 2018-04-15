<?php

namespace WeatherStation\Data\ID;

/**
 * ID handling for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
trait Handling {

    private static $owm_id ='xx';
    private static $owm_station_id ='xy';
    private static $wug_id ='xz';
    private static $clientraw_id ='yx';
    private static $realtime_id ='yy';
    private static $txt_id ='zx';
    private static $wflw_id ='zy';
    private static $piou_id ='zz';

    private static $owm_current_id ='wm';
    private static $owm_pollution_id ='po';
    private static $computed_id ='00';
    private static $ephemeris_id ='ep';
    private static $fake_modulex_id ='mx';
    private static $fake_modulex_cpt ='cx';

    /**
     * Generate a unique id for a OWM station.
     *
     * @param integer $guid The numeric id of the station
     * @return string The unique id of the station.
     *
     * @since 2.0.0
     */
    public static function get_unique_owm_id($guid) {
        $st = self::$owm_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a OWM true station.
     *
     * @param integer $guid The numeric id of the station
     * @return string The unique id of the station.
     *
     * @since 3.0.0
     */
    public static function get_unique_owm_true_id($guid) {
        $st = self::$owm_station_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a WUG station.
     *
     * @param integer $guid The numeric guid of the station.
     * @return string The unique id of the station.
     * @since 3.0.0
     */
    public static function get_unique_wug_id($guid) {
        $st = self::$wug_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a clientraw station.
     *
     * @param integer $guid The numeric guid of the station.
     * @return string The unique id of the station.
     * @since 3.0.0
     */
    public static function get_unique_clientraw_id($guid) {
        $st = self::$clientraw_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a realtime station.
     *
     * @param integer $guid The numeric guid of the station.
     * @return string The unique id of the station.
     * @since 3.0.0
     */
    public static function get_unique_realtime_id($guid) {
        $st = self::$realtime_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a stickertag station.
     *
     * @param integer $guid The numeric guid of the station.
     * @return string The unique id of the station.
     * @since 3.3.0
     */
    public static function get_unique_txt_id($guid) {
        $st = self::$txt_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a weatherflow station.
     *
     * @param integer $guid The numeric guid of the station.
     * @return string The unique id of the station.
     * @since 3.3.0
     */
    public static function get_unique_wflw_id($guid) {
        $st = self::$wflw_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Generate a unique id for a Pioupiou station.
     *
     * @param integer $guid The numeric guid of the station.
     * @return string The unique id of the station.
     * @since 3.5.0
     */
    public static function get_unique_piou_id($guid) {
        $st = self::$piou_id.str_pad(dechex($guid), 10, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Indicates if the id is the id of an OWM station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an OWM station, false otherwise.
     * @since 2.0.0
     */
    public static function is_owm_station($station_id) {
        return (substr($station_id, 0, 2) == self::$owm_id);
    }

    /**
     * Indicates if the id is the id of an OWM true station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an OWM station, false otherwise.
     * @since 3.0.0
     */
    public static function is_owm_true_station($station_id) {
        return (substr($station_id, 0, 2) == self::$owm_station_id);
    }

    /**
     * Indicates if the id is the id of a WUG station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.0.0
     */
    public static function is_wug_station($station_id) {
        return (substr($station_id, 0, 2) == self::$wug_id);
    }

    /**
     * Indicates if the id is the id of a clientraw station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.0.0
     */
    public static function is_raw_station($station_id) {
        return (substr($station_id, 0, 2) == self::$clientraw_id);
    }

    /**
     * Indicates if the id is the id of a realtime station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.0.0
     */
    public static function is_real_station($station_id) {
        return (substr($station_id, 0, 2) == self::$realtime_id);
    }

    /**
     * Indicates if the id is the id of a stickertag station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.3.0
     */
    public static function is_txt_station($station_id) {
        return (substr($station_id, 0, 2) == self::$txt_id);
    }

    /**
     * Indicates if the id is the id of a weatherflow station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.3.0
     */
    public static function is_wflw_station($station_id) {
        return (substr($station_id, 0, 2) == self::$wflw_id);
    }

    /**
     * Indicates if the id is the id of a Pioupiou station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.3.0
     */
    public static function is_piou_station($station_id) {
        return (substr($station_id, 0, 2) == self::$piou_id);
    }

    /**
     * Indicates if the id is the id of an Netatmo station.
     *
     * @param integer $station_id The numeric id of the station.
     * @return boolean True if it's an Netatmo station, false otherwise.
     * @since 3.0.0
     */
    public static function is_netatmo_station($station_id) {
        return (!self::is_owm_station($station_id) && !self::is_wug_station($station_id) &&
                !self::is_raw_station($station_id) && !self::is_real_station($station_id) &&
                !self::is_txt_station($station_id) && !self::is_wflw_station($station_id) &&
                !self::is_piou_station($station_id));
    }

    /**
     * Indicates if the id is the id of an OWM current weather module.
     *
     * @param integer $module_id The numeric id of the module.
     * @return boolean True if it's an OWM station. False otherwise.
     * @since 2.0.0
     */
    public static function is_owm_current_module($module_id) {
        return (substr($module_id, 0, 2) == self::$owm_current_id);
    }

    /**
     * Indicates if the id is the id of an OWM pollution module.
     *
     * @param integer $module_id The numeric id of the module.
     * @return boolean True if it's an OWM station. False otherwise.
     *
     * @since 2.0.0
     */
    public static function is_owm_pollution_module($module_id) {
        return (substr($module_id, 0, 2) == self::$owm_pollution_id);
    }


    /**
     * Get a "virtual" ID for NAComputed module type.
     *
     * @param string $device_id The device ID.
     * @return string A virtual ID for a NAComputed module attached to the device.
     *
     * @since 2.0.0
     */
    public static function get_computed_virtual_id($device_id) {
        $result = self::$computed_id.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Get a "virtual" ID for NACurrent module type.
     *
     * @param string $device_id The device ID.
     * @return string A virtual ID for a NACurrent module attached to the device.
     *
     * @since 2.0.0
     */
    public static function get_owm_current_virtual_id($device_id) {
        $result = self::$owm_current_id.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Get a "virtual" ID for NAPollution module type.
     *
     * @param string $device_id The device ID.
     * @return string A virtual ID for a NAPollution module attached to the device.
     *
     * @since 2.7.0
     */
    public static function get_owm_pollution_virtual_id($device_id) {
        $result = self::$owm_pollution_id.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Get a "virtual" ID for NAEphemer module type.
     *
     * @param string $device_id The device ID.
     * @return string A virtual ID for a NAEphemer module attached to the device.
     *
     * @since 2.0.0
     */
    public static function get_ephemeris_virtual_id($device_id) {
        $result = self::$ephemeris_id.substr($device_id, 2, 40);
        return $result;
    }

    /**
     * Generate a unique id for a fake module, for a given station.
     *
     * @param integer $guid The numeric guid of the station.
     * @param integer $id The X number in NAModuleX type.
     * @param integer $cpt Optional. The #number of module.
     * @return string The unique id of the module.
     * @since 3.0.0
     */
    public static function get_fake_modulex_id($guid, $id, $cpt=0) {
        $st = str_replace('x', $id, self::$fake_modulex_id).str_replace('x', $cpt, self::$fake_modulex_cpt).str_pad(dechex($guid), 8, '0', STR_PAD_LEFT);
        $result = $st[0].$st[1].':'.$st[2].$st[3].':'.$st[4].$st[5].':'.$st[6].$st[7].':'.$st[8].$st[9].':'.$st[10].$st[11];
        return strtolower($result);
    }

    /**
     * Indicates if the id is the id of fake module.
     *
     * @param string $device_id The device ID.
     * @param integer $id The X number in NAModuleX type.
     * @return boolean True if it's an WUG station, false otherwise.
     * @since 3.0.0
     */
    public static function is_fake_modulex_id($device_id, $id) {
        return (substr($device_id, 0, 2) == str_replace('x', $id, self::$fake_modulex_id));
    }
}