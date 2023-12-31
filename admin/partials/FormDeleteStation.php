<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$station_name_icn = $this->output_iconic_value(0, 'station_name', false, false, '#999');
$location_icn = $this->output_iconic_value(0, 'city', false, false, '#999');
$timezone_icn = $this->output_iconic_value(0, 'timezone', false, false, '#999');
$histo_icn = $this->output_iconic_value(0, 'historical', false, false, '#999');


?>

<div class="wrap">
    <h1><?php echo sprintf(__('Remove from %s', 'live-weather-station'), LWS_PLUGIN_NAME);?></h1>
    <form method="post" name="remove-station" id="remove-station" action="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>">
        <input name="service" type="hidden" value="station" />
        <input name="tab" type="hidden" value="delete" />
        <input name="action" type="hidden" value="do" />
        <input name="id" type="hidden" value="<?php echo $station['guid']; ?>" />
        <?php wp_nonce_field('delete-station'); ?>
        <div id="dashboard-widgets" class="metabox-holder" style="width: 100%;clear: both;">
            <div id="postbox-container-1" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables" style="margin:0px">
                    <div id="lws-station" class="postbox " >
                        <button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle"><span>Station</span></h2>
                        <div class="inside">
                            <?php include(LWS_ADMIN_DIR.'partials/StationStation.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 100%;clear: both;">
            <p><?php echo sprintf(__('Are you sure you want to remove this station, and all its data, from %s?', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
            <p class="submit"><input type="submit" name="delete-station" id="delete-station" class="button button-primary" value="<?php esc_html_e( 'Confirm Removal', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-delete-station" id="donot-delete-station" class="button" value="<?php esc_html_e( 'Cancel Removal', 'live-weather-station' );?>"  />
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Removing this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        </div>
    </form>
</div>