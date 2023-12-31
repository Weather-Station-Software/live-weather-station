<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\UI\ListTable\File;

$fileListTable = new File();
$fileListTable->prepare_items();

?>
<div class="wrap">
    <?php if ((bool)get_option('live_weather_station_upload_allowed')) { ?>
        <h2><?php echo __('Export/Import files', 'live-weather-station');?> <a href="#" class="page-title-action add-trigger"><?php echo __('Add', 'live-weather-station'); ?></a></h2>
    <?php } else { ?>
        <h2><?php echo __('Export/Import files', 'live-weather-station');?> </h2>
    <?php } ?>
    <?php settings_errors(); ?>
    <?php if ((bool)get_option('live_weather_station_upload_allowed')) { ?>
        <div class="add-text" style="display:none;">
            <div id="wpcom-stats-meta-box-container" class="metabox-holder">
                <div class="postbox-container" style="width: 100%;margin-right: 10px;">
                    <?php include(LWS_ADMIN_DIR.'partials/ChooseFileToAdd.php'); ?>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php $fileListTable->views(); ?>
    <form id="files-filter" method="get">
        <input type="hidden" name="page" value="lws-files" />
        <?php $fileListTable->display(); ?>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".add-trigger").click(function() {
                $(".add-text").slideToggle(400);
            });
        });
    </script>
</div>