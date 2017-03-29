<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */

use WeatherStation\UI\ListTable\Tasks;

$tasksListTable = new Tasks();
$tasksListTable->prepare_items();

?>

<?php $tasksListTable->display(); ?>
