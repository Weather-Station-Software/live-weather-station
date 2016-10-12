<?php

namespace WeatherStation\SDK\WeatherUnderground\Plugin;

/**
 * This class is responsible for all the croned updates from WeatherUnderground station data.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class StationUpdater {

    use StationClient;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 3.0.0
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $Live_Weather_Station, $version ) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Do the main job.
     *
     * @since 3.0.0
     */
    public function cron_run(){
        $this->__run('Cron Engine');
    }
}