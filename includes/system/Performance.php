<?php

namespace WeatherStation\System\Analytics;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;

/**
 * The class to compute and maintain consistency of performance statistics.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
class Performance {

    use Storage;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Analytics';


    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Delete old records.
     *
     * @since 3.1.0
     */
    public function rotate() {
        Cache::rotate();
        Logger::notice($this->facility,null,null,null,null,null,null,'Performance statistics data cleaned.');
    }
}