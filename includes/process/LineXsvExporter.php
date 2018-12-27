<?php

namespace WeatherStation\Process;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\Data\Output ;

/**
 * A process to export old data as xSV file.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
abstract class LineXsvExporter extends LineExporter {

    use Output;

    protected $delimiter = ',';

    /**
     * Get the url of the process doc.
     *
     * @return string The url of the process doc.
     * @since 3.6.0
     */
    protected function url() {
        return 'https://weather.station.software/handbook/background-processes/xsv-exporter/';
    }

    /**
     * Begin the main process job.
     *
     * @since 3.7.0
     */
    protected function begin_job() {
        $h = array();
        $h[] = __('Date', 'live-weather-station');
        $h[] = __('Module', 'live-weather-station');
        $h[] = __('Measure', 'live-weather-station');
        $h[] = __('Unit', 'live-weather-station');
        $h[] = __('Average', 'live-weather-station');
        $h[] = __('Minimum', 'live-weather-station');
        $h[] = __('Maximum', 'live-weather-station');
        $h[] = __('Median', 'live-weather-station');
        $h[] = __('Standard Deviation', 'live-weather-station');
        $h[] = __('Count', 'live-weather-station');
        $h[] = __('Hourly Maximum', 'live-weather-station');
        $h[] = __('Prevalent Value', 'live-weather-station');
        FS::write_file_line($this->fullfilename, implode($this->delimiter, $h));
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
        $v[] = str_replace($this->delimiter, '', $line['timestamp']);
        $v[] = str_replace($this->delimiter, '', $line['module_name']);
        $v[] = str_replace($this->delimiter, '', $this->get_measurement_type($line['measure_type'], false, $line['module_type']));
        $unit = $this->output_unit($line['measure_type'], $line['module_type'])['unit'];
        if ($unit === '') {
            $unit = '';
        }
        $v[] = str_replace($this->delimiter, '', $unit);
        foreach ($set as $s) {
            if (array_key_exists($s, $line)) {
                $v[] = str_replace($this->delimiter, '_', $this->output_value($line[$s], $line['measure_type'], false, false, $line['module_type']));
            }
            else {
                $v[] = '';
            }
        }
        FS::add_file_line($this->fullfilename, implode($this->delimiter, $v));
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