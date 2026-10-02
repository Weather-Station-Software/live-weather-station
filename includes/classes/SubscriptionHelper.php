<?php

namespace WeatherStation\System\Subscription;

use WeatherStation\System\I18N\Handling as Intl;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Logs\Logger;


/**
 * This class add subscription management.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */

class Handling {

    private $subscribe_done = false;
    private $facility = 'Subscription Helper';
    private $service = 'MailChimp';
    private $list_url = '';
    private $list_id = '';

    /**
     * Class constructor
     *
     * @param string $email The email to subscribe.
     *
     * @since 3.3.0
     */
    public function __construct($email) {
        $this->list_url = '47e5f06905b5efac6d5e76057';
        $this->list_id = '94aea1c726';
        // The caller is responsible for having collected an explicit consent from the person: the address is sent
        // to a third party (MailChimp, hosted in the US).
        $email = sanitize_email((string)$email);
        $this->subscribe_done = (is_email($email) ? $this->_subscribe($email) : false);
    }

    /**
     * Process the result of the post.
     *
     * @param array $content Result of the post.
     * @throws \Exception Contains HTTP error code & message
     * @since 3.3.0
     */
    private function _process_result($content) {
        $error = false;
        $code = 0;
        $message = 'Unknown error';
        if (array_key_exists('response', $content)) {
            $response = $content['response'];
        }
        else {
            $response = array();
        }
        if (array_key_exists('code', $response)) {
            $code = $response['code'];
            if ($code != '200') {
                $error = true;
                if (array_key_exists('message', $response)) {
                    $message = substr(sanitize_text_field((string)$response['message']), 0, 200);
                }
            }
        }
        else {
            $error = true;
        }
        if ($error) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- the message comes from sanitize_text_field() output (line 65) and is only caught at line 107 of this class and written to the Logger, it is never printed
            throw new \Exception($message, (int)$code);
        }
    }

    /**
     * Do the subscription
     *
     * @param string $email The email to subscribe.
     * @return boolean True if the operation was successful, false otherwise.
     * @since 3.3.0
     */
    private function _subscribe($email) {
        $result = false;
        $url = 'https://software.us14.list-manage.com/subscribe/post?u=' . $this->list_url . '&id=' . $this->list_id;
        try {
            $args = array();
            $args['body'] = array( 'EMAIL' => $email);
            $args['user-agent'] = LIVE_WEATHER_STATION_PLUGIN_AGENT;
            $args['timeout'] = max(1, min(60, (int)get_option('live_weather_station_system_http_timeout')));
            $args['redirection'] = 2;
            if (Quota::verify($this->service, 'POST')) {
                $content = wp_remote_post($url, $args);
                if (is_wp_error($content)) {
                    throw new \Exception(substr(sanitize_text_field($content->get_error_message()), 0, 200));
                }
                $this->_process_result($content);
                Logger::notice($this->facility, $this->service, null, null, null, null, null, 'The newsletter subscription request has been successfully sent (the email address is deliberately not logged).');
                $result = true;
            }
            else {
                Logger::warning($this->facility, $this->service, null, null, null, null, 0, 'Quota manager has forbidden to post data.');
            }

        }
        catch (\Exception $ex) {
            Logger::error($this->facility, $this->service, null, null, null, null, $ex->getCode(), substr(sanitize_text_field($ex->getMessage()), 0, 500));
        }
        return $result;
    }

    /**
     * Is the subscribe done?
     *
     * @return boolean True if the operation was successful, false otherwise.
     * @since 3.3.0
     */
    public function is_done() {
        return $this->subscribe_done;
    }
}