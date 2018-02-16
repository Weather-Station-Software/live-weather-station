<?php

namespace WeatherStation\SDK\WeatherFlow\Plugin;

use WeatherStation\SDK\WeatherFlow\Exception;
use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\WeatherFlow\WFLWApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;


/**
 * WeatherFlow station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
trait PublicClient {

    use BaseClient;

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
        $wflw = new WFLWApiClient();
        try {
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
     * Format and store data.
     *
     * @param string $json_weather Weather array json formated.
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
                            throw new \Exception($weather['status']['status_message']);
                        }
                        else {
                            throw new \Exception('WeatherFlow unknown exception');
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
                $this->update_data_table($updates);
                $updates['measure_type'] = 'last_seen';
                $updates['measure_value'] = $timestamp;
                $updates['measure_timestamp'] = $timestamp;
                $this->update_data_table($updates);
                $updates['measure_timestamp'] = $timestamp;
                $updates['measure_type'] = 'loc_city';
                $updates['measure_value'] = $station['loc_city'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_country';
                $updates['measure_value'] = $station['loc_country_code'];
                $this->update_data_table($updates);
                if (array_key_exists('timezone', $weather)) {
                    $station['loc_timezone'] = $weather['timezone'];
                    $updates['measure_type'] = 'loc_timezone';
                    $updates['measure_value'] = $station['loc_timezone'];
                    $this->update_data_table($updates);
                }
                if (array_key_exists('latitude', $weather)) {
                    $station['loc_latitude'] = $weather['latitude'];
                    $updates['measure_type'] = 'loc_latitude';
                    $updates['measure_value'] = $station['loc_latitude'];
                    $this->update_data_table($updates);
                }
                if (array_key_exists('longitude', $weather)) {
                    $station['loc_longitude'] = $weather['longitude'];
                    $updates['measure_type'] = 'loc_longitude';
                    $updates['measure_value'] = $station['loc_longitude'];
                    $this->update_data_table($updates);
                }
                if (array_key_exists('elevation', $weather)) {
                    $station['loc_altitude'] = $weather['elevation'];
                    $updates['measure_type'] = 'loc_altitude';
                    $updates['measure_value'] = $station['loc_altitude'];
                    $this->update_data_table($updates);
                }
                if (array_key_exists('barometric_pressure_indoor', $observation)) {
                    $updates['measure_type'] = 'pressure';
                    $updates['measure_value'] = $observation['barometric_pressure_indoor'];
                    $this->update_data_table($updates);
                }
                if (array_key_exists('barometric_pressure', $observation)) {
                    $updates['measure_type'] = 'pressure';
                    $updates['measure_value'] = $observation['barometric_pressure'];
                    $this->update_data_table($updates);
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
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('air_temperature', $observation)) {
                        $updates['measure_type'] = 'temperature';
                        $updates['measure_value'] = $observation['air_temperature'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('relative_humidity', $observation)) {
                        $updates['measure_type'] = 'humidity';
                        $updates['measure_value'] = $observation['relative_humidity'];
                        $this->update_data_table($updates);
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
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('wind_direction', $observation)) {
                        $updates['measure_type'] = 'windangle';
                        $updates['measure_value'] = $observation['wind_direction'];
                        $this->update_data_table($updates);
                        $updates['measure_type'] = 'gustangle';
                        $updates['measure_value'] = $observation['wind_direction'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('wind_avg', $observation)) {
                        $updates['measure_type'] = 'windstrength';
                        $updates['measure_value'] = $observation['wind_avg'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('wind_gust', $observation)) {
                        $updates['measure_type'] = 'guststrength';
                        $updates['measure_value'] = $observation['wind_gust'];
                        $this->update_data_table($updates);
                    }
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }

                // NAModule3
                if (array_key_exists('precip', $observation) || array_key_exists('precip_accum_last_1hr', $observation) || array_key_exists('precip_accum_last_24hr', $observation)) {
                    $type = 'NAModule3';
                    $updates['device_id'] = $station['station_id'];
                    $updates['device_name'] = $station['station_name'];
                    $updates['module_id'] = $this->get_fake_modulex_id($station['guid'], 3);
                    $updates['module_type'] = $type;
                    $updates['module_name'] = $this->get_fake_module_name($type);
                    $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                    $updates['measure_type'] = 'last_refresh';
                    $updates['measure_value'] = date('Y-m-d H:i:s');
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('precip', $observation)) {
                        $updates['measure_type'] = 'rain';
                        $updates['measure_value'] = $observation['precip'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('precip_accum_last_1hr', $observation)) {
                        $updates['measure_type'] = 'rain_hour_aggregated';
                        $updates['measure_value'] = $observation['precip_accum_last_1hr'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('precip_accum_last_24hr', $observation)) {
                        $updates['measure_type'] = 'rain_day_aggregated';
                        $updates['measure_value'] = $observation['precip_accum_last_24hr'];
                        $this->update_data_table($updates);
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
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates);
                    $updates['measure_timestamp'] = $timestamp;
                    if (array_key_exists('air_temperature_indoor', $observation)) {
                        $updates['measure_type'] = 'temperature';
                        $updates['measure_value'] = $observation['air_temperature_indoor'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('relative_humidity_indoor', $observation)) {
                        $updates['measure_type'] = 'humidity';
                        $updates['measure_value'] = $observation['relative_humidity_indoor'];
                        $this->update_data_table($updates);
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
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates);
                    if (array_key_exists('uv', $observation)) {
                        $updates['measure_type'] = 'uv_index';
                        $updates['measure_value'] = $observation['uv'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('solar_radiation', $observation)) {
                        $updates['measure_type'] = 'irradiance';
                        $updates['measure_value'] = $observation['solar_radiation'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('brightness', $observation)) {
                        $updates['measure_type'] = 'illuminance';
                        $updates['measure_value'] = $observation['brightness'];
                        $this->update_data_table($updates);
                    }
                    $this->update_data_table($updates);
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
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'last_seen';
                    $updates['measure_value'] = $timestamp;
                    $updates['measure_timestamp'] = $timestamp;
                    $this->update_data_table($updates);
                    if (array_key_exists('lightning_strike_count_last_3hr', $observation)) {
                        $updates['measure_type'] = 'strike_count';
                        $updates['measure_value'] = $observation['lightning_strike_count_last_3hr'];
                        $this->update_data_table($updates);
                    }
                    if (array_key_exists('lightning_strike_last_distance', $observation)) {
                        $updates['measure_type'] = 'strike_distance';
                        $updates['measure_value'] = $observation['lightning_strike_last_distance'] * 1000;
                        $updates['measure_timestamp'] = $strikestamp;
                        $this->update_data_table($updates);
                    }
                    $this->update_data_table($updates);
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
        $wflw = new WFLWApiClient();
        foreach ($stations as $st => $station) {
            $device_id = $station['station_id'];
            $device_name = $station['station_name'];
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    $raw_data = $wflw->getRawPublicStationData($station['service_id'], self::$dev_key);
                    $this->format_and_store($raw_data, $station);
                    Logger::notice($this->facility, $this->service_name, $device_id, $device_name, null, null, 0, 'Weather stations data retrieved.');
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