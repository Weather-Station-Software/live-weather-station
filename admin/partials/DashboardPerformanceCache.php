<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$fields = array('frontend', 'widget', 'backend', 'dgraph', 'ygraph', 'cgraph');
$names = array(__('controls', 'live-weather-station'), __('widgets', 'live-weather-station'), __('backend', 'live-weather-station'), __('daily graph', 'live-weather-station'), __('historical graph', 'live-weather-station'), __('climatological data', 'live-weather-station'));
$values = array();
foreach ($fields as $key=>$field) {
    $values[$field]['txt'] = ucfirst($names[$key]);
    $values[$field]['txt'] .= ' - ' .  sprintf(/* translators: %s: cache efficiency as a percentage */ __('%s efficiency', 'live-weather-station'), $val[$field.'_success'].__('%', 'live-weather-station'));
    if ($val[$field.'_time_saving'] > 0) {
        $values[$field]['txt'] .= ', ' . sprintf(/* translators: %s: time saved, in milliseconds */ __('%s saved per request', 'live-weather-station'), $val[$field.'_time_saving'].' '.__('ms', 'live-weather-station'));
    }
    $values[$field]['txt'] .= '.';
    $color1 = 154 - round($val[$field.'_success']/1.4, 0);
    $color2 = 154 + round($val[$field.'_success'], 0);
    $values[$field]['clr'] = 'rgb('.$color1.', '.$color1.', '.$color2.')';
}

$link = sprintf('%s <a href="%s">%s</a>', esc_html__('See', 'live-weather-station'), esc_url(live_weather_station_get_admin_page_url('lws-analytics', null, 'cache')), esc_html__('detailed analytics', 'live-weather-station'));

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
            <ul>
                <?php foreach ($fields as $key=>$field) { ?>
                    <?php if ((bool)get_option('live_weather_station_' . $field . '_cache')) { ?>
                        <li><i style="color:<?php echo esc_attr($values[$field]['clr']); ?>" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-fw fa-circle"></i>&nbsp;&nbsp;<?php echo esc_html($values[$field]['txt']); ?></li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    <?php if ((bool)get_option('live_weather_station_show_analytics') && $show_link) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <i style="color:#999;" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAR);?> fa-<?php echo esc_attr(LIVE_WEATHER_STATION_FA5 ? 'chart-bar' : 'bar-chart');?>"></i>&nbsp;&nbsp;<?php echo wp_kses($link, array('a' => array('href' => array()))); ?>
        </div>
    <?php } ?>
</div>


