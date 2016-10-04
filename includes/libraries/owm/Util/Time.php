<?php

namespace WeatherStation\SDK\OpenWeatherMap\Util;

/**
 * The time class representing a time object.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class Time
{
    /**
     * @var \DateTime The start time for the forecast.
     */
    public $from;

    /**
     * @var \DateTime The end time for the forecast.
     */
    public $to;

    /**
     * @var \DateTime The day of the forecast.
     */
    public $day;

    /**
     * Create a new time object.
     *
     * @param string|\DateTime      $from The start time for the forecast.
     * @param string|\DateTime|null $to   The end time for the forecast.
     *
     * @internal
     */
    public function __construct($from, $to = null)
    {
        if (isset($to)) {
            $from = ($from instanceof \DateTime) ? $from : new \DateTime((string)$from);
            $to = ($to instanceof \DateTime) ? $to : new \DateTime((string)$to);
            $day = new \DateTime($from->format('Y-m-d'));
        } else {
            $from = ($from instanceof \DateTime) ? $from : new \DateTime((string)$from);
            $day = clone $from;
            $to = clone $from;
            $to = $to->add(new \DateInterval('PT23H59M59S'));
        }

        $this->from = $from;
        $this->to = $to;
        $this->day = $day;
    }
}
