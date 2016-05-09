<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<div id="lcd-error-<?php echo $device_key; ?>" class="wrap" style="display:none;">
    <div id="lcd-error-container-<?php echo $device_key; ?>" class="metabox-holder">
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
            <?php if(json_encode($js_array_lcd[$device_key][2]) == '[]') { ?>
                <script language="javascript" type="text/javascript">
                    jQuery(document).ready(function($) {
                        $("#lcd-datas-link-<?php echo $device_key; ?>").click(function(){
                                tb_show('', '#TB_inline?width=400&height=200&inlineId=lcd-error-<?php echo $device_key; ?>');
                                $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('LCD display', 'live-weather-station');?>");
                            }
                        );
                    });
                </script>
            <?php } ?>
        </div>
    </div>
</div>
<div id="lcd-datas-<?php echo $device_key; ?>" class="wrap" style="display:none;">
    <div id="lcd-datas-container-<?php echo $device_key; ?>" class="metabox-holder">
        <div class="postbox-container" style="width: 100%;margin-right: 10px;margin-top:16px;">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '1. Set parameters for the shortcode', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <table cellspacing="0">
                            <tbody>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Module', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="lcd-datas-module-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_lcd[$device_key][2] as $key_module => $module) { ?>
                                                <option value="<?php echo $key_module; ?>"><?php echo $module[0]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Measurement', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="lcd-datas-measurement-<?php echo $device_key; ?>">
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Design', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="lcd-datas-design-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_lcd_design as $design) { ?>
                                                <option value="<?php echo $design[0]; ?>"><?php echo $design[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Size', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="lcd-datas-size-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_lcd_size as $size) { ?>
                                                <option value="<?php echo $size[0]; ?>"><?php echo $size[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Speed', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="lcd-datas-speed-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_lcd_speed as $speed) { ?>
                                                <option value="<?php echo $speed[0]; ?>"><?php echo $speed[1]; ?></option>;
                                            <?php } ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if(json_encode($js_array_lcd[$device_key][2]) != '[]') { ?>
                    <script language="javascript" type="text/javascript">
                        jQuery(document).ready(function($) {

                            <?php
                                $fingerprint = uniqid('', true);
                                $fingerprint = 'lcd'.substr ($fingerprint, count($fingerprint)-6, 80);
                             ?>

                             var c<?php echo $fingerprint; ?> = new lws_lcd.LCDPanel({
                                    id                     : 'id<?php echo $fingerprint; ?>',
                                    parentId               : '<?php echo $fingerprint; ?>',
                                    upperCenterText        : '<?php echo $device['station_name']; ?>',
                                    qDevice                : '<?php echo $device['_id']; ?>',
                                    qModule                : 'aggregated',
                                    qMeasure               : 'aggregated',
                                    qPostUrl               : '<?php echo LWS_AJAX_URL; ?>'
                                });



                            var js_array_lcd_<?php echo $device_key; ?> = <?php echo json_encode($js_array_lcd[$device_key][2]); ?>;

                            new Clipboard('.lcd-cpy-<?php echo $device_key; ?>');

                            $("#lcd-datas-link-<?php echo $device_key; ?>").click(function(){
                                    tb_show('', '#TB_inline?width=600&height=680&inlineId=lcd-datas-<?php echo $device_key; ?>');
                                    $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                    $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('LCD display', 'live-weather-station');?> - <?php echo $device['station_name']?>");
                                }
                            );

                            $("#lcd-datas-module-<?php echo $device_key; ?>").change(function() {
                                var js_array_lcd_measurement_<?php echo $device_key; ?> = js_array_lcd_<?php echo $device_key; ?>[$(this).val()][2];
                                $("#lcd-datas-measurement-<?php echo $device_key; ?>").html("");
                                $(js_array_lcd_measurement_<?php echo $device_key; ?>).each(function (i) {
                                    $("#lcd-datas-measurement-<?php echo $device_key; ?>").append("<option value="+i+">"+js_array_lcd_measurement_<?php echo $device_key; ?>[i][0]+"</option>");
                                });
                                c<?php echo $fingerprint; ?>.setModule(js_array_lcd_<?php echo $device_key; ?>[$("#lcd-datas-module-<?php echo $device_key; ?>").val()][1]);
                                $("#lcd-datas-measurement-<?php echo $device_key; ?>" ).change();
                            });

                            $("#lcd-datas-measurement-<?php echo $device_key; ?>").change(function() {
                                c<?php echo $fingerprint; ?>.setMeasure(js_array_lcd_<?php echo $device_key; ?>[$("#lcd-datas-module-<?php echo $device_key; ?>").val()][2][$("#lcd-datas-measurement-<?php echo $device_key; ?>").val()][1]);
                                $( "#lcd-datas-design-<?php echo $device_key; ?>" ).change();
                            });

                            $("#lcd-datas-design-<?php echo $device_key; ?>").change(function() {
                                c<?php echo $fingerprint; ?>.setDesign($("#lcd-datas-design-<?php echo $device_key; ?>").val());
                                $("#lcd-datas-size-<?php echo $device_key; ?>" ).change();
                            });

                            $("#lcd-datas-size-<?php echo $device_key; ?>").change(function() {
                                if ($("#lcd-datas-size-<?php echo $device_key; ?>").val()=='scalable') {
                                    c<?php echo $fingerprint; ?>.setSize('small', false);
                                    $("#lcd-info-<?php echo $device_key; ?>").show();
                                }
                                else {
                                    c<?php echo $fingerprint; ?>.setSize($("#lcd-datas-size-<?php echo $device_key; ?>").val(), false);
                                    $("#lcd-info-<?php echo $device_key; ?>").hide();
                                }
                                $("#lcd-datas-speed-<?php echo $device_key; ?>" ).change();
                            });

                            $("#lcd-datas-speed-<?php echo $device_key; ?>").change(function() {
                                c<?php echo $fingerprint; ?>.setCycleSpeed($("#lcd-datas-speed-<?php echo $device_key; ?>").val());
                                var sc_device = "<?php echo $device['_id']; ?>";
                                var sc_module = js_array_lcd_<?php echo $device_key; ?>[$("#lcd-datas-module-<?php echo $device_key; ?>").val()][1];
                                var sc_measurement = js_array_lcd_<?php echo $device_key; ?>[$("#lcd-datas-module-<?php echo $device_key; ?>").val()][2][$("#lcd-datas-measurement-<?php echo $device_key; ?>").val()][1];
                                var sc_design = $("#lcd-datas-design-<?php echo $device_key; ?>").val();
                                var sc_size = $("#lcd-datas-size-<?php echo $device_key; ?>").val();
                                var sc_speed = $("#lcd-datas-speed-<?php echo $device_key; ?>").val();
                                var shortcode = "[live-weather-station-lcd device_id='"+sc_device+"' module_id='"+sc_module+"' measure_type='"+sc_measurement+"' design='"+sc_design+"' size='"+sc_size+"' speed='"+sc_speed+"']";
                                $("#lcd-datas-shortcode-<?php echo $device_key; ?>").html(shortcode);
                            });

                            $("#lcd-datas-module-<?php echo $device_key; ?>" ).change();

                        });
                    </script>
                <?php } ?>

                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '2. Verify the generated output', 'live-weather-station' );?></span></h3>
                    <div class="inside" style="text-align: center;">
                        <div id="<?php echo $fingerprint; ?>" style="padding:0px;"></div>
                    </div>
                    <span id="lcd-info-<?php echo $device_key; ?>" style="display: none;">
                        <div id="major-publishing-actions">
                            <?php esc_html_e('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );?>
                        </div>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '3. Copy the following shortcode', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <textarea readonly rows="3" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="lcd-datas-shortcode-<?php echo $device_key; ?>"></textarea>
                    </div>
                    <div id="major-publishing-actions">
                        <div id="delete-action">
                            <?php esc_html_e('This shortcode is ready for use.', 'live-weather-station' );?>
                        </div>
                        <div id="publishing-action">
                            <button data-clipboard-target="#lcd-datas-shortcode-<?php echo $device_key; ?>" class="button button-primary lcd-cpy-<?php echo $device_key; ?>"><?php esc_attr_e('Copy', 'live-weather-station');?></button>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>