<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */

use WeatherStation\System\Help\InlineHelp;
use WeatherStation\UI\ListTable\ColorSchemes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$csListTable = new ColorSchemes();
$csListTable->prepare_items();

?>

<span class="widget_styles-section">
    <h2><?php echo esc_html__('Chart color schemes', 'live-weather-station');?></h2>
    <p><?php echo esc_html__('Custom palettes available for all charts, in addition to standard palettes:', 'live-weather-station');?></p>
    <style>div.tablenav.top, div.tablenav.bottom {display:none;}.widefat th {padding: 8px 10px !important;font-weight: 400 !important;}.row-actions{padding-left: 28px;}</style>
    <?php $csListTable->display();?>
</span>