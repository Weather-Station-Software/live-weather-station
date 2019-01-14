<?php

namespace WeatherStation\SDK\BloomSky\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\BloomSky\BSKYApiClient;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\Unit\Conversion;
use WeatherStation\Data\DateTime\Handling as DateTimeHandling;
use WeatherStation\Data\ID\Handling as ID;
use WeatherStation\Data\Type\Description as Description;


/**
 * Netatmo client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
trait BaseClient {

    use Dashboard_Manipulation, Conversion, Description, DateTimeHandling;

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
        $datas = $this->bloomsky_datas ;
        foreach($datas as &$device){
            if (!array_key_exists('device_id', $device)) {
                continue;
            }
            $store = false;
            foreach ($stations as $station) {
                if ($station['station_id'] == $device['device_id']) {
                    $guid = $station['guid'];
                    $store = true;
                }
            }
            if ($store) {
                $place = array();
                if (array_key_exists('place', $device)) {
                    $place = $device['place'];
                }
                // Main base
                $module_type = 'NAMain';
                $types = array('pressure', 'pressure_sl');
                $module_id = ID::get_fake_modulex_id($guid, 0);
                $module_name = $this->get_fake_module_name($module_type);
                $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');

                // Outdoor module
                $module_type = 'NAModule1';
                $types = array('temperature', 'humidity');
                $module_id = ID::get_fake_modulex_id($guid, 1);
                $module_name = $this->get_fake_module_name($module_type);
                $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');

                // Wind gauge
                if (array_key_exists('windangle', $device)) {
                    $module_type = 'NAModule2';
                    $types = array('windangle', 'gustangle', 'winddirection', 'gustdirection', 'windstrength', 'guststrength');
                    $module_id = ID::get_fake_modulex_id($guid, 2);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // Rain gauge
                if (array_key_exists('rain', $device)) {
                    $module_type = 'NAModule3';
                    $types = array('rain', 'rain_day_aggregated');
                    $module_id = ID::get_fake_modulex_id($guid, 3);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // Solar
                $module_type = 'NAModule5';
                $types = array('illuminance', 'uv_index');
                $module_id = ID::get_fake_modulex_id($guid, 5);
                $module_name = $this->get_fake_module_name($module_type);
                $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');

                // Picture
                $module_type = 'NAModuleP';
                $types = array();
                $module_id = ID::get_fake_modulex_id($guid, 'p');
                $module_name = $this->get_fake_module_name($module_type);
                $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');

                // Video
                $module_type = 'NAModuleV';
                $types = array();
                $module_id = ID::get_fake_modulex_id($guid, 'v');
                $module_name = $this->get_fake_module_name($module_type);
                $this->get_dashboard(LWS_BSKY_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');

                $s = $this->get_station_informations_by_station_id($device['device_id']);
                if (array_key_exists('device_name', $device)) {
                    $s['station_name'] = $device['device_name'];
                }
                if (array_key_exists('device_model', $device)) {
                    $s['station_model'] = $device['device_model'];
                }
                if (array_key_exists('city', $place)) {
                    $s['loc_city'] = $place['city'];
                }
                if (array_key_exists('country', $place)) {
                    $s['loc_country_code'] = $place['country'];
                }
                if (array_key_exists('timezone', $place)) {
                    $s['loc_timezone'] = $place['timezone'];
                }
                if (array_key_exists('altitude', $place)) {
                    $s['loc_altitude'] = $place['altitude'];
                }
                if (array_key_exists('location', $place)) {
                    if (count($place['location']) == 2) {
                        $s['loc_longitude'] = $place['location'][0];
                        $s['loc_latitude'] = $place['location'][1];
                    }
                }
                $s['last_refresh'] = date('Y-m-d H:i:s');
                if (array_key_exists('time_utc', $device)) {
                    $s['last_seen'] = date('Y-m-d H:i:s', $device['time_utc']);
                }
                $this->update_stations_table($s);
            }
        }
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
                $temperature = 15.0;
                $altitude = 0;
                $dat = array();
                $device_model = '';
                if (array_key_exists('DeviceName', $station)) {
                    $dat['device_name'] = $station['DeviceName'];
                }
                else {
                    $dat['device_name'] = '< NO NAME >';
                }
                if (array_key_exists('DeviceID', $station)) {
                    $dat['device_id'] = self::compute_unique_bsky_id($station['DeviceID']);
                    $st = $this->get_station_informations_by_station_id($dat['device_id']);
                    if (array_key_exists('loc_altitude', $st)) {
                        $altitude = $st['loc_altitude'];
                    }
                }
                else {
                    $dat['device_id'] = '00:00:00:00:00:00';
                }
                if (array_key_exists('VideoList', $station)) {
                    $videos = $station['VideoList'];
                    if (count($videos) > 0) {
                        $dat['video_imperial'] = array();
                        foreach ($videos as $video) {
                            $dat['video_imperial'][] = $video;
                        }

                    }
                }
                if (array_key_exists('VideoList_C', $station)) {
                    $videos = $station['VideoList_C'];
                    if (count($videos) > 0) {
                        $dat['video_metric'] = array();
                        foreach ($videos as $video) {
                            $dat['video_metric'][] = $video;
                        }

                    }
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
                        $dat['pressure_sl'] = $this->convert_from_baro_to_mslp($dat['pressure'], $altitude, $temperature);
                    }
                    if (array_key_exists('UVIndex', $data)) {
                        $dat['uv_index'] = $data['UVIndex'];
                    }
                    if (array_key_exists('ImageTS', $data)) {
                        $dat['TS_image'] = $data['ImageTS'];
                    }
                    if (array_key_exists('DeviceType', $data)) {
                        $device_model = 'BloomSky - ' . ucfirst($data['DeviceType']);
                    }
                    if (array_key_exists('Voltage', $data)) {
                        $dat['battery'] = $data['Voltage'];
                    }
                    if (array_key_exists('ImageTS', $data)) {
                        $dat['time_pct'] = $data['ImageTS'];
                    }
                    if (array_key_exists('ImageURL', $data)) {
                        $dat['url_pct'] = $data['ImageURL'];
                    }
                }
                if (array_key_exists('Storm', $station)) {
                    $data = $station['Storm'];
                    if ($device_model == '') {
                        $device_model = 'BloomSky - Storm';
                    }
                    else {
                        $device_model .= '+Storm';
                    }
                    if (array_key_exists('UVIndex', $data)) {
                        $dat['uv_index'] = $data['UVIndex'];
                    }
                    if (array_key_exists('WindDirection', $data)) {
                        $w = $data['WindDirection'];
                        if ($w == 9999) {
                            $w = 0;
                        }
                        $dat['windangle'] = $this->get_reverse_wind_angle_text($w);
                        $dat['gustangle'] = $dat['windangle'];
                        $dat['winddirection'] = (int)floor(($dat['windangle'] + 180) % 360);
                        $dat['gustdirection'] = $dat['winddirection'];
                    }
                    if (array_key_exists('SustainedWindSpeed', $data)) {
                        $dat['windstrength'] = $data['SustainedWindSpeed'];
                        if ($dat['windstrength'] == 9999) {
                            $dat['windstrength'] = 0;
                        }
                    }
                    if (array_key_exists('RainDaily', $data)) {
                        $dat['rain_day_aggregated'] = $data['RainDaily'];
                    }
                    if (array_key_exists('WindGust', $data)) {
                        $dat['guststrength'] = $data['WindGust'];
                        if ($dat['guststrength'] == 9999) {
                            $dat['guststrength'] = 0;
                        }
                    }
                    if (array_key_exists('RainRate', $data)) {
                        $dat['rain'] = $data['RainRate'];
                        if ($dat['rain'] == 9999) {
                            $dat['rain'] = 0;
                        }
                    }
                }
                if ($device_model != '') {
                    $dat['device_model'] = $device_model;
                }
                if (array_key_exists('FullAddress', $station)) {
                    if (strlen($station['FullAddress']) > 1) {
                        $dat['place']['country'] = strtoupper(substr($station['FullAddress'], -2));
                    }
                }
                if (array_key_exists('CityName', $station)) {
                    $dat['place']['city'] = $station['CityName'];
                }
                if (array_key_exists('ALT', $station)) {
                    $dat['place']['altitude'] = round($station['ALT']);
                }
                if (array_key_exists('LON', $station)) {
                    $dat['place']['location'][0] = round($station['LON'], 4);
                }
                if (array_key_exists('LAT', $station)) {
                    $dat['place']['location'][1] = round($station['LAT'], 4);
                }
                if (array_key_exists('place', $dat)) {
                    if (array_key_exists('UTC', $station) && array_key_exists('country', $dat['place'])) {
                        $dat['place']['timezone'] = $this->get_probable_timezone($dat['place']['country'], (integer)$station['UTC']);
                    }
                    else {
                        $dat['place']['timezone'] = 'UTC';
                    }
                }
                if (count($dat) > 3) {
                    $result[] = $dat;
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $dat['device_id'], $dat['device_name'], null, null, 900, 'No module found for this station.');
                }
            }
        }
        $this->bloomsky_datas = $result;
    }
}