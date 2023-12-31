<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

use WeatherStation\System\Help\InlineHelp;

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="yearly-histo" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo __('Historical data', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <?php if ((bool)get_option('live_weather_station_build_history')) { ?>
                <p><strong><?php echo __('Depending on the capabilities of the collected stations, historical data - compiled for one operation set per day - are:', 'live-weather-station');?></strong></p>
                <?php echo do_shortcode('[live-weather-station-historical-capabilities item="yearly" mode="current" style="icon" column="1"]'); ?>
            <?php } else { ?>
                <p><strong><?php echo __('No data currently collected.', 'live-weather-station');?></strong></p>
            <?php } ?>
        </div>
        <div id="major-publishing-actions">
            <div>
                <?php echo InlineHelp::get(6, __('You can find detailed specifications on historical data on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>