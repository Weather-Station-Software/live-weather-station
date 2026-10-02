<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

use WeatherStation\System\Environment\Manager as Env;
use WeatherStation\System\Logs\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// This template shows the server host, IP, document root and database user: administrators only.
if ( ! current_user_can( live_weather_station_manage_capability() ) ) {
	return;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$OS = Env::server_os();

if (!$OS || !Env::server_cpu() || !Env::server_core()) {
    Logger::warning('Core',null,null,null,null,null,null,'Your server configuration does not allow to query system information.');
}

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e('Server', 'live-weather-station');?></span></h3>
        <div class="inside">
            <table cellspacing="10" width="99%">
                <tbody>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-server"></i></td>
                    <td/><?php echo esc_html(gethostname()).' <code>'.esc_html(Env::server_ip()).'</code>'; ?></td>
                </tr>
                <?php if ($OS) { ?>
                    <tr>
                        <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-cog"></i></td>
                        <td><?php echo esc_html($OS); ?></td>
                    </tr>
                <?php } ?>
                <?php if (Env::server_cpu() && Env::server_core()) { ?>
                    <tr>
                        <td width="10%"/><td width="20px"><i style="color:#999999" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-microchip"></i></td>
                        <td><?php echo esc_html(Env::server_cpu() . ' / ' . Env::server_core() . ' ' . __('cores', 'live-weather-station')); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>