<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$values = array();
foreach ($val as $k => $v) {
    if ($v['get']['has_quota']) {
        $values[$k]['txt'] = sprintf(/* translators: 1: name of the service, 2: percentage of the quota used, 3: percentage of the maximal rate reached */ __('%1$s: %2$s used, peak rate at %3$s of the maximal capacity.', 'live-weather-station'), $k, $v['get']['count'].'%', $v['get']['rate'].'%');
        $q = round(($v['get']['count']*7 +  $v['get']['rate']*3)/10, 0);
        $quality = 100 - $q;
        if ($quality > 100) {
            $quality = 100;
        }
        if ($quality < 0) {
            $quality = 0;
        }
        $color1 = 154 - round($quality/1.4, 0);
        $color2 = 154 + $quality;
        $values[$k]['clr'] = 'rgb('.$color1.', '.$color1.', '.$color2.')';
    }
}

$link = sprintf('%s <a href="%s">%s</a>', __('See', 'live-weather-station'), esc_url(live_weather_station_get_admin_page_url('lws-analytics', null, 'quota_short')), __('detailed analytics', 'live-weather-station'));

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <?php foreach ($values as $value) { ?>
                <li><i style="color:<?php echo esc_attr($value['clr']); ?>" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-fw fa-circle"></i>&nbsp;&nbsp;<?php echo esc_html($value['txt']); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php if ((bool)get_option('live_weather_station_show_analytics') && $show_link) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <i style="color:#999;" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAR);?> fa-<?php echo LIVE_WEATHER_STATION_FA5?'chart-bar':'bar-chart';?>"></i>&nbsp;&nbsp;<?php echo wp_kses($link, array('a' => array('href' => array()))) ?>
        </div>
    <?php } ?>
</div>


