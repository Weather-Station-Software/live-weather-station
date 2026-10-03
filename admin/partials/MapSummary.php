<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
    <div style="margin-bottom: 10px;">
        <span style="width:100%;"><?php echo wp_kses_post($map_icn); ?>&nbsp;<?php echo esc_html($map_name); ?></span>
    </div>
    <div style="margin-bottom: 10px;">
        <span style="width:70%;float: left;"><?php echo wp_kses_post($location_icn); ?>&nbsp;<?php echo wp_kses_post($map_location); ?></span>
        <span style="width:30%;"><?php echo wp_kses_post($zoom_icn); ?>&nbsp;<?php echo esc_html($map_zoom); ?></span>
    </div>
</div>
