<?php

namespace WeatherStation\SDK\WeatherUnderground\Plugin;

use WeatherStation\SDK\WeatherUnderground\WUGApiClient;
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
 * @since 3.0.0
 */
trait BaseClient {

    use Dashboard_Manipulation, Id_Manipulation, Description;

    protected $service_name = 'Weather Underground';
    protected static $service = 'Weather Underground';
    public $last_wug_error;

    /**
     * Get station's datas.
     *
     * @param string $key The API key of the account.
     * @param string $plan The plan of the account.
     *
     * @return boolean True if connection is established.
     * @since 3.0.0
     */
    public function authentication($key, $plan) {
        $wug = new WUGApiClient();
        $this->last_wug_error = '';
        try {
            Quota::verify($this->service_name, 'GET');
            $raw_data = $wug->getRawStationData('INORDPAS92', $key);
            $weather = json_decode($raw_data, true);
            if (!is_array($weather)) {
                throw new \Exception('JSON / '.(string)$raw_data);
            }
            if (array_key_exists('response', $weather)) {
                if (array_key_exists('error', $weather['response'])) {
                    if (array_key_exists('description', $weather['response']['error'])) {
                        throw new \Exception($weather['response']['error']['description']);
                    }
                    else {
                        throw new \Exception('Weather Underground unknown exception');
                    }
                }
                if (array_key_exists('features', $weather['response'])) {
                    if (array_key_exists('conditions', $weather['response']['features'])) {
                        if ($weather['response']['features']['conditions'] != 1) {
                            throw new \Exception($weather['response']['error']['description']);
                        }
                    }
                    else {
                        throw new \Exception('Weather Underground unknown exception');
                    }
                }
            }
            update_option('live_weather_station_wug_apikey', $key);
            update_option('live_weather_station_wug_plan', $plan);
            return true;
        }
        catch(\Exception $ex)
        {
            if (strpos($ex->getMessage(), 'this key does not exist') !== false) {
                $this->last_wug_error = __('Wrong Weather Underground API key.', 'live-weather-station');
            }
            else {
                $this->last_wug_error = __('Weather Underground servers have returned empty response. Please retry.', 'live-weather-station');
            }
            update_option('live_weather_station_wug_apikey', '');
            update_option('live_weather_station_wug_plan', 0);
            return false;
        }
    }

    /**
     * Synchronize main table with station table.
     *
     * @since 3.0.0
     */
    protected function synchronize_wug_station() {
        $list = array();
        $stations = $this->get_all_wug_id_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_wug_id($station['guid']);
                $s = $this->get_station_informations_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $this->update_stations_table($s);
                $list[] = $device_id;
            }
            $this->clean_wug_from_table($list);
        }
    }
}