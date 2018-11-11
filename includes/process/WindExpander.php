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
 * @since 3.6.0
 */
class WindExpander extends Process {

    use Query;


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.6.0
     */
    protected function uuid() {
        return '6dd8cc5e-226b-4eeb-b81c-e2f22d144707';
    }

    /**
     * Get the execution mode of the process.
     * Can be :
     *   - pause: can be restarted in the same cycle
     *   - schedule: must wait next cycle to be restarted
     *
     * @return string The execution mode of the process.
     * @since 3.6.0
     */
    protected function execution_mode() {
        return $this->state_pause;
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.6.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return __('Wind angle / Wind source expander', 'live-weather-station');
        }
        else {
            return 'Wind angle / Wind source expander';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.6.0
     */
    protected function description() {
        return sprintf(__('This fix allows %s to handle daily and historical wind angle / wind source measurements for all types of stations.', 'live-weather-station'), LWS_PLUGIN_NAME);
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.6.0
     */
    protected function message() {
        return '';
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.6.0
     */
    protected function priority(){
        return 10;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.6.0
     */
    protected function is_needed() {
        if (!(bool)get_option('live_weather_station_collect_history')) {
            return false;
        }
        if (count($this->get_stations_informations()) === 0) {
            return false;
        }
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.6.0
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
     * @since 3.6.0
     */
    protected function init_core(){
        $this->params['stations'] = array();
        $this->params['stations']['todo'] = array();
        $this->params['stations']['done'] = array();
        foreach ($this->get_stations_informations() as $station) {
            $this->params['stations']['todo'][$station['station_id']] = $station['station_type'];
        }
    }

    /**
     * Run the process.
     *
     * @since 3.6.0
     */
    protected function run_core(){
        if (count($this->params['stations']['todo']) > 0) {
            $station_type = reset($this->params['stations']['todo']);
            $station_id = key($this->params['stations']['todo']);
            try {
                $this->expand($station_id, $station_type);
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
     * Add wind sources field to a table.
     *
     * @param string $station_id The station ID.
     * @param string $table_name The table where to add.
     * @param array $fields The fields to convert.
     * @param boolean $switch Are the values to be switched?
     * @since 3.6.0
     */
    private function add_source($station_id, $table_name, $fields, $switch) {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . $table_name . " WHERE device_id='" . $station_id . "' AND measure_type IN (" . implode(',', $fields).")";
        $query = $wpdb->get_results($sql, ARRAY_A);
        if (is_array($query) && !empty($query)) {
            foreach ($query as &$row) {
                $wind = $row['measure_value'];
                $new_wind = round(fmod($wind + 180, 360), strlen(strrchr($wind, '.')) -1);
                if (array_key_exists('measure_set', $row)) {
                    if ($row['measure_set'] === 'dev') {
                        $new_wind = $wind;
                    }
                }
                if ($switch) {
                    $row['measure_value'] = $new_wind;
                    self::insert_update_table($table_name, $row);
                    $row['measure_type'] = str_replace('angle', 'direction', $row['measure_type']);
                    $row['measure_value'] = $wind;
                    self::insert_update_table($table_name, $row);
                }
                else {
                    $row['measure_type'] = str_replace('angle', 'direction', $row['measure_type']);
                    $row['measure_value'] = $new_wind;
                    self::insert_update_table($table_name, $row);
                }
            }
        }
    }

    /**
     * Expand a station.
     *
     * @param string $station_id The station ID.
     * @param integer $station_type The station type.
     * @since 3.6.0
     */
    private function expand($station_id, $station_type) {
        $switch = false;
        if ($station_type === LWS_PIOU_SID) {
            $switch = true;
        }
        // DAILY DATA
        $fields = array('\'windangle\'', '\'gustangle\'');
        $this->add_source($station_id, self::live_weather_station_histo_daily_table(), $fields, $switch);
        // HISTORICAL DATA
        $fields = array('\'windangle\'', '\'gustangle\'');
        $this->add_source($station_id, self::live_weather_station_histo_yearly_table(), $fields, $switch);
    }

}