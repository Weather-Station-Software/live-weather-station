<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      2.5.0
 */

require_once(LWS_INCLUDES_DIR.'class-netatmo-stations-list-table.php');

$netatmoListTable = new Netatmo_Stations_List_Table();
$netatmoListTable->prepare_items();

?>
<div class="wrap">
    <h2><?php echo __('Netatmo stations', 'live-weather-station');?></h2>
    <form id="stations-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <input type="hidden" name="view" value="manage-netatmo" />
        <input type="hidden" name="action" value="manage-netatmo" />
        <?php $netatmoListTable->display() ?>
    </form>
</div>