<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */

$station_name_icn = $this->output_iconic_value(0, 'station_name', false, false, 'style="color:#999"', 'fa-lg fa-fw');
$location_icn = $this->output_iconic_value(0, 'city', false, false, 'style="color:#999"', 'fa-lg fa-fw');
$timezone_icn = $this->output_iconic_value(0, 'timezone', false, false, 'style="color:#999"', 'fa-lg fa-fw');
$histo_icn = $this->output_iconic_value(0, 'historical', false, false, 'style="color:#999"', 'fa-lg fa-fw');
$static_display = false;
$manage_modules = true;
$constraint_range = true;
$formats = $export_formats;
$show_override = false;
$show_files = false;

?>

<div class="wrap">
    <h1><?php echo lws__('Export historical data', 'live-weather-station');?></h1>
    <form name="export-data" id="export-data" action="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
        <input type="hidden" name="guid" value="<?php echo $station['guid']; ?>" />
        <?php wp_nonce_field('edit-station'); ?>
        <div id="dashboard-widgets" class="metabox-holder" style="width: 100%;clear: both;">
            <div id="postbox-container-1" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable" style="margin:0px">
                    <div id="lws-station" class="postbox" >
                        <button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle ui-sortable-handle"><span>Station</span></h2>
                        <div class="inside">
                            <?php include(LWS_ADMIN_DIR.'partials/StationStation.php'); ?>
                        </div>
                    </div>
                    <div id="lws-date-range" class="postbox" >
                        <button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e_lws__('Date range', 'live-weather-station');?></span></h2>
                        <div class="inside">
                            <?php include(LWS_ADMIN_DIR.'partials/ChooseDateRange.php'); ?>
                        </div>
                    </div>
                    <div id="lws-format-select" class="postbox" >
                        <button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e_lws__('Format', 'live-weather-station');?></span></h2>
                        <div class="inside">
                            <?php include(LWS_ADMIN_DIR.'partials/ChooseFormat.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 100%;clear: both;">
            <p class="submit"><input type="submit" name="do-export-data" id="do-export-data" class="button button-primary" value="<?php esc_html_e_lws__('Export Data', 'live-weather-station');?>"  /> &nbsp;&nbsp;&nbsp;
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station');?></a>
        </div>
    </form>
</div>