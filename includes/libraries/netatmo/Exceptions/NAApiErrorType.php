<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

use WeatherStation\System\Logs\Logger;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.0.0
 */
class NAApiErrorType extends NAClientException
{
    public $http_code;
    public $http_message;
    public $result;
    function __construct($code, $message, $result)
    {
        $this->http_code = $code;
        $this->http_message = $message;
        $this->result = $result;
        if(isset($result["error"]) && is_array($result["error"]) && isset($result["error"]["code"]))
        {
            parent::__construct($result["error"]["code"], $result["error"]["message"], LIVE_WEATHER_STATION_NETATMO_API_ERROR_TYPE);
        }
        else
        {
            parent::__construct($code, $message, LIVE_WEATHER_STATION_NETATMO_API_ERROR_TYPE);
        }

        ///////////////////////////////////////////////////////////////////
        // DETAILED LOGGED ERROR
        $c = $this->http_code;
        $m = $this->http_message;
        $r = 'unknown';
        $s = 'none';
        if (isset($this->result))
        {
            if (is_array($this->result))
            {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Plugin Check: print_r( ..., true ) only builds a string for the plugin's own log, secrets are redacted.
                $s = substr(preg_replace('/(\[(?:access_token|refresh_token|client_secret|password|token|apikey|api_key|appid)\]\s*=>\s*)[^\n]*/i', '$1[redacted]', print_r($this->result, true)), 0, 4000);
                if (isset($this->result['error']['code']) && isset($this->result['error']['message'])) {
                    $c = $this->result['error']['code'];
                    $m = $this->result['error']['message'];
                }
            }
            else
            {
                if ($this->result == LIVE_WEATHER_STATION_NETATMO_WP_ERROR_TYPE) {
                    $r = 'WordPress HTTP API';
                }
                if ($this->result == LIVE_WEATHER_STATION_NETATMO_JSON_ERROR_TYPE) {
                    $r = 'JSON';
                }
                if ($this->result == LIVE_WEATHER_STATION_NETATMO_INTERNAL_ERROR_TYPE) {
                    $r = 'internal';
                }
                if ($this->result == LIVE_WEATHER_STATION_NETATMO_NOT_LOGGED_ERROR_TYPE) {
                    $r = 'not logged';
                }
            }
        }
        $t = $m . PHP_EOL . 'Type: ' . $r . PHP_EOL .  'Detail: ' . $s;
        if ($r == 'unknown' && $c == 2) {
            Logger::debug('API / SDK', 'Netatmo', null, null, null, null, $c, $t);
        }
        else {
            Logger::warning('API / SDK', 'Netatmo', null, null, null, null, $c, $t);
        }
        //
        ///////////////////////////////////////////////////////////////////
        
    }
}

?>
