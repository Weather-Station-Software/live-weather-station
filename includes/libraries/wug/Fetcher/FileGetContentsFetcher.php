<?php

namespace WeatherStation\SDK\WeatherUnderground\Fetcher;

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
     * {@inheritdoc}
     */
    public function fetch($url)
    {
        return file_get_contents($url);
    }
}
