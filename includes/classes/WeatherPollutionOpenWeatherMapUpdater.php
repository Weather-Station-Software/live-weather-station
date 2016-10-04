<?php

namespace WeatherStation\SDK\OpenWeatherMap\Plugin;


/**
 * This class is responsible for all the croned updates from OpenWeatherMap pollution data.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.7.0
 */
class PollutionUpdater {

    use PollutionClient;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 2.7.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Do the main job.
     *
     * @since 2.7.0
     */
    public function cron_run(){
        $this->__run('Cron Engine');
    }
}