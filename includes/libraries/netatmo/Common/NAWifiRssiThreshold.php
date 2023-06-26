<?php
namespace WeatherStation\SDK\Netatmo\Common;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.0.0
 */
class NAWifiRssiThreshold
{
    const RSSI_THRESHOLD_0 = 86;/*bad signal*/
    const RSSI_THRESHOLD_1 = 71;/*middle quality signal*/
    const RSSI_THRESHOLD_2 = 56;/*good signal*/
}

?>
