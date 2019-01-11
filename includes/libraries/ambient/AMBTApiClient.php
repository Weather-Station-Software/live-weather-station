<?php

namespace WeatherStation\SDK\Ambient;

use WeatherStation\SDK\Ambient\AbstractCache;
use WeatherStation\SDK\Ambient\Exception as AMBTException;
use WeatherStation\SDK\Ambient\Fetcher\WPFetcher;
use WeatherStation\SDK\Ambient\Fetcher\FetcherInterface;
use WeatherStation\SDK\Ambient\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * Ambient client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.5.0
 * @license MIT
 */
class AMBTApiClient
{

    /**
     * @var string $mainUrl The api url to fetch data from.
     */
    private $mainUrl = "https://api.ambientweather.net/v1/devices?applicationKey={application}&apiKey={key}";

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // these application key is property of Ambient licensed to Pierre Lannoy, you CAN'T use it for your apps.
    // If you are thinking to develop something, get your application key here: https://dashboard.ambientweather.net
    private $application_key = 'aa2fe7d796fa4ccfa484c41592fc27044970de1019234d0599e7af14d42797fa';
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @var \WeatherStation\SDK\Ambient\AbstractCache|bool $cacheClass The cache class.
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

    private $key;

    /**
     * Constructs the Ambient object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from Ambient. Defaults to
     *                                          WPFetcher() . Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending ambient\Util\Cache.
     * @api
     */
    public function __construct($key, $fetcher = null, $cacheClass = false, $seconds = 600)
    {
        if ($cacheClass !== false && !($cacheClass instanceof AbstractCache)) {
            throw new \Exception("The cache class must implement the FetcherInterface!");
        }
        if (!is_numeric($seconds)) {
            throw new \Exception("\$seconds must be numeric.");
        }
        if (!isset($fetcher)) {
            $fetcher = new WPFetcher();
        }
        if ($seconds == 0) {
            $cacheClass = false;
        }
        $this->cacheClass = $cacheClass;
        $this->seconds = $seconds;
        $this->fetcher = $fetcher;
        $this->key = $key;
    }

    /**
     * Build the url to fetch weather data from.
     *
     * @return string The url, ready to fetch.
     * @since 3.5.0
     *
     */
    private function buildUrl() {
        $result = $this->mainUrl;
        $result = str_replace('{application}', $this->application_key, $result);
        $result = str_replace('{key}', $this->key, $result);
        return $result;
    }

    /**
     * Get the string returned by Ambient for all associated station.
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
