<?php

namespace WeatherStation\SDK\Generic;

use WeatherStation\SDK\Generic\AbstractCache;
use WeatherStation\SDK\Generic\Exception as FileException;
use WeatherStation\SDK\Generic\Fetcher\CurlFetcher;
use WeatherStation\SDK\Generic\Fetcher\FetcherInterface;
use WeatherStation\SDK\Generic\Fetcher\FileGetContentsFetcher;

/**
 * File client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 * @license MIT
 */
class FileClient
{

    /**
     * @var \WeatherStation\SDK\Generic\AbstractCache|bool $cacheClass The cache class.
     */
    private $cacheClass = false;

    /**
     * @var int
     */
    private $seconds;

    /**
     * @var FetcherInterface The file fetcher.
     */
    private $fetcher;

    /**
     * @var integer The type of connection.
     */
    private $type;

    /**
     * Constructs the FileClient object.
     *
     * @param integer $connection_type The type of fetcher to create if needed.
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from HTTP. Defaults to
     *                                          CurlFetcher() if cURL is available. Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending wug\HTTP\Util\Cache.
     * @api
     */
    public function __construct($connection_type, $fetcher = null, $cacheClass = false, $seconds = 600)
    {
        if ($cacheClass !== false && !($cacheClass instanceof AbstractCache)) {
            throw new \Exception("The cache class must implement the FetcherInterface!");
        }
        if (!is_numeric($seconds)) {
            throw new \Exception("\$seconds must be numeric.");
        }
        if (!isset($fetcher)) {
            if ($connection_type == 2 || $connection_type == 3) {
                $fetcher = (function_exists('curl_version')) ? new CurlFetcher() : new FileGetContentsFetcher();
            }
            else {
                $fetcher = new FileGetContentsFetcher();
            }
        }
        if ($seconds == 0) {
            $cacheClass = false;
        }
        $this->cacheClass = $cacheClass;
        $this->seconds = $seconds;
        $this->fetcher = $fetcher;
        $this->type = $connection_type;
    }

    /**
     * Get the string returned by HTTP for a specific station.
     *
     * @param string $url The full url with fqdn and filename where to obtain data files.
     * @return bool|string Returns false on failure and the fetched data in the format you specified on success.
     * @since 3.0.0
     */
    public function getRawStationData($url) {
        switch ($this->type) {
            case 1:
                $proto = '';//'file://';
                break;
            case 2:
                $proto = 'http://';
                break;
            case 3:
                $proto = 'https://';
                break;
            case 4:
                $proto = 'ftp://';
                break;
            case 5:
                $proto = 'ftps://';
                break;
            default:
                $proto = '';
        }
        return $this->cacheOrFetchResult($proto.$url);
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
