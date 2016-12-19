<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
?>
<div id="lcd-error-<?php echo $station_guid; ?>" class="wrap" style="display:none;">
    <div id="lcd-error-container-<?php echo $station_guid; ?>" class="metabox-holder">
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
            <?php if (!isset($js_array_lcd[$station_guid][2])) { ?>
                <script language="javascript" type="text/javascript">
                    jQuery(document).ready(function($) {
                        $("#lcd-datas-link-<?php echo $station_guid; ?>").click(function(){
                                tb_show('', '#TB_inline?width=400&height=200&inlineId=lcd-error-<?php echo $station_guid; ?>');
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
<?php if (isset($js_array_lcd[$station_guid][2])) { ?>
    <div id="lcd-datas-<?php echo $station_guid; ?>" class="wrap" style="display:none;">
        <div id="lcd-datas-container-<?php echo $station_guid; ?>" class="metabox-holder">
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
                                            <select class="option-select" id="lcd-datas-module-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_lcd[$station_guid][2] as $key_module => $module) { ?>
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
                                            <select class="option-select" id="lcd-datas-measurement-<?php echo $station_guid; ?>">
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Design', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" id="lcd-datas-design-<?php echo $station_guid; ?>">
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
                                            <select class="option-select" id="lcd-datas-size-<?php echo $station_guid; ?>">
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
                                            <select class="option-select" id="lcd-datas-speed-<?php echo $station_guid; ?>">
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
                    <script language="javascript" type="text/javascript">
                        jQuery(document).ready(function($) {

                            <?php
                            $fingerprint = uniqid('', true);
                            $fingerprint = 'lcd'.substr ($fingerprint, count($fingerprint)-6, 80);
                            ?>

                            var c<?php echo $fingerprint; ?> = new lws_lcd.LCDPanel({
                                id                     : 'id<?php echo $fingerprint; ?>',
                                parentId               : '<?php echo $fingerprint; ?>',
                                upperCenterText        : '<?php echo $station_name; ?>',
                                qDevice                : '<?php echo $station_id; ?>',
                                qModule                : 'aggregated',
                                qMeasure               : 'aggregated',
                                qPostUrl               : '<?php echo LWS_AJAX_URL; ?>'
                            });



                            var js_array_lcd_<?php echo $station_guid; ?> = <?php echo json_encode($js_array_lcd[$station_guid][2]); ?>;

                            new Clipboard('.lcd-cpy-<?php echo $station_guid; ?>');

                            $("#lcd-datas-link-<?php echo $station_guid; ?>").click(function(){
                                    tb_show('', '#TB_inline?width=600&height=680&inlineId=lcd-datas-<?php echo $station_guid; ?>');
                                    $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                    $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('LCD display', 'live-weather-station');?> - <?php echo $station_name; ?>");
                                }
                            );

                            $("#lcd-datas-module-<?php echo $station_guid; ?>").change(function() {
                                var js_array_lcd_measurement_<?php echo $station_guid; ?> = js_array_lcd_<?php echo $station_guid; ?>[$(this).val()][2];
                                $("#lcd-datas-measurement-<?php echo $station_guid; ?>").html("");
                                $(js_array_lcd_measurement_<?php echo $station_guid; ?>).each(function (i) {
                                    $("#lcd-datas-measurement-<?php echo $station_guid; ?>").append("<option value="+i+">"+js_array_lcd_measurement_<?php echo $station_guid; ?>[i][0]+"</option>");
                                });
                                c<?php echo $fingerprint; ?>.setModule(js_array_lcd_<?php echo $station_guid; ?>[$("#lcd-datas-module-<?php echo $station_guid; ?>").val()][1]);
                                $("#lcd-datas-measurement-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#lcd-datas-measurement-<?php echo $station_guid; ?>").change(function() {
                                c<?php echo $fingerprint; ?>.setMeasure(js_array_lcd_<?php echo $station_guid; ?>[$("#lcd-datas-module-<?php echo $station_guid; ?>").val()][2][$("#lcd-datas-measurement-<?php echo $station_guid; ?>").val()][1]);
                                $( "#lcd-datas-design-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#lcd-datas-design-<?php echo $station_guid; ?>").change(function() {
                                c<?php echo $fingerprint; ?>.setDesign($("#lcd-datas-design-<?php echo $station_guid; ?>").val());
                                $("#lcd-datas-size-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#lcd-datas-size-<?php echo $station_guid; ?>").change(function() {
                                if ($("#lcd-datas-size-<?php echo $station_guid; ?>").val()=='scalable') {
                                    c<?php echo $fingerprint; ?>.setSize('small', false);
                                    $("#lcd-info-<?php echo $station_guid; ?>").show();
                                }
                                else {
                                    c<?php echo $fingerprint; ?>.setSize($("#lcd-datas-size-<?php echo $station_guid; ?>").val(), false);
                                    $("#lcd-info-<?php echo $station_guid; ?>").hide();
                                }
                                $("#lcd-datas-speed-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#lcd-datas-speed-<?php echo $station_guid; ?>").change(function() {
                                c<?php echo $fingerprint; ?>.setCycleSpeed($("#lcd-datas-speed-<?php echo $station_guid; ?>").val());
                                var sc_device = "<?php echo $station_id; ?>";
                                var sc_module = js_array_lcd_<?php echo $station_guid; ?>[$("#lcd-datas-module-<?php echo $station_guid; ?>").val()][1];
                                var sc_measurement = js_array_lcd_<?php echo $station_guid; ?>[$("#lcd-datas-module-<?php echo $station_guid; ?>").val()][2][$("#lcd-datas-measurement-<?php echo $station_guid; ?>").val()][1];
                                var sc_design = $("#lcd-datas-design-<?php echo $station_guid; ?>").val();
                                var sc_size = $("#lcd-datas-size-<?php echo $station_guid; ?>").val();
                                var sc_speed = $("#lcd-datas-speed-<?php echo $station_guid; ?>").val();
                                var shortcode = "[live-weather-station-lcd device_id='"+sc_device+"' module_id='"+sc_module+"' measure_type='"+sc_measurement+"' design='"+sc_design+"' size='"+sc_size+"' speed='"+sc_speed+"']";
                                $("#lcd-datas-shortcode-<?php echo $station_guid; ?>").html(shortcode);
                            });

                            $("#lcd-datas-module-<?php echo $station_guid; ?>" ).change();

                        });
                    </script>
                    <div class="postbox ">
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                        <h3 class="hndle"><span><?php esc_html_e( '2. Verify the generated output', 'live-weather-station' );?></span></h3>
                        <div class="inside" style="text-align: center;">
                            <div id="<?php echo $fingerprint; ?>" style="padding:0px;"></div>
                        </div>
                        <span id="lcd-info-<?php echo $station_guid; ?>" style="display: none;">
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
                            <textarea readonly rows="3" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="lcd-datas-shortcode-<?php echo $station_guid; ?>"></textarea>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <button data-clipboard-target="#lcd-datas-shortcode-<?php echo $station_guid; ?>" class="button button-primary lcd-cpy-<?php echo $station_guid; ?>"><?php esc_attr_e('Copy', 'live-weather-station');?></button>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>