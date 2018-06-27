<?php

/**
 * Dummy class autoloader for BloomSky SDK
 */

require_once(__DIR__ . '/BSKYApiClient.php');
require_once(__DIR__. '/Exception.php');
require_once(__DIR__. '/Fetcher/FetcherInterface.php');
require_once(__DIR__. '/Fetcher/CurlFetcher.php');
require_once(__DIR__. '/Fetcher/FileGetContentsFetcher.php');