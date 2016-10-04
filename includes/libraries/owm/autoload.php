<?php

/**
 * Dummy class autoloader for OpenWeatherMap SDK
 */

require_once(__DIR__. '/OWMApiClient.php');
require_once(__DIR__. '/Exception.php');
require_once(__DIR__. '/CurrentWeather.php');
require_once(__DIR__. '/Fetcher/FetcherInterface.php');
require_once(__DIR__. '/Fetcher/CurlFetcher.php');
require_once(__DIR__. '/Fetcher/FileGetContentsFetcher.php');
require_once(__DIR__. '/Util/City.php');
require_once(__DIR__. '/Util/Sun.php');
require_once(__DIR__. '/Util/Temperature.php');
require_once(__DIR__. '/Util/Time.php');
require_once(__DIR__. '/Util/Unit.php');
require_once(__DIR__. '/Util/Weather.php');
require_once(__DIR__. '/Util/Wind.php');