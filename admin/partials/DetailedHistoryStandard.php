<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

use WeatherStation\System\Help\InlineHelp;

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="daily-histo" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo __('Daily data', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <?php if ((bool)get_option('live_weather_station_collect_history')) { ?>
            <p><strong><?php echo __('Depending on the capabilities of the collected stations, the daily data - compiled at a frequency ranging from 5 to 15 minutes depending on the type of the station - are:', 'live-weather-station');?></strong></p>
            <?php echo do_shortcode('[live-weather-station-historical-capabilities item="daily" mode="current" style="icon" column="3"]'); ?>
            <?php } else { ?>
                <p><strong><?php echo __('No data currently compiled.', 'live-weather-station');?></strong></p>
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