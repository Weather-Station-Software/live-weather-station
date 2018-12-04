<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;
use WeatherStation\System\Device\Manager as ModuleManager;
use WeatherStation\System\Logs\Logger;

/**
 * A process to export data line after line.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
abstract class LineExporter extends Process {

    use Query, DateTimeConversion;

    protected $extension = 'txt';
    protected $fullfilename = null;
    protected $facility = 'Export Manager';


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
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        $sql = "SELECT COUNT(*) as CNT FROM " . $table_name . " WHERE device_id = '" . $this->params['init']['station_id'] . "' AND `timestamp` >= '" . $this->params['init']['start_date'] . "' AND `timestamp` <= '" . $this->params['init']['end_date'] . "'";
        $query = $wpdb->get_results($sql, ARRAY_A);
        if (count($query) > 0) {
            $count = $query[0]['CNT'];
        }
        else {
            $count = 0;
        }
        return ($count > 0);
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.7.0
     */
    protected function is_terminated(){
        return ($this->params['todo'] <= $this->params['done']);
    }

    /**
     * Verify if process is in error.
     *
     * @return boolean True if the process is in error. False otherwise.
     * @since 3.7.0
     */
    protected function is_in_error(){
        return $this->params['error'];
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.7.0
     */
    protected function message() {
        if ($this->is_in_error()) {
            $result = sprintf(__('Unable to create the file named %s in the directory "%s".', 'live-weather-station'), FS::get_file_name($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension), FS::get_root_name()) . "\r\n";
            $result .= "\r\n" . sprintf(__('Check the events log to see what\'s going on: %s', 'live-weather-station'), lws_get_admin_page_url('lws-events')) . "\r\n";
        }
        else {
            $fileurl = FS::get_full_file_url($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension);
            $result = sprintf(__('Historical data of "%s" has been correctly exported for the period from %s to %s.', 'live-weather-station'), $this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date']) . "\r\n";
            $result .= sprintf(__('The file is now ready to download. It will be kept on your server for %s days.', 'live-weather-station'), get_option('live_weather_station_file_retention', '7')) . "\r\n";
            $result .= "\r\n" . $fileurl . "\r\n";
        }
        return $result;
    }

    /**
     * Begin the main process job.
     *
     * @since 3.7.0
     */
    protected abstract function begin_job();

    /**
     * Do the main process job for each line.
     *
     * @param array $set The line to process.
     * @since 3.7.0
     */
    protected abstract function do_job($set);

    /**
     * End the main process job.
     *
     * @since 3.7.0
     */
    protected abstract function end_job();

    /**
     * Do the main process job.
     *
     * @since 3.7.0
     */
    protected function job(){
        $query_start = $this->params['now_date'];
        $query_end = self::add_days_to_mysql_date($this->params['now_date'], 21);
        if (!self::mysql_is_ordered($query_end, $this->params['end_date'])) {
            $query_end = $this->params['end_date'];
        }
        if (self::mysql_is_ordered($query_start, $query_end)) {
            $this->params['now_date'] = self::add_days_to_mysql_date($query_end, 1);
            $modules = ModuleManager::get_modules_names($this->params['init']['station_id']);
            global $wpdb;
            $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
            $order_by = 'ORDER BY `timestamp` ASC, `module_id` ASC, `measure_type` ASC';
            $sql = "SELECT * FROM " . $table_name . " WHERE device_id = '" . $this->params['init']['station_id'] . "' AND `timestamp` >= '" . $query_start . "' AND `timestamp` <= '" . $query_end . "' " . $order_by;
            $query = $wpdb->get_results($sql, ARRAY_A);
            if (count($query) > 0) {
                $ts = '';
                $md = '';
                $tp = '';
                $set = array();
                foreach ($query as $line) {
                    if ($line['timestamp'] !== $ts || $line['module_id'] !== $md || $line['measure_type'] !== $tp) {
                        if (!empty($set)) {
                            $this->do_job($set);
                        }
                        $ts = $line['timestamp'];
                        $md = $line['module_id'];
                        $tp = $line['measure_type'];
                        $set = array();
                        $set['timestamp'] = $line['timestamp'];
                        $set['module_id'] = $line['module_id'];
                        $set['module_type'] = $line['module_type'];
                        if (array_key_exists($set['module_id'], $modules)) {
                            $set['module_name'] = $modules[$set['module_id']];
                        }
                        else {
                            $set['module_name'] = '<unnamed>';
                        }
                        $set['measure_type'] = $line['measure_type'];
                        $set[$line['measure_set']] = $line['measure_value'];
                    }
                    else {
                        $set[$line['measure_set']] = $line['measure_value'];
                    }
                }
                $this->params['done'] = $this->params['done'] + count($query);
            }
        }
        else {
            $this->params['done'] = $this->params['todo'];
        }
    }

    /**
     * Init the process.
     *
     * @since 3.7.0
     */
    protected function init_core(){

        // $this->params['init']['station_id'] // ID of the station
        // $this->params['init']['start_date'] // local timestamp
        // $this->params['init']['end_date']   // local timestamp

        if (!self::mysql_is_ordered($this->params['init']['start_date'], $this->params['init']['end_date'])) {
            $end = $this->params['init']['start_date'];
            $start = $this->params['init']['end_date'];
            $this->params['init']['start_date'] = $start;
            $this->params['init']['end_date'] = $end;
        }

        $this->uuid = $this->meta_uuid();
        $station = $this->get_station_informations_by_station_id($this->params['init']['station_id']);
        $this->params['init']['station_name'] = trim($station['station_name']);
        $this->params['init']['loc_timezone'] = $station['loc_timezone'];
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        $sql = "SELECT COUNT(*) as CNT FROM " . $table_name . " WHERE device_id = '" . $this->params['init']['station_id'] . "' AND `timestamp` >= '" . $this->params['init']['start_date'] . "' AND `timestamp` <= '" . $this->params['init']['end_date'] . "'";
        $query = $wpdb->get_results($sql, ARRAY_A);
        if (count($query) > 0) {
            $count = $query[0]['CNT'];
        }
        else {
            $count = 0;
        }
        $this->params['error'] = (false === FS::file_for_write($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension));
        $this->params['todo'] = $count;
        $this->params['done'] = 0;
        $this->params['start_date'] = $this->params['init']['start_date'];
        $this->params['end_date'] = $this->params['init']['end_date'];
        $this->params['now_date'] = $this->params['init']['start_date'];
    }

    /**
     * Run the process.
     *
     * @since 3.7.0
     */
    protected function run_core(){
        $max = 1;
        for ($i=1; $i<20; $i++) {
            if ((int)round(ini_get('max_execution_time') > $i*20)) {
                $max += 1;
            }
        }
        $this->fullfilename = FS::get_full_file_name($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension);
        if (!file_exists($this->fullfilename)) {
            if (!FS::create_file($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension)) {
                $this->params['error'] = true;
            }
            else {
                $this->begin_job();
            }
        }
        if (!$this->is_terminated() && !$this->is_in_error()) {
            for ($i=1; $i<=$max; $i++) {
                $this->job();
                if ($this->is_terminated()) {
                    break;
                }
            }
        }
        if ($this->is_terminated()) {
            $this->end_job();
            Logger::notice('Export Manager', null, $this->params['init']['station_id'], $this->params['init']['station_name'], null, null, null, 'Data export terminated.');
        }
        if ($this->params['todo'] > 0) {
            $this->set_progress(100 * $this->params['done'] / $this->params['todo']);
        }
        else {
            $this->set_progress(100);
        }
    }

}