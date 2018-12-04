<?php

namespace WeatherStation\Process;

/**
 * A process to export old data as CSV file.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class LineCsvExporter extends LineXsvExporter {

    protected $extension = 'csv';
    protected $delimiter = ',';

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('CSV exporter', 'live-weather-station');
        }
        else {
            return 'CSV exporter';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return __('Exporting historical data from a weather station as a CSV file.', 'live-weather-station');
    }

}