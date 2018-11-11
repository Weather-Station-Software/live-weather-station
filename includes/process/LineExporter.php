<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;

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

    protected $filename = null;
    protected $extension = 'txt';


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
        error_log(print_r($query, true));
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
            $result = sprintf(lws__('Unable to create the file named %s in the directory "%s".', 'live-weather-station'), FS::get_file_name($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension), FS::get_root_name()) . "\r\n";
            $result .= "\r\n" . sprintf(lws__('Check %s to see what\'s going on.', 'live-weather-station'), '<a href="' . lws_get_admin_page_url('lws-events') . '">' . sprintf(lws__('the %s events log', 'live-weather-station'), LWS_PLUGIN_NAME) . '</a>');
        }
        else {
            $fileurl = FS::get_full_file_url($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension);
            $result = sprintf(lws__('The historical data of "%s" has been correctly exported for the period from %s to %s.', 'live-weather-station'), $this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date']) . "\r\n";
            $result .= "\r\n" . sprintf(lws__('The file is now ready to download. It will be keeped on your server for %s days.', 'live-weather-station'), $fileurl) . "\r\n";
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
     * @param array $line The line to process.
     * @since 3.7.0
     */
    protected abstract function do_job($line);

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

        $station = $this->get_station_informations_by_station_id($this->params['init']['station_id']);
        $this->params['init']['station_name'] = $station['station_name'];
        $this->params['init']['loc_timezone'] = $station['loc_timezone'];
        $old_dates = array();
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
        $this->params['filename'] = FS::file_for_write($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension);
        $this->params['error'] = $this->params['filename'] === false;
        $this->params['todo'] = $count;
        $this->params['done'] = 0;
        $this->params['start_date'] = $this->params['init']['start_date'];
        $this->params['end_date'] = $this->params['init']['end_date'];
    }

    /**
     * Run the process.
     *
     * @since 3.7.0
     */
    protected function run_core(){
        $max = 1;
        /*for ($i=1; $i<8; $i++) {
            if ((int)round(ini_get('max_execution_time') > $i*40)) {
                $max += 1;
            }
        }*/
        if (!file_exists($this->params['filename'])) {
            if (!FS::create_file($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension)) {
                $this->params['error'] = true;
            }
            else {
                $this->begin_job();
            }
        }
        if (!$this->is_terminated() && !$this->is_in_error()) {
            for ($i=1; $i<=$max; $i++) {
                if (count($this->params['todo']) > 0) {
                    $this->job();
                }
            }
        }
        if ($this->is_terminated()) {
            $this->end_job();
        }
    }

}