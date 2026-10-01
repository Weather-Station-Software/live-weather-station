<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
    <h2><?php echo esc_html(ucfirst(__('changelog', 'live-weather-station'))); ?></h2>
    <div class="markdown">
        <style type="text/css">
            .markdown ul {
                list-style-type: disc !important;
                padding-left: 40px !important;
            }
        </style>
        <?php echo wp_kses_post(do_shortcode('[live-weather-station-changelog]')); ?>
    </div>
</div>