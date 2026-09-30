<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */

use WeatherStation\System\Help\InlineHelp;

$wflw_parts = \WeatherStation\SDK\WeatherFlow\Plugin\StationCollector::split_wflw_service_id(isset($station['service_id']) ? $station['service_id'] : '');
$wflw_has_token = ($wflw_parts[1] !== '');
$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');
$message = __('Adding this station, please wait', 'live-weather-station');
if ($error_message == '') {
    $errmsg = __('The station you specified is not accessible. Please, verify its ID and retry.', 'live-weather-station' );
}
else {
    $errmsg = __('Unable to access this station:', 'live-weather-station' ) . ' ' . $error_message;
}


?>

<div class="wrap">
    <?php if ($station['guid'] == 0) { ?>
        <h1><?php _e('Add a WeatherFlow station', 'live-weather-station');?></h1>
    <?php } ?>
    <?php if ($station['guid'] != 0) { ?>
        <h1><?php _e('Edit a WeatherFlow station', 'live-weather-station');?></h1>
    <?php } ?>
    <form method="post" name="add-edit-wflw-form" id="add-edit-wflw-form" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
        <input name="station_id" type="hidden" value="<?php echo esc_attr($station['station_id']); ?>" />
        <input name="guid" type="hidden" value="<?php echo esc_attr($station['guid']); ?>" />
        <input name="service" type="hidden" value="WeatherFlow" />
        <input name="tab" type="hidden" value="add-edit" />
        <input name="action" type="hidden" value="do" />
        <?php if ($dashboard) { ?>
            <input name="dashboard" type="hidden" value="1" />
        <?php } ?>
        <?php wp_nonce_field('add-edit-wflw'); ?>
        <table class="form-table">
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_city"><?php esc_html_e('City', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="loc_city" type="text" id="loc_city" value="<?php echo esc_attr($station['loc_city']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="loc_country_code"><?php esc_html_e('Country', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td>
                    <select name="loc_country_code" id="loc_country_code" style="width:25em;">
                        <?php foreach ($countries as $key => $val) { ?>
                            <option value="<?php echo esc_attr($key) ?>"<?php if ($station['loc_country_code']==$key) {?> selected="selected"<?php } ?>><?php echo esc_html($val); ?></option>;
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="service_id"><?php esc_html_e('Station ID', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required name="service_id" aria-required="true" type="text" id="service_id" value="<?php echo esc_attr($wflw_parts[0]) ?>" maxlength="20" style="width:25em;" /></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="service_token"><?php esc_html_e('Personal access token', 'live-weather-station' );?> <span class="description"><?php echo ($wflw_has_token ? esc_html__('(leave empty to keep the current token)', 'live-weather-station') : esc_html__('(required)', 'live-weather-station')); ?></span></label></th>
                <td><input <?php echo ($wflw_has_token ? '' : 'required aria-required="true"'); ?> name="service_token" type="password" id="service_token" value="" maxlength="100" autocomplete="off" style="width:25em;" /></td>
            </tr>
        </table>
        <p><?php esc_html_e('Since 27 March 2025, WeatherFlow only lets you read the stations of your own account: the station must be yours, and the token is a personal access token created at tempestwx.com (Settings, Data Authorizations, Create Token).', 'live-weather-station');?></p>
        <?php if ($station['guid'] != 0 && !$wflw_has_token) { ?>
            <p class="notice notice-error" style="padding:8px 12px;"><strong><?php esc_html_e('This station can no longer be updated: it was saved without a personal access token.', 'live-weather-station');?></strong> <?php esc_html_e('Enter the token of the station owner and save to resume the collection.', 'live-weather-station');?></p>
        <?php } ?>
        <?php if ($error != 0) { ?>
            <p style="color:red;"><?php echo esc_html($errmsg);?></p>
        <?php } ?>
        <?php if ($station['guid'] == 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-wflw" id="add-edit-wflw" class="button button-primary" value="<?php esc_html_e( 'Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo $message;?>&hellip;</strong></span></p>
        <?php } ?>
        <?php if ($station['guid'] != 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-wflw" id="add-edit-wflw" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e( 'Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Updating this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        <?php } ?>
    </form>
</div>