<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.§.0
 */

use WeatherStation\System\Output\Guard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '');
$warning = sprintf(/* translators: %s: name of the plugin */ __('All stations associated to this service will be removed from %s.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);

?>

<form action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="Ambient" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('Ambient', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
            <?php if (get_option('live_weather_station_ambient_connected') == 0) { ?>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><?php esc_html_e('API key', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="apikey" name="apikey" type="text" size="20" value="" class="regular-text"></span>
                    </td>
                </tr>
            <?php } else {?>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-events', null, null, 'Ambient')); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $target is either empty or a fixed target and rel attribute string defined at the top of this template ?>><?php echo esc_html(live_weather_station_lcfirst(__('See events log', 'live-weather-station'))); ?></a>)</span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (get_option('live_weather_station_ambient_connected') == 0) { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="ambient-span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo esc_html__('Connecting to service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="connect" id="ambient-connect" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="ambient-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="ambient-span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo esc_html__('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="disconnect" id="ambient-disconnect" class="button button-primary" onclick="lws_ambient_confirmation = confirm(<?php echo Guard::js($warning); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() returns wp_json_encode() with JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS and JSON_HEX_QUOT, a safe JS literal in an HTML attribute ?>); return lws_ambient_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>