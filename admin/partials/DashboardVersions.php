<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\System\Environment\Manager as EnvManager;


$wp_str = EnvManager::wordpress_version_text() . ' (' . EnvManager::php_version_text() . ' / ' . EnvManager::mysql_version_text() . ').';
$lws_str = EnvManager::weatherstation_version_text();
if (get_option('live_weather_station_last_update')) {
    $format = get_option('date_format') ;//. ', ' . get_option('time_format');
    $update = date_i18n($format, strtotime(get_date_from_gmt(date('Y-m-d H:i:s',get_option('live_weather_station_last_update')))) );
    $lws_str .= ' - ' . $update;
}
$dev = EnvManager::is_plugin_in_dev_mode();
$rc = EnvManager::is_plugin_in_rc_mode();
if (EnvManager::is_updatable()) {
    if (EnvManager::is_autoupdatable()) {
        $autoupdate = sprintf(__('Automatic updates for %s are enabled', 'live-weather-station'), LWS_PLUGIN_NAME);
    }
    else {
        $autoupdate = sprintf(__('Automatic updates for %s are disabled by settings', 'live-weather-station'), LWS_PLUGIN_NAME);
    }
}
else {
    $autoupdate = __('All automatic updates are deactivated by WordPress configuration', 'live-weather-station');
}
$lws_str .= '.';
$autoupdate .= '.';
?>

<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li><i style="color:#21759B" class="fa fa-lg fa-fw fa-wordpress"></i>&nbsp;&nbsp;<?php echo $wp_str; ?></li>
        <li><img style="width:18px;float:left;padding-right: 6px;padding-left: 2px;" src="<?php echo set_url_scheme(SVG::get_base64_menu_icon($color1='#666', $color2='#ffde3a')); ?>" />&nbsp;<?php echo $lws_str; ?></li>
        <?php if (EnvManager::is_updatable()) { ?>
            <?php if (EnvManager::is_autoupdatable()) { ?>
                <li><i class="fa fa-lg fa-fw fa-spin fa-circle-o-notch" style="color:#3ADF00"></i>&nbsp;&nbsp;<?php echo $autoupdate; ?></li>
            <?php } else {?>
                <li><i class="fa fa-lg fa-fw fa-circle-o-notch" style="color:#999"></i>&nbsp;&nbsp;<?php echo $autoupdate; ?></li>
            <?php } ?>
        <?php } else {?>
            <li><i class="fa fa-lg fa-fw fa-circle-o-notch" style="color:#DF0101"></i>&nbsp;&nbsp;<?php echo $autoupdate; ?></li>
        <?php } ?>
        <?php if ($dev) { ?>
            <li><i style="color:#ff4444" class="fa fa-lg fa-fw fa-exclamation-triangle"></i>&nbsp;&nbsp;<strong><?php echo __('Warning', 'live-weather-station'); ?></strong> &mdash; <?php echo sprintf(__('This version of %s is not production-ready. It is a development preview. Use it at your own risk!', 'live-weather-station'), LWS_PLUGIN_NAME); ?></li>
        <?php } ?>
        <?php if ($rc) { ?>
            <li><i style="color:#999" class="fa fa-lg fa-fw fa-exclamation-circle"></i>&nbsp;&nbsp;<strong><?php echo __('Information', 'live-weather-station'); ?></strong> &mdash; <?php echo sprintf(__('This version of %s is a release candidate. Although ready for production, this version is not officially supported in production environments.', 'live-weather-station'), LWS_PLUGIN_NAME); ?></li>
        <?php } ?>
    </ul>
</div>

<div class="activity-block" style="padding-bottom: 0px;">
    <i style="color:#999" class="fa fa-lg fa-fw fa-info"></i>&nbsp;&nbsp;<a href="<?php echo lws_get_admin_page_url('lws-dashboard', 'changelog'); ?>"><?php echo ucfirst(__('changelog', 'live-weather-station')); ?></a>, <a href="<?php echo lws_get_admin_page_url('lws-dashboard', 'configuration'); ?>"><?php echo __('server configuration details', 'live-weather-station'); ?>.</a>
</div>

