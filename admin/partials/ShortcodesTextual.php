<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

?>

<div id="textual-error-<?php echo $station_guid; ?>" class="wrap" style="display:none;">
    <div id="textual-error-container-<?php echo $station_guid; ?>" class="metabox-holder">
        <div class="postbox-container" style="width: 100%;margin-top:16px;">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div class="postbox ">
                    <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                    <h3 class="hndle"><span><?php esc_html_e('No data', 'live-weather-station' );?></span></h3>
                    <div class="inside">
                        <?php esc_html_e('There is currently no data collected for this station and, for this reason, it is not possible to generate shortcodes. This is normally a temporary condition so, please, retry later or force a resynchronization.', 'live-weather-station' );?>
                    </div>
                </div>
            </div>
            <?php if (!isset($js_array_textual[$station_guid][2])) { ?>
                <script language="javascript" type="text/javascript">
                    jQuery(document).ready(function($) {
                        $("#textual-datas-link-<?php echo $station_guid; ?>").click(function(){
                                tb_show('', '#TB_inline?width=400&height=200&inlineId=textual-error-<?php echo $station_guid; ?>');
                                $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('textual data', 'live-weather-station');?>");
                            }
                        );
                    });
                </script>
            <?php } ?>
        </div>
    </div>
</div>
<?php if (isset($js_array_textual[$station_guid][2])) { ?>
    <div id="textual-datas-<?php echo $station_guid; ?>" class="wrap" style="display:none;">
        <div id="textual-datas-container-<?php echo $station_guid; ?>" class="metabox-holder">
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
                                            <select class="option-select" id="textual-datas-module-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_textual[$station_guid][2] as $key_module => $module) { ?>
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
                                            <select class="option-select" id="textual-datas-measurement-<?php echo $station_guid; ?>">
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Element', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" id="textual-datas-element-<?php echo $station_guid; ?>">
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Format', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" id="textual-datas-format-<?php echo $station_guid; ?>">
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

                            var js_array_textual_<?php echo $station_guid; ?> = <?php echo json_encode($js_array_textual[$station_guid][2]); ?>;

                            new Clipboard('.textual-cpy-<?php echo $station_guid; ?>');

                            $("#textual-datas-link-<?php echo $station_guid; ?>").click(function(){
                                    tb_show('', '#TB_inline?width=600&height=560&inlineId=textual-datas-<?php echo $station_guid; ?>');
                                    $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                    $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('textual data', 'live-weather-station');?> - <?php echo $station_name; ?>");
                                }
                            );

                            $("#textual-datas-module-<?php echo $station_guid; ?>").change(function() {
                                var js_array_textual_measurement_<?php echo $station_guid; ?> = js_array_textual_<?php echo $station_guid; ?>[$(this).val()][2];
                                $("#textual-datas-measurement-<?php echo $station_guid; ?>").html("");
                                $(js_array_textual_measurement_<?php echo $station_guid; ?>).each(function (i) {
                                    $("#textual-datas-measurement-<?php echo $station_guid; ?>").append("<option value="+i+">"+js_array_textual_measurement_<?php echo $station_guid; ?>[i][0]+"</option>");
                                });
                                $( "#textual-datas-measurement-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#textual-datas-measurement-<?php echo $station_guid; ?>").change(function() {
                                var js_array_textual_element_<?php echo $station_guid; ?> = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][2][$(this).val()][2];
                                $("#textual-datas-element-<?php echo $station_guid; ?>").html("");
                                $(js_array_textual_element_<?php echo $station_guid; ?>).each(function (i) {
                                    $("#textual-datas-element-<?php echo $station_guid; ?>").append("<option value="+i+">"+js_array_textual_element_<?php echo $station_guid; ?>[i][0]+"</option>");
                                });
                                $( "#textual-datas-element-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#textual-datas-element-<?php echo $station_guid; ?>").change(function() {
                                var js_array_textual_format_<?php echo $station_guid; ?> = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-measurement-<?php echo $station_guid; ?>").val()][2][$(this).val()][2];
                                $("#textual-datas-format-<?php echo $station_guid; ?>").html("");
                                $(js_array_textual_format_<?php echo $station_guid; ?>).each(function (i) {
                                    $("#textual-datas-format-<?php echo $station_guid; ?>").append("<option value="+i+">"+js_array_textual_format_<?php echo $station_guid; ?>[i][0]+"</option>");
                                });
                                $( "#textual-datas-format-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#textual-datas-format-<?php echo $station_guid; ?>").change(function() {
                                var output = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-measurement-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-element-<?php echo $station_guid; ?>").val()][2][$(this).val()][2];
                                var sc_device = "<?php echo $station_id; ?>";
                                var sc_module = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][1];
                                var sc_measurement = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-measurement-<?php echo $station_guid; ?>").val()][1];
                                var sc_element = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-measurement-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-element-<?php echo $station_guid; ?>").val()][1];
                                var sc_format = js_array_textual_<?php echo $station_guid; ?>[$("#textual-datas-module-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-measurement-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-element-<?php echo $station_guid; ?>").val()][2][$("#textual-datas-format-<?php echo $station_guid; ?>").val()][1];
                                var shortcode = "[live-weather-station-textual device_id='"+sc_device+"' module_id='"+sc_module+"' measure_type='"+sc_measurement+"' element='"+sc_element+"' format='"+sc_format+"']";
                                $("#textual-datas-output-<?php echo $station_guid; ?>").html(output);
                                $("#textual-datas-shortcode-<?php echo $station_guid; ?>").html(shortcode);
                            });

                            $( "#textual-datas-module-<?php echo $station_guid; ?>" ).change();
                        });
                    </script>
                    <div class="postbox ">
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                        <h3 class="hndle"><span><?php esc_html_e( '2. Verify the generated output', 'live-weather-station' );?></span></h3>
                        <div class="inside">
                            <textarea readonly rows="1" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="textual-datas-output-<?php echo $station_guid; ?>" style="font-weight:bold;"></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="postbox ">
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                        <h3 class="hndle"><span><?php esc_html_e( '3. Copy the following shortcode', 'live-weather-station' );?></span></h3>
                        <div class="inside">
                            <textarea readonly rows="3" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="textual-datas-shortcode-<?php echo $station_guid; ?>"></textarea>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <button data-clipboard-target="#textual-datas-shortcode-<?php echo $station_guid; ?>" class="button button-primary textual-cpy-<?php echo $station_guid; ?>"><?php esc_attr_e('Copy', 'live-weather-station');?></button>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>