<?php

namespace WeatherStation\SDK\OpenWeatherMap\Util;

/**
 * The sun class representing a sun object.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class Sun
{
    /**
     * @var \DateTime The time of the sun rise.
     */
    public $rise;

    /**
     * @var \DateTime The time of the sun set.
     */
    public $set;

    /**
     * Create a new sun object.
     *
     * @param \DateTime $rise The time of the sun rise
     * @param \DateTime $set  The time of the sun set.
     *
     * @throws \LogicException If sunset is before sunrise.
     * @internal
     */
    public function __construct(\DateTime $rise, \DateTime $set)
    {
        if ($set < $rise) {
            throw new \LogicException('Sunset cannot be before sunrise!');
        }
        $this->rise = $rise;
        $this->set = $set;
    }
}
