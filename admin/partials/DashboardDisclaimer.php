<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

$brands = array('Ambient', 'BloomSky', 'Davis', 'Mapbox', 'MapTiler', 'Navionics', 'Netatmo', 'OpenStreetMap', 'OpenWeatherMap', 'Pioupiou', 'Stamen', 'Thunderforest', 'WeatherFlow', 'Windy', 'YoWindow');
$official = sprintf(__('This plugin is not an official software from %s and, as such, is not endorsed or supported by these companies.', 'live-weather-station'), implode (', ', $brands));
$trademarks = __('All brands, icons and graphic illustrations are registered trademarks of their respective owners.', 'live-weather-station');


?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li>
            <?php echo $official;?>
        </li>
        <li>
            <?php echo $trademarks;?>
        </li>
    </ul>
</div>


