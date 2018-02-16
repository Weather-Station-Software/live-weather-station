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
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
        $this->locale = lws_get_display_locale();
        if ('en_US' === $this->locale) {
            if (is_admin() || is_blog_admin()) {
                update_option('live_weather_station_partial_translation', 0);
            }
        }
        else {
            $this->translation_details();
            if (!$this->is_translatable() && EnvManager::is_plugin_in_production_mode()) {
                if (is_admin() || is_blog_admin()) {
                    update_option('live_weather_station_partial_translation', 0);
                }
            }
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
           return (((EnvManager::is_plugin_in_production_mode() && $this->percent_translated < $this->percent_min) || !EnvManager::is_plugin_in_production_mode()) && (lws_get_display_locale() == get_locale()));
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
        return 'https://translate.wordpress.org/projects/wp-plugins/live-weather-station/' . $branch . '/' . $this->locale_path . '/default/export-translations?format=mo';
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
        return LWS_LANGUAGES_DIR . LWS_PLUGIN_TEXT_DOMAIN . '-' . $branch . '-' . lws_get_display_locale() . '.mo';
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
        $target = LWS_LANGUAGES_DIR . LWS_PLUGIN_TEXT_DOMAIN . '-' . $branch . '-??_??.mo';
        $result = array_map('unlink', glob($target));
        $ok = true;
        if (count($result) > 0) {
            foreach ($result as $r) {
                if (!$r) {
                    $ok = false;
                    break;
                }
            }
        }
        if (!$ok) {
            Logger::error($this->service_name, null, null, null, null, null, 1, 'Unable to delete old translation files in /languages.');
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
                Logger::alert('Core', null, null, null, null, null, 666, 'Unable to use download_url function. Your server lacks of free memory or disk space, or is overloaded.');
                Logger::error($this->service_name, null, null, null, null, null, 666, $this->locale_name . ' translation file can not be downloaded from WordPress.org.');
                return false;
            }
            $file = download_url($url);
            $target .= LWS_PLUGIN_TEXT_DOMAIN . '-' . $branch . '-' . $this->locale . '.mo';
            if (is_wp_error($file)) {
                @unlink($file);
                Logger::error($this->service_name, null, null, null, null, null, 300, 'Unable to download ' . $this->locale_name . ' translation file from WordPress.org. Error was: ' . $file->get_error_messages());
                return false;
            }
            else {
                if (!copy($file, $target)) {
                    Logger::error($this->service_name, null, null, null, null, null, 1, 'Unable to copy ' . $this->locale_name . ' translation file to /languages directory.');
                    @unlink($file);
                    return false;
                }
                else {
                    Logger::notice($this->service_name, null, null, null, null, null, 0, $this->locale_name . ' translation file successfully updated from ' . $branch . ' branch.');
                    @unlink($file);
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
                if ($this->download_mo_file(LWS_LANGUAGES_DIR, $branch)) {
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
                $s = __('%2$s is using a partial translation in %1$s.', 'live-weather-station');
            }
            else {
                $s = __('There is a partial translation of %2$s in %1$s.', 'live-weather-station');
            }
            $message = $s . ' ' . __('This translation is currently %3$d%% complete. We need your help to make it complete and to fix any errors. Please %4$s on how you can help to complete this translation!', 'live-weather-station');
            $locale = (strpos($message, 'We need your help to make it complete') > 0 ? $this->locale_name : $this->locale_native_name);
        }
        if (!$this->translation_exists || $this->percent_translated == 0) {
            $message = __('You\'re using WordPress in a language which is not supported yet by %2$s. For now, this plugin is already translated in %5$d languages and we\'d love to add %1$s to this list. Please %4$s on how you can help to achieve this goal!', 'live-weather-station');
            $locale = (strpos($message, 'you can help to achieve this goal!') > 0 ? $this->locale_name : $this->locale_native_name);
        }
        $help = InlineHelp::get(12, '%s', __('see details', 'live-weather-station'));
        if (!EnvManager::is_plugin_in_production_mode()) {
            $s = __('%2$s is using a partial translation in %1$s.', 'live-weather-station');
            $message = $s . ' ' . __('This translation is currently %3$d%% complete. We need your help to make it complete and to fix any errors. Please %4$s on how you can help to complete this translation!', 'live-weather-station');
            $help = InlineHelp::get(-10, '%s', __('see here', 'live-weather-station'));
        }
        return sprintf($message, $locale, LWS_FULL_NAME, $this->percent_translated, $help, $this->count_translated);
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
        if (!$set || !$this->count_translated) {
            $set = $this->retrieve_translation_details();
            Cache::set_i18n($this->locale, $set);
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
        $api_url = 'https://translate.wordpress.org/api/projects/wp-plugins/' . LWS_PLUGIN_SLUG . $branch;
        try {
            Quota::verify('WordPress.org', 'GET');
            $args = array();
            $args['user-agent'] = LWS_PLUGIN_AGENT;
            $args['timeout'] = get_option('live_weather_station_system_http_timeout');
            $resp = wp_remote_get($api_url, $args);
            $body = wp_remote_retrieve_body($resp);
            unset($resp);
            if ($body) {
                $body = json_decode($body);
                $this->cpt = 0;
                if (isset($body)) {
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
        catch (\Exception $e) {
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
                $this->locale_name = $set->name;
            }
            else {
                $this->locale_native_name = $set->name;
                $this->locale_name = $set->name;
            }
            $this->percent_translated = $set->percent_translated;
            $this->locale_path = $set->locale;
            $this->last_modified = $set->last_modified;
        }
        else {
            $this->locale_native_name = $translations[$this->locale]['native_name'];
            $this->locale_name = $translations[$this->locale]['language'];
            $this->percent_translated = '';
            $this->locale_path = false;
            $this->last_modified = false;
        }
    }

    /**
     * Get the language id.
     *
     * @return string The id of the language.
     *
     * @since 3.3.0
     */
    public static function get_language_id()
    {
        return lws_get_display_language_id();
    }
}