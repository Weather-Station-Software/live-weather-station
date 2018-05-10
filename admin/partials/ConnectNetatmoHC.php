<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '');
$warning = sprintf(__('All Healthy Home Coaches associated to this service will be removed from %s.', 'live-weather-station'), LWS_PLUGIN_NAME);

?>

<form action="<?php echo esc_url(lws_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="NetatmoHC" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('NetatmoHC', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
            <?php if (get_option('live_weather_station_netatmohc_connected') == 0) { ?>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><?php esc_html_e('Login', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="login" name="login" type="text" size="20" value="" class="regular-text"></span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-password" width="35%" align="left" scope="row"><?php esc_html_e('Password', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="password"><input id="password" name="password" type="password" size="20" value="" class="regular-text"></span>
                    </td>
                </tr>
            <?php } else {?>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(lws_get_admin_page_url('lws-events', null, null, 'Netatmo')); ?>"<?php echo $target; ?>><?php echo strtolower(__('See events log', 'live-weather-station')); ?></a>)</span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (get_option('live_weather_station_netatmohc_connected') == 0) { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="netatmohc-span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Connecting to service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="connect" id="netatmohc-connect" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="netatmohc-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="netatmohc-span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="disconnect" id="netatmohc-disconnect" class="button button-primary" onclick="lws_netatmohc_confirmation = confirm('<?php echo $warning; ?>'); return lws_netatmohc_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>