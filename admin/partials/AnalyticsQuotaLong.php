<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

?>

<div style="padding:20px;">
    <h2><?php echo __('API calls: daily distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="quota" metric="call_long"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('API max rate: daily distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="quota" metric="rate_long"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo __('Methods: daily services breakdown', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="quota" metric="service_long"]'); ?>
</div>