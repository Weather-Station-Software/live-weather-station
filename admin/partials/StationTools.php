<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
    <div style="margin-bottom: 10px;line-height: 2;">
        <span style="white-space: nowrap;margin-right: 20px; width:100%;"><?php echo wp_kses_post($manage_link_icn); ?>&nbsp;<?php echo wp_kses_post($manage_link); ?></span>
        <span style="white-space: nowrap;margin-right: 20px; width:100%;"><?php echo wp_kses_post($import_link_icn); ?>&nbsp;<?php echo wp_kses_post($import_link); ?></span>
        <span style="white-space: nowrap;margin-right: 20px; width:100%;"><?php echo wp_kses_post($export_link_icn); ?>&nbsp;<?php echo wp_kses_post($export_link); ?></span>
    </div>
</div>