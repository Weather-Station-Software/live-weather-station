<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */

use WeatherStation\System\Environment\Manager as Env;
use WeatherStation\System\Logs\Logger;

$OS = Env::server_os();

if (!$OS || !Env::server_cpu() || !Env::server_core()) {
    Logger::warning('Core',null,null,null,null,null,null,'Your server configuration does not allow to query system informations.');
}

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo __('Server', 'live-weather-station');?></span></h3>
        <div class="inside">
            <table cellspacing="10" width="99%">
                <tbody>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-server"></i></td>
                    <td/><?php echo gethostname().' <code>'.Env::server_ip().'</code>'; ?></td>
                </tr>
                <?php if ($OS) { ?>
                    <tr>
                        <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-cog"></i></td>
                        <td><?php echo $OS; ?></td>
                    </tr>
                <?php } ?>
                <?php if (Env::server_cpu() && Env::server_core()) { ?>
                    <tr>
                        <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-microchip"></i></td>
                        <td><?php echo Env::server_cpu() . ' / ' . Env::server_core() . ' ' . __('cores', 'live-weather-station'); ?></td>
                    </tr>
                <?php } ?>
                <?php if (Env::server_full_information()) { ?>
                    <tr>
                        <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-map-marker"></i></td>
                        <td><?php echo Env::hoster_name() . ', ' . Env::hoster_location(); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>