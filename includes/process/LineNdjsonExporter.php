<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;

/**
 * A process to export old data as ND-JSON file.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class LineCsvExporter extends LineExporter {

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
            return lws__('ND-JSON exporter', 'live-weather-station');
        }
        else {
            return 'ND-JSON exporter';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return lws__('Exporting historical data from a weather station as a ND-JSON file.', 'live-weather-station');
    }

    /**
     * Begin the main process job.
     *
     * @since 3.7.0
     */
    protected function begin_job() {
        // Nothing to do
    }

    /**
     * Do the main process job for each line.
     *
     * @param array $line The line to process.
     * @since 3.7.0
     */
    protected function do_job($line) {
        $set = array('avg', 'min', 'max', 'med', 'dev', 'agg', 'maxhr', 'dom');
        $v = array();
        $v[] = str_replace(',', '', $line['timestamp']);
        $v[] = str_replace(',', '', $line['module_name']);
        $v[] = str_replace(',', '', $this->get_measurement_type($line['measure_type'], false, $line['module_type']));
        $v[] = str_replace(',', '', $this->output_unit($line['measure_type'], $line['module_type'])['unit']);
        foreach ($set as $s) {
            if (array_key_exists($s, $line)) {
                $v[] = str_replace(',', '_', $this->output_value($line[$s], $line['measure_type'], false, false, $line['module_type']));
            }
            else {
                $v[] = '';
            }
        }
        //FS::add_file_line($this->params['filename'], implode(',', $v));
    }

    /**
     * End the main process job.
     *
     * @since 3.7.0
     */
    protected function end_job()  {
        // Nothing to do
    }

}