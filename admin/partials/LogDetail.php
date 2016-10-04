<?php

use WeatherStation\System\Logs\Logger;

/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */

$color = Logger::get_color($log['level']);
if ($color != '') {
    $color = 'style="color:' . $color . '"';
}
$title = '<i ' . $color . ' class="fa fa-fw ' . Logger::get_icon($log['level']) . '"></i>&nbsp;' . Logger::get_name($log['level']) . ' (#' . $log['id'] . ')';

?>

<div class="wrap">
    <h2><?php echo $title;?></h2>
    <br />
    <p>
        <strong><?php esc_html_e( 'Timestamp', 'live-weather-station' );?>&nbsp;:&nbsp;</strong><?php echo $log['displayed_timestamp'];?>
        <br />
        <strong><?php esc_html_e( 'System', 'live-weather-station' );?>&nbsp;:&nbsp;</strong><?php echo $log['system'];?> <?php echo $log['version'];?>
        <br />
        <strong><?php esc_html_e( 'Service', 'live-weather-station' );?>&nbsp;:&nbsp;</strong><?php echo $log['service'];?>
    </p>
    <p>
        <strong><?php esc_html_e( 'Station', 'live-weather-station' );?>&nbsp;:&nbsp;</strong><?php echo $log['device_name'];?> (<?php echo $log['device_id'];?>)
        <br />
        <strong><?php esc_html_e( 'Module', 'live-weather-station' );?>&nbsp;:&nbsp;</strong><?php echo $log['module_name'];?> (<?php echo $log['module_id'];?>)
    </p>
    <p>
        <strong><?php esc_html_e( 'Error code', 'live-weather-station' );?>&nbsp;:&nbsp;</strong><?php echo $log['code'];?>
        <br />
        <strong><?php esc_html_e( 'Full message', 'live-weather-station' );?>&nbsp;:&nbsp;</strong>
        <br />
        <textarea readonly cols="80" rows="8" style="font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" "><?php echo $log['message'];?></textarea>
    </p>
</div>