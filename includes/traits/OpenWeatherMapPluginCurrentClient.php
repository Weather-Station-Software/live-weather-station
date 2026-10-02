<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\OpenWeatherMap\OWMApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Unit\Conversion;

/**
 * OpenWeatherMap current weather client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
trait CurrentClient {

    use BaseClient, Conversion;

    protected $owm_client;
    protected $owm_measurements;
    protected $facility = 'Weather Collector';

    /**
     * Get station's measurements.
     *
     * @param   string  $city       The city name.
     * @param   string  $country    The country ISO-2 code.
     * @return  array     An array containing lat & lon coordinates.
     * @since    2.0.0
     */
    public static function get_coordinates_via_owm($city, $country) {
        $result = array() ;
        $owm = new OWMApiClient();
        Quota::verify(self::$service, 'GET');
        $weather = $owm->getRawWeatherData($city.','.$country, 'metric', 'en', get_option('live_weather_station_owm_apikey'), 'json');
        $weather = json_decode($weather, true);
        if (is_array($weather)) {
            if (array_key_exists('coord', $weather)) {
                $result['loc_longitude'] = live_weather_station_clean_number(isset($weather['coord']['lon']) ? $weather['coord']['lon'] : null);
                $result['loc_latitude'] = live_weather_station_clean_number(isset($weather['coord']['lat']) ? $weather['coord']['lat'] : null);
            }
        }
        return $result;
    }

    /**
     * Get station's data array.
     *
     * @param   string  $json_weather    Weather array json formatted.
     * @param   array   $station    Station array.
     * @param   string  $device_id  The device id.
     * @return  array     A standard array with value.
     * @throws  \Exception
     * @since    2.0.0
     */
    private function get_owm_measurements_array($json_weather, $station, $device_id) {
        $weather = json_decode($json_weather, true);
        if (!is_array($weather)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- caught in the same class (only tested with strpos() then logged with a fixed text, never printed), and the payload is already cleaned by live_weather_station_clean_text() (sanitize_text_field)
            throw new \Exception('JSON / '.live_weather_station_clean_text($json_weather, 200));
        }
        Logger::debug($this->facility, $this->service_name, null, null, null, null, null, Logger::dump($weather));
        if (array_key_exists('cod', $weather) && $weather['cod'] != 200) {
            if (array_key_exists('message', $weather)) {
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- caught in the same class (only tested with strpos() then logged with a fixed text, never printed), and the message is already cleaned by live_weather_station_clean_text() (sanitize_text_field)
                throw new \Exception(live_weather_station_clean_text($weather['message'], 200));
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
            $result['firmware'] = LIVE_WEATHER_STATION_VERSION;
            $result['data_type'] = array();
            $dashboard = array();
            $dashboard['time_utc'] = live_weather_station_clean_number(isset($weather['dt']) ? $weather['dt'] : null, 0);
            if (array_key_exists('weather', $weather) && is_array($weather['weather']) && isset($weather['weather'][0]['id'])) {
                $dashboard['weather'] = live_weather_station_clean_number($weather['weather'][0]['id'], 0);
                $result['data_type'][] = 'weather';
            } else {
                $dashboard['weather'] = 0;
                $result['data_type'][] = 'weather';
            }
            if (array_key_exists('main', $weather) && isset($weather['main']['temp'])) {
                $temperature = live_weather_station_clean_number($weather['main']['temp'], 15.0);
                $dashboard['temperature'] = $temperature;
                $result['data_type'][] = 'temperature';
            } else {
                $dashboard['temperature'] = 0;
                $temperature = 15.0;
            }
            if (array_key_exists('main', $weather) && isset($weather['main']['pressure'])) {
                $dashboard['pressure_sl'] = live_weather_station_clean_number($weather['main']['pressure'], 0);
                $dashboard['pressure'] = $this->convert_from_mslp_to_baro($dashboard['pressure_sl'], $station['loc_altitude'], $temperature);
                $result['data_type'][] = 'pressure_sl';
                $result['data_type'][] = 'pressure';
            } else {
                $dashboard['pressure_sl'] = 0;
                $dashboard['pressure'] = 0;
            }
            if (array_key_exists('main', $weather) && isset($weather['main']['humidity'])) {
                $dashboard['humidity'] = live_weather_station_clean_number($weather['main']['humidity'], 0);
                $result['data_type'][] = 'humidity';
            } else {
                $dashboard['humidity'] = 0;
            }
            if (array_key_exists('wind', $weather) && isset($weather['wind']['deg']) && isset($weather['wind']['speed'])) {
                $wind_deg = (float)live_weather_station_clean_number($weather['wind']['deg'], 0);
                $wind_speed = (float)live_weather_station_clean_number($weather['wind']['speed'], 0);
                $dashboard['windangle'] = round($wind_deg);
                $dashboard['winddirection'] = (int)floor(fmod($wind_deg + 180, 360));
                $dashboard['windstrength'] = round($wind_speed * 3.6);
                $result['data_type'][] = 'windangle';
                $result['data_type'][] = 'winddirection';
                $result['data_type'][] = 'windstrength';
            } else {
                $dashboard['windangle'] = 0;
                $dashboard['winddirection'] = 0;
                $dashboard['windstrength'] = 0;
            }
            if (array_key_exists('rain', $weather) && isset($weather['rain']['1h'])) {
                $dashboard['rain'] = live_weather_station_clean_number($weather['rain']['1h'], 0);
                $result['data_type'][] = 'rain';
            } elseif (array_key_exists('rain', $weather) && isset($weather['rain']['3h'])) {
                $dashboard['rain'] = (float)live_weather_station_clean_number($weather['rain']['3h'], 0) / 3;
                $result['data_type'][] = 'rain';
            } else {
                $dashboard['rain'] = 0;
                $result['data_type'][] = 'rain';
            }
            if (array_key_exists('snow', $weather) && isset($weather['snow']['3h'])) {
                $dashboard['snow'] = live_weather_station_clean_number($weather['snow']['3h'], 0);
                $result['data_type'][] = 'snow';
            } else {
                $dashboard['snow'] = 0;
                $result['data_type'][] = 'snow';
            }
            if (array_key_exists('clouds', $weather) && isset($weather['clouds']['all'])) {
                $dashboard['cloudiness'] = live_weather_station_clean_number($weather['clouds']['all'], 0);
                $result['data_type'][] = 'cloudiness';
            } else {
                $dashboard['cloudiness'] = 0;
            }
            if (array_key_exists('visibility', $weather)) {
                $dashboard['visibility'] = live_weather_station_clean_number($weather['visibility'], -1);
                $result['data_type'][] = 'visibility';
            } else {
                $dashboard['visibility'] = -1;
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
     * Get station's measurements.
     *
     * @return  array     OWM collected measurements.
     * @since    2.0.0
     */
    public function get_measurements() {
        if (get_option('live_weather_station_owm_apikey') == '') {
            $this->owm_measurements = array ();
            return array ();
        }
        $this->synchronize_owm();
        $this->owm_measurements = array ();
        $stations = $this->get_located_operational_stations_list();
        $owm = new OWMApiClient();
        foreach ($stations as $key => $station) {
            $device_id = $key;
            $device_name = $station['device_name'];
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    if (array_key_exists('loc_longitude', $station) && array_key_exists('loc_latitude', $station)) {
                        $raw_data = $owm->getRawWeatherData(array('lat' => $station['loc_latitude'], 'lon' => $station['loc_longitude']), 'metric', 'en', get_option('live_weather_station_owm_apikey'), 'json');
                    }
                    else {
                        Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, 135, 'Can\'t get current weather for a station without coordinates.');
                        continue;
                    }
                    $values = $this->get_owm_measurements_array($raw_data, $station, $key);
                    $place = array();
                    $place['country'] = $station['loc_country'];
                    $place['city'] = $station['loc_city'];
                    $place['altitude'] = $station['loc_altitude'];
                    $place['timezone'] = $station['loc_timezone'];
                    $place['location'] = array($station['loc_longitude'], $station['loc_latitude']);
                    $values['place'] = $place;
                    Logger::notice($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Data retrieved.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Quota manager has forbidden to retrieve data.');
                    $this->owm_measurements = array ();
                    return array ();
                }
            }
            catch(\Throwable $ex)
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
                $this->owm_measurements[] = $values;
            }
        }
        $this->store_owm_measurements($this->owm_measurements);
        return $this->owm_measurements;
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$owm_update_current_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_measurements();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute();
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute();
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Throwable $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . substr(sanitize_text_field($ex->getMessage()), 0, 500));
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}