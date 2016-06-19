<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      2.8.0
 */

require_once(LWS_INCLUDES_DIR.'class-log-list-table.php');

$logListTable = new Log_List_Table();
$logListTable->prepare_items();

// @todo 3.x / delete the following hidden fields : view, action
// @todo 3.x / set page hidden field at "lws-events"

?>
<div class="wrap">
    <h2><?php echo __('Events log', 'live-weather-station');?></h2>
    <?php $logListTable->views(); ?>
    <form id="logs-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <input type="hidden" name="view" value="list-logs" />
        <input type="hidden" name="action" value="list-logs" />
        <?php if ($logListTable->get_level() != '') : ?>
            <input type="hidden" name="level" value="<?php echo $logListTable->get_level(); ?>" />
        <?php endif; ?>
        <?php $logListTable->display(); ?>
    </form>
</div>