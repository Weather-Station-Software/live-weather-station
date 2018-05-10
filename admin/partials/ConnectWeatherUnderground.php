<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\Utilities\Settings;

$settings = new Settings();
$wug_plan = $settings->get_wug_plan_array();

foreach ($wug_plan as $plan) {
    if (get_option('live_weather_station_wug_plan')==$plan[0]) {
        $plan_name = $plan[1];
    }
}
$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '');
$warning = sprintf(__('All stations associated to this service will be removed from %s.', 'live-weather-station'), LWS_PLUGIN_NAME);

?>

<form action="<?php echo esc_url(lws_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="WeatherUnderground" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('WeatherUnderground', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
            <?php if (get_option('live_weather_station_wug_apikey') == '') { ?>
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
                                <?php foreach ($wug_plan as $plan) { ?>
                                    <option value="<?php echo $plan[0] ?>"<?php if (get_option('live_weather_station_wug_plan')==$plan[0]):?> selected="selected"<?php endif;?>><?php echo $plan[1] ?></option>;
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
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(lws_get_admin_page_url('lws-events', null, null, 'Weather Underground')); ?>"<?php echo $target; ?>><?php echo strtolower(__('See events log', 'live-weather-station')); ?></a>)</span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('API plan', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php echo $plan_name ?> (<?php echo InlineHelp::get(-11, '%s', __('get details', 'live-weather-station'));?>)</span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (get_option('live_weather_station_wug_apikey') == '') { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="wug-span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Connecting to service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="connect" id="wug-connect" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="wug-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="wug-span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="disconnect" id="wug-disconnect" class="button button-primary" onclick="lws_wug_confirmation = confirm('<?php echo $warning; ?>'); return lws_wug_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>