<?php

namespace WeatherStation\SDK\BloomSky\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.7.5
 * @license MIT
 */
class WPFetcher implements FetcherInterface
{
    /**
     * @var array The WP options to use.
     */
    private $wpHeader;

    /**
     * Create a new WPFetcher instance.
     *
     * @param array $wpHeader The WP header to use.
     */
    public function __construct($wpHeader = array())
    {
        $this->wpHeader = $wpHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($url)
    {
        $args = array(
            'user-agent' => LWS_PLUGIN_AGENT,
            'timeout' => get_option('live_weather_station_collection_http_timeout'),
            'blocking'    => true,
        );
        foreach ($this->wpHeader as $f=>$v) {
            $args['headers'][$f] = $v;
        }
        $response = wp_remote_get($url, $args);
        if (wp_remote_retrieve_response_code($response) != 200) {
            $message = (string)wp_remote_retrieve_body($response);
            if ($message === '') {
                $message = 'Unknown error.';
            }
            $code = wp_remote_retrieve_response_code($response);
            if ($code === '') {
                $code = 999;
            }
            throw new \Exception($message, $code);
        }
        return wp_remote_retrieve_body($response);
    }
}
