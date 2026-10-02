<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
    <h2><?php echo esc_html__('Server Configuration', 'live-weather-station');?></h2>
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
                        postboxes.add_postbox_toggles( 'lws-requirements' );
                });
            </script>
            <div class="postbox-container" style="width: 100%;margin-right: 10px;">
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/DetailedServer.php'); ?>
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/DetailedWebserver.php'); ?>
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/DetailedDB.php'); ?>
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/DetailedWordPress.php'); ?>
            </div>
        </div>
    </div>
</div>