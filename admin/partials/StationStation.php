<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$tech = (bool)get_option('live_weather_station_show_technical');
$histo = (bool)get_option('live_weather_station_build_history');
?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
    <div style="margin-bottom: 10px;">
        <span style="width:100%;"><?php echo wp_kses_post($station_name_icn); ?>&nbsp;<?php echo esc_html($station['station_name']); ?></span>
        <?php if ($station['station_model'] != 'N/A') { ?>
            <span style="color:silver"> (<?php echo esc_html($station['station_model']); ?>)</span>
        <?php } ?>
    </div>
    <div style="margin-bottom: 10px;">
        <span style="width:50%;float: left;"><?php echo wp_kses_post($location_icn); ?>&nbsp;<?php echo esc_html($station['txt_location']); ?></span>
        <span style="width:50%;"><?php echo wp_kses_post($timezone_icn); ?>&nbsp;<?php echo esc_html($station['txt_timezone']); ?></span>
    </div>
    <?php if ($histo && $station['oldest_data_txt']) { ?>
        <div style="margin-bottom: 10px;">
            <span style="width:100%;cursor: default;"><?php echo wp_kses_post($histo_icn); ?>&nbsp;<?php echo esc_html($station['oldest_data_txt']); ?></span><span style="color:silver"> (<?php echo esc_html($station['oldest_data_diff_txt']); ?>)</span>
        </div>
    <?php } ?>
</div>
