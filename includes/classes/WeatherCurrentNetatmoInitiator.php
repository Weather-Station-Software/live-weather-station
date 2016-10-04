<?php

namespace WeatherStation\SDK\Netatmo\Plugin;

/**
 * This class is responsible for the first update from Netatmo stations.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Initiator {

    use Client;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 3.0.0
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Detect the Netatmo stations.
     *
     * @return array An array containing stations details.
     *
     * @since 3.0.0
     */
    public function detect_stations(){
        return $this->__get_stations();
    }

    /**
     * Do the main job.
     *
     * @param boolean $auto_init Optional. Force creation of stations.
     *
     * @since 3.0.0
     */
    public function run($auto_init=false){
        if ($auto_init) {
            $this->__get_stations(true);
        }
        $this->__run('Backend');
    }
}