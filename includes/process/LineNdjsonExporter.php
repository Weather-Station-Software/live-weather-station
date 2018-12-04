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
class LineNdjsonExporter extends LineExporter {

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
            return __('ND-JSON exporter', 'live-weather-station');
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
        return __('Exporting historical data from a weather station as a ND-JSON file.', 'live-weather-station');
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
        unset ($line['module_name']);
        FS::add_file_line($this->fullfilename, wp_json_encode($line));
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