<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;

/**
 * A fix to lowercase all IDs.
 *
 * @package Includes\Process
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.3
 */
class IdentifierLowercaser extends Process {

    use Query;


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.6.3
     */
    protected function uuid() {
        return '0f2f2b5b-2ea1-46d1-a8b3-c8664baf859e';
    }

    /**
     * Get the execution mode of the process.
     * Can be :
     *   - pause: can be restarted in the same cycle
     *   - schedule: must wait next cycle to be restarted
     *
     * @return string The execution mode of the process.
     * @since 3.6.3
     */
    protected function execution_mode() {
        return $this->state_pause;
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.6.3
     */
    protected function name($translated=true) {
        return 'FIX#0002';
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.6.3
     */
    protected function description() {
        return sprintf(__('No description.', 'live-weather-station'), LWS_PLUGIN_NAME);
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.6.3
     */
    protected function message() {
        return '';
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.6.3
     */
    protected function priority(){
        return 0;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.6.3
     */
    protected function is_needed() {
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.6.3
     */
    protected function is_terminated(){
        return ($this->progress == 100);
    }

    /**
     * Verify if process is in error.
     *
     * @return boolean True if the process is in error. False otherwise.
     * @since 3.7.0
     */
    protected function is_in_error(){
        return false;
    }

    /**
     * Init the process.
     *
     * @since 3.6.3
     */
    protected function init_core(){
        $this->silent = true;
    }

    /**
     * Run the process.
     *
     * @since 3.6.3
     */
    protected function run_core(){
        self::fields_lower_case(array('station_id'), self::live_weather_station_stations_table());
        self::fields_lower_case(array('device_id', 'module_id'), self::live_weather_station_measurements_table());
        self::fields_lower_case(array('device_id', 'module_id'), self::live_weather_station_histo_daily_table());
        self::fields_lower_case(array('device_id', 'module_id'), self::live_weather_station_histo_yearly_table());
        self::fields_lower_case(array('device_id', 'module_id'), self::live_weather_station_media_table());
        self::fields_lower_case(array('device_id', 'module_id'), self::live_weather_station_module_detail_table());
        self::fields_lower_case(array('device_id', 'module_id'), self::live_weather_station_log_table());
        $this->set_progress(100);
        $this->silent = true;
    }

}