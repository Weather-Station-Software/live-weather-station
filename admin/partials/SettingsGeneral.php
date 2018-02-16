<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\System\I18N\Handling as i18n;

$i18n = new i18n();


?>

<?php if ((bool)get_option('live_weather_station_advanced_mode')) { ?>
    <p>&nbsp;</p>
    <p>
        <?php echo sprintf(__('%s runs currently in extended mode. If you want to make your life easier, switch to simplified mode.', 'live-weather-station'), LWS_PLUGIN_NAME);?><br/>
        <em><?php echo __('Note: if you choose the simplified mode, all settings (like display options, units, etc.) will be automatically set for you.', 'live-weather-station');?></em>
    </p>
    <p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'switch-simplified')); ?>"><?php echo __('Switch to Simplified Mode', 'live-weather-station');?></a></p>
<?php } else { ?>
    <p>&nbsp;</p>
    <p><?php echo sprintf(__('%s runs currently in simplified mode. If you want to access all the available settings, you must switch to extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
    <p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'switch-extended')); ?>"><?php echo __('Switch to Extended Mode', 'live-weather-station');?></a></p>
    <?php if ((0 == get_option('live_weather_station_unit_temperature'))) { ?>
        <p>&nbsp;</p>
        <p><?php echo sprintf(__('The data displayed by %s are in <em>metric units</em>. If that does not suit your needs, you can choose imperial units.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
        <p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'switch-imperial')); ?>"><?php echo __('Display Data in Imperial Units', 'live-weather-station');?></a></p>
    <?php } else { ?>
        <p>&nbsp;</p>
        <p><?php echo sprintf(__('The data displayed by %s are in <em>imperial units</em>. If that does not suit your needs, you can choose metric units.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
        <p><a class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'switch-metric')); ?>"><?php echo __('Display Data in Metric Units', 'live-weather-station');?></a></p>
    <?php } ?>
<?php } ?>

<?php if ($i18n->is_translatable()) { ?>
    <p>&nbsp;</p>
    <?php if ((bool)get_option('live_weather_station_partial_translation')) { ?>
        <p>
            <?php echo sprintf(__('Currently, %s use a partial translation of your language. If you do not like half finished things, click the button below:', 'live-weather-station'), LWS_PLUGIN_NAME);?>
        </p>
        <p><a id="partial-translation" class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'switch-full-translation')); ?>"><?php echo __('Use Only Full Translation', 'live-weather-station');?></a>
            <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Deactivating partial translation, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
    <?php } else { ?>
        <p>
            <?php echo sprintf(__('Currently, %s is not displayed in your language. But, there is a partial translation that can be used!', 'live-weather-station'), LWS_PLUGIN_NAME);?>
        </p>
        <p><a id="partial-translation" class="button button-primary" href="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'switch-partial-translation')); ?>"><?php echo __('Use Partial Translation', 'live-weather-station');?></a>
            <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Activating partial translation, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
    <?php } ?>
<?php } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p><?php echo InlineHelp::get(0, __('You can find help on the general settings on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));?></p>

