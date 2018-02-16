<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\System\Help\InlineHelp as Help;

$welcome_checked = get_user_meta(get_current_user_id(), 'show_lws_welcome_panel', true);
$welcome = true;

?>

<div id="welcome-panel" class="welcome-panel<?php echo ($welcome_checked ? '' : ' hidden'); ?>" style="padding: 0px">
    <?php wp_nonce_field( 'lws-welcome-panel-nonce', 'lwswelcomepanelnonce', false ); ?>
    <a class="welcome-panel-close" href="#" aria-label="<?php esc_attr_e('Dismiss this welcome panel', 'live-weather-station'); ?>"><?php _e('Dismiss', 'live-weather-station'); ?></a>
    <div class="welcome-panel-content" style="margin: 0px; padding: 0px; max-width: none;">
        <h2 style="padding: 23px 23px 0;"><?php echo sprintf(__('Welcome to %s!', 'live-weather-station'), LWS_FULL_NAME); ?></h2>
        <p style="padding: 0 23px 0;" class="about-description"><?php _e( 'We\'ve assembled some links to get you started:', 'live-weather-station'); ?></p>
        <div class="welcome-panel-column-container" style="overflow: hidden;">
            <div class="welcome-panel-column" style="padding-left: 23px;margin-right: -23px;">
                <img style="width:120px;float:left;margin-top:16px;margin-bottom:20px;" src="<?php echo set_url_scheme(SVG::get_base64_lws_icon()); ?>" />
                <h3><?php _e('Connect!', 'live-weather-station'); ?></h3>
                <a class="button button-primary button-hero" href="<?php echo LWS_ADMIN_PHP_URL; ?>?page=lws-settings&tab=services"><?php _e('Services Settings', 'live-weather-station'); ?></a>
                <br/>&nbsp;<br/>&nbsp;<br/>
            </div>
            <div class="welcome-panel-column" style="padding-left: 23px;margin-right: -50px;">
                <h3><?php _e('Next steps', 'live-weather-station'); ?></h3>
                <ul>
                    <li><i class="fa fa-lg fa-fw fa-plus" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="#" class="add-trigger"><?php echo __('Add a new station', 'live-weather-station');?></a></li>
                    <li><i class="fa fa-lg fa-fw fa-list-ul" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo lws_get_admin_page_url('lws-stations'); ?>"><?php echo __('Manage stations', 'live-weather-station');?></a></li>
                </ul>
            </div>
            <div class="welcome-panel-column welcome-panel-last" style="padding-left: 23px;margin-right: -23px;">
                <h3><?php _e('Go Further', 'live-weather-station'); ?></h3>
                <ul>
                    <ul>
                        <li><i class="fa fa-lg fa-fw fa-cogs" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo lws_get_admin_page_url('lws-settings'); ?>"><?php echo __('Adjust settings', 'live-weather-station');?></a></li>
                        <li><i class="fa fa-lg fa-fw fa-newspaper-o" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo lws_get_admin_page_url('lws-events'); ?>"><?php echo __('Browse events log', 'live-weather-station');?></a></li>
                        <li><i class="fa fa-lg fa-fw fa-graduation-cap" style="color:#888;" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo Help::get(14, '%s', __('Learn more about getting started', 'live-weather-station'));?></li>
                    </ul>
                </ul>
            </div>
            <div class="welcome-panel-column" style="width:100%;">
                <div class="add-text" style="display:none;">
                <div id="wpcom-stats-meta-box-container" class="metabox-holder">
                    <div class="postbox-container" style="width:100%;">
                        <?php include(LWS_ADMIN_DIR.'partials/ChooseStationType.php'); ?>
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

