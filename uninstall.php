<?php

/**
 * @package Bootstrap
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// When an uninstall.php file exists, WordPress uses it and ignores the hook registered with register_uninstall_hook():
// the cleanup (options, credentials, tables unless 'keep_tables' is set) must therefore be done from here.
// The main plugin file is not loaded in this context, so load what the uninstaller needs (constants, autoloader).
require_once (__DIR__ . '/init.php');

\WeatherStation\System\Plugin\Uninstaller::uninstall();
