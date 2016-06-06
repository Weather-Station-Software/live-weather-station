<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'Summary', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <table cellspacing="0">
                <tbody>
                <?php if ( $status['enabled'] ):?>
                    <tr>
                        <th scope="row" align="left"><?php esc_html_e( 'Netatmo status', 'live-weather-station' );?></th>
                        <td width="5%"/>
                        <td align="left"> <span><?php if ($status['active']) {esc_html_e( 'Up and running' , 'live-weather-station');} else {esc_html_e( 'Connected but inactive' , 'live-weather-station');}?> (<a href="<?php echo esc_url( $this->get_page_url( 'disconnect_netatmo' ) ); ?>"><?php esc_html_e('disconnect', 'live-weather-station'); ?></a><?php if ($status['active']):?> <?php esc_html_e( 'or', 'live-weather-station' );?> <a href="<?php echo esc_url( $this->get_page_url( 'manage_netatmo' ) ); ?>"><?php esc_html_e('manage related services', 'live-weather-station'); ?></a><?php endif;?>)</span></td>
                    </tr>
                    <?php if ( $warning != '' ):?>
                        <tr>
                            <th scope="row" align="left" style="color: #FFCC00;"><?php esc_html_e( 'Netatmo warning', 'live-weather-station' );?></th>
                            <td width="5%"/>
                            <td align="left"> <span><?php echo $warning;?></span></td>
                        </tr>
                    <?php endif;?>
                <?php else: ?>
                    <tr>
                        <th scope="row" align="left"><?php esc_html_e( 'Netatmo status', 'live-weather-station');?></th>
                        <td width="5%"/>
                        <td align="left"> <span><?php esc_html_e( 'Not connected' , 'live-weather-station');?></span></td>
                    </tr>
                    <?php if ( $error != '' ):?>
                        <tr>
                            <th scope="row" align="left" style="color: #FF2222;"><?php esc_html_e( 'Netatmo error', 'live-weather-station' );?></th>
                            <td width="5%"/>
                            <td align="left"> <span><?php echo $error;?></span></td>
                        </tr>
                    <?php endif;?>
                <?php endif;?>

                <?php if ( $oerror == '' ):?>
                    <?php if ( !$status['o_enabled'] ):?>
                        <tr>
                            <th scope="row" align="left"><?php esc_html_e( 'OWM status', 'live-weather-station');?></th>
                            <td width="5%"/>
                            <td align="left"> <span><?php esc_html_e( 'Not connected' , 'live-weather-station');?></span></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th scope="row" align="left"><?php esc_html_e( 'OWM status', 'live-weather-station' );?></th>
                            <td width="5%"/>
                            <td align="left"> <span><?php if ($status['o_active']) {esc_html_e( 'Up and running' , 'live-weather-station');} else {esc_html_e( 'Connected but inactive' , 'live-weather-station');}?> (<a href="<?php echo esc_url( $this->get_page_url( 'disconnect_owm' ) ); ?>"><?php esc_html_e('disconnect', 'live-weather-station'); ?></a><?php if ( LWS_I18N_LOADED ):?> <?php esc_html_e( 'or', 'live-weather-station' );?> <a href="<?php echo esc_url( $this->get_page_url( 'manage_owm' ) ); ?>"><?php esc_html_e('manage stations', 'live-weather-station'); ?></a><?php endif;?>)</span></td>
                        </tr>
                    <?php endif;?>
                    <?php if ( $owarning != '' ):?>
                        <tr>
                            <th scope="row" align="left" style="color: #FFCC00;"><?php esc_html_e( 'OWM warning', 'live-weather-station' );?></th>
                            <td width="5%"/>
                            <td align="left"> <span><?php echo $owarning;?></span></td>
                        </tr>
                    <?php endif;?>
                <?php else: ?>
                    <tr>
                        <th scope="row" align="left"><?php esc_html_e( 'OWM status', 'live-weather-station');?></th>
                        <td width="5%"/>
                        <td align="left"> <span><?php esc_html_e( 'Not connected' , 'live-weather-station');?></span></td>
                    </tr>
                    <tr>
                        <th scope="row" align="left" style="color: #FF2222;"><?php esc_html_e( 'OWM error', 'live-weather-station' );?></th>
                        <td width="5%"/>
                        <td align="left"> <span><?php echo $oerror;?></span></td>
                    </tr>
                <?php endif;?>
                <tr>
                    <th scope="row" align="left"><?php esc_html_e( 'Version', 'live-weather-station');?></th>
                    <td width="5%"/>
                    <td align="left"> <span><?php echo $status['version'] ;?></span></td>
                </tr>
                <?php if ( !LWS_I18N_LOADED ):?>
                    <tr>
                        <th scope="row" align="left" style="color: #FFCC00;"><?php esc_html_e( 'PHP warning', 'live-weather-station' );?></th>
                        <td width="5%"/>
                        <td align="left"> <span><?php esc_html_e( 'Internationalisation extension is not installed. You can not manage OWM stations...', 'live-weather-station' );?></span></td>
                    </tr>
                <?php endif;?>
                </tbody>
            </table>
        </div>
        <div id="major-publishing-actions">
            <div>
                <a href="<?php echo esc_url( $this->get_page_url('list_logs') ); ?>"><?php esc_html_e('Events log', 'live-weather-station'); ?></a>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>