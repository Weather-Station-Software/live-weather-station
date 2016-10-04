<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\SVG\Handling as SVG;

global $wp_version;
$wp_str = 'Wordpress ' . $wp_version . ' (PHP ' . PHP_VERSION . ')';
$lws_str = 'Weather Station ' . LWS_VERSION;
$dev = (strpos(LWS_VERSION, 'dev') > 0);

?>

<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li><i style="color:#999" class="fa fa-lg fa-wordpress"></i>&nbsp;<?php echo $wp_str; ?></li>
        <li><img style="width:18px;float:left;padding-right: 4px;" src="<?php echo set_url_scheme(SVG::get_base64_menu_icon('#999', '#999')); ?>" /><?php echo $lws_str; ?></li>
    </ul>
</div>
<?php if ($dev) { ?>
<div class="activity-block" style="padding-bottom: 0px">
    <i style="color:#ff4444" class="fa fa-lg fa-exclamation-triangle"></i>&nbsp;<strong><?php echo __('Warning', 'live-weather-station'); ?></strong> &mdash; <?php echo sprintf(__('This version of %s is not production-ready. It is a development preview. Use it at your own risk!', 'live-weather-station'), LWS_PLUGIN_NAME); ?>
</div>
<?php } ?>
