<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;

/**
 * OWM current weather collector for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
class CurrentCollector {

    use CurrentClient;
}