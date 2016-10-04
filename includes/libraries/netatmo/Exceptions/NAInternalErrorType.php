<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 */
class NAInternalErrorType extends NAClientException
{
    function __construct($message)
    {
        parent::__construct(0, $message, INTERNAL_ERROR_TYPE);
    }
}

?>
