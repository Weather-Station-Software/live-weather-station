<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Unit\Conversion;
use WeatherStation\SDK\Pioupiou\Plugin\ArchiveClient;
use WeatherStation\Data\History\Builder;

/**
 * A process to import old data from a Netatmo station.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class PioupiouImporter extends Process {

    use ArchiveClient, Conversion, Query;

    protected $terminated = false;

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
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('Pioupiou sensor importer', 'live-weather-station');
        }
        else {
            return 'Pioupiou sensor importer';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return __('Importing old data from a Pioupiou sensor.', 'live-weather-station');
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
        if ($this->terminated) {
            return true;
        }
        return (count($this->params['todo_ext']) === 0);
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
     * Compute the summary and set according progress and state.
     *
     * @since 3.7.0
     */
    private function summarize() {
        $days_todo = 0;
        $days_done = 0;
        $ended_ext = array();
        foreach ($this->params['todo_ext'] as $todo) {
            $days_todo += $this->params['summary'][$todo['module_id']]['days_todo'];
            $days_done += $this->params['summary'][$todo['module_id']]['days_done'];
            if ($this->params['summary'][$todo['module_id']]['days_done'] === $this->params['summary'][$todo['module_id']]['days_todo']) {
                $ended_ext[] = $todo['module_id'];
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
        if ($days_todo !== 0) {
            $this->set_progress(100 * $days_done / $days_todo);
        }
        else {
            $this->set_progress(100);
        }
        $this->terminated = ($days_done >= $days_todo);
    }

    /**
     * Init the process.
     *
     * @since 3.7.0
     */
    protected function init_core(){

        $station = $this->get_station_informations_by_station_id($this->params['init']['station_id']);
        $this->params['init']['station_name'] = $station['station_name'];
        $this->params['init']['service_id'] = $station['service_id'];
        $this->params['init']['loc_timezone'] = $station['loc_timezone'];
        $this->params['init']['loc_altitude'] = $station['loc_altitude'];

        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->params['init']['start_date'] . ' 00:00:00', new \DateTimeZone($this->params['init']['loc_timezone']));
        if ($datetime !== false) {
            $this->params['init']['start_date'] = $datetime->getTimestamp();
        }
        else {
            $this->params['init']['start_date'] = 0;
        }

        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->params['init']['end_date'] . ' 23:59:59', new \DateTimeZone($this->params['init']['loc_timezone']));
        if ($datetime !== false) {
            $this->params['init']['end_date'] = $datetime->getTimestamp();
        }
        else {
            $this->params['init']['end_date'] = 0;
        }

        $this->bp_service = 'Pioupiou';

        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT device_name, module_id, module_type, module_name FROM " . $table_name . " WHERE device_id = '" . $this->params['init']['station_id'] . "'";
        $rows = $wpdb->get_results($sql, ARRAY_A);
        $this->params['todo_ext'] = array();
        $this->params['done'] = array();
        $this->params['summary'] = array();
        foreach ($rows as $row) {
            if ($row['module_type'] === 'NAModule2') {
                $days_todo = (int)floor((1 + $this->params['init']['end_date'] - $this->params['init']['start_date']) / 86400);
                $module = array('device_id' => $this->params['init']['station_id'], 'module_id' => $row['module_id'], 'module_name' => $row['module_name'], 'module_type' => $row['module_type'], 'start_date' => $old = $this->params['init']['start_date']);
                $this->params['todo_ext'][$row['module_id']] = $module;
                $this->params['summary'][$row['module_id']]['name'] = $row['module_name'];
                $this->params['summary'][$row['module_id']]['measurements'] = 0;
                $this->params['summary'][$row['module_id']]['days_done'] = 0;
                $this->params['summary'][$row['module_id']]['days_todo'] = $days_todo;
            }
        }
        $this->params['process']['start_date'] = $this->params['init']['start_date'];
        $this->params['process']['end_date'] = $this->params['init']['end_date'];
        $this->params['process']['now_ext_date'] = $this->params['process']['start_date'];
        $this->summarize();
    }

    /**
     * Run the process for external modules.
     *
     * @since 3.7.0
     */
    private function run_ext(){
        $namodule2 = array();  // Wind module
        $query_start = 0;
        $query_end = 0;
        if (count($this->params['todo_ext']) > 0) {
            foreach ($this->params['todo_ext'] as $module) {
                switch ($module['module_type']) {
                    case 'NAModule2':
                        $namodule2['meta'] = $module;
                        break;
                }
                $query_start = $this->params['process']['now_ext_date'];
                $query_end = (86400 * 21) + $query_start;
                if ($query_end > $this->params['process']['end_date']) {
                    $query_end = $this->params['process']['end_date'];
                }
                if ($this->params['process']['start_date'] <= $query_start && $query_start < $this->params['process']['end_date'] &&
                    $this->params['process']['start_date'] < $query_end && $query_end <= $this->params['process']['end_date']) {
                    $data = $this->get_archive($this->params['init']['service_id'], $this->params['init']['station_id'], $this->params['init']['station_name'], $this->params['init']['loc_timezone'], $query_start, $query_end);
                    if (!$data) {
                        break;
                    }
                    $namodule2['values'] = $data;
                }
                else {
                    $this->params['summary'][$module['module_id']]['days_done'] = $this->params['summary'][$module['module_id']]['days_todo'];
                }
            }
            $force = null;
            if (array_key_exists('force', $this->params['init'])) {
                $force = $this->params['init']['force'];
            }
            $history = new Builder(LWS_PLUGIN_NAME, LWS_VERSION);
            foreach ($this->params['todo_ext'] as $module) {
                switch ($module['module_type']) {
                    case 'NAModule2':
                        $l = $history->import_data($namodule2, $query_start, $query_end + 1, $force);
                        $this->params['summary'][$namodule2['meta']['module_id']]['measurements'] += $l[0];
                        $c = (int)ceil(($query_end - $query_start) / 86400);
                        if ($c > 0) {
                            $this->params['summary'][$namodule2['meta']['module_id']]['days_done'] += $c;
                        }
                        else {
                            $this->params['summary'][$namodule2['meta']['module_id']]['days_done'] = $this->params['summary'][$namodule2['meta']['module_id']]['days_todo'];
                        }

                        $this->params['process']['now_ext_date'] += (86400 * 21);
                        break;
                }
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
        }
        $this->update_oldest_data($this->params['init']['station_id']);
        $this->summarize();
        if ($this->is_terminated()) {
            Logger::notice('Import Manager', 'Pioupiou', $this->params['init']['station_id'], $this->params['init']['station_name'], null, null, null, 'Data import terminated.');
        }
    }

}