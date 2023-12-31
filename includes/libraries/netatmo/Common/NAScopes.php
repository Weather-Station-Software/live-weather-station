<?php
namespace WeatherStation\SDK\Netatmo\Common;

/**
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.0.0
 */
class NAScopes
{
    const SCOPE_READ_STATION = "read_station";
    const SCOPE_READ_THERM = "read_thermostat";
    const SCOPE_WRITE_THERM = "write_thermostat";
    const SCOPE_READ_CAMERA = "read_camera";
    const SCOPE_WRITE_CAMERA = "write_camera";
    const SCOPE_ACCESS_CAMERA = "access_camera";
    const SCOPE_READ_JUNE = "read_june";
    const SCOPE_WRITE_JUNE = "write_june";
    static $defaultScopes = array(NAScopes::SCOPE_READ_STATION);
    static $validScopes = array(NAScopes::SCOPE_READ_STATION, NAScopes::SCOPE_READ_THERM, NAScopes::SCOPE_WRITE_THERM, NAScopes::SCOPE_READ_CAMERA, NAScopes::SCOPE_WRITE_CAMERA, NAScopes::SCOPE_ACCESS_CAMERA, NAScopes::SCOPE_READ_JUNE, NAScopes::SCOPE_WRITE_JUNE);
    // scope allowed to everyone (no need to be approved)
    static $basicScopes = array(NAScopes::SCOPE_READ_STATION, NAScopes::SCOPE_READ_THERM, NASCopes::SCOPE_WRITE_THERM, NAScopes::SCOPE_READ_CAMERA, NAScopes::SCOPE_WRITE_CAMERA, NAScopes::SCOPE_READ_JUNE, NAScopes::SCOPE_WRITE_JUNE);
}

?>
