<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

$fields = array('frontend', 'widget', 'backend', 'dgraph', 'ygraph');
$names = array(__('controls', 'live-weather-station'), __('widgets', 'live-weather-station'), __('backend', 'live-weather-station'), __('daily graph', 'live-weather-station'), __('historical graph', 'live-weather-station'));
$values = array();
foreach ($fields as $key=>$field) {
    $values[$field]['txt'] = ucfirst($names[$key]);
    $values[$field]['txt'] .= ' - ' .  sprintf(__('%s efficiency', 'live-weather-station'), $val[$field.'_success'].__('%', 'live-weather-station'));
    $values[$field]['txt'] .= ', ' . sprintf(__('%s saved per request', 'live-weather-station'), $val[$field.'_time_saving'].' '.__('ms', 'live-weather-station'));
    $values[$field]['txt'] .= '.';
    $color1 = 154 - round($val[$field.'_success']/1.4, 0);
    $color2 = 154 + round($val[$field.'_success'], 0);
    $values[$field]['clr'] = 'rgb('.$color1.', '.$color1.', '.$color2.')';
}

$link = sprintf('%s <a href="%s">%s</a>', __('See', 'live-weather-station'), lws_get_admin_page_url('lws-analytics', null, 'cache'), __('detailed analytics', 'live-weather-station'));

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
            <ul>
                <?php foreach ($fields as $key=>$field) { ?>
                    <?php if ((bool)get_option('live_weather_station_' . $field . '_cache')) { ?>
                        <li><i style="color:<?php echo $values[$field]['clr']; ?>" class="fa fa-lg fa-fw fa-circle"></i>&nbsp;&nbsp;<?php echo $values[$field]['txt']; ?></li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    <?php if ((bool)get_option('live_weather_station_show_analytics') && $show_link) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <i style="color:#999;" class="fa fa-lg fa-fw fa-bar-chart"></i>&nbsp;&nbsp;<?php echo $link ?>
        </div>
    <?php } ?>
</div>


