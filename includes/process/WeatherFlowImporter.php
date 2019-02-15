<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Unit\Conversion;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;
use WeatherStation\SDK\WeatherFlow\Plugin\PublicClient;
use WeatherStation\Data\ID\Handling as Id_Manipulation;
use WeatherStation\Data\History\Builder;

/**
 * A process to import old data from a Netatmo station.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class WeatherFlowImporter extends Process {

    use Id_Manipulation, PublicClient, Conversion, DateTimeConversion;


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.7.0
     */
    protected function uuid() {
        return $this->generate_v4_uuid();
    }

    /**
     * Get the execution mode of the process.
     * Can be :
     *   - pause: can be restarted in the same cycle
     *   - schedule: must wait next cycle to be restarted
     *
     * @return string The execution mode of the process.
     * @since 3.7.0
     */
    protected function execution_mode() {
        return $this->state_schedule;
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.7.0
     */
    protected function priority(){
        return 20;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.7.0
     */
    protected function is_needed() {
        if (!(bool)get_option('live_weather_station_collect_history')) {
            return false;
        }
        if (!(bool)get_option('live_weather_station_build_history')) {
            return false;
        }
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.7.0
     */
    protected function is_terminated(){
        return (count($this->params['todo_ext'] + $this->params['todo_int']) === 0);
    }

    /**
     * Verify if process is in error.
     *
     * @return boolean True if the process is in error. False otherwise.
     * @since 3.7.0
     */
    protected function is_in_error(){
        return false;
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('WeatherFlow importer', 'live-weather-station');
        }
        else {
            return 'WeatherFlow importer';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return __('Importing old data from a WeatherFlow station.', 'live-weather-station');
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.7.0
     */
    protected function message() {
        $result = sprintf(__('Here are the details of importing old data from the station "%s":', 'live-weather-station'), $this->params['init']['station_name']) . "\r\n";
        foreach ($this->params['summary'] as $module) {
            if ($module['measurements'] === 0 || $module['days_done'] === 0) {
                $result .= '     - ' . sprintf(__('"%s": no measurements.', 'live-weather-station'), $module['name']) . "\r\n";
            }
            else {
                $result .= '     - ' . sprintf(__('"%s": %s measurements spread over %s days.', 'live-weather-station'), $module['name'], $module['measurements'], $module['days_done']) . "\r\n";
            }
        }
        $result .= "\r\n" . sprintf(__('These measurements were compiled in %s.', 'live-weather-station'), $this->get_age_hours_from_seconds($this->exectime)) . ' ';
        $result .= "\r\n" . sprintf(__('Historical data has been updated and is now usable in %s controls.', 'live-weather-station'), LWS_PLUGIN_NAME);
        return $result;
    }

    /**
     * Compute the summary and set according progress and state.
     *
     * @since 3.7.0
     */
    private function summarize() {
        $days_todo = 0;
        $days_done = 0;
        $ended_ext = array();
        $ended_int = array();
        foreach ($this->params['todo_ext'] as $todo) {
            $days_todo += $this->params['summary'][$todo['module_id']]['days_todo'];
            $days_done += $this->params['summary'][$todo['module_id']]['days_done'];
            if ($this->params['summary'][$todo['module_id']]['days_done'] === $this->params['summary'][$todo['module_id']]['days_todo']) {
                $ended_ext[] = $todo['module_id'];
            }
        }
        foreach ($this->params['todo_int'] as $todo) {
            $days_todo += $this->params['summary'][$todo['module_id']]['days_todo'];
            $days_done += $this->params['summary'][$todo['module_id']]['days_done'];
            if ($this->params['summary'][$todo['module_id']]['days_done'] === $this->params['summary'][$todo['module_id']]['days_todo']) {
                $ended_int[] = $todo['module_id'];
            }
        }
        foreach ($this->params['done'] as $done) {
            $days_todo += $this->params['summary'][$done['module_id']]['days_todo'];
            $days_done += $this->params['summary'][$done['module_id']]['days_done'];
        }
        foreach ($ended_ext as $ended) {
            $this->params['done'][] = $this->params['todo_ext'][$ended];
            unset($this->params['todo_ext'][$ended]);
        }
        foreach ($ended_int as $ended) {
            $this->params['done'][] = $this->params['todo_int'][$ended];
            unset($this->params['todo_int'][$ended]);
        }
        if ($days_todo !== 0) {
            $this->set_progress(100 * $days_done / $days_todo);
        }
        else {
            $this->set_progress(100);
        }
    }

    /**
     * Init the process.
     *
     * @since 3.7.0
     */
    protected function init_core(){

        // $this->params['init']['station_id']
        // $this->params['init']['start_date'] // local timestamp
        // $this->params['init']['end_date']   // local timestamp
        // $args['init']['force'] = false;

        $this->params['init']['end_date'] -= 86400;

        $this->bp_service = 'WeatherFlow';
        $station = $this->get_station_informations_by_station_id($this->params['init']['station_id']);
        $this->params['init']['station_name'] = $station['station_name'];
        $this->params['init']['service_id'] = $station['service_id'];
        $this->params['init']['loc_timezone'] = $station['loc_timezone'];
        $this->params['init']['loc_altitude'] = $station['loc_altitude'];
        $old_dates = array();






        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_name, module_id, module_type, module_name FROM " . $table_name . " WHERE device_id = '" . $this->params['init']['station_id'] . "'";
        $rows = $wpdb->get_results($sql, ARRAY_A);




        $this->params['todo_ext'] = array();
        $this->params['todo_int'] = array();
        $this->params['done'] = array();
        $this->params['summary'] = array();
        foreach ($rows as $row) {
            if ($row['module_type'] === 'NAMain' ||
                $row['module_type'] === 'NAModule1' ||
                $row['module_type'] === 'NAModule2' ||
                $row['module_type'] === 'NAModule3' ||
                $row['module_type'] === 'NAModule4') {
                $this->get_oldest_measure($this->params['init']['station_id'], $row['module_id'], $row['module_type']);
                $old = $this->params['init']['start_date'];
                if (count($this->netatmo_datas) > 0) {
                    if (array_key_exists('start', $this->netatmo_datas)) {
                        $old = 86400 * (int)floor(($this->netatmo_datas['start'] + 86400) / 86400);
                    }
                }
                $old_dates[] = $old;
                if ($this->params['init']['end_date'] < $old) {
                    $days_todo = 0;
                }
                else {
                    $days_todo = 2 + (int)floor(($this->params['init']['end_date'] - max($old, $this->params['init']['start_date'])) / 86400);
                }
                $module = array('device_id' => $this->params['init']['station_id'], 'module_id' => $row['module_id'], 'module_name' => $row['module_name'], 'module_type' => $row['module_type'], 'start_date' => $old);
                if ($row['module_type'] === 'NAModule3' ||
                    $row['module_type'] === 'NAModule4') {
                    $this->params['todo_int'][$row['module_id']] = $module;
                }
                else {
                    $this->params['todo_ext'][$row['module_id']] = $module;
                }
                $this->params['summary'][$row['module_id']]['name'] = $row['module_name'];
                $this->params['summary'][$row['module_id']]['measurements'] = 0;
                $this->params['summary'][$row['module_id']]['days_done'] = 0;
                $this->params['summary'][$row['module_id']]['days_todo'] = $days_todo;
            }
        }
        $this->params['process']['start_date'] = max(min($old_dates), $this->params['init']['start_date']);
        $this->params['process']['end_date'] = $this->params['init']['end_date'];
        $this->params['process']['now_ext_date'] = $this->params['process']['start_date'];
        $this->params['process']['now_int_date'] = $this->params['process']['start_date'];
        $this->summarize();
    }

    /**
     * Add all values for NAMain module.
     *
     * @return array An array of timestamped values.
     * @since 3.7.0
     */
    private function expand_namain(){
        $cpt = 0;
        $cpt_type = 'temperature';
        $result = array();
        foreach ($this->netatmo_datas as $type => $set) {
            if (in_array($type, $this->available_types['NAMain'])) {
                if (count($set) > $cpt) {
                    $cpt = count($set);
                    $cpt_type = $type;
                }
            }
        }
        if (array_key_exists($cpt_type, $this->netatmo_datas)) {
            foreach ($this->netatmo_datas[$cpt_type] as $ts => $dummy) {
                $ref_h = null;
                $ref_t = null;
                $ref_n = null;
                $ref_c = null;
                if (array_key_exists($ts, $this->netatmo_datas['co2'])) {
                    $result['co2'][$ts] = $this->netatmo_datas['co2'][$ts];
                    $ref_c = $this->netatmo_datas['co2'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['humidity'])) {
                    $result['humidity'][$ts] = $this->netatmo_datas['humidity'][$ts];
                    $ref_h = $this->netatmo_datas['humidity'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['noise'])) {
                    $result['noise'][$ts] = $this->netatmo_datas['noise'][$ts];
                    $ref_n = $this->netatmo_datas['noise'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['pressure'])) {
                    $result['pressure_sl'][$ts] = $this->netatmo_datas['pressure'][$ts];
                    $result['pressure'][$ts] = $this->convert_from_mslp_to_baro($this->netatmo_datas['pressure'][$ts], $this->params['init']['loc_altitude']);
                }
                if (array_key_exists($ts, $this->netatmo_datas['temperature'])) {
                    $result['temperature'][$ts] = $this->netatmo_datas['temperature'][$ts];
                    $ref_t = $this->netatmo_datas['temperature'][$ts];
                }
                $h = $this->compute_health_index($ref_t, $ref_h, $ref_c, $ref_n);
                if (array_key_exists('health_idx', $h)) {
                    $result['health_idx'][$ts] = $h['health_idx'];
                }
            }
        }
        unset($this->netatmo_datas);
        return $result;
    }

    /**
     * Add all values for NAModule1 module.
     *
     * @return array An array of timestamped values.
     * @since 3.7.0
     */
    private function expand_namodule1(){
        $cpt = 0;
        $cpt_type = 'temperature';
        $result = array();
        foreach ($this->netatmo_datas as $type => $set) {
            if (in_array($type, $this->available_types['NAModule1'])) {
                if (count($set) > $cpt) {
                    $cpt = count($set);
                    $cpt_type = $type;
                }
            }
        }
        if (array_key_exists($cpt_type, $this->netatmo_datas)) {
            foreach ($this->netatmo_datas[$cpt_type] as $ts => $dummy) {
                if (array_key_exists($ts, $this->netatmo_datas['humidity'])) {
                    $result['humidity'][$ts] = $this->netatmo_datas['humidity'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['temperature'])) {
                    $result['temperature'][$ts] = $this->netatmo_datas['temperature'][$ts];
                }
            }
        }
        unset($this->netatmo_datas);
        return $result;
    }

    /**
     * Add all values for NAModule2 module.
     *
     * @return array An array of timestamped values.
     * @since 3.7.0
     */
    private function expand_namodule2(){
        $cpt = 0;
        $cpt_type = 'windangle';
        $result = array();
        foreach ($this->netatmo_datas as $type => $set) {
            if (in_array($type, $this->available_types['NAModule2'])) {
                if (count($set) > $cpt) {
                    $cpt = count($set);
                    $cpt_type = $type;
                }
            }
        }
        if (array_key_exists($cpt_type, $this->netatmo_datas)) {
            foreach ($this->netatmo_datas[$cpt_type] as $ts => $dummy) {
                if (array_key_exists($ts, $this->netatmo_datas['windangle'])) {
                    $result['windangle'][$ts] = $this->netatmo_datas['windangle'][$ts];
                    $result['winddirection'][$ts] = (int)floor(($this->netatmo_datas['windangle'][$ts] + 180) % 360);
                }
                if (array_key_exists($ts, $this->netatmo_datas['windstrength'])) {
                    $result['windstrength'][$ts] = $this->netatmo_datas['windstrength'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['gustangle'])) {
                    $result['gustangle'][$ts] = $this->netatmo_datas['gustangle'][$ts];
                    $result['gustdirection'][$ts] = (int)floor(($this->netatmo_datas['gustangle'][$ts] + 180) % 360);
                }
                if (array_key_exists($ts, $this->netatmo_datas['guststrength'])) {
                    $result['guststrength'][$ts] = $this->netatmo_datas['guststrength'][$ts];
                }
            }
        }
        unset($this->netatmo_datas);
        return $result;
    }

    /**
     * Add all values for NAModule3 module.
     *
     * @return array An array of timestamped values.
     * @since 3.7.0
     */
    private function expand_namodule3(){
        $cpt = 0;
        $cpt_type = 'sum_rain';
        $result = array();
        foreach ($this->netatmo_datas as $type => $set) {
            if (in_array($type, $this->available_types['NAModule3'])) {
                if (count($set) > $cpt) {
                    $cpt = count($set);
                    $cpt_type = $type;
                }
            }
        }
        if (array_key_exists($cpt_type, $this->netatmo_datas)) {
            foreach ($this->netatmo_datas[$cpt_type] as $ts => $dummy) {
                if (array_key_exists($ts, $this->netatmo_datas['sum_rain'])) {
                    $result['sum_rain'][$ts] = $this->netatmo_datas['sum_rain'][$ts];
                }
            }
        }
        unset($this->netatmo_datas);
        return $result;
    }


    /**
     * Add all values for NAModule4 module.
     *
     * @return array An array of timestamped values.
     * @since 3.7.0
     */
    private function expand_namodule4(){
        $cpt = 0;
        $cpt_type = 'temperature';
        $result = array();
        foreach ($this->netatmo_datas as $type => $set) {
            if (in_array($type, $this->available_types['NAModule4'])) {
                if (count($set) > $cpt) {
                    $cpt = count($set);
                    $cpt_type = $type;
                }
            }
        }
        if (array_key_exists($cpt_type, $this->netatmo_datas)) {
            foreach ($this->netatmo_datas[$cpt_type] as $ts => $dummy) {
                $ref_h = null;
                $ref_t = null;
                $ref_n = null;
                $ref_c = null;
                if (array_key_exists($ts, $this->netatmo_datas['co2'])) {
                    $result['co2'][$ts] = $this->netatmo_datas['co2'][$ts];
                    $ref_c = $this->netatmo_datas['co2'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['humidity'])) {
                    $result['humidity'][$ts] = $this->netatmo_datas['humidity'][$ts];
                    $ref_h = $this->netatmo_datas['humidity'][$ts];
                }
                if (array_key_exists($ts, $this->netatmo_datas['temperature'])) {
                    $result['temperature'][$ts] = $this->netatmo_datas['temperature'][$ts];
                    $ref_t = $this->netatmo_datas['temperature'][$ts];
                }
                $h = $this->compute_health_index($ref_t, $ref_h, $ref_c, $ref_n);
                if (array_key_exists('health_idx', $h)) {
                    $result['health_idx'][$ts] = $h['health_idx'];
                }
            }
        }
        unset($this->netatmo_datas);
        return $result;
    }

    /**
     * Add all values for NAComputed module.
     *
     * @param array $data An array containing reference values.
     * @return array An array of timestamped values.
     * @since 3.7.0
     */
    private function expand_nacomputed($data){
        $cpt = 0;
        $cpt_type = 'temperature';
        $result = array();
        foreach ($data as $type => $set) {
            if (in_array($type, array('temperature', 'humidity', 'pressure', 'windstrength'))) {
                if (count($set) > $cpt) {
                    $cpt = count($set);
                    $cpt_type = $type;
                }
            }
        }
        if (array_key_exists($cpt_type, $data)) {
            foreach ($data[$cpt_type] as $ts => $dummy) {
                $h = null;
                $t = null;
                $p = null;
                $w = null;
                $d = null;
                if (array_key_exists($ts, $data['humidity'])) {
                    $h = $data['humidity'][$ts];
                }
                if (array_key_exists($ts, $data['temperature'])) {
                    $t = $data['temperature'][$ts];
                }
                if (array_key_exists($ts, $data['pressure'])) {
                    $p = $data['pressure'][$ts];
                }
                if (array_key_exists($ts, $data['windstrength'])) {
                    $w = $data['windstrength'][$ts];
                }
                if (isset($t) && isset($h)) {
                    $d = $this->compute_dew_point($t, $h);
                    $result['dew_point'][$ts] = $d;
                    $result['frost_point'][$ts] = $this->compute_frost_point($t, $d);
                    $result['heat_index'][$ts] = $this->compute_heat_index($t, $h);
                    $result['humidex'][$ts] = $this->compute_humidex($t, $d);
                    $result['cloud_ceiling'][$ts] = $this->compute_cloud_ceiling($t, $d);
                    $result['cbi'][$ts] = $this->compute_cbi($t, $h);
                    $result['wet_bulb'][$ts] = $this->compute_wet_bulb($t, $h);
                    $result['delta_t'][$ts] = $this->compute_delta_t($t, $h);
                    $result['partial_vapor_pressure'][$ts] = $this->compute_partial_vapor_pressure($t, $h);
                    $result['wood_emc'][$ts] = $this->compute_emc($t, $h);
                    $result['summer_simmer'][$ts] = $this->compute_summer_simmer($t, $h);
                }
                if (isset($t) && isset($h) && isset($p)) {
                    $result['air_density'][$ts] = $this->compute_air_density($t, 100 * $p, $h);
                    $result['partial_absolute_humidity'][$ts] = $this->compute_partial_absolute_humidity($t, 100 * $p, $h);
                    $result['specific_enthalpy'][$ts] = $this->compute_specific_enthalpy($t, 100 * $p, $h);
                    $result['alt_density'][$ts] = $this->compute_density_altitude($t, 100 * $p, $h);
                }
                if (isset($t) && isset($p)) {
                    $result['saturation_absolute_humidity'][$ts] = $this->compute_saturation_absolute_humidity($t, 100 * $p);
                    $result['potential_temperature'][$ts] = $this->compute_potential_temperature($t, 100 * $p);
                    $result['equivalent_temperature'][$ts] = $this->compute_equivalent_temperature($t, 100 * $p);
                    $result['equivalent_potential_temperature'][$ts] = $this->compute_equivalent_potential_temperature($t, 100 * $p);
                }
                if (isset($p)) {
                    $result['alt_pressure'][$ts] = $this->compute_pressure_altitude(100 * $p);
                }
                if (isset($t)) {
                    $result['saturation_vapor_pressure'][$ts] = $this->compute_saturation_vapor_pressure($t);
                }
                if (isset($t) && isset($w)) {
                    $result['wind_chill'][$ts] = $this->compute_wind_chill($t, $w);
                }
                if (isset($t) && isset($h) && isset($w)) {
                    $result['steadman'][$ts] = $this->compute_steadman($t, $h, $w);
                }
            }
        }
        unset($this->netatmo_datas);
        return $result;
    }

    /**
     * Run the process for external modules.
     *
     * @since 3.7.0
     */
    private function run_ext(){
        $namain = array();     // Main module
        $namodule1 = array();  // Outdoor module
        $namodule2 = array();  // Wind module
        $nacomputed = array(); // Computed values
        $nacomputed['meta'] = array('device_id' =>$this->params['init']['station_id'], 'module_id' => self::get_computed_virtual_id($this->params['init']['station_id']), 'module_name' => __('[Computed Values]', 'live-weather-station'), 'module_type' => 'NAComputed', 'start_date' => null);
        $query_start = 0;
        $query_end = 0;
        $done = false;
        if (count($this->params['todo_ext']) > 0) {
            foreach ($this->params['todo_ext'] as $module) {
                switch ($module['module_type']) {
                    case 'NAMain':
                        $namain['meta'] = $module;
                        break;
                    case 'NAModule1':
                        $namodule1['meta'] = $module;
                        break;
                    case 'NAModule2':
                        $namodule2['meta'] = $module;
                        break;
                }
                $query_start = $this->params['process']['now_ext_date'];
                $query_end = (86400 * 21) + $query_start -1;
                if ($query_end > $this->params['process']['end_date']) {
                    $query_end = $this->params['process']['end_date'] + 2*86400 - 1;
                }
                if ($module['start_date'] > $query_end || $this->params['process']['end_date'] < $query_start) {
                    $this->params['summary'][$module['module_id']]['days_done'] = $this->params['summary'][$module['module_id']]['days_todo'];
                }
                else {
                    $done = $this->get_measures($this->params['init']['station_id'], $module['module_id'], '30min', $this->available_types[$module['module_type']], $query_start, $query_end, 1024, false);
                    if (!$done) {
                        break;
                    }
                    switch ($module['module_type']) {
                        case 'NAMain':
                            $namain['values'] = $this->expand_namain();
                            break;
                        case 'NAModule1':
                            $namodule1['values'] = $this->expand_namodule1();
                            break;
                        case 'NAModule2':
                            $namodule2['values'] = $this->expand_namodule2();
                            break;
                    }
                }
            }
            if ($this->has_computer()) {
                if (array_key_exists('values', $namodule1)) {
                    if (array_key_exists('temperature', $namodule1['values'])) {
                        $nacomputed['values']['temperature'] = $namodule1['values']['temperature'];
                    } else {
                        $nacomputed['values']['temperature'] = array();
                    }
                    if (array_key_exists('humidity', $namodule1['values'])) {
                        $nacomputed['values']['humidity'] = $namodule1['values']['humidity'];
                    } else {
                        $nacomputed['values']['humidity'] = array();
                    }
                } else {
                    $nacomputed['values']['temperature'] = array();
                    $nacomputed['values']['humidity'] = array();
                }
                if (array_key_exists('values', $namain) && array_key_exists('pressure', $namain['values'])) {
                    $nacomputed['values']['pressure'] = $namain['values']['pressure'];
                } else {
                    $nacomputed['values']['pressure'] = array();
                }
                if (array_key_exists('values', $namodule2) && array_key_exists('windstrength', $namodule2['values'])) {
                    $nacomputed['values']['windstrength'] = $namodule2['values']['windstrength'];
                } else {
                    $nacomputed['values']['windstrength'] = array();
                }
                $nacomputed['values'] = $this->expand_nacomputed($nacomputed['values']);
                unset($nacomputed['values']['temperature']);
                unset($nacomputed['values']['humidity']);
                unset($nacomputed['values']['pressure']);
                unset($nacomputed['values']['windstrength']);
            }
            $force = null;
            if (array_key_exists('force', $this->params['init'])) {
                $force = $this->params['init']['force'];
            }
            $history = new Builder(LWS_PLUGIN_NAME, LWS_VERSION);
            if ($this->has_computer()) {
                $history->import_data($nacomputed, $query_start, $query_end + 1, $force);
            }

            foreach ($this->params['todo_ext'] as $module) {
                switch ($module['module_type']) {
                    case 'NAMain':
                        $l = $history->import_data($namain, $query_start, $query_end + 1, $force);
                        $this->params['summary'][$namain['meta']['module_id']]['measurements'] += $l[0];
                        $this->params['summary'][$namain['meta']['module_id']]['days_done'] += $l[1];
                        break;
                    case 'NAModule1':
                        $l = $history->import_data($namodule1, $query_start, $query_end + 1, $force);
                        $this->params['summary'][$namodule1['meta']['module_id']]['measurements'] += $l[0];
                        $this->params['summary'][$namodule1['meta']['module_id']]['days_done'] += $l[1];
                        break;
                    case 'NAModule2':
                        $l = $history->import_data($namodule2, $query_start, $query_end + 1, $force);
                        $this->params['summary'][$namodule2['meta']['module_id']]['measurements'] += $l[0];
                        $this->params['summary'][$namodule2['meta']['module_id']]['days_done'] += $l[1];
                        break;
                }
            }
            if ($done) {
                $this->params['process']['now_ext_date'] += (86400 * 21);
            }
        }
    }

    /**
     * Run the process for internal or other modules.
     *
     * @since 3.7.0
     */
    private function run_int(){
        $namodules = array();     // Generic modules (NAModule3 or NAModule4)
        $query_start = 0;
        $query_end = 0;
        $done = false;
        if (count($this->params['todo_int']) > 0) {
            foreach ($this->params['todo_int'] as $module) {
                $namodule = array();
                $namodule['meta'] = $module;
                $query_start = $this->params['process']['now_int_date'];
                $query_end = (86400 * 21) + $query_start -1;

                if ($query_end > $this->params['process']['end_date']) {
                    $query_end = $this->params['process']['end_date'] + 2*86400 - 1;
                }
                if ($module['start_date'] > $query_end || $this->params['process']['end_date'] < $query_start) {
                    $this->params['summary'][$module['module_id']]['days_done'] = $this->params['summary'][$module['module_id']]['days_todo'];
                }
                else {
                    $done = $this->get_measures($this->params['init']['station_id'], $module['module_id'], '30min', $this->available_types[$module['module_type']], $query_start, $query_end, 1024, false);
                    if (!$done) {
                        break;
                    }
                    switch ($module['module_type']) {
                        case 'NAModule3':
                            $namodule['values'] = $this->expand_namodule3();
                            break;
                        case 'NAModule4':
                            $namodule['values'] = $this->expand_namodule4();
                            break;
                    }
                    $namodules[] = $namodule;
                }
            }
            $force = null;
            if (array_key_exists('force', $this->params['init'])) {
                $force = $this->params['init']['force'];
            }
            $history = new Builder(LWS_PLUGIN_NAME, LWS_VERSION);
            foreach ($namodules as $namodule) {
                $l = $history->import_data($namodule, $query_start, $query_end + 1, $force);
                $this->params['summary'][$namodule['meta']['module_id']]['measurements'] += $l[0];
                $this->params['summary'][$namodule['meta']['module_id']]['days_done'] += $l[1];
            }
            if ($done){
                $this->params['process']['now_int_date'] += (86400 * 21);
            }
        }
    }

    /**
     * Run the process.
     *
     * @since 3.7.0
     */
    protected function run_core(){
        $max = 1;
        for ($i=1; $i<8; $i++) {
            if ((int)round(ini_get('max_execution_time') > $i*40)) {
                $max += 1;
            }
        }
        for ($i=1; $i<=$max; $i++) {
            if (count($this->params['todo_ext']) > 0) {
                $this->run_ext();
            }
            if (count($this->params['todo_int']) > 0) {
                $this->run_int();
            }
        }
        $this->summarize();
    }

}