<?php

namespace WeatherStation\SDK\OpenWeatherMap\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
interface FetcherInterface
{
    /**
     * Fetch contents from the specified url.
     *
     * @param string $url The url to be fetched.
     *
     * @return string The fetched content.
     *
     * @api
     */
    public function fetch($url);
}
