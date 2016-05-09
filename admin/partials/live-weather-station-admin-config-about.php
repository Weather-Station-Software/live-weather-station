<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'About', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <table cellspacing="0">
                <tbody>
                <tr>
                    <td align="left"><span><strong><?php esc_html_e( 'Help', 'live-weather-station');?></strong></span></td>
                </tr>
                <tr>
                    <td align="left"><span><?php echo __( 'You can find help and explanation about this plugin on <a href="https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/">this page</a>.', 'live-weather-station');?></span><br/>&nbsp;</td>
                </tr>
                <tr>
                    <td align="left"><span><strong><?php esc_html_e( 'Plugin', 'live-weather-station');?></strong></span></td>
                </tr>
                <tr>
                    <td align="left"> <span><?php echo __( 'Is <em>Live Weather Station</em> helpful? I would be pleased that you <a href="https://wordpress.org/support/view/plugin-reviews/live-weather-station">write a review</a>.', 'live-weather-station');?></span><br/>&nbsp;</td>
                </tr>
                <tr>
                    <td align="left"><span><strong><?php esc_html_e( 'Translations', 'live-weather-station');?></strong></span></td>
                </tr>
                <tr>
                    <td align="left"> <span><?php echo __( 'If you want to help to translate this plugin, you can do it with  <a href="https://translate.wordpress.org/projects/wp-plugins/live-weather-station">GlotPress</a> (please, use the \'stable\' column).', 'live-weather-station');?></span><br/>&nbsp;</td>
                </tr>
                <tr>
                    <td align="left"><span><strong><?php esc_html_e( 'Thanks to', 'live-weather-station');?></strong></span></td>
                </tr>
                <tr>
                    <td align="left"> <span><?php echo __( '<a href="https://twitter.com/cyril_lakech">Cyril Lakech</a>, for its tools and its reactivity and <a href="https://profiles.wordpress.org/bergjet">Martin Punz</a> for tests and kindness from Austria.', 'live-weather-station');?></span><br/>&nbsp;</td>
                </tr>
                <tr>
                    <td align="left"><span><strong><?php esc_html_e( 'About', 'live-weather-station');?></strong></span></td>
                </tr>
                <tr>
                    <td align="left"> <span><?php echo __( '<em>Live Weather Station</em> is a free and open source plugin for WordPress. It integrates other free and open source works (as-is or modified) like Weather Icons project by Erik Flowers, EnzoJS and SteelSeries by Gerrit Grunwald, JustGage by Bojan Đuričić, OpenWeatherMap PHP API by Christian Flach, phpcolors by Arlo Carreon, moonrise/moonset calculation from Matt "dxprog" Hackmann and moon phase calculation from Samir Shah.', 'live-weather-station');?></span><br/>&nbsp;</td>
                </tr>
                <tr>
                    <td align="left"><span><strong><?php esc_html_e( 'Data', 'live-weather-station');?></strong></span></td>
                </tr>
                <tr>
                    <td align="left"> <span><?php echo __( 'All meteorological data provided by OpenWeatherMap are distributed under the terms of the <a href="http://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons CC:BY-SA 4.0 license</a>.', 'live-weather-station');?><br/><?php echo __( 'If you use OpenWeatherMap data on your site, the name of OpenWeatherMap must be mentioned as a weather source on pages where data are shown.', 'live-weather-station');?></span><br/>&nbsp;</td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>