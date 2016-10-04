<?php

namespace WeatherStation\SDK\OpenWeatherMap;

use WeatherStation\SDK\OpenWeatherMap\Util\City;
use WeatherStation\SDK\OpenWeatherMap\Util\Sun;
use WeatherStation\SDK\OpenWeatherMap\Util\Temperature;
use WeatherStation\SDK\OpenWeatherMap\Util\Time;
use WeatherStation\SDK\OpenWeatherMap\Util\Unit;
use WeatherStation\SDK\OpenWeatherMap\Util\Weather as WeatherObj;
use WeatherStation\SDK\OpenWeatherMap\Util\Wind;

/**
 * Class Forecast.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class Forecast extends CurrentWeather
{
    /**
     * @var Time The time of the forecast.
     */
    public $time;

    /**
     * Create a new weather object for forecasts.
     *
     * @param \SimpleXMLElement $xml   The forecasts xml.
     * @param string            $units Ths units used.
     *
     * @internal
     */
    public function __construct(\SimpleXMLElement $xml, $units)
    {
        $this->city = new City($xml->city['id'], $xml->city['name'], $xml->city->coord['lon'], $xml->city->coord['lat'], $xml->city->country);

        if ($units == 'metric') {
            $temperatureUnit = "&deg;C";
        } else {
            $temperatureUnit = 'F';
        }

        $xml->temperature['value'] = ($xml->temperature['max'] + $xml->temperature['min']) / 2;

        $this->temperature = new Temperature(new Unit($xml->temperature['value'], $temperatureUnit), new Unit($xml->temperature['min'], $temperatureUnit), new Unit($xml->temperature['max'], $temperatureUnit), new Unit($xml->temperature['day'], $temperatureUnit), new Unit($xml->temperature['morn'], $temperatureUnit), new Unit($xml->temperature['eve'], $temperatureUnit), new Unit($xml->temperature['night'], $temperatureUnit));
        $this->humidity = new Unit($xml->humidity['value'], $xml->humidity['unit']);
        $this->pressure = new Unit($xml->pressure['value'], $xml->pressure['unit']);

        // This is kind of a hack, because the units are missing in the xml document.
        if ($units == 'metric') {
            $windSpeedUnit = 'm/s';
        } else {
            $windSpeedUnit = 'mps';
        }

        $this->wind = new Wind(new Unit($xml->windSpeed['mps'], $windSpeedUnit, $xml->windSpeed['name']), new Unit($xml->windDirection['value'], $xml->windDirection['code'], $xml->windDirection['name']));
        $this->clouds = new Unit($xml->clouds['all'], $xml->clouds['unit'], $xml->clouds['value']);
        $this->precipitation = new Unit($xml->precipitation['value'], null, $xml->precipitation['type']);
        $this->sun = new Sun(new \DateTime($xml->city->sun['rise']), new \DateTime($xml->city->sun['set']));
        $this->weather = new WeatherObj($xml->symbol['number'], $xml->symbol['name'], $xml->symbol['var']);
        $this->lastUpdate = new \DateTime($xml->lastupdate['value']);

        if (isset($xml['from'])) {
            $this->time = new Time($xml['from'], $xml['to']);
        } else {
            $this->time = new Time($xml['day']);
        }
    }
}
