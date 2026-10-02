<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Quota\Quota;
use WeatherStation\SDK\Netatmo\Clients\NAWSApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;


/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
trait BaseClient {

    use Dashboard_Manipulation;

    public $refresh_token = '';
    public $access_token = '';

    public $last_netatmo_error = '';
    public $last_netatmo_warning = '';
    protected $netatmo_client;
    protected $netatmo_measurements;


    protected $facility = 'Weather Collector';
    protected $service_name = 'Netatmo';

    /**
     * The keys of the Netatmo application to use for the stored tokens.
     *
     * A token only works with the application which issued it: a connection made with the application of the site uses
     * the keys of the site, any other (older) connection keeps using the keys built in the plugin.
     *
     * @param string $prefix Option prefix of the service: 'netatmo' or 'netatmohc'.
     * @param string $builtin_id Client id built in the plugin.
     * @param string $builtin_secret Client secret built in the plugin.
     * @return array The client id and the client secret.
     * @since 3.9.0
     */
    protected function netatmo_app_keys($prefix, $builtin_id, $builtin_secret) {
        if ((bool)get_option('live_weather_station_' . $prefix . '_own_keys')) {
            $id = (string)get_option('live_weather_station_' . $prefix . '_client_id');
            $secret = (string)get_option('live_weather_station_' . $prefix . '_client_secret');
            if ($id !== '' && $secret !== '') {
                return array($id, $secret);
            }
        }
        return array($builtin_id, $builtin_secret);
    }

    /**
     * The scope asked to Netatmo by this service.
     *
     * @return string The scope.
     * @since 3.9.0
     */
    public function get_netatmo_scope() {
        return $this->netatmo_scope;
    }

    /**
     * The address where Netatmo sends the user back after the authorization (to be registered in the Netatmo application).
     *
     * @return string The address.
     * @since 3.9.0
     */
    public static function netatmo_redirect_uri() {
        return admin_url('admin-post.php?action=live_weather_station_netatmo_callback');
    }

    /**
     * The address of the Netatmo page where the user authorizes the application.
     *
     * @param string $client_id Client id of the application of the site.
     * @param string $state Single use random value, checked when the user comes back.
     * @param string $scope Scope to ask for.
     * @return string The address.
     * @since 3.9.0
     */
    public static function netatmo_authorize_url($client_id, $state, $scope) {
        return 'https://api.netatmo.com/oauth2/authorize?' . http_build_query(array(
            'client_id' => $client_id,
            'redirect_uri' => self::netatmo_redirect_uri(),
            'scope' => $scope,
            'state' => $state,
        ), '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Connect the site to Netatmo with its own application, from an authorization code or from a refresh token.
     *
     * The tokens are stored only if Netatmo accepts the keys and the grant.
     *
     * @param string $prefix Option prefix of the service: 'netatmo' or 'netatmohc'.
     * @param string $client_id Client id of the application of the site.
     * @param string $client_secret Client secret of the application of the site.
     * @param string $grant 'code' or 'refresh_token'.
     * @param string $value The authorization code or the refresh token.
     * @return string The error message if the connection failed, an empty string otherwise.
     * @since 3.9.0
     */
    public function netatmo_connect_with_own_keys($prefix, $client_id, $client_secret, $grant, $value) {
        $pattern = '/^[\x21-\x7E]{8,512}$/';
        if (!preg_match($pattern, (string)$client_id) || !preg_match($pattern, (string)$client_secret) || !preg_match($pattern, (string)$value)) {
            return __('The client id, the client secret and the code or the token must be filled in, without spaces.', 'live-weather-station');
        }
        if (!Quota::verify($this->service_name, 'GET')) {
            return __('The quota of requests to Netatmo is reached. Please, retry later.', 'live-weather-station');
        }
        $body = array('client_id' => $client_id, 'client_secret' => $client_secret);
        if ($grant === 'code') {
            $body['grant_type'] = 'authorization_code';
            $body['code'] = $value;
            $body['redirect_uri'] = self::netatmo_redirect_uri();
            $body['scope'] = $this->netatmo_scope;
        }
        else {
            $body['grant_type'] = 'refresh_token';
            $body['refresh_token'] = $value;
        }
        $response = wp_remote_post('https://api.netatmo.com/oauth2/token', array(
            'body' => $body,
            'timeout' => max(1, min(60, (int)get_option('live_weather_station_collection_http_timeout'))),
            'redirection' => 0,
            'user-agent' => LIVE_WEATHER_STATION_PLUGIN_AGENT,
        ));
        if (is_wp_error($response)) {
            return __('Temporarily unable to contact Netatmo servers. Please, retry later.', 'live-weather-station');
        }
        $code = (int)wp_remote_retrieve_response_code($response);
        $data = json_decode((string)wp_remote_retrieve_body($response), true);
        if ($code !== 200 || !is_array($data)) {
            $error = (is_array($data) && isset($data['error']) && is_string($data['error'])) ? $data['error'] : '';
            if ($error === 'invalid_client') {
                return __('Netatmo does not recognize this client id and client secret. Please, check the keys of your Netatmo application.', 'live-weather-station');
            }
            if ($error === 'invalid_grant') {
                return __('Netatmo refused the authorization code or the token. Please, start again.', 'live-weather-station');
            }
            if ($code === 403) {
                return __('Netatmo refused this request.', 'live-weather-station');
            }
            return __('Temporarily unable to contact Netatmo servers. Please, retry later.', 'live-weather-station');
        }
        $token_pattern = '/^[\x21-\x7E]{8,512}$/';
        if (!isset($data['access_token'], $data['refresh_token']) || !is_string($data['access_token']) || !is_string($data['refresh_token'])
            || !preg_match($token_pattern, $data['access_token']) || !preg_match($token_pattern, $data['refresh_token'])) {
            return __('Netatmo answered without usable tokens. Please, retry later.', 'live-weather-station');
        }
        update_option('live_weather_station_' . $prefix . '_client_id', $client_id);
        update_option('live_weather_station_' . $prefix . '_client_secret', $client_secret);
        update_option('live_weather_station_' . $prefix . '_refresh_token', $data['refresh_token']);
        update_option('live_weather_station_' . $prefix . '_access_token', $data['access_token']);
        update_option('live_weather_station_' . $prefix . '_own_keys', 1);
        update_option('live_weather_station_' . $prefix . '_connected', 1);
        return '';
    }



    /**
     * Store station's measurements.
     *
     * @param array $stations The station list.
     * @param boolean $is_hc Optional. True if it's a healthy home coach.
     * @since 3.1.0
     */
    private function store_netatmo_measurements($stations, $is_hc=false) {
        $measurements = $this->netatmo_measurements ;
        foreach($measurements['devices'] as &$device){
            $store = false;
            foreach ($stations as $station) {
                if ($station['station_id'] == $device['_id']) {
                    $store = true;
                }
            }
            if ($store) {
                if (isset($device) && is_array($device) && array_key_exists('dashboard_data', $device)) {
                    $this->get_netatmo_dashboard($device['_id'], $device['station_name'], $device['_id'], $device['module_name'],
                        $device['type'], $device['data_type'], $device['dashboard_data'], $device['place'], $device['wifi_status'], $device['firmware'], $device['last_status_store'], 0, $device['date_setup'], $device['last_setup'], $device['last_upgrade'], $is_hc);
                    Logger::debug($this->facility, $this->service_name, $device['_id'], $device['station_name'], $device['_id'], $device['module_name'], 0, 'Success while collecting device records.');
                    foreach($device['modules'] as &$module)
                    {
                        if (isset($module) && is_array($module) && array_key_exists('dashboard_data', $module)) {
                            $this->get_netatmo_dashboard($device['_id'], $device['station_name'], $module['_id'], $module['module_name'],
                                $module['type'], $module['data_type'], $module['dashboard_data'], $device['place'], $module['rf_status'], $module['firmware'], $module['last_seen'], $module['battery_vp'], null, $module['last_setup'], null, $is_hc);
                            Logger::debug($this->facility, $this->service_name, $device['_id'], $device['station_name'], $module['_id'], $module['module_name'], 0, 'Success while collecting module records.');
                        }
                        else {
                            Logger::warning($this->facility, $this->service_name, $device['_id'], $device['station_name'], null, null, 500, 'Inconsistent data in a module.');
                        }
                    }
                }
                else {
                    Logger::warning($this->facility, $this->service_name, null, null, null, null, 500, 'Inconsistent data in a station.');
                }
            }
        }
    }

    /**
     * Corrects historical station's data.
     *
     * @param array $types : type of measurements you wanna retrieve. Ex : "Temperature, CO2, Humidity".
     *
     * @since 3.7.0
     */
    private function normalize_netatmo_historical_measurements($types) {
        $measurements = $this->netatmo_measurements ;
        unset($measurements['time_server']);
        $result = array();
        Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, Logger::dump($measurements));
        if (count($measurements) > 0) {
            $result['start'] = array_keys($measurements)[0];
            foreach ($measurements as $ts => $data) {
                foreach ($data as $k => $d) {
                    if (!isset($types[$k])) {
                        continue;
                    }
                    $result[strtolower($types[$k])][$ts] = $d;
                }
            }
            $result['end'] = $ts;
        }
        $this->netatmo_measurements = $result;
    }

    /**
     * Corrects station's measurements.
     *
     * @param integer $station_type The station type.
     *
     * @since 2.3.0
     */
    private function normalize_netatmo_measurements($station_type) {
        $measurements = $this->netatmo_measurements ;
        $d = $measurements;
        Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, Logger::dump($d));
        unset($d['devices']);
        Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, Logger::dump($d));
        $measurements['timeshift'] = 0;
        if (array_key_exists('time_server', $measurements)) {
            $measurements['timeshift'] = time() - $measurements['time_server'];
            if (abs($measurements['timeshift']) > get_option('live_weather_station_time_shift_threshold')) {
                Logger::warning('API / SDK', $this->service_name, null, null, null, null, 0, 'Server time shift: ' . $measurements['timeshift'] . 's.');
            }
            else {
                Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, 'Server time shift: ' . $measurements['timeshift'] . 's.');
            }

        }
        foreach($measurements['devices'] as $device_key => &$device){
            if (isset($device) && is_array($device)) {
                if (!isset($device['station_name'])) {
                    $device['station_name'] = '?';
                }
                if (!isset($device['module_name'])) {
                    $device['module_name'] = $device['station_name'];
                }
                if (!isset($device['device_name'])) {
                    $device['device_name'] = $device['module_name'];
                }
                if (!isset($device['name'])) {
                    $device['name'] = $device['device_name'];
                }
                if (!isset($device['type'])) {
                    $device['type'] = 'unknown';
                }
                if ($device['type'] == 'NHC') {
                    $device['type'] = 'NAMain';
                }
                if (!isset($device['data_type'])) {
                    $device['data_type'] = 'unknown';
                }
                if (!isset($device['wifi_status'])) {
                    $device['wifi_status'] = 0;
                }
                if (!isset($device['firmware'])) {
                    $device['firmware'] = 0;
                }
                if (!isset($device) || !isset($device['_id']) || !array_key_exists('dashboard_data', $device) || (array_key_exists('dashboard_data', $device) && !isset($device['dashboard_data'])) || (array_key_exists('dashboard_data', $device) && !is_array($device['dashboard_data']))) {
                    unset($measurements['devices'][$device_key]);
                    Logger::warning($this->facility, $this->service_name, null, null, null, null, 9, 'Station not found.');
                    continue;
                }
                if (!isset($device['modules']) || !is_array($device['modules'])) {
                    $device['modules'] = array();
                }
                if (count($device['modules']) > 0) {
                    foreach ($device['modules'] as $key => &$module) {
                        if (isset($module) && is_array($module)) {
                            if (!isset($module['module_name'])) {
                                $module['module_name'] = '?';
                            }
                            if (!isset($module['type'])) {
                                $module['type'] = 'unknown';
                            }
                            if (!isset($module['data_type'])) {
                                $module['data_type'] = 'unknown';
                            }
                            if (!isset($module['rf_status'])) {
                                $module['rf_status'] = 0;
                            }
                            if (!isset($module['firmware'])) {
                                $module['firmware'] = 0;
                            }
                            if (!isset($module['battery_vp'])) {
                                $module['battery_vp'] = 0;
                            }
                            if (!isset($module) || !isset($module['_id']) || !array_key_exists('dashboard_data', $module) || (array_key_exists('dashboard_data', $module) && !isset($module['dashboard_data'])) || (array_key_exists('dashboard_data', $module) && !is_array($module['dashboard_data']))) {
                                unset($device['modules'][$key]);
                            }
                        } else {
                            Logger::warning($this->facility, $this->service_name, $device['_id'], $device['station_name'], null, null, 111, 'Module is removed or out of battery.');
                        }
                    }
                } else {
                    if ($this->netatmo_type == LIVE_WEATHER_STATION_NETATMO_SID) {
                        Logger::warning($this->facility, $this->service_name, $device['_id'], $device['station_name'], null, null, 900, 'No module found for this station.');
                    }
                }
                $device['modules'] = array_filter($device['modules']);
            }
            else {
                Logger::warning($this->facility, $this->service_name, null, null, null, null, 111, 'Station is unreachable.');
            }

        }
        $this->netatmo_measurements = $measurements;
    }
}