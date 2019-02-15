<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\Utilities\Settings;

$settings = new Settings();
$warning = __('All the maps associated to this service will no longer be displayed.', 'live-weather-station');
$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '');
?>

<form action="<?php echo esc_url(lws_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="Navionics" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('Navionics', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
            <?php if (get_option('live_weather_station_navionics_apikey') == '') { ?>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('API key', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="key" name="key" type="text" size="20" value="" class="regular-text"></span>
                    </td>
                </tr>
            <?php } else {?>
                <tr>
                    <th class="lws-login" width="20%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(lws_get_admin_page_url('lws-events', null, null, 'Navionics')); ?>"<?php echo $target; ?>><?php echo lws_lcfirst(__('See events log', 'live-weather-station')); ?></a>)</span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (get_option('live_weather_station_navionics_apikey') == '') { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="navionics-span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Connecting to service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="connect" id="navionics-connect" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="navionics-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="navionics-span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="disconnect" id="navionics-disconnect" class="button button-primary" onclick="lws_navionics_confirmation = confirm('<?php echo $warning; ?>'); return lws_navionics_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>