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
    public static function major_version($version = LWS_VERSION) {
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
    public static function minor_version($version = LWS_VERSION) {
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
    public static function patch_version($version = LWS_VERSION) {
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
            case 'arq': $result = (get_display_language_id() == 'fr') ? 'Arabe (Algérie)' : 'Arabic (Algeria)' ; break;
            case 'ary': $result = (get_display_language_id() == 'fr') ? 'Arabe (Maroc)' : 'Arabic (Morocco)' ; break;
            case 'azb': $result = (get_display_language_id() == 'fr') ? 'Azerbaïdjanais du Sud' : 'South Azerbaijani' ; break;
            case 'bcc': $result = (get_display_language_id() == 'fr') ? 'Balochi du Sud' : 'Southern Balochi' ; break;
            case 'frp': $result = (get_display_language_id() == 'fr') ? 'Arpitan' : 'Arpitan' ; break;
            case 'fuc': $result = (get_display_language_id() == 'fr') ? 'Pulaar' : 'Pulaar' ; break;
            case 'haz': $result = (get_display_language_id() == 'fr') ? 'Hazaragi' : 'Hazaragi' ; break;
            case 'kin': $result = (get_display_language_id() == 'fr') ? 'Kinyarwanda' : 'Kinyarwanda' ; break;
            case 'kmr': $result = (get_display_language_id() == 'fr') ? 'Kurde du Nord' : 'Northern Kurdish' ; break;
            case 'ory': $result = (get_display_language_id() == 'fr') ? 'Oriya' : 'Oriya' ; break;
            case 'rhg': $result = (get_display_language_id() == 'fr') ? 'Rohingya' : 'Rohingya' ; break;
            case 'szl': $result = (get_display_language_id() == 'fr') ? 'Silésien' : 'Silesian' ; break;
            case 'twd': $result = (get_display_language_id() == 'fr') ? 'Twents' : 'Twents' ; break;
            default: $result = \Locale::getDisplayName($id, get_display_language_id());

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
            $result = \Locale::getDisplayRegion('-'.$id, get_display_locale());
        }
        return $result;
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
                if (get_display_language_id() == 'en') {
                    $name = '%s ' . $name;
                    $slug = ucfirst($slug);
                }
                if (get_display_language_id() == 'fr') {
                    if (strpos($name, ' (') > 0) {
                        $name = str_replace(' (', ' %s (', $name);
                    }
                    else {
                        $name = $name . ' %s';
                    }
                    if ($translation['slug'] == 'formal') {
                        $slug = __('formal', 'live-weather-station');
                    }
                    if ($translation['slug'] == 'informal') {
                        $slug = __('informal', 'live-weather-station');
                    }
                }
                $name = sprintf($name, $slug);
            }
            $set[$id] = $name;
        }
        if (class_exists('\Collator')) {
            $collator = new \Collator(get_display_locale());
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
     * Get the current admin color scheme.
     *
     * @since 3.4.0
     */
    public static function current_color_scheme() {
        global $_wp_admin_css_colors;
        $key = get_user_meta(get_current_user_id(), 'admin_color', true);
        if (array_key_exists($key, $_wp_admin_css_colors)) {
            $c = object_to_array($_wp_admin_css_colors[$key]);
            $c['key'] = $key;
            return $c;
        }
        else {
            return array(
                'key' => 'default',
                'name' =>  _x( 'Default', 'admin color scheme' ),
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
     * Get installed cache name.
     *
     * @since 3.4.0
     */
    public static function installed_cache_name() {
        if (self::is_wp_rocket_installed()) {
            return 'WP Rocket';
        }
        if (self::is_wp_super_cache_installed()) {
            return 'WP Super Cache';
        }
        if (self::is_w3_total_cache_installed()) {
            return 'W3 Total Cache';
        }
        if (self::is_autoptimize_installed()) {
            return 'Autoptimize';
        }
        if (self::is_hyper_cache_installed()) {
            return 'Hyper Cache';
        }
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

}