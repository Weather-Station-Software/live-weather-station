<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

if (!($mid = filter_input(INPUT_GET, 'mid'))) {
    $mid = filter_input(INPUT_POST, 'mid');
}
if (isset($mid) && $mid) {
    $map = $this->get_map_detail($mid);
    $params = unserialize($map['params']);
    $map_name = $map['name'];
    $map_location = $this->output_coordinate($params['common']['loc_latitude'], 'loc_latitude', 5, true);
    $map_location .= ' ⁛ ' . $this->output_coordinate($params['common']['loc_longitude'], 'loc_longitude', 5, true);
    $map_zoom = $params['common']['loc_zoom'];
    $map_icn = $this->output_iconic_value(0, 'map', false, false, '#999');
    $location_icn = $this->output_iconic_value(0, 'location', false, false, '#999');
    $zoom_icn = $this->output_iconic_value(0, 'zoom', false, false, '#999');
}

?>

<div class="wrap">
    <h1><?php echo sprintf(__('Remove from %s', 'live-weather-station'), LWS_PLUGIN_NAME);?></h1>
    <form method="post" name="remove-map" id="remove-map" action="<?php echo esc_url(lws_get_admin_page_url('lws-maps')); ?>">
        <input name="service" type="hidden" value="map" />
        <input name="tab" type="hidden" value="delete" />
        <input name="action" type="hidden" value="do" />
        <input name="mid" type="hidden" value="<?php echo $mid; ?>" />
        <?php wp_nonce_field('delete-map'); ?>
        <div id="dashboard-widgets" class="metabox-holder" style="width: 100%;clear: both;">
            <div id="postbox-container-1" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables" style="margin:0px">
                    <div id="lws-map" class="postbox " >
                        <button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle"><span>Map</span></h2>
                        <div class="inside">
                            <?php include(LWS_ADMIN_DIR.'partials/MapSummary.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 100%;clear: both;">
            <p><?php echo sprintf(__('Are you sure you want to remove this map from %s?', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
            <p class="submit"><input type="submit" name="delete-map" id="delete-map" class="button button-primary" value="<?php esc_html_e( 'Confirm Removal', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-delete-map" id="donot-delete-map" class="button" value="<?php esc_html_e( 'Cancel Removal', 'live-weather-station' );?>"  />
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Removing this map, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        </div>
    </form>
</div>