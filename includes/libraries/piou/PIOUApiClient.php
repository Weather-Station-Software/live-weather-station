<?php

namespace WeatherStation\SDK\Pioupiou;

use WeatherStation\SDK\Pioupiou\AbstractCache;
use WeatherStation\SDK\Pioupiou\Exception as PIOUException;
use WeatherStation\SDK\Pioupiou\Fetcher\CurlFetcher;
use WeatherStation\SDK\Pioupiou\Fetcher\FetcherInterface;
use WeatherStation\SDK\Pioupiou\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * Pioupiou client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.5.0
 * @license MIT
 */
class PIOUApiClient
{

    /**
     * @var string $mainnUrl The api url to fetch data from.
     */
    private $mainnUrl = "https://api.pioupiou.fr/v1/live/{sensor_id}";

    
    /**
     * @var \WeatherStation\SDK\Pioupiou\AbstractCache|bool $cacheClass The cache class.
     */
    private $cacheClass = false;

    /**
     * @var int
     */
    private $seconds;

    /**
     * @var FetcherInterface The url fetcher.
     */
    private $fetcher;

    /**
     * Constructs the Pioupiou object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from Pioupiou. Defaults to
     *                                          CurlFetcher() if cURL is available. Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending piou\Util\Cache.
     * @api
     */
    public function __construct($fetcher = null, $cacheClass = false, $seconds = 600)
    {
        if ($cacheClass !== false && !($cacheClass instanceof AbstractCache)) {
            throw new \Exception("The cache class must implement the FetcherInterface!");
        }
        if (!is_numeric($seconds)) {
            throw new \Exception("\$seconds must be numeric.");
        }
        if (!isset($fetcher)) {
            $fetcher = (function_exists('curl_version')) ? new CurlFetcher() : new FileGetContentsFetcher();
        }
        if ($seconds == 0) {
            $cacheClass = false;
        }
        $this->cacheClass = $cacheClass;
        $this->seconds = $seconds;
        $this->fetcher = $fetcher;
    }

    /**
     * Build the url to fetch weather data from.
     *
     * @param string $sensor_id The sensor to query.
     *
     * @return string The url, ready to fetch.
     * @since 3.5.0
     *
     */
    private function buildUrl($sensor_id) {
        $result = $this->mainnUrl;
        $result = str_replace('{sensor_id}', $sensor_id, $result);
        return $result;
    }

    /**
     * Get the string returned by Pioupiou for a specific public station.
     *
     * @param string $id The station id to get weather information for.
     *
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.5.0
     */
    public function getRawPublicStationData($id) {
        $url = $this->buildUrl($id);
        return $this->cacheOrFetchResult($url);
    }

    /**
     * Fetches the result or delivers a cached version of the result.
     *
     * @param string $url The url to fetch.
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     */
    private function cacheOrFetchResult($url) {
        if ($this->cacheClass !== false) {
            $cache = $this->cacheClass;
            $cache->setSeconds($this->seconds);
            if ($cache->isCached($url)) {
                return $cache->getCached($url);
            }
            $result = $this->fetcher->fetch($url);
            $cache->setCached($url, $result);
        } else {
            $result = $this->fetcher->fetch($url);
        }
        return $result;
    }
}
