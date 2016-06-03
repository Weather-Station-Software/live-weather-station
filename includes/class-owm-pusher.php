<?php

/**
 * Class to push data to OpenWeatherMap.
 *
 * @since      2.5.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'class-live-weather-station-pusher.php');


class OWM_Pusher extends Live_Weather_Station_Pusher {

    /**
     * Get the service Name.
     *
     * @return  string   The service name.
     * @since   3.0.0
     */
    protected function get_service_name() {
        return 'OpenWeatherMap';
    }

    /**
     * Format Netatmo data to be pushed.
     *
     * @param   array   $data      Collected Netatmo datas.
     * @return  array   The data ready to push.
     * @since   2.5.0
     */
    protected function get_pushed_data($data) {
        $result = array();
        if (is_array($data) && !empty($data)) {
            foreach ($data['devices'] as $device) {
                $sub = array();
                if (time() - $device['dashboard_data']['time_utc'] < $this->time_shift) {
                    if (array_key_exists('Pressure', $device['dashboard_data'])) {
                        $sub['pressure'] = $this->get_pressure($device['dashboard_data']['Pressure'], 3);
                    }
                }
                else {
                    continue;
                }
                foreach ($device['modules'] as $module) {
                    $dashboard = $module['dashboard_data'];
                    if (time() - $dashboard['time_utc'] > $this->time_shift) {
                        continue;
                    }
                    switch (strtolower($module['type'])) {
                        case 'namodule1': // Outdoor module
                            if (array_key_exists('Temperature', $dashboard)) {
                                $sub['temp'] = $this->get_temperature($dashboard['Temperature'], 0);
                            }
                            if (array_key_exists('Humidity', $dashboard)) {
                                $sub['humidity'] = $this->get_humidity($dashboard['Humidity']);
                            }
                            break;
                        case 'namodule3': // Rain gauge
                            if (array_key_exists('sum_rain_1', $dashboard)) {
                                $sub['rain_1h'] = $this->get_rain($dashboard['sum_rain_1'], 0);
                            }
                            if (array_key_exists('sum_rain_24', $dashboard)) {
                                $sub['rain_today'] = $this->get_rain($dashboard['sum_rain_24'], 0);
                            }
                            break;
                        case 'namodule2': // Wind gauge
                            if (array_key_exists('WindAngle', $dashboard)) {
                                $sub['wind_dir'] = $this->get_wind_angle($dashboard['WindAngle']);
                            }
                            if (array_key_exists('WindStrength', $dashboard)) {
                                $sub['wind_speed'] = $this->get_wind_speed($dashboard['WindStrength'], 5);
                            }
                            if (array_key_exists('GustStrength', $dashboard)) {
                                $sub['wind_gust'] = $this->get_wind_speed($dashboard['GustStrength'], 5);
                            }
                            break;
                        case 'nacomputed': // Computed values virtual module
                            if (array_key_exists('dew_point', $dashboard)) {
                                $sub['dewpoint'] = $this->get_temperature($dashboard['dew_point'], 0);
                            }
                            break;
                    }
                }
                if (!empty($sub)) {
                    $place = $device['place'];
                    if (isset($place) && is_array($place)) {
                        if (array_key_exists('altitude', $place)) {
                            $sub['alt'] = $place['altitude'];
                        }
                        if (isset($place['location']) && is_array($place['location']) && count($place['location']) > 1) {
                            $sub['lat'] = $place['location'][1];
                        }
                        if (isset($place['location']) && is_array($place['location']) && count($place['location']) > 0) {
                            $sub['long'] = $place['location'][0];
                        }
                    }
                    $result[$device['_id']] = $sub;
                }
            }
        }
        return $result;
    }

    /**
     * Completes data to be pushed.
     *
     * @param   array   $device      The actual device.
     * @param   array   $station      Station details.
     * @return  array   The completed device.
     * @since   2.5.0
     */
    protected function complete_pushed_data($device, $station) {
        $result = $device;
        $device['name'] = $station['station_name'];
        return $result;
    }

    /**
     * Test if these station data must be pushed.
     *
     * @param   array   $station      Station details.
     * @return  boolean   True if these station data must be pushed, false otherwise.
     * @since   2.5.0
     */
    protected function ready_for_push($station) {
        return ($station['owm_sync'] == 1 && $station['owm_user'] != '' && $station['owm_password'] != '');
    }

    /**
     * Get the post url.
     *
     * @return  string   The url where to post data.
     * @since   2.5.0
     */
    protected function get_post_url() {
        return 'http://openweathermap.org/data/post';
    }

    /**
     * Get the user/pwd string for CURLOPT_USERPWD option.
     *
     * @param   array   $station      Station details.
     * @return  string   User and password ready to use by curl_setopt.
     * @since   2.5.0
     */
    protected function get_userpwd($station) {
        return $station['owm_user'] . ':' . $station['owm_password'];
    }

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @throws Exception Contains parsed error message or HTTP error code & message
     * @since   2.5.0
     */
    protected function process_result($content, $station) {
        $body = $content['body'];
        $error = false;
        $code = 0;
        $message = 'Unknown error';
        $id = 0;
        if ($body != '') {
            $body = json_decode($body, true);
            if (is_array($body)) {
                if (array_key_exists('cod', $body)) {
                    $code = $body['cod'];
                    if ($code != '200') {
                        $error = true;
                        if (array_key_exists('message', $body)) {
                            $message = $body['message'];
                        }
                    }
                    else {
                        if (array_key_exists('id', $body)) {
                            $id = $body['id'];
                        }
                        else {
                            $error = true;
                            $code = 498;
                            $message = 'The answer can not be interpreted by the client';
                        }
                    }
                }
                else {
                    $error = true;
                    $code = 498;
                    $message = 'The answer can not be interpreted by the client';
                }
            }
            else {
                $error = true;
                $code = 498;
                $message = 'The answer can not be interpreted by the client';
            }
        }
        else {
            $error = true;
            $code = 418;
            $message = '';
        }
        if ($error) {
            throw new Exception($message, $code);
        }
        if ($id != 0) {
            $station['owm_id'] = $id;
            $this->update_infos_table($station);
        }
    }
}