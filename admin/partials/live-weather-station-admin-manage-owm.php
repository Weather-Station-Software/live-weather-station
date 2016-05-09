<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      2.0.0
 */

require_once(LWS_INCLUDES_DIR.'class-owm-stations-list-table.php');

$owmListTable = new Owm_Stations_List_Table();
$owmListTable->prepare_items();
$addlink = sprintf('<a href="?page=%s&view=add-edit-owm&action=add-edit-owm" class="page-title-action">'.__('Add', 'live-weather-station').'</a>',$_REQUEST['page']);

?>
<div class="wrap">
    <h2><?php echo __('OpenWeatherMap stations', 'live-weather-station');?> <?php echo $addlink; ?></h2>
    <form id="stations-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <input type="hidden" name="view" value="manage-owm" />
        <input type="hidden" name="action" value="manage-owm" />
        <?php $owmListTable->display() ?>
    </form>
</div>