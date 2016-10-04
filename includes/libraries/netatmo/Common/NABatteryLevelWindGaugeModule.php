<?php

namespace WeatherStation\SDK\Netatmo\Common;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 */
class NABatteryLevelWindGaugeModule
{
    /* Battery range: 6000 ... 3950 */
    const WG_BATTERY_LEVEL_0 = 5590;/*full*/
    const WG_BATTERY_LEVEL_1 = 5180;/*high*/
    const WG_BATTERY_LEVEL_2 = 4770;/*medium*/
    const WG_BATTERY_LEVEL_3 = 4360;/*low*/
    /* below 4360: very low */
}

?>
