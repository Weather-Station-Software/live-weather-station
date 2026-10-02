<?php

namespace WeatherStation\SDK\WeatherFlow\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://jasonrouet.com/>.
 * @since 3.7.5
 * @license MIT
 */
class WPFetcher implements FetcherInterface
{

    public function fetch($url, $headers = array())
    {
        $args = array(
            'user-agent' => LIVE_WEATHER_STATION_PLUGIN_AGENT,
            'timeout' => ((int)get_option('live_weather_station_collection_http_timeout') > 0 ? max(5, min(120, (int)get_option('live_weather_station_collection_http_timeout'))) : 10),
            'headers' => (array)$headers,
            'blocking'    => true,
            'redirection' => 3,
            'limit_response_size' => 2097152,
        );
        // Not wp_safe_remote_get(): stations may live on a LAN.
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            return '';
        }
        return wp_remote_retrieve_body($response);
    }
}
