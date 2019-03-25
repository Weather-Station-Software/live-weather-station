<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */


?>

<form name="pages" id="pages" action="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="guid" value="<?php echo $station['guid']; ?>" />
    <?php wp_nonce_field('edit-station', '_wpnonce', false ); ?>
    <div class="inside" style="padding: 11px;">
        <div class="activity-block" style="padding-bottom: 10px;padding-top: 0px;">
            <table cellspacing="0" class="lws-settings">
                <tbody>
                <tr>
                    <th class="lws-link1" width="38%" align="left" scope="row"><?php echo __('Link 1', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="st-link1" name="st-link1" type="text" size="500" value="<?php echo $station['link_1'];?>" class="regular-text"></span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-link2" width="38%" align="left" scope="row"><?php echo __('Link 2', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="st-link2" name="st-link2" type="text" size="500" value="<?php echo $station['link_2'];?>" class="regular-text"></span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-link3" width="38%" align="left" scope="row"><?php echo __('Link 3', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input id="st-link3" name="st-link3" type="text" size="500" value="<?php echo $station['link_3'];?>" class="regular-text"></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div id="major-publishing-actions">
        <div id="publishing-action">
            <?php echo get_submit_button('', 'primary large', 'submit-pages'); ?>
        </div>
        <div class="clear"></div>
    </div>
</form>