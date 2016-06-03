<?php

/**
 * Class to push data to PWS Weather.
 *
 * @since      2.5.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'class-live-weather-station-pusher.php');


class PWS_Pusher extends Live_Weather_Station_Pusher {

    /**
     * Get the service Name.
     *
     * @return  string   The service name.
     * @since   3.0.0
     */
    protected function get_service_name() {
        return 'PWS Weather';
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
                    $sub['dateutc'] = date('Y-m-d H:i:s', $device['dashboard_data']['time_utc']);
                    if (array_key_exists('Pressure', $device['dashboard_data'])) {
                        $sub['baromin'] = $this->get_pressure($device['dashboard_data']['Pressure'], 1);
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
                                $sub['tempf'] = $this->get_temperature($dashboard['Temperature'], 1);
                            }
                            if (array_key_exists('Humidity', $dashboard)) {
                                $sub['humidity'] = $this->get_humidity($dashboard['Humidity']);
                            }
                            break;
                        case 'namodule3': // Rain gauge
                            if (array_key_exists('sum_rain_1', $dashboard)) {
                                $sub['rainin'] = $this->get_rain($dashboard['sum_rain_1'], 2);
                            }
                            if (array_key_exists('sum_rain_24', $dashboard)) {
                                $sub['dailyrainin'] = $this->get_rain($dashboard['sum_rain_24'], 2);
                            }
                            break;
                        case 'namodule2': // Wind gauge
                            if (array_key_exists('WindAngle', $dashboard)) {
                                $sub['winddir'] = $this->get_wind_angle($dashboard['WindAngle']);
                            }
                            if (array_key_exists('WindStrength', $dashboard)) {
                                $sub['windspeedmph'] = $this->get_wind_speed($dashboard['WindStrength'], 6);
                            }
                            if (array_key_exists('GustStrength', $dashboard)) {
                                $sub['windgustmph'] = $this->get_wind_speed($dashboard['GustStrength'], 6);
                            }
                            break;
                        case 'nacomputed': // Computed values virtual module
                            if (array_key_exists('dew_point', $dashboard)) {
                                $sub['dewptf'] = $this->get_temperature($dashboard['dew_point'], 1);
                            }
                            break;
                    }
                }
                if (!empty($sub)) {
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
        $result['ID'] = $station['pws_user'];
        $result['PASSWORD'] = $station['pws_password'];
        $result['softwaretype'] = LWS_PLUGIN_SIGNATURE;
        $result['action'] = 'updateraw';
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
        return ($station['pws_sync'] == 1 && $station['pws_user'] != '' && $station['pws_password'] != '');
    }

    /**
     * Get the post url.
     *
     * @return  string   The url where to post data.
     * @since   2.5.0
     */
    protected function get_post_url() {
        return 'http://www.pwsweather.com/pwsupdate/pwsupdate.php';
    }

    /**
     * Get the user/pwd string for CURLOPT_USERPWD option.
     *
     * @param   array   $station      Station details.
     * @return  string   User and password ready to use by curl_setopt.
     * @since   2.5.0
     */
    protected function get_userpwd($station) {
        return '';
    }

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @throws Exception Contains parsed error message
     * @since   2.5.0
     */
    protected function process_result($content, $station) {
        $body = $content['body'];
        if (strpos(strtolower($body), 'error:') > 0) {
            throw new Exception(substr(trim(substr($body, 6 + strpos(strtolower($body), '<body>'), 255)), 7, 255));
        }
    }
}