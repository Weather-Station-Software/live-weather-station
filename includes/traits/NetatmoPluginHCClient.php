<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Netatmo\Clients\NAWSApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;

/**
 * Netatmo HC client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
trait HCClient {

    use BaseClient;

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
                $config['scope'] = $this->netatmo_scope;
                $config['refresh_token'] = $refresh_token;
                $config['access_token'] = $access_token;
                $this->netatmo_client = new NAWSApiClient($config);
                $this->netatmo_client->getAccessTokenFromRefreshToken();

                //$store = array();
                //$store['refresh_token'] = $refresh_token;
                //$store['access_token'] = $access_token;
                //$this->netatmo_client->setTokensFromStore($store);
            }
            try {
                $this->netatmo_datas = $this->netatmo_client->getHCData();
                $this->normalize_netatmo_datas();
                if ($store) {
                    $this->store_netatmo_datas($this->get_all_netatmo_hc_stations(), true);
                }
                update_option('live_weather_station_netatmo_refresh_token', $this->netatmo_client->getRefreshToken());
                update_option('live_weather_station_netatmo_access_token', $this->netatmo_client->getAccessToken()['access_token']);
                update_option('live_weather_station_netatmo_connected', 1);
                if (isset($config)) {
                    if (array_key_exists('access_token', $config)) {
                        if ($config['access_token'] != $this->netatmo_client->getAccessToken()['access_token']) {
                            Logger::notice('Authentication', $this->service_name, null, null, null, null, 0, 'Access token has been regenerated for following scope: '.$this->netatmo_client->getVariable('scope'));
                        }
                    }
                    if (array_key_exists('refresh_token', $config)) {
                        if ($config['refresh_token'] != $this->netatmo_client->getRefreshToken()) {
                            Logger::notice('Authentication', $this->service_name, null, null, null, null, 0, 'Refresh token has been updated for following scope: '.$this->netatmo_client->getVariable('scope'));
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
                        Logger::critical('Authentication', $this->service_name, null, null, null, null, $ex->getCode(), 'Wrong credentials. Please, verify your login and password.');
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
                Logger::critical($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), $ex->getMessage());
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
                    if ($this->insert_ignore_stations_table($station['device_id'], LWS_NETATMOHC_SID)) {
                        $station['installed'] = true;
                        Logger::notice($this->facility, $this->service_name, $station['device_id'], $station['station_name'], null, null, null, 'Station added.');
                    }
                    else {
                        Logger::notice($this->facility, $this->service_name, $station['device_id'], $station['station_name'], null, null, null, 'This station was already added.');
                    }
                }
            }
            else {
                foreach ($this->get_all_netatmo_hc_stations() as $item) {
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
            $weather->compute(LWS_NETATMOHC_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_NETATMOHC_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
    }
}