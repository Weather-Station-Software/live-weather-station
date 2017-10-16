<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */


?>

<p>&nbsp;</p>
<hr/>
<div class="wrap">
    <h2><?php echo __('Current settings', 'live-weather-station');?></h2>
    <p><?php echo sprintf(__('The current settings allow %s to store, manipulate and display the following data:', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
    <div>
        <div id="wpcom-stats-meta-box-container" class="metabox-holder">
            <div class="postbox-container" style="width: 100%;margin-right: 10px;">
                <?php include(LWS_ADMIN_DIR.'partials/DetailedHistoryStandard.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/DetailedHistoryExtended.php'); ?>
            </div>
        </div>
    </div>
</div>