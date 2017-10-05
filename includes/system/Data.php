<?php

namespace WeatherStation\System\Data;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Watchdog;

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
}