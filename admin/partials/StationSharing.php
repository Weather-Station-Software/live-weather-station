<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Output\Guard;

$service = Guard::token($service, '');
$warning = sprintf(__('%s will stop sending data from the station to this service.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);

?>

<form name="<?php echo esc_attr($service); ?>-share-form" id="<?php echo esc_attr($service); ?>-share-form" action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="guid" value="<?php echo esc_attr($station['guid']); ?>" />
    <?php wp_nonce_field('edit-station', '_wpnonce', false ); ?>
    <div class="inside" style="padding: 11px;">
        <?php if (!$connected) { ?>
            <table cellspacing="0" class="lws-settings">
                <tbody>
                    <tr>
                        <th class="lws-login" width="38%" align="left" scope="row"><?php echo esc_html($f1);?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login"><input required id="user" name="user" type="text" size="40" value="<?php echo esc_attr($user);?>" class="regular-text"></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="lws-password" width="38%" align="left" scope="row"><?php echo esc_html($f2);?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="password"><input required id="password" name="password" type="password" size="40" value="" autocomplete="new-password" class="regular-text"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php } else {?>
            <div style="margin-bottom: 10px;">
                <span><i style="color:#999" class="<?php echo LIVE_WEATHER_STATION_FAS;?> fa-lg fa-fw fa-share-alt" aria-hidden="true"></i>&nbsp;<?php echo wp_kses_post($shared); ?></span>
            </div>
        <?php } ?>
    </div>
    <?php if (!$connected) { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="<?php echo esc_attr($service); ?>-span-sync" style="display: none;"><i class="<?php echo LIVE_WEATHER_STATION_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Activating data sharing, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="<?php echo esc_attr($service); ?>-share" id="<?php echo esc_attr($service); ?>-share" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="<?php echo esc_attr($service); ?>-span-sync" style="display: none;"><i class="<?php echo LIVE_WEATHER_STATION_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Deactivating data sharing, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="<?php echo esc_attr($service); ?>-unshare" id="<?php echo esc_attr($service); ?>-unshare" class="button button-primary" onclick="lws_<?php echo esc_attr($service); ?>_confirmation = confirm(<?php echo Guard::js($warning); ?>); return lws_<?php echo esc_attr($service); ?>_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>