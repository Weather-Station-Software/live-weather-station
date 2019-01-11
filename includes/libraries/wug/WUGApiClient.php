<?php

namespace WeatherStation\SDK\WeatherUnderground;

use WeatherStation\SDK\WeatherUnderground\AbstractCache;
use WeatherStation\SDK\WeatherUnderground\Exception as WUGException;
use WeatherStation\SDK\WeatherUnderground\Fetcher\WPFetcher;
use WeatherStation\SDK\WeatherUnderground\Fetcher\FetcherInterface;
use WeatherStation\SDK\WeatherUnderground\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * WeatherUnderground client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 * @license MIT
 */
class WUGApiClient
{

    /**
     * @var string $mainnUrl The api url to fetch data from.
     */
    private $mainnUrl = "http://api.wunderground.com/api/{key}/{features}/{settings}/q/{query}.{format}";

    
    /**
     * @var \WeatherStation\SDK\WeatherUnderground\AbstractCache|bool $cacheClass The cache class.
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
     * Constructs the WeatherUnderground object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from WeatherUnderground. Defaults to
     *                                          WPFetcher(). Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending wug\WeatherUnderground\Util\Cache.
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
            $fetcher = new WPFetcher();
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
     * @param string $key The API key.
     * @param string $features The features to call.
     * @param array $settings The settings of the query.
     * @param string $query The query itself.
     * @param string $format The format of the data fetched. Possible values are 'xml' or 'json' (default).
     *
     * @return string The url, ready to fetch.
     * @since 3.0.0
     *
     */
    private function buildUrl($key, $features, $settings, $query, $format = 'json') {
        $result = $this->mainnUrl;
        $result = str_replace('{key}', $key, $result);
        $result = str_replace('{features}', $features, $result);
        $result = str_replace('{settings}', implode('/', $settings), $result);
        $result = str_replace('{query}', $query, $result);
        $result = str_replace('{format}', $format, $result);
        return $result;
    }

    /**
     * Get the string returned by WeatherUnderground for a specific station.
     *
     * @param string $id The station id to get weather information for.
     * @param string $key The API key.
     * @param string $lang The language to use for descriptions, default is 'en'.
     * @param string $format The format of the data fetched. Possible values are 'xml' or 'json' (default).
     *
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.0.0
     */
    public function getRawStationData($id, $key, $lang = 'en', $format = 'json') {
        $features = 'conditions';
        $settings = array ('lang:' . strtoupper($lang), 'pws:1', 'bestfct:0');
        $query = 'pws:' . $id;
        $url = $this->buildUrl($key, $features, $settings, $query, $format);
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
