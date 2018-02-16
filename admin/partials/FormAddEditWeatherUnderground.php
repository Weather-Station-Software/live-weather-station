<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp;

$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');
$edit = ($station['guid'] != 0);
if (!isset($station['station_model'])) {
    $station['station_model'] = __('N/A', 'live-weather-station');
}


?>

<div class="wrap">
    <?php if (!$edit) { ?>
        <h1><?php echo sprintf(__('Add a weather station published on %s', 'live-weather-station'), 'Weather Underground');?></h1>
    <?php } else { ?>
        <h1><?php echo sprintf(__('Edit a weather station published on %s', 'live-weather-station'), 'Weather Underground');?></h1>
    <?php } ?>
    <form method="post" name="add-edit-wug-form" id="add-edit-wug-form" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
        <input name="guid" type="hidden" value="<?php echo $station['guid']; ?>" />
        <?php if ($edit) { ?>
            <input name="service_id" type="hidden" value="<?php echo $station['service_id']; ?>" />
        <?php } ?>
        <input name="service" type="hidden" value="WeatherUnderground" />
        <input name="tab" type="hidden" value="add-edit" />
        <input name="action" type="hidden" value="do" />
        <?php if ($dashboard) { ?>
            <input name="dashboard" type="hidden" value="1" />
        <?php } ?>
        <?php wp_nonce_field('add-edit-wug'); ?>
        <table class="form-table">
            <tr class="form-field">
                <th scope="row"><label for="station_name"><?php esc_html_e('Station name', 'live-weather-station' );?></label></th>
                <td><input name="station_name" aria-required="false" type="text" id="station_name" value="<?php echo htmlspecialchars($station['station_name']) ?>" maxlength="60" style="width:25em;" /></td>
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
                <th scope="row"><label for="service_id"><?php esc_html_e('Station ID', 'live-weather-station' );?> <span class="description"><?php esc_html_e( '(required)', 'live-weather-station' );?></span></label></th>
                <td><input required <?php echo ($edit ? 'disabled="disabled" ' : ''); ?>name="service_id" type="text" id="service_id" value="<?php echo htmlspecialchars($station['service_id']) ?>" maxlength="20" style="width:25em;" /><?php echo InlineHelp::article(2)?></td>
            </tr>
        </table>
        <?php if ($station['guid'] == 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-wug" id="add-edit-wug" class="button button-primary" value="<?php esc_html_e('Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Adding this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        <?php } ?>
        <?php if ($station['guid'] != 0) { ?>
            <p class="submit"><input type="submit" name="add-edit-wug" id="add-edit-wug" class="button button-primary" value="<?php esc_html_e('Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp;
                <?php if ($dashboard) { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station' );?></a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station' );?></a>
                <?php } ?>
                <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Updating this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        <?php } ?>
    </form>
</div>