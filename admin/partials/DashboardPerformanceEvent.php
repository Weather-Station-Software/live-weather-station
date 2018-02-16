<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

use WeatherStation\System\Logs\Logger;

$link = sprintf('%s <a href="%s">%s</a>', __('See', 'live-weather-station'), lws_get_admin_page_url('lws-analytics', null, 'event'), __('detailed analytics', 'live-weather-station'));

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
        <ul>
            <?php foreach ($val as $k=>$v) { ?>
            <?php if ($show_link && $v==0) { continue; }?>
                <li><i style="color:<?php echo Logger::get_color($k); ?>" class="fa fa-lg fa-fw <?php echo Logger::get_icon($k) ?>"></i>&nbsp;&nbsp;<?php echo ($v==0?__('no event typed', 'live-weather-station'):sprintf(_n('%s event typed','%s events typed', $v, 'live-weather-station'), $v)); ?> <em><?php echo lcfirst(Logger::get_name($k)); ?></em>.</li>
            <?php } ?>
        </ul>
    </div>
    <?php if ((bool)get_option('live_weather_station_show_analytics') && $show_link) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <i style="color:#999;" class="fa fa-lg fa-fw fa-bar-chart"></i>&nbsp;&nbsp;<?php echo $link ?>
        </div>
    <?php } ?>
</div>


