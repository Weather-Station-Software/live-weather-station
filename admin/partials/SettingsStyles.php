<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\UI\ListTable\ColorSchemes;

$h = InlineHelp::get(20, __('You can find help on these settings on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));
$csListTable = new ColorSchemes();
$csListTable->prepare_items();


?>

<span class="widget_styles-section">
    <p><?php echo __('You can set here all the misc styles options for controls and widgets.', 'live-weather-station') . ' ' . $h; ?></p>
    <p>&nbsp;</p>
    <h2><?php echo __('Widget styles', 'live-weather-station');?></h2>
    <?php do_settings_sections('lws_widget_styles'); ?>
    <p>&nbsp;</p>
    <h2><?php echo __('Chart styles', 'live-weather-station');?></h2>
    <?php do_settings_sections('lws_chart_styles'); ?>
    <p>&nbsp;</p>
    <h2><?php echo __('Chart color schemes', 'live-weather-station');?></h2>
    <p><?php echo __('Custom palettes available for all charts, in addition to standard palettes:', 'live-weather-station');?></p>
    <style>div.tablenav.top, div.tablenav.bottom {display:none;}.widefat th {padding: 8px 10px !important;font-weight: 400 !important;}.row-actions{padding-left: 28px;}</style>
    <?php $csListTable->display();?>
</span>