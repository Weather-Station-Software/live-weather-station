<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.1
 */

use WeatherStation\System\Environment\Manager as EnvManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// This template shows the database name and user: administrators only (the page is already behind the menu capability).
if ( ! current_user_can( live_weather_station_manage_capability() ) ) {
	return;
}
?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo esc_attr__('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo esc_html__('Database', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <table cellspacing="10" width="99%">
                <tbody>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-database"></i></td>
                    <td><?php echo esc_html(EnvManager::mysql_version_text()); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-bolt"></i></td>
                    <td><?php echo esc_html(EnvManager::mysql_name_text() . ' (' . EnvManager::mysql_charset_text() . ')'); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-user"></i></td>
                    <td><?php echo esc_html(EnvManager::mysql_user_text()); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-<?php echo LIVE_WEATHER_STATION_FA5?'arrows-alt':'arrows';?>"></i></td>
                    <td><?php echo esc_html(EnvManager::mysql_total_size_text() . ' (' . __('total', 'live-weather-station') . ')'); ?> / <?php echo esc_html(EnvManager::mysql_lws_size_text() . ' (' . LIVE_WEATHER_STATION_PLUGIN_NAME . ')'); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>