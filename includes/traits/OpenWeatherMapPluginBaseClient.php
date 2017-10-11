<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;

use WeatherStation\SDK\OpenWeatherMap\OWMApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\ID\Handling as Id_Manipulation;
use WeatherStation\System\Quota\Quota;

/**
 * OpenWeatherMap base client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.7.0
 */
trait BaseClient {

    use Dashboard_Manipulation, Id_Manipulation;

    protected $service_name = 'OpenWeatherMap';
    protected static $service = 'OpenWeatherMap';
    public $last_owm_error;

    /**
     * Get station's datas.
     *
     * @param string $key The API key of the account.
     * @param string $plan The plan of the account.
     *
     * @return boolean True if connection is established.
     * @since 2.8.0
     */
    public function authentication($key, $plan) {
        $owm = new OWMApiClient();
        $this->last_owm_error = '';
        try {
            Quota::verify($this->service_name, 'GET');
            $raw_data = $owm->getRawWeatherData(6455259, 'metric', 'en', $key, 'json');
            $weather = json_decode($raw_data, true);
            if (!is_array($weather)) {
                throw new \Exception('JSON / '.(string)$raw_data);
            }
            if (array_key_exists('cod', $weather) && $weather['cod'] != 200) {
                if (array_key_exists('message', $weather)) {
                    throw new \Exception($weather['message']);
                }
                else {
                    throw new \Exception('OpenWeatherMap unknown exception');
                }
            }
            update_option('live_weather_station_owm_apikey', $key);
            update_option('live_weather_station_owm_plan', $plan);
            return true;
        }
        catch(\Exception $ex)
        {
            if (strpos($ex->getMessage(), 'Invalid API key') > -1) {
                $this->last_owm_error = __('Wrong OpenWeatherMap API key.', 'live-weather-station');
            }
            else {
                $this->last_owm_error = __('OpenWeatherMap servers have returned empty response. Please retry.', 'live-weather-station');
            }
            update_option('live_weather_station_owm_apikey', '');
            update_option('live_weather_station_owm_plan', 0);
            return false;
        }
    }

    /**
     * Synchronize main table with station table.
     *
     * @since 2.7.0
     */
    protected function synchronize_owm() {
        $list = array();
        $stations = $this->get_all_owm_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_owm_id($station['guid']);
                $s = $this->get_station_informations_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $s['last_refresh'] = date('Y-m-d H:i:s');
                $this->update_stations_table($s);
                $updates = array() ;
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $device_id;
                $updates['module_type'] = 'NAMain';
                $updates['module_name'] = $station['station_name'];
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'loc_altitude';
                $updates['measure_value'] = $station['loc_altitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_latitude';
                $updates['measure_value'] = $station['loc_latitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_longitude';
                $updates['measure_value'] = $station['loc_longitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_timezone';
                $updates['measure_value'] = $station['loc_timezone'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_country';
                $updates['measure_value'] = $station['loc_country_code'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_city';
                $updates['measure_value'] = $station['loc_city'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates);
                $list[] = $device_id;
            }
            $this->clean_owm_from_table($list);
        }
    }

    /**
     * Synchronize main table with station table.
     *
     * @since 3.0.0
     */
    protected function synchronize_owm_true_station() {
        $list = array();
        $stations = $this->get_all_owm_id_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_owm_true_id($station['guid']);
                $s = $this->get_station_informations_by_guid($station['guid']);
                $s['station_id'] = $device_id;
                $s['last_refresh'] = date('Y-m-d H:i:s');
                $this->update_stations_table($s);
                $list[] = $device_id;
            }
            $this->clean_owm_true_from_table($list);
        }
    }

    /**
     * Store station's datas.
     *
     * @param   array   $datas   OWM collected datas
     * @since    2.7.0
     */
    private function store_owm_datas($datas) {
        foreach ($datas as $data) {
            $this->get_dashboard($data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data'], $data['place']);
        }
    }
}