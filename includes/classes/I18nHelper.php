<?php

namespace WeatherStation\System\I18N;

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\System\Environment\Manager as EnvManager;
use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;


/**
 * This class add i18n management.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

class Handling {

    private $locale;
    private $locale_name;
    private $locale_native_name;
    private $locale_path;
    private $percent_translated;
    private $count_translated;
    private $translation_exists;
    private $last_modified;
    private $percent_min = 95;
    private $cpt;

    private $service_name = 'I18n Helper';

    /**
     * Class constructor
     *
     * @since 3.0.0
     */
    public function __construct() {
        $this->locale = live_weather_station_get_display_locale();
        if ('en_US' === $this->locale) {
            $this->reset_partial_translation();
        }
        else {
            $this->translation_details();
            if (!$this->is_translatable() && EnvManager::is_plugin_in_production_mode()) {
                $this->reset_partial_translation();
            }
        }
    }

    /**
     * Reset the partial translation flag, only for a real administrator.
     * The capability can't be tested while the plugin is loading (user not yet available): it is deferred to init.
     * is_admin() is also true for unauthenticated admin-ajax.php requests, hence the capability check.
     *
     * @since 3.8.15
     */
    private function reset_partial_translation() {
        if (!(is_admin() || is_blog_admin())) {
            return;
        }
        $reset = function() {
            if (current_user_can('manage_options')) {
                update_option('live_weather_station_partial_translation', 0);
            }
        };
        if (did_action('init')) {
            $reset();
        }
        else {
            add_action('init', $reset);
        }
    }

    /**
     * Is the plugin translatable?
     *
     * @return boolean True if it is translatable, false otherwise.
     * @since 3.0.0
     */
    public function is_translatable() {
       if ('en_US' === $this->locale) {
           return false;
       }
       else {
           return (((EnvManager::is_plugin_in_production_mode() && $this->percent_translated < $this->percent_min) || !EnvManager::is_plugin_in_production_mode()) && (live_weather_station_get_display_locale() == get_locale()));
       }
    }

    /**
     * Get the url for the mo file.
     *
     * @param string $branch The branch in which retrieve the file. Accepted values: "stable" or "dev".
     * @return bool|string The URL if it has a translation, false otherwise.
     * @since 3.0.0
     */
    public function get_mo_file_url($branch = 'stable') {
        if ('en_US' === $this->locale) {
            return false;
        }
        if (!$this->locale_path) {
            return false;
        }
        if (!in_array($branch, array('stable', 'dev'), true) || !preg_match('/^[A-Za-z0-9_-]{2,20}$/D', (string)$this->locale_path)) {
            return false;
        }
        return 'https://translate.wordpress.org/projects/wp-plugins/live-weather-station/' . $branch . '/' . rawurlencode($this->locale_path) . '/default/export-translations?format=mo';
    }

    /**
     * Get the directory where the partial translation files are stored (in uploads: the plugin directory is replaced at each update).
     *
     * @param bool $create Optional. Create the directory if it does not exist.
     * @return string The directory, with a trailing slash.
     * @since 3.9.0
     */
    public static function get_languages_dir($create = false) {
        $upload = wp_upload_dir(null, false);
        $dir = trailingslashit($upload['basedir']) . LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN . '/languages/';
        if ($create && !is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    /**
     * Get the mo file for current translation.
     *
     * @return string The full filename for current mo file.
     * @since 3.0.0
     */
    public static function get_current_mo_file() {
        $branch = 'stable';
        if (!EnvManager::is_plugin_in_production_mode()) {
            $branch = 'dev';
        }
        return self::get_languages_dir() . LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN . '-' . $branch . '-' . live_weather_station_get_display_locale() . '.mo';
    }

    /**
     * Delete all the mo files of the current branch.
     *
     * @return boolean True if if has been downloaded, false otherwise.
     * @since 3.0.0
     */
    public function delete_mo_files() {
        $branch = 'stable';
        if (!EnvManager::is_plugin_in_production_mode()) {
            $branch = 'dev';
        }
        $target = self::get_languages_dir() . LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN . '-' . $branch . '-??_??.mo';
        $files = glob($target);
        $ok = true;
        foreach ((is_array($files) ? $files : array()) as $file) {
            wp_delete_file($file);
            if (file_exists($file)) {
                $ok = false;
            }
        }
        if (!$ok) {
            Logger::error($this->service_name, null, null, null, null, null, 1, 'Unable to delete old translation files in the uploads directory.');
        }
        Cache::invalidate_i18n('last_modified_' . $this->locale);
        return $ok;
    }

    /**
     * Download the mo file and copy it in /languages dir.
     *
     * @param string $target The target directory in which to put the file.
     * @param string $branch The branch in which retrieve the file. Accepted values: "stable" or "dev".
     * @return boolean True if if has been downloaded, false otherwise.
     * @since 3.0.0
     */
    public function download_mo_file($target, $branch = 'stable') {
        if ($url = $this->get_mo_file_url($branch)) {
            if (!function_exists('download_url')) {
                // Not loaded in cron or front-end requests.
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            if (!function_exists('download_url')) {
                Logger::alert('Core', null, null, null, null, null, 666, 'Unable to load the WordPress file download functions (download_url).');
                Logger::error($this->service_name, null, null, null, null, null, 666, $this->locale_name . ' translation file can not be downloaded from WordPress.org.');
                return false;
            }
            // Only translate.wordpress.org, over https, is trusted as a source.
            $parts = wp_parse_url($url);
            if (!is_array($parts) || !isset($parts['scheme']) || $parts['scheme'] !== 'https' || !isset($parts['host']) || $parts['host'] !== 'translate.wordpress.org' || !preg_match('/^[a-z]{2,3}(_[A-Za-z0-9]+)*$/D', (string)$this->locale)) {
                Logger::error($this->service_name, null, null, null, null, null, 666, 'Translation file source rejected.');
                return false;
            }
            $file = download_url($url, 30);
            if (!wp_mkdir_p($target)) {
                Logger::error($this->service_name, null, null, null, null, null, 1, 'Unable to create the translation files directory in uploads.');
                if (!is_wp_error($file)) {
                    wp_delete_file($file);
                }
                return false;
            }
            $target .= LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN . '-' . $branch . '-' . $this->locale . '.mo';
            if (is_wp_error($file)) {
                Logger::error($this->service_name, null, null, null, null, null, 300, 'Unable to download ' . $this->locale_name . ' translation file from WordPress.org. Error was: ' . substr(sanitize_text_field(implode(' / ', $file->get_error_messages())), 0, 300));
                return false;
            }
            else {
                // Cheap integrity check: size cap and gettext magic number.
                $head = (@filesize($file) > 0 && @filesize($file) <= 5242880) ? @file_get_contents($file, false, null, 0, 4) : false;
                if ($head !== "\x95\x04\x12\xde" && $head !== "\xde\x12\x04\x95") {
                    Logger::error($this->service_name, null, null, null, null, null, 1, 'Downloaded ' . $this->locale_name . ' translation file is not a valid .mo file.');
                    wp_delete_file($file);
                    return false;
                }
                if (!copy($file, $target)) {
                    Logger::error($this->service_name, null, null, null, null, null, 1, 'Unable to copy ' . $this->locale_name . ' translation file to the uploads directory.');
                    wp_delete_file($file);
                    return false;
                }
                else {
                    Logger::notice($this->service_name, null, null, null, null, null, 0, $this->locale_name . ' translation file successfully updated from ' . $branch . ' branch.');
                    wp_delete_file($file);
                    return true;
                }
            }
        }
    }

    /**
     * Verify if a new translation is ready to download and if so, do it.
     *
     * @since 3.0.0
     */
    public function cron_run() {
        $cron_id = Watchdog::init_chrono(Watchdog::$translation_update_name);
        if ($this->last_modified && (bool)get_option('live_weather_station_partial_translation')) {
            if ($this->last_modified != Cache::get_i18n('last_modified_' . $this->locale)) {
                $branch = 'stable';
                if (!EnvManager::is_plugin_in_production_mode()) {
                    $branch = 'dev';
                }
                if ($this->download_mo_file(self::get_languages_dir(), $branch)) {
                    Cache::set_i18n('last_modified_' . $this->locale, $this->last_modified);
                }
            }
        }
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Get the message to display.
     *
     * @return bool|string The message.
     * @since 3.0.0
     */
    public function get_message() {
        $message = false;
        $locale = $this->locale_name;
        if ($this->translation_exists && $this->percent_translated < $this->percent_min) {
            if ((bool)get_option('live_weather_station_partial_translation')) {
                $s = /* translators: 1: language name, 2: plugin name */ __('%2$s is using a partial translation in %1$s.', 'live-weather-station');
            }
            else {
                $s = /* translators: 1: language name, 2: plugin name */ __('There is a partial translation of %2$s in %1$s.', 'live-weather-station');
            }
            $message = $s . ' ' . /* translators: 3: percentage of the plugin translated, 4: link labelled "see details" to the translation help */ __('This translation is currently %3$d%% complete. We need your help to make it complete and to fix any errors. Please %4$s on how you can help to complete this translation!', 'live-weather-station');
            $locale = (strpos($message, 'We need your help to make it complete') > 0 ? $this->locale_name : $this->locale_native_name);
        }
        if (!$this->translation_exists || $this->percent_translated == 0) {
            $message = /* translators: 1: language name, 2: plugin name, 4: link labelled "see details" to the translation help, 5: number of languages already available */ __('You\'re using WordPress in a language which is not supported yet by %2$s. For now, this plugin is already translated in %5$d languages and we\'d love to add %1$s to this list. Please %4$s on how you can help to achieve this goal!', 'live-weather-station');
            $locale = (strpos($message, 'you can help to achieve this goal!') > 0 ? $this->locale_name : $this->locale_native_name);
        }
        $help = InlineHelp::get(12, '%s', __('see details', 'live-weather-station'));
        if (!EnvManager::is_plugin_in_production_mode()) {
            $s = /* translators: 1: language name, 2: plugin name */ __('%2$s is using a partial translation in %1$s.', 'live-weather-station');
            $message = $s . ' ' . /* translators: 3: percentage of the plugin translated, 4: link labelled "see details" to the translation help */ __('This translation is currently %3$d%% complete. We need your help to make it complete and to fix any errors. Please %4$s on how you can help to complete this translation!', 'live-weather-station');
            $help = InlineHelp::get(-10, '%s', __('see here', 'live-weather-station'));
        }
        return sprintf($message, $locale, LIVE_WEATHER_STATION_FULL_NAME, $this->percent_translated, $help, $this->count_translated);
    }

    /**
     * Try to get translation details from cache, otherwise retrieve them, then parse them.
     *
     * @since 3.0.0
     */
    private function translation_details() {
        $set = $this->find_or_initialize_translation_details();
        $this->translation_exists = !is_null($set);
        $this->parse_translation_set($set);
    }

    /**
     * Try to find the transient for the translation set or retrieve them.
     *
     * @return object|null
     * @since 3.0.0
     */
    private function find_or_initialize_translation_details() {
        $set = Cache::get_i18n($this->locale);
        $this->count_translated = Cache::get_i18n('count');
        // A locale which has no translation set is cached too (as 'none'), so the remote platform is not asked again
        // at every request until the cache expires.
        if ($set === 'none') {
            return null;
        }
        if (!$set || !$this->count_translated) {
            $set = $this->retrieve_translation_details();
            Cache::set_i18n($this->locale, (is_null($set) ? 'none' : $set));
            Cache::set_i18n('count', $this->cpt);
        }
        return $set;
    }

    /**
     * Retrieve the translation details from WP Translate
     *
     * @return object|null
     * @since 3.0.0
     */
    private function retrieve_translation_details() {
        $branch = '/stable';
        if (!EnvManager::is_plugin_in_production_mode()) {
            $branch = '/dev';
        }
        $api_url = 'https://translate.wordpress.org/api/projects/wp-plugins/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . $branch;
        try {
            Quota::verify('WordPress.org', 'GET');
            $args = array();
            $args['user-agent'] = LIVE_WEATHER_STATION_PLUGIN_AGENT;
            $args['timeout'] = max(1, min(60, (int)get_option('live_weather_station_system_http_timeout')));
            $args['limit_response_size'] = 2097152;
            $resp = wp_remote_get($api_url, $args);
            if (is_wp_error($resp)) {
                return null;
            }
            $body = wp_remote_retrieve_body($resp);
            unset($resp);
            if ($body) {
                $body = json_decode($body);
                $this->cpt = 0;
                if (is_object($body) && isset($body->translation_sets) && is_array($body->translation_sets)) {
                    foreach ($body->translation_sets as $set) {
                        if ($set->percent_translated >= $this->percent_min) {
                            $this->cpt += 1;
                        }
                        if (!property_exists($set, 'wp_locale')) {
                            continue;
                        }
                        if ($this->locale == $set->wp_locale) {
                            return $set;
                        }
                    }
                }
            }
            return null;
        }
        catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Set the needed private variables.
     *
     * @param object $set The translation set.
     * @since 3.0.0
     */
    private function parse_translation_set($set) {
        require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        $translations = wp_get_available_translations();
        if ($this->translation_exists && is_object($set)) {
            if (array_key_exists($this->locale, $translations)) {
                $this->locale_native_name = $translations[$this->locale]['native_name'];
                $this->locale_name = substr(sanitize_text_field((string)$set->name), 0, 100);
            }
            else {
                $this->locale_native_name = substr(sanitize_text_field((string)$set->name), 0, 100);
                $this->locale_name = $this->locale_native_name;
            }
            $this->percent_translated = (int)$set->percent_translated;
            $this->locale_path = substr(sanitize_text_field((string)$set->locale), 0, 20);
            $this->last_modified = substr(sanitize_text_field((string)$set->last_modified), 0, 40);
        }
        else {
            $this->locale_native_name = (isset($translations[$this->locale]['native_name']) ? $translations[$this->locale]['native_name'] : $this->locale);
            $this->locale_name = (isset($translations[$this->locale]['language']) ? $translations[$this->locale]['language'] : $this->locale);
            $this->percent_translated = '';
            $this->locale_path = false;
            $this->last_modified = false;
        }
    }

    /**
     * Get the language markup for links.
     *
     * @param array $langs Optional. Indicates the language in which the link is available.
     * @return string The html string of the markup.
     *
     * @since 3.5.4
     */
    public static function get_language_markup($langs=array()){
        return '<span style="font-size:65%;vertical-align: super;line-height: 1em;">&nbsp;(' . implode('/', $langs) . ')</span>';
    }
}