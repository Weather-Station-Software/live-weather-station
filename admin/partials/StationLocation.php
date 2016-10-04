<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\Mapping\Helper as Mapping;

?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
    <div style="margin-bottom: 10px;">
        <span style="width:50%;float: left;"><?php echo $location_icn; ?>&nbsp;<?php echo $station['txt_coordinates']; ?></span>
        <span style="width:50%;"><?php echo $altitude_icn; ?>&nbsp;<?php echo $station['txt_altitude']; ?></span>
    </div>
    <?php echo Mapping::get_embed($station['loc_latitude'], $station['loc_longitude'], 300); ?>
</div>
