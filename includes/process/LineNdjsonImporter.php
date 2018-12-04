<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;
use WeatherStation\Data\History\Builder;
use WeatherStation\System\Logs\Logger;

/**
 * A process to import old data from a ND-JSON file.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class LineNdjsonImporter extends LineImporter {

    use Query, DateTimeConversion;

    protected $extension = 'ndjson';

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('ND-JSON importer', 'live-weather-station');
        }
        else {
            return 'ND-JSON importer';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return __('Importing historical data from a ND-JSON file.', 'live-weather-station');
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
    protected function transform($args, $start_date, $end_date) {
        $result = array();
        try {
            $val = json_decode($args['values'], true);
            if (is_array($val)) {
                if (array_key_exists('timestamp', $val) &&
                    array_key_exists('module_type', $val) &&
                    array_key_exists('measure_type', $val)) {
                    if (in_array($val['measure_type'], Builder::$data_to_historize)) {
                        $timestamp = $val['timestamp'];
                        if (self::verify_mysql_date($timestamp)) {
                            if (self::mysql_is_ordered($args['start_date'], $timestamp) && self::mysql_is_ordered($timestamp, $args['end_date'])) {
                                $device_id = $args['device_id'];
                                $module_type = $val['module_type'];
                                $measure_type = $val['measure_type'];
                                $module_id = null;
                                if (in_array($val['module_id'], $args['white_list'])) {
                                    $module_id = $val['module_id'];
                                }
                                elseif (is_array($args['module']) && count($args['module']) > 0 && array_key_exists($val['module_type'], $args['module'])) {
                                    $module_id = $args['module'][$val['module_type']];
                                }
                                else {
                                    // create module?
                                }
                                if (isset($module_id)) {
                                    for ($i=0; $i<count($this->set); $i++) {
                                        if (array_key_exists($this->set[$i], $val)) {
                                            $v = array();
                                            $v['timestamp'] = $timestamp;
                                            $v['device_id'] = $device_id;
                                            $v['module_id'] = $module_id;
                                            $v['module_type'] = $module_type;
                                            $v['measure_type'] = $measure_type;
                                            $v['measure_set'] = $this->set[$i];
                                            $v['measure_value'] = $val[$this->set[$i]];
                                            $result[] = $v;
                                        }
                                    }
                                }
                                else {
                                    //error_log('Excluded module: ' . $module_type . ' / ' . $measure_type);
                                }
                            }
                            else {
                                //error_log('Excluded date: ' . $timestamp);
                            }
                        }
                        else {
                            Logger::error('Import Manager', null, null, null, null, null, 100, 'Inconsistent date format in source file: ' . $timestamp);
                            throw new \Exception('Inconsistent date format in source file.');
                        }
                    }
                }
                else {
                    Logger::error('Import Manager', null, null, null, null, null, 101, 'Missing key fields in the source file.');
                    throw new \Exception('Missing key fields in the source file.');
                }
            }
        }
        catch (\Exception $ex) {
            return array();
        }
        return $result;
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
        // $this->params['init']['force']      // force overwriting
        // $this->params['init']['uuid']       // file uuid

        if (!self::mysql_is_ordered($this->params['init']['start_date'], $this->params['init']['end_date'])) {
            $end = $this->params['init']['start_date'];
            $start = $this->params['init']['end_date'];
            $this->params['init']['start_date'] = $start;
            $this->params['init']['end_date'] = $end;
        }

        $station = $this->get_station_informations_by_station_id($this->params['init']['station_id']);
        $this->params['init']['station_name'] = $station['station_name'];
        $this->params['init']['station_type'] = $station['station_type'];

        $this->params['error'] = true;
        $this->params['todo'] = 0;
        $this->params['current'] = 0;
        $this->params['file'] = '';
        $this->params['station_name'] = '';
        $this->params['from'] = '';
        $this->params['to'] = '';
        $file = FS::find_valid($this->params['init']['uuid'], array($this->extension));
        if (count($file) > 0) {
            if (FS::construct_full_file_name(file_exists($file['file']))) {
                $this->params['file'] = $file['file'];
                $this->params['todo'] = $file['lines'] - 1;
                $this->params['error'] = false;
                $this->params['station_name'] = $file['station'];
                $this->params['from'] = $file['from'];
                $this->params['to'] = $file['to'];
            }
        }
    }

}