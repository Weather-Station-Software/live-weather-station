<?php
namespace WeatherStation\SDK\Netatmo\Common;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 */
class NAStationSensorsMinMax
{
    const TEMP_MIN = -40;
    const TEMP_MAX = 60;
    const HUM_MIN = 1;
    const HUM_MAX = 99;
    const CO2_MIN = 300;
    const CO2_MAX = 4000;
    const PRESSURE_MIN = 700;
    const PRESSURE_MAX = 1300;
    const NOISE_MIN = 10;
    const NOISE_MAX = 120;
    const RAIN_MIN = 2;
    const RAIN_MAX = 300;
    const WIND_MIN = 5;
    const WIND_MAX = 150;
}


?>
