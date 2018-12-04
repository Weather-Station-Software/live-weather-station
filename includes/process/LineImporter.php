<?php

namespace WeatherStation\Process;
use WeatherStation\Data\History\Builder;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;
use WeatherStation\System\Device\Manager as DeviceManager;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;

/**
 * A process to import old data from a line file.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
abstract class LineImporter extends Process {

    use DateTimeConversion, Query;

    protected $facility = 'Import Manager';
    protected $extension = 'ukn';
    protected $batchsize = 1000;
    protected $set = array('avg', 'min', 'max', 'med', 'dev', 'agg', 'maxhr', 'dom');
    protected $auto = array('NAMain', 'NAModule1', 'NAModule2', 'NAModule3', 'NAModule5', 'NAModule6', 'NAModule7', 'NAComputed', 'NACurrent', 'NAPollution');
    protected $white = array(LWS_NETATMO_SID, LWS_NETATMOHC_SID, LWS_BSKY_SID, LWS_AMBT_SID);

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
        return 30;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.7.0
     */
    protected function is_needed() {
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.7.0
     */
    protected function is_terminated(){
        return ($this->params['todo'] <= $this->params['current']);
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
            $result = sprintf(__('Unable to import the specified file in the station "%s".', 'live-weather-station'), $this->params['init']['station_name']) . "\r\n";
            $result .= "\r\n" . sprintf(__('Check the events log to see what\'s going on: %s', 'live-weather-station'), lws_get_admin_page_url('lws-events')) . "\r\n";
        }
        else {
            $result = sprintf(__('Historical data has been correctly imported in "%s" for the period from %s to %s.', 'live-weather-station'), $this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date']) . "\r\n";
        }
        return $result;
    }

    /**
     * Do the main process job.
     *
     * @param array $args The parameters of the sub-job.
     * @param string $start_date The start date of the import.
     * @param string $end_date The end date of the import.
     * @return array The transformed values, ready to be recorded.
     * @since 3.7.0
     */
    abstract protected function transform($args, $start_date, $end_date);


    /**
     * Do the main process job.
     *
     * @since 3.7.0
     */
    protected function main_job(){
        $args = array();
        $args['white_list'] = array();
        $args['device_id'] = $this->params['init']['station_id'];
        $args['start_date'] = $this->params['init']['start_date'];
        $args['end_date'] = $this->params['init']['end_date'];
        $args['module'] = array();
        $args['types'] = array();
        foreach (DeviceManager::get_modules_details($this->params['init']['station_id']) as $module) {
            if (in_array($module['module_type'], $this->auto)) {
                $args['module'][$module['module_type']] = $module['module_id'];
            }
            if (in_array($this->params['init']['station_type'], $this->white)) {
                $args['white_list'][] = $module['module_id'];
            }
        }

        try {
            $file = new \SplFileObject(FS::construct_full_file_name($this->params['file']));
            $file->seek($this->params['current']);
            for ($i=1; $i < $this->batchsize; $i++) {
                $args['values'] = $file->current();
                $values = $this->transform($args, $this->params['init']['start_date'], $this->params['init']['end_date']);
                foreach ($values as $v) {
                    if (array_key_exists('timestamp', $v) &&
                        array_key_exists('device_id', $v) &&
                        array_key_exists('module_id', $v) &&
                        array_key_exists('module_type', $v) &&
                        array_key_exists('measure_type', $v) &&
                        array_key_exists('measure_set', $v) &&
                        array_key_exists('measure_value', $v)) {
                        Builder::add_record($v['timestamp'], $v['device_id'], $v['module_id'], $v['module_type'], $v['measure_type'], $v['measure_set'], $v['measure_value'], $this->params['init']['force']);
                    }
                }
                $file->next();
                if ($file->eof()) {
                    break;
                }
            }
            $this->params['current'] = $this->params['current'] + $i;
        }
        catch (\Exception $ex) {
            $this->params['error'] = true;
        }
        finally {
            $file = null;
        }
    }

    /**
     * Run the process.
     *
     * @since 3.7.0
     */
    protected function run_core(){
        $max = 1;
        for ($i=1; $i<20; $i++) {
            if ((int)round(ini_get('max_execution_time') > $i*15)) {
                $max += 1;
            }
        }
        if (!$this->is_terminated() && !$this->is_in_error()) {
            for ($i=1; $i<=$max; $i++) {
                $this->main_job();
                if ($this->is_terminated() || $this->is_in_error()) {
                    break;
                }
            }
        }
        $this->update_oldest_data($this->params['init']['station_id']);
        if ($this->params['todo'] > 0) {
            $this->set_progress(100 * $this->params['current'] / $this->params['todo']);
        }
        else {
            $this->set_progress(100);
        }
        if ($this->is_terminated()) {
            Logger::notice('Import Manager', null, $this->params['init']['station_id'], $this->params['init']['station_name'], null, null, null, 'Data import terminated.');
        }
    }

}