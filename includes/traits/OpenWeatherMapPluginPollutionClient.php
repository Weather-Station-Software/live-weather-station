<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\OpenWeatherMap\OWMApiClient;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;

/**
 * OpenWeatherMap pollution client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.7.0
 */
trait PollutionClient {

    use BaseClient;

    private $api_url = 'http://api.openweathermap.org/pollution/v1';
    private $indexes = ['o3', 'co'];//, 'so2', 'no2'];
    protected $facility = 'Pollution Collector';
    protected $owm_datas;

    /**
     * Get the distance between two points.
     *
     * @return float The distance expressed in meters.
     * @since 2.7.0
     */
    private function distanceGeoPoints ($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 3958.75;
        $dLat = deg2rad($lat2-$lat1);
        $dLng = deg2rad($lng2-$lng1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $dist = $earthRadius * $c * 1609;
        return $dist;
    }


    /**
     * Get pollution datas.
     *
     * @param   array   $data       Collected Netatmo datas.
     * @param   array   $stations   Optional. Test specificaly these stations.
     * @return  string  Error string if any.
     * @since    2.5.0
     */
    private function get_pollution_data($st, $lat, $long, $index, $round=0) {
        try {
            $url = $this->api_url . '/' . $index . '/' . round($lat, $round) . ',' . round($long, $round) . '/current.json?appid=' . get_option('live_weather_station_owm_apikey');
            // warning : don't verify quota here
            $args = array();
            $args['user-agent'] = LWS_PLUGIN_AGENT;
            $args['timeout'] = get_option('live_weather_station_collection_http_timeout');
            $content = wp_remote_get($url);
            if (is_wp_error($content)) {
                throw new \Exception($content->get_error_message());
            }
            Logger::debug($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], 999, 'Raw data: ' . print_r($content,true));
            return $content;

        }
        catch (\Exception $ex) {
            Logger::warning($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], $ex->getCode(), $ex->getMessage());
        }
    }


    /**
     * Get pollution's data array.
     *
     * @param   string  $json_pollution    Pollution array json formated.
     * @param   array   $station    Station array.
     * @param   string  $device_id  The device id.
     * @return  array     A standard array with value.
     * @throws  \Exception
     * @since    2.7.0
     */
    private function get_owm_datas_array($json_pollution, $station, $device_id, $index, $lat, $long) {
        $pollution = json_decode($json_pollution['body'], true);
        if (!is_array($pollution)) {
            throw new \Exception('JSON / '.(string)$json_pollution['body']);
        }
        Logger::debug($this->facility, $this->service_name, null, null, null, null, null, print_r($pollution, true));
        $response = $json_pollution['response'];
        if (!is_array($response)) {
            throw new \Exception('JSON / '.(string)$json_pollution['response']);
        }
        if (array_key_exists('code', $response) && $response['code'] == 404) {
            return array();
        }
        if (array_key_exists('code', $response) && $response['code'] != 200) {
            if (array_key_exists('message', $response)) {
                throw new \Exception($response['message']);
            }
            else {
                throw new \Exception('OpenWeatherMap unknown exception');
            }
        }
        $result = array() ;
        if (!empty($pollution)) {
            $result = array();
            $result['TS_'.$index] = strtotime($pollution['time']);
            switch ($index) {
                case 'o3' :
                    $result[$index] = 0;
                    if (array_key_exists('data', $pollution)) {
                        $result[$index] = round($pollution['data']);
                    }
                    break;
                case 'co' :
                //case 'so2' :
                    $result[$index] = 0;
                    if (array_key_exists('data', $pollution)) {
                        $result[$index] = 0;
                        if (is_array($pollution['data'])) {
                            foreach ($pollution['data'] as $pol) {
                                if ($pol['pressure'] == 1000) {
                                    $result[$index] = $pol['value'] * 1000000;
                                    break;
                                }
                            }
                        }
                    }
                    break;
                /*case 'no2' :
                    $result[$index] = 0;
                    if (array_key_exists('data', $pollution)) {
                        $result[$index] = 0;
                        if (is_array($pollution['data'])) {
                            if ($pollution['data']['no2_trop'] && is_array($pollution['data']['no2_trop'])) {
                                $result[$index] = $pollution['data']['no2_trop']['value'];
                                $result[$index] += $pollution['data']['no2_strat']['value'];
                                $result[$index] += $pollution['data']['no2']['value'];
                            }
                        }
                    }
                    break;*/
            }
            if (array_key_exists('location', $pollution) && is_array($pollution['location']) && isset($pollution['location']['latitude']) && isset($pollution['location']['longitude'])) {
                $result[$index.'_distance'] = round($this->distanceGeoPoints($lat, $long, $pollution['location']['latitude'], $pollution['location']['longitude']));
                $result['TS_'.$index.'_distance'] = strtotime($pollution['time']);
            } else {
                $result[$index.'_distance'] = -1;
            }
        }
        return $result;
    }
    /**
     * Get pollution's datas.
     *
     * @return array OWM collected datas.
     * @since 2.7.0
     */
    public function get_datas() {
        if (get_option('live_weather_station_owm_apikey') == '') {
            $this->owm_datas = array ();
            return array ();
        }
        $this->synchronize_owm();
        $this->owm_datas = array ();
        $stations = $this->get_located_operational_stations_list();
        foreach ($stations as $key => $station) {
            $st = array ();
            $st['device_id'] = $key;
            $st['device_name'] = $station['device_name'];
            $st['_id'] = self::get_owm_pollution_virtual_id($key);
            $st['type'] = 'NAPollution';
            $st['module_name'] = __('[OpenWeatherMap Pollution]', 'live-weather-station');
            $st['battery_vp'] = 6000;
            $st['rf_status'] = 0;
            $st['firmware'] = LWS_VERSION;
            $st['data_type'] = array();
            $st['dashboard_data'] = array();
            $values = array();
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    foreach ($this->indexes as $index) {
                        if (array_key_exists('loc_longitude', $station) && array_key_exists('loc_latitude', $station)) {
                            $values = $this->get_owm_datas_array($this->get_pollution_data($st, $station['loc_latitude'], $station['loc_longitude'], $index, 2), $station, $key, $index, $station['loc_latitude'], $station['loc_longitude']);
                        }
                        else {
                            Logger::warning($this->facility, $this->service_name, $st['device_id'], $st['device_name'], null, null, 135, 'Can\'t get pollution records for a station without coordinates.');
                            continue(2);
                        }
                        if (empty($values)) {
                            Quota::verify($this->service_name, 'GET');
                            $values = $this->get_owm_datas_array($this->get_pollution_data($st, $station['loc_latitude'], $station['loc_longitude'], $index, 1), $station, $key, $index, $station['loc_latitude'], $station['loc_longitude']);
                        }
                        if (empty($values)) {
                            Quota::verify($this->service_name, 'GET');
                            $values = $this->get_owm_datas_array($this->get_pollution_data($st, $station['loc_latitude'], $station['loc_longitude'], $index), $station, $key, $index, $station['loc_latitude'], $station['loc_longitude']);
                        }
                        if (!empty($values)) {
                            foreach ($values as $k => $v) {
                                $st['dashboard_data'][$k] = $v;
                            }
                            $st['data_type'][] = $index;
                            if (array_key_exists($index . '_distance', $values)) {
                                $st['data_type'][] = $index . '_distance';
                            }
                        }
                    }
                    $place = array();
                    $place['country'] = $station['loc_country'];
                    $place['city'] = $station['loc_city'];
                    $place['altitude'] = $station['loc_altitude'];
                    $place['timezone'] = $station['loc_timezone'];
                    $place['location'] = array($station['loc_longitude'], $station['loc_latitude']);
                    $st['place'] = $place;
                    if (!empty($st['dashboard_data'])) {
                        $st['dashboard_data']['time_utc'] = time();
                        $this->owm_datas[] = $st;
                        Logger::notice($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], 0, 'Pollution data retrieved.');
                    } else {
                        Logger::notice($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], 0, 'Pollution data are empty or irrelevant.');
                    }
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $st['device_id'], $st['device_name'], null, null, 0, 'Quota manager has forbidden to retrieve data.');
                    $this->owm_datas = array ();
                    return array ();
                }
            }
            catch(\Exception $ex)
            {
                if (strpos($ex->getMessage(), 'Invalid API key') > -1) {
                    Logger::critical('Authentication', $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], $ex->getCode(), 'Wrong credentials. Please, verify your OpenWeatherMap API key.');
                    return array();
                }
                if (strpos($ex->getMessage(), 'JSON /') > -1) {
                    Logger::warning($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], $ex->getCode(), 'OpenWeatherMap servers has returned empty response. Retry will be done shortly.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], $ex->getCode(), 'Temporary unable to contact OpenWeatherMap servers. Retry will be done shortly.');
                    return array();
                }
            }
        }
        if (!empty($this->owm_datas)) {
            $this->store_owm_datas($this->owm_datas);
        }
        return $this->owm_datas;
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$owm_update_pollution_schedule_name);
        try {
            $this->get_datas();
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting pollution data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while collecting pollution data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}