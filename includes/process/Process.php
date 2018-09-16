<?php

namespace WeatherStation\Process;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Handling as Schedules;
use WeatherStation\System\Data\Data;

/**
 * The base class of process.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
abstract class Process {

    use Schedules, Storage;

    private $Live_Weather_Station;
    private $version;
    private $params = null;



    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.6.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Get the UUID of the process.
     *
     * @since 3.6.0
     */
    protected abstract function uuid();

    /**
     * Get the name of the process.
     *
     * @since 3.6.0
     */
    protected abstract function name();

    /**
     * Get the description of the process.
     *
     * @since 3.6.0
     */
    protected abstract function description();

    /**
     * Run the process.
     *
     * @since 3.6.0
     */
    protected abstract function run_core();

}