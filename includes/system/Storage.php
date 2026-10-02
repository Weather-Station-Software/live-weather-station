<?php

namespace WeatherStation\System\Storage;

use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;

/**
 * This class add storage management capacity to the plugin.
 *
 * @package Includes\System
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class Manager {

    protected $Live_Weather_Station;
    protected $version;

    private static $dir = '';
    private static $url = '';
    private static $service = 'Storage Manager';
    private static $file_name_separator = '_';
    private static $allowed_extension = array('ndjson' => 'text/plain', 'json' => 'text/plain');
    // Every extension created by the exporters (json, ndjson, csv, dsv, tsv, txt, wsconf.json) can be listed, viewed,
    // downloaded and purged. Only json/ndjson can be uploaded ($allowed_extension).
    private static $managed_extension = array('ndjson' => 'text/plain', 'json' => 'application/json', 'csv' => 'text/csv', 'dsv' => 'text/plain', 'tsv' => 'text/plain', 'txt' => 'text/plain');
    private static $max_upload_size = 52428800; // 50 MB

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 3.7.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        self::init();
    }

    /**
     * Initialize the static properties of the class.
     *
     * @since 3.7.0
     */
    public static function init() {
        $upload_dir = wp_upload_dir();
        self::$dir = $upload_dir['basedir'] . '/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/';
        self::$url = $upload_dir['baseurl'] . '/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/';
        if (!has_action('admin_post_lws_download_file', array(__CLASS__, 'download_file'))) {
            add_action('admin_post_lws_download_file', array(__CLASS__, 'download_file'));
        }
    }

    /**
     * Protect the storage root against direct web access (index.php and deny rules).
     *
     * @since 3.8.0
     */
    private static function protect_dir() {
        if (!file_exists(self::$dir . 'index.php')) {
            @file_put_contents(self::$dir . 'index.php', "<?php\n// Silence is golden.\n");
        }
        if (!file_exists(self::$dir . '.htaccess')) {
            @file_put_contents(self::$dir . '.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n");
        }
    }

    /**
     * Get the extensions allowed by the file manager.
     *
     * @return string The allowed extensions. Comma separated list.
     * @since 3.8.0
     */
    public static function get_managed_extensions() {
        return array_keys(self::$managed_extension);
    }

    /**
     * Is this file name one of the files managed (created/purged/served) by the plugin?
     *
     * @param string $file A file name (no path).
     * @return string The lowercase extension if managed, empty string otherwise.
     * @since 3.8.0
     */
    public static function managed_extension($file) {
        $ext = strtolower((string)pathinfo((string)$file, PATHINFO_EXTENSION));
        return array_key_exists($ext, self::$managed_extension) ? $ext : '';
    }

    public static function get_allowed_extension() {
        $tab = array();
        foreach (self::$allowed_extension as $key => $val) {
            $tab[] = '.' . $key;
        }
        return implode(', ', $tab);
    }

    /**
     * Check if the file is writable.
     *
     * @return boolean True if the file is writable. False otherwise.
     * @since 3.7.0
     */
    private static function check_for_write() {
        if (!file_exists(self::$dir)) {
            try {
                wp_mkdir_p(self::$dir);
            }
            catch (\Exception $ex) {
                Logger::alert(self::$service,null, null, null, null, null, $ex->getCode(), 'Unable to create persistent storage root: ' . $ex->getMessage());
                return false;
            }
        }
        if (!wp_is_writable(self::$dir)) {
            try {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod -- single chmod of the plugin storage directory, WP_Filesystem has no credentials here (also called outside admin screens) and the path is the plugin own storage root
                chmod(self::$dir, 0755);
            }
            catch (\Exception $ex) {
                Logger::alert(self::$service,null, null, null, null, null, $ex->getCode(), 'Unable to make persistent storage root writable: ' . $ex->getMessage());
                return false;
            }
        }
        if (wp_is_writable(self::$dir)) {
            self::protect_dir();
            return true;
        }
        return false;
    }

    /**
     * Get a random uid (unguessable).
     *
     * @return string The random uid.
     * @since 3.7.0
     */
    private static function uid() {
        return bin2hex(random_bytes(16));
    }

    /**
     * Get the file name.
     *
     * @param string $station_name The name of the station.
     * @param string $start The start date of the export/import.
     * @param string $end The end date of the export/import.
     * @param string $uid The unique id of the file (mainly the V4 UUID of the process).
     * @param string $ext The extension of the file (w/o the dot).
     * @return string The file name.
     * @since 3.7.0
     */
    public static function get_full_file_url($station_name, $start, $end, $uid, $ext) {
        return self::get_download_url(self::get_file_name($station_name, $start, $end, $uid, $ext));
    }

    /**
     * Get the url to download a file. The storage root is not directly accessible from the web:
     * files are served by a handler which checks the capability and a nonce bound to the file.
     *
     * @param string $file The name of the file (w/o path).
     * @param boolean $inline Optional. View the file in the browser instead of downloading it.
     * @return string The url.
     * @since 3.8.0
     */
    public static function get_download_url($file, $inline=false) {
        $file = basename($file);
        $args = array('action' => 'lws_download_file', 'file' => rawurlencode($file), '_wpnonce' => wp_create_nonce('lws-download-' . $file));
        if ($inline) {
            $args['inline'] = 1;
        }
        return add_query_arg($args, admin_url('admin-post.php'));
    }

    /**
     * Serve a file of the storage root (admin-post handler).
     * Checks the capability and the nonce bound to the file before anything else.
     *
     * @since 3.8.0
     */
    public static function download_file() {
        // The nonce is bound to the raw name given in the listing: verify it on the raw requested name.
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- the nonce is bound to the raw name so it cannot be altered by sanitize_text_field(); the name is then validated strictly below (basename, no control character or slash, 255 characters max)
        $file = isset($_GET['file']) ? rawurldecode(wp_unslash((string)$_GET['file'])) : '';
        if ($file !== basename(str_replace('\\', '/', $file)) || preg_match('/[\x00-\x1f\x7f\/\\\\]/', $file) === 1 || strlen($file) > 255) {
            $file = '';
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- this line only reads the nonce, wp_verify_nonce() checks it on the next statement
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash((string)$_GET['_wpnonce'])) : '';
        if (!current_user_can(live_weather_station_manage_capability()) || $file === '' || !wp_verify_nonce($nonce, 'lws-download-' . $file)) {
            Logger::critical('Security', null, null, null, null, null, 0, 'Unauthorized or forged attempt to download a file.');
            wp_die(esc_html__('You do not have sufficient permissions to download this file.', 'live-weather-station'), '', array('response' => 403));
        }
        $ext = self::managed_extension($file);
        $path = self::$dir . $file;
        $real = realpath($path);
        $root = realpath(self::$dir);
        if ($ext === '' || $real === false || $root === false || dirname($real) !== $root || !is_file($real)) {
            wp_die(esc_html__('File not found.', 'live-weather-station'), '', array('response' => 404));
        }
        nocache_headers();
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . self::$managed_extension[$ext] . '; charset=utf-8');
        header('Content-Disposition: ' . (isset($_GET['inline']) ? 'inline' : 'attachment') . '; filename="' . str_replace(array('"', "\r", "\n"), '', $file) . '"');
        header('Content-Length: ' . filesize($real));
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- streams a file to the browser, WP_Filesystem cannot stream; $real is a file of the plugin storage root checked just above (realpath, same directory, managed extension)
        readfile($real);
        exit;
    }

    /**
     * Get the file name.
     *
     * @param string $station_name The name of the station.
     * @param string $start The start date of the export/import.
     * @param string $end The end date of the export/import.
     * @param string $uid The unique id of the file (mainly the V4 UUID of the process).
     * @param string $ext The extension of the file (w/o the dot).
     * @return string The file name.
     * @since 3.7.0
     */
    public static function get_file_name($station_name, $start, $end, $uid, $ext) {
        $station_name = str_replace(self::$file_name_separator, '-', strtolower($station_name));
        return sanitize_file_name( $station_name. self::$file_name_separator . $start . self::$file_name_separator . $end . self::$file_name_separator . $uid . '.' . $ext);
    }

    /**
     * Get the absolute file name.
     *
     * @param string $station_name The name of the station.
     * @param string $start The start date of the export/import.
     * @param string $end The end date of the export/import.
     * @param string $uid The unique id of the file (mainly the V4 UUID of the process).
     * @param string $ext The extension of the file (w/o the dot).
     * @return string The fully qualified file name.
     * @since 3.7.0
     */
    public static function get_full_file_name($station_name, $start, $end, $uid, $ext) {
        return self::$dir . self::get_file_name($station_name, $start, $end, $uid, $ext);
    }

    /**
     * Construct the absolute file name.
     *
     * @param string $file The file.
     * @return string The fully qualified file name.
     * @since 3.7.0
     */
    public static function construct_full_file_name($file) {
        $file = basename(wp_normalize_path(trim((string)$file)));
        if ($file === '' || $file === '.' || $file === '..') {
            return self::$dir;
        }
        $full = self::$dir . $file;
        $real = realpath($full);
        if ($real !== false) {
            // Must stay inside the storage root (no symlink outside).
            $root = realpath(self::$dir);
            if ($root === false || dirname($real) !== $root) {
                return self::$dir;
            }
        }
        return $full;
    }

    /**
     * Get the root name.
     *
     * @return string The file name.
     * @since 3.7.0
     */
    public static function get_root_name() {
        return self::$dir;
    }

    /**
     * Check if the file is writable.
     *
     * @param string $station_name The name of the station.
     * @param string $start The start date of the export/import.
     * @param string $end The end date of the export/import.
     * @param string $uid The unique id of the file (mainly the V4 UUID of the process).
     * @param string $ext The extension of the file (w/o the dot).
     * @return string|boolean The file name if all is ok to write it. False otherwise.
     * @since 3.7.0
     */
    public static function file_for_write($station_name, $start, $end, $uid, $ext) {
        $filename = '';
        // Only the extensions managed by the plugin can be created (the last segment counts: 'wsconf.json' is 'json').
        if (!is_string($ext) || self::managed_extension('x.' . $ext) === '') {
            Logger::critical(self::$service,null, null, null, null, null, 1, 'Unable to write a file with an unmanaged extension.');
            return false;
        }
        if (self::check_for_write()) {
            return self::get_file_name($station_name, $start, $end, $uid, $ext);
        }
        else {
            Logger::critical(self::$service,null, null, null, null, null, 1, 'Unable to write file "' . $filename . '"');
            return false;
        }
    }

    /**
     * Check if the file is writable.
     *
     * @param string $station_name The name of the station.
     * @param string $start The start date of the export/import.
     * @param string $end The end date of the export/import.
     * @param string $uid The unique id of the file (mainly the V4 UUID of the process).
     * @param string $ext The extension of the file (w/o the dot).
     * @return boolean The file name if all is ok to write it. False otherwise.
     * @since 3.7.0
     */
    public static function create_file($station_name, $start, $end, $uid, $ext) {
        $filename = self::file_for_write($station_name, $start, $end, $uid, $ext);
        if ($filename !== false) {
            try {
                $created = (false !== file_put_contents(self::$dir . $filename, ''));
                if ($created) {
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod -- restricts a file just created by the plugin in its own storage root, WP_Filesystem has no credentials here (also called outside admin screens)
                    @chmod(self::$dir . $filename, 0600);
                }
                return $created;
            }
            catch (\Exception $ex) {
                Logger::critical(self::$service,null, null, null, null, null, $ex->getCode(), 'Unable to create a file in persistent storage root: ' . $ex->getMessage());
                return false;
            }
        }
        else {
            Logger::critical(self::$service,null, null, null, null, null, 1, 'Unable to create file "' . $filename . '"');
            return false;
        }
    }

    /**
     * Write a line in a file.
     *
     * @param string $filename The full name of the file.
     * @param string $data The data to write.
     * @since 3.7.0
     */
    public static function write_file($filename, $data) {
        file_put_contents($filename, $data);
    }

    /**
     * Write a line in a file.
     *
     * @param string $filename The full name of the file.
     * @param string $line The line to write.
     * @since 3.7.0
     */
    public static function write_file_line($filename, $line) {
        file_put_contents($filename, $line . PHP_EOL);
    }

    /**
     * Add a line at the end of a file.
     *
     * @param string $filename The full name of the file.
     * @param string $line The line to write.
     * @since 3.7.0
     */
    public static function add_file_line($filename, $line) {
        file_put_contents($filename, $line . PHP_EOL, FILE_APPEND);
    }

    /**
     * List the storage root.
     *
     * @return array The files list.
     * @since 3.7.0
     */
    public static function raw_list_dir() {
        $result = array();
        if (self::check_for_write()) {
            foreach (array_diff(scandir(self::$dir), array('..', '.')) as $item) {
                if (!is_dir(self::$dir . $item) && $item !== 'index.php' && $item !== '.htaccess') {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    /**
     * List the storage root.
     *
     * @param boolean $only_valid Optional. Exclude invalid files.
     * @return array The extended files list.
     * @since 3.7.0
     */
    public static function extended_list_dir($only_valid=true) {
        $result = array();
        foreach (self::raw_list_dir() as $file) {
            $e = explode('_', $file);
            $station = __('unknown station', 'live-weather-station');
            $uuid = '-';
            $from = '-';
            $to = '-';
            $ext = 'ukn';
            $valid = false;
            if (count($e) === 4) {
                $d = explode('.', $e[3]);
                if (count($d) == 3) {
                    $d[1] = $d[1] . '.' . $d[2];
                    unset($d[2]);
                }
                if (count($d) === 2) {
                    $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
                    if (preg_match($UUIDv4, $d[0]) === 1) {
                        $station = sanitize_text_field(ucwords(str_replace('-', ' ', $e[0])));
                        $uuid = sanitize_text_field($d[0]);
                        $from = sanitize_text_field($e[1]);
                        $to = sanitize_text_field($e[2]);
                        $ext = sanitize_text_field($d[1]);
                        $valid = true;
                    }
                }
            }
            if ($valid || !$only_valid) {
                try {
                    $size = filesize(self::$dir . $file);
                }
                catch (\Exception $ex) {
                    $size = 0;
                }
                $decimal = 0;
                if ($size > 1024) {
                    $decimal = 1;
                }
                if ($size > 1024*1024) {
                    $decimal = 2;
                }
                try {
                    $time = filemtime(self::$dir . $file);
                }
                catch (\Exception $ex) {
                    $time = time();
                }
                $f = array();
                $f['file'] = $file;
                $f['station'] = $station;
                $f['uuid'] = $uuid;
                $f['from'] = $from;
                $f['to'] = $to;
                $f['ext'] = $ext;
                $f['size'] = $size;
                $f['state'] = 'none';
                $f['progress'] = '100';
                $f['std_size'] = size_format($f['size'], $decimal);
                $f['date'] = $time;
                $f['url'] = self::get_download_url($file);
                $result[] = $f;
            }
        }
        return $result;
    }

    /**
     * Purge old files.
     *
     * @since 3.8.0
     */
    public static function purge() {
        $count = 0;
        if ((int)get_option('live_weather_station_file_retention', 7) > 0) {
            $time = time() - (86400 * get_option('live_weather_station_file_retention', 7));
            foreach (self::extended_list_dir(false) as $file) {
                // Only delete files managed by the plugin.
                if ($file['date'] < $time && self::managed_extension($file['file']) !== '') {
                    try {
                        wp_delete_file(self::$dir . $file['file']);
                        $count += 1;
                    }
                    catch (\Exception $ex) {
                        //
                    }
                }
            }
        }
        if ($count == 1) {
            Logger::notice(self::$service, null, null, null, null, null, null, '1 obsolete file deleted.');
        }
        elseif ($count > 1) {
            Logger::notice(self::$service, null, null, null, null, null, null, sprintf('%s obsolete file(s) deleted.', $count));
        }
        else {
            Logger::notice(self::$service,null,null,null,null,null,null,'No obsolete files to delete.');
        }
    }

    /**
     * Delete old files.
     *
     * @since 3.8.0
     */
    public function rotate() {
        $cron_id = Watchdog::init_chrono(Watchdog::$file_rotate_name);
        self::purge();
        Watchdog::stop_chrono($cron_id);
    }

    /**
     * Get a list of valid files.
     *
     * @param array $extension Optional. Includes only these file extensions.
     * @return array The extended files list.
     * @since 3.7.0
     */
    public static function get_valid($extension=array()) {
        $result = array();
        foreach (self::extended_list_dir() as $file) {
            if (count($extension) > 0) {
                if (in_array($file['ext'], $extension)) {
                    $result[] = $file;
                }
            }
            else {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Find a file from an uuid.
     *
     * @param string $uuid The uuid to find.
     * @param array $extension Optional. Includes only these file extensions.
     * @return array The extended files list.
     * @since 3.7.0
     */
    public static function find_valid($uuid, $extension=array()) {
        $result = array();
        foreach (self::get_valid($extension) as $file) {
            if ($file['uuid'] === $uuid) {
                $result = $file;
                break;
            }
        }
        if (count($result) > 0) {
            try {
                $file = new \SplFileObject(self::$dir . $result['file']);
                $file->seek(PHP_INT_MAX);
                $lines = $file->key() + 1;
                $file = null;
            }
            catch (\Exception $ex) {
                $lines = 0;
            }
            $result['lines'] = $lines;
        }
        return $result;
    }

    /**
     * Check configuration elements in a file.
     *
     * @param string $uuid The uuid of the file.
     * @return boolean|array False if it's impossible to access the file, otherwise an array containing configuration elements.
     * @since 3.8.0
     */
    public static function check_configuration($uuid) {
        $result = false;
        $content = self::get_configuration($uuid);
        if (is_array($content)) {
            $result = array();
            foreach (array('settings', 'stations', 'modules', 'maps') as $item) {
                if (array_key_exists($item, $content) && is_array($content[$item])) {
                    $result[$item] = count($content[$item]);
                }
            }
        }
        return $result;
    }

    /**
     * Get configuration file content.
     *
     * @param string $uuid The uuid of the file.
     * @return boolean|array False if it's impossible to access the file, otherwise an array containing configuration.
     * @since 3.8.0
     */
    public static function get_configuration($uuid) {
        $file = self::find_valid($uuid, array('wsconf.json'));
        if (!is_array($file) || !array_key_exists('file', $file)) {
            return false;
        }
        $path = self::construct_full_file_name($file['file']);
        if (!is_file($path) || filesize($path) > self::$max_upload_size) {
            return false;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }
        $result = json_decode($content, true);
        return is_array($result) ? $result : false;
    }

    /**
     * Change the upload dir.
     *
     * @return array The file manager directory.
     * @since 3.8.0
     */
    public static function change_upload_dir($dirs) {
        $dirs['subdir'] = '/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/';
        $dirs['path'] = self::$dir;
        $dirs['url'] = self::$url;
        return $dirs;
    }

    /**
     * Change the allowed mime types.
     *
     * @return array The allowed mime types.
     * @since 3.8.0
     */
    public static function change_upload_mimes($mimes) {
        // Restrict (not merge) to the file types managed by the plugin.
        $result = array();
        foreach (self::$allowed_extension as $key => $val) {
            $result[$key] = $val;
        }
        return $result;
    }

    /**
     * Accept .json and .ndjson files whatever the mime type reported by libmagic (text/plain, application/json,
     * application/x-ndjson...). Only hooked during the plugin's own upload; the content is checked afterwards.
     *
     * @param array $data The file data (ext, type, proper_filename).
     * @param string $file The full path of the file.
     * @param string $filename The name of the file.
     * @param array $mimes The allowed mime types.
     * @param string|false $real_mime Optional. The real mime type detected by WordPress.
     * @return array The file data.
     * @since 3.8.0
     */
    public static function recheck_filetype_and_ext($data=null, $file=null, $filename=null, $mimes=null, $real_mime=false) {
        if (!is_array($data)) {
            return $data;
        }
        if (!empty($data['ext']) && !empty($data['type'])) {
            return $data;
        }
        $exploded = explode('.', (string)$filename);
        $ext = strtolower(end($exploded));
        if (!array_key_exists($ext, self::$allowed_extension)) {
            return $data;
        }
        $accepted = array('text/plain', 'application/json', 'application/x-ndjson', 'application/ndjson', 'application/jsonl', 'application/x-jsonlines', 'text/json', 'application/octet-stream');
        if (is_string($real_mime) && $real_mime !== '' && !in_array(strtolower($real_mime), $accepted, true)) {
            return $data;
        }
        $data['ext'] = $ext;
        $data['type'] = self::$allowed_extension[$ext];
        $data['proper_filename'] = false;
        return $data;
    }

    /**
     * Get configuration file content.
     *
     * @return array An array representing the result.
     * @since 3.8.0
     */
    public static function upload_file() {
        if (!function_exists( 'wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $result = array('done' => false, 'error' => __('Unknown error', 'live-weather-station'));
        if (!current_user_can(live_weather_station_manage_capability())) {
            $result['error'] = __('You do not have sufficient permissions to add files.', 'live-weather-station');
            return $result;
        }
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- the 'add-file' nonce is verified by SystemPluginAdmin::add_file() before it calls upload_file(), which also checks the manage capability
        if(!empty($_FILES['file-to-upload'])) {
            if (!isset($_FILES['file-to-upload']['size']) || !is_scalar($_FILES['file-to-upload']['size']) || !isset($_FILES['file-to-upload']['name']) || !is_string($_FILES['file-to-upload']['name']) || (int)$_FILES['file-to-upload']['size'] > self::$max_upload_size || (int)$_FILES['file-to-upload']['size'] <= 0) {
                $result['error'] = __('invalid file size', 'live-weather-station');
                Logger::error(self::$service, null, null, null, null, null, 99, 'Unable to add this file: invalid file size.');
                return $result;
            }
            if (!self::check_for_write()) {
                return $result;
            }
            add_filter('upload_mimes', array(get_called_class(), 'change_upload_mimes'));
            add_filter('wp_check_filetype_and_ext', array(get_called_class(), 'recheck_filetype_and_ext'), 10, 5);
            add_filter('upload_dir', array(get_called_class(), 'change_upload_dir'));
            $file = wp_handle_upload($_FILES['file-to-upload'], array('test_form' => false));
            if (is_array($file) && !isset($file['error'])) {
                if (isset($file['file']) && self::check_uploaded_content($file['file'])) {
                    $result['done'] = true;
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod -- restricts a file just added by the plugin in its own storage root (the plugin serves it itself through download_file()), WP_Filesystem has no credentials here
                    @chmod($file['file'], 0600);
                }
                else {
                    if (isset($file['file'])) {
                        wp_delete_file($file['file']);
                    }
                    $result['error'] = __('invalid file content', 'live-weather-station');
                    Logger::error(self::$service, null, null, null, null, null, 99, 'Unable to add this file: invalid JSON/NDJSON content.');
                }
            } else {
                $error = (is_array($file) && isset($file['error'])) ? (string)$file['error'] : 'unknown error';
                $result['error'] = live_weather_station_lcfirst($error);
                Logger::error(self::$service, null, null, null, null, null, 99, 'Unable to add this file: ' . $error);
            }
            remove_filter('upload_dir', array(get_called_class(), 'change_upload_dir'));
            remove_filter('upload_mimes', array(get_called_class(), 'change_upload_mimes'));
            remove_filter('wp_check_filetype_and_ext', array(get_called_class(), 'recheck_filetype_and_ext'), 10);
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        return $result;
    }

    /**
     * Check the content of an uploaded file: valid JSON (.json) or valid ND-JSON (.ndjson).
     *
     * @param string $path The full path of the file.
     * @return boolean True if the content is valid.
     * @since 3.8.0
     */
    private static function check_uploaded_content($path) {
        $real = realpath($path);
        $root = realpath(self::$dir);
        if ($real === false || $root === false || dirname($real) !== $root || !is_file($real) || filesize($real) > self::$max_upload_size) {
            return false;
        }
        $ext = strtolower((string)pathinfo($real, PATHINFO_EXTENSION));
        if ($ext === 'json') {
            $content = json_decode((string)file_get_contents($real), true);
            return is_array($content);
        }
        if ($ext === 'ndjson') {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- reads the file line by line to validate ND-JSON without loading it all, WP_Filesystem cannot stream; $real is a file of the plugin storage root checked at the top of this method
            $handle = fopen($real, 'r');
            if ($handle === false) {
                return false;
            }
            $count = 0;
            $valid = true;
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (!is_array(json_decode($line, true))) {
                    $valid = false;
                    break;
                }
                $count++;
            }
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- closes the handle opened with fopen() above
            fclose($handle);
            return $valid && $count > 0;
        }
        return false;
    }

}
