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
 * @author Jason Rouet <https://jasonrouet.com/>.
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
    private static $lock_name = 'live_weather_station_background_process_lock';
    private static $lock_ttl = 600;
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
     * Check that a process class name is an existing process of this plugin.
     *
     * The name is stored in database: only a bare class name matching a file of the process directory is accepted.
     *
     * @param mixed $name The class name (without namespace).
     * @return boolean True if the name can be instantiated as a process.
     * @since 3.9.0
     */
    private static function is_known_process($name) {
        if (!is_string($name) || !preg_match('/^[A-Za-z0-9_]{1,80}$/', $name) || $name === 'Process') {
            return false;
        }
        if (!file_exists(LIVE_WEATHER_STATION_INCLUDES_DIR . 'process/' . $name . '.php')) {
            return false;
        }
        return is_subclass_of(self::$namespace . $name, self::$namespace . 'Process');
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param string $class_name The class name process.
     * @param array $args The args to pass to the class.
     * @since 3.6.0
     */
    public static function register($class_name, $args=array()) {
        if (!self::is_known_process($class_name)) {
            Logger::error('Background Process', null, null, null, null, null, 999, 'Unable to register background process: unknown class.');
            return;
        }
        $class_name = self::$namespace . $class_name;
        try {
            $process = new $class_name;
            $process->register($args);
        }
        catch (\Throwable $ex) {
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
            if (!isset($process['class']) || !self::is_known_process($process['class'])) {
                // Log once per process row and per hour, not on every cron run.
                $flag = 'lws_bgp_unknown_' . md5(isset($process['uuid']) ? (string)$process['uuid'] : serialize($process));
                if (!get_transient($flag)) {
                    set_transient($flag, 1, HOUR_IN_SECONDS);
                    Logger::error('Background Process', null, null, null, null, null, 999, 'Unable to run background process: unknown class.');
                }
                continue;
            }
            $class_name = self::$namespace . $process['class'];
            try {
                $p = new $class_name;
                $p->run(!$only_paused, $process['uuid']);
            }
            catch (\Throwable $ex) {
                Logger::error('Background Process', null, null, null, null, null, 999, 'Unable to run background process with class' . $class_name . '. Message: ' . $ex->getMessage());
            }
            live_weather_station_renew_lock(self::$lock_name, self::$lock_ttl);
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
        // Atomic run lock (see live_weather_station_acquire_lock()); it is renewed after each process, so a long job keeps it.
        $lock = self::$lock_name;
        if (!live_weather_station_acquire_lock($lock, self::$lock_ttl)) {
            Logger::info($this->facility, null, null, null, null, null, 0, 'Background process: another run is in progress, skipping.');
            return;
        }
        try {
            $this->do_run();
        }
        finally {
            delete_option($lock);
        }
    }

    /**
     * Do the main job (the lock is held by the caller).
     *
     * @since 3.9.0
     */
    private function do_run() {
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
