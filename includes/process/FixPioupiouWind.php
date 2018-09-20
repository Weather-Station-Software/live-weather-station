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
     * @return string The UUID of the process.
     * @since 3.6.0
     */
    protected function uuid() {
        return '6dd8cc5e-226b-4eeb-b81c-e2f22d144707';
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.6.0
     */
    protected function name($translated=true) {
        return __('Pioupiou stations wind fix', 'live-weather-station');
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.6.0
     */
    protected function description() {
        return sprintf(__('This fix allows %s to rightly handle daily and historical wind angle/source for V1 & V2 versions of the Pioupiou stations.', 'live-weather-station'), LWS_PLUGIN_NAME);
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.6.0
     */
    protected function priority(){
        return 10;
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.6.0
     */
    protected function is_needed() {

        return true;
    }

    /**
     * Run the process.
     *
     * @since 3.6.0
     */
    protected function run_core(){

    }

}