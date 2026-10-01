<?php

/**
 * Dummy class autoloader for BloomSky SDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once(__DIR__ . '/BSKYApiClient.php');
require_once(__DIR__. '/Exception.php');
require_once(__DIR__. '/Fetcher/FetcherInterface.php');
require_once(__DIR__. '/Fetcher/WPFetcher.php');
require_once(__DIR__. '/Fetcher/FileGetContentsFetcher.php');