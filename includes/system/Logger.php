<?php

namespace WeatherStation\System\Logs;

use WeatherStation\System\Schedules\Watchdog;

/**
 * This class add log capacity to the plugin.
 *
 * @package Includes\System
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Logger {

    use \WeatherStation\DB\Query;

    public static $severity = array('emergency' => 0, 'alert' => 1, 'critical' => 2,'error' => 3,'warning' => 4,
        'notice' => 5,'info' => 6,'debug' =>7, 'unknown' => 8);
    public static $ordered_severity = array('debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency');
    private $Live_Weather_Station;
    private $version;

    // Per-request flood protection: maximum number of rows a single request can write, and the last written event.
    // Filterable with 'live_weather_station_log_max_rows'; much higher for CLI/cron/background contexts.
    private static $max_rows_per_request = 500;
    private static $max_rows_long_running = 50000;
    private static $rows_written = 0;
    private static $last_event = '';
    private static $suppressed = 0;
    private static $suppressed_level = 'debug';
    private static $marker_written = false;

    /**
     * Get the maximum number of rows for this request.
     *
     * @return int The cap.
     * @since 3.9.0
     */
    private static function max_rows() {
        $long = (defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON) || (function_exists('wp_doing_cron') && wp_doing_cron()) || PHP_SAPI === 'cli';
        $max = $long ? self::$max_rows_long_running : self::$max_rows_per_request;
        if (function_exists('apply_filters')) {
            $max = (int)apply_filters('live_weather_station_log_max_rows', $max, $long);
        }
        return $max;
    }


    /**
     * Initialize the class and set its properties.
     *
     * @since    2.8.0
     * @param      string    $Live_Weather_Station       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }
    
    /**
     * Init table where to log events.
     *
     * @since 2.8.0
     */
    public static function init() {
        if (!get_option('live_weather_station_logger_installed', false)) {
            update_option('live_weather_station_logger_installed', true, true);
            self::create_live_weather_station_log_table();
            self::notice('Logger',null,null,null,null,null,null,'Logger successfully installed and initialized.');
        }
    }

    /**
     * Truncate log table.
     *
     * @since 3.2.0
     */
    public static function reset() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_log_table();
        $sql = 'TRUNCATE TABLE '.$table_name;
        $wpdb->query($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- TRUNCATE of the plugin log table (name built from $wpdb->prefix and a constant), no variable value, purge must act on the live table
        self::notice('Logger',null,null,null,null,null,null,'Events log has been purged.');
    }

    /**
     * Delete old logged events.
     *
     * @since 2.8.0
     */
    public function rotate() {
        $cron_id = Watchdog::init_chrono(Watchdog::$log_rotate_name);
        $count = 0;
        if ($hour_done = $this->purge_table(self::live_weather_station_log_table() , 'timestamp', 24 * get_option('live_weather_station_logger_retention', 14))) {
            $count += $hour_done;
        }
        $limit = $this->get_log_count() - get_option('live_weather_station_logger_rotate', 20000);
        if ($limit > 0) {
            if ($max_done = $this->rotate_table(self::live_weather_station_log_table() , 'id', $limit)) {
                $count += $max_done;
            }
        }
        if ($count > 0) {
            if ($count == 1) {
                self::notice('Logger',null,null,null,null,null,null,'1 old record deleted.');
            }
            if ($count > 1) {
                self::notice('Logger',null,null,null,null,null,null,$count . ' old records deleted.');
            }
        }
        else {
            self::info('Logger',null,null,null,null,null,null,'No old records to delete.');
        }
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Log an event with specific level.
     *
     * @param   $level          string      Optional. A level of event in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @param   $system         string      Optional. The system/class in which the error is done.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    private static function _log($level = 'unknown', $system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        if (!is_string($level) || !array_key_exists($level, self::$severity)) {
            $level = 'unknown';
        }
        if (get_option('live_weather_station_logger_level', 6) >= self::$severity[$level]) {
            // Do not let one request (e.g. an anonymous one triggering an error path in a loop) flood the log table.
            $event = $level . '|' . $system . '|' . $service . '|' . $device_id . '|' . $module_id . '|' . $code . '|' . (is_scalar($message) ? substr((string)$message, 0, 14999) : '');
            $important = (self::$severity[$level] <= self::$severity['error']);
            // Consecutive identical events are collapsed, but never the important ones.
            if (!$important && $event === self::$last_event) {
                return;
            }
            // Flood cap: important rows (error and above) are never suppressed.
            if (!$important && self::$rows_written >= self::max_rows()) {
                self::$suppressed++;
                if (self::$severity[$level] < self::$severity[self::$suppressed_level]) {
                    self::$suppressed_level = $level;
                }
                if (!self::$marker_written) {
                    self::$marker_written = true;
                    // Register the final summary row once; it is written with the highest suppressed severity.
                    register_shutdown_function(array(__CLASS__, 'flush_suppressed'));
                }
                return;
            }
            self::$last_event = $event;
            self::$rows_written++;
            $values = array();
            $values['level'] = $level;
            $values['timestamp'] = gmdate('Y-m-d H:i:s');
            $values['plugin'] = LIVE_WEATHER_STATION_PLUGIN_NAME;
            $values['version'] = substr($version, 0, 11);
            if (!is_null($system)) {
                $values['system'] = mb_substr(sanitize_text_field($system), 0, 49);
            }
            if (!is_null($service)) {
                $values['service'] = mb_substr(sanitize_text_field($service), 0, 49);
            }
            if (!is_null($device_id)) {
                $values['device_id'] = substr($device_id, 0, 17);
            }
            if (!is_null($device_name)) {
                $values['device_name'] = mb_substr(sanitize_text_field($device_name), 0, 59);
            }
            if (!is_null($module_id)) {
                $values['module_id'] = substr($module_id, 0, 17);
            }
            if (!is_null($module_name)) {
                $values['module_name'] = mb_substr(sanitize_text_field($module_name), 0, 59);
            }
            if (!is_null($code)) {
                $values['code'] = $code;
            }
            if (!is_null($message)) {
                $values['message'] = mb_substr(is_scalar($message) ? (string)$message : '', 0, 14999);
            }
            self::insert_table(self::live_weather_station_log_table(), $values);
        }
    }

    /**
     * Write the single marker row summarizing suppressed messages (called on shutdown).
     *
     * @since 3.9.0
     */
    public static function flush_suppressed() {
        if (self::$suppressed > 0) {
            $n = self::$suppressed;
            self::$suppressed = 0;
            $values = array();
            $values['level'] = self::$suppressed_level;
            $values['timestamp'] = gmdate('Y-m-d H:i:s');
            $values['plugin'] = LIVE_WEATHER_STATION_PLUGIN_NAME;
            $values['version'] = substr(LIVE_WEATHER_STATION_VERSION, 0, 11);
            $values['system'] = 'Logger';
            $values['message'] = $n . ' further messages suppressed in this request';
            self::insert_table(self::live_weather_station_log_table(), $values);
        }
    }

    /**
     * Log a (loggable) exception.
     *
     * @param   $e        LoggableException      Plugin version override.
     * @see LoggableException
     * @since    2.8.0
     */
    public static function exception($e) {
        self::_log($e->getLevel(), $e->getSystem(), $e->getService(), $e->getDeviceId(), $e->getDeviceName(), $e->getModuleId(), $e->getModuleName(), $e->getCode(), $e->getMessage());
    }

    /**
     * Log an emergency event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function emergency($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('emergency', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }
    
    /**
     * Log an alert event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function alert($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('alert', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a critical event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function critical($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('critical', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log an error event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function error($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('error', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a warning event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function warning($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('warning', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a notice event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function notice($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('notice', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log an information event.
     *
     * @param   $system         string      Optional. The system/class in which the event is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function info($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('info', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a debug information.
     *
     * @param   $system         string      Optional. The system/class in which the debug information is triggered.
     * @param   $service        string      Optional. The service accessed when error occurs.
     * @param   $device_id      string      Optional. The device id.
     * @param   $device_name    string      Optional. The device name.
     * @param   $module_id      string      Optional. The module id.
     * @param   $module_name    string      Optional. The module name.
     * @param   $code           integer     Optional. The error code.
     * @param   $message        string      Optional. The error message.
     * @param   $version        string      Optional. Plugin version override.
     * @since    2.8.0
     */
    public static function debug($system = null, $service = null, $device_id = null, $device_name = null, $module_id = null, $module_name = null, $code = null, $message = null, $version = LIVE_WEATHER_STATION_VERSION) {
        self::_log('debug', $system, $service, $device_id, $device_name, $module_id, $module_name, $code, $message, $version);
    }

    /**
     * Log a debug string while developing.
     *
     * @param $message string Optional. The error message.
     * @since 3.0.0
     */
    public static function dev($message = null) {
        self::_log('debug', null, null, null, null, null, null, null, $message);
    }

    /**
     * Get the icon string for a specified level of event.
     *
     * @param $level string Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return string The icon string (awesome icon font).
     * @since 2.8.0
     */
    public static function get_icon($level = 'unknown') {
        switch ($level) {
            case 'emergency':
                $result = 'fa-bolt';
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
            case 'debug':
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
     * @param $level string Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return string The color hex encoded.
     * @since 2.8.0
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
            case 'debug':
                $result = '#B8D0D0';
                break;
            default:
                $result = '#999';
        }
        return $result;
    }

    /**
     * Get the name for a specified level.
     *
     * @param $level string Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return string The name in plain text.
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
            case 'debug':
                $result = __('Debug information', 'live-weather-station');
                break;
            default:
                $result = __('Unknown', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the criticality for a specified level.
     *
     * @param $level string Optional. A level of error in ('emergency','alert','critical','error','warning','notice','info','debug').
     * @return integer The criticality.
     *
     * @since 3.2.0
     */
    public static function get_criticality($level = 'unknown') {
        switch ($level) {
            case 'emergency':
                $result = 13;
                break;
            case 'alert':
                $result = 8;
                break;
            case 'critical':
                $result = 5;
                break;
            case 'error':
                $result = 3;
                break;
            case 'warning':
                $result = 2;
                break;
            case 'notice':
                $result = 0.01;
                break;
            case 'info':
                $result = 0.01;
                break;
            case 'debug':
                $result = 0;
                break;
            default:
                $result = 100;
        }
        return $result*2;
    }

    /**
     * Get the name for a specified numericallevel.
     *
     * @param $level integer Optional. A level of error in [0..7].
     * @return string The name in plain text.
     *
     * @since 3.0.0
     */
    public static function get_name_by_id($level = 8) {
        switch ($level) {
            case 0:
                $result = __('Emergency', 'live-weather-station');
                break;
            case 1:
                $result = __('Alert', 'live-weather-station');
                break;
            case 2:
                $result = __('Critical error', 'live-weather-station');
                break;
            case 3:
                $result = __('Error', 'live-weather-station');
                break;
            case 4:
                $result = __('Warning', 'live-weather-station');
                break;
            case 5:
                $result = __('Notice', 'live-weather-station');
                break;
            case 6:
                $result = __('Information', 'live-weather-station');
                break;
            case 7:
                $result = __('Debug information', 'live-weather-station');
                break;
            default:
                $result = __('Unknown', 'live-weather-station');
        }
        return $result;
    }
}

Logger::init();