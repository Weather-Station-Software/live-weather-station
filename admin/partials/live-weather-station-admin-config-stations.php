<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<?php if (count($datas) > 0) { ?>
<?php foreach($datas['devices'] as $device_key => $device) { ?>
    <?php $tz = (array_key_exists('timezone', $device['place']) ? $device['place']['timezone'] : '');  ?>
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="referrers" class="postbox ">
            <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
            <h3 class="hndle"><span><?php echo $device['station_name']?></span></h3>
            <div class="inside">
                <table cellspacing="0">
                    <tbody>
                    <?php if (!Owm_Current_Client::is_owm_station($device['_id'])) { ?>
                    <tr>
                        <th scope="row" width="20%" align="left"><img class="lws-logo" src="<?php echo LWS_ADMIN_URL . 'images/NAMain.png'; ?>" /><span class="lws-module-name"><?php echo $device['module_name'];?></span></th>
                        <td width="5%"/>
                        <td align="left">
                            <span>
                                <?php esc_attr_e('Location', 'live-weather-station'); echo ': '.$device['place']['city'].', '.$device['place']['country'];  ?> /
                                <?php esc_attr_e((isset($device['place']['location']) && is_array($device['place']['location']) && count($device['place']['location'])>1) ?  __('Geolocation informations found', 'live-weather-station') :  __('Geolocation informations not found', 'live-weather-station')) ?>
                                <?php echo '<br />'; ?>
                                <?php esc_attr_e('Time zone', 'live-weather-station'); echo ': '.$this->output_value($tz,'loc_timezone', true);  ?>
                                <?php
                                        echo '<br />';
                                        echo __('Battery level', 'live-weather-station') . ' ' . strtolower($this->get_battery_level_text(0, 'NAMain')) . ' / ' . __('WiFi signal', 'live-weather-station') . ' ' . strtolower($this->get_wifi_level_text($device['wifi_status']));
                                        echo ' / ' . __('Last seen on', 'live-weather-station') . ' ' . $this->get_date_from_utc($device['dashboard_data']['time_utc'], $tz) . ' ' . __('at', 'live-weather-station') . ' ' . $this->get_time_from_utc($device['dashboard_data']['time_utc'], $tz);
                                        echo '<br/>';
                                        foreach ($device['data_type'] as $key => $data_type) {
                                            if (isset($device['dashboard_data'][$data_type])) {
                                                echo $this->get_measurement_type($data_type);
                                                echo ' (' . $this->output_value($device['dashboard_data'][$data_type], $data_type, true, false, 'NAMain', $tz) . ')';
                                                echo($key < (sizeof($device['data_type']) - 1) ? ', ' : ''); 
                                            }
                                        }
                                ?>
                                <br/><br/>
                            </span>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php foreach($device['modules'] as $module) { ?>
                        <?php $module_data_types = $module['data_type'] ?>
                        <?php if ($module['type'] == 'NAModule2') {$module_data_types = array('WindAngle','WindStrength','GustAngle','GustStrength');} ?>
                        <?php if ($module['type'] != 'NACurrent' && $module['type'] != 'NAComputed' && $module['type'] != 'NAEphemer' && $module['type'] != 'NAPollution') { ?>
                        <tr>
                            <th scope="row" width="20%" align="left"><img class="lws-logo" src="<?php echo LWS_ADMIN_URL . 'images/'.$module['type'].'.png'; ?>" /><span class="lws-module-name"><?php echo $module['module_name'];?></span></th>
                            <td width="5%"/>
                            <td align="left">
                                <span>
                                    <?php
                                        echo __('Battery level', 'live-weather-station').' '.strtolower($this->get_battery_level_text($module['battery_vp'], $module['type'])) . ' / ' . __('Radio signal', 'live-weather-station').' '.strtolower($this->get_rf_level_text($module['rf_status']));
                                        echo '<br/>';
                                        foreach($module_data_types as $key => $data_type) {
                                            if (isset($module['dashboard_data'][$data_type])) {
                                                echo $this->get_measurement_type($data_type, false, $module['type']);
                                                echo ' (' . $this->output_value($module['dashboard_data'][$data_type], $data_type, true, false, $module['type'], $tz) . ')';
                                                echo($key < (sizeof($module_data_types) - 1) ? ', ' : '');
                                            }
                                        }
                                    ?>
                                    <br/><br/>
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ($module['type'] == 'NACurrent') { ?>
                            <?php $module_data_types = array('temperature','pressure', 'humidity', 'windstrength', 'windangle', 'rain', 'snow', 'cloudiness'); ?>
                            <tr>
                                <th scope="row" width="20%" align="left"><img class="lws-logo" src="<?php echo LWS_ADMIN_URL . 'images/'.$module['type'].'.png'; ?>" /><span class="lws-module-name"><?php esc_html_e('OpenWeatherMap records', 'live-weather-station');?></span></th>
                                <td width="5%"/>
                                <td align="left">
                            <span>
                                <?php
                                foreach($module_data_types as $key => $data_type) {
                                    if (isset($module['dashboard_data'][$data_type])) {
                                        echo $this->get_measurement_type($data_type);
                                        echo ' (' . $this->output_value($module['dashboard_data'][$data_type], $data_type, true, false, '', $tz) . ')';
                                        echo($key < (sizeof($module_data_types) - 1) ? ', ' : '');
                                    }
                                }
                                ?>
                                <br/><br/>
                            </span>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($module['type'] == 'NAPollution') { ?>
                        <?php $module_data_types = array('o3', 'co'); ?>
                        <tr>
                            <th scope="row" width="20%" align="left"><img class="lws-logo" src="<?php echo LWS_ADMIN_URL . 'images/'.$module['type'].'.png'; ?>" /><span class="lws-module-name"><?php esc_html_e('OpenWeatherMap pollution', 'live-weather-station');?></span></th>
                            <td width="5%"/>
                            <td align="left">
                            <span>
                                <?php
                                foreach($module_data_types as $key => $data_type) {
                                    if (isset($module['dashboard_data'][$data_type])) {
                                        echo $this->get_measurement_type($data_type);
                                        echo ' (' . $this->output_value($module['dashboard_data'][$data_type], $data_type, true, false, '', $tz) . ')';
                                        echo($key < (sizeof($module_data_types) - 1) ? ', ' : '');
                                    }
                                }
                                ?>
                                <br/><br/>
                            </span>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if ($module['type'] == 'NAComputed') { ?>
                            <tr>
                                <th scope="row" width="20%" align="left"><img class="lws-logo" src="<?php echo LWS_ADMIN_URL . 'images/'.$module['type'].'.png'; ?>" /><span class="lws-module-name"><?php esc_html_e('Computed values', 'live-weather-station');?></span></th>
                                <td width="5%"/>
                                <td align="left">
                            <span>
                                <?php
                                foreach($module_data_types as $key => $data_type) {
                                    if (isset($module['dashboard_data'][$data_type])) {
                                        echo $this->get_measurement_type($data_type);
                                        echo ' (' . $this->output_value($module['dashboard_data'][$data_type], $data_type, true, false, '', $tz) . ')';
                                        echo($key < (sizeof($module_data_types) - 1) ? ', ' : '');
                                    }
                                }
                                ?>
                                <br/><br/>
                            </span>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($module['type'] == 'NAEphemer') { ?>
                            <tr>
                                <th scope="row" width="20%" align="left"><img class="lws-logo" src="<?php echo LWS_ADMIN_URL . 'images/'.$module['type'].'.png'; ?>" /><span class="lws-module-name"><?php esc_html_e('Ephemeris', 'live-weather-station');?></span></th>
                                <td width="5%"/>
                                <td align="left">
                            <span>
                                <?php
                                foreach($module_data_types as $key => $data_type) {
                                    if (isset($module['dashboard_data'][$data_type])) {
                                        echo $this->get_measurement_type($data_type);
                                        echo ' (' . $this->output_value($module['dashboard_data'][$data_type], $data_type, true, false, '', $tz) . ')';
                                        echo($key < (sizeof($module_data_types) - 1) ? ', ' : '');
                                    }
                                }
                                ?>
                                <br/><br/>
                            </span>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div id="major-publishing-actions">
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-shortcodes-textual.php'); ?>
                <?php /*include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-shortcodes-icon.php'); */?>
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-shortcodes-lcd.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-shortcodes-justgage.php'); ?>
                <?php include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-config-shortcodes-steelmeter.php'); ?>
                <div>
                    <?php esc_html_e('Get shortcodes for ', 'live-weather-station'); ?>
                    <a href="#" id="textual-datas-link-<?php echo $device_key; ?>"><?php esc_html_e('textual datas', 'live-weather-station'); ?></a> <?php /*| <a href="#" id="icon-datas-link-<?php echo $device_key; ?>"><?php esc_html_e('icons', 'live-weather-station'); ?></a> */?>| <a href="#" id="lcd-datas-link-<?php echo $device_key; ?>"><?php esc_html_e('LCD display', 'live-weather-station'); ?></a> | <a href="#" id="justgage-datas-link-<?php echo $device_key; ?>"><?php esc_html_e('clean gauge', 'live-weather-station'); ?></a> | <a href="#" id="steelmeter-datas-link-<?php echo $device_key; ?>"><?php esc_html_e('steel meter', 'live-weather-station'); ?></a>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
<?php } ?>
<?php }
    else { ?>
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="referrers" class="postbox ">
            <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
            <h3 class="hndle"><span><?php esc_html_e( 'Get started', 'live-weather-station' );?></span></h3>
            <div class="inside">
                <table cellspacing="0">
                    <tbody>
                    <?php if (!$status['enabled'] && !$status['o_enabled']) { ?>
                        <tr>
                            <td align="left"> <span><?php echo __( 'In order to display weather data, <strong><em>Live Weather Station</em></strong> must be connected to your Netatmo account and/or it must have a valid OpenWeatherMap API key...<br/><strong>If you have a Netatmo weather station</strong> (or have access to some friends stations) please enter your Netatmo login and password in the "Settings" box. <em>Note that, in accordance with the manufacturer recommendations, these credentials will not be stored in your WordPress site.</em><br/><strong>If you do not have a Netatmo account</strong> or if you want to enhance the rendedred datas of your Netatmo station, you must <a href="http://openweathermap.org/appid" target="_blank">get a free OpenWeatherMap API key</a> and paste it in the "Settings" box.', 'live-weather-station');?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if (get_option('live_weather_station_owm_account')[1] != 2 && $status['enabled']) { ?>
                        <tr>
                            <td align="left"> <span><?php echo __( 'There is no Netatmo weather station associated with your account.', 'live-weather-station');?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if (get_option('live_weather_station_owm_account')[1] != 1 && $status['o_enabled']) { ?>
                        <?php if ( !LWS_I18N_LOADED ): ?>
                            <tr>
                                <td align="left"> <span><?php esc_html_e( 'Internationalisation extension is not installed. You can not manage OWM stations...', 'live-weather-station' );?></span></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td align="left"> <span><?php echo __( 'You have not created any OpenWeatherMap station at the moment, this is why nothing appears here. To add your first station, visit', 'live-weather-station').' <a href="'.esc_url($this->get_page_url('manage_owm')).'">'.__( 'the stations management page', 'live-weather-station').'</a>.';?></span></td>
                            </tr>
                        <?php endif; ?>
                    <?php } ?>
                    <?php if (get_option('live_weather_station_owm_account')[1] == 1 && $status['o_enabled'] && !$status['enabled']) { ?>
                        <tr>
                            <td align="left"> <span><?php echo __( 'To see data from OpenWeatherMap stations, you must choose another collection mode in the "Options" box.', 'live-weather-station');?></span></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>
