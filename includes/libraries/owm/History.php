<?php

namespace WeatherStation\SDK\OpenWeatherMap;

use WeatherStation\SDK\OpenWeatherMap\Util\Temperature;
use WeatherStation\SDK\OpenWeatherMap\Util\Unit;
use WeatherStation\SDK\OpenWeatherMap\Util\Weather;
use WeatherStation\SDK\OpenWeatherMap\Util\Wind;

/**
 * Class WeatherHistory.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class History
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
     * @var Util\Weather
     */
    public $weather;

    /**
     * @var \DateTime The time of the history.
     */
    public $time;

    /**
     * @param $city
     * @param $weather
     * @param $temperature
     * @param $pressure
     * @param $humidity
     * @param $clouds
     * @param $rain
     * @param $wind
     * @param $time
     *
     * @internal
     */
    public function __construct($city, $weather, $temperature, $pressure, $humidity, $clouds, $rain, $wind, $time)
    {
        $this->city = $city;
        $this->weather = new Weather($weather['id'], $weather['description'], $weather['icon']);
        $this->temperature = new Temperature(new Unit($temperature['now'] - 273.15, "\xB0C"), new Unit($temperature['min'] - 273.15, "\xB0C"), new Unit($temperature['max'] - 273.15, "\xB0C"));
        $this->pressure = new Unit($pressure, 'kPa');
        $this->humidity = new Unit($humidity, '%');
        $this->clouds = new Unit($clouds, '%');
        $this->precipitation = new Unit($rain['val'], $rain['unit']);
        $this->wind = new Wind(new Unit($wind['speed']), new Unit($wind['deg']));
        $this->time = $time;
    }
}
