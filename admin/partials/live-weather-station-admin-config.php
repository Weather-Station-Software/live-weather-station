<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<div class="wrap">
    <h2><?php echo __(LWS_PLUGIN_NAME, 'live-weather-station');?></h2>
    <div>
        <div id="wpcom-stats-meta-box-container" class="metabox-holder">
            <?php
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', true );
            wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', true );
            ?>
            <script type="text/javascript">
                jQuery(document).ready( function($) {
                    jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                    if(typeof postboxes !== 'undefined')
                        postboxes.add_postbox_toggles( 'plugins_page_lws-config' );
                });
            </script>
            <div class="postbox-container" style="width: 35%;margin-right: 10px;">
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-summary.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-accounts-settings.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-options.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-about.php'); ?>
            </div>
            <div class="postbox-container" style="width:64%;">
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-stations.php'); ?>
            </div>
        </div>
    </div>
</div>