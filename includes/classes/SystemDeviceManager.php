<?php

namespace WeatherStation\System\Device;

use WeatherStation\DB\Query;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\History\Builder as HistoryBuilder;
use WeatherStation\System\Cache\Cache;
use WeatherStation\Data\Type\Description;

/**
 * This class is responsible for device management.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

class Manager
{

    use Query;
    use Description;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Device Manager';
    private $stations = array();


    /**
     * Initialize the class and set its properties.
     *
     * @since 3.4.0
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Cron to execute once a day to execute the device management.
     *
     * @since 3.4.0
     */
    public function cron() {
        $cron_id = Watchdog::init_chrono(Watchdog::$device_management_name);
        $this->manage_netatmo();
        Watchdog::stop_chrono($cron_id);

        if (count($this->stations) > 0) {
            $cron_id = Watchdog::init_chrono(Watchdog::$history_build_name);
            $hb = new HistoryBuilder(LWS_PLUGIN_NAME, LWS_VERSION);
            foreach ($this->stations as $device_id) {
                $hb->build_for($this->get_station_informations_by_station_id($device_id));
            }
            Cache::flush_full(false);
            Watchdog::stop_chrono($cron_id);
        }
    }

    /**
     * Device manager for Netatmo.
     * For now manage add/remove of wind and rain gauge.
     *
     * @since 3.4.0
     */
    private function manage_netatmo() {
        $single_module = array('NAModule2', 'NAModule3');
        $stations = $this->get_all_netatmo_stations();
        foreach ($stations as $station) {
            $managed = array();
            foreach ($single_module as $module) {
                $managed[$module] = array();
                if ($m = $this->get_duplicate_data($station['station_id'], $module)) {
                    $managed[$module][] = $station['station_id'];
                    foreach ($m['old'] as $old) {
                        $this->delete_duplicate_data($station['station_id'], $old);
                    }
                }
                if ($m = $this->get_duplicate_data_histo($station['station_id'], $module, self::live_weather_station_histo_daily_table())) {
                    $managed[$module][] = $station['station_id'];
                    foreach ($m['old'] as $old) {
                        $this->rename_duplicate_data($station['station_id'], $m['new'], $old, self::live_weather_station_histo_daily_table());
                    }
                }
                if ($m = $this->get_duplicate_data_histo($station['station_id'], $module, self::live_weather_station_histo_yearly_table())) {
                    $managed[$module][] = $station['station_id'];
                    foreach ($m['old'] as $old) {
                        $this->rename_duplicate_data($station['station_id'], $m['new'], $old, self::live_weather_station_histo_yearly_table());
                    }
                }
            }
            foreach ($single_module as $module) {
                if (count($managed[$module]) > 0) {
                    if (!in_array($station['station_id'], $this->stations)) {
                        $this->stations[] = $station['station_id'];
                    }
                    Logger::warning($this->facility, $this->get_service_name(0), $station['station_id'], $station['station_name'], null, null, null, sprintf('The new %s has been fully integrated.', lcfirst($this->get_module_type($module))));
                }
            }
        }
    }

    /**
     * Get duplicate modules.
     *
     * @param string $device_id The station.
     * @param string $module The module type.
     * @param string $table The module type.
     * @return boolean|array The duplicated modules.
     * @since 3.4.0
     */
    private function get_duplicate_data_histo($device_id, $module, $table) {
        $result = array();
        $rows = array();
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $sql = "SELECT module_id, MAX(`timestamp`) as val FROM " . $table_name . " WHERE `device_id`='" . $device_id . "' AND `module_type`='" . $module . "' GROUP BY module_id;";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $rows[] = (array)$val;
            }
        } catch (\Exception $ex) {
            //
        }
        if (count($rows) > 1) {
            $time = 0;
            $new = '';
            foreach ($rows as $row) {
                if (strtotime($row['val']) > $time) {
                    if ($new != '') {
                        $result['old'][] = $new;
                    }
                    $time = strtotime($row['val']);
                    $new = $row['module_id'];
                }
                else {
                    $result['old'][] = $row['module_id'];
                }
            }
            $result['new'] = $new;
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * Get duplicate modules.
     *
     * @param string $device_id The station.
     * @param string $module The module type.
     * @return boolean|array The duplicated modules.
     * @since 3.4.0
     */
    private function get_duplicate_data($device_id, $module) {
        $result = array();
        $rows = array();
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "SELECT DISTINCT module_id, measure_value FROM " . $table_name . " WHERE `device_id`='" . $device_id . "' AND `module_type`='" . $module . "' AND `measure_type`='last_seen';";
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            foreach ($query_a as $val) {
                $rows[] = (array)$val;
            }
        } catch (\Exception $ex) {
            //
        }
        if (count($rows) > 1) {
            $time = 0;
            $new = '';
            foreach ($rows as $row) {
                if (strtotime($row['measure_value']) > $time) {
                    if ($new != '') {
                        $result['old'][] = $new;
                    }
                    $time = strtotime($row['measure_value']);
                    $new = $row['module_id'];
                }
                else {
                    $result['old'][] = $row['module_id'];
                }
            }
            $result['new'] = $new;
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * Delete one duplicate module.
     *
     * @param string $device_id The station.
     * @param string $old The module type.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function delete_duplicate_data($device_id, $old) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM " . $table_name . " WHERE `device_id`='" . $device_id . "' AND `module_id`='" . $old . "';";
        return $wpdb->query($sql);
    }

    /**
     * Rename one duplicate module.
     *
     * @param string $device_id The station.
     * @param string $new The new module id.
     * @param string $old The old module id.
     * @param string $table The module type.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function rename_duplicate_data($device_id, $new, $old, $table) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $sql = "UPDATE " . $table_name . " SET `module_id`='" . $new . "' WHERE `device_id`='" . $device_id . "' AND `module_id`='" . $old . "';";
        return $wpdb->query($sql);
    }

}