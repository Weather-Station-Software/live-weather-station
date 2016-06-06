<?php

/**
 * This class add log capacity to the plugin.
 *
 * @since      2.8.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-query.php');

class LoggableException extends Exception {

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

class Logger {

    use Datas_Query;

    public static $severity = array('emergency' => 0, 'alert' => 1, 'critical' => 2,'error' => 3,'warning' => 4,
        'notice' => 5,'info' => 6,'debug' =>7, 'unknown' => 8);
    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.8.0
     * @param      string    $Live_Weather_Station       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     * @access   public
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }
    
    /**
     * Init table where to log events.
     *
     * @since    2.8.0
     */
    public static function init() {
        if (!get_option('live_weather_station_logger_installed', false)) {
            update_option('live_weather_station_logger_installed', true, true);
            self::create_live_weather_station_log_table();
            self::notice(get_called_class(),null,null,null,null,null,null,'Logger successfully installed and initialized.');
        }
    }

    /**
     * Delete old records.
     *
     * @since    2.8.0
     */
    public function rotate() {
        $count = 0;
        $max = get_option('live_weather_station_logger_rotate');
        if (!$max) {
            $max = 50000;
        }
        $current = $this->get_log_count();
        if ($current > $max) {
            $count = $current - $max;
        }
        if ($count > 0) {
            $done = $this->rotate_table(self::live_weather_station_log_table() , 'id', $count);
            if (!$done) {
                self::error('Logger',null,null,null,null,null,null,'Unable to delete ' . $count . ' old records.');
            }
            else {
                if ($count == 1) {
                    self::notice('Logger',null,null,null,null,null,null,'1 old record deleted.');
                }
                if ($count > 1) {
                    self::notice('Logger',null,null,null,null,null,null,$count . ' old records deleted.');
                }
            }
        }
        else {
            self::info('Logger',null,null,null,null,null,null,'No old records to delete.');
        }

    }

    /**
     * Log an error with specific level.
     *
     * @param   $level          string      Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    private static function _log($level = 'unknown', $system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        if (get_option('live_weather_station_logger_level', 6) >= self::$severity[$level]) {
            $values = array();
            $values['level'] = $level;
            $values['timestamp'] = date('Y-m-d H:i:s');
            $values['plugin'] = LWS_PLUGIN_NAME;
            $values['version'] = $version;
            if (!is_null($system)) {
                $values['system'] = substr($system, 0, 49);
            }
            if (!is_null($service)) {
                $values['service'] = substr($service, 0, 49);
            }
            if (!is_null($device_id)) {
                $values['device_id'] = substr($device_id, 0, 16);
            }
            if (!is_null($device_name)) {
                $values['device_name'] = substr($device_name, 0, 59);
            }
            if (!is_null($module_id)) {
                $values['module_id'] = substr($module_id, 0, 16);
            }
            if (!is_null($module_name)) {
                $values['module_name'] = substr($module_name, 0, 59);
            }
            if (!is_null($code)) {
                $values['code'] = $code;
            }
            if (!is_null($message)) {
                $values['message'] = substr($message, 0, 14999);
            }
            self::insert_table(self::live_weather_station_log_table(), $values);
        }
    }

    /**
     * Log an emergency error.
     *
     * @param   $e        LoggableException      Plugin version override.
     *
     * @since    2.8.0
     */
    public static function exception($e) {
        self::_log($e->getLevel(), $e->getSystem(), $e->getService(), $e->getDeviceId(), $e->getDeviceName(), $e->getModuleId(), $e->getModuleName(), $e->getCode(), $e->getMessage());
    }

    /**
     * Log an emergency error.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function emergency($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('emergency', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }
    
    /**
     * Log an alert error.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function alert($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('alert', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a critical error.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function critical($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('critical', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log an error.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function error($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('error', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a warning.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function warning($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('warning', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a notice.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function notice($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('notice', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log an information.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function info($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('info', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a debug information.
     *
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     *
     * @since    2.8.0
     */
    public static function debug($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LWS_VERSION) {
        self::_log('debug', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Get the icon string for a specified level.
     *
     * @param   $level          string      Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return  string      The icon string (awesome icon font).
     *
     * @since    2.8.0
     */
    public static function get_icon($level = 'unknown') {
        switch ($level) {
            case 'emergency':
                $result = 'fa-bomb';
                break;
            case 'alert':
                $result = 'fa-times-circle';
                break;
            case 'critical':
                $result = 'fa-times-circle';
                break;
            case 'error':
                $result = 'fa-exclamation-triangle';
                break;
            case 'warning':
                $result = 'fa-exclamation-circle';
                break;
            case 'notice':
                $result = 'fa-info-circle';
                break;
            case 'info':
                $result = 'fa-info-circle';
                break;
            case 'debug';
                $result = 'fa-info';
                break;
            default:
                $result = 'fa-question-circle';
        }
        return $result;
    }

    /**
     * Get the color for a specified level.
     *
     * @param   $level          string      Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return  string      The color hex encoded.
     *
     * @since    2.8.0
     */
    public static function get_color($level = 'unknown') {
        switch ($level) {
            case 'emergency':
                $result = '#500000';
                break;
            case 'alert':
                $result = '#FF0000';
                break;
            case 'critical':
                $result = '#FF6000';
                break;
            case 'error':
                $result = '#FF9000';
                break;
            case 'warning':
                $result = '#FFC000';
                break;
            case 'notice':
                $result = '#6792E9';
                break;
            case 'info':
                $result = '#86B4D5';
                break;
            case 'debug';
                $result = '#B8D0D0';
                break;
            default:
                $result = '';
        }
        return $result;
    }

    /**
     * Get the name for a specified level.
     *
     * @param   $level          string      Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return  string      The name in plain text.
     *
     * @since    2.8.0
     */
    public static function get_name($level = 'unknown') {
        switch ($level) {
            case 'emergency':
                $result = __('Emergency', 'live-weather-station');
                break;
            case 'alert':
                $result = __('Alert', 'live-weather-station');
                break;
            case 'critical':
                $result = __('Critical error', 'live-weather-station');
                break;
            case 'error':
                $result = __('Error', 'live-weather-station');
                break;
            case 'warning':
                $result = __('Warning', 'live-weather-station');
                break;
            case 'notice':
                $result = __('Notice', 'live-weather-station');
                break;
            case 'info':
                $result = __('Information', 'live-weather-station');
                break;
            case 'debug';
                $result = __('Debug information', 'live-weather-station');
                break;
            default:
                $result = '';
        }
        return $result;
    }

}

Logger::init();