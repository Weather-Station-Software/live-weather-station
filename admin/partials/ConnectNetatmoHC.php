<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

use WeatherStation\System\Output\Guard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '');
$warning = sprintf(/* translators: %s: plugin name */ __('All Healthy Home Coaches associated to this service will be removed from %s.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME);

?>

<?php if (get_option('live_weather_station_netatmohc_connected') == 0) {
    $own_id = (string)get_option('live_weather_station_netatmohc_client_id');
    $own_secret_saved = ((string)get_option('live_weather_station_netatmohc_client_secret') !== '');
    $redirect_uri = \WeatherStation\SDK\Netatmo\Plugin\HCCollector::netatmo_redirect_uri();
?>
<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="service" value="NetatmoHC" />
    <?php wp_nonce_field('NetatmoHC', '_wpnonce', false); ?>
    <?php // Default button of the form (Enter in a field): the connection through Netatmo, not the connection with a token. ?>
    <button type="submit" name="action" value="live_weather_station_netatmo_start" style="display:none" tabindex="-1" aria-hidden="true"></button>
    <div class="inside" style="padding: 11px;">
        <p><?php
            /* translators: 1: name of the service, 2: link to the page of the Netatmo applications */
            $intro = __('Netatmo no longer accepts a login with a password from a plugin. To connect %1$s, create your own application on %2$s, then fill in its keys below.', 'live-weather-station');
            echo wp_kses(sprintf($intro, esc_html('Netatmo Healthy Home Coach'), '<a href="' . esc_url('https://dev.netatmohc.com/apps') . '" target="_blank" rel="noopener noreferrer">dev.netatmohc.com/apps</a>'), array('a' => array('href' => array(), 'target' => array(), 'rel' => array())));
        ?></p>
        <table cellspacing="0" class="lws-settings">
            <tbody>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><label for="netatmohc-client-id"><?php esc_html_e('Client id', 'live-weather-station');?></label></th>
                    <td width="2%"/>
                    <td align="left"><input id="netatmohc-client-id" name="client_id" type="text" size="30" value="<?php echo esc_attr($own_id); ?>" class="regular-text" autocomplete="off" spellcheck="false"></td>
                </tr>
                <tr>
                    <th class="lws-password" width="35%" align="left" scope="row"><label for="netatmohc-client-secret"><?php esc_html_e('Client secret', 'live-weather-station');?></label></th>
                    <td width="2%"/>
                    <td align="left"><input id="netatmohc-client-secret" name="client_secret" type="password" size="30" value="" class="regular-text" autocomplete="off" spellcheck="false"<?php echo ($own_secret_saved ? ' placeholder="' . esc_attr__('Saved: leave empty to keep it', 'live-weather-station') . '"' : ''); ?>></td>
                </tr>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><label for="netatmohc-redirect-uri"><?php esc_html_e('Redirect URI to register in your Netatmo application', 'live-weather-station');?></label></th>
                    <td width="2%"/>
                    <td align="left"><input id="netatmohc-redirect-uri" type="text" size="30" value="<?php echo esc_attr($redirect_uri); ?>" class="regular-text" readonly onfocus="this.select()"></td>
                </tr>
            </tbody>
        </table>
        <details style="margin-top: 14px;">
            <summary><?php esc_html_e('Connect with a refresh token instead', 'live-weather-station');?></summary>
            <p><?php esc_html_e('If you generated a refresh token for your Netatmo application, paste it here and use the second button: no redirection to Netatmo is needed.', 'live-weather-station');?></p>
            <p><label for="netatmohc-refresh-token"><?php esc_html_e('Refresh token', 'live-weather-station');?></label><br/>
            <input id="netatmohc-refresh-token" name="refresh_token" type="password" size="40" value="" class="regular-text" autocomplete="off" spellcheck="false"></p>
            <p><button type="submit" name="action" value="live_weather_station_netatmo_token" id="netatmohc-connect-token" class="button"><?php esc_html_e('Connect with this token', 'live-weather-station');?></button></p>
        </details>
    </div>
    <div id="major-publishing-actions">
        <div id="publishing-action">
            <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                <span id="netatmohc-span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php esc_html_e('Connecting...', 'live-weather-station');?></strong></span>
            </div>
            <button type="submit" name="action" value="live_weather_station_netatmo_start" id="netatmohc-connect" class="button button-primary"><?php esc_html_e('Connect with Netatmo', 'live-weather-station');?></button>
        </div>
        <div class="clear"></div>
    </div>
</form>
<?php } else { ?>
<form action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-settings', null, 'services')); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="action" value="manage-connection" />
    <input type="hidden" name="service" value="NetatmoHC" />
    <input type="hidden" name="option_page" value="services" />
    <?php wp_nonce_field('NetatmoHC', '_wpnonce', true ); ?>
    <div class="inside" style="padding: 11px;">
        <table cellspacing="0" class="lws-settings">
            <tbody>
                <tr>
                    <th class="lws-login" width="35%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                    <td width="2%"/>
                    <td align="left">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $target is the literal attribute string defined at the top of this template (target="_blank" rel="noopener noreferrer") or an empty string ?>
                        <span><?php esc_html_e('Up and running' ,'live-weather-station');?> (<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-events', null, null, 'Netatmo')); ?>"<?php echo $target; ?>><?php echo esc_html(live_weather_station_lcfirst(__('See events log', 'live-weather-station'))); ?></a>)</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
        <div id="major-publishing-actions">
            <div id="publishing-action">
                <input type="submit" name="reconnect" id="netatmohc-reconnect" class="button button-primary" value="<?php esc_attr_e('Change', 'live-weather-station');?>">
                <div id="delete-action" style="text-align: right; padding-right: 14px;height: 0px;">
                    <span id="netatmohc-span-sync" style="display: none;"><i class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo esc_html__('Disconnecting from service, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
                </div>
                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() returns a wp_json_encode() literal with JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT, safe inside an inline script and inside this double-quoted attribute ?>
                <input type="submit" name="disconnect" id="netatmohc-disconnect" class="button button-primary" onclick="lws_netatmohc_confirmation = confirm(<?php echo Guard::js($warning); ?>); return lws_netatmohc_confirmation;" value="<?php esc_attr_e('Disconnect', 'live-weather-station');?>">
            </div>
            <div class="clear"></div>
        </div>
</form>
<?php } ?>
