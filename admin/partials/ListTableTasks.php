<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\UI\ListTable\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$tasksListTable = new Tasks();
$tasksListTable->prepare_items();

?>

    <div class="wrap">
        <h2><?php echo esc_html__('Scheduled tasks', 'live-weather-station');?></h2>
        <?php settings_errors(); ?>
        <?php $tasksListTable->display(); ?>
    </div>



