<?php

/**
 * OpenWeatherMap base client for Live Weather Station plugin
 *
 * @since      2.7.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Exception.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/CurrentWeather.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Fetcher/FetcherInterface.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Fetcher/CurlFetcher.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Fetcher/FileGetContentsFetcher.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/City.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/Sun.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/Temperature.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/Time.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/Unit.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/Weather.php');
require_once(LWS_INCLUDES_DIR. 'owm_api/Cmfcmf/OpenWeatherMap/Util/Wind.php');
require_once(LWS_INCLUDES_DIR. 'trait-dashboard-manipulation.php');
require_once(LWS_INCLUDES_DIR. 'trait-id-manipulation.php');

use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;


trait Owm_Client {

    use Dashboard_Manipulation, Id_Manipulation;

    protected $service_name = 'OpenWeatherMap';
    public $last_owm_error = '';
    public $last_owm_warning = '';


    /**
     * Get station's datas.
     *
     * @return  boolean     True if connection is established.
     * @since    2.8.0
     */
    public function authentication() {
        $owm = new OpenWeatherMap();
        try {
            $raw_data = $owm->getRawWeatherData(6455259, 'metric', 'en', get_option('live_weather_station_owm_account')[0], 'json');
            $weather = json_decode($raw_data, true);
            if (!is_array($weather)) {
                throw new Exception('JSON / '.(string)$raw_data);
            }
            if (array_key_exists('cod', $weather) && $weather['cod'] != 200) {
                if (array_key_exists('message', $weather)) {
                    throw new Exception($weather['message']);
                }
                else {
                    throw new Exception('OpenWeatherMap unknown exception');
                }
            }
            return true;
        }
        catch(Exception $ex)
        {
            if (strpos($ex->getMessage(), 'Invalid API key') > -1) {
                $this->last_owm_error = __('Wrong OpenWeatherMap API key.', 'live-weather-station');
                return false;
            }
            if (strpos($ex->getMessage(), 'JSON /') > -1) {
                $this->last_owm_warning = __('OpenWeatherMap servers have returned empty response for some weather stations. Retry will be done shortly.', 'live-weather-station');
                return false;
            }
            else {
                $this->last_owm_warning = __('Temporary unable to contact OpenWeatherMap servers. Retry will be done shortly.', 'live-weather-station');
                return false;
            }
        }
    }

    /**
     * Synchronize main table with station table.
     *
     * @since    2.7.0
     */
    protected function synchronize_owm() {
        $list = array();
        $stations = $this->get_all_owm_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_owm_id($station['station_id']);
                $updates = array() ;
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $device_id;
                $updates['module_type'] = 'NAMain';
                $updates['module_name'] = $station['station_name'];
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'loc_altitude';
                $updates['measure_value'] = $station['loc_altitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_latitude';
                $updates['measure_value'] = $station['loc_latitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_longitude';
                $updates['measure_value'] = $station['loc_longitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_timezone';
                $updates['measure_value'] = $station['loc_timezone'];
                $this->update_data_table($updates);
                $list[] = $device_id;
            }
            $this->clean_owm_from_table($list);
        }
    }

    /**
     * Store station's datas.
     *
     * @param   array   $datas   OWM collected datas
     * @since    2.7.0
     */
    private function store_owm_datas($datas) {
        foreach ($datas as $data) {
            $this->get_dashboard($data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data']);
        }
    }
}