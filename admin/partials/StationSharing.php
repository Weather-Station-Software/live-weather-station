<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$warning = sprintf(__('%s will stop sending data from the station to this service.', 'live-weather-station'), LWS_PLUGIN_NAME);

?>

<form name="<?php echo $service; ?>-share-form" id="<?php echo $service; ?>-share-form" action="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="guid" value="<?php echo $station['guid']; ?>" />
    <?php wp_nonce_field('edit-station', '_wpnonce', false ); ?>
    <div class="inside" style="padding: 11px;">
        <?php if (!$connected) { ?>
            <table cellspacing="0" class="lws-settings">
                <tbody>
                    <tr>
                        <th class="lws-login" width="38%" align="left" scope="row"><?php echo $f1;?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login"><input required id="user" name="user" type="text" size="40" value="<?php echo $user;?>" class="regular-text"></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="lws-password" width="38%" align="left" scope="row"><?php echo $f2;?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="password"><input required id="password" name="password" type="text" size="40" value="<?php echo $password;?>" class="regular-text"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php } else {?>
            <div style="margin-bottom: 10px;">
                <span><i style="color:#999" class="fa fa-lg fa-fw fa-share-alt" aria-hidden="true"></i>&nbsp;<?php echo $shared; ?></span>
            </div>
        <?php } ?>
    </div>
    <?php if (!$connected) { ?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="<?php echo $service; ?>-span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Activating data sharing, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="<?php echo $service; ?>-share" id="<?php echo $service; ?>-share" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } else {?>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="<?php echo $service; ?>-span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Deactivating data sharing, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <input type="submit" name="<?php echo $service; ?>-unshare" id="<?php echo $service; ?>-unshare" class="button button-primary" onclick="lws_<?php echo $service; ?>_confirmation = confirm('<?php echo $warning; ?>'); return lws_<?php echo $service; ?>_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
    <?php } ?>
</form>