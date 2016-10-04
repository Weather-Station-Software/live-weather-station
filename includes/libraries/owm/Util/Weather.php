<?php

namespace WeatherStation\SDK\OpenWeatherMap\Util;

/**
 * The weather class representing a weather object.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class Weather
{
    /**
     * @var int The weather id.
     */
    public $id;

    /**
     * @var string The weather description.
     */
    public $description;

    /**
     * @var string the icon name.
     */
    public $icon;

    /**
     * @var string The url for icons.
     *
     * @see self::getIconUrl() to see how it is used.
     */
    private $iconUrl = "http://openweathermap.org/img/w/%s.png";

    /**
     * Create a new weather object.
     *
     * @param int    $id          The icon id.
     * @param string $description The weather description.
     * @param string $icon        The icon name.
     *
     * @internal
     */
    public function __construct($id, $description, $icon)
    {
        $this->id = (int)$id;
        $this->description = (string)$description;
        $this->icon = (string)$icon;
    }

    /**
     * Get the weather description.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->description;
    }

    /**
     * Get the icon url.
     *
     * @return string The icon url.
     */
    public function getIconUrl()
    {
        return str_replace("%s", $this->icon, $this->iconUrl);
    }
}
