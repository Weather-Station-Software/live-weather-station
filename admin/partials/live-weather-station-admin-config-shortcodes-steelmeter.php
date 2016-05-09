<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      2.2.0
 */
?>
<div id="steelmeter-error-<?php echo $device_key; ?>" class="wrap" style="display:none;">
    <div id="steelmeter-error-container-<?php echo $device_key; ?>" class="metabox-holder">
        <div class="postbox-container" style="width: 100%;margin-top:16px;">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( 'OpenWeatherMap error', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <?php esc_html_e( 'OpenWeatherMap servers have returned an empty response for this weather station. For this reason, it is currently not possible to generate a shortcode. This is normally a temporary error so, please, retry again later.', 'live-weather-station' );?>
                    </div>
                </div>
            </div>
            <?php if(json_encode($js_array_steelmeter[$device_key][2]) == '[]') { ?>
                <script language="javascript" type="text/javascript">
                    jQuery(document).ready(function($) {
                        $("#steelmeter-datas-link-<?php echo $device_key; ?>").click(function(){
                                tb_show('', '#TB_inline?width=400&height=200&inlineId=steelmeter-error-<?php echo $device_key; ?>');
                                $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('steel meter', 'live-weather-station');?>");
                            }
                        );
                    });
                </script>
            <?php } ?>
        </div>
    </div>
</div>
<div id="steelmeter-datas-<?php echo $device_key; ?>" class="wrap" style="display:none;">
    <div id="steelmeter-datas-container-<?php echo $device_key; ?>" class="metabox-holder">
        <div class="postbox-container" style="width: 54%;margin-right: 16px;margin-top:16px;">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '1. Set parameters for the shortcode', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <table cellspacing="0">
                            <tbody>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Module', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-module-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter[$device_key][2] as $key_module => $module) { ?>
                                                <option value="<?php echo $key_module; ?>"><?php echo $module[0]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Measurement', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-measurement-<?php echo $device_key; ?>">
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Design', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-design-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_design as $design) { ?>
                                                <option value="<?php echo $design[0]; ?>"><?php echo $design[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Bezel', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-frame-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_frame as $frame) { ?>
                                                <option value="<?php echo $frame[0]; ?>"><?php echo $frame[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Face', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-background-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_background as $background) { ?>
                                                <option value="<?php echo $background[0]; ?>"><?php echo $background[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Labels orientation', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-orientation-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_orientation as $orientation) { ?>
                                                <option value="<?php echo $orientation[0]; ?>"><?php echo $orientation[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Main pointer type', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-main-pointer-type-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_pointer_type as $pointer_type) { ?>
                                                <option value="<?php echo $pointer_type[0]; ?>"><?php echo $pointer_type[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Main pointer color', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-main-pointer-color-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_pointer_color as $pointer_color) { ?>
                                                <option value="<?php echo $pointer_color[0]; ?>"><?php echo $pointer_color[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('2nd pointer type', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-aux-pointer-type-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_pointer_type as $pointer_type) { ?>
                                                <option value="<?php echo $pointer_type[0]; ?>"><?php echo $pointer_type[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('2nd pointer color', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-aux-pointer-color-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_pointer_color as $pointer_color) { ?>
                                                <option value="<?php echo $pointer_color[0]; ?>"><?php echo $pointer_color[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Knob', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-knob-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_knob as $knob) { ?>
                                                <option value="<?php echo $knob[0]; ?>"><?php echo $knob[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php echo esc_html(ucfirst(__('LCD display', 'live-weather-station')));?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-lcd-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_lcd_color as $lcd) { ?>
                                                <option value="<?php echo $lcd[0]; ?>"><?php echo $lcd[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Alarm', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-alarm-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_led_color as $led) { ?>
                                                <option value="<?php echo $led[0]; ?>"><?php echo $led[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Trend', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-trend-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_led_color as $led) { ?>
                                                <option value="<?php echo $led[0]; ?>"><?php echo $led[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Min/max', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-minmax-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_minmax as $minmax) { ?>
                                                <option value="<?php echo $minmax[0]; ?>"><?php echo $minmax[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Index style', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-index-style-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_index_style as $style) { ?>
                                                <option value="<?php echo $style[0]; ?>"><?php echo $style[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Index color', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-index-color-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_index_color as $color) { ?>
                                                <option value="<?php echo $color[0]; ?>"><?php echo $color[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Glass', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-glass-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_glass as $glass) { ?>
                                                <option value="<?php echo $glass[0]; ?>"><?php echo $glass[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="45%" align="left" scope="row"><?php esc_html_e('Size', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" style="width: 270px;" id="steelmeter-datas-size-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_steelmeter_size as $size) { ?>
                                                <option value="<?php echo $size[0]; ?>"<?php echo($size[0]=='large'?'SELECTED':''); ?>><?php echo $size[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if(json_encode($js_array_steelmeter[$device_key][2]) != '[]') { ?>
                    <script language="javascript" type="text/javascript">
                        jQuery(document).ready(function($) {

                            <?php
                            $fingerprint = uniqid('', true);
                            $fingerprint = 'ssm'.substr ($fingerprint, count($fingerprint)-6, 80);
                            ?>

                            var js_array_steelmeter_<?php echo $device_key; ?> = <?php echo json_encode($js_array_steelmeter[$device_key][2]); ?>;

                            new Clipboard('.steelmeter-cpy-<?php echo $device_key; ?>');

                            $("#steelmeter-datas-link-<?php echo $device_key; ?>").click(function(){
                                    tb_show('', '#TB_inline?width=940&height=724&inlineId=steelmeter-datas-<?php echo $device_key; ?>');
                                    $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                    $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('steel meter', 'live-weather-station');?> - <?php echo $device['station_name']?>");
                                }
                            );

                            $("#steelmeter-datas-module-<?php echo $device_key; ?>").change(function() {
                                var js_array_steelmeter_measurement_<?php echo $device_key; ?> = js_array_steelmeter_<?php echo $device_key; ?>[$(this).val()][2];
                                $("#steelmeter-datas-measurement-<?php echo $device_key; ?>").html("");
                                $(js_array_steelmeter_measurement_<?php echo $device_key; ?>).each(function (i) {
                                    $("#steelmeter-datas-measurement-<?php echo $device_key; ?>").append("<option value="+i+">"+js_array_steelmeter_measurement_<?php echo $device_key; ?>[i][0]+"</option>");
                                });
                                $("#steelmeter-datas-measurement-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-measurement-<?php echo $device_key; ?>").change(function() {
                                $( "#steelmeter-datas-design-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-design-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-frame-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-frame-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-background-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-background-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-orientation-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-orientation-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-main-pointer-type-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-main-pointer-type-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-main-pointer-color-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-main-pointer-color-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-aux-pointer-type-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-aux-pointer-type-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-aux-pointer-color-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-aux-pointer-color-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-knob-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-knob-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-lcd-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-lcd-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-alarm-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-alarm-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-trend-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-trend-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-minmax-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-minmax-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-index-style-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-index-style-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-index-color-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-index-color-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-glass-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-glass-<?php echo $device_key; ?>").change(function() {
                                $("#steelmeter-datas-size-<?php echo $device_key; ?>" ).change();
                            });

                            $("#steelmeter-datas-size-<?php echo $device_key; ?>").change(function() {
                                var sc_device = "<?php echo $device['_id']; ?>";
                                var sc_module = js_array_steelmeter_<?php echo $device_key; ?>[$("#steelmeter-datas-module-<?php echo $device_key; ?>").val()][1];
                                var sc_measurement = js_array_steelmeter_<?php echo $device_key; ?>[$("#steelmeter-datas-module-<?php echo $device_key; ?>").val()][2][$("#steelmeter-datas-measurement-<?php echo $device_key; ?>").val()][1];
                                var sc_design = $("#steelmeter-datas-design-<?php echo $device_key; ?>").val();
                                var sc_frame = $("#steelmeter-datas-frame-<?php echo $device_key; ?>").val();
                                var sc_background = $("#steelmeter-datas-background-<?php echo $device_key; ?>").val();
                                var sc_orientation = $("#steelmeter-datas-orientation-<?php echo $device_key; ?>").val();
                                var sc_main_pointer_type = $("#steelmeter-datas-main-pointer-type-<?php echo $device_key; ?>").val();
                                var sc_main_pointer_color = $("#steelmeter-datas-main-pointer-color-<?php echo $device_key; ?>").val();
                                var sc_aux_pointer_type = $("#steelmeter-datas-aux-pointer-type-<?php echo $device_key; ?>").val();
                                var sc_aux_pointer_color = $("#steelmeter-datas-aux-pointer-color-<?php echo $device_key; ?>").val();
                                var sc_knob = $("#steelmeter-datas-knob-<?php echo $device_key; ?>").val();
                                var sc_lcd = $("#steelmeter-datas-lcd-<?php echo $device_key; ?>").val();
                                var sc_alarm = $("#steelmeter-datas-alarm-<?php echo $device_key; ?>").val();
                                var sc_trend = $("#steelmeter-datas-trend-<?php echo $device_key; ?>").val();
                                var sc_minmax = $("#steelmeter-datas-minmax-<?php echo $device_key; ?>").val();
                                var sc_index_style = $("#steelmeter-datas-index-style-<?php echo $device_key; ?>").val();
                                var sc_index_color = $("#steelmeter-datas-index-color-<?php echo $device_key; ?>").val();
                                var sc_glass = $("#steelmeter-datas-glass-<?php echo $device_key; ?>").val();
                                var sc_size = $("#steelmeter-datas-size-<?php echo $device_key; ?>").val();
                                var shortcode = "[live-weather-station-steelmeter device_id='"+sc_device+
                                    "' module_id='"+sc_module+
                                    "' measure_type='"+sc_measurement+
                                    "' design='"+sc_design.toLowerCase()+
                                    "' frame='"+sc_frame.toLowerCase()+
                                    "' background='"+sc_background.toLowerCase()+
                                    "' orientation='"+sc_orientation.toLowerCase()+
                                    "' main_pointer_type='"+sc_main_pointer_type.toLowerCase()+
                                    "' main_pointer_color='"+sc_main_pointer_color.toLowerCase()+
                                    "' aux_pointer_type='"+sc_aux_pointer_type.toLowerCase()+
                                    "' aux_pointer_color='"+sc_aux_pointer_color.toLowerCase()+
                                    "' knob='"+sc_knob.toLowerCase()+
                                    "' lcd='"+sc_lcd.toLowerCase()+
                                    "' alarm='"+sc_alarm.toLowerCase()+
                                    "' trend='"+sc_trend.toLowerCase()+
                                    "' minmax='"+sc_minmax.toLowerCase()+
                                    "' index_style='"+sc_index_style.toLowerCase()+
                                    "' index_color='"+sc_index_color.toLowerCase()+
                                    "' glass='"+sc_glass.toLowerCase()+
                                    "' size='"+sc_size.toLowerCase()+"']";
                                $("#steelmeter-datas-shortcode-<?php echo $device_key; ?>").html(shortcode);
                                if (sc_design.indexOf('meter') > -1 || sc_design.indexOf('windcompass') > -1) {
                                    $("#steelmeter-datas-orientation-<?php echo $device_key; ?>").val('auto');
                                    $("#steelmeter-datas-orientation-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-orientation-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_design.indexOf('windcompass') < 0) {
                                    $("#steelmeter-datas-aux-pointer-type-<?php echo $device_key; ?>").prop('disabled', true);
                                    $("#steelmeter-datas-aux-pointer-color-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-aux-pointer-type-<?php echo $device_key; ?>").prop('disabled', false);
                                    $("#steelmeter-datas-aux-pointer-color-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_design.indexOf('digital') > -1) {
                                    $("#steelmeter-datas-main-pointer-type-<?php echo $device_key; ?>").prop('disabled', true);
                                    $("#steelmeter-datas-main-pointer-color-<?php echo $device_key; ?>").prop('disabled', true);
                                    $("#steelmeter-datas-knob-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-main-pointer-type-<?php echo $device_key; ?>").prop('disabled', false);
                                    $("#steelmeter-datas-main-pointer-color-<?php echo $device_key; ?>").prop('disabled', false);
                                    $("#steelmeter-datas-knob-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_design.indexOf('meter') == 0) {
                                    $("#steelmeter-datas-lcd-<?php echo $device_key; ?>").val('none');
                                    $("#steelmeter-datas-lcd-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-lcd-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_design.indexOf('meter') > -1 || sc_design.indexOf('windcompass') > -1) {
                                    $("#steelmeter-datas-alarm-<?php echo $device_key; ?>").val('none');
                                    $("#steelmeter-datas-alarm-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-alarm-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_design.indexOf('4') > -1) {
                                    $("#steelmeter-datas-trend-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                else {
                                    $("#steelmeter-datas-trend-<?php echo $device_key; ?>").val('none');
                                    $("#steelmeter-datas-trend-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                if (sc_design.indexOf('altimeter') > -1 || sc_design.indexOf('windcompass') > -1 || sc_design.indexOf('digital') > -1) {
                                    $("#steelmeter-datas-minmax-<?php echo $device_key; ?>").val('none');
                                    $("#steelmeter-datas-minmax-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-minmax-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_design.indexOf('altimeter') > -1) {
                                    $("#steelmeter-datas-index-style-<?php echo $device_key; ?>").val('none');
                                    $("#steelmeter-datas-index-style-<?php echo $device_key; ?>").prop('disabled', true);
                                    $("#steelmeter-datas-index-color-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#steelmeter-datas-index-style-<?php echo $device_key; ?>").prop('disabled', false);
                                    $("#steelmeter-datas-index-color-<?php echo $device_key; ?>").prop('disabled', false);
                                }
                                if (sc_index_style.indexOf('none') > -1) {
                                    $("#steelmeter-datas-index-color-<?php echo $device_key; ?>").prop('disabled', true);
                                }
                                $("#steelmeter-spinner-<?php echo $device_key; ?>").addClass('is-active');
                                $("#<?php echo $fingerprint; ?>" ).empty();
                                if (sc_size=='small') {
                                    var wsize = 150;
                                }
                                if (sc_size=='medium') {
                                    var wsize = 200;
                                }
                                if (sc_size=='large') {
                                    var wsize = 250;
                                }
                                if (sc_size=='macro') {
                                    var wsize = 300;
                                }
                                var canvas = document.getElementById('<?php echo $fingerprint; ?>');
                                canvas.getContext('2d').clearRect(0, 0, $("#<?php echo $fingerprint; ?>").width(), $("#<?php echo $fingerprint; ?>").height());
                                $("#<?php echo $fingerprint; ?>").width(1);
                                $("#<?php echo $fingerprint; ?>").width(wsize).height(wsize);
                                var http = new XMLHttpRequest();
                                var params = 'action=lws_query_steelmeter_config';
                                params = params+'&id=<?php echo $fingerprint; ?>';
                                params = params+'&device_id='+sc_device;
                                params = params+'&module_id='+sc_module;
                                params = params+'&measure_type='+sc_measurement;
                                params = params+'&design='+sc_design;
                                params = params+'&frame='+sc_frame;
                                params = params+'&background='+sc_background;
                                params = params+'&orientation='+sc_orientation;
                                params = params+'&main_pointer_type='+sc_main_pointer_type;
                                params = params+'&main_pointer_color='+sc_main_pointer_color;
                                params = params+'&aux_pointer_type='+sc_aux_pointer_type;
                                params = params+'&aux_pointer_color='+sc_aux_pointer_color;
                                params = params+'&knob='+sc_knob;
                                params = params+'&lcd='+sc_lcd;
                                params = params+'&alarm='+sc_alarm;
                                params = params+'&trend='+sc_trend;
                                params = params+'&minmax='+sc_minmax;
                                params = params+'&index_style='+sc_index_style;
                                params = params+'&index_color='+sc_index_color;
                                params = params+'&glass='+sc_glass;
                                params = params+'&size='+sc_size;
                                http.open('POST', '<?php echo LWS_AJAX_URL; ?>', true);
                                http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                                http.onreadystatechange = function () {
                                    if (http.readyState == 4 && http.status == 200) {
                                        if (sc_design.indexOf('analog-') > -1) {
                                            var g<?php echo $fingerprint; ?> = new steelseries.Radial('<?php echo $fingerprint; ?>', JSON.parse(http.responseText, function (k, v) {return eval(v);}));

                                        }
                                        if (sc_design.indexOf('digital-') > -1) {
                                            var g<?php echo $fingerprint; ?> = new steelseries.RadialBargraph('<?php echo $fingerprint; ?>', JSON.parse(http.responseText, function (k, v) {return eval(v);}));

                                        }
                                        if (sc_design.indexOf('meter-') > -1) {
                                            var g<?php echo $fingerprint; ?> = new steelseries.RadialVertical('<?php echo $fingerprint; ?>', JSON.parse(http.responseText, function (k, v) {return eval(v);}));

                                        }
                                        if (sc_design.indexOf('windcompass-') > -1) {
                                            var g<?php echo $fingerprint; ?> = new steelseries.WindDirection('<?php echo $fingerprint; ?>', JSON.parse(http.responseText, function (k, v) {return eval(v);}));

                                        }
                                        if (sc_design.indexOf('altimeter-') > -1) {
                                            var g<?php echo $fingerprint; ?> = new steelseries.Altimeter('<?php echo $fingerprint; ?>', JSON.parse(http.responseText, function (k, v) {return eval(v);}));

                                        }
                                        var http2 = new XMLHttpRequest();
                                        var params2 = 'action=lws_query_steelmeter_datas';
                                        params2 = params2+'&id=<?php echo $fingerprint; ?>';
                                        params2 = params2+'&device_id='+sc_device;
                                        params2 = params2+'&module_id='+sc_module;
                                        params2 = params2+'&measure_type='+sc_measurement;
                                        http2.open('POST', '<?php echo LWS_AJAX_URL; ?>', true);
                                        http2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                                        http2.onreadystatechange = function () {
                                            if (http2.readyState == 4 && http2.status == 200) {
                                                values = JSON.parse(http2.responseText);
                                                if (sc_design.indexOf('analog-') > -1) {
                                                    g<?php echo $fingerprint; ?>.setValue(values.value);
                                                    g<?php echo $fingerprint; ?>.setUserLedOnOff(values.alarm);
                                                    if (values.value_min > -9999) {
                                                        g<?php echo $fingerprint; ?>.setMinMeasuredValue(values.value_min);
                                                    }
                                                    if (values.value_max > -9999) {
                                                        g<?php echo $fingerprint; ?>.setMaxMeasuredValue(values.value_max);
                                                    }
                                                    if (values.value_trend == 'up') {
                                                        g<?php echo $fingerprint; ?>.setTrend(steelseries.TrendState.UP);
                                                    }
                                                    if (values.value_trend == 'down') {
                                                        g<?php echo $fingerprint; ?>.setTrend(steelseries.TrendState.DOWN);
                                                    }
                                                    if (values.value_trend == 'steady') {
                                                        g<?php echo $fingerprint; ?>.setTrend(steelseries.TrendState.STEADY);
                                                    }
                                                }
                                                if (sc_design.indexOf('digital-') > -1) {
                                                    g<?php echo $fingerprint; ?>.setValue(values.value);
                                                    g<?php echo $fingerprint; ?>.setUserLedOnOff(values.alarm);
                                                    if (values.value_trend == 'up') {
                                                        g<?php echo $fingerprint; ?>.setTrend(steelseries.TrendState.UP);
                                                    }
                                                    if (values.value_trend == 'down') {
                                                        g<?php echo $fingerprint; ?>.setTrend(steelseries.TrendState.DOWN);
                                                    }
                                                    if (values.value_trend == 'steady') {
                                                        g<?php echo $fingerprint; ?>.setTrend(steelseries.TrendState.STEADY);
                                                    }
                                                }
                                                if (sc_design.indexOf('meter-') > -1) {
                                                    g<?php echo $fingerprint; ?>.setValue(values.value);
                                                    if (values.value_min > -9999) {
                                                        g<?php echo $fingerprint; ?>.setMinMeasuredValue(values.value_min);
                                                    }
                                                    if (values.value_max > -9999) {
                                                        g<?php echo $fingerprint; ?>.setMaxMeasuredValue(values.value_max);
                                                    }
                                                }
                                                if (sc_design.indexOf('windcompass-') > -1) {
                                                    g<?php echo $fingerprint; ?>.setValueLatest(values.value);
                                                    g<?php echo $fingerprint; ?>.setValueAverage(values.value_aux);
                                                }
                                                if (sc_design.indexOf('altimeter-') > -1) {
                                                    g<?php echo $fingerprint; ?>.setValue(values.value);
                                                }
                                            }
                                        }
                                        http2.send(params2);
                                        $("#steelmeter-spinner-<?php echo $device_key; ?>").removeClass('is-active');
                                    }
                                }
                                http.send(params);
                            });
                            $("#steelmeter-datas-module-<?php echo $device_key; ?>" ).change();
                        });
                    </script>
                <?php } ?>
            </div>

        </div>
        <div class="postbox-container" style="width: 44%;margin-top:16px;">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '2. Verify the generated output', 'live-weather-station' );?></span></h3>
                    <div class="inside" style="height: 360px">
                        <div id="steelmeter-spinner-<?php echo $device_key; ?>" style="margin:0;width:100%;height:100%;background-position-x:50%;background-position-y:50%;" class="spinner"></div>
                        <div id="steelmeter-bg-<?php echo $device_key; ?>" style="border-radius: 5px;margin-bottom:10px;height:98%;width: 100%;float: inherit;display: flex;align-items: center;justify-content: center;top: -355px;position: relative;">
                            <canvas id="<?php echo $fingerprint; ?>"></canvas>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '3. Copy the following shortcode', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <textarea readonly rows="5" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="steelmeter-datas-shortcode-<?php echo $device_key; ?>"></textarea>
                    </div>
                    <div id="major-publishing-actions">
                        <div id="delete-action">
                            <?php esc_html_e('This shortcode is ready for use.', 'live-weather-station' );?>
                        </div>
                        <div id="publishing-action">
                            <button data-clipboard-target="#steelmeter-datas-shortcode-<?php echo $device_key; ?>" class="button button-primary steelmeter-cpy-<?php echo $device_key; ?>"><?php esc_attr_e('Copy', 'live-weather-station');?></button>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>