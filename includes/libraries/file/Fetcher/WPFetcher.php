<?php

namespace WeatherStation\SDK\Generic\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
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
        if (is_wp_error($response)) {
            $code = wp_remote_retrieve_response_code($response);
            $message = wp_remote_retrieve_response_message($response);
            throw new \Exception($message, (int)$code);
        }
        return wp_remote_retrieve_body($response);
    }
}
