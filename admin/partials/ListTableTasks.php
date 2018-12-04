<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\UI\ListTable\Tasks;

$tasksListTable = new Tasks();
$tasksListTable->prepare_items();

?>

    <div class="wrap">
        <h2><?php echo __('Scheduled tasks', 'live-weather-station');?></h2>
        <?php settings_errors(); ?>
        <?php $tasksListTable->display(); ?>
    </div>



