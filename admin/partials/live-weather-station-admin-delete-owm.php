<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      2.0.0
 */


?>
<div class="wrap">
    <h2><?php echo __('Deleteting OpenWeatherMap stations', 'live-weather-station');?></h2>
    <form id="stations-filter" method="get">
        <?php wp_nonce_field('confirm-delete-'); ?>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <input type="hidden" name="view" value="manage-owm" />
        <input type="hidden" name="action" value="manage-owm" />
        <input type="hidden" name="subaction" value="confirm-delete" />
        <?php if (count($stations) == 1 ) { ?>
            <p><?php esc_html_e( 'You have chosen to delete this station:', 'live-weather-station' );?></p>
        <?php } else { ?>
            <p><?php esc_html_e( 'You have chosen to remove these stations:', 'live-weather-station' );?></p>
        <?php } ?>
         <ul>
           <?php foreach ($stations as $station) { ?>
               <li><input type="hidden" name="delstation[]" value="<?php echo $station['station_id']; ?>" /><?php echo sprintf('%1$s <span style="color:silver">(%2$s, %3$s)</span>', $station['station_name'], $station['loc_city'], $station['country']); ?></li>
           <?php } ?>
        </ul>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Confirm Delete', 'live-weather-station' );?>"  /></p>
    </form>
</div>