<?php

namespace WeatherStation\Process;

/**
 * The base class of process.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class FixPioupiouWind extends Process {



    /**
     * Get the UUID of the process.
     *
     * @since 3.6.0
     */
    protected function uuid() {
        return '6dd8cc5e-226b-4eeb-b81c-e2f22d144707';
    }

    /**
     * Get the name of the process.
     *
     * @since 3.6.0
     */
    protected function name() {
        return __('Pioupiou stations wind fix', 'live-weather-station');
    }

    /**
     * Get the description of the process.
     *
     * @since 3.6.0
     */
    protected function description() {
        return __('This fix allows Weather Station to rightly handle historical and daily wind angle/source for V1 & V2 versions of the Pioupiou stations.', 'live-weather-station');
    }

    /**
     * Run the process.
     *
     * @since 3.6.0
     */
    protected function run_core(){

    }

}