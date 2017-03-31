<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;

use WeatherStation\SDK\Generic\Plugin\Weather\Current\Pusher as Abstract_Pusher;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;


/**
 * Class to push data to OpenWeatherMap.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.5.0
 */
class Pusher extends Abstract_Pusher {

    /**
     * Get the service Name.
     *
     * @return  string   The service name.
     * @since   3.0.0
     */
    protected function get_service_name() {
        return 'OpenWeatherMap';
    }

    /**
     * Process the data before pushing it.
     *
     * @param array $data Collected data.
     * @return array Data ready to push.
     * @since 3.0.0
     */
    protected function process_data($data) {
        $result = array();
        if (array_key_exists('pressure', $data)) {
            $result['pressure'] = $this->get_pressure($data['pressure'], 3);
        }
        if (array_key_exists('temperature', $data)) {
            $result['temp'] = $this->get_temperature($data['temperature'], 0);
        }
        if (array_key_exists('humidity', $data)) {
            $result['humidity'] = $this->get_humidity($data['humidity']);
        }
        if (array_key_exists('rain_hour_aggregated', $data)) {
            $result['rain_1h'] = $this->get_rain($data['rain_hour_aggregated'], 0);
        }
        if (array_key_exists('rain_day_aggregated', $data)) {
            $result['rain_today'] = $this->get_rain($data['rain_day_aggregated'], 0);
        }
        if (array_key_exists('windangle', $data)) {
            $result['wind_dir'] = $this->get_wind_angle($data['windangle']);
        }
        if (array_key_exists('windstrength', $data)) {
            $result['wind_speed'] = $this->get_wind_speed($data['windstrength'], 5);
        }
        if (array_key_exists('guststrength', $data)) {
            $result['wind_speed'] = $this->get_wind_speed($data['guststrength'], 5);
        }
        if (array_key_exists('dew_point', $data)) {
            $result['dewpoint'] = $this->get_temperature($data['dew_point'], 0);
        }
        return $result;
    }

    /**
     * Completes data to be pushed.
     *
     * @param   array   $device      The actual device.
     * @param   array   $station      Station details.
     * @return  array   The completed device.
     * @since   2.5.0
     */
    protected function complete_pushed_data($device, $station) {
        $result = $device;
        $device['name'] = $station['station_name'];
        return $result;
    }

    /**
     * Test if these station data must be pushed.
     *
     * @param   array   $station      Station details.
     * @return  boolean   True if these station data must be pushed, false otherwise.
     * @since   2.5.0
     */
    protected function ready_for_push($station) {
        return ($station['owm_sync'] == 1 && $station['owm_user'] != '' && $station['owm_password'] != '');
    }

    /**
     * Get the post url.
     *
     * @return  string   The url where to post data.
     * @since   2.5.0
     */
    protected function get_post_url() {
        return 'http://openweathermap.org/data/post';
    }

    /**
     * Get the user/pwd string for CURLOPT_USERPWD option.
     *
     * @param   array   $station      Station details.
     * @return  string   User and password ready to use by curl_setopt.
     * @since   2.5.0
     */
    protected function get_userpwd($station) {
        return $station['owm_user'] . ':' . $station['owm_password'];
    }

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @throws \Exception Contains parsed error message or HTTP error code & message
     * @since   2.5.0
     */
    protected function process_result($content, $station) {
        $body = $content['body'];
        $error = false;
        $code = 0;
        $message = 'Unknown error';
        $id = 0;
        if ($body != '') {
            $body = json_decode($body, true);
            if (is_array($body)) {
                if (array_key_exists('cod', $body)) {
                    $code = $body['cod'];
                    if ($code != '200') {
                        $error = true;
                        if (array_key_exists('message', $body)) {
                            $message = $body['message'];
                        }
                    }
                    else {
                        if (array_key_exists('id', $body)) {
                            $id = $body['id'];
                        }
                        else {
                            $error = true;
                            $code = 498;
                            $message = 'The answer can not be interpreted by the client';
                        }
                    }
                }
                else {
                    $error = true;
                    $code = 498;
                    $message = 'The answer can not be interpreted by the client';
                }
            }
            else {
                $error = true;
                $code = 498;
                $message = 'The answer can not be interpreted by the client';
            }
        }
        else {
            $error = true;
            $code = 418;
            $message = '';
        }
        if ($error) {
            throw new \Exception($message, $code);
        }
        if ($id != 0) {
            $station['owm_id'] = $id;
            $this->update_stations_table($station);
        }
    }

    /**
     * Do the main job.
     *
     * @since 3.2.0
     */
    public function cron_run(){
        $cron_id = Watchdog::init_chrono(Watchdog::$owm_push_schedule_name);
        $svc = 'OpenWeatherMap';
        try {
            $this->push_data();
            Logger::info('Cron Engine', $svc, null, null, null, null, 0, 'Job done: pushing weather data.');
        }
        catch (\Exception $ex) {
            Logger::error('Cron Engine', $svc, null, null, null, null, $ex->getCode(), 'Error while pushing weather data: ' . $ex->getMessage());
        }
        Watchdog::stop_chrono($cron_id);
    }
}