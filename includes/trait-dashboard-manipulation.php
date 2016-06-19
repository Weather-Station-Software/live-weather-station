<?php

/**
 * Dashboard manipulation functionalities for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-query.php');


trait Dashboard_Manipulation {

    use Datas_Query;

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
     * @since    2.0.0
     */
    private function get_dashboard($device_id, $device_name, $module_id, $module_name, $module_type, $types, $datas) {
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
     * @param   integer $battery            Optional. The battery status.
     * @since    1.0.0
     * @access   private
     */
    private function get_netatmo_dashboard($device_id, $device_name, $module_id, $module_name, $module_type, $types, $datas, $place, $signal, $firmware, $battery=0) {
        if ($module_type == 'NAModule2') { // Corrects types for the wind gauge module
            $types = array('WindAngle','WindStrength','GustAngle','GustStrength');
        }
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
            }
        }

        // place datas from device
        if(isset($place) && is_array($place)) {
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
        }

        // Specific datas from module
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
    }

    /**
     * Get station's datas for OpenWeatherMap.
     *
     * @param   array   $datas              Collected datas.
     * @return  array     The data ready to push to OpenWeatherMap.
     * @since    2.3.0
     */
    private function get_owm_datas($datas) {
        $result = array();
        if (is_array($datas) && !empty($datas)) {
            foreach ($datas['devices'] as $device) {
                $sub = array();
                if (time() - $device['dashboard_data']['time_utc'] < $this->time_shift) {
                    if (array_key_exists('Pressure', $device['dashboard_data'])) {
                        $sub['pressure'] = round($device['dashboard_data']['Pressure'], 1);
                    }
                }
                foreach ($device['modules'] as $module) {
                    $dashboard = $module['dashboard_data'];
                    if (time() - $dashboard['time_utc'] > $this->time_shift) {
                        continue;
                    }
                    switch (strtolower($module['type'])) {
                        case 'namodule1': // Outdoor module
                            if (array_key_exists('Temperature', $dashboard)) {
                                $sub['temp'] = round($dashboard['Temperature'], 1);
                            }
                            if (array_key_exists('Humidity', $dashboard)) {
                                $sub['humidity'] = round($dashboard['Humidity'], 1);
                            }
                            break;
                        case 'namodule3': // Rain gauge
                            if (array_key_exists('sum_rain_1', $dashboard)) {
                                $sub['rain_1h'] = round($dashboard['sum_rain_1'], 1);
                            }
                            if (array_key_exists('sum_rain_24', $dashboard)) {
                                $sub['rain_today'] = round($dashboard['sum_rain_24'], 1);
                            }
                            break;
                        case 'namodule2': // Wind gauge
                            if (array_key_exists('WindAngle', $dashboard)) {
                                $sub['wind_dir'] = round($dashboard['WindAngle'], 0);
                            }
                            if (array_key_exists('WindStrength', $dashboard)) {
                                $sub['wind_speed'] = round($dashboard['WindStrength'] / 3.6, 2);
                            }
                            if (array_key_exists('GustStrength', $dashboard)) {
                                $sub['wind_gust'] = round($dashboard['GustStrength'] / 3.6, 2);
                            }
                            break;
                    }
                }
                if (!empty($sub)) {
                    $place = $device['place'];
                    if (isset($place) && is_array($place)) {
                        if (array_key_exists('altitude', $place)) {
                            $sub['alt'] = $place['altitude'];
                        }
                        if (isset($place['location']) && is_array($place['location']) && count($place['location']) > 1) {
                            $sub['lat'] = $place['location'][1];
                        }
                        if (isset($place['location']) && is_array($place['location']) && count($place['location']) > 0) {
                            $sub['long'] = $place['location'][0];
                        }
                    }
                    $result[$device['_id']] = $sub;
                }
            }
        }
        return $result;
    }
}