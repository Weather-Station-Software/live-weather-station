<?php

namespace WeatherStation\UI\Analytics;

use WeatherStation\System\Analytics\Performance;

/**
 * This class builds elements of general tab for analytics page.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
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
     * @param string $analytics The analytics page.
     * @since 3.1.0
     */
    public function __construct($Live_Weather_Station, $version, $analytics) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        $this->screen = $analytics;
        add_action('load-' . $analytics, array($this, 'analytics_add_options'));
        add_action('admin_footer-' . $analytics, array($this, 'analytics_add_footer'));
    }

    /**
     * Add options.
     *
     * @since 3.1.0
     */
    public function analytics_add_options() {
        self::add_metaboxes();
    }

    /**
     * Add footer scripts.
     *
     * @since 3.1.0
     */
    public function analytics_add_footer() {
        $result = '';
        $result .= '<script type="text/javascript">';
        $result .= "    jQuery(document).ready( function($) {";
        $result .= "        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');";
        $result .= "        if(typeof postboxes !== 'undefined')";
        $result .= "            postboxes.add_postbox_toggles('lws-analytics');";
        $result .= "    });";
        $result .= '</script>';
        echo $result;
    }

    /**
     * Get the full content of general tab (in analytics page).
     *
     * @since 3.1.0
     **/
    public function get() {
        echo '<form name="lws_analytics" method="post">';
        echo '<div id="analytics-widgets-wrap">';
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
        echo '    <div id="dashboard-widgets" class="metabox-holder">';
        echo '        <div id="postbox-container-1" class="postbox-container">';
        do_meta_boxes('lws-analytics','normal',null);
        echo '        </div>';
        echo '        <div id="postbox-container-2" class="postbox-container">';
        do_meta_boxes('lws-analytics','side',null);
        echo '        </div>';
        echo '        <div id="postbox-container-3" class="postbox-container">';
        do_meta_boxes('lws-analytics','column3',null);
        echo '        </div>';
        echo '        <div id="postbox-container-4" class="postbox-container">';
        do_meta_boxes('lws-analytics','column4',null);
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
        echo '</form>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.1.0
     */
    public function add_metaboxes() {
        // Left column
        if ((bool)get_option('live_weather_station_netatmo_connected') ||
            (bool)get_option('live_weather_station_netatmohc_connected') ||
            get_option('live_weather_station_owm_apikey') != '' ||
            get_option('live_weather_station_wug_apikey') != '') {
            add_meta_box('lws-perf-quota_24', __('Quota usage', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_quota_widget_24'), 'lws-analytics', 'normal');
        }
        if ((bool)get_option('live_weather_station_frontend_cache') ||
            (bool)get_option('live_weather_station_widget_cache') ||
            (bool)get_option('live_weather_station_dgraph_cache') ||
            (bool)get_option('live_weather_station_ygraph_cache') ||
            (bool)get_option('live_weather_station_backend_cache')) {
            add_meta_box('lws-perf-cache24', __('Cache performance', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_cache_widget_24'), 'lws-analytics', 'normal');
        }
        add_meta_box('lws-perf-cron24', __('Tasks', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_cron_widget_24'), 'lws-analytics', 'normal');
        add_meta_box('lws-perf-event24', __('Events', 'live-weather-station') . ' - ' . __('24 hours', 'live-weather-station'), array($this, 'perf_event_widget_24'), 'lws-analytics', 'normal');
        // Right column
        if ((bool)get_option('live_weather_station_netatmo_connected') ||
            (bool)get_option('live_weather_station_netatmohc_connected') ||
            get_option('live_weather_station_owm_apikey') != '' ||
            get_option('live_weather_station_wug_apikey') != '') {
            add_meta_box('lws-perf-quota_30', __('Quota usage', 'live-weather-station') . ' - ' . __('30 days', 'live-weather-station'), array($this, 'perf_quota_widget_30'), 'lws-analytics', 'side');
        }
        if ((bool)get_option('live_weather_station_frontend_cache') ||
            (bool)get_option('live_weather_station_widget_cache') ||
            (bool)get_option('live_weather_station_dgraph_cache') ||
            (bool)get_option('live_weather_station_ygraph_cache') ||
            (bool)get_option('live_weather_station_backend_cache')) {
            add_meta_box('lws-perf-cache30', __('Cache performance', 'live-weather-station') . ' - ' . __('30 days', 'live-weather-station'), array($this, 'perf_cache_widget_30'), 'lws-analytics', 'side');
        }
        add_meta_box('lws-perf-cron30', __('Tasks', 'live-weather-station') . ' - ' . __('30 days', 'live-weather-station'), array($this, 'perf_cron_widget_30'), 'lws-analytics', 'side');
        add_meta_box('lws-perf-event30', __('Events', 'live-weather-station') . ' - ' . __('30 days', 'live-weather-station'), array($this, 'perf_event_widget_30'), 'lws-analytics', 'side');
    }

    /**
     * Get content of the Quota Usage box.
     *
     * @since 3.2.0
     */
    public function perf_quota_widget_24() {
        $val = Performance::get_quota_values()['agr24'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceQuota.php');
    }

    /**
     * Get content of the Quota Usage box.
     *
     * @since 3.2.0
     */
    public function perf_quota_widget_30() {
        $val = Performance::get_quota_values()['agr30'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceQuota.php');
    }

    /**
     * Get content of the Cache Performance box.
     *
     * @since 3.1.0
     */
    public function perf_cache_widget_24() {
        $val = Performance::get_cache_values()['agr24'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceCache.php');
    }

    /**
     * Get content of the Cache Performance box.
     *
     * @since 3.1.0
     */
    public function perf_cache_widget_30() {
        $val = Performance::get_cache_values()['agr30'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceCache.php');
    }

    /**
     * Get content of the Cron Performance box.
     *
     * @since 3.1.0
     */
    public function perf_cron_widget_24() {
        $val = Performance::get_cron_values()['agr24'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceCron.php');
    }

    /**
     * Get content of the Cron Performance box.
     *
     * @since 3.1.0
     */
    public function perf_cron_widget_30() {
        $val = Performance::get_cron_values()['agr30'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceCron.php');
    }

    /**
     * Get content of the events performance box.
     *
     * @since 3.2.0
     */
    public function perf_event_widget_24() {
        $val = Performance::get_event_values()['agr24'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceEvent.php');
    }

    /**
     * Get content of the events performance box.
     *
     * @since 3.2.0
     */
    public function perf_event_widget_30() {
        $val = Performance::get_event_values()['agr30'];
        $show_link = false;
        include(LWS_ADMIN_DIR.'partials/DashboardPerformanceEvent.php');
    }
}