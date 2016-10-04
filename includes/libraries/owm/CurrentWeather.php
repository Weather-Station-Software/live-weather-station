<?php

namespace WeatherStation\SDK\OpenWeatherMap;

//use WeatherStation\SDK\OpenWeatherMap;
use WeatherStation\SDK\OpenWeatherMap\Util\City;
use WeatherStation\SDK\OpenWeatherMap\Util\Sun;
use WeatherStation\SDK\OpenWeatherMap\Util\Temperature;
use WeatherStation\SDK\OpenWeatherMap\Util\Unit;
use WeatherStation\SDK\OpenWeatherMap\Util\Weather as WeatherObj;
use WeatherStation\SDK\OpenWeatherMap\Util\Wind;

/**
 * Weather class used to hold the current weather data.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class CurrentWeather
{
    /**
     * The city object.
     *
     * @var Util\City
     */
    public $city;

    /**
     * The temperature object.
     *
     * @var Util\Temperature
     */
    public $temperature;

    /**
     * @var Util\Unit
     */
    public $humidity;

    /**
     * @var Util\Unit
     */
    public $pressure;

    /**
     * @var Util\Wind
     */
    public $wind;

    /**
     * @var Util\Unit
     */
    public $clouds;

    /**
     * @var Util\Unit
     */
    public $precipitation;

    /**
     * @var Util\Sun
     */
    public $sun;

    /**
     * @var Util\Weather
     */
    public $weather;

    /**
     * @var \DateTime
     */
    public $lastUpdate;

    /**
     * Create a new weather object.
     *
     * @param \SimpleXMLElement $xml
     * @param string            $units
     *
     * @internal
     */
    public function __construct(\SimpleXMLElement $xml, $units)
    {
        $this->city = new City($xml->city['id'], $xml->city['name'], $xml->city->coord['lon'], $xml->city->coord['lat'], $xml->city->country);
        $this->temperature = new Temperature(new Unit($xml->temperature['value'], $xml->temperature['unit']), new Unit($xml->temperature['min'], $xml->temperature['unit']), new Unit($xml->temperature['max'], $xml->temperature['unit']));
        $this->humidity = new Unit($xml->humidity['value'], $xml->humidity['unit']);
        $this->pressure = new Unit($xml->pressure['value'], $xml->pressure['unit']);

        // This is kind of a hack, because the units are missing in the xml document.
        if ($units == 'metric') {
            $windSpeedUnit = 'm/s';
        } else {
            $windSpeedUnit = 'mph';
        }
        $this->wind = new Wind(new Unit($xml->wind->speed['value'], $windSpeedUnit, $xml->wind->speed['name']), new Unit($xml->wind->direction['value'], $xml->wind->direction['code'], $xml->wind->direction['name']));

        $this->clouds = new Unit($xml->clouds['value'], null, $xml->clouds['name']);
        $this->precipitation = new Unit($xml->precipitation['value'], $xml->precipitation['unit'], $xml->precipitation['mode']);
        $utctz = new \DateTimeZone('UTC');
        $this->sun = new Sun(new \DateTime($xml->city->sun['rise'], $utctz), new \DateTime($xml->city->sun['set'], $utctz));
        $this->weather = new WeatherObj($xml->weather['number'], $xml->weather['value'], $xml->weather['icon']);
        $this->lastUpdate = new \DateTime($xml->lastupdate['value'], $utctz);
    }
}
