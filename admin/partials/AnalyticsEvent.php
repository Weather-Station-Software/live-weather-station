<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div>
    <div style="display: inline-block;padding:20px;float: left; margin-right: 40px;">
        <h2><?php esc_html_e('Events: density', 'live-weather-station'); ?></h2>
        <?php echo do_shortcode('[live-weather-station-admin-analytics item="event" metric="density"]'); ?>
    </div>
    <div style="display: inline-block;padding:20px;">
        <h2><?php esc_html_e('Events: relative criticality', 'live-weather-station'); ?></h2>
        <?php echo do_shortcode('[live-weather-station-admin-analytics item="event" metric="criticality"]'); ?>
    </div>
</div>

<div style="padding:20px;">
    <h2><?php esc_html_e('Events: stations breakdown', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="event" metric="device_name"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php esc_html_e('Events: services breakdown', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="event" metric="service"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php esc_html_e('Events: systems breakdown', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="event" metric="system"]'); ?>
</div>
