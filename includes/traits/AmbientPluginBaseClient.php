<?php

namespace WeatherStation\SDK\Ambient\Plugin;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Ambient\AMBTApiClient;
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

    public $last_ambient_error = '';

    protected $ambient_client = null;
    protected $ambient_datas = array();


    protected $facility = 'Weather Collector';
    protected $service_name = 'Ambient';



    /**
     * Store station's datas.
     *
     * @param array $stations The station list.
     * @since 3.6.0
     */
    private function store_ambient_datas($stations) {
        $datas = $this->ambient_datas;
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
                $s = $this->get_station_informations_by_station_id($device['device_id']);
                $place = array();
                $place['city'] = $s['loc_city'] ;
                $place['country'] = $s['loc_country_code'];
                $place['timezone'] = $s['loc_timezone'];
                $place['altitude'] = $s['loc_altitude'];
                $place['location'] = array();
                $place['location'][0] = $s['loc_longitude'];
                $place['location'][1] = $s['loc_latitude'];
                $s['last_refresh'] = date('Y-m-d H:i:s');
                if (array_key_exists('time_utc', $device)) {
                    $s['last_seen'] = date('Y-m-d H:i:s', $device['time_utc']);
                }
                $device['device_name'] = $s['station_name'];

                // Main base
                $module_type = 'NAMain';
                $types = array('pressure', 'pressure_sl');
                $module_id = ID::get_fake_modulex_id($guid, 0);
                $module_name = $this->get_fake_module_name($module_type);
                $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place, true);
                Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');

                // Outdoor module
                if (array_key_exists('temperature', $device) || array_key_exists('humidity', $device)) {
                    $module_type = 'NAModule1';
                    $types = array('temperature', 'humidity');
                    $module_id = ID::get_fake_modulex_id($guid, 1);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // Indoor module
                if (array_key_exists('tempint', $device) || array_key_exists('humint', $device) || array_key_exists('co2', $device)) {
                    $tempint = null;
                    $humint = null;
                    if (array_key_exists('tempint', $device)) {
                        $device['temperature'] = $device['tempint'];
                        $tempint = $device['tempint'];
                    }
                    else {
                        unset($device['temperature']);
                    }
                    if (array_key_exists('humint', $device)) {
                        $device['humidity'] = $device['humint'];
                        $humint = $device['humint'];
                    }
                    else {
                        unset($device['humidity']);
                    }
                    $module_type = 'NAModule4';
                    $types = array('temperature', 'humidity');
                    $health = $this->compute_health_index($tempint, $humint, null, null);
                    foreach ($health as $key => $idx) {
                        $device[$key] = $idx;
                        $types[] = $key;
                    }
                    $module_id = ID::get_fake_modulex_id($guid, 4);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // Wind gauge
                if (array_key_exists('windangle', $device)) {
                    $module_type = 'NAModule2';
                    $types = array('windangle', 'gustangle', 'winddirection', 'gustdirection', 'windstrength', 'guststrength');
                    $module_id = ID::get_fake_modulex_id($guid, 2);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // Rain gauge
                if (array_key_exists('rain', $device)) {
                    $module_type = 'NAModule3';
                    $types = array('rain', 'rain_day_aggregated', 'rain_month_aggregated', 'rain_year_aggregated');
                    $module_id = ID::get_fake_modulex_id($guid, 3);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // Solar
                if (array_key_exists('uv_index', $device) || array_key_exists('irradiance', $device)) {
                    $module_type = 'NAModule5';
                    $types = array('irradiance', 'uv_index');
                    $module_id = ID::get_fake_modulex_id($guid, 5);
                    $module_name = $this->get_fake_module_name($module_type);
                    $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                    Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                }

                // NAModule9 - max 9 extra modules
                for ($i = 0; $i < 9; $i++) {
                    if (array_key_exists('temp' . $i . 'f', $device) || array_key_exists('humidity' . $i, $device)) {
                        if (array_key_exists('temp' . $i . 'f', $device)) {
                            $device['temperature'] = $device['temp' . $i . 'f'];
                        }
                        else {
                            unset($device['temperature']);
                        }
                        if (array_key_exists('humidity' . $i, $device)) {
                            $device['humidity'] = $device['humidity' . $i];
                        }
                        else {
                            unset($device['humidity']);
                        }
                        $module_type = 'NAModule9';
                        $module_id = ID::get_fake_modulex_id($guid, 9);
                        $module_name = $this->get_fake_module_name($module_type);
                        $this->get_dashboard(LWS_AMBT_SID, $device['device_id'], $device['device_name'], $module_id, $module_name, $module_type, $types, $device, $place);
                        Logger::debug($this->facility, $this->service_name, $device['device_id'], $device['device_name'], $module_id, $module_name, 0, 'Success while collecting module records.');
                    }
                }


                // Station full
                $this->update_stations_table($s);
            }
        }
    }

    /**
     * Corrects station's datas.
     *
     * @since 3.6.0
     */
    private function normalize_ambient_datas() {
        $result = array();
        Logger::debug('API / SDK', $this->service_name, null, null, null, null, 0, print_r($this->ambient_datas, true));
        foreach($this->ambient_datas as $station) {
            if (is_array($station)) {
                $temperature = 15.0;
                $altitude = 0;
                $dat = array();
                if (array_key_exists('info', $station)) {
                    if (array_key_exists('name', $station['info'])) {
                        $dat['fixed_device_name'] = $station['info']['name'];
                    }
                    else {
                        $dat['fixed_device_name'] = '< NO NAME >';
                    }
                    if (array_key_exists('location', $station['info'])) {
                        $dat['fixed_device_name'] .= ' (' . $station['info']['location'] . ')';
                    }
                }
                if (array_key_exists('macAddress', $station)) {
                    $dat['device_id'] = strtolower($station['macAddress']);
                    $st = $this->get_station_informations_by_station_id($dat['device_id']);
                    if (array_key_exists('loc_altitude', $st)) {
                        $altitude = $st['loc_altitude'];
                    }
                }
                else {
                    $dat['device_id'] = '00:00:00:00:00:00';
                }
                if (array_key_exists('lastData', $station)) {
                    $data = $station['lastData'];
                    if (array_key_exists('dateutc', $data)) {
                        $dat['time_utc'] = (int)round($data['dateutc'] / 1000, 0);
                        $dat['last_seen'] = (int)round($data['dateutc'] / 1000, 0);
                    }
                    if (array_key_exists('winddir', $data)) {
                        $dat['windangle'] = $data['winddir'];
                        $dat['winddirection'] = (int)floor(($data['winddir'] + 180) % 360);
                        $dat['gustangle'] = $data['winddir'];
                        $dat['gustdirection'] = (int)floor(($data['winddir'] + 180) % 360);
                    }
                    if (array_key_exists('windgustmph', $data)) {
                        $dat['guststrength'] = $this->get_reverse_wind_speed($data['windgustmph'], 1);
                    }
                    if (array_key_exists('windspeedmph', $data)) {
                        $dat['windstrength'] = $this->get_reverse_wind_speed($data['windspeedmph'], 1);
                    }
                    if (array_key_exists('humidity', $data)) {
                        $dat['humidity'] = $data['humidity'];
                    }
                    if (array_key_exists('humidityin', $data)) {
                        $dat['humint'] = $data['humidityin'];
                    }
                    for ($i=1; $i<10; $i++) {
                        if (array_key_exists('humidity' . $i, $data)) {
                            $dat['humidity' . $i] = $data['humidity' . $i];
                        }
                    }
                    if (array_key_exists('tempf', $data)) {
                        $dat['temperature'] = $this->get_reverse_temperature($data['tempf'], 1);
                        $temperature = $dat['temperature'];
                    }
                    if (array_key_exists('tempinf', $data)) {
                        $dat['tempint'] = $this->get_reverse_temperature($data['tempinf'], 1);
                    }
                    for ($i=1; $i<10; $i++) {
                        if (array_key_exists('temp' . $i . 'f', $data)) {
                            $dat['temperature'] = $this->get_reverse_temperature($data['temp' . $i . 'f'], 1);
                        }
                    }
                    if (array_key_exists('hourlyrainin', $data)) {
                        $dat['rain'] = $this->get_reverse_rain($data['hourlyrainin'], 1);
                    }
                    if (array_key_exists('dailyrainin', $data)) {
                        $dat['rain_day_aggregated'] = $this->get_reverse_rain($data['dailyrainin'], 1);
                    }
                    if (array_key_exists('monthlyrainin', $data)) {
                        $dat['rain_month_aggregated'] = $this->get_reverse_rain($data['monthlyrainin'], 1);
                    }
                    if (array_key_exists('yearlyrainin', $data)) {
                        $dat['rain_year_aggregated'] = $this->get_reverse_rain($data['yearlyrainin'], 1);
                    }
                    if (array_key_exists('baromabsin', $data)) {
                        $dat['pressure'] = $this->get_reverse_pressure($data['baromabsin'], 1);
                        $dat['pressure_sl'] = $this->convert_from_baro_to_mslp($dat['pressure'], $altitude, $temperature);
                    }
                    if (array_key_exists('co2', $data)) {
                        $dat['co2'] = $data['co2'];
                    }
                    if (array_key_exists('uv', $data)) {
                        $dat['uv_index'] = $data['uv'];
                    }
                    if (array_key_exists('solarradiation', $data)) {
                        $dat['irradiance'] = $data['solarradiation'];
                    }
                }
                if (count($dat) > 1) {
                    $result[] = $dat;
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $dat['device_id'], $dat['device_name'], null, null, 900, 'No module found for this station.');
                }
            }
        }
        $this->ambient_datas = $result;
    }
}