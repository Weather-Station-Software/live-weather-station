<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Unit\Conversion;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;

/**
 * A process to import old data from a Netatmo station.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class NetatmoImporter extends Process {

    use Conversion, Query, DateTimeConversion;

    private $station_id = '00:00:00:00:00:00';
    private $station_name = '';

    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.7.0
     */
    protected function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
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
        return $this->state_pause;
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.7.0
     */
    protected function name($translated=true) {
        return lws__('Netatmo importer', 'live-weather-station');
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.7.0
     */
    protected function description() {
        return lws__('Importing old data from a Netatmo station.', $this->station_name);
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.7.0
     */
    protected function message() {
        $result = sprintf(lws__('Here are the details of importing old data from the "%s" station:', 'live-weather-station'), $this->station_name) . "\r\n";
        foreach ($this->params['summary'] as $module) {
            $result .= '  - ' . sprintf(lws__('"%s": %s measurements spread over %s days.', 'live-weather-station'), $module['name'], $module['measurements'], $module['days']) . "\r\n";
        }
        $result .= "\r\n" . sprintf(lws__('These measurements were compiled in %s.', 'live-weather-station'), $this->get_age_hours_from_seconds($this->exectime)) . ' ';
        $result .= "\r\n" . lws__('Historical data has been updated and is now usable.', 'live-weather-station');
        return $result;
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
        if (!(bool)get_option('live_weather_station_collect_history')) {
            return false;
        }
        if (!(bool)get_option('live_weather_station_build_history')) {
            return false;
        }
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.7.0
     */
    protected function is_terminated(){
        return (count($this->params['stations']['todo']) === 0);
    }

    /**
     * Init the process.
     *
     * @since 3.7.0
     */
    protected function init_core(){
        //$t






        $this->params['stations'] = array();
        $this->params['stations']['todo'] = array();
        $this->params['stations']['done'] = array();
        foreach ($this->get_stations_informations() as $station) {
            $this->params['stations']['todo'][$station['station_id']] = array($station['station_type'], $station['loc_altitude']);
        }

        $this->params['summary'] = array();
    }

    /**
     * Run the process.
     *
     * @since 3.7.0
     */
    protected function run_core(){
        if (count($this->params['stations']['todo']) > 0) {
            $station_spec = reset($this->params['stations']['todo']);
            $station_id = key($this->params['stations']['todo']);
            try {
                //$this->expand($station_id, $station_spec);
                unset($this->params['stations']['todo'][$station_id]);
                $this->params['stations']['done'][$station_id] = $station_spec;
                $this->set_progress(100 * count($this->params['stations']['done']) / (count($this->params['stations']['todo']) + count($this->params['stations']['done'])));
            }
            catch (\Exception $ex) {
                Logger::error($this->facility, null, null, null, null, null, 999, 'Error while running background process {' . $this->uuid() . '}. Message: ' . $ex->getMessage());
            }
        }
    }

}