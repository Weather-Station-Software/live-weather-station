<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$active_tab = (isset($_GET['tab']) ? $_GET['tab'] : 'general');
$buttons = str_replace('</p>', '', get_submit_button()) . ' &nbsp;&nbsp;&nbsp; ' . str_replace('<p class="submit">', '', get_submit_button(__('Reset to Defaults', 'live-weather-station'), 'secondary', 'reset'));


?>

<div class="wrap">

    <h2><?php echo __('Settings', 'live-weather-station');?></h2>
    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=lws-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php echo __('General', 'live-weather-station');?></a>
        <a href="?page=lws-settings&tab=services" class="nav-tab <?php echo $active_tab == 'services' ? 'nav-tab-active' : ''; ?>"><?php echo __('Services', 'live-weather-station');?></a>
        <?php if ((bool)get_option('live_weather_station_advanced_mode')) { ?>
            <a href="?page=lws-settings&tab=display" class="nav-tab <?php echo $active_tab == 'display' ? 'nav-tab-active' : ''; ?>"><?php echo __('Display', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=thresholds" class="nav-tab <?php echo $active_tab == 'thresholds' ? 'nav-tab-active' : ''; ?>"><?php echo __('Thresholds', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=history" class="nav-tab <?php echo $active_tab == 'history' ? 'nav-tab-active' : ''; ?>"><?php echo __('History', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=system" class="nav-tab <?php echo $active_tab == 'system' ? 'nav-tab-active' : ''; ?>"><?php echo __('System', 'live-weather-station');?></a>
            <a href="?page=lws-settings&tab=maintenance" class="nav-tab <?php echo $active_tab == 'maintenance' ? 'nav-tab-active' : ''; ?>"><?php echo __('Maintenance', 'live-weather-station');?></a>
            <?php if ((bool)get_option('live_weather_station_show_tasks')) { ?>
                <a href="?page=lws-settings&tab=tasks" class="nav-tab <?php echo $active_tab == 'tasks' ? 'nav-tab-active' : ''; ?>"><?php echo __('Scheduled tasks', 'live-weather-station');?></a>
            <?php } ?>
        <?php } ?>
    </h2>

    <form action="<?php echo esc_url(lws_get_admin_page_url('lws-settings', null, $active_tab)); ?>" method="POST">
        <?php do_settings_sections('lws_'.$active_tab); ?>
        <?php if ($active_tab != 'general' && $active_tab != 'services' && $active_tab != 'maintenance' && $active_tab != 'tasks') { ?>
            <?php settings_fields($active_tab);?>
            <?php echo $buttons;?>
        <?php } ?>
    </form>
    <?php if ($active_tab == 'general') { ?>
        <?php include(LWS_ADMIN_DIR.'partials/SettingsGeneral.php'); ?>
    <?php } ?>
    <?php if ($active_tab == 'services') { ?>
        <?php $this->_services->get(); ?>
    <?php } ?>
    <?php if ($active_tab == 'maintenance') { ?>
        <?php include(LWS_ADMIN_DIR.'partials/SettingsMaintenance.php'); ?>
    <?php } ?>
    <?php if ($active_tab == 'tasks') { ?>
        <?php include(LWS_ADMIN_DIR.'partials/SettingsTasks.php'); ?>
    <?php } ?>
    <?php if ($active_tab == 'history') { ?>
        <?php include(LWS_ADMIN_DIR.'partials/SettingsHistory.php'); ?>
    <?php } ?>

</div>