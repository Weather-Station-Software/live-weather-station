<?php

namespace WeatherStation\System\I18N;

use WeatherStation\System\Help\InlineHelp;

/**
 * This class add i18n management.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Handling {

    private $locale_name;
    private $percent_translated;
    private $count_translated;
    private $translation_exists;
    private $translation_loaded;
    private $percent_min = 96;
    private $cpt;

    /**
     * Class constructor
     *
     * @since 3.0.0
     */
    public function __construct() {
        $this->locale = get_locale();
        if ('en_US' === $this->locale ) {
            return;
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
           $this->translation_details();
           return ($this->percent_translated < $this->percent_min);
       }
    }

    /**
     * Get the message to display.
     *
     * @return bool|string The message.
     * @since 3.0.0
     */
    public function get_message() {
        $this->translation_details();
        $message = false;
        if ($this->translation_exists && $this->translation_loaded && $this->percent_translated < $this->percent_min) {
            $message = 'As you can see, there is a partial translation of %2$s in %1$s. This translation is currently %3$d%% complete. We need your help to make it complete and to fix any errors. Please %4$s on how you can help to complete this %1$s translation!';
        }
        if (!$this->translation_loaded && $this->translation_exists) {
            $message = 'You\'re using WordPress in %1$s. While %2$s has been translated to %1$s for %3$d%%, it\'s not been shipped with the plugin yet. But, you can help! Please %4$s on how you can participate to this %1$s translation!';
        }
        if (!$this->translation_exists) {
            $message = 'You\'re using WordPress in a language which is not supported yet by %2$s. For now, this plugin is already translated in %5$d languages and we\'d love to add %1$s to this list. Please %4$s on how you can help to achieve this goal!';
        }
        $help = InlineHelp::get(12, '%s', 'see details');
        if ((strpos(LWS_VERSION, 'dev') > 0) || (strpos(LWS_VERSION, 'rc') > 0)) {
            $help = InlineHelp::get(-10, '%s', 'see here');
        }
        return sprintf($message, $this->locale_name, LWS_FULL_NAME, $this->percent_translated, $help, $this->count_translated);
    }

    /**
     * Try to get translation details from cache, otherwise retrieve them, then parse them.
     *
     * @since 3.0.0
     */
    private function translation_details() {
        $set = $this->find_or_initialize_translation_details();
        $this->translation_exists = !is_null($set);
        $this->translation_loaded = is_textdomain_loaded(LWS_PLUGIN_TEXT_DOMAIN);
        $this->parse_translation_set($set);
    }

    /**
     * Try to find the transient for the translation set or retrieve them.
     *
     * @return object|null
     * @since 3.0.0
     */
    private function find_or_initialize_translation_details() {
        $set = get_transient('lws_i18n_' . $this->locale);
        $this->count_translated = get_transient('lws_i18n_count');
        if (!$set || !$this->count_translated) {
            $set = $this->retrieve_translation_details();
            set_transient('lws_i18n_' . $this->locale, $set, DAY_IN_SECONDS / 4);
            set_transient('lws_i18n_count', $this->cpt, DAY_IN_SECONDS / 4);
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
        if ((strpos(LWS_VERSION, 'dev') > 0) || (strpos(LWS_VERSION, 'rc') > 0)) {
            $branch = '/dev';
        }
        $api_url = 'https://translate.wordpress.org/api/projects/wp-plugins/' . LWS_PLUGIN_SLUG . $branch;
        $resp = wp_remote_get($api_url);
        $body = wp_remote_retrieve_body($resp);
        unset($resp);
        if ($body) {
            $body = json_decode($body);
            $this->cpt = 0;
            foreach ($body->translation_sets as $set) {
                if ($set->percent_translated >= $this->percent_min) {
                    $this->cpt += 1;
                }
                if (!property_exists($set,'wp_locale')) {
                    continue;
                }
                if ($this->locale == $set->wp_locale) {
                    return $set;
                }
            }
        }
        return null;
    }

    /**
     * Set the needed private variables.
     *
     * @param object $set The translation set.
     * @since 3.0.0
     */
    private function parse_translation_set($set) {
        if ($this->translation_exists && is_object($set)) {
            $this->locale_name = $set->name;
            $this->percent_translated = $set->percent_translated;
        }
        else {
            $this->locale_name = '';
            $this->percent_translated = '';
        }
    }
}