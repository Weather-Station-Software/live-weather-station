<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

$active_tab = (isset($_GET['tab']) ? $_GET['tab'] : 'view');
$view = array('action'=>'manage', 'tab'=>'view', 'service'=>'station');
$current = array('action'=>'shortcode', 'tab'=>'current', 'service'=>'station');
$daily = array('action'=>'shortcode', 'tab'=>'daily', 'service'=>'station');
$yearly = array('action'=>'shortcode', 'tab'=>'yearly', 'service'=>'station');


?>

<h2 class="nav-tab-wrapper">
    <a href="<?php echo lws_re_get_admin_page_url($view); ?>" class="nav-tab <?php echo $active_tab == 'view' ? 'nav-tab-active' : ''; ?>"><?php echo __('Station details', 'live-weather-station');?></a>
    <a href="<?php echo lws_re_get_admin_page_url($current); ?>" class="nav-tab <?php echo $active_tab == 'current' ? 'nav-tab-active' : ''; ?>"><?php echo __('Current records', 'live-weather-station');?></a>
    <a href="<?php echo lws_re_get_admin_page_url($daily); ?>" class="nav-tab <?php echo $active_tab == 'daily' ? 'nav-tab-active' : ''; ?>"><?php echo __('Daily data', 'live-weather-station');?></a>
    <a href="<?php echo lws_re_get_admin_page_url($yearly); ?>" class="nav-tab <?php echo $active_tab == 'yearly' ? 'nav-tab-active' : ''; ?>"><?php echo __('Historical data', 'live-weather-station');?></a>
</h2>