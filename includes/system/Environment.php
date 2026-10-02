<?php

namespace WeatherStation\System\Environment;
use WeatherStation\SDK\Generic\Exception;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Logs\Logger;

/**
 * The class to manage and detect environment.
 *
 * @package Includes\System
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */


class Manager {

    private $Live_Weather_Station;
    private $version;
    private static $stats_ttl = 172800;

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
     * Read /proc/cpuinfo (no shell involved).
     *
     * @return string|false The content or false if not readable.
     * @since 3.1.0
     */
    private static function read_cpuinfo() {
        if (!@is_readable('/proc/cpuinfo')) {
            return false;
        }
        $return = @file_get_contents('/proc/cpuinfo', false, null, 0, 1048576);
        return (empty($return) ? false : $return);
    }

    /**
     * Check if the CPU information is available.
     *
     * @since 3.1.0
     */
    private static function isShellEnabled() {
        return (self::read_cpuinfo() !== false);
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
        return (isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '');
    }

    /**
     * Get the API of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_api() {
        return (isset($_SERVER['GATEWAY_INTERFACE']) ? sanitize_text_field(wp_unslash($_SERVER['GATEWAY_INTERFACE'])) : '');
    }

    /**
     * Get the protocol of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_protocol() {
        return (isset($_SERVER['SERVER_PROTOCOL']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_PROTOCOL'])) : '');
    }

    /**
     * Get the port of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_port() {
        return (isset($_SERVER['SERVER_PORT']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_PORT'])) : '');
    }

    /**
     * Get the document root of this web server.
     *
     * @since 3.1.0
     */
    public static function webserver_document_root() {
        return (isset($_SERVER['DOCUMENT_ROOT']) ? sanitize_text_field(wp_unslash($_SERVER['DOCUMENT_ROOT'])) : '');
    }

    /**
     * Verification of the home web server.
     *
     * @since 3.4.0
     */
    public static function is_home_server() {
        return strpos(self::webserver_domain(), 'eather.station.software') > 0;
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
     * Get the ip address of the server.
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
        $os_detail = @php_uname();
        if ($os_detail == '') {
            return false;
        }
        else {
            $os = explode( " ", trim($os_detail));
            if (count($os) == 13) {
                return $os[0] . ' ' . $os[12];
            }
            elseif (count($os) > 11) {
                return $os[0] . ' ' . $os[count($os) - 1];
            }
            else {
                return $os[0];
            }
        }
    }

    /**
     * Get all the values of a /proc/cpuinfo key.
     *
     * @param string $key The key.
     * @return array The values, as strings.
     * @since 3.8.9
     */
    private static function cpuinfo_values($key) {
        $result = array();
        $content = self::read_cpuinfo();
        if ($content === false) {
            return $result;
        }
        foreach (preg_split('/\R/', $content) as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2 && trim($parts[0]) === $key) {
                $result[] = trim($parts[1]);
            }
        }
        return $result;
    }

    /**
     * Get CPU count of the server.
     *
     * @since 3.1.0
     */
    public static function server_cpu() {
        $cpu_count = get_transient('lws_cpu_count');
        if ($cpu_count === false) {
            $cpu_count = self::cpu_topology()['cpu'];
            set_transient('lws_cpu_count', $cpu_count, HOUR_IN_SECONDS);
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
            $core_count = self::cpu_topology()['core'];
            set_transient('lws_core_count', $core_count, HOUR_IN_SECONDS);
        }
        return $core_count;
    }

    /**
     * Compute the CPU (socket) and core counts from /proc/cpuinfo. ARM and VMs have no 'physical id' / 'cpu cores'
     * lines: fall back to the number of 'processor' lines, and to 'unknown' if nothing can be read.
     *
     * @return array array('cpu' => string, 'core' => string), never empty.
     * @since 3.8.9
     */
    public static function cpu_topology($content = null) {
        $result = array('cpu' => 'unknown', 'core' => 'unknown');
        if ($content === null) {
            $processors = count(self::cpuinfo_values('processor'));
            $physical = array_unique(self::cpuinfo_values('physical id'));
            $cores = self::cpuinfo_values('cpu cores');
        }
        else {
            $processors = 0;
            $physical = $cores = array();
            foreach (preg_split('/\R/', (string)$content) as $line) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $k = trim($parts[0]);
                    if ($k === 'processor') {
                        $processors++;
                    }
                    elseif ($k === 'physical id') {
                        $physical[] = trim($parts[1]);
                    }
                    elseif ($k === 'cpu cores') {
                        $cores[] = trim($parts[1]);
                    }
                }
            }
            $physical = array_unique($physical);
        }
        if ($processors > 0 || count($physical) > 0) {
            $sockets = max(1, count($physical));
            $per_socket = (count($cores) > 0 ? (int)reset($cores) : 0);
            $total = ($per_socket > 0 ? $per_socket * $sockets : $processors);
            $result['cpu'] = (string)$sockets;
            if ($total > 0) {
                $result['core'] = (string)$total;
            }
        }
        return $result;
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
        return (!version_compare(PHP_VERSION, '5.6.0', '<'));
    }

    /**
     * Is the plugin a development preview?
     *
     * @since 3.0.0
     */
    public static function is_plugin_in_dev_mode() {
        return (strpos(LIVE_WEATHER_STATION_VERSION, 'dev') > 0);
    }

    /**
     * Is the plugin a release candidate?
     *
     * @since 3.0.0
     */
    public static function is_plugin_in_rc_mode() {
        return (strpos(LIVE_WEATHER_STATION_VERSION, 'rc') > 0);
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
    public static function major_version($version = LIVE_WEATHER_STATION_VERSION) {
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
    public static function minor_version($version = LIVE_WEATHER_STATION_VERSION) {
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
    public static function patch_version($version = LIVE_WEATHER_STATION_VERSION) {
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
     * Get a stat.
     *
     * @param $arg string The stat to get.
     * @return integer The value of the stat.
     * @since 3.4.0
     */
    private static function stat_misc_get($arg) {
        $result = 0;
        if ($stats = get_option('live_weather_station_misc_stat', false)) {
            if (array_key_exists('timestamp', $stats)) {
                if (time() - $stats['timestamp'] < self::$stats_ttl) {
                    if (array_key_exists($arg, $stats)) {
                        $result = $stats[$arg];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get active installs number.
     *
     * @return integer The active installs number.
     * @since 3.4.0
     */
    public static function stat_active_installs() {
        return self::stat_misc_get('active_installs');
    }

    /**
     * Get downloaded number.
     *
     * @return integer The downloaded number.
     * @since 3.4.0
     */
    public static function stat_downloaded() {
        $result =  self::stat_misc_get('downloaded');
        return ((int)($result / 1000)) * 1000;
    }

    /**
     * Get ratings number.
     *
     * @return integer The ratings number.
     * @since 3.4.0
     */
    public static function stat_num_ratings() {
        return self::stat_misc_get('num_ratings');
    }

    /**
     * Get downloaded number.
     *
     * @return integer The downloaded number.
     * @since 3.4.0
     */
    public static function stat_rating() {
        $result =  self::stat_misc_get('rating');
        return sprintf('%.1F', round($result/20, 1));
    }

    /**
     * Get translation stat.
     *
     * @return array The value of the stat.
     * @since 3.4.0
     */
    private static function stat_translation_get() {
        $result = array('translation_sets' => array());
        if ($stats = get_option('live_weather_station_translation_stat', false)) {
            $result = $stats;
        }
        return $result;
    }

    /**
     * Get translations stats.
     *
     * @param integer $min Min value of percent translated.
     * @param integer $max Max value of percent translated.
     * @return array The translations.
     * @since 3.4.0
     */
    public static function stat_translation($min=0, $max=100) {
        $result = array();
        if ($max == 100) {
            $a = array();
            $a['id'] = 0;
            $a['name'] = 'English (USA)';
            $a['slug'] = 'default';
            $a['project_id'] = 318504;
            $a['locale'] = 'en-us';
            $a['current_count'] = 0;
            $a['untranslated_count'] = 0;
            $a['waiting_count'] = 0;
            $a['fuzzy_count'] = 0;
            $a['percent_translated'] = 100;
            $a['wp_locale'] = 'en_US';
            $a['last_modified'] = '';
            $result[] = $a;
        }
        $stat = self::stat_translation_get();
        if (array_key_exists('translation_sets', $stat)) {
            foreach ($stat['translation_sets'] as $lang) {
                if (array_key_exists('percent_translated', $lang)) {
                    if ($lang['percent_translated'] >= $min && $lang['percent_translated'] <= $max) {
                        $result[] = $lang;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get special locale names.
     *
     * @param string $id The id of the locale.
     * @return string The locale name.
     * @since 3.4.0
     */
    private static function locale_name($id) {
        switch ($id) {
            case 'arq': $result = 'Arabic (Algeria)' ; break;
            case 'ary': $result = 'Arabic (Morocco)' ; break;
            case 'azb': $result = 'South Azerbaijani' ; break;
            case 'bcc': $result = 'Southern Balochi' ; break;
            case 'frp': $result = 'Arpitan' ; break;
            case 'fuc': $result = 'Pulaar' ; break;
            case 'haz': $result = 'Hazaragi' ; break;
            case 'kin': $result = 'Kinyarwanda' ; break;
            case 'kmr': $result = 'Northern Kurdish' ; break;
            case 'ory': $result = 'Oriya' ; break;
            case 'rhg': $result = 'Rohingya' ; break;
            case 'szl': $result = 'Silesian' ; break;
            case 'twd': $result = 'Twents' ; break;
            default: $result = live_weather_station_get_locale_name($id, live_weather_station_get_display_locale());

        }
        return $result;
    }

    /**
     * Get special country code for a locale.
     *
     * @param string $id The id of the locale.
     * @return string The country code name.
     * @since 3.4.0
     */
    private static function country_code($id) {
        $result = '';
        if ($result == '' && strpos($id, '_')) {
            $result = substr($id, strpos($id, '_') + 1);
        }
        if ($result == '' && strpos($id, '-')) {
            $result = substr($id, strpos($id, '-') + 1);
        }
        if ($result == '') {
            switch ($id) {
                case 'sq': $result = 'AL' ; break;
                case 'an': $result = 'ES' ; break;
                case 'hy': $result = 'AM' ; break;
                case 'as': $result = 'IN' ; break;
                case 'ba': $result = 'RU' ; break;
                case 'co': $result = 'FR' ; break;
                case 'dv': $result = 'MV' ; break;
                case 'et': $result = 'EE' ; break;
                case 'fo': $result = 'DK' ; break;
                case 'fy': $result = 'NL' ; break;
                case 'cy': $result = 'GB' ; break;
                case 'el': $result = 'GR' ; break;
                case 'ja': $result = 'JP' ; break;
                case 'kn': $result = 'IN' ; break;
                case 'lv': $result = 'LV' ; break;
                case 'mr': $result = 'IN' ; break;
                case 'th': $result = 'TH' ; break;
                case 'tl': $result = 'PH' ; break;
                case 'arq': $result = 'DZ' ; break;
                case 'ary': $result = 'MA' ; break;
                case 'bel': $result = 'BY' ; break;
                case 'bre': $result = 'FR' ; break;
                case 'ceb': $result = 'PH' ; break;
                case 'dzo': $result = 'BT' ; break;
                case 'fur': $result = 'IT' ; break;
                case 'haz': $result = 'AF' ; break;
                case 'kab': $result = 'DZ' ; break;
                case 'kal': $result = 'DK' ; break;
                case 'ory': $result = 'IN' ; break;
                case 'roh': $result = 'CH' ; break;
                case 'sah': $result = 'RU' ; break;
                case 'scn': $result = 'IT' ; break;
                case 'srd': $result = 'IT' ; break;
                case 'tah': $result = 'FR' ; break;
                case 'twd': $result = 'NL' ; break;
                case 'xho': $result = 'ZA' ; break;
                case 'art-xemoji': $result = 'ZZ' ; break;
            }
        }
        if ($result == '') {
            $result = 'WP';
        }
        return $result;
    }

    /**
     * Get country name.
     *
     * @param string $id The country code.
     * @return string The country name.
     * @since 3.4.0
     */
    private static function country_name($id) {
        if ($id == 'WP') {
            $result = '-';
        }
        else {
            $result = live_weather_station_get_region_name('-'.$id, live_weather_station_get_display_locale());
        }
        return $result;
    }

    /**
     * Verify if class "Locale" is correctly installed.
     *
     * @since 3.5.3
     */
    public static function is_locale_operational() {
        $t = live_weather_station_get_region_name('-FR', live_weather_station_get_display_locale());
        return $t != 'FR';
    }

    /**
     * Get translations stats sorted by locale names.
     *
     * @param integer $min Min value of percent translated.
     * @param integer $max Max value of percent translated.
     * @return array The translations sorted by locale names.
     * @since 3.4.0
     */
    public static function stat_translation_by_locale($min=0, $max=100) {
        $result = array();
        $set = array();
        $translations = self::stat_translation($min, $max);
        foreach ($translations as $id => $translation) {
            $name = self::locale_name($translation['locale']);
            $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
            if ($translation['slug'] != 'default') {
                $slug = $translation['slug'];
                $name = '%s ' . $name;
                $slug = ucfirst($slug);
                $name = sprintf($name, $slug);
            }
            $set[$id] = $name;
        }
        if (class_exists('\Collator')) {
            $collator = new \Collator(live_weather_station_get_display_locale());
            $collator->asort($set);
        }
        else {
            asort($set, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        }
        foreach ($set as $id => $translation) {
            $result[$id]['name'] = $translation;
            $result[$id]['translated'] = $translations[$id]['percent_translated'];
            $result[$id]['locale_code'] = $translations[$id]['locale'];
            if (array_key_exists('wp_locale', $translations[$id])) {
                $result[$id]['country_code'] = strtoupper(self::country_code($translations[$id]['wp_locale']));
            }
            else {
                $result[$id]['country_code'] = 'WP';
            }

            $result[$id]['country_name'] = self::country_name($result[$id]['country_code']);
            $result[$id]['details'] = $translations[$id];
            $result[$id]['svg-class'] = 'flag-icon-' . strtolower($result[$id]['country_code']);
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
            $result = ($old != LIVE_WEATHER_STATION_VERSION);
        }
        return $result;
    }

    /**
     * Is the WP update system enabled?
     *
     * @return boolean True if the WP update system is enabled, false otherwise.
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
     * Checks if the automatic update of the plugin is enabled in WordPress (the native per-plugin setting).
     *
     * @since 3.1.3
     * @return bool Returns true if auto-update is enabled, false otherwise.
     */
    public static function is_autoupdatable() {
        if (!self::is_updatable()) {
            return false;
        }
        $enabled = (array)get_site_option('auto_update_plugins', array());
        return in_array(plugin_basename(LIVE_WEATHER_STATION_PLUGIN_DIR . 'live-weather-station.php'), $enabled, true);
    }

    /**
     * Run a callback on the current site or, on a multisite, on other sites.
     *
     * @param callable $callback The callback to run (no argument).
     * @param string|int|null $sites Optional. Null for the current site, 'all' for every site of the network, a site id for this site.
     *                               Outside a multisite, the callback always runs on the current (only) site.
     * @since 3.9.0
     */
    public static function run_on_sites($callback, $sites=null) {
        if ($sites === null || !is_multisite()) {
            call_user_func($callback);
            return;
        }
        $ids = ($sites === 'all') ? get_sites(array('fields' => 'ids', 'number' => 0)) : array((int)$sites);
        foreach ($ids as $id) {
            switch_to_blog($id);
            try {
                call_user_func($callback);
            }
            finally {
                restore_current_blog();
            }
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
     * Verify if PHP is up to date.
     *
     * @since 3.5.4
     */
    public static function is_php_version_uptodate() {
        return (version_compare(PHP_VERSION, LIVE_WEATHER_STATION_MINIMUM_PHP_VERSION, '>='));
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
     * Get the database size.
     *
     * @since 3.4.1
     */
    public static function mysql_total_size() {
        global $wpdb;
        $query = $wpdb->get_results('SHOW TABLE STATUS', ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- constant query without any parameter: live database size for the system information screen, must not be cached
        $result = 0;
        if ($wpdb->num_rows > 0) {
            foreach ($query as $row) {
                $result += $row['Data_length'] + $row['Index_length'];
            }
        }
        return $result;
    }

    /**
     * Get the database size allocated for Weather Station.
     *
     * @since 3.4.1
     */
    public static function mysql_lws_size() {
        global $wpdb;
        $query = $wpdb->get_results('SHOW TABLE STATUS', ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- constant query without any parameter: live database size for the system information screen, must not be cached
        $result = 0;
        if ($wpdb->num_rows > 0) {
            foreach ($query as $row) {
                if (strpos($row['Name'], 'live_weather_station_') !== false) {
                    $result += $row['Data_length'] + $row['Index_length'];
                }
            }
        }
        return $result;
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
     * Get the database size human readable.
     *
     * @since 3.4.1
     */
    public static function mysql_total_size_text() {
        return size_format(self::mysql_total_size(), 1);
    }

    /**
     * Get the database size allocated for Weather Station human readable.
     *
     * @since 3.4.1
     */
    public static function mysql_lws_size_text() {
        return size_format(self::mysql_lws_size(), 1);
    }

    /**
     * Get the database name and host.
     *
     * @since 3.4.1
     */
    public static function mysql_name_text() {
        return DB_NAME . '@' . DB_HOST;
    }

    /**
     * Get the database charset.
     *
     * @since 3.4.1
     */
    public static function mysql_charset_text() {
        return DB_CHARSET;
    }

    /**
     * Get the database charset.
     *
     * @since 3.4.1
     */
    public static function mysql_user_text() {
        return DB_USER;
    }

    /**
     * Get the WordPress version human readable.
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
     * Get the WordPress version.
     *
     * @since 3.0.0
     */
    public static function wordpress_version_id() {
        global $wp_version;
        return 'WordPress/' . $wp_version;
    }

    /**
     * Get the WordPress debug status.
     *
     * @since 3.0.0
     */
    public static function wordpress_debug_text() {
        $debug = false;
        $opt = array();
        $s = '';
        if (defined('WP_DEBUG')) {
            if (WP_DEBUG) {
                $debug = true;
                if (defined('WP_DEBUG_LOG')) {
                    if (WP_DEBUG_LOG) {
                        $opt[] = __('log', 'live-weather-station');
                    }
                }
                if (defined('WP_DEBUG_DISPLAY')) {
                    if (WP_DEBUG_DISPLAY) {
                        $opt[] = __('display', 'live-weather-station');
                    }
                }
                $s = implode(', ', $opt);
            }
        }
        return ($debug ? __('Debug enabled', 'live-weather-station') .($s != '' ? ' (' . $s . ')' : '') :  __('Debug disabled', 'live-weather-station'));
    }

    /**
     * Verify if WP is up to date.
     *
     * @since 3.5.4
     */
    public static function is_wp_version_uptodate() {
        global $wp_version;
        return (version_compare($wp_version, LIVE_WEATHER_STATION_MINIMUM_WP_VERSION) >= 0);
    }

    /**
     * Get the current admin color scheme.
     *
     * @since 3.4.0
     */
    public static function current_color_scheme() {
        global $_wp_admin_css_colors;
        $key = get_user_meta(get_current_user_id(), 'admin_color', true);
        if (array_key_exists($key, $_wp_admin_css_colors)) {
            $c = live_weather_station_object_to_array($_wp_admin_css_colors[$key]);
            $c['key'] = $key;
            return $c;
        }
        else {
            return array(
                'key' => 'default',
                'name' =>  _x( 'Default', 'admin color scheme' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- deliberately the WordPress core string (default text domain) to label the core admin color scheme
                'url' => false,
                'colors' => array( '#222', '#333', '#0073aa', '#00a0d2' ),
                'icon_colors' => array( 'base' => '#82878c', 'focus' => '#00a0d2', 'current' => '#fff' ));
        }
    }
    /**
     * Get the current admin color scheme.
     *
     * @since 3.4.0
     */
    public static function icon_color_scheme() {
        $c = self::current_color_scheme();
        $result = array();
        switch ($c['key']) {
            case 'light':
                $result['text'] = '#FFF';
                $result['border'] = $c['colors'][1];
                $result['background'] = $c['colors'][1];
                break;
            case 'midnight':
                $result['text'] = $c['icon_colors']['current'];
                $result['border'] = $c['colors'][1];
                $result['background'] = $c['colors'][3];
                break;
            default:
                $result['text'] = $c['icon_colors']['current'];
                $result['border'] = $c['colors'][1];
                $result['background'] = $c['colors'][2];
        }
        return $result;
    }

    /**
     * Get the Weather Station version human readable.
     *
     * @since 3.0.0
     */
    public static function weatherstation_version_text() {
        $s = LIVE_WEATHER_STATION_PLUGIN_NAME . ' ' . LIVE_WEATHER_STATION_VERSION;
        if (defined('LIVE_WEATHER_STATION_CODENAME')) {
            $s .= ' ' . LIVE_WEATHER_STATION_CODENAME;
        }
        return $s;
    }

    /**
     * Get the Weather Station version human readable.
     *
     * @since 3.0.0
     */
    public static function weatherstation_version_id() {
        return 'WeatherStation/' . LIVE_WEATHER_STATION_VERSION;
    }

    /**
     * Verify if WP Rocket is installed.
     *
     * @since 3.4.0
     */
    public static function is_wp_rocket_installed() {
        return function_exists('rocket_clean_domain');
    }

    /**
     * Verify if WP Super Cache is installed.
     *
     * @since 3.4.0
     */
    public static function is_wp_super_cache_installed() {
        return function_exists('wpsc_init');
    }

    /**
     * Verify if W3 Total Cache is installed.
     *
     * @since 3.4.0
     */
    public static function is_w3_total_cache_installed() {
        return class_exists('\W3TC\Root_Loader');
    }

    /**
     * Verify if Autoptimize is installed.
     *
     * @since 3.4.0
     */
    public static function is_autoptimize_installed() {
        return class_exists('autoptimizeCache');
    }

    /**
     * Verify if HyperCache is installed.
     *
     * @since 3.4.0
     */
    public static function is_hyper_cache_installed() {
        return class_exists('HyperCache');
    }

    /**
     * Verify if opcache is active.
     *
     * @since 3.4.0
     */
    public static function is_opcache_installed() {
        if (function_exists('opcache_get_status') && version_compare(PHP_VERSION, '5.6.0') >= 0) {
            $status = @opcache_get_status(false);
            return (is_array($status) && !empty($status['opcache_enabled']));

        }
        else return false;
    }

    /**
     * Verify if WP_CACHE is active.
     *
     * @since 3.4.0
     */
    public static function is_wpcache_installed() {
        if (defined('WP_CACHE')) {
            return WP_CACHE;

        }
        else return false;
    }

    /**
     * Get the installed cache name.
     *
     * @since 3.4.0
     */
    public static function get_installed_cache_name() {
        $result = '';
        if (self::is_wp_rocket_installed()) {
            $result = 'WP Rocket';
        }
        if (self::is_wp_super_cache_installed()) {
            $result = 'WP Super Cache';
        }
        if (self::is_w3_total_cache_installed()) {
            $result = 'W3 Total Cache';
        }
        if (self::is_autoptimize_installed()) {
            $result = 'Autoptimize';
        }
        if (self::is_hyper_cache_installed()) {
            $result = 'Hyper Cache';
        }
        return $result;
    }

    /**
     * Get the WordPress cache text.
     *
     * @since 3.4.1
     */
    public static function wordpress_cache_text() {
        $opt = array();
        if (self::is_wpcache_installed()) {
            $opt[] = ucfirst(__('persistent cache', 'live-weather-station'));
        }
        else {
            $opt[] = ucfirst(__('transient cache', 'live-weather-station'));
        }
        if (self::is_opcache_installed()) {
            $opt[] = __('OPcache', 'live-weather-station');
        }
        if (self::is_cache_installed()) {
            $opt[] = self::get_installed_cache_name();
        }
        return implode(' + ', $opt);
    }

    /**
     * Verify if a cache is installed.
     *
     * @since 3.4.0
     */
    public static function is_cache_installed() {
        return self::is_wp_rocket_installed() || self::is_wp_super_cache_installed() ||
               self::is_w3_total_cache_installed() || self::is_autoptimize_installed() ||
               self::is_hyper_cache_installed();
    }

    /**
     * Verify if Polylang is installed.
     *
     * @since 3.4.1
     */
    public static function is_polylang_installed() {
        return function_exists('pll_current_language');
    }

    /**
     * Verify if WPML is installed.
     *
     * @since 3.4.1
     */
    public static function is_wpml_installed() {
        return function_exists('icl_object_id');
    }

    /**
     * Verify if Babble is installed.
     *
     * @since 3.4.1
     */
    public static function is_babble_installed() {
        return function_exists('bbl_get_current_content_lang_code');
    }

    /**
     * Verify if Weglot is installed.
     *
     * @since 3.4.1
     */
    public static function is_weglot_installed() {
        return class_exists('Weglot');
    }


    /**
     * Get the installed multilang manager name.
     *
     * @since 3.4.1
     */
    public static function get_installed_multilang_name() {
        $result = '';
        if (self::is_polylang_installed()) {
            $result = 'Polylang';
        }
        elseif (self::is_wpml_installed()) {
            $result = 'WPML';
        }
        if (self::is_babble_installed()) {
            $result = 'Babble';
        }
        if (self::is_weglot_installed()) {
            $result = 'Weglot';
        }
        return $result;
    }

    /**
     * Verify if a multilang manager is installed.
     *
     * @since 3.4.1
     */
    public static function is_multilang_installed() {
        return self::is_polylang_installed() || self::is_wpml_installed() ||
            self::is_babble_installed() || self::is_weglot_installed();
    }

    /**
     * Get the current language id for the page.
     *
     * @since 3.4.1
     */
    public static function get_multilang_language_id() {
        $result = live_weather_station_get_display_locale();
        if (self::is_polylang_installed()) {
            $result = pll_current_language();
        }
        if (self::is_wpml_installed()) {
            $result = ICL_LANGUAGE_CODE;
        }
        if (self::is_babble_installed()) {
            $result = bbl_get_current_content_lang_code();
        }
        if (self::is_weglot_installed()) {
            $result = \Weglot::Instance()->getCurrentLang();
        }
        return strtolower($result);
    }

    /**
     * Get the cache prefix.
     *
     * @since 3.4.1
     */
    public static function get_cache_prefix() {
        return self::get_multilang_language_id() . '_';
    }

}