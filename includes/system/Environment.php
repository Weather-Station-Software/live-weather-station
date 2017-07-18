<?php

namespace WeatherStation\System\Environment;
use WeatherStation\SDK\Generic\Exception;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Logs\Logger;

/**
 * The class to manage and detect environment.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */


class Manager {

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.0.0
     */
    public function __construct( $Live_Weather_Station, $version ) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Check if the server config allows shell_exec().
     *
     * @since 3.1.0
     */
    private static function isShellEnabled() {
        if (function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(', ', ini_get('disable_functions')))) && strtolower(ini_get('safe_mode')) != 1 ) {
            $return = shell_exec('cat /proc/cpuinfo');
            if (!empty($return)) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * The detection of this url allows the plugin to make ajax calls when
     * the site has not a standard /wp-admin/ path.
     *
     * @since 2.2.2
     */
    public static function ajax_dir_relative_url() {
        $url = preg_replace('/(http[s]?:\/\/.*\/)/iU', '',  get_admin_url(), 1);
        return (substr($url, 0) == '/' ? '' : '/') . $url . (substr($url, -1) == '/' ? '' : '/') .'admin-ajax.php';
    }

    /**
     * The detection of this url allows the plugin to be called from
     * WP dashboard when the site has not a standard /wp-admin/ path.
     *
     * @since 2.2.2
     */
    public static function admin_dir_relative_url() {
        $url = preg_replace('/(http[s]?:\/\/.*\/)/iU', '',  get_admin_url(), 1);
        return (substr($url, 0) == '/' ? '' : '/') . $url . (substr($url, -1) == '/' ? '' : '/') .'admin.php';
    }

    /**
     * Get the name of this web server software.
     *
     * @since 3.1.0
     */
    public static function webserver_software_name() {
        return $_SERVER['SERVER_SOFTWARE'];
    }

    /**
     * Get the API of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_api() {
        return $_SERVER['GATEWAY_INTERFACE'];
    }

    /**
     * Get the protocol of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_protocol() {
        return $_SERVER['SERVER_PROTOCOL'];
    }

    /**
     * Get the port of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_port() {
        return $_SERVER['SERVER_PORT'];
    }

    /**
     * Get the document root of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_document_root() {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Get the domain of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_domain() {
        $domain = get_option('siteurl');
        $domain = str_replace('http://', '', $domain);
        $domain = str_replace('https://', '', $domain);
        return $domain;
    }

    /**
     * Get the ip adresss of the server.
     *
     * @since 3.1.0
     */
    public static function server_ip() {
        return gethostbyname(self::webserver_domain());
    }

    /**
     * Get the OS of the server.
     *
     * @since 3.1.0
     */
    public static function server_os() {
        $os_detail = php_uname();
        $os = explode( " ", trim($os_detail));
        return $os[0] . ' ' . $os[12];
    }

    /**
     * Get CPU count of the server.
     *
     * @since 3.1.0
     */
    public static function server_cpu() {
        $cpu_count = get_transient('lws_cpu_count');
        if ($cpu_count === false) {
            if (self::isShellEnabled()) {
                $cpu_count = shell_exec('cat /proc/cpuinfo |grep "physical id" | sort | uniq | wc -l');
                set_transient ('lws_cpu_count', $cpu_count, HOUR_IN_SECONDS);
            } else {
                return false;
            }
        }
        return $cpu_count;
    }
    
    /**
     * Get core count of the server.
     *
     * @since 3.1.0
     */
    public static function server_core() {
        $core_count = get_transient('lws_core_count');
        if ($core_count === false) {
            if (self::isShellEnabled()) {
                $core_count = shell_exec("echo \"$((`cat /proc/cpuinfo | grep cores | grep -o '[0-9]' | uniq` * `cat /proc/cpuinfo |grep 'physical id' | sort | uniq | wc -l`))\"");
                set_transient ('lws_core_count', $core_count, HOUR_IN_SECONDS);
            } else {
                return false;
            }
        }
        return $core_count;
    }

    /**
     * Get the full information for the ip of the server.
     *
     * @since 3.1.0
     */
    public static function server_full_information() {
        if ($result = get_transient('lws_server_location')) {
            return $result;
        }
        try {
            Quota::verify('ip-API', 'GET');
            $query = 'http://ip-api.com/json/'.self::server_ip();
            $args = array();
            $args['user-agent'] = LWS_PLUGIN_AGENT;
            $args['timeout'] = get_option('live_weather_station_system_http_timeout');
            $content = wp_remote_get($query, $args);
            if (is_wp_error($content)) {
                Logger::error('API / SDK','ip-API',null,null,null,null,$content->get_error_code(),$content->get_error_message() );
                return false;
            }
            $error = false;
            $code = 0;
            $message = 'Unknown error.';
            if (array_key_exists('response', $content)) {
                $response = $content['response'];
            }
            else {
                $response = array();
            }
            if (array_key_exists('code', $response)) {
                $code = $response['code'];
                if ($code != '200') {
                    $error = true;
                    if (array_key_exists('message', $response)) {
                        $message = $response['message'];
                    }
                }
            }
            else {
                $error = true;
            }
            if ($error) {
                Logger::error('API / SDK','ip-API',null,null,null,null,$code,$message);
                return false;
            }
            if (!array_key_exists('body', $content)) {
                Logger::error('API / SDK','ip-API',null,null,null,null,null,'The server sent an empty response.');
                return false;
            }
            $result = json_decode($content['body'], true);
        }
        catch (Exception $e) {
            Logger::error('API / SDK','ip-API',null,null,null,null,$e->getCode(),$e->getMessage() );
            return false;
        }
        set_transient('lws_server_location', $result, HOUR_IN_SECONDS);
        return $result;
    }

    /**
     * Get the hoster detail.
     *
     * @since 3.1.0
     */
    public static function hoster_name() {
        $s = self::server_full_information();
        return $s['org'];
    }

    /**
     * Get the hoster location.
     *
     * @since 3.1.0
     */
    public static function hoster_location() {
        $s = self::server_full_information();
        return $s['city'] . ', ' . $s['country'];
    }

    /**
     * Verification of mandatory internationalization extension.
     *
     * @since 2.3.0
     */
    public static function is_i18n_loaded() {
        return (class_exists('Locale') && class_exists('DateTimeZone'));
    }

    /**
     * Verification of mandatory cURL extension.
     *
     * @since 3.0.0
     */
    public static function is_curl_loaded() {
        return (function_exists('curl_version'));
    }

    /**
     * Verification of mandatory json extension.
     *
     * @since 3.0.0
     */
    public static function is_json_loaded() {
        return (function_exists('json_decode'));
    }

    /**
     * Verification of PHP version.
     *
     * @since 3.0.0
     */
    public static function is_php_version_ok() {
        return (!version_compare(PHP_VERSION, '5.4.0', '<'));
    }

    /**
     * Is the plugin a development preview?
     *
     * @since 3.0.0
     */
    public static function is_plugin_in_dev_mode() {
        return (strpos(LWS_VERSION, 'dev') > 0);
    }

    /**
     * Is the plugin a release candidate?
     *
     * @since 3.0.0
     */
    public static function is_plugin_in_rc_mode() {
        return (strpos(LWS_VERSION, 'rc') > 0);
    }

    /**
     * Is the plugin in a production-ready version?
     *
     * @since 3.0.0
     */
    public static function is_plugin_in_production_mode() {
        return (!self::is_plugin_in_dev_mode() && !self::is_plugin_in_rc_mode());
    }

    /**
     * Get the major version number.
     *
     * @param string $version Optional. The full version string.
     * @return string The major version number.
     * @since 3.3.0
     */
    private static function major_version($version = LWS_VERSION) {
        try {
            $result = substr($version, 0, strpos($version, '.'));
        } catch (\Exception $ex) {
            $result = 'x';
        }
        return $result;
    }

    /**
     * Get the major version number.
     *
     * @param string $version Optional. The full version string.
     * @return string The major version number.
     * @since 3.3.0
     */
    private static function minor_version($version = LWS_VERSION) {
        try {
            $result = substr($version, strpos($version, '.') + 1, 1000);
            $result = substr($result, 0, strpos($result, '.'));
        } catch (\Exception $ex) {
            $result = 'x';
        }
        return $result;
    }

    /**
     * Get the major version number.
     *
     * @param string $version Optional. The full version string.
     * @return string The major version number.
     * @since 3.3.0
     */
    private static function patch_version($version = LWS_VERSION) {
        try {
            $result = substr($version, strpos($version, '.') + 1, 1000);
            $result = substr($result, strpos($result, '.') + 1, 1000);
            if (strpos($result, '-') > 0) {
                $result = substr($result, 0, strpos($result, '-') );
            }
        } catch (\Exception $ex) {
            $result = 'x';
        }
        return $result;
    }

    /**
     * Is the plugin be updated?
     *
     * @param string $old Previous version.
     * @return boolean True if it is updated (not just patched), false otherwise.
     * @since 3.3.0
     */
    public static function is_updated($old) {
        if (self::is_plugin_in_production_mode()) {
            $result = ((self::major_version() != self::major_version($old)) || (self::minor_version() != self::minor_version($old)));
        }
        else {
            $result = ($old != LWS_VERSION);
        }
        return $result;
    }

    /**
     * Is the WP update system enabled?
     *
     * @since 3.1.3
     */
    public static function is_updatable() {
        $result = true;
        if (defined('AUTOMATIC_UPDATER_DISABLED')) {
            $result = !AUTOMATIC_UPDATER_DISABLED;
        }
        return $result;
    }

    /**
     * Is the plugin auto-update enabled?
     *
     * @since 3.1.3
     */
    public static function is_autoupdatable() {
        return (self::is_updatable() && get_option('live_weather_station_auto_update'));
    }

    /**
     * Choose if the plugin must be auto-updated or not.
     * Concerned hook: auto_update_plugin
     *
     * @since 3.1.3
     */
    public static function lws_auto_update($update, $item) {
        if (($item->slug == LWS_PLUGIN_SLUG) && self::is_autoupdatable()){
            return true;
        }
        else {
            return $update;
        }
    }

    /**
     * Get the PHP version.
     *
     * @since 3.0.0
     */
    public static function php_version() {
        $s = phpversion();
        if (strpos($s, '-') > 0) {
            $s = substr($s, 0, strpos($s, '-'));
        }
        return $s;
    }

    /**
     * Get the PHP version human readable.
     *
     * @since 3.0.0
     */
    public static function php_version_text() {
        return 'PHP ' . self::php_version();
    }

    /**
     * Get the MYSQL version.
     *
     * @since 3.0.0
     */
    public static function mysql_version() {
        global $wpdb;
        return $wpdb->db_version();
    }

    /**
     * Get the MYSQL version human readable.
     *
     * @since 3.0.0
     */
    public static function mysql_version_text() {
        return 'MySQL ' . self::mysql_version();
    }

    /**
     * Get the Wordpress version human readable.
     *
     * @since 3.0.0
     */
    public static function wordpress_version_text() {
        global $wp_version;
        $s = '';
        if (is_multisite()) {
            $s = 'MU ';
        }
        return 'WordPress ' . $s . $wp_version;
    }

    /**
     * Get the Wordpress version.
     *
     * @since 3.0.0
     */
    public static function wordpress_version_id() {
        global $wp_version;
        return 'WordPress/' . $wp_version;
    }

    /**
     * Get the Weather Station version human readable.
     *
     * @since 3.0.0
     */
    public static function weatherstation_version_text() {
        $s = LWS_PLUGIN_NAME . ' ' . LWS_VERSION;
        if (defined('LWS_CODENAME')) {
            $s .= ' ' . LWS_CODENAME;
        }
        return $s;
    }

    /**
     * Get the Weather Station version human readable.
     *
     * @since 3.0.0
     */
    public static function weatherstation_version_id() {
        return 'WeatherStation/' . LWS_VERSION;
    }

}