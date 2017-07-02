<?php

namespace WeatherStation\SDK\WeatherFlow;

use WeatherStation\SDK\WeatherFlow\AbstractCache;
use WeatherStation\SDK\WeatherFlow\Exception as WFLWException;
use WeatherStation\SDK\WeatherFlow\Fetcher\CurlFetcher;
use WeatherStation\SDK\WeatherFlow\Fetcher\FetcherInterface;
use WeatherStation\SDK\WeatherFlow\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * WeatherFlow client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.3.0
 * @license MIT
 */
class WFLWApiClient
{

    /**
     * @var string $mainnUrl The api url to fetch data from.
     */
    private $mainnUrl = "https://swd.weatherflow.com/swd/rest/{command}/{params}";

    
    /**
     * @var \WeatherStation\SDK\WeatherFlow\AbstractCache|bool $cacheClass The cache class.
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
     * Constructs the WeatherFlow object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from WeatherFlow. Defaults to
     *                                          CurlFetcher() if cURL is available. Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending wflw\WeatherFlow\Util\Cache.
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
     * @param string $command The features to execute.
     * @param array $params The parameters to add.
     *
     * @return string The url, ready to fetch.
     * @since 3.3.0
     *
     */
    private function buildUrl($command, $params = array()) {
        $result = $this->mainnUrl;
        $result = str_replace('{command}', $command, $result);
        if (count($params) > 0) {
            $result = str_replace('{params}', '?' . implode('&', $params), $result);
        }
        else {
            $result = str_replace('{params}', '', $result);
        }
        return $result;
    }

    /**
     * Get the string returned by WeatherFlow for a specific public station.
     *
     * @param string $id The station id to get weather information for.
     * @param string $key The API key.
     *
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.3.0
     */
    public function getRawPublicStationData($id, $key) {
        $command = 'observations/station/' . $id;
        $url = $this->buildUrl($command, array('api_key='.$key));
        return $this->cacheOrFetchResult($url);
    }

    /**
     * Get the string returned by WeatherFlow for a specific private station.
     *
     * @param string $id The station id to get weather information for.
     * @param string $key The API key.
     *
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.3.0
     */
    public function getRawPrivateStationData($id, $key) {
        $command = 'observations/station/' . $id;
        $url = $this->buildUrl($command, array('token='.$key));
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
