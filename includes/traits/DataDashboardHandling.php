<?php

namespace WeatherStation\Data\Dashboard;

use \WeatherStation\DB\Query;
use \WeatherStation\SDK\Generic\Plugin\Common\Utilities;

/**
 * Dashboard handling for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
trait Handling {

    use Query, Utilities;

    private $time_shift = 500;

    /**
     * Analyzes dashboard datas for simple collector/computer and store it.
     *
     * @param   string  $device_id          The device id to update.
     * @param   string  $device_name        The device name to update.
     * @param   string  $module_id          The module id to update.
     * @param   string  $module_name        The module name to update.
     * @param   string  $module_type        The type of the module (NAMain, NAModule1..4).
     * @param   array   $types              The data types available in the $datas array.
     * @param   array   $datas              The dashboard datas.
     * @param   array   $place              Optional. The place datas.
     * @since    2.0.0
     */
    private function get_dashboard($device_id, $device_name, $module_id, $module_name, $module_type, $types, $datas, $place=null) {
        foreach($types as $type) {
            if (array_key_exists($type, $datas)) {
                $updates = array();
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $device_name;
                $updates['module_id'] = $module_id;
                $updates['module_type'] = $module_type;
                $updates['module_name'] = $module_name;
                if (array_key_exists('TS_'.$type, $datas)) {
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['TS_'.$type]);
                }
                elseif (array_key_exists('time_utc', $datas)){
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
                }
                else {
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                }
                $updates['measure_type'] = strtolower($type);
                $updates['measure_value'] = $datas[$type];
                $this->update_data_table($updates);
            }
        }
        $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        $updates['measure_type'] = 'last_refresh';
        $updates['measure_value'] = date('Y-m-d H:i:s');
        $this->update_data_table($updates);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        if (array_key_exists('time_utc', $datas)){
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
        }
        else {
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        }
        $updates['measure_type'] = 'signal';
        $updates['measure_value'] = 0 ;
        $this->update_data_table($updates);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        if (array_key_exists('time_utc', $datas)){
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
        }
        else {
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        }
        $updates['measure_type'] = 'battery';
        $updates['measure_value'] = 6000 ;
        $this->update_data_table($updates);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        if (array_key_exists('time_utc', $datas)){
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
        }
        else {
            $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        }
        $updates['measure_type'] = 'firmware';
        $updates['measure_value'] = LWS_VERSION ;
        $this->update_data_table($updates);
        // place datas from device
        if(isset($place) && is_array($place)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $datas)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_country';
            $updates['measure_value'] = '';
            if (array_key_exists('country', $place)) {
                $updates['measure_value'] = $place['country'];
            }
            $this->update_data_table($updates);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $datas)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_city';
            $updates['measure_value'] = '';
            if (array_key_exists('city', $place)) {
                $updates['measure_value'] = $place['city'];
            }
            $this->update_data_table($updates);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $datas)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_altitude';
            $updates['measure_value'] = 0;
            if (array_key_exists('altitude', $place)) {
                $updates['measure_value'] = $place['altitude'];
            }
            $this->update_data_table($updates);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $datas)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_latitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>1) {
                $updates['measure_value'] = $place['location'][1];
                $this->update_data_table($updates);
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $datas)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_longitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>0) {
                $updates['measure_value'] = $place['location'][0];
                $this->update_data_table($updates);
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            if (array_key_exists('time_utc', $datas)){
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            }
            else {
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
            }
            $updates['measure_type'] = 'loc_timezone';
            $updates['measure_value'] = 'UTC';
            if (array_key_exists('timezone', $place)) {
                $updates['measure_value'] = str_replace('\\', '', $place['timezone']);
            }
            $this->update_data_table($updates);
        }
    }

    /**
     * Analyzes Netatmo dashboard datas and store it.
     *
     * @param   string  $device_id          The device id to update.
     * @param   string  $device_name        The device name to update.
     * @param   string  $module_id          The module id to update.
     * @param   string  $module_name        The module name to update.
     * @param   string  $module_type        The type of the module (NAMain, NAModule1..4).
     * @param   array   $types              The data types available in the $datas array.
     * @param   array   $datas              The dashboard datas.
     * @param   array   $place              The place datas.
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
    private function get_netatmo_dashboard($device_id, $device_name, $module_id, $module_name, $module_type, $types, $datas, $place, $signal, $firmware, $lastseen, $battery=0, $firstsetup=null, $lastsetup=null, $lastupgrade=null, $is_hc=false) {
        if ($module_type == 'NAModule2') { // Corrects types for the wind gauge module
            $types = array('WindAngle','WindStrength','GustAngle','GustStrength');
        }
        if ($module_type == 'NAMain') {
            $station = $this->get_station_informations_by_station_id($device_id);
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
        foreach($types as $type) {
            if (array_key_exists($type, $datas)) {
                $updates = array();
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $device_name;
                $updates['module_id'] = $module_id;
                $updates['module_type'] = $module_type;
                $updates['module_name'] = $module_name;
                $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
                $updates['measure_type'] = strtolower($type);
                $updates['measure_value'] = $datas[$type];
                if ($type == 'wind_chill' || $type == 'wind_ref') {
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['wind_time_utc']);
                }
                $this->update_data_table($updates);
                if (strtolower($type) == 'temperature') {
                    $hi_tmp = $datas[$type];
                }
                if (strtolower($type) == 'humidity') {
                    $hi_hmd = $datas[$type];
                }
                if (strtolower($type) == 'co2') {
                    $hi_co2 = $datas[$type];
                }
                if (strtolower($type) == 'noise') {
                    $hi_nse = $datas[$type];
                }
            }
        }
        $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        $updates['measure_type'] = 'last_refresh';
        $updates['measure_value'] = date('Y-m-d H:i:s');
        $this->update_data_table($updates);

        // place datas from device
        if(isset($place) && is_array($place)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'loc_country';
            $updates['measure_value'] = '';
            if (array_key_exists('country', $place)) {
                $updates['measure_value'] = $place['country'];
            }
            $this->update_data_table($updates);
            if ($is_station) {
                $station['loc_country_code'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'loc_city';
            $updates['measure_value'] = '';
            if (array_key_exists('city', $place)) {
                $updates['measure_value'] = $place['city'];
            }
            $this->update_data_table($updates);
            if ($is_station) {
                $station['loc_city'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'loc_altitude';
            $updates['measure_value'] = 0;
            if (array_key_exists('altitude', $place)) {
                $updates['measure_value'] = $place['altitude'];
            }
            $this->update_data_table($updates);
            if ($is_station) {
                $station['loc_altitude'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'loc_latitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>1) {
                $updates['measure_value'] = $place['location'][1];
            }
            $this->update_data_table($updates);
            if ($is_station) {
                $station['loc_latitude'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'loc_longitude';
            $updates['measure_value'] = 0;
            if (isset($place['location']) && is_array($place['location']) && count($place['location'])>0) {
                $updates['measure_value'] = $place['location'][0];
            }
            $this->update_data_table($updates);
            if ($is_station) {
                $station['loc_longitude'] = $updates['measure_value'];
            }
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'loc_timezone';
            $updates['measure_value'] = 'UTC';
            if (array_key_exists('timezone', $place)) {
                $updates['measure_value'] = str_replace('\\', '', $place['timezone']);
            }
            $this->update_data_table($updates);
            if ($is_station) {
                $station['loc_timezone'] = $updates['measure_value'];
            }
        }

        // Specific datas from module
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s');
        $updates['measure_type'] = 'last_seen';
        $updates['measure_value'] = date('Y-m-d H:i:s', $lastseen);
        $this->update_data_table($updates);
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
            $this->update_data_table($updates);
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
            $this->update_data_table($updates);
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
            $this->update_data_table($updates);
        }
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
        $updates['measure_type'] = 'signal';
        $updates['measure_value'] =$signal ;
        $this->update_data_table($updates);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
        $updates['measure_type'] = 'battery';
        $updates['measure_value'] =$battery ;
        $this->update_data_table($updates);
        $updates = array();
        $updates['device_id'] = $device_id;
        $updates['device_name'] = $device_name;
        $updates['module_id'] = $module_id;
        $updates['module_type'] = $module_type;
        $updates['module_name'] = $module_name;
        $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
        $updates['measure_type'] = 'firmware';
        $updates['measure_value'] = $firmware ;
        $this->update_data_table($updates);

        // Additional datas about temperature
        if (array_key_exists('date_max_temp', $datas) &&
            array_key_exists('date_min_temp', $datas) &&
            array_key_exists('min_temp', $datas) &&
            array_key_exists('max_temp', $datas)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['date_min_temp']);
            $updates['measure_type'] = 'temperature_min';
            $updates['measure_value'] = $datas['min_temp'] ;
            $this->update_data_table($updates);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['date_max_temp']);
            $updates['measure_type'] = 'temperature_max';
            $updates['measure_value'] = $datas['max_temp'] ;
            $this->update_data_table($updates);
        }

        // Additional datas about temperature trend
        if (array_key_exists('temp_trend', $datas)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'temperature_trend';
            $updates['measure_value'] = $datas['temp_trend'] ;
            $this->update_data_table($updates);
        }

        // Additional datas about pressure trend
        if (array_key_exists('pressure_trend', $datas)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'pressure_trend';
            $updates['measure_value'] = $datas['pressure_trend'] ;
            $this->update_data_table($updates);
        }

        // Additional datas about rain
        if (array_key_exists('sum_rain_1', $datas)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'rain_hour_aggregated';
            $updates['measure_value'] =$datas['sum_rain_1'] ;
            $this->update_data_table($updates);
        }
        // Additional datas about rain
        if (array_key_exists('sum_rain_24', $datas)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $updates['measure_type'] = 'rain_day_aggregated';
            $updates['measure_value'] =$datas['sum_rain_24'] ;
            $this->update_data_table($updates);
        }
        // Additional datas about wind
        if (array_key_exists('WindHistoric', $datas) &&
            is_array($datas['WindHistoric'])) {
            $wsmax=0;
            $wamax=0;
            $wdmax = time();
            foreach($datas['WindHistoric'] as $wind) {
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
            $updates['measure_value'] =$wamax ;
            $this->update_data_table($updates);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $wdmax);
            $updates['measure_type'] = 'windstrength_hour_max';
            $updates['measure_value'] = $wsmax ;
            $this->update_data_table($updates);

        }

        // Additional datas about wind
        if (array_key_exists('date_max_wind_str', $datas) &&
            array_key_exists('max_wind_angle', $datas) &&
            array_key_exists('max_wind_str', $datas)) {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['date_max_wind_str']);
            $updates['measure_type'] = 'windangle_day_max';
            $updates['measure_value'] =$datas['max_wind_angle'] ;
            $this->update_data_table($updates);
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['date_max_wind_str']);
            $updates['measure_type'] = 'windstrength_day_max';
            $updates['measure_value'] =$datas['max_wind_str'] ;
            $this->update_data_table($updates);
        }

        // Health index computing
        if ($module_type == 'NAMain' || $module_type == 'NAModule4') {
            $updates = array();
            $updates['device_id'] = $device_id;
            $updates['device_name'] = $device_name;
            $updates['module_id'] = $module_id;
            $updates['module_type'] = $module_type;
            $updates['module_name'] = $module_name;
            $updates['measure_timestamp'] = date('Y-m-d H:i:s', $datas['time_utc']);
            $health = $this->compute_health_index($hi_tmp, $hi_hmd, $hi_co2, $hi_nse);
            if ($is_hc && !get_option('live_weather_station_overload_hc')) {
                $updates['measure_type'] = 'health_idx';
                $updates['measure_value'] = 90 - (20*$datas['health_idx']) ;
                $this->update_data_table($updates);
            }
            else {
                foreach ($health as $key => $idx) {
                    $updates['measure_type'] = $key;
                    $updates['measure_value'] = $idx;
                    $this->update_data_table($updates);
                }
            }
        }
        if ($is_station) {
            $station['last_refresh'] = date('Y-m-d H:i:s');
            $station['last_seen'] = date('Y-m-d H:i:s', $lastseen);
            $this->update_stations_table($station, true);
        }
    }
}