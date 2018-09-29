<?php

namespace WeatherStation\System\Background;

use WeatherStation\Process;




use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Handling as Schedules;
use WeatherStation\System\Data\Data;

/**
 * The class to perform background process.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class ProcessManager {

    use Schedules, Storage;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Background Process';
    private static $namespace = 'WeatherStation\Process\\';


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
     * Initialize the class and set its properties.
     *
     * @param string $class_name The class name process.
     * @since 3.6.0
     */
    public static function register($class_name) {
        $class_name = self::$namespace . $class_name;
        try {
            $process = new $class_name;
            $process->register();
        }
        catch (\Exception $ex) {
            Logger::error('Background Process', null, null, null, null, null, 999, 'Unable to run background process with class' . $class_name . '. Message: ' . $ex->getMessage());
        }
    }

    /**
     * Do the main job.
     *
     * @since 3.6.0
     */
    public function run(){
        $cron_id = Watchdog::init_chrono(Watchdog::$background_process_name);
        Logger::debug($this->facility, null, null, null, null, null, 0, 'Background process: starting main job.');




        //max_execution_time

        //microtime(true);














        Logger::debug($this->facility, null, null, null, null, null, 0, 'Background process: ending main job.');
        Watchdog::stop_chrono($cron_id);
    }

}