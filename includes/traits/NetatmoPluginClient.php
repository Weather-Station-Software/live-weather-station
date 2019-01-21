<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Netatmo\Clients\NAWSApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;

/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Client {
    
    use BaseClient;

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // these API keys are property of Netatmo licensed to Pierre Lannoy, you CAN'T use them for your apps.
    // If you are thinking to develop something, get your API Keys here: https://dev.netatmo.com
    private $client_id = '561695d4cce37cd35c8b4659';
    private $client_secret = 'yfavTSFLnq5hzJxgMYBkfZdvaX04wx4WFLtqsChm8RGuv';
    //////////////////////////////////////////////////////////////////////////////////////////////////////

    protected $netatmo_scope = 'read_station';
    protected $netatmo_type = LWS_NETATMO_SID;
    public $available_types = array('NAMain' => array('Temperature', 'CO2', 'Humidity', 'Pressure', 'Noise'),
                                    'NAModule1' => array('Temperature', 'Humidity'),
                                    'NAModule2' => array('WindStrength', 'WindAngle', 'Guststrength', 'GustAngle'),
                                    'NAModule3' => array('Rain', 'Sum_Rain'),
                                    'NAModule4' => array('Temperature', 'CO2', 'Humidity'));


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
            Quota::verify($this->service_name, 'GET');
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
     * Get station's (old) measures.
     *
     * @param string $device_id The device_id.
     * @param string $module_id Optional. If specified will retrieve the module's measurements, else it will retrieve the main device's measurements
     * @param string $scale : interval of time between two measurements. Allowed values : max, 30min, 1hour, 3hours, 1day, 1week, 1month
     * @param array $type : type of measurements you wanna retrieve. Ex : "Temperature, CO2, Humidity".
     * @param integer $start Optional. Starting timestamp of requested measurements
     * @param integer $end Optional. Ending timestamp of requested measurements.
     * @param int $limit Optional. Limits numbers of measurements returned (default & max : 1024)
     * @param bool $optimize Optional. Optimize the bandwith usage if true. Optimize = FALSE enables an easier result parsing
     * @param bool $realtime Optional. Remove time offset (+scale/2) for scale bigger than max
     *
     * @return boolean True if it was a success, false otherwise.
     * @since 3.7.0
     */
    public function get_measures($device_id, $module_id, $scale, $type, $start = null, $end = null, $limit = null, $optimize = null, $realtime = null) {
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
            }
            try {
                if (Quota::verify($this->service_name, 'GET', true)) {
                    if (true) {
                        //if (isset($this->netatmo_datas)) {
                        $this->netatmo_datas = $this->netatmo_client->getMeasure($device_id, $module_id, $scale, implode(',', $type), $start, $end, $limit, $optimize, $realtime);
                        $this->normalize_netatmo_historical_datas($type);
                        update_option('live_weather_station_netatmo_refresh_token', $this->netatmo_client->getRefreshToken());
                        update_option('live_weather_station_netatmo_access_token', $this->netatmo_client->getAccessToken()['access_token']);
                        update_option('live_weather_station_netatmo_connected', 1);
                        if (isset($config)) {
                            if (array_key_exists('access_token', $config)) {
                                if ($config['access_token'] != $this->netatmo_client->getAccessToken()['access_token']) {
                                    Logger::notice('Authentication', $this->service_name, null, null, null, null, 0, 'Access token has been regenerated for following scope: ' . $this->netatmo_client->getVariable('scope'));
                                }
                            }
                            if (array_key_exists('refresh_token', $config)) {
                                if ($config['refresh_token'] != $this->netatmo_client->getRefreshToken()) {
                                    Logger::notice('Authentication', $this->service_name, null, null, null, null, 0, 'Refresh token has been updated for following scope: ' . $this->netatmo_client->getVariable('scope'));
                                }
                            }
                        }
                    }
                    else {
                        Logger::warning($this->facility, $this->service_name, null, null, null, null, 543, 'Empty response from Netatmo servers. Retry will be done shortly.');
                    }
                }
                else {
                    return false;
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
                        Logger::alert($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), 'Application deactivated. Please contact support.');
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
                return false;
            }
        }
        return true;
    }

    /**
     * Get station's datas.
     *
     * @param string $device_id The device_id.
     * @param string $module_id Optional. If specified will retrieve the module's measurements, else it will retrieve the main device's measurements
     *
     * @since 3.7.0
     */
    public function get_oldest_measure($device_id, $module_id, $module_type) {
        $this->get_measures($device_id, $module_id, '30min', $this->available_types[$module_type], null, null, 1, false);
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
                $config['scope'] = $this->netatmo_scope;
                $config['refresh_token'] = $refresh_token;
                $config['access_token'] = $access_token;
                $this->netatmo_client = new NAWSApiClient($config);
            }
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    $this->netatmo_datas = $this->netatmo_client->getData();
                    if (true) {
                    //if (isset($this->netatmo_datas)) {
                        $this->normalize_netatmo_datas(LWS_NETATMO_SID);
                        if ($store) {
                            $this->store_netatmo_datas($this->get_all_netatmo_stations());
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
                        Logger::notice($this->facility, $this->service_name, null, null, null, null, 0, 'Data retrieved.');

                    }
                    else {
                        Logger::warning($this->facility, $this->service_name, null, null, null, null, 543, 'Empty response from Netatmo servers. Retry will be done shortly.');
                    }
                }
                else {
                    Logger::warning($this->facility, $this->service_name, null, null, null, null, 0, 'Quota manager has forbidden to retrieve data.');
                    return array ();
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
                        Logger::alert($this->facility, $this->service_name, null, null, null, null, $ex->getCode(), 'Application deactivated. Please contact support.');
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
            if (true) {
                //if (isset($this->netatmo_datas)) {
                $datas = $this->netatmo_datas ;
                foreach($datas['devices'] as $device){
                    $result[] = array('device_id' => $device['_id'], 'station_name' => $device['station_name'], 'installed' => false);
                }
                if ($store) {
                    foreach ($result as &$station) {
                        if ($this->insert_ignore_stations_table($station['device_id'], LWS_NETATMO_SID)) {
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
            else {
                Logger::warning($this->facility, $this->service_name, null, null, null, null, 543, 'Empty response from Netatmo servers. Retry will be done shortly.');
            }
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
        $cron_id = Watchdog::init_chrono(Watchdog::$netatmo_update_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_datas();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_NETATMO_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_NETATMO_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}