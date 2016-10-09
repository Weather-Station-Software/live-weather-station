<?php

namespace WeatherStation\System\Plugin;
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

use WeatherStation\System\I18N\Handling as Intl;

class I18n {

	private $domain;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain($this->domain, false, false);
	}

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
     * Override the mo file for the domain.
     *
     * @param string $override The override.
     * @param string $domain The domain that represents the locale of this plugin.
     * @return string The mo file to load.
     * @since 3.0.0
     */
	public function load_local_textdomain_mofile($override, $domain) {
        if (LWS_PLUGIN_TEXT_DOMAIN == $domain && (bool)get_option('live_weather_station_partial_translation')) {
            remove_filter('override_load_textdomain', array($this, 'load_local_textdomain_mofile'));
            $file = Intl::get_current_mo_file();
            if (!file_exists($file)) {
                $i18n = new Intl();
                $i18n->cron_run();
            }
            return load_textdomain($domain, $file);
        }
        return $override;
    }

}
