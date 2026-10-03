<?php

namespace WeatherStation\System\Plugin;

/**
 * A "Weather Station" section in the Site Health screen of WordPress (Tools, Site Health, Info).
 *
 * It gives the information which a support request needs, and WordPress' own button "Copy site info to clipboard" puts it in
 * a report to paste in an issue. Nothing is sent anywhere. The section holds no name, identifier, address, key, token or
 * password: only counts, dates, switches and settings.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.9.0
 */
class SiteHealth {

    /**
     * The names of the types of station.
     *
     * @var array
     * @since 3.9.0
     */
    private static $station_types = array(
        0 => 'Netatmo', 1 => 'Location', 2 => 'OpenWeatherMap (no longer available)', 3 => 'Weather Underground (no longer available)',
        4 => 'Clientraw', 5 => 'Realtime', 6 => 'Netatmo Healthy Home Coach', 7 => 'Stickertags', 8 => 'WeatherFlow', 9 => 'Pioupiou',
        10 => 'BloomSky (no longer available)', 11 => 'Ambient Weather', 12 => 'WeatherLink',
    );

    /**
     * Add the section to the debug information of WordPress.
     *
     * @param array $info The debug information.
     * @return array The debug information with the section of the plugin.
     * @since 3.9.0
     */
    public static function add_section($info) {
        if (!is_array($info)) {
            return $info;
        }
        $info['live-weather-station'] = array(
            'label' => LIVE_WEATHER_STATION_PLUGIN_NAME,
            'description' => __('Information about the plugin for a support request: counts, dates and settings only, no name, identifier, address or credential.', 'live-weather-station'),
            'fields' => self::fields(),
        );
        return $info;
    }

    /**
     * Add the test of the late collection to the Site Health tests.
     *
     * @param array $tests The tests of Site Health.
     * @return array The tests with the one of the plugin.
     * @since 3.9.0
     */
    public static function add_test($tests) {
        if (is_array($tests)) {
            $tests['direct']['lws_collection'] = array(
                'label' => __('Weather Station data are up to date', 'live-weather-station'),
                'test' => array(__CLASS__, 'test_collection'),
            );
        }
        return $tests;
    }

    /**
     * Test of Site Health: has a station been refreshed recently?
     *
     * A station is late when it has not been refreshed for more than the number of minutes set in the settings (0 turns the test off).
     * Stations of a service which is no longer available, and stations which have never been refreshed, are not counted.
     *
     * @return array The result of the test.
     * @since 3.9.0
     */
    public static function test_collection() {
        global $wpdb;
        $limit = (int)get_option('live_weather_station_late_collection_minutes', 60);
        $result = array(
            'label' => __('Weather Station data are up to date', 'live-weather-station'),
            'status' => 'good',
            'badge' => array('label' => LIVE_WEATHER_STATION_PLUGIN_NAME, 'color' => 'blue'),
            'description' => '<p>' . esc_html__('Every station has been refreshed recently.', 'live-weather-station') . '</p>',
            'actions' => '',
            'test' => 'lws_collection',
        );
        if ($limit < 1) {
            $result['description'] = '<p>' . esc_html__('The warning about late data is turned off in the settings of the plugin.', 'live-weather-station') . '</p>';
            return $result;
        }
        $table = $wpdb->prefix . 'live_weather_station_stations';
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table, the name comes from the plugin itself.
        $rows = $wpdb->get_results("SELECT station_name, station_type, last_refresh FROM " . $table, ARRAY_A);
        $late = array();
        foreach ((array)$rows as $row) {
            if (in_array((int)$row['station_type'], array(2, 3, 10), true)) {
                continue;
            }
            $refresh = (string)$row['last_refresh'];
            if ($refresh === '' || strpos($refresh, '0000') === 0) {
                continue;
            }
            $time = strtotime($refresh . ' UTC');
            if ($time !== false && $time < time() - $limit * MINUTE_IN_SECONDS) {
                $late[] = (string)$row['station_name'];
            }
        }
        if (count($late) === 0) {
            return $result;
        }
        $shown = array_map('esc_html', array_slice($late, 0, 5));
        $names = implode(', ', $shown) . (count($late) > 5 ? '…' : '');
        $result['status'] = 'recommended';
        $result['label'] = __('Weather Station data are late', 'live-weather-station');
        $result['badge']['color'] = 'orange';
        $description = '<p>' . sprintf(
            /* translators: 1: number of stations, 2: number of minutes, 3: names of the stations */
            _n('%1$d station has not been refreshed for more than %2$d minutes: %3$s.', '%1$d stations have not been refreshed for more than %2$d minutes: %3$s.', count($late), 'live-weather-station'),
            count($late), $limit, $names) . '</p>';
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            $description .= '<p>' . esc_html__('WP-Cron is disabled by the configuration of this site: a task of your server must call wp-cron.php regularly, otherwise the plugin cannot collect data.', 'live-weather-station') . '</p>';
        }
        $result['description'] = $description;
        $result['actions'] = '<p><a href="' . esc_url(admin_url('admin.php?page=lws-scheduler&tab=tasks')) . '">' . esc_html__('Open the scheduled tasks of Weather Station', 'live-weather-station') . '</a></p>';
        return $result;
    }

    /**
     * Build a field of the section.
     *
     * @param string $label The label.
     * @param mixed $value The value.
     * @return array The field.
     * @since 3.9.0
     */
    private static function field($label, $value) {
        if (is_bool($value)) {
            $value = $value ? __('Yes', 'live-weather-station') : __('No', 'live-weather-station');
        }
        return array('label' => $label, 'value' => (string)$value, 'debug' => (string)$value);
    }

    /**
     * Yes if the option holds a non empty value (the value itself is never shown).
     *
     * @param string $option The name of the option.
     * @return bool True if it is set.
     * @since 3.9.0
     */
    private static function is_set($option) {
        $value = get_option($option);
        return !($value === false || $value === '' || $value === '0' || $value === 0 || $value === null);
    }

    /**
     * The fields of the section.
     *
     * @return array The fields.
     * @since 3.9.0
     */
    private static function fields() {
        global $wpdb;
        $fields = array();
        $fields['version'] = self::field(__('Version', 'live-weather-station'), LIVE_WEATHER_STATION_VERSION);
        $fields['mode'] = self::field(__('Advanced mode', 'live-weather-station'), (bool)get_option('live_weather_station_advanced_mode'));
        $fields['multisite'] = self::field(__('Multisite', 'live-weather-station'), is_multisite());

        // Stations: counts only.
        $table = $wpdb->prefix . 'live_weather_station_stations';
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table, the name comes from the plugin itself.
        $rows = $wpdb->get_results("SELECT station_type, public_access, txt_sync, yow_sync, wow_sync, pws_sync, wug_sync, owm_sync, last_refresh FROM " . $table, ARRAY_A);
        $types = array();
        $public = 0;
        $sharing = 0;
        $newest = '';
        $oldest = '';
        $count = 0;
        foreach ((array)$rows as $row) {
            $count++;
            $type = (int)$row['station_type'];
            $name = isset(self::$station_types[$type]) ? self::$station_types[$type] : __('Unknown type', 'live-weather-station');
            $types[$name] = (isset($types[$name]) ? $types[$name] : 0) + 1;
            $public += ((int)$row['public_access'] === 1) ? 1 : 0;
            $sharing += ((int)$row['txt_sync'] + (int)$row['yow_sync'] + (int)$row['wow_sync'] + (int)$row['pws_sync'] + (int)$row['wug_sync'] + (int)$row['owm_sync'] > 0) ? 1 : 0;
            $refresh = (string)$row['last_refresh'];
            if ($refresh !== '' && strpos($refresh, '0000') !== 0) {
                if ($newest === '' || $refresh > $newest) {
                    $newest = $refresh;
                }
                if ($oldest === '' || $refresh < $oldest) {
                    $oldest = $refresh;
                }
            }
        }
        $fields['stations'] = self::field(__('Stations', 'live-weather-station'), $count);
        $list = array();
        foreach ($types as $name => $n) {
            $list[] = $name . ': ' . $n;
        }
        $fields['station_types'] = self::field(__('Stations by type', 'live-weather-station'), count($list) > 0 ? implode(', ', $list) : '-');
        $fields['public'] = self::field(__('Public stations', 'live-weather-station'), $public . ' / ' . $count);
        $fields['sharing'] = self::field(__('Stations which share their data (feeds, services)', 'live-weather-station'), $sharing);
        $fields['refresh_newest'] = self::field(__('Most recent refresh of a station (UTC)', 'live-weather-station'), $newest !== '' ? $newest : '-');
        $fields['refresh_oldest'] = self::field(__('Oldest last refresh of a station (UTC)', 'live-weather-station'), $oldest !== '' ? $oldest : '-');

        // Connections: only if they are set.
        $services = array(
            'Netatmo' => (bool)get_option('live_weather_station_netatmo_connected'),
            'Netatmo Healthy Home Coach' => (bool)get_option('live_weather_station_netatmohc_connected'),
            'Netatmo own keys' => (bool)get_option('live_weather_station_netatmo_own_keys'),
            'Ambient Weather' => (bool)get_option('live_weather_station_ambient_connected'),
            'OpenWeatherMap key' => self::is_set('live_weather_station_owm_apikey'),
            'Windy key' => self::is_set('live_weather_station_windy_apikey'),
        );
        $on = array();
        foreach ($services as $name => $state) {
            if ($state) {
                $on[] = $name;
            }
        }
        $fields['connections'] = self::field(__('Connected services and keys set', 'live-weather-station'), count($on) > 0 ? implode(', ', $on) : '-');

        // Scheduled jobs.
        $disabled = (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON);
        $fields['wp_cron_disabled'] = self::field(__('WP-Cron disabled by the configuration', 'live-weather-station'), $disabled);
        $jobs = 0;
        $late = 0;
        $crons = _get_cron_array();
        if (is_array($crons)) {
            foreach ($crons as $time => $hooks) {
                foreach ((array)$hooks as $hook => $events) {
                    if (is_string($hook) && strpos($hook, 'lws_') === 0) {
                        $jobs += count((array)$events);
                        if ((int)$time < time() - 15 * MINUTE_IN_SECONDS) {
                            $late += count((array)$events);
                        }
                    }
                }
            }
        }
        $fields['jobs'] = self::field(__('Scheduled jobs of the plugin', 'live-weather-station'), $jobs);
        $fields['jobs_late'] = self::field(__('Jobs more than 15 minutes late', 'live-weather-station'), $late);

        // Events log: the last 200 entries by level (no message).
        $log = $wpdb->prefix . 'live_weather_station_log';
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table, the name comes from the plugin itself.
        $levels = $wpdb->get_results("SELECT level, COUNT(*) AS n FROM (SELECT level FROM " . $log . " ORDER BY id DESC LIMIT 200) AS last_entries GROUP BY level", ARRAY_A);
        $l = array();
        foreach ((array)$levels as $row) {
            $l[] = sanitize_key((string)$row['level']) . ': ' . (int)$row['n'];
        }
        $fields['log'] = self::field(__('Last 200 entries of the events log, by level', 'live-weather-station'), count($l) > 0 ? implode(', ', $l) : '-');
        $fields['log_level'] = self::field(__('Level of the events log', 'live-weather-station'), (int)get_option('live_weather_station_logger_level', 6));

        // Settings which change the behaviour.
        $fields['history'] = self::field(__('Retention of the history (weeks, 0 = unlimited)', 'live-weather-station'), (int)get_option('live_weather_station_retention_history'));
        $fields['caches'] = self::field(__('Caches: frontend, backend', 'live-weather-station'), (get_option('live_weather_station_frontend_cache') ? __('Yes', 'live-weather-station') : __('No', 'live-weather-station')) . ', ' . (get_option('live_weather_station_backend_cache') ? __('Yes', 'live-weather-station') : __('No', 'live-weather-station')));
        $fields['limits'] = self::field(__('Limits (requests per minute: controls, feeds; cache entries per hour)', 'live-weather-station'), (int)get_option('live_weather_station_rate_limit_public', 120) . ', ' . (int)get_option('live_weather_station_rate_limit_feed', 0) . '; ' . (int)get_option('live_weather_station_cache_budget', 6000));
        $fields['partial_translation'] = self::field(__('Partial translation', 'live-weather-station'), (bool)get_option('live_weather_station_partial_translation'));
        $fields['mask'] = self::field(__('Personal data masked in the events log', 'live-weather-station'), (bool)get_option('live_weather_station_logger_mask_sensitive'));
        return $fields;
    }
}
