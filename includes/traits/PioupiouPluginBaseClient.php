<?php

namespace WeatherStation\SDK\Pioupiou\Plugin;

use WeatherStation\SDK\Pioupiou\PIOUApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\ID\Handling as Id_Manipulation;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Type\Description;

/**
 * WeatherUnderground base client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */
trait BaseClient {

    use Dashboard_Manipulation, Id_Manipulation, Description;

    protected $service_name = 'Pioupiou';
    protected static $service = 'Pioupiou';

    /**
     * Synchronize main table with station table.
     *
     * @since 3.5.0
     */
    protected function synchronize_piou_station() {
        $list = array();
        $stations = $this->get_all_piou_id_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_piou_id($station['guid']);
                $s = $this->get_station_informations_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $this->update_stations_table($s);
                $list[] = $device_id;
            }
            $this->clean_piou_from_table($list);
        }
    }
}