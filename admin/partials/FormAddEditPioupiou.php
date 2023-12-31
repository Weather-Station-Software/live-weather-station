<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */

$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');
$message = __('Adding this station, please wait', 'live-weather-station');
if ($error_message == '') {
    $errmsg = __('The station you specified is not accessible. Please, verify its ID and retry.', 'live-weather-station' );
}
else {
    $errmsg = __('Unable to access this station:', 'live-weather-station' ) . ' ' . $error_message;
}
if (!isset($station['station_model'])) {
    $station['station_model'] = 'Pioupiou V1';
}
?>

<div class="wrap">
    <?php if ($station['guid'] == 0) { ?>
        <h1><?php _e('Add a Pioupiou sensor as a station', 'live-weather-station');?></h1>
    <?php } ?>
    <?php if ($station['guid'] != 0) { ?>
        <h1><?php _e('Edit a Pioupiou sensor as a station', 'live-weather-station');?></h1>
    <?php } ?>
    <form method="post" name="add-edit-piou-form" id="add-edit-piou-form" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
        <input name="station_id" type="hidden" value="<?php echo $station['station_id']; ?>" />
        <input name="guid" type="hidden" value="<?php echo $station['guid']; ?>" />
        <input name="service" type="hidden" value="pioupiou" />
        <input name="tab" type="hidden" value="add-edit" />
        <input name="action" type="hidden" value="do" />
        <?php if ($dashboard) { ?>
            <input name="dashboard" type="hidden" value="1" />
        <?php } ?>
        <?php wp_nonce_field('add-edit-piou'); ?>
        <table class="form-table">
            <tr class="form-field form-required">
                <th scope="row"><label for="station_model"><?php esc_html_e('Station model', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td>
                    <select name="station_model" id="station_model" style="width:25em;">
                        <?php foreach ($models as $val) { ?>
                            <option value="<?php echo $val ?>"<?php if ($station['station_model']==$val) {?> selected="selected"<?php } ?>><?php echo $val; ?></option>;
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_city"><?php esc_html_e('City', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="loc_city" type="text" id="loc_city" value="<?php echo htmlspecialchars($station['loc_city']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_country_code"><?php esc_html_e('Country', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td>
                    <select name="loc_country_code" id="loc_country_code" style="width:25em;">
                        <?php foreach ($countries as $key => $val) { ?>
                            <option value="<?php echo $key ?>"<?php if ($station['loc_country_code']==$key) {?> selected="selected"<?php } ?>><?php echo $val ?></option>;
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_tz"><?php esc_html_e('Time zone', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td>
                    <select name="loc_tz" id="loc_tz" style="width:25em;">
                    </select>
                </td>
            </tr>

            <script language="javascript" type="text/javascript">
                jQuery(document).ready(function($) {

                    var js_array_tz_all = <?php echo json_encode($timezones); ?>;
                    var actual_tz = "<?php echo $station['loc_timezone']; ?>";
                    var selected = "";

                    $("#loc_country_code").change(function() {
                        var js_array_tz = js_array_tz_all[$(this).val()];
                        $("#loc_tz").html("");
                        $(js_array_tz_all[$(this).val()]).each(function (i) {
                            if (js_array_tz[i][0] == actual_tz) {
                                selected = " selected=\"selected\"";
                            }
                            else {
                                selected = "";
                            }
                            $("#loc_tz").append("<option value="+js_array_tz[i][0]+selected+">"+js_array_tz[i][1]+"</option>");
                        });
                    });

                    $("#loc_country_code").change();
                });
            </script>
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_altitude"><?php esc_html_e('Altitude (in meters)', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="loc_altitude" type="text" id="loc_altitude" value="<?php echo htmlspecialchars($station['loc_altitude']) ?>" maxlength="20" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="service_id"><?php esc_html_e('Station ID', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="service_id" aria-required="true" type="text" id="service_id" value="<?php echo htmlspecialchars($station['service_id']) ?>" maxlength="240" style="width:25em;" /></td>
            </tr>
        </table>
        <?php if ($error != 0) { ?>
            <p style="color:red;"><?php echo $errmsg;?></p>
        <?php } ?>
        <?php if ($station['guid'] == 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-piou" id="add-edit-piou" class="button button-primary" value="<?php esc_html_e( 'Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo $message;?>&hellip;</strong></span></p>
        <?php } ?>
        <?php if ($station['guid'] != 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-piou" id="add-edit-piou" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Updating this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        <?php } ?>
    </form>
</div>