<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
    <div style="margin-bottom: 10px;">
        <span style="width:100%;"><?php echo $station_name_icn; ?>&nbsp;<?php echo $station['station_name']; ?></span>
        <?php if ($station['station_model'] != 'N/A') { ?>
            <span style="color:silver"> (<?php echo $station['station_model']; ?>)</span>
        <?php } ?>
    </div>
    <div style="margin-bottom: 10px;">
        <span style="width:50%;float: left;"><?php echo $location_icn; ?>&nbsp;<?php echo $station['txt_location']; ?></span>
        <span style="width:50%;"><?php echo $timezone_icn; ?>&nbsp;<?php echo $station['txt_timezone']; ?></span>
    </div>
</div>
