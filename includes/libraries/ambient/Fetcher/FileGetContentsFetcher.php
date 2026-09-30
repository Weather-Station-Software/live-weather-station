<?php

namespace WeatherStation\SDK\Ambient\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.0.0
 * @license MIT
 */
class FileGetContentsFetcher implements FetcherInterface
{
    /**
     * Maximum size of a fetched resource, in bytes.
     */
    const MAX_SIZE = 2097152;

    /**
     * Validates a resource locator.
     *
     * Only three kinds of locators are accepted: plain local paths (no scheme), ftp:// / ftps:// urls and
     * http:// / https:// urls. Every other stream wrapper (php://, phar://, data:, file://, glob://, expect://...)
     * is rejected, as well as NUL bytes and '..' path segments (except in a local path resolving inside the WordPress tree).
     *
     * @param string $url The locator to check.
     * @return string 'local', 'ftp' or 'http'.
     * @throws \Exception If the locator is not acceptable.
     */
    public static function validate($url){
        if (!is_string($url) || $url === '' || strlen($url) > 2048 || strpos($url, "\0") !== false) {
            throw new \Exception('Invalid resource', 12);
        }
        if (preg_match('/[\x00-\x1f\x7f]/', $url)) {
            throw new \Exception('Invalid resource', 12);
        }
        $has_dots = (preg_match('#(^|[\\\\/])\.\.([\\\\/]|$)#', $url) === 1);
        if (preg_match('#^([a-z][a-z0-9+.\-]+):#i', $url, $m)) {
            if ($has_dots) {
                throw new \Exception('Invalid resource', 12);
            }
            // A single letter is a Windows drive, more is a scheme.
            switch (strtolower($m[1])) {
                case 'http':
                case 'https':
                    if (!preg_match('#^https?://[^/\s]+#i', $url)) {
                        throw new \Exception('Invalid resource', 12);
                    }
                    return 'http';
                case 'ftp':
                case 'ftps':
                    if (!preg_match('#^ftps?://[^/\s]+#i', $url)) {
                        throw new \Exception('Invalid resource', 12);
                    }
                    return 'ftp';
                default:
                    throw new \Exception('Unsupported resource type', 12);
            }
        }
        if ($has_dots) {
            // A local path with '..' segments is accepted only if it resolves inside the WordPress tree.
            $real = @realpath($url);
            $ok = false;
            if ($real !== false) {
                foreach (array(ABSPATH, WP_CONTENT_DIR) as $base) {
                    $base = @realpath($base);
                    if ($base !== false && strpos($real . DIRECTORY_SEPARATOR, rtrim($base, '\\/') . DIRECTORY_SEPARATOR) === 0) {
                        $ok = true;
                    }
                }
            }
            if (!$ok) {
                throw new \Exception('Invalid resource', 12);
            }
        }
        return 'local';
    }

    /**
     * Split the 'user:pass@' part of an http(s) url into an explicit Basic authorization header.
     *
     * @param string $url The url.
     * @return array array(url without credentials, array of headers).
     */
    public static function prepare_http($url){
        $headers = array();
        if (preg_match('#^(https?://)([^/\s@]*)@([^/\s]+)(.*)$#is', $url, $m) && $m[2] !== '') {
            $headers['Authorization'] = 'Basic ' . base64_encode(rawurldecode($m[2]));
            $url = $m[1] . $m[3] . $m[4];
        }
        return array($url, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($url){
        $kind = self::validate($url);
        if ($kind === 'http') {
            // The stations are often on a LAN, so private addresses must stay reachable: wp_safe_remote_get() is
            // deliberately not used here.
            $timeout = (int)get_option('live_weather_station_collection_http_timeout');
            $prepared = self::prepare_http($url);
            $url = $prepared[0];
            $response = wp_remote_get($url, array(
                'headers' => $prepared[1],
                'user-agent' => defined('LWS_PLUGIN_AGENT') ? LWS_PLUGIN_AGENT : 'WeatherStation',
                'timeout' => ($timeout > 0 ? max(5, min(120, $timeout)) : 10),
                'redirection' => 3,
                'limit_response_size' => self::MAX_SIZE,
            ));
            if (is_wp_error($response) || (int)wp_remote_retrieve_response_code($response) !== 200) {
                throw new \Exception('Unable to access file', 12);
            }
            $result = wp_remote_retrieve_body($response);
        }
        else {
            $context = stream_context_create(array('ftp' => array('proxy' => null), 'http' => array('timeout' => 10)));
            $result = @file_get_contents($url, false, $context, 0, self::MAX_SIZE);
        }
        if (!$result) {
            throw new \Exception('Unable to access file', 12);
        }
        return $result;
    }
}
