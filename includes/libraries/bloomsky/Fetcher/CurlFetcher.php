<?php

namespace WeatherStation\SDK\BloomSky\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.5.0
 * @license MIT
 */
class CurlFetcher implements FetcherInterface
{
    /**
     * @var array The Curl options to use. 
     */
    private $curlHeader;

    /**
     * Create a new CurlFetcher instance.
     * 
     * @param array $curlHeader The Curl header to use. See http://php.net/manual/de/function.curl-setopt.php
     * for a list of available options.
     */
    public function __construct($curlHeader = array())
    {
        $this->curlHeader = $curlHeader;
    }
    
    /**
     * {@inheritdoc}
     */
    public function fetch($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, get_option('live_weather_station_collection_http_timeout'));
        curl_setopt($ch, CURLOPT_USERAGENT, LWS_PLUGIN_AGENT);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$this->curlHeader);
        $content = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode != 200) {
            throw new \Exception((string)$content, $httpcode);
        }
        return $content;
    }
}
