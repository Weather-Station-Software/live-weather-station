<?php

namespace WeatherStation\Process;

/**
 * A process to export old data as TSV file.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class LineTsvExporter extends LineXsvExporter {

    protected $extension = 'tsv';
    protected $delimiter = "\t";

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('TSV exporter', 'live-weather-station');
        }
        else {
            return 'TSV exporter';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return __('Exporting historical data from a weather station as a TSV file.', 'live-weather-station');
    }

}