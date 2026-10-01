<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
?>
<div class="alignleft actions bulkactions">
    <label for="system-selector-top" class="screen-reader-text"><?php esc_html_e('Filter systems to display', 'live-weather-station');?></label>
    <select name="system" id="system-selector-top">
        <?php foreach ($list->get_system_select() as $system) { ?>
            <option <?php echo $system['selected']; ?>value="<?php echo esc_attr($system['value']); ?>"><?php echo esc_html($system['text']); ?></option>
        <?php } ?>
    </select>
    <label for="service-selector-top" class="screen-reader-text"><?php esc_html_e('Filter services to display', 'live-weather-station');?></label>
    <select name="service" id="service-selector-top">
        <?php foreach ($list->get_service_select() as $service) { ?>
            <option <?php echo $service['selected']; ?>value="<?php echo esc_attr($service['value']); ?>"><?php echo esc_html($service['text']); ?></option>
        <?php } ?>
    </select>
    <label for="station-selector-top" class="screen-reader-text"><?php esc_html_e('Filter stations to display', 'live-weather-station');?></label>
    <select name="station" id="station-selector-top">
        <?php foreach ($list->get_station_select() as $station) { ?>
            <option <?php echo $station['selected']; ?>value="<?php echo esc_attr($station['value']); ?>"><?php echo esc_html($station['text']); ?></option>
        <?php } ?>
    </select>
    <input type="submit" class="button action" value="<?php esc_html_e('Apply', 'live-weather-station');?>"  />
</div>