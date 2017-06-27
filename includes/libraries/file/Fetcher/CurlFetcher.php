<?php

namespace WeatherStation\SDK\Generic\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 * @license MIT
 */
class CurlFetcher implements FetcherInterface
{
    /**
     * @var array The Curl options to use. 
     */
    private $curlOptions;

    /**
     * Create a new CurlFetcher instance.
     * 
     * @param array $curlOptions The Curl options to use. See http://php.net/manual/de/function.curl-setopt.php
     * for a list of available options.
     */
    public function __construct($curlOptions = array())
    {
        $this->curlOptions = $curlOptions;
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
        curl_setopt_array($ch, $this->curlOptions);
        
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}
