<?php

namespace WeatherStation\UI\Dashboard;

use WeatherStation\System\Help\InlineHelp as RSS;
use WeatherStation\System\I18N\Handling as I18N;
use WeatherStation\System\Analytics\Performance;

/**
 * This class builds elements of the dashboard.
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
    private $action;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $dashboard The dashboard screen.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version, $dashboard) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        $this->screen = $dashboard;
        $this->action = null;
        if (!($this->action = filter_input(INPUT_GET, 'action'))) {
            $this->action = filter_input(INPUT_POST, 'action');
        }
        add_action('load-' . $dashboard, array($this, 'dashboard_add_options'));
        add_action('admin_footer-' . $dashboard, array($this, 'dashboard_add_footer'));
        if (!isset($this->action) || (isset($this->action) && ($this->action != 'configuration' && $this->action != 'changelog'))) {
            add_filter('screen_settings', array($this, 'append_screen_settings'), 10, 2);
        }
    }

    /**
     * Add options.
     *
     * @since 3.0.0
     */
    public function dashboard_add_options() {
        $this->add_metaboxes();
    }

    /**
     * Add footer scripts.
     *
     * @since 3.0.0
     */
    public function dashboard_add_footer() {
        $result = '';
        $result .= '<script type="text/javascript">';
        $result .= "    jQuery(document).ready( function($) {";
        $result .= "        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');";
        $result .= "        if(typeof postboxes !== 'undefined')";
        $result .= "            postboxes.add_postbox_toggles('lws-dashboard');";
        $result .= "    });";
        $result .= '</script>';
        echo $result;
    }

    /**
     * Append custom panel HTML to the "Screen Options" box of the current page.
     * Callback for the 'screen_settings' filter.
     *
     * @param string $current Current content.
     * @param \WP_Screen $screen Screen object.
     * @return string The HTML code to append to "Screen Options"
     */
    public function append_screen_settings($current, $screen){
        if (!isset($screen->id)) {
            return $current;
        }
        $s = convert_to_screen($this->screen);
        if ($screen->id !== $s->id) {
            return $current;
        }
        $current .= '<div id="lws_dashboard" class="metabox-prefs custom-options-panel requires-autosave"><input type="hidden" name="_wpnonce-lws_dashboard" value="' . wp_create_nonce('save_settings_lws_dashboard') . '" />';
        $current .= $this->get_options();
        $current .= '</div>';
        return $current ;
    }

    /**
     * Get the box options.
     *
     * @return string The HTML code to append.
     * @since 3.0.0
     */
    public function get_options() {
        $result = '<fieldset class="metabox-prefs">';
        $result .= '<legend>' . __('Boxes', 'live-weather-station') . '</legend>';
        $result .= $this->meta_box_prefs('lws-dashboard');
        if (isset($_GET['welcome'])) {
            $welcome_checked = (empty($_GET['welcome']) ? 0 : 1);
            update_user_meta(get_current_user_id(), 'show_lws_welcome_panel', $welcome_checked);
        }
        else {
            if (!metadata_exists('user', get_current_user_id(), 'show_lws_welcome_panel')) {
                update_user_meta(get_current_user_id(), 'show_lws_welcome_panel', true);
            }
            $welcome_checked = get_user_meta(get_current_user_id(), 'show_lws_welcome_panel', true);
            $result .= '<label for="lws_welcome_panel-hide">';
            $result .= '<input class="hide-postbox-tog" type="checkbox" value="welcome_panel" name="lws_welcome_panel-hide" id="lws_welcome_panel-hide"' . checked((bool)$welcome_checked, true, false ) . ' />';
            $result .= __('Welcome', 'live-weather-station') . '</label>';
        }
        $result .= '</fieldset>';
        return $result;
    }

    /**
     * Prints the meta box preferences for dashboard screen meta.
     *
     * @param string|\WP_Screen $screen Screen object or name.
     * @return string The HTML code to append.
     * @since 3.0.0
     */
    public function meta_box_prefs($screen) {
        global $wp_meta_boxes;
        $result = '';
        if (is_string($screen)) {
            $screen = convert_to_screen($screen);
        }
        if (empty($wp_meta_boxes[$screen->id])) {
            return '';
        }
        $hidden = get_hidden_meta_boxes($screen);
        foreach (array_keys($wp_meta_boxes[$screen->id]) as $context) {
            foreach (array('high', 'core', 'default', 'low') as $priority) {
                if (!isset( $wp_meta_boxes[$screen->id][$context][$priority])) {
                    continue;
                }
                foreach ($wp_meta_boxes[$screen->id][$context][$priority] as $box) {
                    if (false == $box || ! $box['title']) {
                        continue;
                    }
                    if ('submitdiv' == $box['id'] || 'linksubmitdiv' == $box['id']) {
                        continue;
                    }
                    $box_id = $box['id'];
                    $result .= '<label for="' . $box_id . '-hide">';
                    $result .= '<input class="hide-postbox-tog" name="' . $box_id . '-hide" type="checkbox" id="' . $box_id . '-hide" value="' . $box_id . '"' . (!in_array($box_id, $hidden) ? ' checked="checked"' : '') . ' />';
                    $result .= $box['title'] . '</label>';
                }
            }
        }
        return $result;
    }

    /**
     * Ajax handler for updating whether to display the welcome panel.
     *
     * @since 3.0.0
     */
    public static function update_lws_welcome_panel_callback() {
        check_ajax_referer('lws-welcome-panel-nonce', 'lwswelcomepanelnonce');
        update_user_meta(get_current_user_id(), 'show_lws_welcome_panel', empty($_POST['visible'] ) ? 0 : 1);
        wp_die(1);
    }

    /**
     * Get the full dashboard.
     *
     * @since 3.0.0
     **/
    public function get() {
        echo '<div class="wrap">';
        echo '<h1>' . sprintf(__('%s Dashboard', 'live-weather-station'), LWS_PLUGIN_NAME) . '</h1>';
        settings_errors();
        echo '<form name="lws_dashboard" method="post">';
        $this->welcome_panel();
        echo '<div id="dashboard-widgets-wrap">';
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
        echo '    <div id="dashboard-widgets" class="metabox-holder">';
        echo '        <div id="postbox-container-1" class="postbox-container">';
        do_meta_boxes('lws-dashboard','normal',null);
        echo '        </div>';
        echo '        <div id="postbox-container-2" class="postbox-container">';
        do_meta_boxes('lws-dashboard','side',null);
        echo '        </div>';
        echo '        <div id="postbox-container-3" class="postbox-container">';
        do_meta_boxes('lws-dashboard','column3',null);
        echo '        </div>';
        echo '        <div id="postbox-container-4" class="postbox-container">';
        do_meta_boxes('lws-dashboard','column4',null);
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.0.0
     */
    public function add_metaboxes() {
        // Left column
        add_meta_box('lws-summary', __('At a Glance', 'live-weather-station'), array($this, 'summary_widget'), 'lws-dashboard', 'normal');
        if ((bool)get_option('live_weather_station_netatmo_connected') ||
            (bool)get_option('live_weather_station_netatmohc_connected') ||
            get_option('live_weather_station_owm_apikey') != '' ||
            get_option('live_weather_station_wug_apikey') != '') {
            add_meta_box('lws-perf-quota', __('Quota usage', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_quota_widget'), 'lws-dashboard', 'normal');
        }
        if ((bool)get_option('live_weather_station_frontend_cache') ||
            (bool)get_option('live_weather_station_widget_cache') ||
            (bool)get_option('live_weather_station_backend_cache')) {
            add_meta_box('lws-perf-cache', __('Cache performance', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_cache_widget'), 'lws-dashboard', 'normal');
        }
        add_meta_box('lws-perf-event24', __('Events', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_event_widget_24'), 'lws-dashboard', 'normal');
        add_meta_box('lws-version', __('Versions', 'live-weather-station'), array($this, 'version_widget'), 'lws-dashboard', 'normal');
        // Right column
        $intl = new I18N();
        if ($intl->is_translatable()) {
            add_meta_box('lws-translation', __('Translation', 'live-weather-station'), array($this, 'translation_widget'), 'lws-dashboard', 'side', 'high', array('message' => $intl->get_message()));
        }
        add_meta_box('lws-news', sprintf(__('%s News', 'live-weather-station'), LWS_PLUGIN_NAME), array($this, 'news_widget'), 'lws-dashboard', 'side');
        add_meta_box('lws-signup', sprintf(__('Subscribe', 'live-weather-station'), LWS_PLUGIN_NAME), array($this, 'signup_widget'), 'lws-dashboard', 'side');
        add_meta_box('lws-about', __('About', 'live-weather-station'), array($this, 'about_widget'), 'lws-dashboard', 'side');
        add_meta_box('lws-licenses', __('Licenses', 'live-weather-station'), array($this, 'licenses_widget'), 'lws-dashboard', 'side');
    }

    /**
     * Callback for add_action('wp_dashboard_setup'...).
     *
     * @since 3.0.0
     */
    public static function add_wp_dashboard_widget() {
        wp_add_dashboard_widget('lws_dashboard_widget', LWS_FULL_NAME, array(get_called_class(), '_summary_widget'));
    }

    /**
     * Get content of the main dashboard widget and "At a Glance" box.
     *
     * @since 3.0.0
     */
    public static function _summary_widget() {
        include(LWS_ADMIN_DIR.'partials/DashboardSummary.php');
    }

    /**
     * Get content of the main dashboard widget and "At a Glance" box.
     *
     * @since 3.0.0
     */
    public function summary_widget() {
        self::_summary_widget();
    }

    /**
     * Get content of the Quota Usage box.
     *
     * @since 3.2.0
     */
    public function perf_quota_widget() {
        $val = Performance::get_quota_values()['agr24'];
        $show_link = true;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceQuota.php');
    }

    /**
     * Get content of the Cache Performance box.
     *
     * @since 3.1.0
     */
    public function perf_cache_widget() {
        $val = Performance::get_cache_values()['agr24'];
        $show_link = true;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceCache.php');
    }

    /**
     * Get content of the events performance box.
     *
     * @since 3.2.0
     */
    public function perf_event_widget_24() {
        $val = Performance::get_event_values()['agr24'];
        $show_link = true;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceEvent.php');
    }

    /**
     * Get content of the versions box.
     *
     * @since 3.0.0
     */
    public function version_widget() {
        include(LWS_ADMIN_DIR.'partials/DashboardVersions.php');
    }

    /**
     * Get content of the news box.
     *
     * @since 3.0.0
     */
    public function news_widget() {
        $url = RSS::get(-4);
        include(LWS_ADMIN_DIR.'partials/DashboardNews.php');
    }

    /**
     * Get content of the news box.
     *
     * @since 3.0.0
     */
    public function signup_widget() {
        include(LWS_ADMIN_DIR.'partials/DashboardSignup.php');
    }

    /**
     * Get content of the translation box.
     *
     * @since 3.0.0
     */
    public function translation_widget($n, $args) {
        $message = '';
        if (array_key_exists('message', $args['args'])) {
            $message = $args['args']['message'];
        }
        include(LWS_ADMIN_DIR.'partials/DashboardTranslation.php');
    }

    /**
     * Get content of the about box.
     *
     * @since 3.0.0
     */
    public function about_widget() {
        include(LWS_ADMIN_DIR.'partials/DashboardAbout.php');
    }

    /**
     * Get content of the licenses box.
     *
     * @since 3.0.0
     */
    public function licenses_widget() {
        include(LWS_ADMIN_DIR.'partials/DashboardLicenses.php');
    }

    /**
     * Get the welcome panel of the dashboard.
     *
     * @since 3.0.0
     */
    private function welcome_panel() {
        include(LWS_ADMIN_DIR.'partials/DashboardWelcome.php');
    }
}