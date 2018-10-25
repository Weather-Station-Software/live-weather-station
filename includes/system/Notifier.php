<?php

namespace WeatherStation\System\Notifications;

use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Schedules\Watchdog;

/**
 * This class add notification capacity to the plugin.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class Notifier {

    use \WeatherStation\DB\Query;

    public static $ordered_severity = array('info', 'warning', 'error');
    private $Live_Weather_Station;
    private $version;
    private static $cacheid = 'notifications';


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
     * Record a notification with specific level.
     *
     * @param $level string Optional. A level of notification in ('info','warning','error').
     * @param $name string Optional. The name of the notification.
     * @param $url string Optional. The URL of the detail.
     * @param $description string Optional. The description of the notification.
     * @param $shift boolean Optional. Indicates if teher is a decay in the notification.
     * @since 3.6.0
     */
    private static function _notify($level = 'error', $name='', $url='', $description='', $shift=false) {
        $values = array();
        $values['timestamp'] = date('Y-m-d H:i:s', ($shift?time()+1:time()));
        $values['level'] = $level;
        $values['name'] = substr($name, 0, 99);
        $values['description'] = substr($description, 0, 1999);
        $values['url'] = substr($url, 0, 199);
        self::insert_table(self::live_weather_station_notifications_table(), $values);
        Cache::invalidate_backend(self::$cacheid);
    }

    /**
     * Record a notification with error level.
     *
     * @param $name string Optional. The name of the notification.
     * @param $url string Optional. The URL of the detail.
     * @param $description string Optional. The description of the notification.
     * @param $shift boolean Optional. Indicates if teher is a decay in the notification.
     * @since 3.6.0
     */
    public static function error($name='', $url='', $description='', $shift=false) {
        self::_notify('error', $name, $url, $description, $shift);
    }

    /**
     * Record a notification with warning level.
     *
     * @param $name string Optional. The name of the notification.
     * @param $url string Optional. The URL of the detail.
     * @param $description string Optional. The description of the notification.
     * @param $shift boolean Optional. Indicates if teher is a decay in the notification.
     * @since 3.6.0
     */
    public static function warning($name='', $url='', $description='', $shift=false) {
        self::_notify('warning', $name, $url, $description, $shift);
    }

    /**
     * Record a notification with info level.
     *
     * @param $name string Optional. The name of the notification.
     * @param $url string Optional. The URL of the detail.
     * @param $description string Optional. The description of the notification.
     * @param $shift boolean Optional. Indicates if teher is a decay in the notification.
     * @since 3.6.0
     */
    public static function info($name='', $url='', $description='', $shift=false) {
        self::_notify('info', $name, $url, $description, $shift);
    }

    /**
     * Delete a notification.
     *
     * @param $id int The id of the notification.
     * @since 3.6.0
     */
    public static function delete($id) {
        if (1 === self::delete_row_on_id(self::live_weather_station_notifications_table(), $id)) {
            Cache::invalidate_backend(self::$cacheid);
        }
    }

    /**
     * Get all notifications.
     *
     * @return array The notifications list.
     * @since 3.6.0
     */
    public static function get() {
        $result = Cache::get_backend(self::$cacheid);
        if (false === $result) {
            $result = self::get_newest_rows(self::live_weather_station_notifications_table());
            Cache::set_backend(self::$cacheid, $result);
        }
        return $result;
    }

    /**
     * Count notifications.
     *
     * @return array The notifications list.
     * @since 3.6.0
     */
    public static function count() {
        return count(self::get());
    }

}