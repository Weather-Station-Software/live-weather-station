<?php

namespace WeatherStation\DB;
use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Cache\Cache;
use WeatherStation\Data\History\Builder;
use WeatherStation\System\Background\ProcessManager;
use WeatherStation\System\Environment\Manager as Env;
use WeatherStation\System\Notifications\Notifier;

/**
 * Storage management.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

define('LWS_NETATMO_SID', 0);
define('LWS_LOC_SID', 1);
define('LWS_OWM_SID', 2);
define('LWS_WUG_SID', 3);
define('LWS_RAW_SID', 4);
define('LWS_REAL_SID', 5);
define('LWS_NETATMOHC_SID', 6);
define('LWS_TXT_SID', 7);
define('LWS_WFLW_SID', 8);
define('LWS_PIOU_SID', 9);
define('LWS_BSKY_SID', 10);
define('LWS_AMBT_SID', 11);
define('LWS_WLINK_SID', 12);

define('DEFAULT_UUID', '00000000-0000-0000-0000-000000000000');

trait Storage {

    /**
     *
     * @since 1.0.0
     */
    public static function live_weather_station_datas_table() {
        return 'live_weather_station_datas';
    }

    /**
     *
     * @since 3.7.0
     */
    public static function live_weather_station_maps_table() {
        return 'live_weather_station_maps';
    }

    /**
     *
     * @since 3.3.2
     */
    public static function live_weather_station_histo_daily_table() {
        return 'live_weather_station_datas_day';
    }

    /**
     *
     * @since 3.3.2
     */
    public static function live_weather_station_histo_yearly_table() {
        return 'live_weather_station_datas_year';
    }

    /**
     *
     * @since    3.0.0
     */
    public static function live_weather_station_stations_table() {
        return 'live_weather_station_stations';
    }

    /**
     *
     * @since    2.0.0
     */
    public static function live_weather_station_owm_stations_table() {
        return 'live_weather_station_owm_stations';
    }

    /**
     *
     * @since    2.3.0
     */
    public static function live_weather_station_infos_table() {
        return 'live_weather_station_infos';
    }

    /**
     *
     * @since    3.0.0
     */
    public static function live_weather_station_log_table() {
        return 'live_weather_station_log';
    }

    /**
     *
     * @since 3.1.0
     */
    public static function live_weather_station_performance_cache_table() {
        return 'live_weather_station_performance_cache';
    }

    /**
     *
     * @since 3.2.0
     */
    public static function live_weather_station_performance_cron_table() {
        return 'live_weather_station_performance_cron';
    }

    /**
     *
     * @since 3.2.0
     */
    public static function live_weather_station_quota_day_table() {
        return 'live_weather_station_quota_day';
    }

    /**
     *
     * @since 3.2.0
     */
    public static function live_weather_station_quota_year_table() {
        return 'live_weather_station_quota_year';
    }

    /**
     *
     * @since 3.5.0
     */
    public static function live_weather_station_module_detail_table() {
        return 'live_weather_station_module_detail';
    }

    /**
     *
     * @since 3.5.0
     */
    public static function live_weather_station_data_year_table() {
        return 'live_weather_station_data_year';
    }

    /**
     *
     * @since 3.6.0
     */
    public static function live_weather_station_media_table() {
        return 'live_weather_station_medias';
    }

    /**
     *
     * @since 3.6.0
     */
    public static function live_weather_station_background_process_table() {
        return 'live_weather_station_background_process';
    }

    /**
     *
     * @since 3.6.0
     */
    public static function live_weather_station_notifications_table() {
        return 'live_weather_station_notifications';
    }

    /**
     * Performs a safe add column.
     *
     * @since    2.5.0
     */
    private static function safe_add_column($table, $column, $alter) {
        global $wpdb;
        $sql = "SELECT * FROM " . $table ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $data = array();
            foreach ($query_a as $val) {
                $data[] = (array)$val;
            }
        } catch (\Exception $ex) {
            $data = array();
        }
        $do_action = false;
        $result = false;
        if (count($data) > 0) {
            if (is_array($data[0])) {
                if (!array_key_exists($column, $data[0])) {
                    $do_action = true;
                }
            }
            else {
                $do_action = true;
            }
        }
        else {
            $do_action = true;
        }
        if ($do_action) {
            try {
                $wpdb->query($alter);
                $result = true;
            }
            catch (\Exception $ex) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Verify if a table is empty.
     *
     * @since    2.7.0
     */
    private static function is_empty_table($table) {
        global $wpdb;
        $sql = "SELECT * FROM " . $table ;
        try {
            $query = (array)$wpdb->get_results($sql);
            $query_a = (array)$query;
            $data = array();
            foreach ($query_a as $val) {
                $data[] = (array)$val;
            }
        } catch (\Exception $ex) {
            $data = array();
        }
        return (count($data) == 0);
    }

    /**
     * Creates table for the plugin.
     *
     * @since    2.7.0
     */
    private static function create_live_weather_station_datas_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (device_id varchar(17) NOT NULL,";
		$sql .= " device_name varchar(60) DEFAULT '<unnamed>' NOT NULL,";
		$sql .= " module_id varchar(17) NOT NULL,";
		$sql .= " module_type varchar(12) DEFAULT '<unknown>' NOT NULL,";
		$sql .= " module_name varchar(60) DEFAULT '<unnamed>' NOT NULL,";
		$sql .= " measure_timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
		$sql .= " measure_type varchar(40) DEFAULT '' NOT NULL,";
		$sql .= " measure_value varchar(50) DEFAULT '' NOT NULL,";
		$sql .= " UNIQUE KEY dmm (device_id,module_id,measure_type)";
		$sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.5.0
     */
    private static function create_live_weather_station_module_detail_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_module_detail_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`device_id` varchar(17) NOT NULL,";
        $sql .= " `module_id` varchar(17) NOT NULL,";
        $sql .= " `module_name` varchar(60) DEFAULT '<unnamed>' NOT NULL,";
        $sql .= " `module_type` varchar(12) DEFAULT '<unknown>' NOT NULL,";
        $sql .= " `screen_name` varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " `hidden` boolean DEFAULT 0 NOT NULL,";
        $sql .= " UNIQUE KEY mdl (`device_id`, `module_id`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.3.2
     */
    private static function create_live_weather_station_histo_daily_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_histo_daily_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `device_id` varchar(17) NOT NULL,";
        $sql .= " `module_id` varchar(17) NOT NULL,";
        $sql .= " `module_type` varchar(12) DEFAULT '<unknown>' NOT NULL,";
        $sql .= " `measure_type` varchar(40) DEFAULT '' NOT NULL,";
        $sql .= " `measure_value` decimal(20,10) NOT NULL,";
        $sql .= " UNIQUE KEY dly (`timestamp`, `device_id`, `module_id`, `measure_type`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.6.0
     */
    private static function create_live_weather_station_media_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_media_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `device_id` varchar(17) NOT NULL,";
        $sql .= " `module_id` varchar(17) NOT NULL,";
        $sql .= " `module_type` varchar(12) DEFAULT '<unknown>' NOT NULL,";
        $sql .= " `item_type` varchar(12) DEFAULT 'none' NOT NULL,";
        $sql .= " `item_url` varchar(2000) DEFAULT '' NOT NULL,";
        $sql .= " UNIQUE KEY mdia (`timestamp`, `device_id`, `module_id`, `module_type`, `item_type`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.3.2
     */
    private static function create_live_weather_station_histo_yearly_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_histo_yearly_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`timestamp` date NOT NULL DEFAULT '0000-00-00',";
        $sql .= " `device_id` varchar(17) NOT NULL,";
        $sql .= " `module_id` varchar(17) NOT NULL,";
        $sql .= " `module_type` varchar(12) DEFAULT '<unknown>' NOT NULL,";
        $sql .= " `measure_type` varchar(40) DEFAULT '' NOT NULL,";
        $sql .= " `measure_set` varchar(5) DEFAULT '' NOT NULL,";
        $sql .= " `measure_value` decimal(20,10) NOT NULL,";
        $sql .= " UNIQUE KEY dly (`timestamp`, `device_id`, `module_id`, `measure_type`, `measure_set`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since    3.0.0
     */
    private static function create_live_weather_station_stations_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_stations_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (guid bigint(20) unsigned NOT NULL auto_increment,";
        $sql .= " station_id varchar(17) DEFAULT '' NOT NULL,";
        $sql .= " station_type int(11) NOT NULL DEFAULT '0',";
        $sql .= " station_model varchar(200) DEFAULT 'N/A' NOT NULL,";
        $sql .= " service_id varchar(250) DEFAULT '' NOT NULL,";
        $sql .= " connection_type int(11) NOT NULL DEFAULT '0',";
        $sql .= " station_name varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " loc_city varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " loc_country_code varchar(2) DEFAULT '' NOT NULL,";
        $sql .= " loc_timezone varchar(50) DEFAULT '' NOT NULL,";
        $sql .= " loc_latitude varchar(20) DEFAULT '' NOT NULL,";
        $sql .= " loc_longitude varchar(20) DEFAULT '' NOT NULL,";
        $sql .= " loc_altitude varchar(20) DEFAULT '' NOT NULL,";
        $sql .= " comp_bas int(11) NOT NULL DEFAULT '0',";
        $sql .= " comp_ext int(11) NOT NULL DEFAULT '0',";
        $sql .= " comp_int int(11) NOT NULL DEFAULT '0',";
        $sql .= " comp_xtd int(11) NOT NULL DEFAULT '0',";
        $sql .= " comp_vrt int(11) NOT NULL DEFAULT '0',";
        $sql .= " txt_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " raw_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " real_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " yow_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " owm_user varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " owm_password varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " owm_id varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " owm_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " pws_user varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " pws_password varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " pws_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " wow_user varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " wow_password varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " wow_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " wet_user varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " wet_password varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " wet_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " wug_user varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " wug_password varchar(60) DEFAULT '' NOT NULL,";
        $sql .= " wug_sync boolean DEFAULT 0 NOT NULL,";
        $sql .= " last_refresh datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
        $sql .= " last_seen datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
        $sql .= " oldest_data date NOT NULL DEFAULT '0000-00-00',";
        $sql .= " PRIMARY KEY (guid),";
        $sql .= " UNIQUE KEY (station_id)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin logging system.
     *
     * @since 3.0.0
     */
    private static function create_live_weather_station_log_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::live_weather_station_log_table();
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name;
        $sql .= " (`id` int(11) NOT NULL AUTO_INCREMENT,";
        $sql .= " `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `level` enum('emergency','alert','critical','error','warning','notice','info','debug','unknown') NOT NULL DEFAULT 'unknown',";
        $sql .= " `plugin` varchar(20) NOT NULL DEFAULT '" . LWS_PLUGIN_NAME . "',";
        $sql .= " `version` varchar(11) NOT NULL DEFAULT 'N/A',";
        $sql .= " `system` varchar(50) NOT NULL DEFAULT 'N/A',";
        $sql .= " `service` varchar(50) NOT NULL DEFAULT 'N/A',";
        $sql .= " `device_id` varchar(17) NOT NULL DEFAULT '00:00:00:00:00:00',";
        $sql .= " `device_name` varchar(60) NOT NULL DEFAULT 'N/A',";
        $sql .= " `module_id` varchar(17) NOT NULL DEFAULT '00:00:00:00:00:00',";
        $sql .= " `module_name` varchar(60) NOT NULL DEFAULT 'N/A',";
        $sql .= " `code` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `message` varchar(15000) NOT NULL DEFAULT '-',";
        $sql .= " PRIMARY KEY (`id`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the cache performance analytics.
     *
     * @since 3.1.0
     */
    private static function create_live_weather_station_performance_cache_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::live_weather_station_performance_cache_table();
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name;
        $sql .= " (`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `backend_hit_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `backend_hit_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `backend_miss_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `backend_miss_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `widget_hit_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `widget_hit_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `widget_miss_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `widget_miss_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `frontend_hit_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `frontend_hit_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `frontend_miss_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `frontend_miss_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `dgraph_hit_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `dgraph_hit_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `dgraph_miss_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `dgraph_miss_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `ygraph_hit_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `ygraph_hit_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `ygraph_miss_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `ygraph_miss_time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " PRIMARY KEY (`timestamp`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the cron performance analytics.
     *
     * @since 3.2.0
     */
    private static function create_live_weather_station_performance_cron_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::live_weather_station_performance_cron_table();
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name;
        $sql .= " (`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `cron` varchar(30) NOT NULL DEFAULT 'N/A',";
        $sql .= " `count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `time` int(11) NOT NULL DEFAULT '0',";
        $sql .= " UNIQUE KEY perf (timestamp, cron)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates daily table for the quota performance analytics and quota manager.
     *
     * @since 3.2.0
     */
    private static function create_live_weather_station_quota_day_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::live_weather_station_quota_day_table();
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name;
        $sql .= " (`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `service` varchar(30) NOT NULL DEFAULT 'N/A',";
        $sql .= " `post` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `get` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `put` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `patch` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `delete` int(11) NOT NULL DEFAULT '0',";
        $sql .= " UNIQUE KEY perf (timestamp, service)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates yearly table for the quota performance analytics and quota manager.
     *
     * @since 3.2.0
     */
    private static function create_live_weather_station_quota_year_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::live_weather_station_quota_year_table();
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name;
        $sql .= " (`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `service` varchar(30) NOT NULL DEFAULT 'N/A',";
        $sql .= " `post` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `post_rate` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `post_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `post_rate_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `get` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `get_rate` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `get_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `get_rate_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `put` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `put_rate` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `put_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `put_rate_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `patch` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `patch_rate` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `patch_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `patch_rate_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `delete` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `delete_rate` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `delete_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `delete_rate_q` int(11) NOT NULL DEFAULT '0',";
        $sql .= " UNIQUE KEY perf (timestamp, service)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates yearly table for the data statistics.
     *
     * @since 3.5.0
     */
    private static function create_live_weather_station_data_year_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::live_weather_station_data_year_table();
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name;
        $sql .= " (`timestamp` date NOT NULL DEFAULT '0000-00-00',";
        $sql .= " `table_name` varchar(60) NOT NULL DEFAULT 'N/A',";
        $sql .= " `table_size` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `row_count` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `row_size` int(11) NOT NULL DEFAULT '0',";
        $sql .= " UNIQUE KEY perf (timestamp, table_name)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.6.0
     */
    private static function create_live_weather_station_background_process_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_background_process_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`uuid` char(36),";
        $sql .= " `priority` int(11) NOT NULL DEFAULT '0',";
        $sql .= " `class` varchar(100) NOT NULL,";
        $sql .= " `name` varchar(100) NOT NULL,";
        $sql .= " `description` varchar(2000) NOT NULL,";
        $sql .= " `state` varchar(12) DEFAULT 'init' NOT NULL,";
        $sql .= " `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `params` longtext DEFAULT '',";
        $sql .= " `exec_time` int(11) DEFAULT '0' NOT NULL,";
        $sql .= " `pass` int(11) DEFAULT '0' NOT NULL,";
        $sql .= " `progress` int(11) DEFAULT '0' NOT NULL,";
        $sql .= " UNIQUE KEY (uuid)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.6.0
     */
    private static function create_live_weather_station_maps_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_maps_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`id` int(11) NOT NULL AUTO_INCREMENT,";
        $sql .= " `type` int(11) DEFAULT '0' NOT NULL,";
        $sql .= " `name` varchar(80) DEFAULT '<unnamed>' NOT NULL,";
        $sql .= " `params` longtext DEFAULT '',";
        $sql .= " PRIMARY KEY (`id`)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates table for the plugin.
     *
     * @since 3.6.0
     */
    private static function create_live_weather_station_notifications_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.self::live_weather_station_notifications_table();
        $sql = "CREATE TABLE IF NOT EXISTS ".$table_name;
        $sql .= " (`id` int(11) NOT NULL AUTO_INCREMENT,";
        $sql .= " `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',";
        $sql .= " `level` enum('info','warning','error') NOT NULL DEFAULT 'error',";
        $sql .= " `name` varchar(100) NOT NULL DEFAULT '',";
        $sql .= " `url` varchar(200) NOT NULL DEFAULT '',";
        $sql .= " `description` varchar(2000) NOT NULL DEFAULT '',";
        $sql .= " UNIQUE KEY (id)";
        $sql .= ") $charset_collate;";
        $wpdb->query($sql);
    }

    /**
     * Creates tables for the plugin.
     *
     * @since 1.0.0
     */
    protected static function create_tables() {
        self::create_live_weather_station_datas_table();
        self::create_live_weather_station_histo_daily_table();
        self::create_live_weather_station_histo_yearly_table();
        self::create_live_weather_station_stations_table();
        self::create_live_weather_station_module_detail_table();
        self::create_live_weather_station_performance_cache_table();
        self::create_live_weather_station_performance_cron_table();
        self::create_live_weather_station_quota_day_table();
        self::create_live_weather_station_quota_year_table();
        self::create_live_weather_station_data_year_table();
        self::create_live_weather_station_media_table();
        self::create_live_weather_station_background_process_table();
        self::create_live_weather_station_notifications_table();
        self::create_live_weather_station_maps_table();
    }

    /**
     * Updates tables from previous versions.
     *
     * @param string $oldversion Version id before migration.
     * @since 2.0.0
     */
    protected static function update_tables($oldversion) {
        global $wpdb;
        $id = $oldversion[0];

        if ($id == 3) {
            // DROP ALL OLD TABLES FROM 1.X & 2.X versions
            $table_name = $wpdb->prefix . self::live_weather_station_owm_stations_table();
            $sql = 'DROP TABLE IF EXISTS ' . $table_name;
            $wpdb->query($sql);
            $table_name = $wpdb->prefix . self::live_weather_station_infos_table();
            $sql = 'DROP TABLE IF EXISTS ' . $table_name;
            $wpdb->query($sql);
        }

        // UPDATES BEFORE 4.0
        if ($id < 4) {

            // VERSION 3.3.2
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            if (self::is_empty_table($table_name)) {
                $sql = 'DROP TABLE IF EXISTS ' . $table_name;
                $wpdb->query($sql);
                self::create_live_weather_station_stations_table();
            } else {
                self::safe_add_column($table_name, 'last_refresh', "ALTER TABLE " . $table_name . " ADD last_refresh datetime DEFAULT '0000-00-00 00:00:00' NOT NULL;");
                self::safe_add_column($table_name, 'last_seen', "ALTER TABLE " . $table_name . " ADD last_seen datetime DEFAULT '0000-00-00 00:00:00' NOT NULL;");
            }

            // VERSION 3.3.3
            update_option('live_weather_station_tempext_min_boundary', -70);
            update_option('live_weather_station_frost_point_min_boundary', -70);
            update_option('live_weather_station_wind_chill_min_boundary', -120);
            update_option('live_weather_station_cbi_min_boundary', -30);
            update_option('live_weather_station_cbi_max_boundary', 160);


            // VERSION 3.4.0
            $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
            if (self::is_empty_table($table_name)) {
                $sql = 'DROP TABLE IF EXISTS ' . $table_name;
                $wpdb->query($sql);
                self::create_live_weather_station_stations_table();
            } else {
                self::safe_add_column($table_name, 'oldest_data', "ALTER TABLE " . $table_name . " ADD oldest_data date NOT NULL DEFAULT '0000-00-00';");
            }

            $table_name = $wpdb->prefix . self::live_weather_station_log_table();
            if (self::is_empty_table($table_name)) {
                $sql = 'DROP TABLE IF EXISTS ' . $table_name;
                $wpdb->query($sql);
                self::create_live_weather_station_log_table();
            } else {
                $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN version varchar(11) NOT NULL DEFAULT 'N/A';";
                $wpdb->query($sql);
            }


            $table_name = $wpdb->prefix . self::live_weather_station_performance_cache_table();
            if (self::is_empty_table($table_name)) {
                $sql = 'DROP TABLE IF EXISTS ' . $table_name;
                $wpdb->query($sql);
                self::create_live_weather_station_performance_cache_table();
            } else {
                self::safe_add_column($table_name, 'dgraph_hit_count', "ALTER TABLE " . $table_name . " ADD dgraph_hit_count int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'dgraph_hit_time', "ALTER TABLE " . $table_name . " ADD dgraph_hit_time int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'dgraph_miss_count', "ALTER TABLE " . $table_name . " ADD dgraph_miss_count int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'dgraph_miss_time', "ALTER TABLE " . $table_name . " ADD dgraph_miss_time int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'ygraph_hit_count', "ALTER TABLE " . $table_name . " ADD ygraph_hit_count int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'ygraph_hit_time', "ALTER TABLE " . $table_name . " ADD ygraph_hit_time int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'ygraph_miss_count', "ALTER TABLE " . $table_name . " ADD ygraph_miss_count int(11) NOT NULL DEFAULT '0';");
                self::safe_add_column($table_name, 'ygraph_miss_time', "ALTER TABLE " . $table_name . " ADD ygraph_miss_time int(11) NOT NULL DEFAULT '0';");
            }


            // VERSION 3.5.0
            self::create_live_weather_station_module_detail_table();
            self::create_live_weather_station_data_year_table();


            // VERSION 3.6.0
            self::create_live_weather_station_media_table();
            self::create_live_weather_station_background_process_table();
            self::create_live_weather_station_notifications_table();
            if (version_compare($oldversion, '3.6.0', '<')) {
                ProcessManager::register('WindExpander');
            }


            // VERSION 3.6.3
            if (version_compare($oldversion, '3.6.3', '<')) {
                ProcessManager::register('IdentifierLowercaser');
                ProcessManager::register('PressureExpander');
            }

            // VERSION 3.7.0
            self::create_live_weather_station_maps_table();

            // VERSION 3.7.8
            update_option('live_weather_station_absolute_humidity_min_boundary', 0.00001);




            // ALL VERSION

            // OUTDATED PHP
            if (!Env::is_php_version_uptodate()) {
                Notifier::error(__('Your PHP version is deprecated', 'live-weather-station'),
                               'http://php.net/supported-versions.php',
                                __('This version of PHP is no longer supported by the PHP team and will not even receive security fixes in a few weeks. You should seriously consider to update it.', 'live-weather-station') .
                                '<br/><em>' . __('Note: even if you do not update, Weather Station will continue to work, but I can\'t offer technical support anymore...', 'live-weather-station') . '</em>');
            }

            // WUG STATION COLLECTED
            $wug = self::wug_stations();
            if (count($wug) > 0) {
                $st = implode('", "', $wug);
                $url = 'https://weather.station.software/blog/weather-underground-closes-its-doors-to-individual-users/';
                Notifier::error(__('Weather Underground error', 'live-weather-station'),
                    $url,
                    sprintf(__('As Weather Underground closed its API service, "%s" can not be collected anymore.', 'live-weather-station'), $st));

                $to = get_bloginfo('admin_email');
                $subject = __('About your Weather Underground stations', 'live-weather-station');
                $message = __('Hello!', 'live-weather-station') . "\r\n" . "\r\n";
                $message .= sprintf(__('%s informs you that Weather Underground closed its API service.', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ';
                $message .= __('As a result, the following stations will no longer be collected:', 'live-weather-station') . "\r\n" ;
                foreach ($wug as $station) {
                    $message .= '     - ' . $station . "\r\n";
                }
                $message .= "\r\n" . sprintf(__('To know the reasons for this, and discover alternative methods to collect weather data with %s, please read the following article:', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ' . $url . ".\r\n" . "\r\n";
                if (function_exists('wp_mail')) {
                    wp_mail($to, $subject, $message);
                }
                else {
                    define('LWS_WUG_ALERT_TO', $to);
                    define('LWS_WUG_ALERT_SUBJECT', $subject);
                    define('LWS_WUG_ALERT_MESSAGE', $message);
                    add_action('wp_loaded', 'lws_send_alert_message');
                }
            }

        }
    }


    /**
     * Truncate tables of the plugin.
     *
     * @since 1.0.0
     * @access protected
     */
    protected static function truncate_data_table() {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = 'TRUNCATE TABLE '.$table_name;
        $wpdb->query($sql);
    }

    /**
     * Drop tables of the plugin.
     *
     * @param boolean $drop_log Drop log table too.
     * @since 1.0.0
     * @access protected
     */
    protected static function drop_tables($drop_log = true) {
        global $wpdb;
        $table_name = $wpdb->prefix.self::live_weather_station_datas_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_stations_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        if ($drop_log) {
            $table_name = $wpdb->prefix.self::live_weather_station_log_table();
            $sql = 'DROP TABLE IF EXISTS '.$table_name;
            $wpdb->query($sql);
        }
        $table_name = $wpdb->prefix.self::live_weather_station_infos_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_owm_stations_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_performance_cache_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_performance_cron_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_quota_day_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_quota_year_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_histo_daily_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_histo_yearly_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_module_detail_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_data_year_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_media_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_background_process_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::live_weather_station_notifications_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
        $table_name = $wpdb->prefix.self::create_live_weather_station_maps_table();
        $sql = 'DROP TABLE IF EXISTS '.$table_name;
        $wpdb->query($sql);
    }

    /**
     * Lower case specified fields.
     *
     * @param array $fields The fields to lowercase.
     * @param string $table The table name.
     * @since 3.6.3
     */
    protected static function fields_lower_case($fields, $table) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        foreach ($fields as $field) {
            $sql = "UPDATE " . $table_name . " SET `" . $field . "`=LOWER(`" . $field . "`) WHERE 1";
            $wpdb->query($sql);
        }
    }

    /**
     * Count the number of records in a table.
     *
     * @param string $table_name The table to count.
     * @return integer Count of records.
     * @since 3.5.0
     */
    private function count_table($table_name) {
        $result = -1;
        global $wpdb;
        $sql = "SELECT COUNT(*) as CNT FROM `" . $wpdb->prefix . $table_name . "`;";
        $cnt = $wpdb->get_results($sql, ARRAY_A);
        if (count($cnt) > 0) {
            if (array_key_exists('CNT', $cnt[0])) {
                $result = $cnt[0]['CNT'];
            }
        }
        return $result;
    }

    /**
     * Count the number of records in a table.
     *
     * @return array The stations names.
     * @since 3.7.0
     */
    private static function wug_stations() {
        $result = array();
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        $sql = "SELECT station_name FROM `" . $table_name . "` WHERE `station_type` ='" . LWS_WUG_SID ."' ;";
        foreach ($wpdb->get_results($sql, ARRAY_A) as $station) {
            $result[] = $station['station_name'];
        }
        return $result;
    }

    /**
     * Get the stats of a table.
     *
     * @param string $table_name The table to get the stats.
     * @return array The stats of the table.
     * @since 3.5.0
     */
    private function stats_table($table_name) {
        $result = array();
        $row_count = 0;
        $row_size = 0;
        $table_size = 0;
        global $wpdb;
        $sql = "SELECT * FROM information_schema.tables WHERE table_schema='" . $wpdb->dbname . "' and table_name='" . $wpdb->prefix . $table_name . "';";
        $line = $wpdb->get_results($sql, ARRAY_A);
        if (count($line) > 0) {
            if (array_key_exists('TABLE_ROWS', $line[0])) {
                $row_count = $line[0]['TABLE_ROWS'];
            }
            if (array_key_exists('AVG_ROW_LENGTH', $line[0])) {
                $row_size = $line[0]['AVG_ROW_LENGTH'];
            }
            if (array_key_exists('DATA_LENGTH', $line[0])) {
                $table_size += $line[0]['DATA_LENGTH'];
            }
            if (array_key_exists('INDEX_LENGTH', $line[0])) {
                $table_size += $line[0]['INDEX_LENGTH'];
            }
        }
        $result['table_name'] = $table_name;
        $result['table_size'] = $table_size;
        $result['row_count'] = $row_count;
        $result['row_size'] = $row_size;
        return $result;
    }


    /**
     * Update table with current value line.
     *
     * @param   string  $table_name The table to update.
     * @param   array   $value  The values to update or insert in the table
     * @return integer The insert id if anny
     * @since    2.0.0
     */
    private static function insert_table($table_name, $value) {
        global $wpdb;
        if ($wpdb->insert($wpdb->prefix.$table_name,$value)) {
            return $wpdb->insert_id;
        }
        return 0;
    }

    /**
     * Update table with current value line.
     *
     * @param string $table_name The table name.
     * @param array $value The values to update or insert in the table
     * @since 3.5.0
     */
    protected static function insert_update_table($table_name, $value) {
        $field_insert = array();
        $value_insert = array();
        $value_update = array();
        foreach ($value as $k => $v) {
            $field_insert[] = '`' . $k . '`';
            $value_insert[] = "'" . $v . "'";
            $value_update[] = '`' . $k . '`=' . "'" . $v . "'";
        }
        if (count($field_insert) > 0) {
            global $wpdb;
            $sql = "INSERT INTO `" . $wpdb->prefix . $table_name . "` ";
            $sql .= "(" . implode(',', $field_insert) . ") ";
            $sql .= "VALUES (" . implode(',', $value_insert) . ") ";
            $sql .= "ON DUPLICATE KEY UPDATE " . implode(',', $value_update) . ";";
            $wpdb->query($sql);
        }
    }

    /**
     * Insert in a table with current value line.
     *
     * @param string $table_name The table name.
     * @param array $value The values to update or insert in the table
     * @since 3.7.0
     */
    protected static function insert_ignore_table($table_name, $value) {
        $field_insert = array();
        $value_insert = array();
        foreach ($value as $k => $v) {
            $field_insert[] = '`' . $k . '`';
            $value_insert[] = "'" . $v . "'";
        }
        if (count($field_insert) > 0) {
            global $wpdb;
            $sql = "INSERT IGNORE INTO `" . $wpdb->prefix . $table_name . "` ";
            $sql .= "(" . implode(',', $field_insert) . ") ";
            $sql .= "VALUES (" . implode(',', $value_insert) . ");";
            $wpdb->query($sql);
        }
    }

    /**
     * Delete one row in a table.
     *
     * @param string $table_name The table to update.
     * @param int $id The id to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.6.0
     */
    protected static function delete_row_on_id($table_name, $id) {
        if (isset($id)) {
            if (is_numeric($id)) {
                $id = (int)round($id);
                global $wpdb;
                $table_name = $wpdb->prefix . $table_name;
                $sql = "DELETE FROM " . $table_name . " WHERE `id`='" . $id . "';";
                return $wpdb->query($sql);
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }

    /**
     * Get the newest rows in a table.
     *
     * @param string $table_name The table to update.
     * @param int $limit The id to delete.
     * @return array The $limit newer rows.
     * @since 3.6.0
     */
    protected static function get_newest_rows($table_name, $limit=20) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $sql = "SELECT * FROM " . $table_name . " ORDER BY `timestamp` DESC LIMIT ".$limit;
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the oldest rows in a table.
     *
     * @param string $table_name The table to update.
     * @param int $limit The id to delete.
     * @return array The $limit newer rows.
     * @since 3.6.0
     */
    protected static function get_oldest_rows($table_name, $limit=20) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $sql = "SELECT * FROM " . $table_name . " ORDER BY `timestamp` ASC LIMIT ".$limit;
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Update table with current value line.
     *
     * @param string $table_name The table to update.
     * @param string $field The field name.
     * @param string $old_value The value to modify.
     * @param string $new_value The new value.
     * @since 3.0.0
     */
    private function modify_table($table_name, $field, $old_value, $new_value) {
        global $wpdb;
        $sql = "UPDATE " . $wpdb->prefix.$table_name . " SET " . $field . "='" . $new_value . "' WHERE " . $field . "='" . $old_value."'";
        $wpdb->query($sql);
    }

    /**
     * Update table with current value line.
     *
     * @param string $table_name The table to update.
     * @param array $value The values to update or insert in the table
     * @since 2.0.0
     */
    private function update_table($table_name, $value) {
        global $wpdb;
        $wpdb->replace($wpdb->prefix.$table_name,$value);
    }

    /**
     * Update daily table with current value line.
     *
     * @param array $value The values to update or insert in the table
     * @since 3.3.2
     */
    private function update_historic($value) {
        if ((bool)get_option('live_weather_station_collect_history')) {
            if (in_array($value['measure_type'], Builder::$data_to_historize)) {
                $ts = mysql2date('G', $value['measure_timestamp']);
                $sec = $ts % 86400;
                if ($sec > (86400 - 150)) {         //if near midnight (less than 2'30")
                    $ts = $ts + 1 + 86400 - $sec;   //jump to tomorrow midnight + 1 second
                }
                $now = date('Y-m-d H:i', $ts);
                if (in_array(substr($now, -1), array('8', '9', '0', '1', '2'))) {
                    $min = '0:00';
                } else {
                    $min = '5:00';
                }
                $now = substr($now, 0, strlen($now) - 1) . $min;
                $field_insert = array('timestamp', 'device_id', 'module_id', 'module_type', 'measure_type', 'measure_value');
                $value_insert = array("'" . $now . "'", "'" . $value['device_id'] . "'", "'" . $value['module_id'] . "'", "'" . $value['module_type'] . "'", "'" . $value['measure_type'] . "'", "'" . $value['measure_value'] . "'");
                global $wpdb;
                $sql = "INSERT IGNORE INTO " . $wpdb->prefix . self::live_weather_station_histo_daily_table() . " ";
                $sql .= "(" . implode(',', $field_insert) . ") ";
                $sql .= "VALUES (" . implode(',', $value_insert) . ");";
                $wpdb->query($sql);
            }
        }
    }

    /**
     * Get the measurement boundary.
     *
     * @param string $type The type of the value.
     * @param string $module_type The type of the module.
     * @param string $opt The specific type of option.
     * @return string The measurement boundary.
     * @since 3.7.5
     */
    protected function get_measurement_boundary ($type, $module_type, $opt) {
        switch (strtolower($type)) {
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
            case 'humidex':
            case 'heat_index':
            case 'steadman':
            case 'summer_simmer':
            case 'wet_bulb':
            case 'delta_t':
            case 'equivalent_temperature':
            case 'potential_temperature':
            case 'equivalent_potential_temperature':
                if (strtolower($module_type)=='namodule4' || strtolower($module_type)=='namain') {
                    $t = 'tempint';
                }
                else {
                    $t = 'tempext';
                }
                break;
            case 'humidity':
            case 'humidity_min':
            case 'humidity_max':
                if (strtolower($module_type)=='namodule4' || strtolower($module_type)=='namain') {
                    $t = 'humint';
                }
                else {
                    $t = 'humext';
                }
                break;
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_sl_min':
            case 'pressure_sl_max':
            case 'pressure_ref':
                $t = 'pressure';
                break;
            case 'rain_yesterday_aggregated':
                $t = 'rain_day_aggregated';
                break;
            case 'rain_season_aggregated':
                $t = 'rain_year_aggregated';
                break;
            case 'cloudiness':
                $t = 'cloud_cover';
                break;
            case 'gustangle':
            case 'windangle_hour_max':
            case 'windangle_day_max':
                $t = 'windangle';
                break;
            case 'gustdirection':
            case 'winddirection_hour_max':
            case 'winddirection_day_max':
                $t = 'winddirection';
                break;
            case 'guststrength':
            case 'windstrength_hour_max':
            case 'windstrength_day_max':
                $t = 'windstrength';
                break;
            case 'wood_emc':
            case 'emc':
                $t = 'emc';
                break;
            case 'vapor_pressure':
            case 'partial_vapor_pressure':
            case 'saturation_vapor_pressure':
                $t = 'vapor_pressure';
                break;
            case 'absolute_humidity':
            case 'partial_absolute_humidity':
            case 'saturation_absolute_humidity':
                $t = 'absolute_humidity';
                break;
            default:
                $t = $type;
        }
        return get_option('live_weather_station_' . $t . '_' . $opt . '_boundary', 'NaN');
    }

    /**
     * Get specific lines.
     *
     * @param array $attributes An array representing the query.
     * @param string $after The DateTime breakdown.
     * @return array An array containing all the datas.
     * @since 3.7.5
     */
    protected function get_datas_rows_after($attributes, $after) {
        if (array_key_exists('measure_timestamp', $attributes)) {
            unset($attributes['measure_timestamp']);
        }
        global $wpdb;
        $where = array();
        foreach ($attributes as $k => $v) {
            if (isset($v)) {
                $where[] = '`' . $k . '`=' .  "'" . $v . "'";
            }
        }
        $where[] = '`measure_timestamp`>' .  "'" . $after . "'";
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "SELECT * FROM " . $table_name . " WHERE (" . implode(" AND ", $where) . ");";
        try {
            $result = (array)$wpdb->get_results($sql, ARRAY_A);
        }
        catch(\Exception $ex) {
            return array();
        }
        return $result;
    }

    /**
     * Get the station timezone by choosing the best (quickest) method.
     *
     * @param array $station The station details.
     * @param array $place The place details.
     * @param string $guid The station guid.
     * @param string $station_id The station id.
     * @return string The timezone ready to use.
     * @since 3.7.8
     */
    protected function get_timezone($station, $place, $guid, $station_id) {
        $result = '';
        if (isset($station) && is_array($station)) {
            if (array_key_exists('loc_timezone', $station)) {
                $result = $station['loc_timezone'];
            }
        }
        if (!$result && isset($place) && is_array($place)) {
            if (array_key_exists('timezone', $place)) {
                $result = str_replace('\\', '', $place['timezone']);
            }
        }
        if (!$result && isset($guid)) {
            $station = $this->get_station($guid);
            if (isset($station) && is_array($station)) {
                if (array_key_exists('loc_timezone', $station)) {
                    $result = $station['loc_timezone'];
                }
            }
        }
        if (!$result && isset($station_id)) {
            $station = $this->get_station_informations_by_station_id($station_id);
            if (isset($station) && is_array($station)) {
                if (array_key_exists('loc_timezone', $station)) {
                    $result = $station['loc_timezone'];
                }
            }
        }
        if (!$result) {
            $result = 'UTC';
        }
        return $result;
    }

    /**
     * Get trend.
     *
     * @param array $attributes An array representing the query.
     * @param string $tz The timezone of the station.
     * @return string The trend.
     * @since 3.7.8
     */
    protected function get_trend($attributes, $tz) {
        $result = '';
        if (array_key_exists('measure_value', $attributes)) {
            $value = $attributes['measure_value'];
            unset($attributes['measure_value']);
        }
        else {
            $value = 0;
        }
        if (array_key_exists('measure_timestamp', $attributes)) {
            unset($attributes['measure_timestamp']);
        }
        if (array_key_exists('module_name', $attributes)) {
            unset($attributes['module_name']);
        }
        if (array_key_exists('device_name', $attributes)) {
            unset($attributes['device_name']);
        }
        switch ($attributes['measure_type']) {
            case 'cloudiness':
            case 'humidity':
            case 'absolute_humidity':
                $shift = 3600;
                $sensibility = 0.01;
                break;
            case 'moisture_content':
            case 'moisture_tension':
            case 'soil_temperature':
                $shift = 7200;
                $sensibility = 0.001;
                break;
            case 'pressure':
            case 'pressure_sl':
                $shift = 7200;
                $sensibility = 0.005;
                break;
            case 'windstrength':
            case 'guststrength':
                $shift = 1800;
                $sensibility = 0.05;
                break;
            default:
                $shift = 1800;
                $sensibility = 0.005;
        }
        try {
            $datetime = new \DateTime(date('Y-m-d H:i:s', time()-$shift), new \DateTimeZone($tz));
        }
        catch(\Exception $ex) {
            return '';
        }
        global $wpdb;
        $where = array();
        foreach ($attributes as $k => $v) {
            if (isset($v)) {
                $where[] = '`' . $k . '`=' .  "'" . $v . "'";
            }
        }
        $where[] = '`timestamp`>' .  "'" . date('Y-m-d H:i:s', $datetime->getTimestamp()) . "'";
        $table_name = $wpdb->prefix . self::live_weather_station_histo_daily_table();
        $sql = "SELECT * FROM " . $table_name . " WHERE (" . implode(" AND ", $where) . ") ORDER BY `timestamp` ASC ;";
        try {
            $data = (array)$wpdb->get_results($sql, ARRAY_A);
        }
        catch(\Exception $ex) {
            return '';
        }
        $set = array();
        $cpt = 0;
        foreach ($data as $i => $d) {
            if (array_key_exists('measure_value', $d)) {
                $set[] = $d['measure_value'];
                $cpt += $i;
            }
        }
        $n = count($set);
        if ($n > 2) {
            $x_sum = $cpt;
            $y_sum = array_sum($set);
            $xx_sum = 0;
            $xy_sum = 0;
            foreach ($set as $i => $s) {
                $xy_sum += ($i * $s);
                $xx_sum += ($i * $i);
            }
            try {
                $slope = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
            }
            catch(\Exception $ex) {
                $slope = 0;
            }
            if (abs($slope) > abs($value * $sensibility)) {
                if ($slope > 0) {
                    $result = 'up';
                }
                if ($slope < 0) {
                    $result = 'down';
                }
            }
            else {
                $result = 'stable';
            }
            //error_log ($attributes['measure_type'] . ' (' . $result . ') = ' . $value . ' / ' . $slope);
        }
        return $result;
    }

    /**
     * Update data table with current value line.
     *
     * @param array $value The values to update or insert in the table.
     * @param string $tz The timezone of the station.
     * @since 1.0.0
     */
    protected function update_data_table($value, $tz) {
        $verified = isset($value['measure_value']);
        if ($verified) {
            $verified = !is_null($value['measure_value']);
        }
        if ($verified) {
            if (!in_array(strtolower($value['module_type']), array('nacomputed', 'naephemer', 'napollution', 'naforecast', 'namodulev', 'namodulep'))) {
                $min = $this->get_measurement_boundary($value['measure_type'], $value['module_type'], 'min');
                $max = $this->get_measurement_boundary($value['measure_type'], $value['module_type'], 'max');
                if ($min !== 'NaN' && $max !== 'NaN') {
                    if (is_numeric($min) && is_numeric($max) && is_numeric($value['measure_value'])) {
                        if ($value['measure_value'] < $min) {
                            $verified = false;
                        }
                        if ($value['measure_value'] > $max) {
                            $verified = false;
                        }
                    }
                }
            }
        }
        if ($verified) {
            try {
                $type = $value['measure_type'];
                $v = $value['measure_value'];
                $this->update_table(self::live_weather_station_datas_table(), $value);
                $this->update_historic($value);
                if (in_array($value['measure_type'], $this->min_max_trend)) {
                    $comp = array('min', 'max', 'trend');
                    if ($value['measure_type'] === 'windstrength' || $value['measure_type'] === 'guststrength') {
                        $comp = array('day_min', 'day_max', 'day_trend');
                    }
                    foreach ($comp as $i => $c) {
                        $value['measure_type'] = $type;
                        $value['measure_value'] = $v;
                        $trend = '';
                        if ($i === 2) {
                            if ((bool)get_option('live_weather_station_collect_history')) {
                                $trend = $this->get_trend($value, $tz);
                            }
                            if (!$trend) {
                                $datetime = new \DateTime('today midnight', new \DateTimeZone($tz));
                                $oldval = $this->get_datas_rows_after($value, date('Y-m-d H:i:s', $datetime->getTimestamp()));
                            }
                        }
                        unset ($value['measure_value']);
                        $value['measure_type'] = $type . '_' . $c;
                        $datetime = new \DateTime('today midnight', new \DateTimeZone($tz));
                        $val = $this->get_datas_rows_after($value, date('Y-m-d H:i:s', $datetime->getTimestamp()));
                        if (count($val) > 0) {
                            switch ($i) {
                                case 0:
                                    if (array_key_exists('measure_value', $val[0])) {
                                        if ($v < $val[0]['measure_value']) {
                                            $value['measure_value'] = $v;
                                            $this->update_data_table($value, $tz);
                                        }
                                    }
                                    else {
                                        $value['measure_value'] = $v;
                                        $this->update_data_table($value, $tz);
                                    }
                                    break;
                                case 1:
                                    if (array_key_exists('measure_value', $val[0])) {
                                        if ($v > $val[0]['measure_value']) {
                                            $value['measure_value'] = $v;
                                            $this->update_data_table($value, $tz);
                                        }
                                    }
                                    else {
                                        $value['measure_value'] = $v;
                                        $this->update_data_table($value, $tz);
                                    }
                                    break;
                                case 2:
                                    if (!$trend && isset($val) && is_array($val) && array_key_exists('measure_value', $val[0]) && isset($oldval) && is_array($oldval) && array_key_exists('measure_value', $oldval[0])) {
                                        switch ($val[0]['measure_value']) {
                                            case 'up':
                                                if ($v > $oldval[0]['measure_value'] * 1.02) {
                                                    $trend = 'up';
                                                }
                                                else {
                                                    $trend = 'stable';
                                                }
                                                break;
                                            case 'down':
                                                if ($v < $oldval[0]['measure_value'] * 0.98) {
                                                    $trend = 'down';
                                                }
                                                else {
                                                    $trend = 'stable';
                                                }
                                                break;
                                            default:
                                                $trend = 'stable';
                                                if ($v < $oldval[0]['measure_value'] * 0.98) {
                                                    $trend = 'down';
                                                }
                                                if ($v > $oldval[0]['measure_value'] * 1.02) {
                                                    $trend = 'down';
                                                }
                                        }
                                        $value['measure_value'] = $trend;
                                        $this->update_data_table($value, $tz);
                                    }
                                    else {
                                        $value['measure_value'] = 'stable';
                                        if ($trend) {
                                            $value['measure_value'] = $trend;
                                        }
                                        $this->update_data_table($value, $tz);
                                    }
                            }
                        }
                        else {
                            if ($i < 2) {
                                $value['measure_value'] = $v;
                                $this->update_data_table($value, $tz);
                            }
                            else {
                                $value['measure_value'] = 'stable';
                                $this->update_data_table($value, $tz);
                            }
                        }
                    }
                }
            }
            catch (\Exception $ex) {
                Logger::warning('Data Manager', null, null, null, null, null, 500, 'Inconsistent data to insert in data table: ' . print_r($value, true));
            }
        }
        else {
            try {
                Logger::warning('Data Manager', null, $value['device_id'], $value['device_name'], $value['module_id'], $value['module_name'], 500, 'Inconsistent data to insert in data table: ' . print_r($value, true));
            }
            catch (\Exception $ex) {
                Logger::warning('Data Manager', null, null, null, null, null, 500, 'Inconsistent data to insert in data table: ' . print_r($value, true));
            }

        }
    }

    /**
     * Update stations table with current value line.
     *
     * @param array $value The values to update or insert in the table
     * @param boolean $force Optional. Specifies if an INSERT must be done if the record doesn't exist.
     * @return integer 0 if there is a problem when updating, the value of guid otherwise.
     * @since 3.0.0
     */
    protected function update_stations_table($value, $force=false) {
        global $wpdb;
        $result = 0;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        if (!array_key_exists('guid', $value) && array_key_exists('station_id', $value)) {
            $sql = "SELECT * FROM " . $table_name . " WHERE station_id='" . $value['station_id']."'";
            try {
                $query = (array)$wpdb->get_results($sql);
                $query_a = (array)$query;
                if (count($query_a) <= 0) {
                    throw new \Exception('-');
                }
                foreach ($query_a as $val) {
                    $v = (array)$val;
                    if (array_key_exists('guid', $v)) {
                        $value['guid'] = $v['guid'];
                    }
                }
            } catch (\Exception $ex) {
                if ($force) {
                    if ($this->insert_ignore_stations_table($value['station_id'])) {
                        return $this->update_stations_table($value);
                    }
                    else {
                        Logger::error('Data Manager', null, null, null, null, null, 500, 'Inconsistent data in stations table: unable to insert station ' . $value['station_id'] . '.');
                    }
                } else {
                    Logger::error('Data Manager', null, null, null, null, null, 500, 'Inconsistent data in stations table: unable to find station ' . $value['station_id'] . '.');
                }
            }
        }
        if (array_key_exists('guid', $value)) {
            $this->update_table(self::live_weather_station_stations_table(), $value);
            $result = $value['guid'];
            $cache_id = 'get_station'.$value['guid'];
            Cache::invalidate_query($cache_id);
            Cache::flush_query();
        }
        else {
            Logger::error('Data Manager', null, null, null, null, null, 500, 'Inconsistent data in stations table: unable to get guid for this record: ' . print_r($value, true));
        }
        return $result;
    }

    /**
     * Insert a new station in stations table.
     *
     * @param string $station_id The device id of the station to insert in the table
     * @param integer $station_type Optional. The type id of the station.
     * @return int|false The number of rows inserted, or false on error.
     * @since 2.3.0
     */
    protected function insert_ignore_stations_table($station_id, $station_type=null) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_stations_table();
        if (isset($station_type)) {
            $sql = "INSERT IGNORE INTO ".$table_name." (station_id,station_type) VALUES('".$station_id."',".$station_type.");";
        }
        else {
            $sql = "INSERT IGNORE INTO ".$table_name." (station_id) VALUES('".$station_id."');";
        }
        return $wpdb->query($sql);
    }

    /**
     * Delete some lines in a table.
     *
     * @param string $table_name The table to update.
     * @param string $field_name The name of the field containing ids.
     * @param array $value  The list of id to delete.
     * @param string $sep Optional. Separator.
     * @return int|false The number of rows deleted, or false on error.
     * @since 2.0.0
     */
    private function delete_table($table_name, $field_name, $value, $sep='') {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $sql = "DELETE FROM ".$table_name." WHERE ".$field_name." IN (" . $sep . implode($sep.','.$sep, $value) . $sep . ")";
        return $wpdb->query($sql);
    }

    /**
     * Delete some lines in a table.
     *
     * @param   string      $table_name The table to update.
     * @param   string      $field_name   The name of the field containing ids.
     * @param   integer     $limit  The number of lines to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since    2.8.0
     */
    private function rotate_table($table_name, $field_name, $limit) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $sql = "DELETE FROM ".$table_name." ORDER BY ".$field_name." ASC LIMIT ".$limit;
        return $wpdb->query($sql);
    }

    /**
     * Delete some lines in a table.
     *
     * @param string $table_name The table to update.
     * @param string $field_name The name of the field containing timestamp.
     * @param integer $interval The number of hours of age to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    private function purge_table($table_name, $field_name, $interval) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        $sql = "DELETE FROM ".$table_name." WHERE (" . $field_name . " < NOW() - INTERVAL " . $interval . " HOUR);";
        return $wpdb->query($sql);
    }

    /**
     * Delete some owm stations.
     *
     * @param array $value The guid to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function delete_stations_table($value) {
        return $this->delete_table(self::live_weather_station_stations_table(), 'guid', $value);
    }

    /**
     * Delete some maps.
     *
     * @param array $value The id to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.7.0
     */
    protected function delete_maps_table($value) {
        return $this->delete_table(self::live_weather_station_maps_table(), 'id', $value);
    }

    /**
     * Delete all data for a station.
     *
     * @param array $value The device_id to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function delete_operational_stations_table($value) {
        $result = $this->delete_table(self::live_weather_station_datas_table(), 'device_id', $value, '\'');
        $this->delete_table(self::live_weather_station_module_detail_table(), 'device_id', $value, '\'');
        $this->delete_table(self::live_weather_station_media_table(), 'device_id', $value, '\'');
        $this->delete_table(self::live_weather_station_histo_daily_table(), 'device_id', $value, '\'');
        $this->delete_table(self::live_weather_station_histo_yearly_table(), 'device_id', $value, '\'');
        Cache::invalidate_backend(Cache::$db_stat_operational);
        return $result;
    }

    /**
     * Delete some owm stations.
     *
     * @param   array   $values  The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since    2.7.0
     */
    protected function clean_owm_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'xx:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some owm true stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_owm_true_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'xy:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some wug stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_wug_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'xz:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some weatherflow stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_wflw_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'zy:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some Pioupiou stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_piou_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'zz:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some clientraw stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_clientraw_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'yx:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some realtime stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_realtime_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'yy:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some stickertags stations.
     *
     * @param array $values The values NOT to delete from the table
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_stickertags_from_table($values) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::live_weather_station_datas_table();
        $sql = "DELETE FROM ".$table_name." WHERE device_id like 'zx:%' AND device_id NOT IN ( '" . implode($values, "', '") . "' )";
        return $wpdb->query($sql);
    }

    /**
     * Delete some usermeta values.
     *
     * @param string $key The end of meta_key field.
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected function clean_usermeta($key) {
        return self::_clean_usermeta($key);
    }

    /**
     * Delete some usermeta values.
     *
     * @param string $key The end of meta_key field.
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected static function _clean_usermeta($key) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'usermeta';
        $sql = "DELETE FROM " . $table_name . " WHERE meta_key LIKE \"%\_" . $key . "%\" AND user_id=" . get_current_user_id() . ";";
        return $wpdb->query($sql);
    }

    /**
     * Delete all usermeta values for all users.
     *
     * @return int|false The number of rows deleted, or false on error.
     * @since 3.0.0
     */
    protected static function clean_all_usermeta() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'usermeta';
        $sql = "DELETE FROM " . $table_name . " WHERE meta_key LIKE \"%\_lws-%\"" . ";";
        return $wpdb->query($sql);
    }
}