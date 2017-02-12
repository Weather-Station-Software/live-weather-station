<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Netatmo\Clients\NAWSApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;

/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
    protected $netatmo_datas;

    protected $netatmo_scope = 'read_station read_homecoach';

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
     * @since 3.1.0
     */
    public function authentication($login, $password) {
        $config = array();
        $this->last_netatmo_error = '';
        $config['client_id'] = $this->client_id;
        $config['client_secret'] = $this->client_secret;
        $config['scope'] = $this->netatmo_scope;
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
     * @param array $stations The station list.
     * @param boolean $is_hc Optional. True if it's a healthy home coach.
     * @since 3.1.0
     */
    private function store_netatmo_datas($stations, $is_hc=false) {
        $datas = $this->netatmo_datas ;
        foreach($datas['devices'] as $device){
            $store = false;
            foreach ($stations as $station) {
                if ($station['station_id'] == $device['_id']) {
                    $store = true;
                }
            }
            if ($store) {
                $this->get_netatmo_dashboard($device['_id'], $device['station_name'], $device['_id'], $device['module_name'],
                    $device['type'], $device['data_type'], $device['dashboard_data'], $device['place'], $device['wifi_status'], $device['firmware'], $device['last_status_store'], 0, $device['date_setup'], $device['last_setup'], $device['last_upgrade'], $is_hc);
                Logger::debug($this->facility, $this->service_name, $device['_id'], $device['station_name'], $device['_id'], $device['module_name'], 0, 'Success while collecting module records.');
                foreach($device['modules'] as $module)
                {
                    $this->get_netatmo_dashboard($device['_id'], $device['station_name'], $module['_id'], $module['module_name'],
                        $module['type'], $module['data_type'], $module['dashboard_data'], $device['place'], $module['rf_status'], $module['firmware'], $module['last_seen'], $module['battery_vp'], null, $module['last_setup'], null, $is_hc);
                    Logger::debug($this->facility, $this->service_name, $device['_id'], $device['station_name'], $module['_id'], $module['module_name'], 0, 'Success while collecting module records.');
                }
            }
        }
    }

    /**
     * Corrects station's datas.
     *
     * @since 2.3.0
     */
    private function normalize_netatmo_datas() {
        $datas = $this->netatmo_datas ;
        $d = $datas;
        unset($d['devices']);
        Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, print_r($d, true));
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
            if (isset($device['name'])) {
                $device['station_name'] = $device['name'];
                $device['module_name'] = $device['name'];
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
}