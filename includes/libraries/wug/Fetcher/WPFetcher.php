<?php

namespace WeatherStation\SDK\WeatherUnderground\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.7.5
 * @license MIT
 */
class WPFetcher implements FetcherInterface
{

    public function fetch($url)
    {
        $args = array(
            'user-agent' => LWS_PLUGIN_AGENT,
            'timeout' => get_option('live_weather_station_collection_http_timeout'),
            'blocking'    => true,
        );
        $response = wp_remote_get($url, $args);
        return wp_remote_retrieve_body($response);
    }
}
