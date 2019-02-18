<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\UI\ListTable\File;

$fileListTable = new File();
$fileListTable->prepare_items();

?>
<div class="wrap">
    <h2><?php echo __('Export/Import files', 'live-weather-station');?></h2>
    <?php settings_errors(); ?>
    <?php $fileListTable->views(); ?>
    <form id="files-filter" method="get">
        <input type="hidden" name="page" value="lws-files" />
        <?php $fileListTable->display(); ?>
    </form>
</div>