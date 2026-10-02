<?php

namespace WeatherStation\System\Plugin;
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

use WeatherStation\System\I18N\Handling as Intl;

class I18n {

	private $domain;

	/**
	 * Set the domain equal to that of the specified domain.
	 *
     * @param string $domain The domain that represents the locale of this plugin.
	 * @since 1.0.0
	 */
	public function set_domain($domain) {
		$this->domain = $domain;
	}

    /**
     * Complete the translation with the file shipped in the plugin, for the strings the language pack is missing.
     *
     * The pack of WordPress.org (or the partial translation) is loaded first, as usual: when a file is loaded for a
     * domain which already has translations, WordPress keeps the strings of the first and adds the missing ones of
     * the second. So the strings added by a release are translated before the community has translated them.
     *
     * @since 3.9.0
     */
    public function load_shipped_translation() {
        $domain = LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN;
        $locale = determine_locale();
        $file = LIVE_WEATHER_STATION_PLUGIN_DIR . 'languages/' . $domain . '-' . $locale . '.mo';
        if (!is_readable($file)) {
            return;
        }
        // Forces the normal loading of the domain (language pack, partial translation) before ours.
        __('Weather Station', 'live-weather-station');
        load_textdomain($domain, $file, $locale);
    }

    /**
     * Override the mo file for the domain.
     *
     * @param string $override The override.
     * @param string $domain The domain that represents the locale of this plugin.
     * @return string The mo file to load.
     * @since 3.0.0
     */
	public function load_local_textdomain_mofile($override, $domain) {
        if (LIVE_WEATHER_STATION_PLUGIN_TEXT_DOMAIN == $domain && (bool)get_option('live_weather_station_partial_translation')) {
            remove_filter('override_load_textdomain', array($this, 'load_local_textdomain_mofile'));
            $file = Intl::get_current_mo_file();
            if (!file_exists($file)) {
                $i18n = new Intl();
                $i18n->cron_run();
            }
            //error_log('USE PARTIAL TRANSLATION');
            return load_textdomain($domain, $file);
        }
        else {
            //error_log('DONT USE PARTIAL TRANSLATION');
        }
        return $override;
    }

}
