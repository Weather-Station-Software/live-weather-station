<?php

namespace WeatherStation\SDK\WeatherLink;

use WeatherStation\SDK\WeatherLink\AbstractCache;
use WeatherStation\SDK\WeatherLink\Exception as WLINKException;
use WeatherStation\SDK\WeatherLink\Fetcher\WPFetcher;
use WeatherStation\SDK\WeatherLink\Fetcher\FetcherInterface;
use WeatherStation\SDK\WeatherLink\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * WeatherLink client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.8.0
 * @license MIT
 */
class WLINKApiClient
{

    /**
     * @var string $mainnUrl The api url to fetch data from.
     */
    private $mainnUrl = "https://api.weatherlink.com/v1/{command}.json?user={service_did}&pass={service_ownerpass}&apiToken={service_apitoken}";
    
    /**
     * @var \WeatherStation\SDK\WeatherLink\AbstractCache|bool $cacheClass The cache class.
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
     * Constructs the WeatherLink object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from WeatherLink. Defaults to
     *                                          WPFetcher(). Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending wlink\WeatherLink\Util\Cache.
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
     * @param string $command The features to execute.
     * @param string $params The parameters for the query.
     *
     * @return string The url, ready to fetch.
     * @since 3.8.0
     *
     */
    private function buildUrl($command, $params = '') {
        $result = $this->mainnUrl;
        $result = str_replace('{command}', $command, $result);
        $id = array();
        $exp = array();
        if ($params !== '') {
            $exp = explode(LWS_SERVICE_SEPARATOR, $params);
        }
        if (count($exp) !== 3) {
            $id['service_did'] = '-';
            $id['service_apitoken'] = '-';
            $id['service_ownerpass'] = '-';
        }
        else {
            $id['service_did'] = $exp[0];
            $id['service_apitoken'] = $exp[1];
            $id['service_ownerpass'] = $exp[2];
        }
        $result = str_replace('{service_did}', $id['service_did'], $result);
        $result = str_replace('{service_apitoken}', $id['service_apitoken'], $result);
        $result = str_replace('{service_ownerpass}', $id['service_ownerpass'], $result);
        return $result;
    }

    /**
     * Get the json returned by WeatherLink for a specific station status.
     *
     * @param string $id The service id.
     *
     * @return bool|string Returns false on failure and the fetched data on success.
     * @since 3.8.0
     */
    public function getRawStationData($id) {
        $command = 'NoaaExt';
        $url = $this->buildUrl($command, $id);
        return $this->cacheOrFetchResult($url);
    }

    /**
     * Get the json returned by WeatherLink for a specific station meta.
     *
     * @param string $id The service id.
     *
     * @return bool|string Returns false on failure and the fetched data on success.
     * @since 3.8.0
     */
    public function getRawStationMeta($id) {
        $command = 'StationStatus';
        $url = $this->buildUrl($command, $id);
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
