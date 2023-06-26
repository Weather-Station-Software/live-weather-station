<?php

/**
 * Standalone page bootstrap.
 *
 * @package Bootstrap
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

require_once(dirname(__FILE__) . '/includes/classes/PageStandaloneGenerator.php');
use WeatherStation\Engine\Page\Standalone\Generator;

$generator = new Generator();
$generator->run();
