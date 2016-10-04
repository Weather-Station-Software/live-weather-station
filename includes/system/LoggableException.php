<?php

namespace WeatherStation\System\Logs;

/**
 * Loggable exception to be used by Logger class.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */
class LoggableException extends \Exception {

    protected $level;
    protected $system;
    protected $service;
    protected $device_name;
    protected $device_id;
    protected $module_name;
    protected $module_id;

    public function __construct($level = 'unknown', $system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = 0, $message = '') {
        parent::__construct($message, $code);
        $this->level = $level;
        $this->system = $system;
        $this->service = $service;
        $this->device_name = $device_name;
        $this->device_id = $device_id;
        $this->module_name = $module_name;
        $this->module_id = $module_id;
    }

    public function getLevel() { return $this->level;}
    public function getSystem() { return $this->system;}
    public function getService() { return $this->service;}
    public function getDeviceName() { return $this->device_name;}
    public function getDeviceId() { return $this->device_id;}
    public function getModuleName() { return $this->module_name;}
    public function getModuleId() { return $this->module_id;}
}