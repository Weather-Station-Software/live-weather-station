<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$map = ' ('.sprintf('<a href="https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=%3$s/%1$s/%2$s"' . ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank"' : '') . '>'.lcfirst(__('Verify on a map', 'live-weather-station')).'</a>',$station['loc_latitude'],$station['loc_longitude'], get_option('live_weather_station_map_zoom')).')';
$confirm = sprintf(__('Here are the coordinates we\'ve found %s. You can confirm it by clicking again on the button <em>%s</em>!', 'live-weather-station'),$map, ($station['station_id'] == 0 ? __( 'Add This Station', 'live-weather-station' ) : __( 'Save Changes', 'live-weather-station' )));
$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');

?>

<div class="wrap">
    <?php if ($station['guid'] == 0) { ?>
        <h1><?php esc_html_e( 'Add a "virtual" weather station', 'live-weather-station' );?></h1>
    <?php } ?>
    <?php if ($station['guid'] != 0) { ?>
        <h1><?php esc_html_e( 'Edit a "virtual" weather station', 'live-weather-station' );?></h1>
    <?php } ?>
    <?php if ($error == 3) { ?>
        <p style="color:red;"><?php esc_html_e( 'Some required fields are missing!', 'live-weather-station' );?></p>
    <?php } ?>
    <form method="post" name="add-edit-loc-form" id="add-edit-loc-form" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
        <input name="station_id" type="hidden" value="<?php echo $station['station_id']; ?>" />
        <input name="guid" type="hidden" value="<?php echo $station['guid']; ?>" />
        <input name="service" type="hidden" value="Location" />
        <input name="tab" type="hidden" value="add-edit" />
        <input name="action" type="hidden" value="do" />
        <?php if ($dashboard) { ?>
            <input name="dashboard" type="hidden" value="1" />
        <?php } ?>
        <?php wp_nonce_field('add-edit-loc'); ?>
        <table class="form-table">
            <tr class="form-field form-required">
                <th scope="row"><label for="station_name"><?php esc_html_e('Station name', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="station_name" aria-required="true" type="text" id="station_name" value="<?php echo htmlspecialchars($station['station_name']) ?>" maxlength="60" style="width:25em;" /></td>
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
        </table>
        <?php if ($error == 0 || $error == 3) { ?>
            <?php $message = __('Searching for coordinates, please wait', 'live-weather-station');?>
            <p><?php esc_html_e('If you know the exact coordinates of this location, enter them here. Otherwise, we will try to find them for you...', 'live-weather-station' );?></p>
        <?php } ?>
        <?php if ($error == 1) { ?>
            <?php $message = __('Adding this station, please wait', 'live-weather-station');?>
            <p style="color:red;"><?php esc_html_e('Despite our best efforts, we were unable to find the coordinates of this location. Please, enter them here - or try an other location:', 'live-weather-station' );?></p>
        <?php } ?>

        <?php if ($error == 2) { ?>
            <?php $message = __('Verifying coordinates, please wait', 'live-weather-station');?>
            <p style="color:red;"><?php esc_html_e('The coordinates you have entered are not valid. Please, retry or left blank:', 'live-weather-station' );?></p>
        <?php } ?>
        <?php if ($error == 4) { ?>
            <?php $message = __('Adding this station, please wait', 'live-weather-station');?>
            <p><?php echo $confirm;?></p>
        <?php } ?>
        <table class="form-table">
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_latitude"><?php esc_html_e( 'Latitude', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(eg 49.85)', 'live-weather-station' );?></label></th>
                <td><input name="loc_latitude" type="text" id="loc_latitude" value="<?php echo $station['loc_latitude'] ?>" maxlength="20" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_longitude"><?php esc_html_e( 'Longitude', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(eg -1.6)', 'live-weather-station' );?></label></th>
                <td><input name="loc_longitude" type="text" id="loc_longitude" value="<?php echo $station['loc_longitude'] ?>" maxlength="20" style="width:25em;" /></td>
            </tr>
        </table>
        <?php if ($station['guid'] == 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-loc" id="add-edit-loc" class="button button-primary" value="<?php esc_html_e( 'Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
            <?php if ($dashboard) { ?>
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
            <?php } else { ?>
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
            <?php } ?>
                <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo $message;?>&hellip;</strong></span></p>
        <?php } ?>
        <?php if ($station['guid'] != 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-loc" id="add-edit-loc" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
            <?php if ($dashboard) { ?>
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
            <?php } else { ?>
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
            <?php } ?>
                <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Updating this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        <?php } ?>
    </form>
</div>