<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.0.0
 */
class NAWPErrorType extends NAClientException
{
    function __construct($code, $message)
    {
        parent::__construct($code, $message, WP_ERROR_TYPE);
    }
}

?>
