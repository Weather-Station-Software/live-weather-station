<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */

use WeatherStation\System\Help\InlineHelp;

$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');
$message = __('Adding this station, please wait', 'live-weather-station');
if ($error_message == '') {
    $errmsg = __('The station you specified is not accessible. Please, verify its ID and retry.', 'live-weather-station' );
}
else {
    $errmsg = __('Unable to access this station:', 'live-weather-station' ) . ' ' . $error_message;
}


$id = array();
$exp = array();
if (isset($station) && is_array($station) && array_key_exists('service_id', $station) && $station['service_id'] !== '') {
    $exp = explode(LWS_SERVICE_SEPARATOR, $station['service_id']);
}
if (count($exp) !== 3) {
    $id['service_did'] = '';
    $id['service_apitoken'] = '';
    $id['service_ownerpass'] = '';
}
else {
    $id['service_did'] = $exp[0];
    $id['service_apitoken'] = $exp[1];
    $id['service_ownerpass'] = $exp[2];
}


?>

<div class="wrap">
    <?php if ($station['guid'] == 0) { ?>
        <h1><?php esc_html_e_lws__('Add a station connected to WeatherLink', 'live-weather-station');?></h1>
    <?php } ?>
    <?php if ($station['guid'] != 0) { ?>
        <h1><?php esc_html_e_lws__('Edit a station connected to WeatherLink', 'live-weather-station');?></h1>
    <?php } ?>
    <form method="post" name="add-edit-wlink-form" id="add-edit-wlink-form" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
        <input name="station_id" type="hidden" value="<?php echo $station['station_id']; ?>" />
        <input name="guid" type="hidden" value="<?php echo $station['guid']; ?>" />
        <input name="service" type="hidden" value="WeatherLink" />
        <input name="tab" type="hidden" value="add-edit" />
        <input name="action" type="hidden" value="do" />
        <?php if ($dashboard) { ?>
            <input name="dashboard" type="hidden" value="1" />
        <?php } ?>
        <?php wp_nonce_field('add-edit-wlink'); ?>
        <table class="form-table">
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
                <th scope="row"><label for="service_did"><?php esc_html_e_lws__('Device ID', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="service_did" aria-required="true" type="text" id="service_did" value="<?php echo htmlspecialchars($id['service_did']) ?>" maxlength="20" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="service_apitoken"><?php esc_html_e_lws__('API Token', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="service_apitoken" aria-required="true" type="text" id="service_apitoken" value="<?php echo htmlspecialchars($id['service_apitoken']) ?>" maxlength="20" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="service_ownerpass"><?php esc_html_e_lws__('Password', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="service_ownerpass" aria-required="true" type="text" id="service_ownerpass" value="<?php echo htmlspecialchars($id['service_ownerpass']) ?>" maxlength="20" style="width:25em;" /></td>
            </tr>

        </table>
        <?php if ($error != 0) { ?>
            <p style="color:red;"><?php echo $errmsg;?></p>
        <?php } ?>
        <?php if ($station['guid'] == 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-wlink" id="add-edit-wlink" class="button button-primary" value="<?php esc_html_e( 'Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo $message;?>&hellip;</strong></span></p>
        <?php } ?>
        <?php if ($station['guid'] != 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-wlink" id="add-edit-wlink" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Updating this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        <?php } ?>
    </form>
</div>