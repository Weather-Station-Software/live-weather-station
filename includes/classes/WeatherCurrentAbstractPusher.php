<?php

namespace WeatherStation\SDK\Generic\Plugin\Weather\Current;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Unit\Conversion as Unit_Conversion;
use WeatherStation\DB\Query as Query;
use WeatherStation\System\Quota\Quota;

/**
 * Abstract class to push data to weather services.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.5.0
 */
abstract class Pusher {

    use Query, Unit_Conversion;

    public $facility = 'Weather Pusher';

    /**
     * Get the service Name.
     *
     * @return  string   The service name.
     * @since   3.0.0
     */
    abstract protected function get_service_name();

    /**
     * Completes data to be pushed.
     *
     * @param   array   $device      The actual device.
     * @param   array   $station      Station details.
     * @return  array   The completed device.
     * @since   2.5.0
     */
    abstract protected function complete_pushed_data($device, $station);

    /**
     * Test if this station must be pushed.
     *
     * @param   array   $station      Station details.
     * @return  boolean   True if these station data must be pushed, false otherwise.
     * @since   2.5.0
     */
    abstract protected function ready_for_push($station);

    /**
     * Get the post url.
     *
     * @return  string   The url where to post data.
     * @since   2.5.0
     */
    abstract protected function get_post_url();

    /**
     * Get the user/pwd string.
     *
     * @param   array   $station      Station details.
     * @return  string   User and password ready to use.
     * @since   2.5.0
     */
    abstract protected function get_userpwd($station);

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @since   2.5.0
     */
    abstract protected function process_result($content, $station);

    /**
     * Process the data before pushing it.
     *
     * @param array $data Collected data.
     * @return array Data ready to push.
     * @since 3.0.0
     */
    abstract protected function process_data($data);

    /**
     * Sanitize a message coming from a remote service before it is thrown, logged or displayed.
     *
     * @param   mixed   $message      The remote message.
     * @return  string  A plain text, length-capped message.
     * @since   3.8.9
     */
    protected function sanitize_remote_message($message) {
        if (!is_scalar($message)) {
            return 'Unknown error';
        }
        $message = substr(sanitize_text_field((string)$message), 0, 200);
        return ($message === '' ? 'Unknown error' : $message);
    }

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @throws \Exception Contains HTTP error code & message
     * @since   2.5.0
     */
    protected function _process_result($content, $station) {
        $error = false;
        $code = 0;
        $message = 'Unknown error';
        $response = (isset($content['response']) && is_array($content['response'])) ? $content['response'] : array();
        if (array_key_exists('code', $response)) {
            $code = $response['code'];
            if ($code != '200') {
                $error = true;
                if (array_key_exists('message', $response)) {
                    $message = $this->sanitize_remote_message($response['message']);
                }
            }
        }
        else {
            $error = true;
        }
        if ($error) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exception is caught by the caller and its message only goes to the plugin events log (Logger), where it is escaped on display; it is never printed directly (the caller applies sanitize_remote_message() again). The remote message is sanitized by sanitize_remote_message() above.
            throw new \Exception($message, (int)$code);
        }
        else {
            $this->process_result($content, $station);
        }
    }

    /**
     * Post station's measurements to service.
     *
     * @param array $stations Optional. Test specifically these stations.
     * @return string Error string if any.
     * @since 3.0.0
     */
    public function push_data($stations = array()) {
        if (empty($stations)) {
            $test = false;
            $stations = $this->get_stations_information();
        }
        else {
            $test = true;
        }
        foreach ($stations as $station) {
            if (!$this->ready_for_push($station) && !$test) {
                continue;
            }
            $sid = $station['station_id'];
            $sname = $station['station_name'];
            $values = $this->complete_pushed_data($this->process_data($this->get_all_measurements_for_push($station['station_id'])), $station);
            $auth = $this->get_userpwd($station);
            try {
                $args = array();
                if ($auth != '') {
                    $args['headers'] = array ('Authorization' => 'Basic ' . base64_encode($auth));
                }
                $args['body'] = $values;
                $args['timeout'] = max(1, min(60, (int)get_option('live_weather_station_sharing_http_timeout')));
                // The services never redirect an upload: a redirection would forward the credentials and the data to another host.
                $args['redirection'] = 0;
                $args['user-agent'] = LIVE_WEATHER_STATION_PLUGIN_AGENT;
                if (Quota::verify($this->get_service_name(), 'POST')) {
                    $content = wp_remote_post($this->get_post_url(), $args);
                    if (is_wp_error($content)) {
                        throw new \Exception($this->sanitize_remote_message($content->get_error_message()));
                    }
                    $this->_process_result($content, $station);
                    if ($test) {
                        Logger::notice($this->facility, $this->get_service_name(), $sid, $sname, null, null, null, 'Service connectivity test: OK.');
                        return '';
                    }
                    else {
                        // Every station which shares its data is pushed, not only the first one.
                        Logger::notice($this->facility, $this->get_service_name(), $sid, $sname, null, null, null, 'Outdoor data pushed.');
                    }
                }
                else {
                    Logger::warning($this->facility, $this->get_service_name(), null, null, null, null, 0, 'Quota manager has forbidden to post data.');
                }

            }
            catch (\Exception $ex) {
                // Remote messages are untrusted: plain text and length-capped, never the request itself.
                $msg = $this->sanitize_remote_message($ex->getMessage());
                if ($test) {
                    Logger::notice($this->facility, $this->get_service_name(), $sid, $sname, null, null, $ex->getCode(), 'Service connectivity test: KO / ' . $msg);
                    return $msg;
                }
                else {
                    Logger::error($this->facility, $this->get_service_name(), $sid, $sname, null, null, $ex->getCode(), $msg);
                }
            }
        }
    }
}