<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp;

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo sprintf(__('%s can\'t run on your WordPress site', 'live-weather-station' ), LWS_PLUGIN_NAME);?></span></h3>
        <div class="inside">
            <strong><?php echo sprintf(__('The PHP configuration of your server doesn\'t meet the minimal requirements needed to run %s. Please, see below to identify which PHP extension must be installed:', 'live-weather-station'), LWS_PLUGIN_NAME);?></strong>
            <br/>
            <table cellspacing="10" width="99%">
                <tbody>
                    <tr>
                        <?php if (LWS_PHPVERSION_OK) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="fa fa-lg fa-check-circle"></i></td>
                            <td><?php echo __('PHP version is greater than or equal to 5.4.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="fa fa-lg fa-minus-circle"></i></td>
                            <td><?php echo __('PHP version is lower than 5.4.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if (LWS_I18N_LOADED) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="fa fa-lg fa-check-circle"></i></td>
                            <td><?php echo __('Internationalization support is installed.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="fa fa-lg fa-minus-circle"></i></td>
                            <td><?php echo __('Internationalization support is not installed.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if (LWS_CURL_LOADED) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="fa fa-lg fa-check-circle"></i></td>
                            <td><?php echo __('cURL support is installed.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="fa fa-lg fa-minus-circle"></i></td>
                            <td><?php echo __('cURL support is not installed.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if (LWS_JSON_LOADED) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="fa fa-lg fa-check-circle"></i></td>
                            <td><?php echo __('JSON support is installed.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="fa fa-lg fa-minus-circle"></i></td>
                            <td><?php echo __('JSON support is not installed.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if (LWS_ICONV_LOADED) { ?>
                            <td width="10%"/><td width="20px"><i style="color:limegreen" class="fa fa-lg fa-check-circle"></i></td>
                            <td><?php echo __('ICONV support is installed.', 'live-weather-station'); ?></td>
                        <?php } else { ?>
                            <td width="10%"/><td width="20px"><i style="color:red" class="fa fa-lg fa-minus-circle"></i></td>
                            <td><?php echo __('ICONV support is not installed.', 'live-weather-station'); ?></td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="major-publishing-actions">
            <div>
                <?php echo InlineHelp::get(11, __('You can find detailed requirements on %s.', 'live-weather-station'), __('this page', 'live-weather-station'));?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>