<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$brands = array('Ambient', 'BloomSky', 'Davis', 'Mapbox', 'MapTiler', 'Netatmo', 'OpenStreetMap', 'OpenWeatherMap', 'Pioupiou', 'Stadia Maps', 'Stamen', 'Thunderforest', 'WeatherFlow', 'Windy', 'YoWindow');
$official = sprintf(/* translators: %s: comma separated list of company and product names */ __('This plugin is not an official software from %s and, as such, is not endorsed or supported by these companies.', 'live-weather-station'), implode (', ', $brands));
$trademarks = __('All brands, icons and graphic illustrations are registered trademarks of their respective owners.', 'live-weather-station');


?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li>
            <?php echo esc_html($official);?>
        </li>
        <li>
            <?php echo esc_html($trademarks);?>
        </li>
    </ul>
</div>


