<?php

/**
 * OpenWeatherMap pollution client for Live Weather Station plugin
 *
 * @since      2.7.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-owm-client.php');

trait Owm_Pollution_Client {

    use Owm_Client;

    private $api_url = 'http://api.openweathermap.org/pollution/v1';
    private $indexes = ['o3', 'co'];
    protected $facility = 'Pollution Collector';
    protected $owm_datas;

    /**
     * Get the distance between two points.
     *
     * @return  float  The distance expressed in meters.
     * @since    2.7.0
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
            $url = $this->api_url . '/' . $index . '/' . round($lat, $round) . ',' . round($long, $round) . '/current.json?appid=' . get_option('live_weather_station_owm_account')[0];
            $content = wp_remote_get($url);
            if (is_wp_error($content)) {
                throw new Exception($content->get_error_message());
            }
            Logger::debug($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], 999, 'Raw data: ' . print_r($content,true));
            return $content;

        }
        catch (Exception $ex) {
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
     * @throws  exception
     * @since    2.7.0
     */
    private function get_owm_datas_array($json_pollution, $station, $device_id, $index, $lat, $long) {
        $pollution = json_decode($json_pollution['body'], true);
        if (!is_array($pollution)) {
            throw new Exception('JSON / '.(string)$json_pollution['body']);
        }
        $response = $json_pollution['response'];
        if (!is_array($response)) {
            throw new Exception('JSON / '.(string)$json_pollution['response']);
        }
        if (array_key_exists('code', $response) && $response['code'] == 404) {
            return array();
        }
        if (array_key_exists('code', $response) && $response['code'] != 200) {
            if (array_key_exists('message', $response)) {
                throw new Exception($response['message']);
            }
            else {
                throw new Exception('OpenWeatherMap unknown exception');
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
                    $result[$index] = 0;
                    if (array_key_exists('data', $pollution)) {
                        $result[$index] = 0;
                        if (is_array($pollution['data'])) {
                            foreach ($pollution['data'] as $co) {
                                if ($co['pressure'] == 1000) {
                                    $result[$index] = $co['value'] * 1000000;
                                    break;
                                }
                            }
                        }
                    }
                    break;
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
     * @return  array     OWM collected datas.
     * @since    2.7.0
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
            try {
                foreach ($this->indexes as $index) {
                    $values = $this->get_owm_datas_array($this->get_pollution_data($st, $station['loc_latitude'], $station['loc_longitude'], $index, 2), $station, $key, $index, $station['loc_latitude'], $station['loc_longitude']);
                    if (empty($values)) {
                        $values = $this->get_owm_datas_array($this->get_pollution_data($st, $station['loc_latitude'], $station['loc_longitude'], $index, 1), $station, $key, $index, $station['loc_latitude'], $station['loc_longitude']);
                    }
                    if (empty($values)) {
                        $values = $this->get_owm_datas_array($this->get_pollution_data($st, $station['loc_latitude'], $station['loc_longitude'], $index), $station, $key, $index, $station['loc_latitude'], $station['loc_longitude']);
                    }
                    if (!empty($values)) {
                        foreach ($values as $k => $v) {
                            $st['dashboard_data'][$k] = $v;
                        }
                        $st['data_type'][] = $index;
                        if (array_key_exists($index.'_distance',$values )) {
                            $st['data_type'][] = $index.'_distance';
                        }
                    }
                }
                if (!empty($st['dashboard_data'])) {
                    $st['dashboard_data']['time_utc'] = time();
                    $this->owm_datas[] = $st;
                    Logger::debug($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], 0, 'Success while collecting pollution data.');
                }
                else {
                    Logger::notice($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], 0, 'Data are empty or irrelevant.');
                }
            }
            catch(Exception $ex)
            {
                if (strpos($ex->getMessage(), 'Invalid API key') > -1) {
                    $this->last_owm_error = __('Wrong OpenWeatherMap API key.', 'live-weather-station');
                    Logger::critical($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], $ex->getCode(), 'Wrong credentials. Please, verify your OpenWeatherMap API key.');
                    return array();
                }
                if (strpos($ex->getMessage(), 'JSON /') > -1) {
                    Logger::warning($this->facility, $this->service_name, $st['device_id'], $st['device_name'], $st['_id'], $st['module_name'], $ex->getCode(), 'OpenWeatherMap servers has returned empty response. Retry will be done shortly.');
                    $this->last_owm_warning = __('OpenWeatherMap servers have returned empty response for some weather stations. Retry will be done shortly.', 'live-weather-station');
                }
                else {
                    $this->last_owm_warning = __('Temporary unable to contact OpenWeatherMap servers. Retry will be done shortly.', 'live-weather-station');
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
}