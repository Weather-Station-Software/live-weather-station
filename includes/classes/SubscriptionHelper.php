<?php

namespace WeatherStation\System\Subscription;

use WeatherStation\System\I18N\Handling as Intl;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Logs\Logger;


/**
 * This class add subscription management.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
     * @param string $lang Optional. The language to wich subscribe.
     *
     * @since 3.3.0
     */
    public function __construct($email, $lang=null) {
        if (!isset($lang)) {
            $lang = Intl::get_language_id();
        }
        switch (strtolower($lang)) {
            case 'fr':
                $this->list_url = '47e5f06905b5efac6d5e76057';
                $this->list_id = '30544cf55b';
                break;
            default :
                $this->list_url = '47e5f06905b5efac6d5e76057';
                $this->list_id = '94aea1c726';
        }
        $this->subscribe_done = $this->_subscribe($email);
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
                    $message = $response['message'];
                }
            }
        }
        else {
            $error = true;
        }
        if ($error) {
            throw new \Exception($message, $code);
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
        $url = 'https://software.us14.list-manage.com/subscribe/post?u=' . $this->list_url . '&amp;id=' . $this->list_id;
        try {
            $args = array();
            $args['body'] = array( 'EMAIL' => $email);
            $args['user-agent'] = LWS_PLUGIN_AGENT;
            $args['timeout'] = get_option('live_weather_station_system_http_timeout');
            if (Quota::verify($this->service, 'POST')) {
                $content = wp_remote_post($url, $args);
                if (is_wp_error($content)) {
                    throw new \Exception($content->get_error_message());
                }
                $this->_process_result($content);
                Logger::notice($this->facility, $this->service, null, null, null, null, null, sprintf('The email %s has been successfully subscribed.', $email));
                $result = true;
            }
            else {
                Logger::warning($this->facility, $this->service, null, null, null, null, 0, 'Quota manager has forbidden to post data.');
            }

        }
        catch (\Exception $ex) {
            Logger::error($this->facility, $this->service, null, null, null, null, $ex->getCode(), $ex->getMessage());
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