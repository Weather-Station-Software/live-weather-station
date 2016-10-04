<?php

namespace WeatherStation\SDK\OpenWeatherMap;

use WeatherStation\SDK\OpenWeatherMap;
use WeatherStation\SDK\OpenWeatherMap\Util\City;
use WeatherStation\SDK\OpenWeatherMap\Util\Sun;

/**
 * Weather class returned by owm\OpenWeatherMap->getWeather().
 *
 * @see WeatherStation\SDK\OpenWeatherMap::getWeather() The function using it.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class WeatherForecast implements \Iterator
{
    /**
     * A city object.
     *
     * @var Util\City
     */
    public $city;

    /**
     * A sun object
     *
     * @var Util\Sun
     */
    public $sun;

    /**
     * The time of the last update of this weather data.
     *
     * @var \DateTime
     */
    public $lastUpdate;

    /**
     * An array of {@link Forecast} objects.
     *
     * @var Forecast[]
     *
     * @see Forecast The Forecast class.
     */
    private $forecasts;

    /**
     * @internal
     */
    private $position = 0;

    /**
     * Create a new Forecast object.
     *
     * @param        $xml
     * @param string $units
     * @param int    $days How many days of forecast to receive.
     *
     * @internal
     */
    public function __construct($xml, $units, $days)
    {
        $this->city = new City(-1, $xml->location->name, $xml->location->location['longitude'], $xml->location->location['latitude'], $xml->location->country);
        $this->sun = new Sun(new \DateTime($xml->sun['rise']), new \DateTime($xml->sun['set']));
        $this->lastUpdate = new \DateTime($xml->meta->lastupdate);

        $counter = 0;
        foreach ($xml->forecast->time as $time) {
            $forecast = new Forecast($time, $units);
            $forecast->city = $this->city;
            $this->forecasts[] = $forecast;

            $counter++;
            // Make sure to only return the requested number of days.
            if ($days <= 5 && $counter == $days * 8) {
                break;
            } elseif ($days > 5 && $counter == $days) {
                break;
            }
        }
    }

    /**
     * @internal
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @internal
     */
    public function current()
    {
        return $this->forecasts[$this->position];
    }

    /**
     * @internal
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @internal
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @internal
     */
    public function valid()
    {
        return isset($this->forecasts[$this->position]);
    }
}
