<?php

namespace WeatherStation\SDK\WeatherLink;

use WeatherStation\SDK\WeatherLink\AbstractCache;
use WeatherStation\SDK\WeatherLink\Exception as WLINKException;
use WeatherStation\SDK\WeatherLink\Fetcher\WPFetcher;
use WeatherStation\SDK\WeatherLink\Fetcher\FetcherInterface;
use WeatherStation\SDK\WeatherLink\Fetcher\FileGetContentsFetcher;

use WeatherStation\System\Logs\Logger;

/**
 * WeatherLink client implementation.
 *
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Jason Rouet <https://www.jasonrouet.com/>.
 * @since 3.8.0
 * @license MIT
 */
class WLINKApiClient
{

    /**
     * @var string $mainnUrl The api url to fetch data from.
     */
    private $mainnUrl = "https://api.weatherlink.com/v1/{command}.json?user={service_did}&pass={service_ownerpass}&apiToken={service_apitoken}";
    
    /**
     * @var int Maximum length of the device ID. The three values joined by the separator must fit in the 250 characters of the service_id column.
     * @since 3.9.0
     */
    const MAX_DID_LENGTH = 40;

    /**
     * @var int Maximum length of the API token (v1 tokens have 32 or 33 characters).
     * @since 3.9.0
     */
    const MAX_TOKEN_LENGTH = 64;

    /**
     * @var int Maximum length of the account password (40 + 64 + 120 + 2 separators of 11 characters = 246 <= 250).
     * @since 3.9.0
     */
    const MAX_PASS_LENGTH = 120;

    /**
     * Join the three WeatherLink credentials in a service id, after having checked them.
     *
     * Nothing is ever truncated: a value that is too long, or that contains the separator, is refused.
     *
     * @param string $did The device ID.
     * @param string $token The API token.
     * @param string $pass The account password.
     * @return array An array with 'service_id' (string, empty on error) and 'error' (string, empty on success).
     * @since 3.9.0
     */
    public static function join_credentials($did, $token, $pass) {
        $did = (string)$did;
        $token = (string)$token;
        $pass = (string)$pass;
        $fields = array(
            array($did, self::MAX_DID_LENGTH, __('Device ID', 'live-weather-station')),
            array($token, self::MAX_TOKEN_LENGTH, __('API Token', 'live-weather-station')),
            array($pass, self::MAX_PASS_LENGTH, __('Password', 'live-weather-station')));
        foreach ($fields as $field) {
            if (mb_strlen($field[0], 'UTF-8') > $field[1]) {
                return array('service_id' => '', 'error' => sprintf(__('%1$s is too long (%2$d characters maximum).', 'live-weather-station'), $field[2], $field[1]));
            }
            if (strpos($field[0], LWS_SERVICE_SEPARATOR) !== false) {
                return array('service_id' => '', 'error' => sprintf(__('%s contains a forbidden character sequence.', 'live-weather-station'), $field[2]));
            }
        }
        $joined = $did . LWS_SERVICE_SEPARATOR . $token . LWS_SERVICE_SEPARATOR . $pass;
        // A value ending or starting with part of the separator could shift the split: the round trip must be exact.
        if (explode(LWS_SERVICE_SEPARATOR, $joined) !== array($did, $token, $pass)) {
            return array('service_id' => '', 'error' => __('Unable to save these WeatherLink credentials: they contain a forbidden character sequence.', 'live-weather-station'));
        }
        return array('service_id' => $joined, 'error' => '');
    }

    /**
     * @var \WeatherStation\SDK\WeatherLink\AbstractCache|bool $cacheClass The cache class.
     */
    private $cacheClass = false;

    /**
     * @var int
     */
    private $seconds;

    /**
     * @var FetcherInterface The url fetcher.
     */
    private $fetcher;

    /**
     * Constructs the WeatherLink object.
     *
     * @param null|FetcherInterface $fetcher    The interface to fetch the data from WeatherLink. Defaults to
     *                                          WPFetcher(). Otherwise defaults to
     *                                          FileGetContentsFetcher() using 'file_get_contents()'.
     * @param bool|string           $cacheClass If set to false, caching is disabled. Otherwise this must be a class
     *                                          extending AbstractCache. Defaults to false.
     * @param int                   $seconds    How long weather data shall be cached. Default 10 minutes.
     *
     * @throws \Exception If $cache is neither false nor a valid callable extending wlink\WeatherLink\Util\Cache.
     * @api
     */
    public function __construct($fetcher = null, $cacheClass = false, $seconds = 600)
    {
        if ($cacheClass !== false && !($cacheClass instanceof AbstractCache)) {
            throw new \Exception("The cache class must implement the FetcherInterface!");
        }
        if (!is_numeric($seconds)) {
            throw new \Exception("\$seconds must be numeric.");
        }
        if (!isset($fetcher)) {
            $fetcher = new WPFetcher();
        }
        if ($seconds == 0) {
            $cacheClass = false;
        }
        $this->cacheClass = $cacheClass;
        $this->seconds = $seconds;
        $this->fetcher = $fetcher;
    }

    /**
     * Build the url to fetch weather data from.
     *
     * @param string $command The features to execute.
     * @param string $params The parameters for the query.
     *
     * @return string The url, ready to fetch.
     * @since 3.8.0
     *
     */
    private function buildUrl($command, $params = '') {
        $result = $this->mainnUrl;
        $result = str_replace('{command}', rawurlencode((string)$command), $result);
        $id = array();
        $exp = array();
        if ($params !== '') {
            $exp = explode(LWS_SERVICE_SEPARATOR, $params);
        }
        if (count($exp) !== 3) {
            $id['service_did'] = '-';
            $id['service_apitoken'] = '-';
            $id['service_ownerpass'] = '-';
        }
        else {
            $id['service_did'] = $exp[0];
            $id['service_apitoken'] = $exp[1];
            $id['service_ownerpass'] = $exp[2];
        }
        $result = str_replace('{service_did}', rawurlencode((string)$id['service_did']), $result);
        $result = str_replace('{service_apitoken}', rawurlencode((string)$id['service_apitoken']), $result);
        $result = str_replace('{service_ownerpass}', rawurlencode((string)$id['service_ownerpass']), $result);
        return $result;
    }

    /**
     * Get the json returned by WeatherLink for a specific station status.
     *
     * @param string $id The service id.
     *
     * @return bool|string Returns false on failure and the fetched data on success.
     * @since 3.8.0
     */
    public function getRawStationData($id) {
        $command = 'NoaaExt';
        $url = $this->buildUrl($command, $id);
        return $this->cacheOrFetchResult($url);
    }

    /**
     * Get the json returned by WeatherLink for a specific station meta.
     *
     * @param string $id The service id.
     *
     * @return bool|string Returns false on failure and the fetched data on success.
     * @since 3.8.0
     */
    public function getRawStationMeta($id) {
        $command = 'StationStatus';
        $url = $this->buildUrl($command, $id);
        return $this->cacheOrFetchResult($url);
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
