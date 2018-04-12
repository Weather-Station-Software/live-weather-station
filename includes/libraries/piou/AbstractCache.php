<?php

namespace WeatherStation\SDK\Pioupiou;

/**
 * Abstract cache class to be overwritten by custom cache implementations.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.5.0
 * @license MIT
 */
abstract class AbstractCache
{
    /**
     * @var int $seconds Cache time in seconds.
     */
    protected $seconds;

    /**
     * Checks whether a cached weather data is available.
     *
     * @param string $url The unique url of the cached content.
     *
     * @return bool False if no cached information is available, otherwise true.
     *
     * You need to check if a cached result is outdated here. Return false in that case.
     */
    abstract public function isCached($url);

    /**
     * Returns cached weather data.
     *
     * @param string $url The unique url of the cached content.
     *
     * @return string|bool The cached data if it exists, false otherwise.
     */
    abstract public function getCached($url);

    /**
     * Saves cached weather data.
     *
     * @param string $url     The unique url of the cached content.
     * @param string $content The weather data to cache.
     *
     * @return bool True on success, false on failure.
     */
    abstract public function setCached($url, $content);

    /**
     * Set after how much seconds the cache shall expire.
     *
     * @param int $seconds
     */
    public function setSeconds($seconds)
    {
        $this->seconds = $seconds;
    }
}
