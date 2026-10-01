<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Logs\Logger;

/**
 * This class is responsible of history cleaning.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

class Stats
{

    private $Live_Weather_Station;
    private $version;
    private $facility = 'Core';
    private $service = 'WordPress.org';


    /**
     * Initialize the class and set its properties.
     *
     * @since 3.4.0
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Cron to execute four times a day to execute the cleaning.
     *
     * @since 3.4.0
     */
    public function cron() {
        $cron_id = Watchdog::init_chrono(Watchdog::$plugin_stat_name);
        $this->__statistics();
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Main process of statistics collector.
     *
     * @since 3.4.0
     */
    private function __statistics() {
        if ((bool)get_option('live_weather_station_plugin_stat')) {
            $this->get_misc_stat();
            $this->get_translation_stat();
        }
    }

    /**
     * Collect plugin misc stats from WP.org.
     *
     * @since 3.4.0
     */
    private function get_misc_stat() {
        try {
            if (!function_exists('plugins_api')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
            }
            Quota::verify($this->service, 'GET');
            $query = array( 'active_installs' => true,
                            'downloaded' => true,
                            'rating' => true,
                            'num_ratings' => true);
            $api = plugins_api( 'plugin_information', array('slug' => LIVE_WEATHER_STATION_PLUGIN_SLUG, 'fields' => $query));
            if (!is_wp_error($api)) {
                $result = get_object_vars($api);
                $result['timestamp'] = time();
                update_option('live_weather_station_misc_stat', $result);
            }
            else {
                Logger::warning('Core', null, null, null, null, null, 1, 'Plugin statistics can not be downloaded from WordPress.org.');
            }
        }
        catch(\Exception $ex) {
            Logger::warning('Core', null, null, null, null, null, 2, 'Plugin statistics can not be downloaded from WordPress.org.');
        }
    }

    /**
     * Retrieve the translation details from from WP.org.
     *
     * @since 3.4.0
     */
    private function get_translation_stat() {
        $api_url = 'https://translate.wordpress.org/api/projects/wp-plugins/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/stable';
        try {
            Quota::verify($this->service, 'GET');
            $args = array();
            $args['user-agent'] = LIVE_WEATHER_STATION_PLUGIN_AGENT;
            $args['timeout'] = get_option('live_weather_station_system_http_timeout');
            $resp = wp_remote_get($api_url, $args);
            $body = '';
            if (!is_wp_error($resp) && (int)wp_remote_retrieve_response_code($resp) === 200) {
                $body = wp_remote_retrieve_body($resp);
            }
            unset($resp);
            if ($body) {
                $body = json_decode($body);
                if (isset($body) && (is_object($body) || is_array($body))) {
                    $result = live_weather_station_object_to_array($body);
                    // Only the fields used to display the statistics are kept, with their expected types.
                    $clean = array();
                    $clean['name'] = isset($result['name']) && is_scalar($result['name']) ? sanitize_text_field((string)$result['name']) : '';
                    $clean['slug'] = isset($result['slug']) && is_scalar($result['slug']) ? sanitize_key((string)$result['slug']) : '';
                    $clean['translation_sets'] = array();
                    if (isset($result['translation_sets']) && is_array($result['translation_sets'])) {
                        foreach (array_slice($result['translation_sets'], 0, 500) as $set) {
                            if (!is_array($set)) {
                                continue;
                            }
                            $item = array();
                            foreach ($set as $k => $v) {
                                if (is_string($k) && is_scalar($v)) {
                                    $item[sanitize_key($k)] = is_string($v) ? sanitize_text_field($v) : $v;
                                }
                            }
                            $clean['translation_sets'][] = $item;
                        }
                    }
                    $clean['timestamp'] = time();
                    update_option('live_weather_station_translation_stat', $clean);
                }
            }
            else {
                Logger::warning('Core', null, null, null, null, null, 1, 'Plugin translations statistics can not be downloaded from WordPress.org.');
            }
        }
        catch (\Exception $e) {
            Logger::warning('Core', null, null, null, null, null, 2, 'Plugin translations statistics can not be downloaded from WordPress.org.');
        }
    }
}