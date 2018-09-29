<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query;

/**
 * A process to expand wind measurements for existing stations.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class WindExpander extends Process {

    use Query;


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
        return __('Wind angle / Wind source expander', 'live-weather-station');
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.6.0
     */
    protected function description() {
        return sprintf(__('This fix allows %s to handle daily and historical wind angle / wind source measurements for all types of stations.', 'live-weather-station'), LWS_PLUGIN_NAME);
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.6.0
     */
    protected function message() {
        return '';
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
        if (!(bool)get_option('live_weather_station_collect_history')) {
            return false;
        }
        if (count($this->get_stations_informations()) === 0) {
            return false;
        }
        return true;
    }

    /**
     * Init the process.
     *
     * @since 3.6.0
     */
    protected function init_core(){
        $this->params['stations'] = array();
        $this->params['stations']['todo'] = array();
        $this->params['stations']['done'] = array();
        foreach ($this->get_stations_informations() as $station) {
            $this->params['stations']['todo'][$station['station_id']] = $station['station_type'];
        }
    }

    /**
     * Run the process.
     *
     * @since 3.6.0
     */
    protected function run_core(){
        if (count($this->params['stations']['todo']) > 0) {

            //MAIN CORE



        }

        if (count($this->params['stations']['todo']) === 0) {
            $this->change_state($this->state_end);
        }
    }

}