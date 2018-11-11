<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Unit\Conversion;

/**
 * A process to expand wind measurements for existing stations.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.3
 */
class PressureExpander extends Process {

    use Conversion, Query;


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.6.3
     */
    protected function uuid() {
        return 'da0d5263-1824-4b55-99b9-f474aba831ba';
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
        if ($translated) {
            return __('Pressure expander', 'live-weather-station');
        }
        else {
            return 'Pressure expander';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.6.3
     */
    protected function description() {
        return sprintf(__('This fix allows %s to handle daily and historical barometric and atmospheric pressures measurements for all types of stations.', 'live-weather-station'), LWS_PLUGIN_NAME);
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
        return 10;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.6.3
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
     * @since 3.6.3
     */
    protected function is_terminated(){
        return (count($this->params['stations']['todo']) === 0);
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
        $this->params['stations'] = array();
        $this->params['stations']['todo'] = array();
        $this->params['stations']['done'] = array();
        foreach ($this->get_stations_informations() as $station) {
            $this->params['stations']['todo'][$station['station_id']] = array($station['station_type'], $station['loc_altitude']);
        }
    }

    /**
     * Run the process.
     *
     * @since 3.6.3
     */
    protected function run_core(){
        if (count($this->params['stations']['todo']) > 0) {
            $station_spec = reset($this->params['stations']['todo']);
            $station_id = key($this->params['stations']['todo']);
            try {
                $this->expand($station_id, $station_spec);
                unset($this->params['stations']['todo'][$station_id]);
                $this->params['stations']['done'][$station_id] = $station_spec;
                $this->set_progress(100 * count($this->params['stations']['done']) / (count($this->params['stations']['todo']) + count($this->params['stations']['done'])));
            }
            catch (\Exception $ex) {
                Logger::error($this->bp_facility, null, null, null, null, null, 999, 'Error while running background process {' . $this->uuid() . '}. Message: ' . $ex->getMessage());
            }
        }
    }

    /**
     * Process all historical data.
     *
     * @param string $station_id The station ID.
     * @param string $table_name The table where to add.
     * @param integer $altitude The altitude of the station.
     * @param boolean $switch Are the values to be switched?
     * @since 3.6.3
     */
    private function process_histo_pressure($station_id, $table_name, $altitude, $switch) {
        global $wpdb;
        $sql = "SELECT `timestamp`, avg(`measure_value`) as temperature FROM " . $wpdb->prefix . $table_name . " WHERE `device_id` = '" . $station_id . "' AND `measure_type` = 'temperature' AND `measure_set` = 'avg' AND (`module_type`='NAModule1' OR`module_type`='NACurrent') GROUP BY `timestamp` ORDER BY `timestamp` ASC";
        $query = $wpdb->get_results($sql, ARRAY_A);
        $temps = array();
        if (is_array($query) && !empty($query)) {
            foreach ($query as &$row) {
                $temps[$row['timestamp']] = $row['temperature'];
            }
        }
        $fields = array('\'pressure\'', '\'air_density\'', '\'specific_enthalpy\'', '\'potential_temperature\'', '\'equivalent_potential_temperature\'' );
        $sql = "SELECT * FROM " . $wpdb->prefix . $table_name . " WHERE device_id='" . $station_id . "' AND measure_type IN (" . implode(',', $fields).")";
        $query = $wpdb->get_results($sql, ARRAY_A);
        if (is_array($query) && !empty($query)) {
            foreach ($query as &$row) {
                if ($row['measure_type'] === 'pressure') {
                    $temperature = 15.0;
                    if (array_key_exists($row['timestamp'], $temps)) {
                        $temperature = (float)$temps[$row['timestamp']];
                    }
                    if ($switch || $row['module_type'] === 'NACurrent') {
                        $mslp = $row['measure_value'];
                        $baro = $this->convert_from_mslp_to_baro($mslp, (float)$altitude, $temperature);
                    }
                    else {
                        $baro = $row['measure_value'];
                        $mslp = $this->convert_from_baro_to_mslp($baro, (float)$altitude, $temperature);
                    }
                    $row['measure_type'] = 'pressure';
                    $row['measure_value'] = sprintf('%.14F', round($baro, 14));
                    self::insert_update_table($table_name, $row);
                    $row['measure_type'] = 'pressure_sl';
                    $row['measure_value'] = sprintf('%.14F', round($mslp, 14));
                    self::insert_update_table($table_name, $row);
                }
                if ($row['measure_type'] === 'air_density' || $row['measure_type'] === 'specific_enthalpy' || $row['measure_type'] === 'potential_temperature' || $row['measure_type'] === 'equivalent_potential_temperature') {
                    if ($row['measure_type'] === 'air_density') {
                        $coef = (float)(1 - $altitude * 0.00011);
                    }
                    else {
                        $coef = (float)(1 + $altitude * 0.000065);
                    }
                    $row['measure_value'] = sprintf('%.14F', round($row['measure_value'] * $coef, 14));
                    self::insert_update_table($table_name, $row);
                }
            }
        }
    }

    /**
     * Process all daily data.
     *
     * @param string $station_id The station ID.
     * @param string $table_name The table where to add.
     * @param integer $altitude The altitude of the station.
     * @param boolean $switch Are the values to be switched?
     * @since 3.6.3
     */
    private function process_daily_pressure($station_id, $table_name, $altitude, $switch) {
        global $wpdb;
        $sql = "SELECT avg(`measure_value`) as temperature FROM " . $wpdb->prefix . $table_name . " WHERE `device_id` = '" . $station_id . "' AND `measure_type` = 'temperature' AND (`module_type`='NAModule1' OR`module_type`='NACurrent')";
        $query = $wpdb->get_results($sql, ARRAY_A);
        $temperature = 15.0;
        if (is_array($query) && !empty($query)) {
            $temperature = $query[0]['temperature'];
        }
        $fields = array('\'pressure\'', '\'air_density\'', '\'specific_enthalpy\'', '\'potential_temperature\'', '\'equivalent_potential_temperature\'' );
        $sql = "SELECT * FROM " . $wpdb->prefix . $table_name . " WHERE device_id='" . $station_id . "' AND measure_type IN (" . implode(',', $fields).")";
        $query = $wpdb->get_results($sql, ARRAY_A);
        if (is_array($query) && !empty($query)) {
            foreach ($query as &$row) {
                if ($row['measure_type'] === 'pressure') {
                    if ($switch || $row['module_type'] === 'NACurrent') {
                        $mslp = $row['measure_value'];
                        $baro = $this->convert_from_mslp_to_baro($mslp, (float)$altitude, $temperature);
                    }
                    else {
                        $baro = $row['measure_value'];
                        $mslp = $this->convert_from_baro_to_mslp($baro, (float)$altitude, $temperature);
                    }
                    $row['measure_type'] = 'pressure';
                    $row['measure_value'] = sprintf('%.10F', round($baro, 10));
                    self::insert_update_table($table_name, $row);
                    $row['measure_type'] = 'pressure_sl';
                    $row['measure_value'] = sprintf('%.10F', round($mslp, 10));
                    self::insert_update_table($table_name, $row);
                }
                if ($row['measure_type'] === 'air_density' || $row['measure_type'] === 'specific_enthalpy' || $row['measure_type'] === 'potential_temperature' || $row['measure_type'] === 'equivalent_potential_temperature') {
                    if ($row['measure_type'] === 'air_density') {
                        $coef = (float)(1 - $altitude * 0.00011);
                    }
                    else {
                        $coef = (float)(1 + $altitude * 0.000065);
                    }
                    $row['measure_value'] = sprintf('%.10F', round($row['measure_value'] * $coef, 10));
                    self::insert_update_table($table_name, $row);
                }
            }
        }
    }

    /**
     * Expand a station.
     *
     * @param string $station_id The station ID.
     * @param array $station_spec The station type and altitude.
     * @since 3.6.3
     */
    private function expand($station_id, $station_spec) {
        $switch = false;
        if ((integer)$station_spec[0] <= 7) { // All stations from LWS_NETATMO_SID to LWS_TXT_SID must be switched
            $switch = true;
        }

        // DAILY DATA
        $this->process_daily_pressure($station_id, self::live_weather_station_histo_daily_table(), (integer)$station_spec[1], $switch);

        // HISTORICAL DATA
        $this->process_histo_pressure($station_id, self::live_weather_station_histo_yearly_table(), (integer)$station_spec[1], $switch);
    }

}