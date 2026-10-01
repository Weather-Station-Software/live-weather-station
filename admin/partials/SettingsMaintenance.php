<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Output\Guard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$cache_warning = sprintf(__('The %s events log will be purged. Is it really what you want?', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);

?>

<p>&nbsp;</p>
<p><?php echo sprintf(__('You can restore layouts and boxes positions to their defaults for the views used by %s.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);?><br/><em><?php echo __('To do it, just click on the corresponding button:', 'live-weather-station'); ?></em></p>
<p><a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'reset-dashboard', 'maintenance'), 'reset-dashboard')); ?>"><?php echo __('Reset Dashboard View', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'reset-services', 'maintenance'), 'reset-services')); ?>"><?php echo __('Reset Services View', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'reset-stations', 'maintenance'), 'reset-stations')); ?>"><?php echo __('Reset Stations Views', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'reset-analytics', 'maintenance'), 'reset-analytics')); ?>"><?php echo __('Reset Analytics Views', 'live-weather-station');?></a></p>

<p>&nbsp;</p>
<p><?php echo sprintf(__('You can delete all data collected for the stations added in %s and wait for scheduled resynchronization, or you can force resynchronization just after deletion.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);?><br/><em><?php echo __('To do it, just click on the corresponding button:', 'live-weather-station'); ?></em></p>
<p><a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'purge-data', 'maintenance'), 'purge-data')); ?>"><?php echo __('Purge Only', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp; <a id="link-sync" class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'sync-data', 'maintenance'), 'sync-data')); ?>"><?php echo __('Purge & Resynchronize', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <span id="span-sync" style="display: none;"><i class="<?php echo LIVE_WEATHER_STATION_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Synchronization in progress, please wait', 'live-weather-station');?>&hellip;</strong></span></p>

<p>&nbsp;</p>
<p><?php echo sprintf(__('At last, you can reset some subsystems of %s if something is going wrong.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);?><br/><em><?php echo __('To do it, just click on the corresponding button:', 'live-weather-station'); ?></em></p>
<p><a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'reset-cache', 'maintenance'), 'reset-cache')); ?>"><?php echo __('Invalidate Cache', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'purge-log', 'maintenance'), 'reset-log')); ?>" onclick="lws_purgelog_confirmation = confirm(<?php echo Guard::js($cache_warning); ?>); return lws_purgelog_confirmation;"><?php echo __('Purge Events Log', 'live-weather-station');?></a></p>

<p>&nbsp;</p>
<hr />
<h2><?php echo __('Configuration', 'live-weather-station');?></h2>
<p><?php echo sprintf(__('If you want to backup your configuration or replicate this configuration on another server, you can export the configuration of %s from here.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);?><br/><em><?php echo __('To export all settings, stations configuration and maps definitions, just click on the following button:', 'live-weather-station'); ?></em></p>
    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(live_weather_station_get_admin_page_url('lws-settings', 'export-configuration', 'maintenance'), 'export-configuration')); ?>"><?php echo __('Export Configuration', 'live-weather-station');?></a></p>
<p><em><?php echo __('API keys, tokens and passwords are not included in this export. If you really need them (for example to move the site to another server), you can include them explicitly, but keep the resulting file private and delete it as soon as it is imported:', 'live-weather-station'); ?></em><br/>
    <a class="button" href="<?php echo esc_url(wp_nonce_url(add_query_arg('include-credentials', '1', live_weather_station_get_admin_page_url('lws-settings', 'export-configuration', 'maintenance')), 'export-configuration')); ?>"><?php echo __('Export Configuration with Credentials', 'live-weather-station');?></a></p>
