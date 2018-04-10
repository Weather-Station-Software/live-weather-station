<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$tech = get_option('live_weather_station_show_technical');
$main = ($module['module_type'] == 'NAMain');
$hw = (strpos($module['module_type'], 'AModule') == 1);
$module_icn = $this->output_iconic_value(0, 'module', false, false, 'style="color:#999"', 'fa-lg');


?>
<?php if ($static_display) { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
        <div style="margin-bottom: 10px;">
            <span style="<?php echo($main||$hw?'width:50%;float: left;':'width:100%;'); ?>;cursor: default;"><?php echo $module_icn; ?>&nbsp;<?php echo $module['module_type_name']; ?></span>
            <?php if (array_key_exists('battery', $module) && array_key_exists('signal', $module)) { ?>
                <?php if ($main) { ?>
                        <span style="width:25%;float: left;cursor: default;"><?php echo $module['battery_icn']; ?>&nbsp;<?php echo $module['battery_txt']; ?></span>
                        <span style="width:25%;cursor: default;"><?php echo $module['signal_icn']; ?>&nbsp;<?php echo $module['signal_txt']; ?></span>
                <?php } ?>
                <?php if ($hw) { ?>
                    <span style="width:25%;float: left;cursor: default;"><?php echo $module['battery_icn']; ?>&nbsp;<?php echo $module['battery_txt']; ?></span>
                    <span style="width:25%;cursor: default;"><?php echo $module['signal_icn']; ?>&nbsp;<?php echo $module['signal_txt']; ?></span>
                <?php } ?>
            <?php } else { ?>
                <?php if ($main || $hw) { ?>
                    <span style="width:25%;float: left;cursor: default;">&nbsp;</span>
                    <span style="width:25%;cursor: default;">&nbsp;</span>
                <?php } ?>
            <?php } ?>
        </div>
        <?php if (array_key_exists('last_refresh', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo $refresh_icn; ?>&nbsp;<?php echo $module['last_refresh_txt']; ?></span><span style="color:silver"> (<?php echo $module['last_refresh_diff_txt']; ?>)</span>
            </div>
        <?php } ?>
    </div>

    <?php if ($hw && $tech) { ?>
    <div class="activity-block" style="padding-bottom: 0px;">
        <?php if (array_key_exists('last_seen', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo $last_seen_icn; ?>&nbsp;<?php echo $module['last_seen_txt']; ?></span><span style="color:silver"> (<?php echo $module['last_seen_diff_txt']; ?>)</span>
            </div>
        <?php } ?>
        <?php if (array_key_exists('firmware', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:1000%;cursor: default;"><?php echo $firmware_icn; ?>&nbsp;<?php echo $module['firmware_txt']; ?></span>
            </div>
        <?php } ?>
        <?php if (array_key_exists('last_setup', $module)) { ?>
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo $setup_icn; ?>&nbsp;<?php echo $module['last_setup_txt']; ?></span><span style="color:silver"> (<?php echo $module['last_setup_diff_txt']; ?>)</span>
            </div>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if ($main && $tech) { ?>
        <div class="activity-block" style="padding-bottom: 0px;">
            <?php if (array_key_exists('last_seen', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo $last_seen_icn; ?>&nbsp;<?php echo $module['last_seen_txt']; ?></span><span style="color:silver"> (<?php echo $module['last_seen_diff_txt']; ?>)</span>
                </div>
            <?php } ?>
            <?php if (array_key_exists('firmware', $module) && array_key_exists('last_upgrade', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo $firmware_icn; ?>&nbsp;<?php echo $module['firmware_txt']; ?> <?php echo __('installed on', 'live-weather-station'); ?> <?php echo $module['last_upgrade_txt']; ?></span>
                </div>
            <?php } ?>
            <?php if (array_key_exists('firmware', $module) && !array_key_exists('last_upgrade', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo $firmware_icn; ?>&nbsp;<?php echo $module['firmware_txt']; ?></span>
                </div>
            <?php } ?>
            <?php if (array_key_exists('first_setup', $module)) { ?>
                <div style="margin-bottom: 10px;">
                    <span style="width:100%;cursor: default;"><?php echo $setup_icn; ?>&nbsp;<?php echo $module['first_setup_txt']; ?></span><span style="color:silver"> (<?php echo $module['first_setup_diff_txt']; ?>)</span>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if (array_key_exists('measure', $module)) { ?>
        <?php if (count($module['measure']) > 0) { ?>
            <div class="activity-block" style="padding-bottom: 0px;">
                <div style="margin-bottom: 10px;">
                    <?php foreach ($module['measure'] as $measure) { ?>
                        <span title="<?php echo $measure['measure_type_txt']; ?>" style="white-space: nowrap;margin-right: 20px;line-height: 2.2em;cursor: default;"><?php echo $measure['measure_value_icn']; ?>&nbsp;<?php echo $measure['measure_value_txt']; ?></span>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <?php if ($manage_modules) { ?>
        <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
            <div style="margin-bottom: 10px;">
                <span style="width:100%;cursor: default;"><?php echo $module_icn; ?>&nbsp;<?php echo $this->get_module_type($module['module_type'], false);; ?></span>
                <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                    <tr>
                        <th class="lws-login" width="38%" align="left" scope="row"><?php esc_html_e('Displayed name', 'live-weather-station' );?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login"><input id="<?php echo 'lws-name-' . $module['module_id'] ?>" name="<?php echo 'lws-name-' . $module['module_id'] ?>" type="text" size="60" value="<?php echo htmlspecialchars($module['screen_name']) ?>" class="regular-text" /></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="lws-login" width="38%" align="left" scope="row"><?php esc_html_e('Status', 'live-weather-station');?></th>
                        <td width="2%"/>
                        <td align="left">
                            <span class="login">
                                <select name="<?php echo 'lws-hidden-' . $module['module_id'] ?>" id="<?php echo 'lws-hidden-' . $module['module_id'] ?>" style="width:25em;">
                                    <option value="0" <?php echo ((boolean)$module['hidden']?'':'selected="selected"'); ?>><?php echo __('visible', 'live-weather-station') ?></option>;
                                    <option value="1" <?php echo ((boolean)$module['hidden']?'selected="selected"':''); ?>><?php echo __('hidden', 'live-weather-station') ?></option>;
                                </select></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php } ?>
<?php } ?>