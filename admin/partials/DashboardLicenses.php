<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp as Help;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li>
            <strong>PiouPiou</strong><br/>
            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Help::get(-26) builds an anchor from a literal URL and the literal text 'Open Data' plus I18nHelper::get_language_markup() (fixed span with inline style, which wp_kses_post() would alter); the sentence is a plugin translation ?>
            <?php echo sprintf(/* translators: %1$s: name of the license, with a link */ __('All wind data provided by the Pioupiou network are %1$s.', 'live-weather-station'), Help::get(-26));?> <?php echo esc_html__( 'If you use on your site data provided by the Pioupiou network, you must give credit and provide a link to the Pioupiou website on pages where data are shown.', 'live-weather-station');?>
        </li>
        <li>
            <strong>OpenWeatherMap</strong><br/>
            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Help::get(-9) builds an anchor from a literal URL and a translated license name plus I18nHelper::get_language_markup() (fixed span with inline style, which wp_kses_post() would alter); the sentence is a plugin translation ?>
            <?php echo sprintf(/* translators: %1$s: name of the license, with a link */ __( 'All meteorological data provided by OpenWeatherMap are distributed under the terms of the %1$s.', 'live-weather-station'), Help::get(-9));?> <?php echo esc_html__( 'If you use OpenWeatherMap data on your site, the name of OpenWeatherMap must be mentioned as a weather source on pages where data are shown.', 'live-weather-station');?>
        </li>
    </ul>
</div>


