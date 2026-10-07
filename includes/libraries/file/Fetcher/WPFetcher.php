<?php

namespace WeatherStation\SDK\Generic\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://jasonrouet.com/>.
 * @since 3.7.5
 * @license MIT
 */
class WPFetcher implements FetcherInterface
{

    public function fetch($url)
    {
        $args = array(
            'user-agent' => LIVE_WEATHER_STATION_PLUGIN_AGENT,
            'timeout' => ((int)get_option('live_weather_station_collection_http_timeout') > 0 ? max(5, min(120, (int)get_option('live_weather_station_collection_http_timeout'))) : 10),
            'blocking'    => true,
            'redirection' => 3,
            'limit_response_size' => 2097152,
        );
        // Not wp_safe_remote_get(): stations may live on a LAN.
        // 'user:pass@host' is sent as an explicit Basic authorization header (transport independent).
        $prepared = FileGetContentsFetcher::prepare_http($url);
        $url = $prepared[0];
        if (!empty($prepared[1])) {
            $args['headers'] = $prepared[1];
        }
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $code = wp_remote_retrieve_response_code($response);
            $message = wp_remote_retrieve_response_message($response);
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Plugin Check: exception messages are never echoed (they are caught and logged by the plugin), and they are either fixed strings or already sanitized.
            throw new \Exception(substr(sanitize_text_field((string)$message), 0, 200), (int)$code);
        }
        if ((int)wp_remote_retrieve_response_code($response) >= 400) {
            throw new \Exception('Unable to access file', (int)wp_remote_retrieve_response_code($response));
        }
        return wp_remote_retrieve_body($response);
    }
}
