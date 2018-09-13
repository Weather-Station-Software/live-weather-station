<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */

use WeatherStation\SDK\Ambient\Plugin\StationInitiator as Ambient_Initiator;

$n = new Ambient_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
$stationlist = $n->detect_stations();
$can_add = false;
foreach ($stationlist as $s) {
    if (!$s['installed']) {
        $can_add = true;
    }
}

$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');
$message = __('Adding this station, please wait', 'live-weather-station');

?>

<div class="wrap">
    <?php if ($station['guid'] === 0) { ?>
        <h1><?php _e('Add a weather station published on Ambient Weather Network', 'live-weather-station');?></h1>
    <?php } ?>
    <?php if ($station['guid'] !== 0) { ?>
        <h1><?php _e('Edit a weather station published on Ambient Weather Network', 'live-weather-station');?></h1>
    <?php } ?>

    <?php if ($can_add || $station['guid'] !== 0) { ?>
        <form method="post" name="add-edit-ambient-form" id="add-edit-ambient-form" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
            <input name="guid" type="hidden" value="<?php echo $station['guid']; ?>" />
            <?php if ($station['guid'] !== 0) { ?>
                <input name="id" type="hidden" value="<?php echo $station['station_id']; ?>" />
            <?php } ?>
            <input name="service" type="hidden" value="ambient" />
            <input name="tab" type="hidden" value="add-edit" />
            <input name="action" type="hidden" value="do" />
            <?php if ($dashboard) { ?>
                <input name="dashboard" type="hidden" value="1" />
            <?php } ?>
            <?php wp_nonce_field('add-edit-ambient'); ?>
            <table class="form-table">
                <?php if ($station['guid'] === 0) { ?>
                    <tr class="form-field">
                        <th scope="row"><label for="id"><?php esc_html_e( 'Station', 'live-weather-station' );?></label></th>
                        <td>
                            <select name="id" id="id" style="width:25em;">
                                <?php foreach($stationlist as $s) { ?>
                                    <option value="<?php echo $s['device_id']; ?>"<?php echo ($s['installed'] ? ' disabled' : ''); ?>><?php echo $s['station_name']; ?></option>;
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php } else { ?>
                    <tr class="form-field">
                        <th scope="row"><label for="id"><?php esc_html_e( 'Station', 'live-weather-station' );?></label></th>
                        <td>
                            <select disabled name="id" id="id" style="width:25em;">
                                <?php foreach($stationlist as $s) { ?>
                                    <option value="<?php echo $s['device_id']; ?>"<?php echo ($s['device_id'] === $station['station_id'] ? ' selected' : ''); ?>><?php echo $s['station_name']; ?></option>;
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
                <tr class="form-field form-required">
                    <th scope="row"><label for="station_name"><?php esc_html_e('Name', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                    <td><input required name="station_name" type="text" id="station_name" value="<?php echo htmlspecialchars($station['station_name']) ?>" maxlength="60" style="width:25em;" /></td>
                </tr>
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
                    <th scope="row"><label for="loc_latitude"><?php esc_html_e('Latitude', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></label></th>
                    <td><input required name="loc_latitude" type="text" id="loc_latitude" value="<?php echo $station['loc_latitude'] ?>" maxlength="20" style="width:25em;" /></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="loc_longitude"><?php esc_html_e('Longitude', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></label></th>
                    <td><input required name="loc_longitude" type="text" id="loc_longitude" value="<?php echo $station['loc_longitude'] ?>" maxlength="20" style="width:25em;" /></td>
                </tr>
            </table>
            <?php if ($station['guid'] === 0) { ?>
                <p class="submit"><input type="submit" name="add-edit-ambient" id="add-edit-ambient" class="button button-primary" value="<?php esc_html_e( 'Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                    <?php if ($dashboard) { ?>
                        <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                    <?php } else { ?>
                        <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                    <?php } ?>
                    <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo $message;?>&hellip;</strong></span></p>
            <?php } ?>
            <?php if ($station['guid'] !== 0) { ?>
                <p class="submit"><input type="submit" name="add-edit-ambient" id="add-edit-ambient" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                    <?php if ($dashboard) { ?>
                        <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                    <?php } else { ?>
                        <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                    <?php } ?>
                    <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Updating this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
            <?php } ?>
        </form>
    <?php } else { ?>
        <p><?php esc_html_e( 'All Ambient stations have been already added!', 'live-weather-station' );?></p>
        <?php if ($dashboard) { ?>
            <p class="submit"><a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button button-primary" ><?php esc_html_e( 'Back', 'live-weather-station' );?></a></p>
        <?php } else { ?>
            <p class="submit"><a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button button-primary" ><?php esc_html_e( 'Back', 'live-weather-station' );?></a></p>
        <?php } ?>
    <?php } ?>
</div>