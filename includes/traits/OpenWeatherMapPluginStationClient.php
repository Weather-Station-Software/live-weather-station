<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\OpenWeatherMap\OWMApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;

/**
 * OpenWeatherMap station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait StationClient {

    use BaseClient;

    protected $owm_client;
    protected $owm_datas;
    protected $facility = 'Weather Collector';

    /**
     * Get station's data array.
     *
     * @param string $json_weather Weather array json formated.
     * @param array $station Station array.
     * @param string $device_id The device id.
     * @return array A standard array with value.
     * @throws \Exception
     * @since 3.0.0
     */
    private function get_owm_datas_array($json_weather, $station, $device_id) {
        $weather = json_decode($json_weather, true);
        if (!is_array($weather)) {
            throw new \Exception('JSON / '.(string)$json_weather);
        }
        Logger::debug($this->facility, $this->service_name, null, null, null, null, null, print_r($weather, true));
        if (array_key_exists('cod', $weather) && $weather['cod'] != 200) {
            if (array_key_exists('message', $weather)) {
                throw new \Exception($weather['message']);
            }
            else {
                throw new \Exception('OpenWeatherMap unknown exception');
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
            Logger::debug($this->facility, $this->service_name, $result['device_id'], $result['device_name'], $result['_id'], $result['module_name'], 0, 'Success while collecting current weather data.');
        }
        else {
            Logger::notice($this->facility, $this->service_name, $result['device_id'], $result['device_name'], $result['_id'], $result['module_name'], 0, 'Data are empty or irrelevant.');
        }
        return $result;
    }

    /**
     * Get true station's datas.
     *
     * @return array OWM collected datas.
     * @since 3.0.0
     */
    public function get_datas() {
        if (get_option('live_weather_station_owm_apikey') == '') {
            $this->owm_datas = array ();
            return array ();
        }
        $this->synchronize_owm_true_station();
        $this->owm_datas = array ();
        $stations = $this->get_operational_stations_list();
        //Logger::dev(print_r($stations, true));

        $owm = new OWMApiClient();
        foreach ($stations as $st => $station) {
            $device_id = $station['device_id'];
            $device_name = $station['device_name'];
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    $raw_data = $owm->getRawStationData(29584, 'metric', 'en', get_option('live_weather_station_owm_apikey'), 'json');
                    Logger::notice($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Weather stations data retrieved.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Quota manager has forbidden to retrieve data.');
                }

                /*$values = $this->get_owm_datas_array($raw_data, $station, $key);
                $place = array();
                $place['country'] = $station['loc_country'];
                $place['city'] = $station['loc_city'];
                $place['altitude'] = $station['loc_altitude'];
                $place['timezone'] = $station['loc_timezone'];
                $place['location'] = array($station['loc_longitude'], $station['loc_latitude']);
                $values['place'] = $place;*/
            }
            catch(\Exception $ex)
            {
                if (strpos($ex->getMessage(), 'Invalid API key') > -1) {
                    Logger::critical('Authentication', $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Wrong credentials. Please, verify your OpenWeatherMap API key.');
                    return array();
                }
                if (strpos($ex->getMessage(), 'JSON /') > -1) {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'OpenWeatherMap servers has returned empty response. Retry will be done shortly.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Temporary unable to contact OpenWeatherMap servers. Retry will be done shortly.');
                    return array();
                }
            }
            if (isset($values) && is_array($values)) {
                $this->owm_datas[] = $values;
            }
        }
        //$this->store_owm_datas($this->owm_datas);
        return $this->owm_datas;
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$owm_update_station_schedule_name);
        $err = '';
        try {
            /*$err = 'collecting weather';
            $this->get_datas();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_LOC_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_LOC_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
            */
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}