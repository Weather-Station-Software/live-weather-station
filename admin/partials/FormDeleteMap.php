<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
// phpcs:disable WordPress.Security.NonceVerification -- read-only lookup, the deletion itself is protected by a nonce
$mid = 0;
if (isset($_GET['mid'])) {
    $mid = absint(wp_unslash($_GET['mid']));
}
if (!$mid && isset($_POST['mid'])) {
    $mid = absint(wp_unslash($_POST['mid']));
}
// phpcs:enable WordPress.Security.NonceVerification
$map = array();
if (isset($mid) && $mid) {
    $map = $this->get_map_detail($mid);
}
if (!is_array($map) || empty($map)) {
    // Unknown map: nothing to show
    wp_die(esc_html__('This map does not exist.', 'live-weather-station'));
}
$params = (isset($map['params']) ? @unserialize($map['params'], array('allowed_classes' => false)) : array());
if (!is_array($params)) {
    $params = array();
}
$params['common'] = (isset($params['common']) && is_array($params['common']) ? $params['common'] : array());
$map_name = (isset($map['name']) ? $map['name'] : '');
$map_location = $this->output_coordinate((isset($params['common']['loc_latitude']) ? $params['common']['loc_latitude'] : 0), 'loc_latitude', 5, true);
$map_location .= ' ⁛ ' . $this->output_coordinate((isset($params['common']['loc_longitude']) ? $params['common']['loc_longitude'] : 0), 'loc_longitude', 5, true);
$map_zoom = (isset($params['common']['loc_zoom']) ? $params['common']['loc_zoom'] : 0);
$map_icn = $this->output_iconic_value(0, 'map', false, false, '#999');
$location_icn = $this->output_iconic_value(0, 'location', false, false, '#999');
$zoom_icn = $this->output_iconic_value(0, 'zoom', false, false, '#999');

?>

<div class="wrap">
    <h1><?php echo wp_kses_post(sprintf(/* translators: %s: plugin name */ __('Remove from %s', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME));?></h1>
    <form method="post" name="remove-map" id="remove-map" action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-maps')); ?>">
        <input name="service" type="hidden" value="map" />
        <input name="tab" type="hidden" value="delete" />
        <input name="action" type="hidden" value="do" />
        <input name="mid" type="hidden" value="<?php echo esc_attr($mid); ?>" />
        <?php wp_nonce_field('delete-map-' . (int)$mid); ?>
        <div id="dashboard-widgets" class="metabox-holder" style="width: 100%;clear: both;">
            <div id="postbox-container-1" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables" style="margin:0px">
                    <div id="lws-map" class="postbox " >
                        <button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
                        <h2 class="hndle"><span>Map</span></h2>
                        <div class="inside">
                            <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/MapSummary.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 100%;clear: both;">
            <p><?php echo wp_kses_post(sprintf(/* translators: %s: plugin name */ __('Are you sure you want to remove this map from %s?', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME));?></p>
            <p class="submit"><input type="submit" name="delete-map" id="delete-map" class="button button-primary" value="<?php esc_html_e( 'Confirm Removal', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-delete-map" id="donot-delete-map" class="button" value="<?php esc_html_e( 'Cancel Removal', 'live-weather-station' );?>"  />
                <span id="span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php esc_html_e('Removing this map, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        </div>
    </form>
</div>