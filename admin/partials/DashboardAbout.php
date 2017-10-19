<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp as Help;
use WeatherStation\System\Environment\Manager as EnvManager;


?>
<div class="activity-block" style="padding-bottom: 0px; padding-top: 0px;">
    <ul>
        <li>
            <?php echo sprintf(__( '%s is a free and open source plugin for WordPress. It integrates other free and open source works (as-is or modified) like Weather Icons project by Erik Flowers, EnzoJS and SteelSeries by Gerrit Grunwald, JustGage by Bojan Đuričić, OpenWeatherMap PHP API by Christian Flach, phpcolors by Arlo Carreon, moonrise/moonset calculation from Matt "dxprog" Hackmann and moon phase calculation from Samir Shah.', 'live-weather-station'), '<em>' . LWS_PLUGIN_NAME . '</em>');?>
            <?php echo sprintf(__( 'Data manipulation and visualization tools included with %s are free and open source too. Notable tools are d3.js from Mike Bostock, nvd3.js from Novus Partners, Inc. and Cal-heatmap from Wan Qi Chen.', 'live-weather-station'), '<em>' . LWS_PLUGIN_NAME . '</em>');?>
        </li>
        <li>
            <?php echo sprintf(__( 'Is %1$s helpful? I would be pleased that you %2$s.', 'live-weather-station'), '<em>' . LWS_PLUGIN_NAME . '</em>', Help::get(-5, '%s', __('write a review', 'live-weather-station')));?>
            <?php if (EnvManager::stat_rating() > 0) { ?>
                <?php echo ' ' . sprintf(__( 'To date, %1$s users rated %2$s and awarded it %3$s stars out of 5.', 'live-weather-station'), EnvManager::stat_num_ratings(), LWS_PLUGIN_NAME, EnvManager::stat_rating()); ?>
            <?php } ?>
        </li>
        <li>
            <?php echo sprintf(__( 'Thanks to %1$s for its tools and its reactivity, to %2$s for Netatmo tests and kindness from Austria and to %3$s for advice and patience in testing stickertags generator. Also, many thanks to all the translation editors and contributors for making %4$s available and maintained in many languages: girls and guys, you\'re awesome!', 'live-weather-station'), Help::get(-6), Help::get(-7), Help::get(-8), LWS_PLUGIN_NAME);?>
        </li>
    </ul>
</div>


