<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p>&nbsp;</p>
<hr/>
<div class="wrap">
    <h2><?php echo esc_html__('Current settings', 'live-weather-station');?></h2>
    <p><?php echo esc_html(sprintf(/* translators: %s: name of the plugin */ __('The current settings allow %s to store, manipulate and display the following data:', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME));?></p>
    <div>
        <div id="wpcom-stats-meta-box-container" class="metabox-holder">
            <div class="postbox-container" style="width: 100%;margin-right: 10px;">
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/DetailedHistoryStandard.php'); ?>
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/DetailedHistoryExtended.php'); ?>
            </div>
        </div>
    </div>
</div>