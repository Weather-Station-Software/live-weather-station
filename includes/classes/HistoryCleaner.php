<?php

namespace WeatherStation\Data\History;

use WeatherStation\DB\Query;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\UI\ListTable\Log;
use WeatherStation\Data\DateTime\Conversion;

/**
 * This class is responsible of history cleaning.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

class Cleaner
{

    use Query;
    use Conversion;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'History Cleaner';


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
     * Cron to execute once a day to execute the cleaning.
     *
     * @since 3.4.0
     */
    public function cron() {
        $cron_id = Watchdog::init_chrono(Watchdog::$history_clean_name);
        $this->__clean();
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Main process of the cleaner.
     *
     * @since 3.4.0
     */
    private function __clean() {
        if (get_option('live_weather_station_retention_history') > 0) {
            $stations = $this->get_stations_list();
            foreach ($stations as $station) {
                $device_id = $station['station_id'];
                if ($this->delete_old_yearly_values($device_id, $station['loc_timezone'])) {
                    Logger::notice($this->facility, null, $station['station_id'], $station['station_name'], null, null, null, 'Old historical data cleaned.');
                }
                Logger::info($this->facility, null, $station['station_id'], $station['station_name'], null, null, null, 'No old historical data to clean.');
            }
        }
    }

    /**
     * Delete old yearly values.
     *
     * @param string $device_id The station.
     * @param string $tz The timezone.
     * @return bool True if operation was fully done, false otherwise.
     * @since 3.4.0
     */
    private function delete_old_yearly_values($device_id, $tz) {
        $max = date('Y-m-d', self::get_local_n_days_ago_midnight(7 * get_option('live_weather_station_retention_history'), $tz));
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_histo_yearly_table();
        $sql = "DELETE FROM ".$table_name." WHERE `timestamp`<='" . $max . "' AND `device_id`='" . $device_id . "';";
        return $wpdb->query($sql);
    }


}