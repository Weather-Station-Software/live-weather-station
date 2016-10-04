<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */

?>
<div class="alignleft actions bulkactions">
    <label for="system-selector-top" class="screen-reader-text"><?php esc_html_e('Filter systems to display', 'live-weather-station');?></label>
    <select name="system" id="system-selector-top">
        <?php foreach ($list->get_system_select() as $system) { ?>
            <option <?php echo $system['selected']; ?>value="<?php echo $system['value']; ?>"><?php echo $system['text']; ?></option>
        <?php } ?>
    </select>
    <label for="service-selector-top" class="screen-reader-text"><?php esc_html_e('Filter services to display', 'live-weather-station');?></label>
    <select name="service" id="service-selector-top">
        <?php foreach ($list->get_service_select() as $service) { ?>
            <option <?php echo $service['selected']; ?>value="<?php echo $service['value']; ?>"><?php echo $service['text']; ?></option>
        <?php } ?>
    </select>
    <label for="station-selector-top" class="screen-reader-text"><?php esc_html_e('Filter stations to display', 'live-weather-station');?></label>
    <select name="station" id="station-selector-top">
        <?php foreach ($list->get_station_select() as $station) { ?>
            <option <?php echo $station['selected']; ?>value="<?php echo $station['value']; ?>"><?php echo $station['text']; ?></option>
        <?php } ?>
    </select>
    <input type="submit" class="button action" value="<?php esc_html_e('Apply', 'live-weather-station');?>"  />
</div>