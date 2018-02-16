<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$cache_warning = sprintf(__('The %s events log will be purged. Is it really what you want?', 'live-weather-station'), LWS_PLUGIN_NAME);

?>

<p>&nbsp;</p>
<p><?php echo sprintf(__('You can restore layouts and boxes positions to their defaults for the views used by %s.', 'live-weather-station'), LWS_PLUGIN_NAME);?><br/><em><?php echo __('To do it, just click on the corresponding button:', 'live-weather-station'); ?></em></p>
<p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'reset-dashboard', 'maintenance')); ?>"><?php echo __('Reset Dashboard View', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'reset-services', 'maintenance')); ?>"><?php echo __('Reset Services View', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'reset-stations', 'maintenance')); ?>"><?php echo __('Reset Stations Views', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'reset-analytics', 'maintenance')); ?>"><?php echo __('Reset Analytics Views', 'live-weather-station');?></a></p>

<p>&nbsp;</p>
<p><?php echo sprintf(__('You can delete all data collected for the stations added in %s and wait for scheduled resynchronization, or you can force resynchronization just after deletion.', 'live-weather-station'), LWS_PLUGIN_NAME);?><br/><em><?php echo __('To do it, just click on the corresponding button:', 'live-weather-station'); ?></em></p>
<p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'purge-data', 'maintenance')); ?>"><?php echo __('Purge Only', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp; <a id="link-sync" class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'sync-data', 'maintenance')); ?>"><?php echo __('Purge & Resynchronize', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Synchronization in progress, please wait', 'live-weather-station');?>&hellip;</strong></span></p>

<p>&nbsp;</p>
<p><?php echo sprintf(__('At last, you can reset some subsystems of %s if something is going wrong.', 'live-weather-station'), LWS_PLUGIN_NAME);?><br/><em><?php echo __('To do it, just click on the corresponding button:', 'live-weather-station'); ?></em></p>
<p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'reset-cache', 'maintenance')); ?>"><?php echo __('Invalidate Cache', 'live-weather-station');?></a> &nbsp;&nbsp;&nbsp;
    <a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'purge-log', 'maintenance')); ?>" onclick="lws_purgelog_confirmation = confirm('<?php echo $cache_warning; ?>'); return lws_purgelog_confirmation;"><?php echo __('Purge Events Log', 'live-weather-station');?></a></p>