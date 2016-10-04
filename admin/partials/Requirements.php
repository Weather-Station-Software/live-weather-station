<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
?>
<div class="wrap">
    <h2><?php echo __('Requirements', 'live-weather-station');?></h2>
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
                <?php include(LWS_ADMIN_DIR.'partials/DetailedRequirements.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/PhpInfo.php'); ?>
            </div>
        </div>
    </div>
</div>