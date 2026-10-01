<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Device\Manager as DeviceManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$tech = get_option('live_weather_station_show_technical');
$module_icn = $this->output_iconic_value(0, 'module', false, false, '#999');
$station_name_icn = $this->output_iconic_value(0, 'station_name', false, false, '#999');

?>
<?php if ($static_display) { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
        <div style="margin-bottom: 10px;">
            <span style="width:50%;float: left;cursor: default;"><?php echo wp_kses_post($module_icn); ?>&nbsp;<?php echo esc_html($module['module_type_name']); ?></span>
            <span style="width:25%;float: left;cursor: default;"><?php echo wp_kses_post($module['battery_icn']); ?>&nbsp;<?php echo esc_html($module['battery_txt']); ?></span>
            <span style="width:25%;cursor: default;"><?php echo wp_kses_post($module['signal_icn']); ?>&nbsp;<?php echo esc_html($module['signal_txt']); ?></span>
        </div>
        <div style="margin-bottom: 10px;">
            <span style="width:100%;"><?php echo wp_kses_post($station_name_icn); ?>&nbsp;<?php echo esc_html($module['self_name']); ?></span>
            <span style="color:silver"> (<?php echo esc_html($module['self_visibility']); ?>)</span>
        </div>
        <?php if (array_key_exists('last_refresh', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo wp_kses_post($refresh_icn); ?>&nbsp;<?php echo esc_html($module['last_refresh_txt']); ?></span><span style="color:silver"> (<?php echo esc_html($module['last_refresh_diff_txt']); ?>)</span>
            </div>
        <?php } ?>
    </div>

    <?php if (DeviceManager::is_hardware($module['module_type']) && $tech && ($module['module_type'] !== 'NAMain')) { ?>
    <div class="activity-block" style="padding-bottom: 0px;">
        <?php if (array_key_exists('last_seen', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo wp_kses_post($last_seen_icn); ?>&nbsp;<?php echo esc_html($module['last_seen_txt']); ?></span><span style="color:silver"> (<?php echo esc_html($module['last_seen_diff_txt']); ?>)</span>
            </div>
        <?php } ?>
        <?php if (array_key_exists('firmware', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:1000%;cursor: default;"><?php echo wp_kses_post($firmware_icn); ?>&nbsp;<?php echo esc_html($module['firmware_txt']); ?></span>
            </div>
        <?php } ?>
        <?php if (array_key_exists('last_setup', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo wp_kses_post($setup_icn); ?>&nbsp;<?php echo esc_html($module['last_setup_txt']); ?></span><span style="color:silver"> (<?php echo esc_html($module['last_setup_diff_txt']); ?>)</span>
            </div>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (($module['module_type'] == 'NAMain') && $tech) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <?php if (array_key_exists('last_seen', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo wp_kses_post($last_seen_icn); ?>&nbsp;<?php echo esc_html($module['last_seen_txt']); ?></span><span style="color:silver"> (<?php echo esc_html($module['last_seen_diff_txt']); ?>)</span>
                </div>
            <?php } ?>
            <?php if (array_key_exists('firmware', $module) && array_key_exists('last_upgrade', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo wp_kses_post($firmware_icn); ?>&nbsp;<?php echo esc_html($module['firmware_txt']); ?> <?php echo esc_html__('installed on', 'live-weather-station'); ?> <?php echo esc_html($module['last_upgrade_txt']); ?></span>
                </div>
            <?php } ?>
            <?php if (array_key_exists('firmware', $module) && !array_key_exists('last_upgrade', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo wp_kses_post($firmware_icn); ?>&nbsp;<?php echo esc_html($module['firmware_txt']); ?></span>
                </div>
            <?php } ?>
            <?php if (array_key_exists('first_setup', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo wp_kses_post($setup_icn); ?>&nbsp;<?php echo esc_html($module['first_setup_txt']); ?></span><span style="color:silver"> (<?php echo esc_html($module['first_setup_diff_txt']); ?>)</span>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if (array_key_exists('measure', $module)) { ?>
        <?php if (count($module['measure']) > 0) { ?>
            <div class="activity-block" style="padding-bottom: 0px;">
                <div style="margin-bottom: 10px;">
                    <?php foreach ($module['measure'] as $measure) { ?>
                        <span title="<?php echo esc_attr($measure['measure_type_txt']); ?>" style="white-space: nowrap;margin-right: 20px;line-height: 2.2em;cursor: default;"><?php echo wp_kses_post($measure['measure_value_icn']); ?>&nbsp;<?php echo wp_kses_post($measure['measure_value_txt']); ?></span>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <?php if ($manage_modules) { ?>
        <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo wp_kses_post($module_icn); ?>&nbsp;<?php echo esc_html($this->get_module_type($module['module_type'], false)); ?></span>
                <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                    <tr>
                        <th class="lws-login" width="38%" align="left" scope="row"><?php esc_html_e('Displayed name', 'live-weather-station' );?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login"><input id="<?php echo esc_attr('lws-name-' . $module['module_id']) ?>" name="<?php echo esc_attr('lws-name-' . $module['module_id']) ?>" type="text" size="60" value="<?php echo esc_attr($module['screen_name']) ?>" class="regular-text" /></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="lws-login" width="38%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login">
                                <select name="<?php echo esc_attr('lws-hidden-' . $module['module_id']) ?>" id="<?php echo esc_attr('lws-hidden-' . $module['module_id']) ?>" style="width:100%;">
                                    <option value="0" <?php echo ((bool)$module['hidden']?'':'selected="selected"'); ?>><?php echo esc_html__('visible', 'live-weather-station') ?></option>;
                                    <option value="1" <?php echo ((bool)$module['hidden']?'selected="selected"':''); ?>><?php echo esc_html__('hidden', 'live-weather-station') ?></option>;
                                </select></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php } ?>
<?php } ?>