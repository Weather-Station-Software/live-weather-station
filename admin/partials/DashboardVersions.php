<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\System\Environment\Manager as EnvManager;


$wp_str = EnvManager::wordpress_version_text() . ' (' . EnvManager::php_version_text() . ' / ' . EnvManager::mysql_version_text() . ')';
$lws_str = EnvManager::weatherstation_version_text();
$dev = EnvManager::is_plugin_in_dev_mode();
$rc = EnvManager::is_plugin_in_rc_mode();

?>

<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li><i style="color:#999" class="fa fa-lg fa-fw fa-wordpress"></i>&nbsp;&nbsp;<?php echo $wp_str; ?></li>
        <li><img style="width:18px;float:left;padding-right: 4px;" src="<?php echo set_url_scheme(SVG::get_base64_menu_icon('#999', '#999')); ?>" />&nbsp;<?php echo $lws_str; ?></li>
    </ul>
</div>

<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
    <li><i style="color:#999" class="fa fa-lg fa-fw fa-info"></i>&nbsp;&nbsp;<a href="<?php echo get_admin_page_url('lws-dashboard', 'changelog'); ?>"><?php echo __('changelog', 'live-weather-station'); ?></a>, <a href="<?php echo get_admin_page_url('lws-dashboard', 'configuration'); ?>"><?php echo __('server configuration details', 'live-weather-station'); ?>.</a></li>
    </ul>
</div>

<?php if ($dev) { ?>
<div class="activity-block" style="padding-bottom: 0px">
    <i style="color:#ff4444" class="fa fa-lg fa-fw fa-exclamation-triangle"></i>&nbsp;&nbsp;<strong><?php echo __('Warning', 'live-weather-station'); ?></strong> &mdash; <?php echo sprintf(__('This version of %s is not production-ready. It is a development preview. Use it at your own risk!', 'live-weather-station'), LWS_PLUGIN_NAME); ?>
</div>
<?php } ?>
<?php if ($rc) { ?>
    <div class="activity-block" style="padding-bottom: 0px">
        <i style="color:#999" class="fa fa-lg fa-fw fa-exclamation-circle"></i>&nbsp;&nbsp;<strong><?php echo __('Information', 'live-weather-station'); ?></strong> &mdash; <?php echo sprintf(__('This version of %s is a release candidate. Although ready for production, this version is not officially supported in production environments.', 'live-weather-station'), LWS_PLUGIN_NAME); ?>
    </div>
<?php } ?>
