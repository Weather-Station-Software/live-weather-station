<?php

namespace WeatherStation\SDK\PWSWeather\Plugin;

use WeatherStation\SDK\Generic\Plugin\Weather\Current\Pusher as Abstract_Pusher;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;

/**
 * Class to push data to PWS Weather.
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
        return 'PWS Weather';
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
        $result['dateutc'] = date('Y-m-d H:i:s', time()-60);
        /*if (array_key_exists('timestamp', $data)) {
            $result['dateutc'] = $data['timestamp'];
        }*/
        if (array_key_exists('pressure', $data)) {
            $result['baromin'] = $this->get_pressure($data['pressure'], 1);
        }
        if (array_key_exists('temperature', $data)) {
            $result['tempf'] = $this->get_temperature($data['temperature'], 1);
        }
        if (array_key_exists('humidity', $data)) {
            $result['humidity'] = $this->get_humidity($data['humidity']);
        }
        if (array_key_exists('rain_hour_aggregated', $data)) {
            $result['rainin'] = $this->get_rain($data['rain_hour_aggregated'], 2);
        }
        if (array_key_exists('rain_day_aggregated', $data)) {
            $result['dailyrainin'] = $this->get_rain($data['rain_day_aggregated'], 2);
        }
        if (array_key_exists('windangle', $data)) {
            $result['winddir'] = $this->get_wind_angle($data['windangle']);
        }
        if (array_key_exists('windstrength', $data)) {
            $result['windspeedmph'] = $this->get_wind_speed($data['windstrength'], 6);
        }
        if (array_key_exists('guststrength', $data)) {
            $result['windgustmph'] = $this->get_wind_speed($data['guststrength'], 6);
        }
        if (array_key_exists('dew_point', $data)) {
            $result['dewptf'] = $this->get_temperature($data['dew_point'], 1);
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
        $result['ID'] = $station['pws_user'];
        $result['PASSWORD'] = $station['pws_password'];
        $result['softwaretype'] = LWS_PLUGIN_SIGNATURE;
        $result['action'] = 'updateraw';
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
        return ($station['pws_sync'] == 1 && $station['pws_user'] != '' && $station['pws_password'] != '');
    }

    /**
     * Get the post url.
     *
     * @return  string   The url where to post data.
     * @since   2.5.0
     */
    protected function get_post_url() {
        return 'http://www.pwsweather.com/pwsupdate/pwsupdate.php';
    }

    /**
     * Get the user/pwd string for CURLOPT_USERPWD option.
     *
     * @param   array   $station      Station details.
     * @return  string   User and password ready to use by curl_setopt.
     * @since   2.5.0
     */
    protected function get_userpwd($station) {
        return '';
    }

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @throws \Exception Contains parsed error message
     * @since   2.5.0
     */
    protected function process_result($content, $station) {
        $body = $content['body'];
        if (strpos(strtolower($body), 'error:') > 0) {
            throw new \Exception(substr(trim(substr($body, 6 + strpos(strtolower($body), '<body>'), 255)), 7, 255));
        }
    }

    /**
     * Do the main job.
     *
     * @since 3.2.0
     */
    public function cron_run(){
        $cron_id = Watchdog::init_chrono(Watchdog::$pws_push_schedule_name);
        $svc = 'PWS Weather';
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