<?php

namespace WeatherStation\SDK\OpenWeatherMap\Util;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class Wind
{
    /**
     * @var Unit The wind speed.
     */
    public $speed;

    /**
     * @var Unit The wind direction.
     */
    public $direction;

    /**
     * Create a new wind object.
     *
     * @param Unit $speed     The wind speed.
     * @param Unit $direction The wind direction.
     *
     * @internal
     */
    public function __construct(Unit $speed, Unit $direction)
    {
        $this->speed = $speed;
        $this->direction = $direction;
    }
}
