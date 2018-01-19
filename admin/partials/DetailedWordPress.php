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
        <h3 class="hndle"><span>WordPress</span></h3>
        <div class="inside">
            <table cellspacing="10" width="99%">
                <tbody>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-wordpress"></i></td>
                    <td><?php echo EnvManager::wordpress_version_text() . ' / ' . EnvManager::php_version_text(); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-microchip"></i></td>
                    <td><?php echo WP_MAX_MEMORY_LIMIT . ' / ' . WP_MEMORY_LIMIT; ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-compress"></i></td>
                    <td><?php echo EnvManager::wordpress_cache_text(); ?></td>
                </tr>
                <tr>
                    <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-bug"></i></td>
                    <td><?php echo EnvManager::wordpress_debug_text(); ?></td>
                </tr>
                <?php if (EnvManager::is_multilang_installed()) { ?>
                    <tr>
                        <td width="10%"/><td width="20px"><i style="color:#999999" class="fa fa-lg fa-language"></i></td>
                        <td><?php echo EnvManager::get_installed_multilang_name(); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>