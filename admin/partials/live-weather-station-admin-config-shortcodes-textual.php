<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */
?>
<div id="textual-datas-<?php echo $device_key; ?>" class="wrap" style="display:none;">
    <div id="textual-datas-container-<?php echo $device_key; ?>" class="metabox-holder">
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
                                        <select class="option-select" id="textual-datas-module-<?php echo $device_key; ?>">
                                            <?php foreach($js_array_textual[$device_key][2] as $key_module => $module) { ?>
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
                                        <select class="option-select" id="textual-datas-measurement-<?php echo $device_key; ?>">
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Element', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="textual-datas-element-<?php echo $device_key; ?>">
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Format', 'live-weather-station');?></th>
                                <td width="5%"/>
                                <td align="left">
                                    <span class="select-option">
                                        <select class="option-select" id="textual-datas-format-<?php echo $device_key; ?>">
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script language="javascript" type="text/javascript">
                    jQuery(document).ready(function($) {

                        var js_array_textual_<?php echo $device_key; ?> = <?php echo json_encode($js_array_textual[$device_key][2]); ?>;

                        new Clipboard('.textual-cpy-<?php echo $device_key; ?>');

                        $("#textual-datas-link-<?php echo $device_key; ?>").click(function(){
                                tb_show('', '#TB_inline?width=600&height=620&inlineId=textual-datas-<?php echo $device_key; ?>');
                                $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('textual datas', 'live-weather-station');?> - <?php echo $device['station_name']?>");
                            }
                        );

                        $("#textual-datas-module-<?php echo $device_key; ?>").change(function() {
                            var js_array_textual_measurement_<?php echo $device_key; ?> = js_array_textual_<?php echo $device_key; ?>[$(this).val()][2];
                            $("#textual-datas-measurement-<?php echo $device_key; ?>").html("");
                            $(js_array_textual_measurement_<?php echo $device_key; ?>).each(function (i) {
                                $("#textual-datas-measurement-<?php echo $device_key; ?>").append("<option value="+i+">"+js_array_textual_measurement_<?php echo $device_key; ?>[i][0]+"</option>");
                            });
                            $( "#textual-datas-measurement-<?php echo $device_key; ?>" ).change();
                        });

                        $("#textual-datas-measurement-<?php echo $device_key; ?>").change(function() {
                            var js_array_textual_element_<?php echo $device_key; ?> = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][2][$(this).val()][2];
                            $("#textual-datas-element-<?php echo $device_key; ?>").html("");
                            $(js_array_textual_element_<?php echo $device_key; ?>).each(function (i) {
                                $("#textual-datas-element-<?php echo $device_key; ?>").append("<option value="+i+">"+js_array_textual_element_<?php echo $device_key; ?>[i][0]+"</option>");
                            });
                            $( "#textual-datas-element-<?php echo $device_key; ?>" ).change();
                        });

                        $("#textual-datas-element-<?php echo $device_key; ?>").change(function() {
                            var js_array_textual_format_<?php echo $device_key; ?> = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][2][$("#textual-datas-measurement-<?php echo $device_key; ?>").val()][2][$(this).val()][2];
                            $("#textual-datas-format-<?php echo $device_key; ?>").html("");
                            $(js_array_textual_format_<?php echo $device_key; ?>).each(function (i) {
                                $("#textual-datas-format-<?php echo $device_key; ?>").append("<option value="+i+">"+js_array_textual_format_<?php echo $device_key; ?>[i][0]+"</option>");
                            });
                            $( "#textual-datas-format-<?php echo $device_key; ?>" ).change();
                        });

                        $("#textual-datas-format-<?php echo $device_key; ?>").change(function() {
                            var output = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][2][$("#textual-datas-measurement-<?php echo $device_key; ?>").val()][2][$("#textual-datas-element-<?php echo $device_key; ?>").val()][2][$(this).val()][2];
                            var sc_device = "<?php echo $device['_id']; ?>";
                            var sc_module = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][1];
                            var sc_measurement = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][2][$("#textual-datas-measurement-<?php echo $device_key; ?>").val()][1];
                            var sc_element = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][2][$("#textual-datas-measurement-<?php echo $device_key; ?>").val()][2][$("#textual-datas-element-<?php echo $device_key; ?>").val()][1];
                            var sc_format = js_array_textual_<?php echo $device_key; ?>[$("#textual-datas-module-<?php echo $device_key; ?>").val()][2][$("#textual-datas-measurement-<?php echo $device_key; ?>").val()][2][$("#textual-datas-element-<?php echo $device_key; ?>").val()][2][$("#textual-datas-format-<?php echo $device_key; ?>").val()][1];
                            var shortcode = "[live-weather-station-textual device_id='"+sc_device+"' module_id='"+sc_module+"' measure_type='"+sc_measurement+"' element='"+sc_element+"' format='"+sc_format+"']";
                            $("#textual-datas-output-<?php echo $device_key; ?>").html(output);
                            $("#textual-datas-shortcode-<?php echo $device_key; ?>").html(shortcode);
                        });

                        $( "#textual-datas-module-<?php echo $device_key; ?>" ).change();
                    });
                </script>
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '2. Verify the generated output', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <textarea readonly rows="1" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="textual-datas-output-<?php echo $device_key; ?>" style="font-weight:bold;"></textarea>
                    </div>
                    <div id="major-publishing-actions">
                        <?php esc_html_e('This value is for illustration only and is not the precise value that will be computed at runtime.', 'live-weather-station' );?>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e( '3. Copy the following shortcode', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <textarea readonly rows="3" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="textual-datas-shortcode-<?php echo $device_key; ?>"></textarea>
                    </div>
                    <div id="major-publishing-actions">
                        <div id="delete-action">
                            <?php esc_html_e('This shortcode is ready for use.', 'live-weather-station' );?>
                        </div>
                        <div id="publishing-action">
                            <button data-clipboard-target="#textual-datas-shortcode-<?php echo $device_key; ?>" class="button button-primary textual-cpy-<?php echo $device_key; ?>"><?php esc_attr_e('Copy', 'live-weather-station');?></button>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>