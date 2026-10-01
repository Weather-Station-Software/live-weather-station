<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selection restricted to an allowlist just below, no state change
$active_tab = (isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general');
if (!in_array($active_tab, array('general', 'services', 'display', 'styles', 'thresholds', 'history', 'system', 'maintenance', 'tasks'), true)) {
    $active_tab = 'general';
}
$buttons = str_replace('</p>', '', get_submit_button()) . ' &nbsp;&nbsp;&nbsp; ' . str_replace('<p class="submit">', '', get_submit_button(__('Reset to Defaults', 'live-weather-station'), 'secondary', 'reset'));


?>

<div class="wrap">

    <h2><?php esc_html_e('Settings', 'live-weather-station');?></h2>
    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=lws-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General', 'live-weather-station');?></a>
        <a href="?page=lws-settings&tab=services" class="nav-tab <?php echo $active_tab == 'services' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Services', 'live-weather-station');?></a>
        <?php if ((bool)get_option('live_weather_station_advanced_mode')) { ?>
            <a href="?page=lws-settings&tab=display" class="nav-tab <?php echo $active_tab == 'display' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Display', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=styles" class="nav-tab <?php echo $active_tab == 'styles' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Styles', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=thresholds" class="nav-tab <?php echo $active_tab == 'thresholds' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Thresholds', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=history" class="nav-tab <?php echo $active_tab == 'history' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('History', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=system" class="nav-tab <?php echo $active_tab == 'system' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('System', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=maintenance" class="nav-tab <?php echo $active_tab == 'maintenance' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Maintenance', 'live-weather-station');?></a>
        <?php } ?>
    </h2>

    <form action="<?php echo esc_url(live_weather_station_get_admin_page_url('lws-settings', null, $active_tab)); ?>" method="POST">
        <?php if ($active_tab !== 'general' && $active_tab !== 'services' && $active_tab !== 'maintenance' && $active_tab !== 'tasks') { ?>
            <?php do_settings_sections('lws_'.$active_tab); ?>
            <?php settings_fields($active_tab);?>
            <?php if ($active_tab === 'styles') { ?>
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/SettingsStyles.php'); ?>
            <?php } ?>
            <?php echo $buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buttons HTML built from the WordPress core function that renders submit buttons (escaped by core), the Reset label is a translated string ?>
        <?php } ?>
    </form>
    <?php if ($active_tab === 'general') { ?>
        <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/SettingsGeneral.php'); ?>
    <?php } ?>
    <?php if ($active_tab === 'services') { ?>
        <?php $this->_services->get(); ?>
    <?php } ?>
    <?php if ($active_tab === 'maintenance') { ?>
        <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/SettingsMaintenance.php'); ?>
    <?php } ?>
    <?php if ($active_tab === 'history') { ?>
        <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/SettingsHistory.php'); ?>
    <?php } ?>

</div>