<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

use WeatherStation\System\Environment\Manager as Env;

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo __('Webserver', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <table cellspacing="10" width="99%">
                <tbody>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-window-maximize"></i></td>
                    <td><?php echo Env::webserver_software_name().' '.__('with', 'live-weather-station').' '.Env::webserver_api(); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-bolt"></i></td>
                    <td><?php echo Env::webserver_protocol().' '.__('on port', 'live-weather-station').' '.Env::webserver_port(); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-hdd-o"></i></td>
                    <td><?php echo __('Document root', 'live-weather-station').' <code>'.Env::webserver_document_root().'</code>'; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>