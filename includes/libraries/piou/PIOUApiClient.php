<?php

namespace WeatherStation\SDK\Pioupiou;

use WeatherStation\SDK\Pioupiou\AbstractCache;
use WeatherStation\SDK\Pioupiou\Exception as PIOUException;
use WeatherStation\SDK\Pioupiou\Fetcher\WPFetcher;
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
     * @var string $mainnUrl The "live" api url to fetch data from.
     */
    private $mainnUrl = "https://api.pioupiou.fr/v1/live/{sensor_id}";

    /**
     * @var string $archiveUrl The "archive" api url to fetch data from.
     */
    private $archiveUrl = "https://api.pioupiou.fr/v1/archive/{sensor_id}?start={start}&stop={stop}";


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
     *                                          WPFetcher(). Otherwise defaults to
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
     * Build the url to fetch weather data from.
     *
     * @param string $sensor_id The sensor to query.
     * @param string $start The UTC starting date.
     * @param string $stop The UTC ending date.
     * @return string The url, ready to fetch.
     * @since 3.5.0
     *
     */
    private function buildArchiveUrl($sensor_id, $start, $stop) {
        $result = $this->archiveUrl;
        $result = str_replace('{sensor_id}', $sensor_id, $result);
        $result = str_replace('{start}', $start, $result);
        $result = str_replace('{stop}', $stop, $result);
        return $result;
    }

    /**
     * Get the string returned by Pioupiou for a specific public station.
     *
     * @param string $id The station id to get archive for.
     * @param string $start_date The UTC starting date.
     * @param string $end_date The UTC ending date.
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.7.0
     */
    public function getRawPublicStationArchive($id, $start_date, $end_date) {
        $url = $this->buildArchiveUrl($id, $start_date, $end_date);
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
