<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;

/**
 * This class builds elements of the map view for Windy maps.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

class WindyHandling {

    use Output;

    protected $type = 1;

    /**
     * Initialize the class and set its properties.
     *
     * @param array $common The common parameters to add.
     * @return integer The new map ID.
     * @since 3.7.0
     */
    public function new_map($common) {
        $params = array();
        $params['common'] = $common;
        $params['stations'] = array(94, 96, 125);
        return $this->add_new_map($this->type, lws__('New Windy map', 'live-weather-station'), $params);
    }


}