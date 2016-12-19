<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

?>
<div id="justgage-error-<?php echo $station_guid; ?>" class="wrap" style="display:none;">
    <div id="justgage-error-container-<?php echo $station_guid; ?>" class="metabox-holder">
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
            <?php if (!isset($js_array_justgage[$station_guid][2])) { ?>
                <script language="javascript" type="text/javascript">
                    jQuery(document).ready(function($) {
                        $("#justgage-datas-link-<?php echo $station_guid; ?>").click(function(){
                                tb_show('', '#TB_inline?width=400&height=200&inlineId=justgage-error-<?php echo $station_guid; ?>');
                                $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('clean gauge', 'live-weather-station');?>");
                            }
                        );
                    });
                </script>
            <?php } ?>
        </div>
    </div>
</div>
<?php if (isset($js_array_justgage[$station_guid][2])) { ?>
    <div id="justgage-datas-<?php echo $station_guid; ?>" class="wrap" style="display:none;">
        <div id="justgage-datas-container-<?php echo $station_guid; ?>" class="metabox-holder">
            <div class="postbox-container" style="width: 49%;margin-right: 16px;margin-top:16px;">
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
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-module-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage[$station_guid][2] as $key_module => $module) { ?>
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
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-measurement-<?php echo $station_guid; ?>">
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Design', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-design-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_design as $design) { ?>
                                                    <option value="<?php echo $design[0]; ?>"><?php echo $design[1]; ?></option>;
                                                <?php } ?>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Colors', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-color-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_color as $color) { ?>
                                                    <option value="<?php echo $color[0]; ?>"><?php echo $color[1]; ?></option>;
                                                <?php } ?>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Pointer', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-pointer-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_pointer as $pointer) { ?>
                                                    <option value="<?php echo $pointer[0]; ?>"><?php echo $pointer[1]; ?></option>;
                                                <?php } ?>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Title', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-title-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_title as $title) { ?>
                                                    <option value="<?php echo $title[0]; ?>"><?php echo $title[1]; ?></option>;
                                                <?php } ?>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Label', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-subtitle-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_title as $subtitle) { ?>
                                                    <option value="<?php echo $subtitle[0]; ?>"><?php echo $subtitle[1]; ?></option>;
                                                <?php } ?>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="lws-option" width="35%" align="left" scope="row"><?php esc_html_e('Unit', 'live-weather-station');?></th>
                                    <td width="5%"/>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-unit-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_unit as $unit) { ?>
                                                    <option value="<?php echo $unit[0]; ?>"><?php echo $unit[1]; ?></option>;
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
                                            <select class="option-select" style="width: 270px;" id="justgage-datas-size-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_size as $size) { ?>
                                                    <option value="<?php echo $size[0]; ?>"<?php echo($size[0]=='medium'?'SELECTED':''); ?>><?php echo $size[1]; ?></option>;
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
                            $fingerprint = 'jgg'.substr ($fingerprint, count($fingerprint)-6, 80);
                            ?>

                            var js_array_justgage_<?php echo $station_guid; ?> = <?php echo json_encode($js_array_justgage[$station_guid][2]); ?>;

                            new Clipboard('.justgage-cpy-<?php echo $station_guid; ?>');

                            $("#justgage-datas-link-<?php echo $station_guid; ?>").click(function(){
                                    tb_show('', '#TB_inline?width=800&height=640&inlineId=justgage-datas-<?php echo $station_guid; ?>');
                                    $("#TB_ajaxContent").css("background-color",$(".wp-toolbar").css("backgroundColor"));
                                    $("#TB_ajaxWindowTitle").html("<?php esc_html_e('Shortcodes for', 'live-weather-station');?> <?php esc_html_e('clean gauge', 'live-weather-station');?> - <?php echo $station_name; ?>");
                                }
                            );

                            $("#justgage-datas-module-<?php echo $station_guid; ?>").change(function() {
                                var js_array_justgage_measurement_<?php echo $station_guid; ?> = js_array_justgage_<?php echo $station_guid; ?>[$(this).val()][2];
                                $("#justgage-datas-measurement-<?php echo $station_guid; ?>").html("");
                                $(js_array_justgage_measurement_<?php echo $station_guid; ?>).each(function (i) {
                                    $("#justgage-datas-measurement-<?php echo $station_guid; ?>").append("<option value="+i+">"+js_array_justgage_measurement_<?php echo $station_guid; ?>[i][0]+"</option>");
                                });
                                $("#justgage-datas-measurement-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-measurement-<?php echo $station_guid; ?>").change(function() {
                                $( "#justgage-datas-design-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-design-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-datas-color-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-color-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-datas-pointer-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-pointer-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-datas-title-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-title-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-datas-subtitle-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-subtitle-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-datas-unit-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-unit-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-datas-size-<?php echo $station_guid; ?>" ).change();
                            });

                            $("#justgage-datas-size-<?php echo $station_guid; ?>").change(function() {
                                if ($("#justgage-datas-size-<?php echo $station_guid; ?>").val()=='scalable') {
                                    $("#justgage-info-<?php echo $station_guid; ?>").show();
                                }
                                else {
                                    $("#justgage-info-<?php echo $station_guid; ?>").hide();
                                }
                                if ($("#justgage-datas-size-<?php echo $station_guid; ?>").val()=='micro') {
                                    $("#justgage-datas-pointer-<?php echo $station_guid; ?>").val('none');
                                    $("#justgage-datas-pointer-<?php echo $station_guid; ?>").prop('disabled', true);
                                    $("#justgage-datas-title-<?php echo $station_guid; ?>").val('none');
                                    $("#justgage-datas-title-<?php echo $station_guid; ?>").prop('disabled', true);
                                    $("#justgage-datas-subtitle-<?php echo $station_guid; ?>").val('none');
                                    $("#justgage-datas-subtitle-<?php echo $station_guid; ?>").prop('disabled', true);
                                    $("#justgage-datas-unit-<?php echo $station_guid; ?>").val('none');
                                    $("#justgage-datas-unit-<?php echo $station_guid; ?>").prop('disabled', true);
                                }
                                else {
                                    $("#justgage-datas-pointer-<?php echo $station_guid; ?>").prop('disabled', false);
                                    $("#justgage-datas-title-<?php echo $station_guid; ?>").prop('disabled', false);
                                    $("#justgage-datas-subtitle-<?php echo $station_guid; ?>").prop('disabled', false);
                                    $("#justgage-datas-unit-<?php echo $station_guid; ?>").prop('disabled', false);
                                }
                                var sc_device = "<?php echo $station_id; ?>";
                                var sc_module = js_array_justgage_<?php echo $station_guid; ?>[$("#justgage-datas-module-<?php echo $station_guid; ?>").val()][1];
                                var sc_measurement = js_array_justgage_<?php echo $station_guid; ?>[$("#justgage-datas-module-<?php echo $station_guid; ?>").val()][2][$("#justgage-datas-measurement-<?php echo $station_guid; ?>").val()][1];
                                var sc_design = $("#justgage-datas-design-<?php echo $station_guid; ?>").val();
                                var sc_color = $("#justgage-datas-color-<?php echo $station_guid; ?>").val();
                                var sc_pointer = $("#justgage-datas-pointer-<?php echo $station_guid; ?>").val();
                                var sc_title = $("#justgage-datas-title-<?php echo $station_guid; ?>").val();
                                var sc_subtitle = $("#justgage-datas-subtitle-<?php echo $station_guid; ?>").val();
                                var sc_unit = $("#justgage-datas-unit-<?php echo $station_guid; ?>").val();
                                var sc_size = $("#justgage-datas-size-<?php echo $station_guid; ?>").val();
                                var shortcode = "[live-weather-station-justgage device_id='"+sc_device+"' module_id='"+sc_module+"' measure_type='"+sc_measurement+"' design='"+sc_design+"' color='"+sc_color+"' pointer='"+sc_pointer+"' title='"+sc_title+"' subtitle='"+sc_subtitle+"' unit='"+sc_unit+"' size='"+sc_size+"']";
                                $("#justgage-datas-shortcode-<?php echo $station_guid; ?>").html(shortcode);
                                $("#justgage-bg-<?php echo $station_guid; ?>" ).css('background-color', 'transparent');
                                $("#justgage-spinner-<?php echo $station_guid; ?>").addClass('is-active');
                                $("#<?php echo $fingerprint; ?>" ).empty();
                                if (sc_size=='micro') {
                                    $("#<?php echo $fingerprint; ?>" ).width(75).height(75);
                                }
                                if (sc_size=='small') {
                                    $("#<?php echo $fingerprint; ?>" ).width(100).height(100);
                                }
                                if (sc_size=='medium') {
                                    $("#<?php echo $fingerprint; ?>" ).width(225).height(225);
                                }
                                if (sc_size=='large' || sc_size=='scalable') {
                                    $("#<?php echo $fingerprint; ?>" ).width(350).height(350);
                                }

                                var http = new XMLHttpRequest();
                                var params = 'action=lws_query_justgage_config';
                                params = params+'&id=<?php echo $fingerprint; ?>';
                                params = params+'&device_id='+sc_device;
                                params = params+'&module_id='+sc_module;
                                params = params+'&measure_type='+sc_measurement;
                                params = params+'&design='+sc_design;
                                params = params+'&color='+sc_color;
                                params = params+'&pointer='+sc_pointer;
                                params = params+'&title='+sc_title;
                                params = params+'&subtitle='+sc_subtitle;
                                params = params+'&unit='+sc_unit;
                                params = params+'&size='+sc_size;
                                http.open('POST', '<?php echo LWS_AJAX_URL; ?>', true);
                                http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                                http.onreadystatechange = function () {
                                    if (http.readyState == 4 && http.status == 200) {
                                        var g<?php echo $fingerprint; ?> = new JustGage(JSON.parse(http.responseText));
                                        $("#justgage-spinner-<?php echo $station_guid; ?>").removeClass('is-active');
                                        $("#justgage-bg-color-<?php echo $station_guid; ?>" ).change();
                                    }
                                }
                                http.send(params);
                            });

                            $("#justgage-bg-color-<?php echo $station_guid; ?>").change(function() {
                                $("#justgage-bg-<?php echo $station_guid; ?>" ).css('background-color', $("#justgage-bg-color-<?php echo $station_guid; ?>").val());
                            });
                            $("#justgage-bg-color-<?php echo $station_guid; ?>" ).change();

                            $("#justgage-datas-module-<?php echo $station_guid; ?>" ).change();

                        });
                    </script>
                </div>
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    <div class="postbox ">
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                        <h3 class="hndle"><span><?php esc_html_e( '3. Copy the following shortcode', 'live-weather-station' );?></span></h3>
                        <div class="inside">
                            <textarea readonly rows="4" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="justgage-datas-shortcode-<?php echo $station_guid; ?>"></textarea>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <button data-clipboard-target="#justgage-datas-shortcode-<?php echo $station_guid; ?>" class="button button-primary justgage-cpy-<?php echo $station_guid; ?>"><?php esc_attr_e('Copy', 'live-weather-station');?></button>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="postbox-container" style="width: 49%;margin-top:16px;">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    <div class="postbox ">
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
                        <h3 class="hndle"><span><?php esc_html_e( '2. Verify the generated output', 'live-weather-station' );?></span></h3>
                        <div class="inside" style="height: 460px">
                            <table cellspacing="0" style="margin-bottom: 8px;">
                                <tbody>
                                <tr>
                                    <td align="left">
                                        <span class="select-option">
                                            <select class="option-select" style="width:360px;" id="justgage-bg-color-<?php echo $station_guid; ?>">
                                                <?php foreach($js_array_justgage_background as $color) { ?>
                                                    <option value="<?php echo $color[0]; ?>"><?php echo $color[1]; ?></option>;
                                                <?php } ?>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div id="justgage-spinner-<?php echo $station_guid; ?>" style="margin:0;width:100%;height:100%;background-position-x:50%;background-position-y:50%;" class="spinner"></div>
                            <div id="justgage-bg-<?php echo $station_guid; ?>" style="border-radius: 5px;margin-bottom:10px;height:98%;width: 100%;float: inherit;display: flex;align-items: center;justify-content: center;top: -460px;position: relative;">
                                <div id="<?php echo $fingerprint; ?>"></div>
                            </div>
                        </div>
                        <span id="justgage-info-<?php echo $station_guid; ?>" style="display: none;">
                                <div id="major-publishing-actions">
                                    <?php esc_html_e('This controls will be dynamically resized to fit its parent\'s size.', 'live-weather-station' );?>
                                </div>
                            </span>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>