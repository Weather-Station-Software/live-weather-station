<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */

$station_name_icn = $this->output_iconic_value(0, 'station_name', false, false, 'style="color:#999"', 'fa-lg');
$location_icn = $this->output_iconic_value(0, 'city', false, false, 'style="color:#999"', 'fa-lg');
$timezone_icn = $this->output_iconic_value(0, 'timezone', false, false, 'style="color:#999"', 'fa-lg');
$histo_icn = $this->output_iconic_value(0, 'historical', false, false, 'style="color:#999"', 'fa-lg');
$static_display = false;
$manage_modules = true;


?>

<div class="wrap">
    <h1><?php echo sprintf(__('Manage modules', 'live-weather-station'), LWS_PLUGIN_NAME);?></h1>
    <form name="manage-modules" id="manage-modules" action="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
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
                    <?php foreach($station['module_detail'] as $module) { ?>
                        <div class="postbox " >
                            <button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                            <h2 class="hndle ui-sortable-handle"><span><?php echo $module['module_name']; ?></span></h2>
                            <div class="inside">
                                <?php include(LWS_ADMIN_DIR.'partials/StationModule.php'); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div style="width: 100%;clear: both;">
            <p class="submit"><input type="submit" name="do-manage-modules" id="do-manage-modules" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
        </div>
    </form>
</div>