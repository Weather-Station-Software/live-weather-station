<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

$active_tab = (isset($_GET['tab']) ? $_GET['tab'] : 'general');
$page = LWS_ADMIN_DIR.'partials/Analytics' . ucfirst($active_tab) . '.php';
$page = str_replace('_short', 'Short', $page);
$page = str_replace('_long', 'Long', $page);
if (!file_exists($page) && ($active_tab != 'general')) {
    $active_tab = 'general';
    $page = LWS_ADMIN_DIR.'partials/Analytics' . ucfirst($active_tab) . '.php';
}

$show_cache = ((bool)get_option('live_weather_station_frontend_cache') ||
    (bool)get_option('live_weather_station_widget_cache') ||
    (bool)get_option('live_weather_station_dgraph_cache') ||
    (bool)get_option('live_weather_station_ygraph_cache') ||
    (bool)get_option('live_weather_station_backend_cache'));


?>

<div class="wrap">

    <h2><?php echo __('Analytics', 'live-weather-station');?></h2>

    <h2 class="nav-tab-wrapper">
        <a href="?page=lws-analytics&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php echo __('General', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=quota_short" class="nav-tab <?php echo $active_tab == 'quota_short' ? 'nav-tab-active' : ''; ?>"><?php echo __('API short-time scale', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=quota_long" class="nav-tab <?php echo $active_tab == 'quota_long' ? 'nav-tab-active' : ''; ?>"><?php echo __('API long-time scale', 'live-weather-station');?></a>
        <?php if ($show_cache) { ?>
            <a href="?page=lws-analytics&tab=cache" class="nav-tab <?php echo $active_tab == 'cache' ? 'nav-tab-active' : ''; ?>"><?php echo __('Cache', 'live-weather-station');?></a>
        <?php } ?>
        <a href="?page=lws-analytics&tab=event" class="nav-tab <?php echo $active_tab == 'event' ? 'nav-tab-active' : ''; ?>"><?php echo __('Events', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=task" class="nav-tab <?php echo $active_tab == 'task' ? 'nav-tab-active' : ''; ?>"><?php echo __('Tasks', 'live-weather-station');?></a>
        <a href="?page=lws-analytics&tab=database" class="nav-tab <?php echo $active_tab == 'database' ? 'nav-tab-active' : ''; ?>"><?php echo __('Database', 'live-weather-station');?></a>
    </h2>

    <?php if ($active_tab == 'general') { ?>
        <?php $this->_analytics->get(); ?>
    <?php } else { ?>
        <?php include($page); ?>
    <?php } ?>

</div>