<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

define('WP_ERROR_TYPE', 0);
define('API_ERROR_TYPE',1);//error return from api
define('INTERNAL_ERROR_TYPE', 2); //error because internal state is not consistent
define('JSON_ERROR_TYPE',3);
define('NOT_LOGGED_ERROR_TYPE', 4); //unable to get access token

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.0.0
 */
class NAClientException extends NASDKException
{
    public $error_type;
    /**
    * Make a new API Exception with the given result.
    *
    * @param $result
    *   The result from the API server.
    */
    public function __construct($code, $message, $error_type)
    {
        $this->error_type = $error_type;
        parent::__construct($code, $message);
    }
}

?>
