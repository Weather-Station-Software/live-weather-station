<?php

namespace WeatherStation\SDK\OpenWeatherMap\Util;

/**
 * The unit class representing a unit object.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 2.0.0
 * @license MIT
 */
class Unit
{
    /**
     * @var float The value.
     *
     * @internal
     */
    private $value;

    /**
     * @var string The value's unit.
     *
     * @internal
     */
    private $unit;

    /**
     * @var string The value's description.
     *
     * @internal
     */
    private $description;

    /**
     * Create a new unit object.
     *
     * @param float  $value       The value.
     * @param string $unit        The unit of the value.
     * @param string $description The description of the value.
     *
     * @internal
     */
    public function __construct($value = 0.0, $unit = "", $description = "")
    {
        $this->value = (float)$value;
        $this->unit = (string)$unit;
        $this->description = (string)$description;
    }

    /**
     * Get the value as formatted string with unit.
     *
     * @return string The value as formatted string with unit.
     *
     * The unit is not included if it is empty.
     */
    public function __toString()
    {
        return $this->getFormatted();
    }

    /**
     * Get the value's unit.
     *
     * @return string The value's unit.
     *
     * This also converts 'celsius' to '°C' and 'fahrenheit' to 'F'.
     */
    public function getUnit()
    {
        // Units are inconsistent. Only celsius and fahrenheit are not abbreviated. This check fixes that.
        // Also, the API started to return "metric" as temperature unit recently. Also fix that.
        if ($this->unit == 'celsius' || $this->unit == 'metric') {
            return "&deg;C";
        } elseif ($this->unit == 'fahrenheit') {
            return 'F';
        } else {
            return $this->unit;
        }
    }

    /**
     * Get the value.
     *
     * @return float The value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the value's description.
     *
     * @return string The value's description.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the value as formatted string with unit.
     *
     * @return string The value as formatted string with unit.
     *
     * The unit is not included if it is empty.
     */
    public function getFormatted()
    {
        if ($this->getUnit() != "") {
            return $this->getValue() . " " . $this->getUnit();
        } else {
            return (string)$this->getValue();
        }
    }
}
