<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;

/**
 * A process to expand wind measurements for existing stations.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class SunshineAggregator extends Process {

    use Query;


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.8.0
     */
    protected function uuid() {
        return '2a2d5803-6cfa-4247-b7d1-f25fe2fada98';
    }

    /**
     * Get the execution mode of the process.
     * Can be :
     *   - pause: can be restarted in the same cycle
     *   - schedule: must wait next cycle to be restarted
     *
     * @return string The execution mode of the process.
     * @since 3.8.0
     */
    protected function execution_mode() {
        return $this->state_pause;
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.8.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('Sunshine aggregator', 'live-weather-station');
        }
        else {
            return 'Sunshine aggregator';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.8.0
     */
    protected function description() {
        return sprintf(__('This fix modifies the way %s handle and store historical sunshine durations.', 'live-weather-station'), LWS_PLUGIN_NAME);
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.8.0
     */
    protected function message() {
        return '';
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.8.0
     */
    protected function priority(){
        return 0;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.8.0
     */
    protected function is_needed() {
        return $this->is_historized('sunshine');
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.8.0
     */
    protected function is_terminated(){
        return ($this->progress == 100);
    }

    /**
     * Verify if process is in error.
     *
     * @return boolean True if the process is in error. False otherwise.
     * @since 3.8.0
     */
    protected function is_in_error(){
        return false;
    }

    /**
     * Init the process.
     *
     * @since 3.8.0
     */
    protected function init_core(){
        $this->progress = 0;
    }

    /**
     * Run the process.
     *
     * @since 3.8.0
     */
    protected function run_core(){
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        $sql = "UPDATE " . $table_name . " SET `measure_set`='agg' WHERE `measure_type`='sunshine' AND `measure_set`='max'";
        $wpdb->query($sql);
        $sql = "DELETE FROM " . $table_name . " WHERE `measure_type`='sunshine' AND `measure_set`<>'agg'";
        $wpdb->query($sql);
        $this->set_progress(100);
    }



}