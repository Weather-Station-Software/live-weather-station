<?php

namespace WeatherStation\Data\Dashboard;

use \WeatherStation\DB\Query;
use \WeatherStation\SDK\Generic\Plugin\Common\Utilities;

/**
 * Dashboard handling for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
trait Handling {

    use Query, Utilities;

    private $time_shift = 500;
    private $pressure_ref = null;

    /**
     * Analyzes dashboard measurements for simple collector/computer and store it.
     *
     * @param integer $station_type The station type.
     * @param string $device_id The device id to update.
     * @param string $device_name The device name to update.
     * @param string $module_id The module id to update.
     * @param string $module_name The module name to update.
     * @param string $module_type The type of the module (NAMain, NAModule1..9, v..p).
     * @param array $types The data types available in the $measurements array.
     * @param array $measurements The dashboard measurements.
     * @param array $place Optional. The place measurements.
     * @param boolean $last_seen Optional. Must add the last_seen value.
     * @since 2.0.0
     */
    private function get_dashboard($station_type, $device_id, $device_name, $module_id, $module_name, $module_type, $types, $measurements, $place=null, $last_seen=false) {
        $pressure_ref = null;
        $temperature_ref = null;
        $humidity_ref = null;
        $timezone = $this->get_timezone(null, $place, null, $device_id);
        foreach($types as $type) {
            if (isset($measurements) && is_array($measurements) && array_key_exists($type, $measurements)) {
                $updates = array();
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $device_name;
                $updates['module_id'] = $module_id;
                $updates['module_type'] = $module_type;
                $updates['module_name'] = $module_name;
                if (array_key_exists('TS_'.$type, $measurements)) {
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['TS_'.$type]);
                }
                elseif (array_key_exists('time_utc', $measurements)){
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
                }
                else {
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                }
                $updates['measure_type'] = strtolower($type);
                $updates['measure_value'] = $measurements[$type];
                $this->update_data_table($updates, $timezone);
                if ($type === 'temperature') {
                    $temperature_ref = $measurements[$type];
                }
                if ($type === 'humidity') {
                    $humidity_ref = $measurements[$type];
                }
            }
        }
        if (isset($measurements) && is_array($measurements) && array_key_exists('pressure', $measurements)) {
            $pressure_ref = $measurements['pressure'];
        }
        if (isset($temperature_ref) && isset($pressure_ref) && isset($humidity_ref)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'absolute_humidity';
            $updates['measure_value'] = $this->compute_partial_absolute_humidity($temperature_ref, 100 * $pressure_ref, $humidity_ref);
            $this->update_data_table($updates, $timezone);
        }
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        $updates['measure_type'] = 'last_refresh';
        $updates['measure_value'] = date('Y-m-d H:i:s');
        $this->update_data_table($updates, $timezone);
        if ($last_seen) {
            if (array_key_exists('TS_'.$type, $measurements)) {
                $updates['measure_value'] = date('Y-m-d H:i:s', $measurements['TS_'.$type]);
            }
            elseif (array_key_exists('time_utc', $measurements)){
                $updates['measure_value'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_value'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'last_seen';
            $this->update_data_table($updates, $timezone);
        }

        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        if (array_key_exists('time_utc', $measurements)){
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
        }
        else {
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        }
        $updates['measure_type'] = 'signal';
        if (array_key_exists('signal', $measurements)){
            $updates['measure_value'] = $measurements['signal'] ;
        }
        else {
            $updates['measure_value'] = 9999;
        }
        $this->update_data_table($updates, $timezone);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        if (array_key_exists('time_utc', $measurements)){
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
        }
        else {
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        }
        $updates['measure_type'] = 'battery';
        if (array_key_exists('battery', $measurements)){
            $updates['measure_value'] = $measurements['battery'] ;
        }
        else {
            $updates['measure_value'] = 6000 ;
        }
        $this->update_data_table($updates, $timezone);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        if (array_key_exists('time_utc', $measurements)){
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
        }
        else {
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        }
        $updates['measure_type'] = 'firmware';
        $updates['measure_value'] = LWS_VERSION ;
        $this->update_data_table($updates, $timezone);
        // place measurements from device
        if(isset($place) && is_array($place)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $measurements)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_country';
            $updates['measure_value'] = '';
            if (array_key_exists('country', $place)) {
                $updates['measure_value'] = $place['country'];
            }
            $this->update_data_table($updates, $timezone);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $measurements)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_city';
            $updates['measure_value'] = '';
            if (array_key_exists('city', $place)) {
                $updates['measure_value'] = $place['city'];
            }
            $this->update_data_table($updates, $timezone);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $measurements)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_altitude';
            $updates['measure_value'] = 0;
            if (array_key_exists('altitude', $place)) {
                $updates['measure_value'] = $place['altitude'];
            }
            $this->update_data_table($updates, $timezone);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $measurements)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_latitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>1) {
                $updates['measure_value'] = $place['location'][1];
                $this->update_data_table($updates, $timezone);
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $measurements)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_longitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>0) {
                $updates['measure_value'] = $place['location'][0];
                $this->update_data_table($updates, $timezone);
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $measurements)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_timezone';
            $updates['measure_value'] = 'UTC';
            if (array_key_exists('timezone', $place)) {
                $updates['measure_value'] = str_replace('\\', '', $place['timezone']);
            }
            $this->update_data_table($updates, $timezone);
        }
        if ($module_type === 'NAModuleP') {
            if (array_key_exists('time_pct', $measurements) && array_key_exists('url_pct', $measurements)){
                $updates = array();
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $device_name;
                $updates['module_id'] = $module_id;
                $updates['module_type'] = $module_type;
                $updates['module_name'] = $module_name;
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_pct']);
                $updates['measure_type'] = 'picture';
                if ($station_type === LWS_BSKY_SID) {
                    $updates['measure_value'] = substr(__('View from station', 'live-weather-station'), 0, 50);
                    $this->update_data_table($updates, $timezone);
                    $media = array();
                    $media['timestamp'] = date('Y-m-d H:i:s', $measurements['time_pct']);
                    $media['device_id'] = $device_id;
                    $media['module_id'] = $module_id;
                    $media['module_type'] = $module_type;
                    $media['item_type'] = 'none';
                    $media['item_url'] = str_replace('http://', 'https://', $measurements['url_pct']);
                    self::insert_update_table(self::live_weather_station_media_table(), $media);
                }
            }
        }
        if ($module_type === 'NAModuleV') {
            foreach (array('imperial', 'metric') as $item_type) {
                if (array_key_exists('video_' . $item_type, $measurements) && isset($measurements['video_' . $item_type])){
                    $updates = array();
                    $updates['device_id'] = $device_id;
                    $updates['device_name'] = $device_name;
                    $updates['module_id'] = $module_id;
                    $updates['module_type'] = $module_type;
                    $updates['module_name'] = $module_name;
                    $updates['measure_type'] = 'video_' . $item_type;
                    if ($station_type === LWS_BSKY_SID) {
                        if (count($measurements['video_' . $item_type]) > 0) {
                            $video = end($measurements['video_' . $item_type]);
                            $timestamp = str_replace('_C', '', str_replace('.mp4', '', substr($video, ($item_type === 'imperial' ? -14 : -16)))) . ' 12:00:00';
                            $mode = ($item_type === 'imperial' ? __('Daily timelapse with imperial subtitles', 'live-weather-station') : __('Daily timelapse with metric subtitles', 'live-weather-station'));
                            $updates['measure_timestamp'] = $timestamp;
                            $updates['measure_value'] = substr($mode, 0, 50);
                            $this->update_data_table($updates, $timezone);
                            $media = array();
                            $media['timestamp'] = $timestamp;
                            $media['device_id'] = $device_id;
                            $media['module_id'] = $module_id;
                            $media['module_type'] = $module_type;
                            $media['item_type'] = $item_type;
                            $media['item_url'] = str_replace('http://', 'https://', $video);
                            self::insert_update_table(self::live_weather_station_media_table(), $media);
                        }
                    }
                }
            }
        }
    }

    /**
     * Analyzes Netatmo dashboard measurements and store it.
     *
     * @param   string  $device_id          The device id to update.
     * @param   string  $device_name        The device name to update.
     * @param   string  $module_id          The module id to update.
     * @param   string  $module_name        The module name to update.
     * @param   string  $module_type        The type of the module (NAMain, NAModule1..4).
     * @param   array   $types              The data types available in the $measurements array.
     * @param   array   $measurements              The dashboard measurements.
     * @param   array   $place              The place measurements.
     * @param   integer $signal             The radio or wifi signal quality.
     * @param   integer $firmware           The firmware version.
     * @param   integer $lastseen           The last seen timestamp.
     * @param   integer $battery            Optional. The battery status.
     * @param   integer $firstsetup         Optional. The first setup timestamp.
     * @param   integer $lastsetup          Optional. The last setup timestamp.
     * @param   integer $lastupgrade        Optional. The last upgrade timestamp.
     * @param   boolean $is_hc              Optional. True if it's a healthy home coach.
     * @since   1.0.0
     */
    private function get_netatmo_dashboard($device_id, $device_name, $module_id, $module_name, $module_type, $types, $measurements, $place, $signal, $firmware, $lastseen, $battery=0, $firstsetup=null, $lastsetup=null, $lastupgrade=null, $is_hc=false) {
        if ($module_type == 'NAModule2') { // Corrects types for the wind gauge module
            $types = array('WindAngle','WindStrength','GustAngle','GustStrength');
        }
        if ($module_type == 'NAMain') {
            $types[] = 'AbsolutePressure';
            $this->pressure_ref = null;
            $station = $this->get_station_information_by_station_id($device_id);
            if (count($station) > 0) {
                if ($station['station_name'] == '') {
                    $station['station_name'] = $device_name;
                }
            }
            else {
                $station['station_name'] = $device_name;
                $station['station_id'] = $device_id;
            }
            if ($is_hc) {
                $station['station_model'] = 'Netatmo - Healthy Home Coach';
                $station['station_type'] = LWS_NETATMOHC_SID;
            }
            else {
                $station['station_model'] = 'Netatmo - Personal Weather Station';
                $station['station_type'] = LWS_NETATMO_SID;
            }
            $is_station = true;
        }
        else {
            $station = array();
            $is_station = false;
        }
        $hi_tmp = null;
        $hi_hmd = null;
        $hi_co2 = null;
        $hi_nse = null;
        $timezone = $this->get_timezone(null, $place, null, $device_id);
        foreach($types as $type) {
            if (isset($measurements) && is_array($measurements) && array_key_exists($type, $measurements)) {
                $updates = array();
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $device_name;
                $updates['module_id'] = $module_id;
                $updates['module_type'] = $module_type;
                $updates['module_name'] = $module_name;
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
                $updates['measure_type'] = strtolower($type);
                $updates['measure_value'] = $measurements[$type];
                if ($type === 'WindAngle') {
                    $wind = $measurements[$type];
                    if ($wind < 0) {
                        $wind = 0;
                    }
                    $updates['measure_value'] = $wind;
                }
                if ($type === 'GustAngle') {
                    $wind = $measurements[$type];
                    if ($wind < 0) {
                        $wind = 0;
                    }
                    $updates['measure_value'] = $wind;
                }
                if ($type === 'wind_chill' || $type === 'wind_ref') {
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['wind_time_utc']);
                }
                if ($type === 'Pressure') {
                    $updates['measure_type'] = 'pressure_sl';
                }
                if ($type === 'AbsolutePressure') {
                    $updates['measure_type'] = 'pressure';
                    $this->pressure_ref = $updates['measure_value'];
                }
                $this->update_data_table($updates, $timezone);
                if ($type == 'WindAngle') {
                    $updates['measure_type'] = 'winddirection';
                    $updates['measure_value'] = (int)floor(($wind + 180) % 360);
                    $this->update_data_table($updates, $timezone);
                }
                if ($type == 'GustAngle') {
                    $updates['measure_type'] = 'gustdirection';
                    $updates['measure_value'] = (int)floor(($wind + 180) % 360);
                    $this->update_data_table($updates, $timezone);
                }
                if (strtolower($type) == 'temperature') {
                    $hi_tmp = $measurements[$type];
                }
                if (strtolower($type) == 'humidity') {
                    $hi_hmd = $measurements[$type];
                }
                if (strtolower($type) == 'co2') {
                    $hi_co2 = $measurements[$type];
                }
                if (strtolower($type) == 'noise') {
                    $hi_nse = $measurements[$type];
                }
            }
        }
        $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        $updates['measure_type'] = 'last_refresh';
        $updates['measure_value'] = date('Y-m-d H:i:s');
        $this->update_data_table($updates, $timezone);

        // place measurements from device
        if(isset($place) && is_array($place)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'loc_country';
            $updates['measure_value'] = '';
            if (array_key_exists('country', $place)) {
                $updates['measure_value'] = $place['country'];
            }
            $this->update_data_table($updates, $timezone);
            if ($is_station) {
                $station['loc_country_code'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'loc_city';
            $updates['measure_value'] = '';
            if (array_key_exists('city', $place)) {
                $updates['measure_value'] = $place['city'];
            }
            $this->update_data_table($updates, $timezone);
            if ($is_station) {
                $station['loc_city'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'loc_altitude';
            $updates['measure_value'] = 0;
            if (array_key_exists('altitude', $place)) {
                $updates['measure_value'] = $place['altitude'];
            }
            $this->update_data_table($updates, $timezone);
            if ($is_station) {
                $station['loc_altitude'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'loc_latitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>1) {
                $updates['measure_value'] = $place['location'][1];
            }
            $this->update_data_table($updates, $timezone);
            if ($is_station) {
                $station['loc_latitude'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'loc_longitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>0) {
                $updates['measure_value'] = $place['location'][0];
            }
            $this->update_data_table($updates, $timezone);
            if ($is_station) {
                $station['loc_longitude'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'loc_timezone';
            $updates['measure_value'] = 'UTC';
            if (array_key_exists('timezone', $place)) {
                $updates['measure_value'] = str_replace('\\', '', $place['timezone']);
            }
            $this->update_data_table($updates, $timezone);
            if ($is_station) {
                $station['loc_timezone'] = $updates['measure_value'];
            }
        }

        // Specific measurements from module
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        $updates['measure_type'] = 'last_seen';
        $updates['measure_value'] = date('Y-m-d H:i:s', $lastseen);
        $this->update_data_table($updates, $timezone);
        if (isset($firstsetup)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'first_setup';
            $updates['measure_value'] = date('Y-m-d H:i:s', $firstsetup);
            $this->update_data_table($updates, $timezone);
        }
        if (isset($lastsetup)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'last_setup';
            $updates['measure_value'] = date('Y-m-d H:i:s', $lastsetup);
            $this->update_data_table($updates, $timezone);
        }
        if (isset($lastupgrade)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            $updates['measure_type'] = 'last_upgrade';
            $updates['measure_value'] = date('Y-m-d H:i:s', $lastupgrade);
            $this->update_data_table($updates, $timezone);
        }
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
        $updates['measure_type'] = 'signal';
        $updates['measure_value'] =$signal ;
        $this->update_data_table($updates, $timezone);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
        $updates['measure_type'] = 'battery';
        $updates['measure_value'] =$battery ;
        $this->update_data_table($updates, $timezone);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
        $updates['measure_type'] = 'firmware';
        $updates['measure_value'] = $firmware ;
        $this->update_data_table($updates, $timezone);

        // Additional measurements about temperature
        // TODO: remove after validation
        /*if (array_key_exists('date_max_temp', $measurements) &&
            array_key_exists('date_min_temp', $measurements) &&
            array_key_exists('min_temp', $measurements) &&
            array_key_exists('max_temp', $measurements)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['date_min_temp']);
            $updates['measure_type'] = 'temperature_min';
            $updates['measure_value'] = $measurements['min_temp'] ;
            $this->update_data_table($updates, $timezone);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['date_max_temp']);
            $updates['measure_type'] = 'temperature_max';
            $updates['measure_value'] = $measurements['max_temp'] ;
            $this->update_data_table($updates, $timezone);
        }*/

        // Additional measurements about temperature trend
        if (array_key_exists('temp_trend', $measurements)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'temperature_trend';
            $updates['measure_value'] = $measurements['temp_trend'] ;
            $this->update_data_table($updates, $timezone);
        }

        // Additional measurements about pressure trend
        if (array_key_exists('pressure_trend', $measurements)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'pressure_trend';
            $updates['measure_value'] = $measurements['pressure_trend'] ;
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'pressure_sl_trend';
            $this->update_data_table($updates, $timezone);
        }

        // Additional measurements about rain
        if (array_key_exists('sum_rain_1', $measurements)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'rain_hour_aggregated';
            $updates['measure_value'] =$measurements['sum_rain_1'] ;
            $this->update_data_table($updates, $timezone);
        }
        // Additional measurements about rain
        if (array_key_exists('sum_rain_24', $measurements)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $updates['measure_type'] = 'rain_day_aggregated';
            $updates['measure_value'] =$measurements['sum_rain_24'] ;
            $this->update_data_table($updates, $timezone);
        }
        // Additional measurements about wind
        if (array_key_exists('WindHistoric', $measurements) && is_array($measurements['WindHistoric'])) {
            $wsmax=0;
            $wamax=0;
            $wdmax = time();
            foreach($measurements['WindHistoric'] as $wind) {
                if ($wind['WindStrength'] > $wsmax) {
                    $wsmax = $wind['WindStrength'];
                    $wamax = $wind['WindAngle'];
                    $wdmax = $wind['time_utc'];
                }
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $wdmax);
            $updates['measure_type'] = 'windangle_hour_max';
            $updates['measure_value'] = $wamax ;
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'winddirection_hour_max';
            $updates['measure_value'] = (int)floor(($wamax + 180) % 360); ;
            $this->update_data_table($updates, $timezone);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $wdmax);
            $updates['measure_type'] = 'windstrength_hour_max';
            $updates['measure_value'] = $wsmax ;
            $this->update_data_table($updates, $timezone);

        }

        // Additional measurements about wind
        if (array_key_exists('date_max_wind_str', $measurements) &&
            array_key_exists('max_wind_angle', $measurements) &&
            array_key_exists('max_wind_str', $measurements)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['date_max_wind_str']);
            $updates['measure_type'] = 'windangle_day_max';
            $updates['measure_value'] = $measurements['max_wind_angle'] ;
            $this->update_data_table($updates, $timezone);
            $updates['measure_type'] = 'winddirection_day_max';
            $updates['measure_value'] = (int)floor(($measurements['max_wind_angle'] + 180) % 360);
            $this->update_data_table($updates, $timezone);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['date_max_wind_str']);
            $updates['measure_type'] = 'windstrength_day_max';
            $updates['measure_value'] =$measurements['max_wind_str'] ;
            $this->update_data_table($updates, $timezone);
        }

        // Health index computing
        if ($module_type == 'NAMain' || $module_type == 'NAModule4') {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            $health = $this->compute_health_index($hi_tmp, $hi_hmd, $hi_co2, $hi_nse);
            if ($is_hc && !get_option('live_weather_station_overload_hc')) {
                $updates['measure_type'] = 'health_idx';
                $updates['measure_value'] = 90 - (20*$measurements['health_idx']) ;
                $this->update_data_table($updates, $timezone);
            }
            else {
                foreach ($health as $key => $idx) {
                    $updates['measure_type'] = $key;
                    $updates['measure_value'] = $idx;
                    $this->update_data_table($updates, $timezone);
                }
            }
        }
        // Absolute humidity computing
        if ($module_type == 'NAMain' || $module_type == 'NAModule1' || $module_type == 'NAModule4') {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $measurements['time_utc']);
            if (isset($hi_tmp) && isset($this->pressure_ref) && isset($hi_hmd)) {
                $updates['measure_type'] = 'absolute_humidity';
                $updates['measure_value'] = $this->compute_partial_absolute_humidity($hi_tmp, 100 * $this->pressure_ref, $hi_hmd);
                $this->update_data_table($updates, $timezone);
            }
        }
        if ($is_station) {
            $station['last_refresh'] = date('Y-m-d H:i:s');
            $station['last_seen'] = date('Y-m-d H:i:s', $lastseen);
            $this->update_stations_table($station, true);
        }
    }
}