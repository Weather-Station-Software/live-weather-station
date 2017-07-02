<?php

/**
 * Dummy class autoloader for WeatherFlow SDK
 */

require_once(__DIR__ . '/WFLWApiClient.php');
require_once(__DIR__. '/Exception.php');
require_once(__DIR__. '/Fetcher/FetcherInterface.php');
require_once(__DIR__. '/Fetcher/CurlFetcher.php');
require_once(__DIR__. '/Fetcher/FileGetContentsFetcher.php');