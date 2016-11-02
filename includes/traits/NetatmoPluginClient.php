<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Netatmo\Clients\NAWSApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;

/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Client {
    
    use Dashboard_Manipulation;

    public $refresh_token = '';
    public $access_token = ''; 

    public $last_netatmo_error = '';
    public $last_netatmo_warning = '';
    protected $netatmo_client;
    protected $netatmo_datas;

    protected $facility = 'Weather Collector';
    protected $service_name = 'Netatmo';

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // these API keys are property of NetAtmo licensed to Pierre Lannoy, you CAN'T use them for your apps.
    // If you are thinking to develop something, get your API Keys here: https://dev.netatmo.com
    private $client_id = '561695d4cce37cd35c8b4659';
    private $client_secret = 'yfavTSFLnq5hzJxgMYBkfZdvaX04wx4WFLtqsChm8RGuv';
    //////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Connects to the Netatmo account.
     *
     * @since    2.0.0
     */
    public function authentication($login, $password) {
        $config = array();
        $this->last_netatmo_error = '';
        $config['client_id'] = $this->client_id;
        $config['client_secret'] = $this->client_secret;
        $config['scope'] = 'read_station';
        $this->netatmo_client = new NAWSApiClient($config);
        $this->netatmo_client->setVariable('username', $login);
        $this->netatmo_client->setVariable('password', $password);
        try
        {
            $tokens = $this->netatmo_client->getAccessToken();
            update_option('live_weather_station_netatmo_refresh_token', $tokens['refresh_token']);
            update_option('live_weather_station_netatmo_access_token', $tokens['access_token']);
            update_option('live_weather_station_netatmo_connected', 1);
        }
        catch (\Exception $ex) {
            $this->last_netatmo_error = __('Wrong credentials. Please, verify your login and password.', 'live-weather-station');
            update_option('live_weather_station_netatmo_refresh_token', '');
            update_option('live_weather_station_netatmo_access_token', '');
            update_option('live_weather_station_netatmo_connected', 0);
            return false;
        }
        return true;
    }

    /**
     * Store station's datas.
     *
     * @since    1.0.0
     */
    private function store_netatmo_datas() {
        $datas = $this->netatmo_datas ;
        $stations = $this->get_all_netatmo_stations();
        foreach($datas['devices'] as $device){
            $store = false;
            foreach ($stations as $station) {
                if ($station['station_id'] == $device['_id']) {
                    $store = true;
                }
            }
            if ($store) {
                $this->get_netatmo_dashboard($device['_id'], $device['station_name'], $device['_id'], $device['module_name'],
                    $device['type'], $device['data_type'], $device['dashboard_data'], $device['place'], $device['wifi_status'], $device['firmware'], $device['last_status_store'], 0, $device['date_setup'], $device['last_setup'], $device['last_upgrade']);
                foreach($device['modules'] as $module)
                {
                    $this->get_netatmo_dashboard($device['_id'], $device['station_name'], $module['_id'], $module['module_name'],
                        $module['type'], $module['data_type'], $module['dashboard_data'], $device['place'], $module['rf_status'], $module['firmware'], $module['last_seen'], $module['battery_vp'], null, $module['last_setup']);
                    Logger::debug($this->facility, $this->service_name, $device['_id'], $device['station_name'], $module['_id'], $module['module_name'], 0, 'Success while collecting module records.');
                }
            }
        }
    }

    /**
     * Corrects station's datas.
     *
     * @since    2.3.0
     */
    private function normalize_netatmo_datas() {
        $datas = $this->netatmo_datas ;
        $datas['timeshift'] = 0;
        if (array_key_exists('time_server', $datas)) {
            $datas['timeshift'] = time() - $datas['time_server'];
            if (abs($datas['timeshift']) > get_option('live_weather_station_time_shift_threshold')) {
                Logger::warning('API / SDK', $this->service_name, null, null, null, null, 0, 'Server time shift: ' . $datas['timeshift'] . 's.');
            }
            else {
                Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, 'Server time shift: ' . $datas['timeshift'] . 's.');
            }

        }
        foreach($datas['devices'] as &$device){
            if (!isset($device['module_name'])) {
                $device['module_name'] = '?';
            }
            if (!isset($device['station_name'])) {
                $device['station_name'] = '?';
            }
            if (!isset($device['type'])) {
                $device['type'] = 'unknown';
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
            if (!isset($device['_id']) || !isset($device['dashboard_data']) || !is_array($device['dashboard_data'])) {
                unset($device);
                continue;
            }
            foreach($device['modules'] as &$module)
            {
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
                if (!isset($module['_id']) || !isset($module['dashboard_data']) || !is_array($module['dashboard_data'])) {
                    unset($module);
                }
            }
        }
        $this->netatmo_datas = $datas;
    }

    /**
     * Get station's datas.
     *
     * @param boolean $store Optional. Store the data.
     *
     * @return array The netatmo collected datas.
     * @since 1.0.0
     */
    public function get_datas($store=true) {
        $refresh_token = get_option('live_weather_station_netatmo_refresh_token');
        $access_token = get_option('live_weather_station_netatmo_access_token');
        $this->last_netatmo_error = '';
        $this->last_netatmo_warning = '';
        if ($refresh_token != '' && $access_token != '') {
            if (!isset($this->netatmo_client)) {
                $config = array();
                $config['client_id'] = $this->client_id;
                $config['client_secret'] = $this->client_secret;
                $config['scope'] = 'read_station';
                $config['refresh_token'] = $refresh_token;
                $config['access_token'] = $access_token;
                $this->netatmo_client = new NAWSApiClient($config);
            }
            try {
                $this->netatmo_datas = $this->netatmo_client->getData();
                $this->normalize_netatmo_datas();
                if ($store) {
                    $this->store_netatmo_datas();
                }
                update_option('live_weather_station_netatmo_refresh_token', $this->netatmo_client->getRefreshToken());
                update_option('live_weather_station_netatmo_access_token', $this->netatmo_client->getAccessToken()['access_token']);
                update_option('live_weather_station_netatmo_connected', 1);
                if (isset($config)) {
                    if (array_key_exists('access_token', $config)) {
                        if ($config['access_token'] != $this->netatmo_client->getAccessToken()['access_token']) {
                            Logger::notice($this->facility, $this->service_name, null, null, null, null, 0, 'Access token has been regenerated.');
                        }
                    }
                    if (array_key_exists('refresh_token', $config)) {
                        if ($config['refresh_token'] != $this->netatmo_client->getRefreshToken()) {
                            Logger::notice($this->facility, $this->service_name, null, null, null, null, 0, 'Refresh token has been updated.');
                        }
                    }
                }
            }
            catch (\Exception $ex) {
                switch ($ex->getCode()) {
                    case 2:
                    case 23:
                    case 32:
                        $this->last_netatmo_error = __('Wrong credentials. Please, verify your login and password.', 'live-weather-station');
                        Logger::critical($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), 'Wrong credentials. Please, verify your login and password.');
                        break;
                    case 5:
                    case 22:
                        $this->last_netatmo_error = __('Application deactivated. Please contact support.', 'live-weather-station');
                        Logger::emergency($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), 'Application deactivated. Please contact support.');
                        break;
                    case 20:
                        $this->last_netatmo_error = __('Too many users with this IP.', 'live-weather-station');
                        Logger::error($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), 'Too many users with this IP.');
                        break;
                    default:
                        $this->last_netatmo_warning = __('Temporary unable to contact Netatmo servers. Retry will be done shortly.', 'live-weather-station');
                        Logger::warning($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), 'Temporary unable to contact Netatmo servers. Retry will be done shortly.');
                }
                return array();
            }
        }
        return $this->netatmo_datas;
    }

    /**
     * Detect the Netatmo stations and, optionaly, store them in the stations table.
     *
     * @param boolean $store Optional. Store the found stations in the stations table.
     * @return array An array containing stations details.
     *
     * @since 3.0.0
     */
    protected function __get_stations($store=false){
        $result = array();
        try {
            $this->get_datas(false);
            $datas = $this->netatmo_datas ;
            foreach($datas['devices'] as $device){
                $result[] = array('device_id' => $device['_id'], 'station_name' => $device['station_name'], 'installed' => false);
            }
            if ($store) {
                foreach ($result as &$station) {
                    if ($this->insert_ignore_stations_table($station['device_id'])) {
                        $station['installed'] = true;
                        Logger::notice($this->facility, $this->service_name, $station['device_id'], $station['station_name'], null, null, null, 'Station added.');
                    }
                    else {
                        Logger::notice($this->facility, $this->service_name, $station['device_id'], $station['station_name'], null, null, null, 'This station was already added.');
                    }
                }
            }
            else {
                foreach ($this->get_all_netatmo_stations() as $item) {
                    foreach ($result as &$station) {
                        if ($item['station_id'] == $station['device_id']) {
                            $station['installed'] = true;
                        }
                    }
                }
            }
            Logger::info('Backend', $this->service_name, null, null, null, null, 0, 'Job done: detecting stations.');
        }
        catch (\Exception $ex) {
            Logger::critical('Backend', $this->service_name, null, null, null, null, $ex->getCode(), 'Error while detecting stations: ' . $ex->getMessage());
            return array();
        }
        return $result;
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.0.0
     */
    protected function __run($system){
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_datas();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute();
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute();
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
    }
}