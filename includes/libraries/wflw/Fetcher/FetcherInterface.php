<?php

namespace WeatherStation\SDK\WeatherFlow\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://jasonrouet.com/>.
 * @since 3.3.0
 * @license MIT
 */
interface FetcherInterface
{
    /**
     * Fetch contents from the specified url.
     *
     * @param string $url The url to be fetched.
     * @param array $headers Optional. Additional HTTP headers.
     *
     * @return string The fetched content.
     *
     * @api
     */
    public function fetch($url, $headers = array());
}
