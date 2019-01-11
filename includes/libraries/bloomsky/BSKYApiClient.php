<?php

namespace WeatherStation\SDK\BloomSky;

use WeatherStation\SDK\BloomSky\AbstractCache;
use WeatherStation\SDK\BloomSky\Exception as BSKYException;
use WeatherStation\SDK\BloomSky\Fetcher\WPFetcher;
use WeatherStation\SDK\BloomSky\Fetcher\FetcherInterface;
use WeatherStation\SDK\BloomSky\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * BloomSky client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.5.0
 * @license MIT
 */
class BSKYApiClient
{

    /**
     * @var string $mainUrl The api url to fetch data from.
     */
    private $mainUrl = "https://api.bloomsky.com/api/skydata/";

    
    /**
     * @var \WeatherStation\SDK\BloomSky\AbstractCache|bool $cacheClass The cache class.
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
     * Constructs the BloomSky object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from BloomSky. Defaults to
     *                                          WPFetcher(). Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending bloomsky\Util\Cache.
     * @api
     */
    public function __construct($config=array(), $fetcher = null, $cacheClass = false, $seconds = 600)
    {
        if ($cacheClass !== false && !($cacheClass instanceof AbstractCache)) {
            throw new \Exception("The cache class must implement the FetcherInterface!");
        }
        if (!is_numeric($seconds)) {
            throw new \Exception("\$seconds must be numeric.");
        }
        if (!isset($fetcher)) {
            $fetcher = new WPFetcher($config);
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
     * @return string The url, ready to fetch.
     * @since 3.5.0
     *
     */
    private function buildUrl() {
        $result = $this->mainUrl . '?unit=intl';
        return $result;
    }

    /**
     * Get the string returned by BloomSky for all associated station.
     *
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.5.0
     */
    public function getData() {
        $url = $this->buildUrl();
        return json_decode($this->cacheOrFetchResult($url), true);
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
