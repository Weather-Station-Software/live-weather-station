<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\DB\Stats;
use WeatherStation\System\Help\InlineHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
if (LIVE_WEATHER_STATION_REQUIREMENTS_OK) {
    $stats = new Stats();
    $a = $stats->get_operational();
    if ($a['station'] == 0) {
        $data_str = __('The system is paused: it has no data to collect.', 'live-weather-station');
    }
    else {
        /* translators: 1: number of stations, 2: number of modules, 3: number of measurements */
        $data_str = sprintf(_n('The system is up and running: it is currently collecting %3$d measurements from %1$d station composed of %2$d modules.', 'The system is up and running: it is currently collecting %3$d measurements from %1$d stations composed of %2$d modules.', $a['station'], 'live-weather-station'), $a['station'], $a['module'], $a['measure']);
    }
    $p = $stats->get_processes();
    if (count($p) > 0) {
        $show_processes = true;
        $de = array();
        foreach ($p as $d) {
            $de[] = $d['name'] . ' (' . $d['progress'] . '%)';
        }
        $processes_str = __('Currently running:', 'live-weather-station') . ' ' . implode(', ', $de) . '.';
    }
    else {
        $show_processes = false;
        $processes_str = '';
    }
    $services_str = __('none', 'live-weather-station');
    $services = array();
    if (get_option('live_weather_station_ambient_connected')) {
        $services[] = 'Ambient';
    }
    if (get_option('live_weather_station_bloomsky_connected')) {
        $services[] = 'BloomSky';
    }
    if (get_option('live_weather_station_mapbox_apikey') != '') {
        $services[] = 'Mapbox';
    }
    if (get_option('live_weather_station_maptiler_apikey') != '') {
        $services[] = 'MapTiler';
    }
    if ((bool)get_option('live_weather_station_netatmo_connected') || (bool)get_option('live_weather_station_netatmohc_connected')) {
        $services[] = 'Netatmo';
    }
    if (get_option('live_weather_station_owm_apikey') != '') {
        $services[] = 'OpenWeatherMap';
    }
    if (get_option('live_weather_station_stadia_apikey') != '') {
        $services[] = 'Stadia Maps';
    }
    if (get_option('live_weather_station_thunderforest_apikey') != '') {
        $services[] = 'ThunderForest';
    }
    if (get_option('live_weather_station_wug_apikey') != '') {
        $services[] = 'Weather Underground';
    }
    if (get_option('live_weather_station_windy_apikey') != '') {
        $services[] = 'Windy';
    }
    if (count($services) > 0) {
        $services_str = implode(', ', $services);
    }
    $services_str = __('Connected services:', 'live-weather-station') . ' ' . $services_str . '.';
    $log_url = '<a href="' . esc_url(LIVE_WEATHER_STATION_ADMIN_PHP_URL . '?page=lws-events&level=error') . '" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>' . live_weather_station_lcfirst(__('Events log', 'live-weather-station')) . '</a>';
    $a = $stats->get_log();
    if ($a['emergency'] > 0) {
        /* translators: 1: name of this plugin, 2: link to the events log */
        $run_str = sprintf(__('%1$s has encountered operating issues in the last 3 days. You should check the %2$s to know the cause of this problem.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME, $log_url);
    } else {
        if ($a['error'] > ($a['recent_error'] == 0 ? 2 : 0)) {
            /* translators: 1: name of this plugin, 2: link to the events log */
            $run_str = sprintf(__('%1$s has experienced some difficulties while operating. You should take a look at the %2$s to see what could be improved.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME, $log_url);
        } else {
            /* translators: 1: name of this plugin */
            $run_str = sprintf(__('All good, %1$s runs smoothly.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);
        }
    }
}
else {
    $req_url = '<a href="' . esc_url(LIVE_WEATHER_STATION_ADMIN_PHP_URL . '?page=lws-requirements') . '" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>' . lcfirst(__('see why', 'live-weather-station')) . '</a>';
    /* translators: 1: name of this plugin, 2: link to the requirements page */
    $run_str = sprintf(__('%1$s can\'t run: %2$s', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME, $req_url) . '&hellip;';
}
$quota = get_transient('live_weather_station_quota_alert');
if ($quota > 0) {
    $quota_str = __('API usage has exceeded quotas!', 'live-weather-station');
    if ($quota == 1) {
        $quota_str = __('API usage will soon exceed quotas!', 'live-weather-station');
    }
}

?>
<?php if (LIVE_WEATHER_STATION_REQUIREMENTS_OK) { ?>
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <li><?php echo esc_html($data_str); ?></li>
            <li><?php echo esc_html($services_str); ?></li>
        </ul>
    </div>
<?php } ?>
<?php if (get_option('live_weather_station_quota_mode') == 1 && $quota > 0) { ?>
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <li><i style="color:#FF4444;" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-fw fa-exclamation-triangle"></i>&nbsp;&nbsp;<?php echo esc_html($quota_str); ?></li>
        </ul>
    </div>
<?php } ?>
<?php if ($show_processes) { ?>
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <?php echo esc_html($processes_str); ?>
        </ul>
    </div>
<?php } ?>
    <div class="activity-block" style="padding-bottom: 0px;">
        <?php echo wp_kses_post($run_str); ?>
    </div>


