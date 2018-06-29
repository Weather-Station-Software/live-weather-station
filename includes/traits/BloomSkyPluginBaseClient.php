<?php

namespace WeatherStation\SDK\BloomSky\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\BloomSky\BSKYApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\Unit\Conversion;


/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
trait BaseClient {

    use Dashboard_Manipulation, Conversion;

    public $last_bloomsky_error = '';

    protected $bloomsky_client = null;
    protected $bloomsky_datas = array();


    protected $facility = 'Weather Collector';
    protected $service_name = 'BloomSky';



    /**
     * Store station's datas.
     *
     * @param array $stations The station list.
     * @since 3.6.0
     */
    private function store_bloomsky_datas($stations) {
        /*$datas = $this->netatmo_datas ;
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
        }*/
    }

    /**
     * Corrects station's datas.
     *
     * @since 3.6.0
     */
    private function normalize_bloomsky_datas() {
        $result = array();
        Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, print_r($this->bloomsky_datas, true));
        foreach($this->bloomsky_datas as $station) {
            if (is_array($station)) {
                $dat = array();
                $device_model = '';
                if (array_key_exists('DeviceName', $station)) {
                    $dat['device_name'] = $station['DeviceName'];
                }
                else {
                    $dat['device_name'] = '< NO NAME >';
                }
                if (array_key_exists('DeviceID', $station)) {
                    $dat['device_id'] = $station['DeviceID'];
                }
                else {
                    $dat['device_id'] = '00:00:00:00:00:00';
                }
                if (array_key_exists('Data', $station)) {
                    $data = $station['Data'];
                    if (array_key_exists('TS', $data)) {
                        $dat['time_utc'] = $data['TS'];
                    }
                    if (array_key_exists('Luminance', $data)) {
                        $dat['illuminance'] = $data['Luminance'];
                    }
                    if (array_key_exists('Temperature', $data)) {
                        $dat['temperature'] = $data['Temperature'];
                    }
                    if (array_key_exists('Humidity', $data)) {
                        $dat['humidity'] = $data['Humidity'];
                    }
                    if (array_key_exists('Pressure', $data)) {
                        $dat['pressure'] = $data['Pressure'];
                    }
                    if (array_key_exists('UVIndex', $data)) {
                        $dat['uv_index'] = $data['UVIndex'];
                    }
                    if (array_key_exists('ImageTS', $data)) {
                        $dat['TS_image'] = $data['ImageTS'];
                    }
                    if (array_key_exists('DeviceType', $data)) {
                        $device_model = ucfirst($data['DeviceType']);
                    }
                    if (array_key_exists('Voltage', $data)) {
                        $dat['battery'] = $data['Voltage'];
                    }
                }
                if (array_key_exists('Storm', $station)) {
                    $data = $station['Storm'];
                    if ($device_model == '') {
                        $device_model = 'Storm';
                    }
                    else {
                        $device_model .= '+Storm';
                    }
                    if (array_key_exists('UVIndex', $data)) {
                        $dat['uv_index'] = $data['UVIndex'];
                    }
                    if (array_key_exists('WindDirection', $data)) {
                        $dat['windangle'] = $this->get_reverse_wind_angle_text($data['WindDirection']);
                        $dat['gustangle'] = $dat['windangle'];
                    }
                    if (array_key_exists('SustainedWindSpeed', $data)) {
                        $dat['windstrength'] = $data['SustainedWindSpeed'];
                    }
                    if (array_key_exists('RainDaily', $data)) {
                        $dat['rain_day_aggregated'] = $data['RainDaily'];
                    }
                    if (array_key_exists('WindGust', $data)) {
                        $dat['guststrength'] = $data['WindGust'];
                    }
                    if (array_key_exists('RainRate', $data)) {
                        $dat['rain'] = $data['RainRate'];
                    }
                }
                if ($device_model != '') {
                    $dat['device_model'] = $device_model;
                }
                if (array_key_exists('FullAddress', $data)) {
                    if (strlen($data['FullAddress']) > 1) {
                        $dat['place']['country'] = strtoupper(substr($data['FullAddress'], -2));
                    }
                }
                if (array_key_exists('CityName', $data)) {
                    $dat['place']['city'] = $data['CityName'];
                }
                if (array_key_exists('ALT', $data)) {
                    $dat['place']['altitude'] = (integer)$data['ALT'];
                }
                if (array_key_exists('LON', $data)) {
                    $dat['place']['location'][0] = (integer)$data['LON'];
                }
                if (array_key_exists('LAT', $data)) {
                    $dat['place']['location'][1] = (integer)$data['LAT'];
                }






                if (count($dat) > 2) {
                    $result[] = $dat;
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $dat['device_id'], $dat['device_name'], null, null, 900, 'No module found for this station.');
                }

            }


        }





        /*$datas = $this->netatmo_datas ;
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
                Logger::warning($this->facility, $this->service_name, null, null, null, null, 9, 'Station not found.');
                continue;
            }
            if (!isset($device['modules'])) {
                $device['modules'] = array();
            }
            if (count($device['modules']) > 0) {
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
            else {
                if ($this->netatmo_type == LWS_NETATMO_SID) {
                    Logger::warning($this->facility, $this->service_name, $device['_id'], $device['station_name'], null, null, 900, 'No module found for this station.');
                }
            }

        }
        $this->netatmo_datas = $datas;*/



        $this->bloomsky_datas = $result;
    }
}