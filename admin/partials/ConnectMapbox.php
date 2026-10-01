<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\System\Output\Guard;

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\Utilities\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$settings = new Settings();
$mapbox_plan = $settings->get_mapbox_plan_array();

$plan_name = '';
foreach ($mapbox_plan as $plan) {
    if (get_option('live_weather_station_mapbox_plan')==$plan[0]) {
        $plan_name = $plan[1];
    }
}
$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '');
$warning = __('All the maps associated to this service will no longer be displayed.', 'live-weather-station');

?>

<form action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="Mapbox" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('Mapbox', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
            <?php if (get_option('live_weather_station_mapbox_apikey') == '') { ?>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('API key', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="key" name="key" type="text" size="20" value="" class="regular-text"></span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-password" width="20%" align="left" scope="row"><?php esc_html_e('API plan', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="select-option">
                            <select class="option-at-100" name="plan">
                                <?php foreach ($mapbox_plan as $plan) { ?>
                                    <option value="<?php echo esc_attr($plan[0]) ?>"<?php if (get_option('live_weather_station_mapbox_plan')==$plan[0]):?> selected="selected"<?php endif;?>><?php echo esc_html($plan[1]) ?></option>;
                                <?php } ?>
                            </select>
                        </span>
                    </td>
                </tr>
            <?php } else {?>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-events', null, null, 'Mapbox')); ?>"<?php echo $target; ?>><?php echo esc_html(live_weather_station_lcfirst(__('See events log', 'live-weather-station'))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $target is built above from the plugin option and fixed attribute text ?></a>)</span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('API plan', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php echo esc_html($plan_name) ?> (<?php echo InlineHelp::get(-44, '%s', __('get details', 'live-weather-station')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- InlineHelp::get() builds an anchor from a fixed plugin URL; the anchor text is a plugin translated string ?>)</span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (get_option('live_weather_station_mapbox_apikey') == '') { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="mapbox-span-sync" style="display: none;"><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo esc_html__('Connecting to service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="connect" id="mapbox-connect" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="mapbox-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="mapbox-span-sync" style="display: none;"><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo esc_html__('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="disconnect" id="mapbox-disconnect" class="button button-primary" onclick="lws_mapbox_confirmation = confirm(<?php echo Guard::js($warning); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the string with wp_json_encode() and JSON_HEX_TAG|AMP|APOS|QUOT, safe in an inline attribute ?>); return lws_mapbox_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>