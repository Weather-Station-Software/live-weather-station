<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */

use WeatherStation\UI\ListTable\Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$logListTable = new Log();
$logListTable->prepare_items();

?>
<div class="wrap">
    <h2><?php echo __('Events log', 'live-weather-station');?></h2>
    <?php $logListTable->views(); ?>
    <form id="logs-filter" method="get">
        <input type="hidden" name="page" value="lws-events" />
        <?php if ($logListTable->get_level() != '') : ?>
            <input type="hidden" name="level" value="<?php echo esc_attr($logListTable->get_level()); ?>" />
        <?php endif; ?>
        <?php $logListTable->display(); ?>
    </form>
</div>