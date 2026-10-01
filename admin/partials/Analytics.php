<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector, sanitized with sanitize_key(), no state change
$active_tab = (isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general');
$page = LIVE_WEATHER_STATION_ADMIN_DIR.'partials/Analytics' . ucfirst($active_tab) . '.php';
$page = str_replace('_short', 'Short', $page);
$page = str_replace('_long', 'Long', $page);
if ((!in_array($active_tab, array('general', 'quota_short', 'quota_long', 'cache', 'event', 'task', 'database'), true) || !file_exists($page)) && ($active_tab != 'general')) {
    $active_tab = 'general';
    $page = LIVE_WEATHER_STATION_ADMIN_DIR.'partials/Analytics' . ucfirst($active_tab) . '.php';
}

$show_cache = ((bool)get_option('live_weather_station_frontend_cache') ||
    (bool)get_option('live_weather_station_widget_cache') ||
    (bool)get_option('live_weather_station_dgraph_cache') ||
    (bool)get_option('live_weather_station_ygraph_cache') ||
    (bool)get_option('live_weather_station_cgraph_cache') ||
    (bool)get_option('live_weather_station_backend_cache'));


?>

<div class="wrap">

    <h2><?php echo esc_html__('Analytics', 'live-weather-station');?></h2>

    <h2 class="nav-tab-wrapper">
        <a href="?page=lws-analytics&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('General', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=quota_short" class="nav-tab <?php echo $active_tab == 'quota_short' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('API short-time scale', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=quota_long" class="nav-tab <?php echo $active_tab == 'quota_long' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('API long-time scale', 'live-weather-station');?></a>
        <?php if ($show_cache) { ?>
            <a href="?page=lws-analytics&tab=cache" class="nav-tab <?php echo $active_tab == 'cache' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Cache', 'live-weather-station');?></a>
        <?php } ?>
        <a href="?page=lws-analytics&tab=event" class="nav-tab <?php echo $active_tab == 'event' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Events', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=task" class="nav-tab <?php echo $active_tab == 'task' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Tasks', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=database" class="nav-tab <?php echo $active_tab == 'database' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Database', 'live-weather-station');?></a>
    </h2>

    <?php if ($active_tab == 'general') { ?>
        <?php $this->_analytics->get(); ?>
    <?php } else { ?>
        <?php include($page); ?>
    <?php } ?>

</div>