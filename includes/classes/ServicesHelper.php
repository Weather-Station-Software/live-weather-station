<?php

namespace WeatherStation\UI\Services;

/**
 * This class builds elements of services tab for settings page.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

class Handling {

    private $Live_Weather_Station;
    private $version;
    private $screen;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $settings The settings page.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version, $settings) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        $this->screen = $settings;
        add_action('load-' . $settings, array($this, 'settings_add_options'));
        add_action('admin_footer-' . $settings, array($this, 'settings_add_footer'));
    }

    /**
     * Add options.
     *
     * @since 3.0.0
     */
    public function settings_add_options() {
        self::add_metaboxes();
    }

    /**
     * Add footer scripts.
     *
     * @since 3.0.0
     */
    public function settings_add_footer() {
        $result = '';
        $result .= '<script type="text/javascript">';
        $result .= "    jQuery(document).ready( function($) {";
        $result .= "        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');";
        $result .= "        if(typeof postboxes !== 'undefined')";
        $result .= "            postboxes.add_postbox_toggles('lws-settings');";
        $result .= "    });";
        $result .= '</script>';
        echo $result;
    }

    /**
     * Get the full content of services tab (in settings page).
     *
     * @since 3.0.0
     **/
    public function get() {
        echo '<form name="lws_services" method="post">';
        echo '<div id="services-widgets-wrap">';
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
        echo '    <div id="dashboard-widgets" class="metabox-holder">';
        echo '        <div id="postbox-container-1" class="postbox-container">';
        do_meta_boxes('lws-settings','normal',null);
        echo '        </div>';
        echo '        <div id="postbox-container-2" class="postbox-container">';
        do_meta_boxes('lws-settings','side',null);
        echo '        </div>';
        echo '        <div id="postbox-container-3" class="postbox-container">';
        do_meta_boxes('lws-settings','column3',null);
        echo '        </div>';
        echo '        <div id="postbox-container-4" class="postbox-container">';
        do_meta_boxes('lws-settings','column4',null);
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
        echo '</form>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.0.0
     */
    public function add_metaboxes() {
        // Left column
        add_meta_box('lws-connect-netatmo', 'Netatmo', array($this, 'netatmo_box'), 'lws-settings', 'normal');
        add_meta_box('lws-connect-netatmohc', 'Netatmo (Healthy Home Coach)', array($this, 'netatmohc_box'), 'lws-settings', 'normal');
        // Right column
        add_meta_box('lws-connect-owm', 'OpenWeatherMap', array($this, 'owm_box'), 'lws-settings', 'side');
        add_meta_box('lws-connect-wug', 'Weather Underground', array($this, 'wug_box'), 'lws-settings', 'side');
    }

    /**
     * Get content of the Netatmo box.
     *
     * @since 3.0.0
     */
    public function netatmo_box() {
        include(LWS_ADMIN_DIR.'partials/ConnectNetatmo.php');
    }

    /**
     * Get content of the NetatmoHC box.
     *
     * @since 3.1.0
     */
    public function netatmohc_box() {
        include(LWS_ADMIN_DIR.'partials/ConnectNetatmoHC.php');
    }

    /**
     * Get content of the OpenWeatherMap box.
     *
     * @since 3.0.0
     */
    public function owm_box() {
        include(LWS_ADMIN_DIR.'partials/ConnectOpenWeatherMap.php');
    }

    /**
     * Get content of the WeatherUnderground box.
     *
     * @since 3.0.0
     */
    public function wug_box() {
        include(LWS_ADMIN_DIR.'partials/ConnectWeatherUnderground.php');
    }
}