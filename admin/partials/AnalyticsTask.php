<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

?>

<div style="padding:20px;">
    <h2><?php echo __('Tasks durations: hourly average by pool', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="task" metric="time_by_pool"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('Executed tasks: hourly distribution by pool', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="task" metric="count_by_pool"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('Tasks durations: hourly average for "collection" pool', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="task" metric="time_for_pull"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('Tasks durations: hourly average for "sharing" pool', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="task" metric="time_for_push"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('Tasks durations: hourly average for "system" pool', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="task" metric="time_for_system"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('Tasks durations: hourly average for "history" pool', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="task" metric="time_for_history"]'); ?>
</div>