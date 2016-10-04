<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

use WeatherStation\System\Logs\Logger;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
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
            parent::__construct($result["error"]["code"], $result["error"]["message"], API_ERROR_TYPE);
        }
        else
        {
            parent::__construct($code, $message, API_ERROR_TYPE);
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
                $s = print_r($this->result, true);
                if (isset($this->result['error']['code']) && isset($this->result['error']['message'])) {
                    $c = $this->result['error']['code'];
                    $m = $this->result['error']['message'];
                }
            }
            else
            {
                if ($this->result == CURL_ERROR_TYPE) {
                    $r = 'cURL';
                }
                if ($this->result == JSON_ERROR_TYPE) {
                    $r = 'JSON';
                }
                if ($this->result == INTERNAL_ERROR_TYPE) {
                    $r = 'internal';
                }
                if ($this->result == NOT_LOGGED_ERROR_TYPE) {
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
