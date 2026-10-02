<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Jason Rouet <https://jasonrouet.com/>.
 * @since 3.0.0
 */
class NAInternalErrorType extends NAClientException
{
    function __construct($message)
    {
        parent::__construct(0, $message, LIVE_WEATHER_STATION_NETATMO_INTERNAL_ERROR_TYPE);
    }
}

?>
