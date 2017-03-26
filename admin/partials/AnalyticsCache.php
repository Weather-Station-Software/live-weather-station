<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

?>

<div style="padding:20px;">
    <h2><?php echo __('Rendering requests: hourly distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="cache" metric="count"]'); ?>
</div>
<div style="padding:20px;">
    <h2><?php echo __('Rendering average time: hourly distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="cache" metric="time"]'); ?>
</div>
<div style="padding:20px;">
    <h2><?php echo __('Efficiency: hourly distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="cache" metric="efficiency"]'); ?>
</div>
<div style="padding:20px;">
    <h2><?php echo __('Time saving: hourly distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="cache" metric="time_saving"]'); ?>
</div>
