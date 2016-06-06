<?php

/**
 * OpenWeatherMap current weather client for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-owm-client.php');

use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;

trait Owm_Current_Client {

    use Owm_Client;

    protected $owm_client;
    protected $owm_datas;
    protected $facility = 'Weather Collector';

    /**
     * Get station's datas.
     *
     * @param   string  $city       The city name.
     * @param   string  $country    The country ISO-2 code.
     * @return  array     An array containing lat & lon coordinates.
     * @since    2.0.0
     */
    public static function get_coordinates_via_owm($city, $country) {
        $result = array() ;
        $owm = new OpenWeatherMap();
        $weather = $owm->getRawWeatherData($city.','.$country, 'metric', 'en', get_option('live_weather_station_owm_account')[0], 'json');
        $weather = json_decode($weather, true);
        if (is_array($weather)) {
            if (array_key_exists('coord', $weather)) {
                $result['loc_longitude'] = $weather['coord']['lon'];
                $result['loc_latitude'] = $weather['coord']['lat'];
            }
        }
        return $result;
    }

    /**
     * Get station's data array.
     *
     * @param   string  $json_weather    Weather array json formated.
     * @param   array   $station    Station array.
     * @param   string  $device_id  The device id.
     * @return  array     A standard array with value.
     * @throws  exception
     * @since    2.0.0
     */
    private function get_owm_datas_array($json_weather, $station, $device_id) {
        $weather = json_decode($json_weather, true);
        if (!is_array($weather)) {
            throw new Exception('JSON / '.(string)$json_weather);
        }
        if (array_key_exists('cod', $weather) && $weather['cod'] != 200) {
            if (array_key_exists('message', $weather)) {
                throw new Exception($weather['message']);
            }
            else {
                throw new Exception('OpenWeatherMap unknown exception');
            }
        }
        $result = array() ;
        if (!empty($weather)) {
            $result['device_id'] = $device_id;
            $result['device_name'] = $station['device_name'];
            $result['_id'] = self::get_owm_current_virtual_id($device_id);
            $result['type'] = 'NACurrent';
            $result['module_name'] = __('[OpenWeatherMap Records]', 'live-weather-station');
            $result['battery_vp'] = 6000;
            $result['rf_status'] = 0;
            $result['firmware'] = LWS_VERSION;
            $result['data_type'] = array();
            $dashboard = array();
            $dashboard['time_utc'] = $weather['dt'];
            if (array_key_exists('weather', $weather) && is_array($weather['weather']) && isset($weather['weather'][0]['id'])) {
                $dashboard['weather'] = $weather['weather'][0]['id'];
                $result['data_type'][] = 'weather';
            } else {
                $dashboard['weather'] = 0;
                $result['data_type'][] = 'weather';
            }
            if (array_key_exists('main', $weather) && isset($weather['main']['temp'])) {
                $dashboard['temperature'] = $weather['main']['temp'];
                $result['data_type'][] = 'temperature';
            } else {
                $dashboard['temperature'] = 0;
            }
            if (array_key_exists('main', $weather) && isset($weather['main']['pressure'])) {
                $dashboard['pressure'] = $weather['main']['pressure'];
                $result['data_type'][] = 'pressure';
            } else {
                $dashboard['pressure'] = 0;
            }
            if (array_key_exists('main', $weather) && isset($weather['main']['humidity'])) {
                $dashboard['humidity'] = $weather['main']['humidity'];
                $result['data_type'][] = 'humidity';
            } else {
                $dashboard['humidity'] = 0;
            }
            if (array_key_exists('wind', $weather) && isset($weather['wind']['deg']) && isset($weather['wind']['speed'])) {
                $dashboard['windangle'] = round($weather['wind']['deg']);
                $dashboard['windstrength'] = round($weather['wind']['speed'] * 3.6);
                $result['data_type'][] = 'windangle';
                $result['data_type'][] = 'windstrength';
            } else {
                $dashboard['windangle'] = 0;
                $dashboard['windstrength'] = 0;
            }
            if (array_key_exists('rain', $weather) && isset($weather['rain']['3h'])) {
                $dashboard['rain'] = $weather['rain']['3h'];
                $result['data_type'][] = 'rain';
            } else {
                $dashboard['rain'] = 0;
                $result['data_type'][] = 'rain';
            }
            if (array_key_exists('snow', $weather) && isset($weather['snow']['3h'])) {
                $dashboard['snow'] = $weather['snow']['3h'];
                $result['data_type'][] = 'snow';
            } else {
                $dashboard['snow'] = 0;
                $result['data_type'][] = 'snow';
            }
            if (array_key_exists('clouds', $weather) && isset($weather['clouds']['all'])) {
                $dashboard['cloudiness'] = $weather['clouds']['all'];
                $result['data_type'][] = 'cloudiness';
            } else {
                $dashboard['cloudiness'] = 0;
            }
            if (array_key_exists('sys', $weather) && is_array($weather['sys']) && isset($weather['sys']['sunrise']) && isset($weather['sys']['sunset'])) {
                $now = time();
                if ($weather['sys']['sunrise'] < $now && $weather['sys']['sunset'] > $now) {
                    $dashboard['is_day'] = 1;
                } else {
                    $dashboard['is_day'] = 0;
                }
                $result['data_type'][] = 'is_day';
            }
            $result['dashboard_data'] = $dashboard;
            Logger::debug($this->facility, $this->service_name, $result['device_id'], $result['device_name'], $result['_id'], $result['module_name'], 0, 'Success while collecting pollution data.');
        }
        else {
            Logger::notice($this->facility, $this->service_name, $result['device_id'], $result['device_name'], $result['_id'], $result['module_name'], 0, 'Data are empty or irrelevant.');
        }
        return $result;
    }
    
    /**
     * Get station's datas.
     *
     * @return  array     OWM collected datas.
     * @since    2.0.0
     */
    public function get_datas() {
        $this->last_owm_warning = '';
        $this->last_owm_error = '';
        if (get_option('live_weather_station_owm_account')[1] == 1 || get_option('live_weather_station_owm_account')[0] == '') {
            $this->owm_datas = array ();
            return array ();
        }
        $this->synchronize_owm();
        $this->owm_datas = array ();
        $stations = $this->get_located_stations_list();
        $owm = new OpenWeatherMap();
        foreach ($stations as $key => $station) {
            try {
                $raw_data = $owm->getRawWeatherData(array('lat' => $station['loc_latitude'], 'lon' => $station['loc_longitude']), 'metric', 'en', get_option('live_weather_station_owm_account')[0], 'json');
                $values = $this->get_owm_datas_array($raw_data, $station, $key);
            }
            catch(Exception $ex)
            {
                if (strpos($ex->getMessage(), 'Invalid API key') > -1) {
                    $this->last_owm_error = __('Wrong OpenWeatherMap API key.', 'live-weather-station');
                    Logger::critical($this->facility, $this->service_name, $station['device_id'], $station['device_name'], null, null, $ex->getCode(), 'Wrong credentials. Please, verify your OpenWeatherMap API key.');
                    return array();
                }
                if (strpos($ex->getMessage(), 'JSON /') > -1) {
                    Logger::warning($this->facility, $this->service_name, $station['device_id'], $station['device_name'], null, null, $ex->getCode(), 'OpenWeatherMap servers has returned empty response. Retry will be done shortly.');
                    $this->last_owm_warning = __('OpenWeatherMap servers have returned empty response for some weather stations. Retry will be done shortly.', 'live-weather-station');
                }
                else {
                    $this->last_owm_warning = __('Temporary unable to contact OpenWeatherMap servers. Retry will be done shortly.', 'live-weather-station');
                    Logger::warning($this->facility, $this->service_name, $station['device_id'], $station['device_name'], null, null, $ex->getCode(), 'Temporary unable to contact OpenWeatherMap servers. Retry will be done shortly.');
                    return array();
                }
            }
            if (isset($values) && is_array($values)) {
                $this->owm_datas[] = $values;
            }
        }
        $this->store_owm_datas($this->owm_datas);
        return $this->owm_datas;
    }
}