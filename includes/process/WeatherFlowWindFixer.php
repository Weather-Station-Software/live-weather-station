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
class WeatherFlowWindFixer extends Process {

    use Query;


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.8.0
     */
    protected function uuid() {
        return 'e89e129f-7ba1-406a-9b96-ffc8687ad7de';
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
            return __('WeatherFlow wind fixer', 'live-weather-station');
        }
        else {
            return 'WeatherFlow wind fixer';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.8.0
     */
    protected function description() {
        return sprintf(__('This fix allows %s to correctly handle current, daily and historical wind & gust strength with WeatherFlow stations.', 'live-weather-station'), LWS_PLUGIN_NAME);
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
        return 10;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.8.0
     */
    protected function is_needed() {
        if (!(bool)get_option('live_weather_station_collect_history')) {
            return false;
        }
        if (count($this->get_all_wflw_id_stations()) === 0) {
            return false;
        }
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.8.0
     */
    protected function is_terminated(){
        return (count($this->params['stations']['todo']) === 0);
    }

    /**
     * Verify if process is in error.
     *
     * @return boolean True if the process is is in error. False otherwise.
     * @since 3.7.0
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
        $this->params['stations'] = array();
        $this->params['stations']['todo'] = array();
        $this->params['stations']['done'] = array();
        foreach ($this->get_all_wflw_id_stations() as $station) {
            $this->params['stations']['todo'][$station['station_id']] = $station['station_type'];
        }
    }

    /**
     * Run the process.
     *
     * @since 3.8.0
     */
    protected function run_core(){
        if (count($this->params['stations']['todo']) > 0) {
            $station_type = reset($this->params['stations']['todo']);
            $station_id = key($this->params['stations']['todo']);
            try {
                $this->fix($station_id, $station_type);
                unset($this->params['stations']['todo'][$station_id]);
                $this->params['stations']['done'][$station_id] = $station_type;
                $this->set_progress(100 * count($this->params['stations']['done']) / (count($this->params['stations']['todo']) + count($this->params['stations']['done'])));
            }
            catch (\Exception $ex) {
                Logger::error($this->bp_facility, null, null, null, null, null, 999, 'Error while running background process {' . $this->uuid() . '}. Message: ' . $ex->getMessage());
            }
        }
    }

    /**
     * Fix wind & gust strength fields for a table.
     *
     * @param string $station_id The station ID.
     * @param string $table_name The table where to fix.
     * @since 3.8.0
     */
    private function fix_table($station_id, $table_name) {
        global $wpdb;
        $sql = "UPDATE " . $wpdb->prefix . $table_name . " SET measure_value=measure_value * 3.6 WHERE device_id='" . $station_id . "' AND module_type ='NAModule2' AND (measure_type='windstrength' OR measure_type='guststrength')";
        $wpdb->query($sql);
    }

    /**
     * Fix wind & gust strength for a station.
     *
     * @param string $station_id The station ID.
     * @param integer $station_type The station type.
     * @since 3.8.0
     */
    private function fix($station_id, $station_type) {
        if ($station_type != LWS_WFLW_SID) {
            return;
        }
        // DAILY DATA
        $this->fix_table($station_id, self::live_weather_station_histo_daily_table());
        // HISTORICAL DATA
        $this->fix_table($station_id, self::live_weather_station_histo_yearly_table());
    }

}