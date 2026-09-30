<?php

namespace WeatherStation\SDK\BloomSky\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
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
            'timeout' => ((int)get_option('live_weather_station_collection_http_timeout') > 0 ? max(5, min(120, (int)get_option('live_weather_station_collection_http_timeout'))) : 10),
            'blocking'    => true,
            'redirection' => 3,
            'limit_response_size' => 2097152,
        );
        foreach ($this->wpHeader as $f=>$v) {
            $args['headers'][$f] = $v;
        }
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            throw new \Exception(substr(sanitize_text_field($response->get_error_message()), 0, 200), 999);
        }
        if (wp_remote_retrieve_response_code($response) != 200) {
            $message = substr(sanitize_text_field((string)wp_remote_retrieve_body($response)), 0, 200);
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
