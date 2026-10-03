<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\Mapping\Helper as Mapping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
    <div style="margin-bottom: 10px;">
        <span style="width:50%;float: left;"><?php echo wp_kses_post($location_icn); ?>&nbsp;<?php echo wp_kses_post($station['txt_coordinates']); ?></span>
        <span style="width:50%;"><?php echo wp_kses_post($altitude_icn); ?>&nbsp;<?php echo wp_kses_post($station['txt_altitude']); ?></span>
    </div>
    <?php echo Mapping::get_embed($station['loc_latitude'], $station['loc_longitude'], 300); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Mapping::get_embed() builds an OpenStreetMap iframe whose size and coordinates are cast to int/float; wp_kses_post() would remove the iframe. ?>
</div>
