<?php

namespace WeatherStation\SDK\Netatmo\Exceptions;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 */
class NASDKException extends \Exception
{
    public function __construct($code, $message)
    {
        if (!$message) {
            $message = 'Unknow error';
        }
        if (!is_numeric($code)) {
            $code = 505;
        }
        parent::__construct($message, $code);
    }
}

?>
