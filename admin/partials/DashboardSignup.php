<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$email = get_option('admin_email');

?>



<form name="subscribe-form" id="subscribe-form" action="<?php echo esc_url(live_weather_station_get_admin_page_url()); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="action" value="subscribe" />
    <?php wp_nonce_field('subscribe', '_wpnonce', false ); ?>
    <p>
        <i style="color:#999;" class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAR );?> fa-<?php echo LIVE_WEATHER_STATION_FA5?'envelope':'envelope-o';?>"></i>&nbsp;&nbsp;
        <?php echo esc_html( sprintf( /* translators: %s: name of the plugin */ __('Receive the latest news and updates from %s.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME) );?>&nbsp;&nbsp;
    </p>
    <p>
        <input required id="email" name="email" type="email" value="<?php echo esc_attr($email);?>" style="width:70%">&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="submit" name="subscribe-submit" id="subscribe-submit" class="button" value="<?php esc_attr_e('Subscribe', 'live-weather-station');?>">
    </p>
    <p>
        <i><?php echo esc_html( sprintf( /* translators: %s: name of the plugin */ __('Your email address is sacred. It will not be sold or ceded. It will only be used, via MailChimp services, to send you news from %s.', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME) );?></i>
    </p>
</form>
