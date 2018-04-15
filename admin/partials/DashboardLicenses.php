<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp as Help;

?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li>
            <strong>PiouPiou</strong><br/>
            <?php echo sprintf(__('All wind data provided by the Pioupiou network are %1$s.', 'live-weather-station'), Help::get(-26));?> <?php echo __( 'If you use on your site data provided by the Pioupiou network, you must give credit and provide a link to the Pioupiou website on pages where data are shown.', 'live-weather-station');?>
        </li>
        <li>
            <strong>OpenWeatherMap</strong><br/>
            <?php echo sprintf(__( 'All meteorological data provided by OpenWeatherMap are distributed under the terms of the %1$s.', 'live-weather-station'), Help::get(-9));?> <?php echo __( 'If you use OpenWeatherMap data on your site, the name of OpenWeatherMap must be mentioned as a weather source on pages where data are shown.', 'live-weather-station');?>
        </li>
    </ul>
</div>


