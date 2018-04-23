<?php

namespace WeatherStation\System\Data;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Device\Manager as DeviceManager;

/**
 * The class to manage data integrity.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.1
 */
class Data {

    use Storage;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Data Manager';

    private $ws_tables = array();

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.3.1
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        $this->ws_tables_construct();
    }

    /**
     * Get table name.
     *
     * @since 3.5.0
     */
    public function get_table_name($table){
        $result = __('Unknown table', 'live-weather-station');
        if (array_key_exists($table, $this->ws_tables)) {
            $result = $this->ws_tables[$table]['name'];
        }
        return $result;
    }

    /**
     * Get table name.
     *
     * @since 3.5.0
     */
    public function get_table_item($table){
        $result = _n('item', 'items', 20, 'live-weather-station');
        if (array_key_exists($table, $this->ws_tables)) {
            $result = $this->ws_tables[$table]['item'];
        }
        return $result;
    }

    /**
     * Construction of tables definitions.
     *
     * @since 3.5.0
     */
    private function ws_tables_construct(){
        $n = 20;
        $this->ws_tables[self::live_weather_station_stations_table()] = array('name' => __('Stations details', 'live-weather-station'), 'item' => _n('station', 'stations', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_module_detail_table()] = array('name' => __('Modules details', 'live-weather-station'), 'item' => _n('module', 'modules', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_datas_table()] = array('name' => __('Current records', 'live-weather-station'), 'item' => _n('record', 'records', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_log_table()] = array('name' => __('Events log', 'live-weather-station'), 'item' => _n('event', 'events', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_histo_daily_table()] = array('name' => __('Daily data', 'live-weather-station'), 'item' => _n('record', 'records', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_histo_yearly_table()] = array('name' => __('Historical data', 'live-weather-station'), 'item' => _n('record', 'records', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_quota_day_table()] = array('name' => __('Daily API usage', 'live-weather-station'), 'item' => _n('entry', 'entries', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_quota_year_table()] = array('name' => __('Yearly API usage', 'live-weather-station'), 'item' => _n('entry', 'entries', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_performance_cache_table()] = array('name' => __('Cache statistics', 'live-weather-station'), 'item' => _n('entry', 'entries', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_performance_cron_table()] = array('name' => __('Cron statistics', 'live-weather-station'), 'item' => _n('entry', 'entries', $n, 'live-weather-station'));
        $this->ws_tables[self::live_weather_station_data_year_table()] = array('name' => __('Database statistics', 'live-weather-station'), 'item' => _n('entry', 'entries', $n, 'live-weather-station'));
    }

    /**
     * Full integrity check.
     *
     * @since 3.3.1
     */
    public function full_check($croned=true){
        if ($croned) {
            $cron_id = Watchdog::init_chrono(Watchdog::$integrity_check_name);
        }
        else {
            $cron_id = null;
        }
        $this->__full_check();
        if ($croned) {
            Watchdog::stop_chrono($cron_id);
        }
    }

    /**
     * Full integrity check.
     *
     * @since 3.3.1
     */
    private function __full_check(){
        $this->delete_orphaned_stations();
        $this->delete_orphaned_modules();
        $this->database_statistics();
        DeviceManager::synchronize_modules();
        Logger::info($this->facility,null,null,null,null,null,null,'Data integrity fully checked.');
    }

    /**
     * Delete orphaned stations in data table.
     *
     * @since 3.3.1
     */
    private function delete_orphaned_stations() {
        global $wpdb;
        $sql = "DELETE FROM " . $wpdb->prefix.self::live_weather_station_datas_table() . " WHERE device_id=''";
        $wpdb->query($sql);
    }

    /**
     * Delete orphaned modules in data table.
     *
     * @since 3.3.1
     */
    private function delete_orphaned_modules() {
        global $wpdb;
        $sql = "DELETE FROM " . $wpdb->prefix.self::live_weather_station_datas_table() . " WHERE module_id=''";
        $wpdb->query($sql);
    }

    /**
     * Performs database statistics.
     *
     * @since 3.5.0
     */
    private function database_statistics() {
        foreach ($this->ws_tables as $table => $detail) {
            $value = $this->stats_table($table);
            $datetime = new \DateTime();
            $datetime->setTime(12, 0, 0);
            $value['timestamp'] = $datetime->format('Y-m-d');
            self::insert_update_table(self::live_weather_station_data_year_table(), $value);
        }
    }
}