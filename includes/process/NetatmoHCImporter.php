<?php

namespace WeatherStation\Process;

/**
 * A process to import old data from a Netatmo station.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class NetatmoHCImporter extends NetatmoImporter {


    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return lws__('Netatmo "Healthy Home Coach" importer', 'live-weather-station');
        }
        else {
            return 'Netatmo "Healthy Home Coach" importer';
        }
    }

    /**
     * Verify if the station has a computer.
     *
     * @return boolean True if the station has a computer. False otherwise.
     * @since 3.7.0
     */
    protected function has_computer() {
        return false;
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return lws__('Importing old data from a Netatmo "Healthy Home Coach" station.', 'live-weather-station');
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.7.0
     */
    protected function message() {
        $result = sprintf(lws__('Here are the details of importing old data from the Healthy Home Coach "%s":', 'live-weather-station'), $this->params['init']['station_name']) . "\r\n";
        foreach ($this->params['summary'] as $module) {
            if ($module['measurements'] === 0 || $module['days_done'] === 0) {
                $result .= '     - ' . sprintf(lws__('"%s": no measurements.', 'live-weather-station'), $module['name']) . "\r\n";
            }
            else {
                $result .= '     - ' . sprintf(lws__('"%s": %s measurements spread over %s days.', 'live-weather-station'), $module['name'], $module['measurements'], $module['days_done']) . "\r\n";
            }
        }
        $result .= "\r\n" . sprintf(lws__('These measurements were compiled in %s.', 'live-weather-station'), $this->get_age_hours_from_seconds($this->exectime)) . ' ';
        $result .= "\r\n" . sprintf(lws__('Historical data has been updated and is now usable in %s controls.', 'live-weather-station'), LWS_PLUGIN_NAME);
        return $result;
    }
}