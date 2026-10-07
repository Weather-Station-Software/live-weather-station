<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$fields = array('pull', 'push', 'system', 'history');
$values = array();
foreach ($fields as $field) {
    if ($field == 'system') {
        $d = 150;
    }
    else {
        $d = 2500;
    }
    if ($val[$field]['avr_time'] > $d) {
        $quality = 0;
    }
    else {
        $quality = abs(round(100 * (1 - ($val[$field]['avr_time'] / $d)), 0));
    }
    if ($quality > 100) {
        $quality = 100;
    }
    if ($quality < 0) {
        $quality = 0;
    }
    $color1 = 154 - round($quality/1.4, 0);
    $color2 = 154 + $quality;
    $values[$field]['clr'] = 'rgb('.$color1.', '.$color1.', '.$color2.')';
    $values[$field]['txt'] = ucfirst($val[$field]['name']);
    $values[$field]['txt'] .= ' - ' .  sprintf(/* translators: 1: Number of executions, 2: Average execution time in milliseconds. */ __('tasks executed %1$s times in an average time of %2$s ms.', 'live-weather-station'), $val[$field]['count'], $val[$field]['avr_time']);
}

$link = sprintf('%s <a href="%s">%s</a>', __('See', 'live-weather-station'), esc_url(live_weather_station_get_admin_page_url('lws-analytics', null, 'task')), __('detailed analytics', 'live-weather-station'));

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <?php foreach ($fields as $field) { ?>
                <li><i style="color:<?php echo esc_attr($values[$field]['clr']); ?>" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-fw fa-circle"></i>&nbsp;&nbsp;<?php echo esc_html($values[$field]['txt']); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php if ((bool)get_option('live_weather_station_show_analytics') && $show_link) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <i style="color:#999;" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAR );?> fa-<?php echo LIVE_WEATHER_STATION_FA5?'chart-bar':'bar-chart';?>"></i>&nbsp;&nbsp;<?php echo wp_kses_post($link); ?>
        </div>
    <?php } ?>
</div>


