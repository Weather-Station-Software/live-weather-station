<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
?>

<form name="visibility" id="visibility" action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="guid" value="<?php echo esc_attr($station['guid']); ?>" />
    <?php wp_nonce_field('edit-station', '_wpnonce', false ); ?>
    <div class="inside" style="padding: 11px;">
        <div class="activity-block" style="padding-bottom: 10px;padding-top: 0px;">
            <fieldset>
                <label>
                    <input name="public_access" id="public_access" type="checkbox" value="1"<?php echo (!empty($station['public_access']) ? ' checked="checked"' : ''); ?>/>
                    <?php echo esc_html__('Public: the visitors of the site can see this station', 'live-weather-station'); ?>
                </label>
                <p class="description"><?php echo esc_html__('When it is not ticked, the visitors see nothing of this station: shortcodes, widgets, maps and public feeds answer nothing. As an administrator, you always see everything.', 'live-weather-station'); ?></p>
            </fieldset>
        </div>
    </div>
    <div id="major-publishing-actions">
        <div id="publishing-action" style="margin-top: -30px;margin-bottom: -26px;">
            <?php echo get_submit_button('', 'primary large', 'submit-visibility'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core helper, the markup is escaped by WordPress ?>
        </div>
        <div class="clear"></div>
    </div>
</form>
