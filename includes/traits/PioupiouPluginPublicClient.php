<?php

namespace WeatherStation\SDK\Pioupiou\Plugin;

use WeatherStation\SDK\Pioupiou\Exception;
use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Pioupiou\PIOUApiClient;
use WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer as Ephemeris_Computer;
use WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer as Weather_Index_Computer;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;


/**
 * Pioupiou station client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */
trait PublicClient {

    use BaseClient;

    protected $facility = 'Weather Collector';
    public $detected_station_name = '';

    /**
     * Verify if a station is accessible.
     *
     * @param string $id The station ID.
     * @return string The error message, empty string otherwise.
     * @since 3.5.0
     */
    public function test_station($id) {
        $result = 'unknown station ID';
        $piou = new PIOUApiClient();
        try {
            Quota::verify(self::$service, 'GET');
            $raw_data = $piou->getRawPublicStationData($id);
            $weather = json_decode($raw_data, true);
            if (is_array($weather)) {
                if (array_key_exists('data', $weather)) {
                    if (array_key_exists('meta', $weather['data'])) {
                        if (array_key_exists('name', $weather['data']['meta'])) {
                            $this->detected_station_name = $weather['data']['meta']['name'];
                        }
                        else {
                            $this->detected_station_name = '< NO NAME >';
                        }
                        $result = '';
                    }
                }
                else {
                    $result = 'Pioupiou servers have returned unknown response';
                    if (array_key_exists('error_message', $weather)) {
                        $result = $weather['error_message'];
                        $result = str_replace('{station_id}', 'Station ID', $result);
                    }
                }
            }
            else {
                $result = 'internal Pioupiou error';
            }
        }
        catch(\Exception $ex)
        {
            $result = 'unable to contact Pioupiou servers';
        }
        return $result;
    }

    /**
     * Format and store data.
     *
     * @param string $json_weather Weather array json formated.
     * @param array $station Station array.
     * @throws \Exception
     * @since 3.5.0
     */
    private function format_and_store($json_weather, $station) {
        $weather = json_decode($json_weather, true);
        if (is_array($weather)) {
            if (array_key_exists('data', $weather)) {
                if (array_key_exists('meta', $weather['data'])) {
                    if (array_key_exists('name', $weather['data']['meta'])) {
                        $this->detected_station_name = $weather['data']['meta']['name'];
                    }
                    else {
                        $this->detected_station_name = '< NO NAME >';
                    }
                }
            }
            else {
                if (array_key_exists('error_message', $weather)) {
                    throw new \Exception($weather['error_message']);
                }
                else {
                    throw new \Exception('Pioupiou unknown exception');
                }
            }
        }
        else {
            throw new \Exception('JSON / '.(string)$json_weather);
        }
        Logger::debug($this->facility, $this->service_name, null, null, null, null, null, print_r($weather, true));
        if (!empty($weather)) {
            $meta = null;
            $location = null;
            $measurements = null;
            $status = null;
            if (array_key_exists('data', $weather)) {
                if (array_key_exists('meta', $weather['data'])) {
                    $meta = $weather['data']['meta'];
                }
                if (array_key_exists('location', $weather['data'])) {
                    $location = $weather['data']['location'];
                }
                if (array_key_exists('measurements', $weather['data'])) {
                    $measurements = $weather['data']['measurements'];
                }
                if (array_key_exists('status', $weather['data'])) {
                    $status = $weather['data']['status'];
                }
            }
            if ($meta && $location && $measurements && $status && is_array($meta) && is_array($location) && is_array($measurements) && is_array($status)) {
                $station['station_name'] = $this->detected_station_name;
                $station_is_on = false;
                if (array_key_exists('state', $status)) {
                    $station_is_on = strtolower($status['state']) == 'on';
                }
                if (!$station_is_on) {
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
                    $updates['measure_type'] = 'signal';
                    $updates['measure_value'] = 0;
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'battery';
                    $updates['measure_value'] = 0 ;
                    $this->update_data_table($updates);
                    $this->update_table(self::live_weather_station_stations_table(), $station);
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

                    // NAModule2
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
                    $updates['measure_type'] = 'signal';
                    $updates['measure_value'] = 0;
                    $this->update_data_table($updates);
                    $updates['measure_type'] = 'battery';
                    $updates['measure_value'] = 0 ;
                    $this->update_data_table($updates);
                    Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                }
                else {
                    $away = true;
                    if (array_key_exists('date', $status)) {
                        if ($status['date']) {
                            $away = false;
                            $timestamp = date('Y-m-d H:i:s', strtotime($status['date']));
                            $locstamp = $timestamp;
                            $windstamp = $timestamp;
                            if (array_key_exists('date', $location)) {
                                if ($location['date']) {
                                    $locstamp = date('Y-m-d H:i:s', strtotime($location['date']));
                                }
                            }
                            if (array_key_exists('date', $measurements)) {
                                if ($measurements['date']) {
                                    $windstamp = date('Y-m-d H:i:s', strtotime($measurements['date']));
                                }
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
                            $updates['measure_type'] = 'loc_timezone';
                            $updates['measure_value'] = $station['loc_timezone'];
                            $this->update_data_table($updates);
                            $updates['measure_type'] = 'loc_altitude';
                            $updates['measure_value'] = $station['loc_altitude'];
                            $this->update_data_table($updates);
                            $updates['measure_type'] = 'battery';
                            $updates['measure_value'] = 100 ;
                            $this->update_data_table($updates);
                            if (array_key_exists('snr', $status)) {
                                $updates['measure_type'] = 'signal';
                                $updates['measure_value'] = $status['snr'];
                                $this->update_data_table($updates);
                            }
                            if (array_key_exists('latitude', $location)) {
                                $station['loc_latitude'] = $location['latitude'];
                                $updates['measure_timestamp'] = $locstamp;
                                $updates['measure_type'] = 'loc_latitude';
                                $updates['measure_value'] = $station['loc_latitude'];
                                $this->update_data_table($updates);
                            }
                            if (array_key_exists('longitude', $location)) {
                                $station['loc_longitude'] = $location['longitude'];
                                $updates['measure_timestamp'] = $locstamp;
                                $updates['measure_type'] = 'loc_longitude';
                                $updates['measure_value'] = $station['loc_longitude'];
                                $this->update_data_table($updates);
                            }


                            $station['last_refresh'] = date('Y-m-d H:i:s');
                            $station['last_seen'] = $timestamp;
                            $this->update_table(self::live_weather_station_stations_table(), $station);
                            Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');

                            // NAModule2
                            if (array_key_exists('wind_heading', $measurements) || array_key_exists('wind_speed_avg', $measurements) || array_key_exists('wind_speed_max', $measurements)) {
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

                                $updates['measure_timestamp'] = $windstamp;
                                if (array_key_exists('wind_heading', $measurements)) {
                                    $value = ($measurements['wind_heading'] + 180) % 360;
                                    $updates['measure_type'] = 'windangle';
                                    $updates['measure_value'] = $value;
                                    $this->update_data_table($updates);
                                    $updates['measure_type'] = 'gustangle';
                                    $updates['measure_value'] = $value;
                                    $this->update_data_table($updates);
                                }
                                if (array_key_exists('wind_speed_avg', $measurements)) {
                                    $updates['measure_type'] = 'windstrength';
                                    $updates['measure_value'] = $measurements['wind_speed_avg'];
                                    $this->update_data_table($updates);
                                }
                                if (array_key_exists('wind_speed_max', $measurements)) {
                                    $updates['measure_type'] = 'guststrength';
                                    $updates['measure_value'] = $measurements['wind_speed_max'];
                                    $this->update_data_table($updates);
                                }
                                Logger::debug($this->facility, $this->service_name, $updates['device_id'], $updates['device_name'], $updates['module_id'], $updates['module_name'], 0, 'Success while collecting current weather data.');
                            }
                        }
                    }
                    if ($away) {
                        Logger::notice($this->facility, $this->service_name, $station['station_id'], $station['station_name'], null, null, 0, 'Outdated station data.');
                    }
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
     * @since 3.5.0
     */
    public function get_and_store_data() {
        $this->synchronize_piou_station();
        $stations = $this->get_all_piou_id_stations();
        $piou = new PIOUApiClient();
        foreach ($stations as $st => $station) {
            $device_id = $station['station_id'];
            $device_name = $station['station_name'];
            try {
                if (Quota::verify($this->service_name, 'GET')) {
                    $raw_data = $piou->getRawPublicStationData($station['service_id']);
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
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Pioupiou servers has returned empty response. Retry will be done shortly.');
                }
                else {
                    Logger::warning($this->facility, $this->service_name, $device_id, $device_name, null, null, $ex->getCode(), 'Temporary unable to contact Pioupiou servers. Retry will be done shortly.');
                    return array();
                }
            }
        }
    }

    /**
     * Do the main job.
     *
     * @param string $system The calling system.
     * @since 3.5.0
     */
    protected function __run($system){
        $cron_id = Watchdog::init_chrono(Watchdog::$piou_update_station_schedule_name);
        $err = '';
        try {
            $err = 'collecting weather';
            $this->get_and_store_data();
            $err = 'computing weather';
            $weather = new Weather_Index_Computer();
            $weather->compute(LWS_PIOU_SID);
            $err = 'computing ephemeris';
            $ephemeris = new Ephemeris_Computer();
            $ephemeris->compute(LWS_PIOU_SID);
            Logger::info($system, $this->service_name, null, null, null, null, 0, 'Job done: collecting and computing weather and ephemeris data.');
        }
        catch (\Exception $ex) {
            Logger::critical($system, $this->service_name, null, null, null, null, $ex->getCode(), 'Error while ' . $err . ' data: ' . $ex->getMessage());
        }
        $this->synchronize_modules_count();
        Watchdog::stop_chrono($cron_id);
    }
}