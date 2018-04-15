<?php

namespace WeatherStation\SDK\Pioupiou\Plugin;

/**
 * This class is responsible for the first update from Pioupiou stations.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */
class StationInitiator {

    use PublicClient;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 3.5.0
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Do the main job.
     *
     * @since 3.5.0
     */
    public function run(){
        $this->__run('Backend');
    }
}