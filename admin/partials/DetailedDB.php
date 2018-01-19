<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.1
 */

use WeatherStation\System\Environment\Manager as EnvManager;

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php echo __('Database', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <table cellspacing="10" width="99%">
                <tbody>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-database"></i></td>
                    <td><?php echo EnvManager::mysql_version_text(); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-bolt"></i></td>
                    <td><?php echo EnvManager::mysql_name_text() . ' (' . EnvManager::mysql_charset_text() . ')'; ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-user"></i></td>
                    <td><?php echo EnvManager::mysql_user_text(); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-arrows"></i></td>
                    <td><?php echo EnvManager::mysql_total_size_text() . ' (' . __('total', 'live-weather-station') . ')'; ?> / <?php echo EnvManager::mysql_lws_size_text() . ' (' . LWS_PLUGIN_NAME . ')'; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>