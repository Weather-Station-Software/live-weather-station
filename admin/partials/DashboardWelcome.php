<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\System\Help\InlineHelp as Help;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$welcome_checked = get_user_meta(get_current_user_id(), 'show_lws_welcome_panel', true);
$welcome = true;

?>

<div id="welcome-panel" class="welcome-panel<?php echo ($welcome_checked ? '' : ' hidden'); ?>" style="padding: 0px">
    <?php wp_nonce_field( 'lws-welcome-panel-nonce', 'lwswelcomepanelnonce', false ); ?>
    <a class="welcome-panel-close" href="#" aria-label="<?php esc_attr_e('Dismiss this welcome panel', 'live-weather-station'); ?>"><?php esc_html_e('Dismiss', 'live-weather-station'); ?></a>
    <div class="welcome-panel-content" style="margin: 0px; padding: 0px; max-width: none;">
        <h2 style="padding: 23px 23px 0;"><?php echo esc_html( sprintf( /* translators: %s: name of the plugin */ __('Welcome to %s!', 'live-weather-station'), LIVE_WEATHER_STATION_FULL_NAME ) ); ?></h2>
        <p style="padding: 0 23px 0;" class="about-description"><?php esc_html_e( 'We\'ve assembled some links to get you started:', 'live-weather-station'); ?></p>
        <div class="welcome-panel-column-container" style="overflow: hidden;">
            <div class="welcome-panel-column" style="padding-left: 23px;margin-right: -23px;">
                <div class="lws-brandicon" >
                    <img style="width:80px;" src="<?php echo esc_attr(set_url_scheme(SVG::get_base64_lws_icon())); ?>" />

                </div>
                <h3><?php esc_html_e('Connect!', 'live-weather-station'); ?></h3>
                <a class="button button-primary button-hero" href="<?php echo esc_url(LIVE_WEATHER_STATION_ADMIN_PHP_URL . '?page=lws-settings&tab=services'); ?>"><?php esc_html_e('Services Settings', 'live-weather-station'); ?></a>
                <br/>&nbsp;<br/>&nbsp;<br/>
            </div>
            <div class="welcome-panel-column" style="padding-left: 23px;margin-right: -50px;">
                <h3><?php esc_html_e('Next steps', 'live-weather-station'); ?></h3>
                <ul>
                    <li><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-fw fa-plus" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="#" class="add-trigger"><?php echo esc_html__('Add a new station', 'live-weather-station');?></a></li>
                    <li><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-fw fa-list-ul" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-stations')); ?>"><?php echo esc_html__('Manage stations', 'live-weather-station');?></a></li>
                </ul>
            </div>
            <div class="welcome-panel-column welcome-panel-last" style="padding-left: 23px;margin-right: -23px;">
                <h3><?php esc_html_e('Go Further', 'live-weather-station'); ?></h3>
                <ul>
                    <ul>
                        <li><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-fw fa-cogs" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-settings')); ?>"><?php echo esc_html__('Adjust settings', 'live-weather-station');?></a></li>
                        <li><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAR );?> fa-lg fa-fw fa-<?php echo LIVE_WEATHER_STATION_FA5?'newspaper':'newspaper-o';?>" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-events')); ?>"><?php echo esc_html__('Browse events log', 'live-weather-station');?></a></li>
                        <li><i class="<?php echo esc_attr( LIVE_WEATHER_STATION_FAS );?> fa-lg fa-fw fa-graduation-cap" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo Help::get(14, '%s', __('Learn more about getting started', 'live-weather-station')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Help::get() builds an anchor from a fixed plugin URL; the anchor text is a plugin translated string ?></li>
                    </ul>
                </ul>
            </div>
            <div class="welcome-panel-column" style="width:100%;">
                <div class="add-text" style="display:none;">
                <div id="wpcom-stats-meta-box-container" class="metabox-holder">
                    <div class="postbox-container" style="width:100%;">
                        <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/ChooseStationType.php'); ?>
                    </div>
                </div>
            </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $(".add-trigger").click(function() {
                        $(".add-text").slideToggle(400);
                    });
                });
            </script>
        </div>
    </div>
</div>

