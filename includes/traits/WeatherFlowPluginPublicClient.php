<?php

namespace WeatherStation\SDK\WeatherFlow\Plugin;

use WeatherStation\SDK\WeatherFlow\Exception;
use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\WeatherFlow\WFLWApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\Unit\Conversion;


/**
 * WeatherFlow station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
trait PublicClient {

    use BaseClient, Conversion;

    protected $facility = 'Weather Collector';
    public $detected_station_name = '';
    private static $dev_key = '42f82f28-44c8-4866-921d-315f53c7bd39';

    /**
     * Verify if a station is accessible.
     *
     * @param string $id The station ID.
     * @return string The error message, empty string otherwise.
     * @since 3.3.0
     */
    public function test_station($id) {
        $result = 'unknown station ID';
        try {
            $wflw = new WFLWApiClient();
            Quota::verify(self::$service, 'GET');
            $raw_data = $wflw->getRawPublicStationData($id, self::$dev_key);
            $weather = json_decode($raw_data, true);
            if (is_array($weather)) {
                if (array_key_exists('status', $weather)) {
                    if (array_key_exists('status_code', $weather['status'])) {
                        if ($weather['status']['status_code'] == 0) {
                            $result = '';
                            if (array_key_exists('public_name', $weather)) {
                                $this->detected_station_name = $weather['public_name'];
                            }
                            elseif (array_key_exists('station_name', $weather)) {
                                $this->detected_station_name = $weather['station_name'];
                            }
                        }
                        else {
                            if (array_key_exists('status_message', $weather['status'])) {
                                $result = $weather['status']['status_message'];
                            }
                        }
                    }
                }
                else {
                    $result = 'WeatherFlow servers have returned empty response';
                }
            }
            else {
                $result = 'internal WeatherFlow error';
            }
        }
        catch(\Exception $ex)
        {
            $result = 'unable to contact WeatherFlow servers';
        }
        return $result;
    }

    /**
     * Get the devices attached to a station.
     *
     * @param string $id The station ID.
     * @return array The devices.
     * @since 3.7.0
     */
    public function get_devices($id) {
        $result = array();
        try {
            $wflw = new WFLWApiClient();
            $this->devices = array();
            Quota::verify(self::$service, 'GET');
            $raw_data = $wflw->getRawPublicStationMeta($id, self::$dev_key);
            $data = json_decode($raw_data, true);
            if (is_array($data)) {
                if (array_key_exists('status', $data)) {
                    if (array_key_exists('status_code', $data['status'])) {
                        if ($data['status']['status_code'] == 0) {
                            if (array_key_exists('stations', $data)) {
                                if (array_key_exists('devices', $data['stations'])) {
                                    foreach ($data['stations']['devices'] as $device) {
                                        if (array_key_exists('serial_number', $device)) {
                                            if ($device['device_type'] === 'AR' ||
                                                $device['device_type'] === 'SK') {
                                                if (array_key_exists('device_id', $device)) {
                                                    $device_id = $device['device_id'];
                                                    if (array_key_exists('device_meta', $device) &&
                                                        array_key_exists('serial_number', $device)) {
                                                        if (array_key_exists('environment', $device['device_meta'])) {
                                                            if ($device['device_meta']['environment'] === 'indoor') {
                                                                $result['indoor'] = $device_id;
                                                            }
                                                            else {
                                                                $result['outdoor'] = $device_id;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        catch(\Exception $ex) {
            $result = array();
        }
        return $result;
    }

    /**
     * Format and store data.
     *
     * @param string $json_weather Weather array json formatted.
     * @param array $station Station array.
     * @throws \Exception
     * @since 3.3.0
     */
    private function format_and_store($json_weather, $station) {
        $weather = json_decode($json_weather, true);
        if (is_array($weather)) {
            if (array_key_exists('status', $weather)) {
                if (array_key_exists('status_code', $weather['status'])) {
                    if ($weather['status']['status_code'] != 0) {
                        if (array_key_exists('status_message', $weather['status'])) {
                            throw new \Exception($weather['status']['status_message'], $weather['status']['status_code']);
                        }
                        else {
                            throw new \Exception('WeatherFlow unknown exception', 0);
                        }
                    }
                }
            }
            else {
                throw new \Exception('WeatherFlow unknown exception');
            }
        }
        else {
            throw new \Exception('JSON / '.(string)$json_weather);
        }
        Logger::debug($this->facility, $this->service_name, null, null, null, null, null, print_r($weather, true));
        if (!empty($weather) && array_key_exists('obs', $weather) && is_array($weather['obs'])) {
            if (array_key_exists('timezone', $weather)) {
                $timezone = $weather['timezone'];
            }
            else {
                $timezone = $this->get_timezone($station, null, $station['guid'], $station['station_id']);
            }
            if (array_key_exists(0, $weather['obs']) && is_array($weather['obs'][0])) {
                $observation = $weather['obs'][0];
                if (is_null($observation)) {
                    $observation = array();
                }
                if (array_key_exists('public_name', $weather)) {
                    $station['station_name'] = $weather['public_name'];
                } elseif (array_key_exists('station_name', $weather)) {
                    $station['station_name'] = $weather['station_name'];
                }
                if ($station['station_name'] == '') {
                    $station['station_name'] = '< NO NAME >';
                }
                if (array_key_exists('timestamp', $observation)) {
                    try {
                        $timestamp = date('Y-m-d H:i:s', $observation['timestamp']);
                    } catch (Exception $e) {
                        $timestamp = date('Y-m-d H:i:s');
                    }
                } else {
                    $timestamp = date('Y-m-d H:i:s');
                }
                if (array_key_exists('lightning_strike_last_epoch', $observation)) {
                    try {
                        $strikestamp = date('Y-m-d H:i:s', $observation['lightning_strike_last_epoch']);
                    } catch (Exception $e) {
                        $strikestamp = date('Y-m-d H:i:s');
                    }
                } else {
                    $strikestamp = date('Y-m-d H:i:s');
                }
                $pressure_ref = null;
                $temperature_ref = null;
                $humidity_ref = null;

                // NAMain
                $type = 'NAMain';
                $updates['device_id'] = $station['station_id'];
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $station['station_id'];
                $updates['module_type'] = $type;
                $updates['module_name'] = $this->get_fake_module_name($type);
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'last_refresh';
                $updates['measure_value'] = date('Y-m-d H:i:s');
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates, $timezone);
                $updates['measure_timestamp'] = $timestamp;
                $updates['measure_type'] = 'loc_city';
                $updates['measure_value'] = $station['loc_city'];
                $this->update_data_table($updates, $timezone);
                $updates['measure_type'] = 'loc_country';
                $updates['measure_value'] = $station['loc_country_code'];
                $this->update_data_table($updates, $timezone);
                if (array_key_exists('timezone', $weather)) {
                    $station['loc_timezone'] = $weather['timezone'];
                    $updates['measure_type'] = 'loc_timezone';
                    $updates['measure_value'] = $station['loc_timezone'];
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('latitude', $weather)) {
                    $station['loc_latitude'] = $weather['latitude'];
                    $updates['measure_type'] = 'loc_latitude';
                    $updates['measure_value'] = $station['loc_latitude'];
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('longitude', $weather)) {
                    $station['loc_longitude'] = $weather['longitude'];
                    $updates['measure_type'] = 'loc_longitude';
                    $updates['measure_value'] = $station['loc_longitude'];
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('elevation', $weather)) {
                    $station['loc_altitude'] = $weather['elevation'];
                    $updates['measure_type'] = 'loc_altitude';
                    $updates['measure_value'] = $station['loc_altitude'];
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('station_pressure_indoor', $observation)) {
                    $updates['measure_type'] = 'pressure';
                    $pressure_ref = $observation['station_pressure_indoor'];
                    $updates['measure_value'] = $pressure_ref;
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('station_pressure', $observation)) {
                    $updates['measure_type'] = 'pressure';
                    $pressure_ref = $observation['station_pressure'];
                    $updates['measure_value'] = $pressure_ref;
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('sea_level_pressure_indoor', $observation)) {
                    $updates['measure_type'] = 'pressure_sl';
                    $updates['measure_value'] = $observation['sea_level_pressure_indoor'];
                    $this->update_data_table($updates, $timezone);
                }
                if (array_key_exists('sea_level_pressure', $observation)) {
                    $updates['measure_type'] = 'pressure_sl';
                    $updates['measure_value'] = $observation['sea_level_pressure'];
                    $this->update_data_table($updates, $timezone);
                }
                $station['last_refresh'] = date('Y-m-d H:i:s');
                $station['last_seen'] = $timestamp;
                $this->update_table(self::live_weather_station_stations_table(), $station);
                Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');


                // NAModule1
                if (array_key_exists('air_temperature', $observation) || array_key_exists('relative_humidity', $observation)) {
                    $type = 'NAModule1';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 1);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('air_temperature', $observation)) {
                        $updates['measure_type'] = 'temperature';
                        $temperature_ref = $observation['air_temperature'];
                        $updates['measure_value'] = $temperature_ref;
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('relative_humidity', $observation)) {
                        $updates['measure_type'] = 'humidity';
                        $humidity_ref = $observation['relative_humidity'];
                        $updates['measure_value'] = $humidity_ref;
                        $this->update_data_table($updates, $timezone);
                    }
                    if (isset($temperature_ref) && isset($pressure_ref) && isset($humidity_ref)) {
                        $updates['measure_type'] = 'absolute_humidity';
                        $updates['measure_value'] = $this->compute_partial_absolute_humidity($temperature_ref, 100 * $pressure_ref, $humidity_ref);
                        $this->update_data_table($updates, $timezone);
                        $temperature_ref = null;
                        $humidity_ref = null;
                    }
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }

                // NAModule2
                if (array_key_exists('wind_direction', $observation) || array_key_exists('wind_avg', $observation) || array_key_exists('wind_gust', $observation)) {
                    $type = 'NAModule2';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 2);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('wind_direction', $observation)) {
                        $updates['measure_type'] = 'windangle';
                        $updates['measure_value'] = $observation['wind_direction'];
                        $this->update_data_table($updates, $timezone);
                        $updates['measure_type'] = 'winddirection';
                        $updates['measure_value'] = (int)floor(($observation['wind_direction'] + 180) % 360);
                        $this->update_data_table($updates, $timezone);
                        $updates['measure_type'] = 'gustangle';
                        $updates['measure_value'] = $observation['wind_direction'];
                        $this->update_data_table($updates, $timezone);
                        $updates['measure_type'] = 'gustdirection';
                        $updates['measure_value'] = (int)floor(($observation['wind_direction'] + 180) % 360);
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('wind_avg', $observation)) {
                        $updates['measure_type'] = 'windstrength';
                        $updates['measure_value'] = $this->get_reverse_wind_speed($observation['wind_avg'], 2);
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('wind_gust', $observation)) {
                        $updates['measure_type'] = 'guststrength';
                        $updates['measure_value'] = $this->get_reverse_wind_speed($observation['wind_gust'], 2);
                        $this->update_data_table($updates, $timezone);
                    }
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }

                // NAModule3
                if (array_key_exists('precip', $observation) || array_key_exists('precip_accum_last_1hr', $observation) || array_key_exists('precip_accum_local_day', $observation) || array_key_exists('precip_accum_local_yesterday', $observation)) {
                    $type = 'NAModule3';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 3);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('precip', $observation)) {
                        $updates['measure_type'] = 'rain';
                        $updates['measure_value'] = $observation['precip'];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('precip_accum_last_1hr', $observation)) {
                        $updates['measure_type'] = 'rain_hour_aggregated';
                        $updates['measure_value'] = $observation['precip_accum_last_1hr'];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('precip_accum_local_day', $observation)) {
                        $updates['measure_type'] = 'rain_day_aggregated';
                        $updates['measure_value'] = $observation['precip_accum_local_day'];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('precip_accum_local_yesterday', $observation)) {
                        $updates['measure_type'] = 'rain_yesterday_aggregated';
                        $updates['measure_value'] = $observation['precip_accum_local_yesterday'];
                        $this->update_data_table($updates, $timezone);
                    }
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }

                // NAModule4
                if (array_key_exists('air_temperature_indoor', $observation) || array_key_exists('relative_humidity_indoor', $observation)) {
                    $type = 'NAModule4';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 4);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('air_temperature_indoor', $observation)) {
                        $updates['measure_type'] = 'temperature';
                        $temperature_ref = $observation['air_temperature_indoor'];
                        $updates['measure_value'] = $temperature_ref;
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('relative_humidity_indoor', $observation)) {
                        $updates['measure_type'] = 'humidity';
                        $humidity_ref = $observation['relative_humidity_indoor'];
                        $updates['measure_value'] = $humidity_ref;
                        $this->update_data_table($updates, $timezone);
                    }
                    if (isset($temperature_ref) && isset($pressure_ref) && isset($humidity_ref)) {
                        $updates['measure_type'] = 'absolute_humidity';
                        $updates['measure_value'] = $this->compute_partial_absolute_humidity($temperature_ref, 100 * $pressure_ref, $humidity_ref);
                        $this->update_data_table($updates, $timezone);
                    }
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }

                // NAModule5
                if (array_key_exists('uv', $observation) || array_key_exists('solar_radiation', $observation) || array_key_exists('brightness', $observation)) {
                    $type = 'NAModule5';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 5);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    if (array_key_exists('uv', $observation)) {
                        $updates['measure_type'] = 'uv_index';
                        $updates['measure_value'] = $observation['uv'];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('solar_radiation', $observation)) {
                        $updates['measure_type'] = 'irradiance';
                        $updates['measure_value'] = $observation['solar_radiation'];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('brightness', $observation)) {
                        $updates['measure_type'] = 'illuminance';
                        $updates['measure_value'] = $observation['brightness'];
                        $this->update_data_table($updates, $timezone);
                    }
                    $this->update_data_table($updates, $timezone);
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }

                // NAModule7
                if (array_key_exists('lightning_strike_count_last_3hr', $observation) || array_key_exists('lightning_strike_last_distance', $observation)) {
                    $type = 'NAModule7';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 7);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates, $timezone);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates, $timezone);
                    if (array_key_exists('lightning_strike_count_last_3hr', $observation)) {
                        $updates['measure_type'] = 'strike_count';
                        $updates['measure_value'] = $observation['lightning_strike_count_last_3hr'];
                        $this->update_data_table($updates, $timezone);
                    }
                    if (array_key_exists('lightning_strike_last_distance', $observation)) {
                        $updates['measure_type'] = 'strike_distance';
                        $updates['measure_value'] = $observation['lightning_strike_last_distance'] * 1000;
                        $updates['measure_timestamp'] = $strikestamp;
                        $this->update_data_table($updates, $timezone);
                    }
                    $this->update_data_table($updates, $timezone);
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }
            }
            else {
                Logger::notice($this->facility, $this->service_name, $station['station_id'], $station['station_name'], null, null, 0, 'Data are empty.');
            }
        }
        else {
            Logger::notice($this->facility, $this->service_name, $station['station_id'], $station['station_name'], null, null, 0, 'Data are empty or irrelevant.');
        }
    }

    /**
     * Get and store station's data.
     *
     * @since 3.3.0
     */
    public function get_and_store_data() {
        $this->synchronize_wflw_station();
        $stations = $this->get_all_wflw_id_stations();
        foreach ($stations as $st => $station) {
            $device_id = $station['station_id'];
            $device_name = $station['station_name'];
            try {
                $wflw = new WFLWApiClient();
                if (Quota::verify($this->service_name, 'GET')) {
                    $raw_data = $wflw->getRawPublicStationData($station['service_id'], self::$dev_key);
                    $this->format_and_store($raw_data, $station);
                    Logger::notice($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Data retrieved.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Quota manager has forbidden to retrieve data.');
                }
            }
            catch(\Exception $ex)
            {
                if (strpos($ex->getMessage(), 'JSON /') > -1) {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'WeatherFlow servers has returned empty response. Retry will be done shortly.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Temporary unable to contact WeatherFlow servers. Retry will be done shortly.');
                    return array();
                }
            }
        }
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.3.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$wflw_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_and_store_data();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_WFLW_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_WFLW_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}