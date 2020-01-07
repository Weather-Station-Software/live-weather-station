<?php

namespace WeatherStation\SDK\Generic\Fetcher;

/**
 * @package Includes\Libraries
 * @author Originally written by Christian Flach <https://github.com/cmfcmf>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 * @license MIT
 */
class FileGetContentsFetcher implements FetcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch($url){
        $result = @file_get_contents($url);
        if (!$result) {
            throw new \Exception('Unable to access file', 12);
        }
        return $result;
    }
}
