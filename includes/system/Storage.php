<?php

namespace WeatherStation\System\Storage;

use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;

/**
 * This class add storage management capacity to the plugin.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class Manager {


    private static $dir = '';
    private static $url = '';
    private static $service = 'Storage Manager';
    private static $file_name_separator = '_';
    private static $allowed_extension = array('ndjson' => 'text/plain', 'json' => 'text/plain');

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
        self::$dir = $upload_dir['basedir'] . '/' . LWS_PLUGIN_SLUG . '/';
        self::$url = $upload_dir['baseurl'] . '/' . LWS_PLUGIN_SLUG . '/';
    }

    /**
     * Get the extensions allowed by the file manager.
     *
     * @return string The allowed extensions. Comma separated list.
     * @since 3.8.0
     */
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
                mkdir(self::$dir, 0755);
            }
            catch (\Exception $ex) {
                Logger::alert(self::$service,null, null, null, null, null, $ex->getCode(), 'Unable to create persistent storage root: ' . $ex->getMessage());
                return false;
            }
        }
        if (!is_writable(self::$dir)) {
            try {
                chmod(self::$dir, 0755);
            }
            catch (\Exception $ex) {
                Logger::alert(self::$service,null, null, null, null, null, $ex->getCode(), 'Unable to make persistent storage root writable: ' . $ex->getMessage());
                return false;
            }
        }
        return is_writable(self::$dir);
    }

    /**
     * Get a pseudo uid.
     *
     * @return string The pseudo uid.
     * @since 3.7.0
     */
    private static function uid() {
        $fingerprint = uniqid('', true);
        return substr ($fingerprint, strlen($fingerprint)-10, 80);
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
        return self::$url . self::get_file_name($station_name, $start, $end, $uid, $ext);
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
        $file = trim($file);
        $file = str_replace('/../', '', $file);
        $file = str_replace('../', '', $file);
        $file = str_replace('/..', '', $file);
        $file = str_replace('/./', '', $file);
        $file = str_replace('./', '', $file);
        $file = str_replace('/.', '', $file);
        $file = str_replace('/', '', $file);
        return self::$dir . $file;
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
                return false !== file_put_contents(self::$dir . $filename, '');
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
                if (!is_dir(self::$dir . $item)) {
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
                    if (preg_match($UUIDv4, $d[0]) !== false) {
                        $station = ucwords(str_replace('-', ' ', $e[0]));
                        $uuid = $d[0];
                        $from = $e[1];
                        $to = $e[2];
                        $ext = $d[1];
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
                $f['url'] = self::get_full_file_url($station, $from, $to, $uuid, $ext);
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
                if ($file['date'] < $time) {
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
        if ($content) {
            try {
                $result = array();
                foreach (array('settings', 'stations', 'modules', 'maps') as $item) {
                    if (array_key_exists($item, $content)) {
                        $result[$item] = count($content[$item]);
                    }
                }
            }
            catch (\Exception $ex) {
                $result = false;
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
        try {
            $result = json_decode(file_get_contents(self::get_root_name() . '/' . $file['file']), true);
        }
        catch (\Exception $ex) {
            $result = false;
        }
        return $result;
    }

    /**
     * Change the upload dir.
     *
     * @return array The file manager directory.
     * @since 3.8.0
     */
    public static function change_upload_dir($dirs) {
        $dirs['subdir'] = '/' . LWS_PLUGIN_SLUG . '/';
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
        foreach (self::$allowed_extension as $key => $val) {
            $mimes[$key] = $val;
        }
        return $mimes;
    }

    /**
     * Change the allowed mime types.
     *
     * @return array The allowed mime types.
     * @since 3.8.0
     */
    public static function recheck_filetype_and_ext($data=null, $file=null, $filename=null, $mimes=null) {
        $ext = isset($data['ext'])?$data['ext']:'';
        if (strlen($ext) < 1) {
            $exploded = explode('.', $filename);
            $ext = strtolower(end($exploded));
        }
        if ($ext === 'json') {
            $values['ext'] = 'json';
            $values['type'] = 'application/json';
        }
        if ($ext === 'ndjson') {
            $values['ext'] = 'ndjson';
            $values['type'] = 'application/json';
        }
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
        if(!empty($_FILES['file-to-upload'])) {
            //add_filter('wp_check_filetype_and_ext', array(get_called_class(), 'recheck_filetype_and_ext'));
            add_filter('upload_mimes', array(get_called_class(), 'change_upload_mimes'));
            add_filter('upload_dir', array(get_called_class(), 'change_upload_dir'));
            $file = wp_handle_upload($_FILES['file-to-upload'], array('test_form' => false));
            if ($file && !isset($file['error'])) {
                $result['done'] = true;
            } else {
                $result['error'] = lws_lcfirst($file['error']);
                Logger::error(self::$service, null, null, null, null, null, 99, 'Unable to add this file: ' . $file['error']);
            }
            remove_filter('upload_dir', array(get_called_class(), 'change_upload_dir'));
            remove_filter('upload_mimes', array(get_called_class(), 'change_upload_mimes'));
            //remove_filter('wp_check_filetype_and_ext', array(get_called_class(), 'recheck_filetype_and_ext'));
        }
        return $result;
    }

}