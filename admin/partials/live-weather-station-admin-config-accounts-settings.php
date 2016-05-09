<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<?php if (!$status['enabled'] || !$status['o_enabled']) { ?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e('Settings', 'live-weather-station');?></span></h3>
        <form name="lws_conf" id="lws-conf" action="<?php echo esc_url( $this->get_page_url() ); ?>" method="POST">
            <div class="inside">
                <table cellspacing="0" class="lws-settings">
                    <tbody>
                    <?php if (!$status['enabled']) { ?>
                    <tr>
                        <th class="lws-login" width="35%" align="left" scope="row"><?php esc_html_e('Netatmo login', 'live-weather-station');?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login"><input id="login" name="login" type="text" size="20" value="" class="regular-text"></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="lws-password" width="35%" align="left" scope="row"><?php esc_html_e('Netatmo password', 'live-weather-station');?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="password"><input id="password" name="password" type="password" size="20" value="" class="regular-text"></span>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (!$status['o_enabled']) { ?>
                    <tr>
                        <th class="lws-password" width="35%" align="left" scope="row"><?php esc_html_e('OpenWeatherMap key', 'live-weather-station');?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login"><input id="key" name="key" type="text" size="20" value="<?php echo esc_attr( get_option('live_weather_station_owm_account')[0] ); ?>" class="regular-text"></span>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div id="major-publishing-actions">
                <div id="publishing-action">
                    <input type="hidden" name="action" value="set-values">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Connect', 'live-weather-station');?>">
                </div>
                <div class="clear"></div>
            </div>
        </form>
    </div>
</div>
<?php } ?>