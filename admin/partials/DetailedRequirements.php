<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo esc_attr__('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo esc_html(sprintf(/* translators: %s: name of the plugin */ __('%s can\'t run on your WordPress site', 'live-weather-station' ), LIVE_WEATHER_STATION_PLUGIN_NAME));?></span></h3>
        <div class="inside">
            <strong><?php echo esc_html(sprintf(/* translators: %s: name of the plugin */ __('The PHP configuration of your server doesn\'t meet the minimal requirements needed to run %s. Please, see below to identify which PHP extension must be installed:', 'live-weather-station'), LIVE_WEATHER_STATION_PLUGIN_NAME));?></strong>
            <br/>
            <table cellspacing="10" width="99%">
                <tbody>
                    <tr>
                        <?php if (LIVE_WEATHER_STATION_PHPVERSION_OK) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-check-circle"></i></td>
                            <td><?php echo esc_html__('PHP version is greater than or equal to 5.6.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-minus-circle"></i></td>
                            <td><?php echo esc_html__('PHP version is lower than 5.6.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if (LIVE_WEATHER_STATION_I18N_LOADED) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-check-circle"></i></td>
                            <td><?php echo esc_html__('Internationalization support is installed.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-minus-circle"></i></td>
                            <td><?php echo esc_html__('Internationalization support is not installed.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if (LIVE_WEATHER_STATION_JSON_LOADED) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-check-circle"></i></td>
                            <td><?php echo esc_html__('JSON support is installed.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="<?php echo esc_attr(LIVE_WEATHER_STATION_FAS);?> fa-lg fa-minus-circle"></i></td>
                            <td><?php echo esc_html__('JSON support is not installed.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="major-publishing-actions">
            <div>
                <?php echo InlineHelp::get(11, /* translators: %s: link to the detailed requirements page */ __('You can find detailed requirements on %s.', 'live-weather-station'), __('this page', 'live-weather-station')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- InlineHelp::get() builds an <a> and a language marker from the plugin's own link table and I18nHelper::get_language_markup(), around the translated message and anchor passed here ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>