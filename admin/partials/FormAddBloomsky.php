<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */

use WeatherStation\SDK\BloomSky\Plugin\StationInitiator as Bloomsky_Initiator;

$n = new Bloomsky_Initiator(LWS_PLUGIN_ID, LWS_VERSION);
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
    <h2><?php esc_html_e('Add a Bloomsky station', 'live-weather-station');?></h2>
    <?php if ($can_add) { ?>
        <form method="post" name="add-bloomsky" id="add-bloomsky" action="<?php echo esc_url(lws_get_admin_page_url($url)); ?>">
            <input name="service" type="hidden" value="Bloomsky" />
            <input name="tab" type="hidden" value="add" />
            <input name="action" type="hidden" value="do" />
            <?php if ($dashboard) { ?>
                <input name="dashboard" type="hidden" value="1" />
            <?php } ?>
            <?php wp_nonce_field('add-bloomsky'); ?>
            <table class="form-table">
                <tr class="form-field">
                    <th scope="row"><label for="id"><?php esc_html_e( 'Station', 'live-weather-station' );?></label></th>
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
            <p class="submit"><input type="submit" name="add-bloomsky" id="add-bloomsky" class="button button-primary" value="<?php esc_html_e( 'Add This Station', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-add-bloomsky" id="donot-add-bloomsky" class="button" value="<?php esc_html_e( 'Cancel', 'live-weather-station' );?>"  />
                <span id="span-sync" style="display: none;"><i class="<?php echo LWS_FAS;?> fa-cog fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Adding this station, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
        </form>
    <?php } else { ?>
        <p><?php esc_html_e( 'All BloomSky stations have been already added!', 'live-weather-station' );?></p>
        <?php if ($dashboard) { ?>
            <p class="submit"><a href="<?php echo esc_url(lws_get_admin_page_url('lws-dashboard')); ?>" class="button button-primary" ><?php esc_html_e( 'Back', 'live-weather-station' );?></a></p>
        <?php } else { ?>
            <p class="submit"><a href="<?php echo esc_url(lws_get_admin_page_url('lws-stations')); ?>" class="button button-primary" ><?php esc_html_e( 'Back', 'live-weather-station' );?></a></p>
        <?php } ?>
    <?php } ?>
</div>