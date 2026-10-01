<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div style="padding:20px;">
    <h2><?php echo esc_html__('API calls: daily distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="quota" metric="call_long"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo esc_html__('API max rate: daily distribution', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="quota" metric="rate_long"]'); ?>
</div>

<div style="padding:20px;">
    <h2><?php echo esc_html__('Methods: daily services breakdown', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="quota" metric="service_long"]'); ?>
</div>