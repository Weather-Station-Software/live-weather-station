<?php

namespace WeatherStation\System\Background;

use WeatherStation\Process;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Query;
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

    use Schedules, Query;

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Background Process';
    private static $namespace = 'WeatherStation\Process\\';
    private $max_time = 0;
    private $start = 0;
    private $chrono = 0;


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
     * @param array $args The args to pass to the class.
     * @since 3.6.0
     */
    public static function register($class_name, $args=array()) {
        $class_name = self::$namespace . $class_name;
        try {
            $process = new $class_name;
            $process->register($args);
        }
        catch (\Exception $ex) {
            Logger::error('Background Process', null, null, null, null, null, 999, 'Unable to run background process with class' . $class_name . '. Message: ' . $ex->getMessage());
        }
    }

    /**
     * Do the main job.
     *
     * @param boolean $only_paused Optional. Only the paused processes.
     * @return boolean False if there's not process to run, True otherwise.
     * @since 3.6.0
     */
    private function _run($only_paused=false) {
        $this->start = round(microtime(true));
        $processes = self::get_ready_background_processes($only_paused);
        if (count($processes) === 0) {
            return false;
        }
        foreach ($processes as $process) {
            $class_name = self::$namespace . $process['class'];
            try {
                $p = new $class_name;
                $p->run(!$only_paused, $process['uuid']);
            }
            catch (\Exception $ex) {
                Logger::error('Background Process', null, null, null, null, null, 999, 'Unable to run background process with class' . $class_name . '. Message: ' . $ex->getMessage());
            }
            if ($this->chrono > $this->max_time) {
                break;
            }
        }
        $this->chrono += round(microtime(true)) - $this->start;
        return true;
    }

    /**
     * Do the main job.
     *
     * @since 3.6.0
     */
    public function run(){
        $cron_id = Watchdog::init_chrono(Watchdog::$background_process_name);
        Logger::info($this->facility, null, null, null, null, null, 0, 'Background process: starting main job.');
        if (ini_get('max_execution_time') < 180) {
            $this->max_time = (int)round(ini_get('max_execution_time') * 2 / 3);
        }
        else {
            $this->max_time = 120;
        }
        $this->chrono = 0;
        if ($this->_run()) {
            while($this->chrono < $this->max_time) {
                if (!$this->_run(true)) {
                    break;
                }
            }
        }
        Logger::info($this->facility, null, null, null, null, null, 0, 'Background process: ending main job.');
        Watchdog::stop_chrono($cron_id);
    }

}