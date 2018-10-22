<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Unit\Conversion;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;
use WeatherStation\SDK\Netatmo\Plugin\Client;

/**
 * A process to import old data from a Netatmo station.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class NetatmoImporter extends Process {

    use Client, Conversion, Query, DateTimeConversion;


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
        return $this->state_pause;
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
            return lws__('Netatmo importer', 'live-weather-station');
        }
        else {
            return 'Netatmo importer';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return lws__('Importing old data from a Netatmo station.', 'live-weather-station');
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.7.0
     */
    protected function message() {
        $result = sprintf(lws__('Here are the details of importing old data from the "%s" station:', 'live-weather-station'), $this->params['init']['station_name']) . "\r\n";
        foreach ($this->params['summary'] as $module) {
            $result .= '  - ' . sprintf(lws__('"%s": %s measurements spread over %s days.', 'live-weather-station'), $module['name'], $module['measurements'], $module['days']) . "\r\n";
        }
        $result .= "\r\n" . sprintf(lws__('These measurements were compiled in %s.', 'live-weather-station'), $this->get_age_hours_from_seconds($this->exectime)) . ' ';
        $result .= "\r\n" . sprintf(lws__('Historical data has been updated and is now usable in %s controls.', 'live-weather-station'), LWS_PLUGIN_NAME);
        return $result;
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
        return (count($this->params['stations']['todo']) === 0);
    }

    /**
     * Init the process.
     *
     * @since 3.7.0
     */
    protected function init_core(){

        // $this->params['init']['station_id']
        // $this->params['init']['start_date']
        // $this->params['init']['end_date']

        $station = $this->get_station_informations_by_station_id($this->params['init']['station_id']);
        $this->params['init']['station_name'] = $station['station_name'];
        $this->params['init']['loc_timezone'] = $station['loc_timezone'];
        $this->params['process']['now'] = $this->params['init']['start_date']; //self::get_local_date($station['loc_timezone']);
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
                $module = array();
                $module[] = array('module_id' => $row['module_id'], 'module_name' => $row['module_name'], 'module_type' => $row['module_type']);
                if ($row['module_type'] === 'NAModule3' ||
                    $row['module_type'] === 'NAModule4') {
                    $this->params['todo_int'][] = $module;
                }
                else {
                    $this->params['todo_ext'][] = $module;
                }
                $this->params['summary'][$row['module_id']]['name'] = $row['module_name'];
                $this->params['summary'][$row['module_id']]['measurements'] = 0;
                $this->params['summary'][$row['module_id']]['days'] = 0;


            }
        }
    }

    /**
     * Run the process.
     *
     * @since 3.7.0
     */
    protected function run_core(){
        if (count($this->params['stations']['todo_ext']) > 0) {






            $station_spec = reset($this->params['stations']['todo']);
            $station_id = key($this->params['stations']['todo']);
            try {
                //$this->expand($station_id, $station_spec);
                unset($this->params['stations']['todo'][$station_id]);
                $this->params['stations']['done'][$station_id] = $station_spec;
                $this->set_progress(100 * count($this->params['stations']['done']) / (count($this->params['stations']['todo']) + count($this->params['stations']['done'])));
            }
            catch (\Exception $ex) {
                Logger::error($this->facility, null, null, null, null, null, 999, 'Error while running background process {' . $this->uuid() . '}. Message: ' . $ex->getMessage());
            }
        }
    }

}