<?php
// Loads one plugin class, interface or trait through the plugin autoloader, outside WordPress.
// Composition errors (two traits defining the same method, a missing parent, ...) are fatal errors that `php -l` cannot see.
// Usage: php check-classes-load.php <repo-root> <fully\qualified\Name>
$root = rtrim($argv[1], '/');
define('ABSPATH', $root . '/');
define('LIVE_WEATHER_STATION_INCLUDES_DIR', $root . '/includes/');
define('LIVE_WEATHER_STATION_ADMIN_DIR', $root . '/admin/');
define('LIVE_WEATHER_STATION_PUBLIC_DIR', $root . '/public/');
define('LIVE_WEATHER_STATION_PLUGIN_DIR', $root . '/');
error_reporting(E_ALL);
// Minimal stand-ins for the few WordPress symbols touched while a class is being declared.
if (!class_exists('WP_Widget')) { class WP_Widget { public function __construct() {} } }
if (!function_exists('get_option')) { function get_option($name, $default = false) { return $default; } }
if (!function_exists('update_option')) { function update_option($name, $value = null) { return true; } }
require $root . '/autoload.php';
$name = $argv[2];
// The logger creates its database table while it is declared (it needs a real $wpdb): its traits are loaded through the other classes.
if ($name === 'WeatherStation\\System\\Logs\\Logger') {
    exit(0);
}
if (!class_exists($name) && !trait_exists($name) && !interface_exists($name)) {
    fwrite(STDERR, "Not loadable: $name\n");
    exit(2);
}
