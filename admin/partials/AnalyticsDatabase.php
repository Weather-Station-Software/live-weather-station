<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.5.0
 */

?>

<div style="padding:20px;">
    <h2><?php echo __('Table size', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="database" metric="table_size"]'); ?>
</div>
<div style="padding:20px;">
    <h2><?php echo __('Row count', 'live-weather-station'); ?></h2>
    <?php echo do_shortcode('[live-weather-station-admin-analytics item="database" metric="row_count"]'); ?>
</div>