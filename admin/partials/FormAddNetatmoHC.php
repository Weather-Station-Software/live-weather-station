<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

use WeatherStation\SDK\Netatmo\Plugin\HCInitiator as Netatmo_HCInitiator;

$n = new Netatmo_HCInitiator(LWS_PLUGIN_ID, LWS_VERSION);
$stations = $n->detect_stations();
$can_add = false;
foreach ($stations as $station) {
    if (!$station['installed']) {
        $can_add = true;
    }
}
$url = ($dashboard ? 'lws-dashboard' : 'lws-stations');

?>

<div class="wrap">
    <h2><?php esc_html_e('Add a Netatmo "Healthy Home Coach" device', 'live-weather-station');?></h2>
    <?php if ($can_add) { ?>
        <form method="post" name="add-netatmohc" id="add-netatmohc" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
            <input name="service" type="hidden" value="Netatmo" />
            <input name="tab" type="hidden" value="add" />
            <input name="action" type="hidden" value="do" />
            <?php if ($dashboard) { ?>
                <input name="dashboard" type="hidden" value="1" />
            <?php } ?>
            <?php wp_nonce_field('add-netatmohc'); ?>
            <table class="form-table">
                <tr class="form-field">
                    <th scope="row"><label for="station_name"><?php esc_html_e( 'Device', 'live-weather-station' );?></label></th>
                    <td align="left">
                    <span class="select-option">
                        <select class="option-select" name="id">
                            <?php foreach($stations as $station) { ?>
                                <option value="<?php echo $station['device_id']; ?>"<?php echo ($station['installed'] ? ' disabled' : ''); ?>><?php echo $station['station_name']; ?></option>;
                            <?php } ?>
                        </select>
                    </span>
                    </td>
                </tr>

            </table>
            <p class="submit"><input type="submit" name="add-netatmohc" id="add-netatmohc" class="button button-primary" value="<?php esc_html_e( 'Add This Device', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-add-netatmohc" id="donot-add-netatmohc" class="button" value="<?php esc_html_e( 'Cancel', 'live-weather-station' );?>"  />
                <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Adding this device, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        </form>
    <?php } else { ?>
        <p><?php esc_html_e( 'All Netatmo "Healthy Home Coach" devices have been already added!', 'live-weather-station' );?></p>
        <?php if ($dashboard) { ?>
            <p class="submit"><a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button button-primary" ><?php esc_html_e( 'Back', 'live-weather-station' );?></a></p>
        <?php } else { ?>
            <p class="submit"><a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button button-primary" ><?php esc_html_e( 'Back', 'live-weather-station' );?></a></p>
        <?php } ?>
    <?php } ?>
</div>