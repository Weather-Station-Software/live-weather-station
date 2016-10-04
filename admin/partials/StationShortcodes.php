<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

$s_textual = '<a href="#" id="textual-datas-link-' . $station_guid . '">' . ucfirst(__('textual datas', 'live-weather-station')) . '</a>';
$s_lcd = '<a href="#" id="lcd-datas-link-' . $station_guid . '">' . ucfirst(__('LCD display', 'live-weather-station')) . '</a>';
$s_justgage = '<a href="#" id="justgage-datas-link-' . $station_guid . '">' . ucfirst(__('clean gauge', 'live-weather-station')) . '</a>';
$s_steelmeter = '<a href="#" id="steelmeter-datas-link-' . $station_guid . '">' . ucfirst(__('steel meter', 'live-weather-station')) . '</a>';

?>

<div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none;">
    <div style="margin-bottom: 10px;">
        <span style="width:50%;float: left;"><i style="color:#999" class="fa fa-lg fa-fw fa-file-text-o" aria-hidden="true"></i>&nbsp;<?php echo $s_textual; ?></span>
        <span style="width:50%;"><i style="color:#999" class="fa fa-lg fa-fw fa-th" aria-hidden="true"></i>&nbsp;<?php echo $s_lcd; ?></span>
    </div>
    <div style="margin-bottom: 10px;">
        <span style="width:50%;float: left;"><i style="color:#999" class="fa fa-lg fa-fw fa-circle-thin" aria-hidden="true"></i>&nbsp;<?php echo $s_justgage; ?></span>
        <span style="width:50%;"><i style="color:#999" class="fa fa-lg fa-fw fa-tachometer" aria-hidden="true"></i>&nbsp;<?php echo $s_steelmeter; ?></span>
    </div>
</div>
