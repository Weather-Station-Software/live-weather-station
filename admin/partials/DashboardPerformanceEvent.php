<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

use WeatherStation\System\Logs\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$link = sprintf('%s <a href="%s">%s</a>', __('See', 'live-weather-station'), esc_url(live_weather_station_get_admin_page_url('lws-analytics', null, 'event')), __('detailed analytics', 'live-weather-station'));

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <?php foreach ($val as $k=>$v) { ?>
            <?php if ($show_link && $v==0) { continue; }?>
                <li><i style="color:<?php echo esc_attr(Logger::get_color($k)); ?>" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-fw <?php echo esc_attr(Logger::get_icon($k)) ?>"></i>&nbsp;&nbsp;<?php echo esc_html($v==0?__('no event typed', 'live-weather-station'):sprintf(/* translators: %s: number of events of this type */ _n('%s event typed','%s events typed', $v, 'live-weather-station'), $v)); ?> <em><?php echo esc_html(live_weather_station_lcfirst(Logger::get_name($k))); ?></em>.</li>
            <?php } ?>
        </ul>
    </div>
    <?php if ((bool)get_option('live_weather_station_show_analytics') && $show_link) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <i style="color:#999;" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAR );?> fa-<?php echo LIVE_WEATHER_STATION_FA5?'chart-bar':'bar-chart';?>"></i>&nbsp;&nbsp;<?php echo wp_kses_post($link); ?>
        </div>
    <?php } ?>
</div>


