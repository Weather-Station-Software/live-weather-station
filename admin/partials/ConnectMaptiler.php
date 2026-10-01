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
$maptiler_plan = $settings->get_maptiler_plan_array();

$plan_name = '';
foreach ($maptiler_plan as $plan) {
    if (get_option('live_weather_station_maptiler_plan')==$plan[0]) {
        $plan_name = $plan[1];
    }
}
$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '');
$warning = __('All the maps associated to this service will no longer be displayed.', 'live-weather-station');

?>

<form action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="Maptiler" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('Maptiler', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
            <?php if (get_option('live_weather_station_maptiler_apikey') == '') { ?>
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
                                <?php foreach ($maptiler_plan as $plan) { ?>
                                    <option value="<?php echo esc_attr($plan[0]) ?>"<?php if (get_option('live_weather_station_maptiler_plan')==$plan[0]):?> selected="selected"<?php endif;?>><?php echo esc_html($plan[1]) ?></option>;
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
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-events', null, null, 'Maptiler')); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the target attribute is the fixed string chosen at the top of this file (target blank with noopener noreferrer, or empty), never user data ?>><?php echo esc_html(live_weather_station_lcfirst(__('See events log', 'live-weather-station'))); ?></a>)</span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('API plan', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php echo esc_html($plan_name) ?> (<?php echo wp_kses_post(InlineHelp::get(-45, '%s', __('get details', 'live-weather-station')));?>)</span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (get_option('live_weather_station_maptiler_apikey') == '') { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="maptiler-span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php esc_html_e('Connecting to service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="connect" id="maptiler-connect" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="maptiler-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="maptiler-span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php esc_html_e('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="disconnect" id="maptiler-disconnect" class="button button-primary" onclick="lws_maptiler_confirmation = confirm(<?php echo Guard::js($warning); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes with JSON_HEX_QUOT, JSON_HEX_AMP, JSON_HEX_APOS and JSON_HEX_TAG, so the literal is safe inside an HTML attribute and no quote can end it ?>); return lws_maptiler_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>