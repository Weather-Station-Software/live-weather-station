<?php

namespace WeatherStation\SDK\OpenWeatherMap;

use WeatherStation\SDK\OpenWeatherMap;

/**
 * Class WeatherHistory.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class WeatherHistory implements \Iterator
{
    /**
     * The city object. IMPORTANT: Not all values will be set
     *
     * @var Util\City
     */
    public $city;

    /**
     * The time needed to calculate the request data.
     *
     * @var float
     */
    public $calctime;

    /**
     * An array of {@link WeatherHistory} objects.
     *
     * @var array
     *
     * @see WeatherForecast The WeatherForecast class.
     */
    private $histories;

    /**
     * @internal
     */
    private $position = 0;

    public function __construct($weatherHistory, $query)
    {
        if (isset($weatherHistory['list'][0]['city'])) {
            $country = $weatherHistory['list'][0]['city']['country'];
            $population = $weatherHistory['list'][0]['city']['population'];
        } else {
            $country = null;
            $population = null;
        }

        $this->city = new OpenWeatherMap\Util\City($weatherHistory['city_id'], (is_string($query)) ? $query : null, (isset($query['lon'])) ? $query['lon'] : null, (isset($query['lat'])) ? $query['lat'] : null, $country, $population);
        $this->calctime = $weatherHistory['calctime'];

        foreach ($weatherHistory['list'] as $history) {
            if (isset($history['rain'])) {
                $units = array_keys($history['rain']);
            } else {
                $units = array(0 => null);
            }

            $this->histories[] = new History($this->city, $history['weather'][0], array('now' => $history['main']['temp'], 'min' => $history['main']['temp_min'], 'max' => $history['main']['temp_max']), $history['main']['pressure'], $history['main']['humidity'], $history['clouds']['all'], isset($history['rain']) ? array('val' => $history['rain'][($units[0])], 'unit' => $units[0]) : null, $history['wind'], \DateTime::createFromFormat('U', $history['dt']));
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
        return $this->histories[$this->position];
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
        return isset($this->histories[$this->position]);
    }
}
