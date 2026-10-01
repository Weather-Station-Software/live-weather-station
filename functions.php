<?php
/**
 * Utilities functions.
 *
 * @package Bootstrap
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

/**
 * Get the proper admin page url.
 *
 * @param string $page The main page.
 * @param string $action Optional. The specific action on the page.
 * @param string $tab Optional. The tab if the page is tabbed.
 * @param boolean $dashboard Optional. If set to true, redirects to plugin dashboard.
 * @return string The full url of the admin page.
 * @since 3.0.0
 */
function live_weather_station_get_admin_page_url($page='lws-dashboard', $action=null, $tab=null, $service=null, $dashboard=false, $id=null, $xid=null) {
    $args = array('page' => $page);
    if (isset($tab)) {
        $args['tab'] = $tab;
    }
    if (isset($action)) {
        $args['action'] = $action;
    }
    if (isset($service)) {
        $args['service'] = $service;
    }
    if (isset($id)) {
        $args['id'] = $id;
    }
    if (isset($xid)) {
        $args['xid'] = $xid;
    }
    $args['dashboard'] = $dashboard;
    $url = add_query_arg($args, admin_url('admin.php'));
    return $url;
}

/**
 * Get and admin page url based on the current one.
 *
 * @param array $params The params to override.
 * @return string The full url of the admin page.
 * @since 3.4.0
 */
function live_weather_station_re_get_admin_page_url($params) {
    $set = array('page', 'tab', 'action', 'service', 'id');
    $args = array();
    foreach ($set as $arg) {
        if (isset($_POST[$arg]) && is_scalar($_POST[$arg])) {
            $args[$arg] = ($arg == 'id' ? sanitize_text_field(wp_unslash($_POST[$arg])) : sanitize_key(wp_unslash($_POST[$arg])));
        }
        if (isset($_GET[$arg]) && is_scalar($_GET[$arg])) {
            $args[$arg] = ($arg == 'id' ? sanitize_text_field(wp_unslash($_GET[$arg])) : sanitize_key(wp_unslash($_GET[$arg])));
        }
        if (array_key_exists($arg, $params)) {
            $args[$arg] = $params[$arg];
        }
    }
    $url = add_query_arg($args, admin_url('admin.php'));
    return $url;
}

/**
 * Get the proper user locale regarding WP version differences.
 *
 * @param int|WP_User $user_id User's ID or a WP_User object. Defaults to current user.
 * @return string The locale of the user.
 * @since 3.0.8
 */
function live_weather_station_get_display_locale($user_id = 0) {
    global $current_user;
    if (!empty($current_user) && $user_id === 0) {
        if ($current_user instanceof WP_User) {
            $user_id = $current_user->ID;
        }
        if (is_object($current_user) && isset($current_user->ID)) {
            $user_id = $current_user->ID;
        }
    }
    /*
    * @fixme how to manage ajax calls made from frontend?
    */
    if (function_exists('get_user_locale') && (is_admin() || is_blog_admin())) {
        return get_user_locale($user_id);
    }
    else {
        return get_locale();
    }
}

/**
 * Make a string's first character lowercase the Weather Station's way.
 * @param string $str The input string.
 * @return string the resulting string.
 * @since 3.7.5
 */
function live_weather_station_lcfirst($str) {
    if (strpos(strtolower(live_weather_station_get_display_locale()), 'de') === 0) {
        return ucfirst($str);
    }
    else {
        return lcfirst($str);
    }
}

/**
 * Cast recursively an object in array of arrays.
 *
 * @param object|array $obj The object to cast.
 * @return array The converted array.
 * @since 3.4.0
 */
function live_weather_station_object_to_array($obj) {
    $arr = array();
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? live_weather_station_object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}

/**
 * Order an array of array.
 *
 * @return array The sorted array.
 * @since 3.4.0
 */
function live_weather_station_array_orderby(){
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

/**
 * Compare the key 1 of two arrays.
 *
 * @return boolean Result of the comparison.
 * @since 3.4.0
 */
function live_weather_station_array_compare_1($a, $b){
    $a = (array)$a;
    $b = (array)$b;
    return strcasecmp($a[1], $b[1]);
}

/**
 * Multi dimensional version of array_unique.
 *
 * @param array $array The array to make unique.
 * @param string|integer $key The key on which comparing.
 * @return array The uniquified array.
 * @since 3.4.0
 */
function live_weather_station_array_super_unique($array, $key){
    $temp_array = array();
    foreach ($array as &$v) {
        if (!isset($temp_array[$v[$key]]))
            $temp_array[$v[$key]] =& $v;
    }
    $array = array_values($temp_array);
    return $array;
}

/**
 * Capability required to manage the plugin, filterable by the site.
 * The former filter name (lws_manage_options_capability) is still honoured so existing customizations keep working.
 *
 * @since 3.9.0
 * @return string The capability.
 */
function live_weather_station_manage_capability() {
    $capability = apply_filters('lws_manage_options_capability', 'manage_options');
    return apply_filters('live_weather_station_manage_options_capability', $capability);
}

/**
 * Registers (but don't enqueues) a style asset of the plugin.
 *
 * Regarding user's option, asset is ready to enqueue from local plugin dir or from CDN (jsDelivr)
 *
 * @since 3.5.0
 */
function live_weather_station_register_style($handle, $source, $file, $deps = array(), $cdn_available=true) {
    if ((bool)get_option('live_weather_station_use_cdn') && $cdn_available) {
        if ($source == LIVE_WEATHER_STATION_ADMIN_URL) {
            $file = 'https://cdn.jsdelivr.net/wp/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/tags/' . LIVE_WEATHER_STATION_VERSION . '/admin/' . $file;
        }
        else {
            $file = 'https://cdn.jsdelivr.net/wp/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/tags/' . LIVE_WEATHER_STATION_VERSION . '/public/' . $file;
        }
        wp_register_style($handle, $file, $deps, null);
    }
    else {
        wp_register_style($handle, $source . $file, $deps, LIVE_WEATHER_STATION_VERSION);
    }
}

/**
 * Registers (but don't enqueues) a script asset of the plugin.
 *
 * Regarding user's option, asset is ready to enqueue from local plugin dir or from CDN (jsDelivr)
 *
 * @since 3.5.0
 */
function live_weather_station_register_script($handle, $source, $file, $deps = array(), $cdn_available=true) {
    if ((bool)get_option('live_weather_station_use_cdn') && $cdn_available) {
        if ($source == LIVE_WEATHER_STATION_ADMIN_URL) {
            $file = 'https://cdn.jsdelivr.net/wp/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/tags/' . LIVE_WEATHER_STATION_VERSION . '/admin/' . $file;
        }
        else {
            $file = 'https://cdn.jsdelivr.net/wp/' . LIVE_WEATHER_STATION_PLUGIN_SLUG . '/tags/' . LIVE_WEATHER_STATION_VERSION . '/public/' . $file;
        }
        wp_register_script($handle, $file, $deps, null, (bool)get_option('live_weather_station_footer_scripts', false));
    }
    else {
        wp_register_script($handle, $source . $file, $deps, LIVE_WEATHER_STATION_VERSION, (bool)get_option('live_weather_station_footer_scripts', false));
    }
}


/**
 * Enqueues the right scripts and/or stylesheets regarding the selected version of Font Awesome
 *
 * @since 3.5.3
 */
function live_weather_station_font_awesome($all=false) {
    $mode = get_option('live_weather_station_fa_mode');
    if (is_admin()) {
        $mode = 1;
    }
    switch ($mode) {
        case 0:                                             // Font Awesome 4 outputted by Weather Station
            wp_enqueue_style('lws-font-awesome-4');
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'fa');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fa');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fa');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', false);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', false);
            }
            break;
        case 1:                                             // Font Awesome 5 outputted by Weather Station as CSS
            wp_enqueue_style('lws-font-awesome-5');
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'far');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fab');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fas');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', true);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', false);
            }
            break;
        case 2:                                             // Font Awesome 5 outputted by Weather Station as JS+SVG
            if ($all) {
                wp_enqueue_script('lws-fa-all');
            }
            else {
                wp_enqueue_script('lws-fa-regular');
                wp_enqueue_script('lws-fa-solid');
            }
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'far');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fab');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fas');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', true);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', true);
            }
            break;
        case 3:                                             // Font Awesome 4 outputted by theme or other plugin
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'fa');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fa');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fa');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', false);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', false);
            }
            break;
        case 4:                                             // Font Awesome 5 outputted by theme or other plugin as CSS
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'far');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fab');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fas');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', true);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', false);
            }
            break;
        case 5:                                             // Font Awesome 5 outputted by theme or other plugin as JS+SVG
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'far');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fab');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fas');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', true);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', true);
            }
            break;
        case 6:                                             // Font Awesome official plugin
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LIVE_WEATHER_STATION_FAR')) {
                define('LIVE_WEATHER_STATION_FAR', 'far');
            }
            if (!defined('LIVE_WEATHER_STATION_FAB')) {
                define('LIVE_WEATHER_STATION_FAB', 'fab');
            }
            if (!defined('LIVE_WEATHER_STATION_FAS')) {
                define('LIVE_WEATHER_STATION_FAS', 'fas');
            }
            if (!defined('LIVE_WEATHER_STATION_FA5')) {
                define('LIVE_WEATHER_STATION_FA5', true);
            }
            if (!defined('LIVE_WEATHER_STATION_FA_SVG')) {
                define('LIVE_WEATHER_STATION_FA_SVG', true);
            }
            break;
    }
}

/**
 * Check whether Weather Station is active.
 *
 * Only plugins installed in the plugins/ folder can be active.
 *
 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
 * return false for those plugins.
 *
 * @since 3.5.3
 *
 * @return bool True, if Weather Station is in the active plugins list. False, otherwise.
 */

function live_weather_station_is_active() {
    return in_array( 'live-weather-station/live-weather-station.php', (array) get_option( 'active_plugins', array() ) ) || live_weather_station_is_active_for_network();
}

/**
 * Check whether Weather Station is active.
 *
 * Only plugins installed in the plugins/ folder can be active.
 *
 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
 * return false for those plugins.
 *
 * @since 3.5.3
 *
 * @return bool True, if Weather Station is in the active plugins list. False, otherwise.
 */
function live_weather_station_is_active_for_network() {
    if ( !is_multisite() )
        return false;

    $plugins = get_site_option( 'active_sitewide_plugins');
    if ( isset($plugins['live-weather-station/live-weather-station.php']) )
        return true;

    return false;
}

/**
 * Returns an appropriately localized display name for the input locale
 *
 * @since 3.5.4
 *
 * @param string $locale The locale to return a display name for.
 * @param string $in_locale Optional. Format locale.
 * @return string Display name of the locale in the format appropriate for $in_locale.
 */
function live_weather_station_get_locale_name($locale, $in_locale = null) {
    $result = $locale;
    if (LIVE_WEATHER_STATION_I18N_LOADED) {
        $result = \Locale::getDisplayName($locale, $in_locale);
    }
    return $result;
}

/**
 * Returns an appropriately localized display name for region of the input locale
 *
 * @since 3.5.4
 *
 * @param string $locale The locale to return a display region for.
 * @param string $in_locale Optional. Format locale.
 * @return string Display name of the region for the $locale in the format appropriate for $in_locale.
 */
function live_weather_station_get_region_name($locale, $in_locale = null) {
    $result = $locale;
    if (LIVE_WEATHER_STATION_I18N_LOADED) {
        $result = \Locale::getDisplayRegion($locale, $in_locale);
    }
    return $result;
}

/**
 * Try to send an alert email.
 *
 * @since 3.7.0
 */
function live_weather_station_send_alert_message() {
    if (defined('LIVE_WEATHER_STATION_WUG_ALERT_TO') && defined('LIVE_WEATHER_STATION_WUG_ALERT_SUBJECT') && defined('LIVE_WEATHER_STATION_WUG_ALERT_MESSAGE')) {
        if (function_exists('wp_mail')) {
            wp_mail(LIVE_WEATHER_STATION_WUG_ALERT_TO, LIVE_WEATHER_STATION_WUG_ALERT_SUBJECT, LIVE_WEATHER_STATION_WUG_ALERT_MESSAGE);
        }
    }
}

/**
 * Sanitize a free-text value coming from a remote source (vendor API, feed) before storing or displaying it.
 *
 * @param mixed $value The raw value.
 * @param int $max_length Optional. The max length in characters.
 * @return string The sanitized value.
 * @since 3.8.15
 */
function live_weather_station_clean_text($value, $max_length=100) {
    if (!is_scalar($value)) {
        return '';
    }
    return mb_substr(sanitize_text_field((string)$value), 0, $max_length);
}

/**
 * Sanitize a numeric value coming from a remote source (vendor API, feed) before storing it.
 *
 * @param mixed $value The raw value.
 * @param int|float|null $default Optional. The value returned if the raw value is not numeric (null and '' are returned unchanged).
 * @return int|float|string|null The numeric value.
 * @since 3.8.15
 */
function live_weather_station_clean_number($value, $default=null) {
    if ($value === null || $value === '') {
        return $value;
    }
    return is_numeric($value) ? $value + 0 : $default;
}

/**
 * Sanitize an URL coming from a remote source (vendor API, feed) before storing it.
 *
 * @param mixed $value The raw value.
 * @return string The sanitized URL, empty string if not acceptable.
 * @since 3.8.15
 */
function live_weather_station_clean_url($value) {
    if (!is_scalar($value)) {
        return '';
    }
    return esc_url_raw((string)$value, array('http', 'https'));
}

/**
 * Print the beginning of the script tag.
 *
 * @param string $jsInitId Optional. The uid of the init function.
 * @return string The output ready to print.
 * @since 3.7.0
 */
function live_weather_station_print_begin_script($jsInitId='') {
    $jsInitId = preg_replace('/[^A-Za-z0-9_]/', '', (string)$jsInitId);
    $result = '<script language="javascript" type="text/javascript">';
    if ((bool)get_option('live_weather_station_wait_for_dom', 1) && !is_admin()) {
        if ($jsInitId == '') {
            $result .= 'document.addEventListener("DOMContentLoaded", function(event) {';
        }
        else {
            $result .= 'if (document.readyState !== "loading") {lwsInitDeferred' . $jsInitId . '();} else {document.addEventListener("DOMContentLoaded", function () {lwsInitDeferred' . $jsInitId . '();});}';
            $result .= 'function lwsInitDeferred' . $jsInitId . '() {';
        }
    }
    return $result;
}

/**
 * Print the end of the script tag.
 *
 * @param string $jsInitId Optional. The uid of the init function.
 * @return string The output ready to print.
 * @since 3.7.0
 */
function live_weather_station_print_end_script($jsInitId='') {
    $jsInitId = preg_replace('/[^A-Za-z0-9_]/', '', (string)$jsInitId);
    $result = '';
    if ((bool)get_option('live_weather_station_wait_for_dom', 1) && !is_admin()) {
        if ($jsInitId == '') {
            $result .= '});';
        }
        else {
            $result .= '}';
        }
    }
    $result .= '</script>';
    return $result;
}

/**
 * Sanitize width.
 *
 * @param string $s The size element.
 * @param array $u Optional. The accepted units
 * @return string The sanitized size.
 * @since 3.7.0
 */
function live_weather_station_sanitize_width_height_field($s, $u=array('px')) {
    $s = trim(strtolower(sanitize_text_field($s)));
    switch ($s) {
        case 'auto':
        case 'initial':
        case 'inherit':
            $result = $s;
            break;
        default:
            $i = (int)$s;
            if ($i > 0 && $i < 2000) {
                $t = trim(strtolower(substr($s, strpos($s, (string)$i) + strlen((string)$i))));
                if (!in_array($t, $u)) {
                    $t = 'px';
                }
                $result = $i . $t;
            }
            else {
                $result = '100px';
            }
            break;
    }
    return $result;
}

/**
 * Sanitize width.
 *
 * @param string $w The width.
 * @return string The sanitized width.
 * @since 3.7.0
 */
function live_weather_station_sanitize_width_field($w) {
    return live_weather_station_sanitize_width_height_field($w, array('cm', 'mm', 'in', 'px', 'pt', 'pc', 'em', 'ex', 'ch', 'rem', 'vw', 'vh', 'vmin', 'vmax', '%'));
}

/**
 * Sanitize width.
 *
 * @param string $h The width.
 * @return string The sanitized width.
 * @since 3.7.0
 */
function live_weather_station_sanitize_height_field($h) {
    return live_weather_station_sanitize_width_height_field($h, array('cm', 'mm', 'in', 'px', 'pt', 'pc', 'em', 'ex', 'ch', 'rem', 'vw', 'vh', 'vmin', 'vmax'));
}


/**
 * Adapt phpinfo line.
 *
 * @param string $i The line.
 * @return string The adapted line.
 * @since 3.7.5
 */
function live_weather_station_phpinfo_line($i) {
    return ".phpinfodisplay " . preg_replace( '/,/', ',.phpinfodisplay ', $i);
}

/**
 * Simulate iconv function but without iconv support.
 *
 * @param string $string The string to convert.
 * @return string The converted string.
 * @since 3.7.5
 */
function live_weather_station_iconv($string) {
    $string = remove_accents($string);
    $string = str_replace('₂', '2', $string);
    $string = str_replace('₃', '3', $string);
    return $string;
}

/**
 * Compute a sunrise or sunset timestamp without the date_sunrise() / date_sunset() functions (deprecated in PHP 8.1).
 *
 * This is a port of the algorithm used by PHP (timelib, astro.c: Paul Schlyter's "sunriset", upper limb
 * correction, altitude = 90 - zenith, result truncated to the second). It returns exactly the same
 * timestamp as date_sunrise/date_sunset(..., SUNFUNCS_RET_TIMESTAMP, ...) and false when the sun never
 * rises or never sets on that day (polar day or night). The day is the one of $time in the default timezone.
 *
 * @param int $time The timestamp of any moment of the day.
 * @param float $lat Latitude in degrees (north positive).
 * @param float $lon Longitude in degrees (east positive).
 * @param float $zenith Zenith in degrees (for ex. 90 + 50/60 for sunrise/sunset, 96 civil, 102 nautical, 108 astronomical).
 * @param boolean $sunset Optional. True to get the sunset, false (default) to get the sunrise.
 * @return int|boolean The timestamp, or false.
 * @since 3.9.0
 */
function live_weather_station_sun_timestamp($time, $lat, $lon, $zenith, $sunset = false) {
    $lat = (float)$lat;
    $lon = (float)$lon;
    $altit = 90 - (float)$zenith;
    if (is_nan($lat) || is_infinite($lat) || is_nan($lon) || is_infinite($lon)) {
        return false;
    }
    $rad = M_PI / 180.0;
    $deg = 180.0 / M_PI;
    $rev = function ($x) {
        return $x - 360.0 * floor($x * (1.0 / 360.0));
    };
    $rev180 = function ($x) {
        return $x - 360.0 * floor($x * (1.0 / 360.0) + 0.5);
    };
    // 00:00 UTC of the current (local, default timezone) day.
    $utc = gmmktime(0, 0, 0, (int)date('n', (int)$time), (int)date('j', (int)$time), (int)date('Y', (int)$time));
    // Days since 2000 Jan 0.0 at 12h local mean solar time.
    $d = ($utc / 86400.0 + 2440587.5) - 2451545 + 2 - $lon / 360.0;
    // Sun position.
    $m = $rev(356.0470 + 0.9856002585 * $d);
    $w = 282.9404 + 4.70935E-5 * $d;
    $e = 0.016709 - 1.151E-9 * $d;
    $ea = $m + $e * $deg * sin($m * $rad) * (1.0 + $e * cos($m * $rad));
    $x = cos($ea * $rad) - $e;
    $y = sqrt(1.0 - $e * $e) * sin($ea * $rad);
    $r = sqrt($x * $x + $y * $y);
    $v = $deg * atan2($y, $x);
    $slon = $v + $w;
    if ($slon >= 360.0) {
        $slon -= 360.0;
    }
    $x = $r * cos($slon * $rad);
    $y = $r * sin($slon * $rad);
    $obl = 23.4393 - 3.563E-7 * $d;
    $z = $y * sin($obl * $rad);
    $y = $y * cos($obl * $rad);
    $ra = $deg * atan2($y, $x);
    $dec = $deg * atan2($z, sqrt($x * $x + $y * $y));
    // Local sidereal time, and time when the sun is at south (hours UT).
    $sid = $rev($rev((180.0 + 356.0470 + 282.9404) + (0.9856002585 + 4.70935E-5) * $d) + 180.0 + $lon);
    $tsouth = 12.0 - $rev180($sid - $ra) / 15.0;
    // Upper limb correction.
    $altit -= 0.2666 / $r;
    $den = cos($lat * $rad) * cos($dec * $rad);
    if ($den == 0) {
        return false;
    }
    $cost = (sin($altit * $rad) - sin($lat * $rad) * sin($dec * $rad)) / $den;
    if ($cost >= 1.0 || $cost <= -1.0) {
        return false; // Polar night or polar day.
    }
    $t = $deg * acos($cost) / 15.0;
    return (int)((($sunset ? $tsouth + $t : $tsouth - $t) * 3600) + $utc);
}


/**
 * Fake __() function for debugging / developing purpose.
 *
 * @since 3.6.1
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function live_weather_station__($text, $domain='default') {
    return $text;
}

/**
 * Fake __() function for debugging / developing purpose.
 *
 * @since 3.6.1
 *
 * @param string $single The text to be used if the number is singular.
 * @param string $plural The text to be used if the number is plural.
 * @param int    $number The number to compare against to use either the singular or plural form.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function live_weather_station_n($single, $plural, $number, $domain = 'default' ) {
    return _n($single, $plural, $number, $domain);
}

/**
 * Fake __() function for debugging / developing purpose.
 *
 * @since 3.7.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function live_weather_station_esc_html__($text, $domain='default') {
    return esc_html($text);
}

/**
 * Fake __() function for debugging / developing purpose.
 *
 * @since 3.7.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function live_weather_station_esc_html_e__($text, $domain='default') {
    echo esc_html($text);
}

/**
 * Set/update the value of a cache item.
 *
 * @param string $name  Cache name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
 * @param mixed $value Cache value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool False if value was not set and true if value was set.
 * @since 3.8.0
 */
function live_weather_station_meta_cache($name, $value, $expiration=0) {
    if (defined('LIVE_WEATHER_STATION_FILE_CACHE')) {
        if (LIVE_WEATHER_STATION_FILE_CACHE && ($expiration == 0 || $expiration >= 120)) {
            $cache_dir = WP_CONTENT_DIR . '/cache/live-weather-station/';
            if (!file_exists($cache_dir)) {
                try {
                    mkdir($cache_dir, 0755, true);
                }
                catch (\Exception $ex) {
                    return false;
                }
            }
            if (is_dir($cache_dir) && wp_is_writable($cache_dir)) {
                // Deny direct web access to the cache directory
                if (!file_exists($cache_dir . 'index.php')) {
                    @file_put_contents($cache_dir . 'index.php', "<?php\n// Silence is golden.\n");
                }
                if (!file_exists($cache_dir . '.htaccess')) {
                    @file_put_contents($cache_dir . '.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n");
                }
                $blog_id = get_current_blog_id();
                $cache_file = $cache_dir . sanitize_file_name($blog_id . '_' . $name);
                try {
                    file_put_contents($cache_file, serialize($value), LOCK_EX);
                }
                catch (\Exception $ex) {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            return set_transient($name, $value, $expiration);
        }
    }
    else {
        return set_transient($name, $value, $expiration);
    }
}

/**
 * Does a value contain an object (recursively)?
 *
 * @since 3.8.0
 */
function live_weather_station_value_has_object($value) {
    if (is_object($value)) {
        return true;
    }
    if (is_array($value)) {
        foreach ($value as $item) {
            if (live_weather_station_value_has_object($item)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Read the value of a cache item.
 *
 * @param string $name  Cache name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool|mixed False if value was not set and value if it was set.
 * @since 3.8.0
 */
function live_weather_station_meta_uncache($name, $expiration=0) {
    if (defined('LIVE_WEATHER_STATION_FILE_CACHE')) {
        if (LIVE_WEATHER_STATION_FILE_CACHE && ($expiration == 0 || $expiration >= 120)) {
            $cache_dir = WP_CONTENT_DIR . '/cache/live-weather-station/';
            $blog_id = get_current_blog_id();
            $cache_file = $cache_dir . sanitize_file_name($blog_id . '_' . $name);
            if (file_exists($cache_file)) {
                try {
                    $t = filemtime($cache_file);
                    if (time() - $t > $expiration) {
                        unlink($cache_file);
                        return false;
                    }
                    else {
                        // Decision: the experimental file cache (LIVE_WEATHER_STATION_FILE_CACHE, off by default) is expected to hold arrays/scalars only.
                        // Objects are never instantiated when reading (security): a cached object is treated as a cache miss.
                        $value = unserialize((string)file_get_contents($cache_file), array('allowed_classes' => false));
                        if (live_weather_station_value_has_object($value)) {
                            @unlink($cache_file);
                            return false;
                        }
                        return $value;
                    }
                }
                catch (\Exception $ex) {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            return get_transient($name);
        }
    }
    else {
        return get_transient($name);
    }
}

/**
 * Remove a cache item.
 *
 * @param string $name  Cache name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
 * @return bool True if it was removed, false otherwise.
 * @since 3.8.0
 */
function live_weather_station_meta_rmcache($name) {
    if (defined('LIVE_WEATHER_STATION_FILE_CACHE')) {
        if (LIVE_WEATHER_STATION_FILE_CACHE) {
            $cache_dir = WP_CONTENT_DIR . '/cache/live-weather-station/';
            $blog_id = get_current_blog_id();
            $cache_file = $cache_dir . sanitize_file_name($blog_id . '_' . $name);
            if (file_exists($cache_file)) {
                try {
                    unlink($cache_file);
                }
                catch (\Exception $ex) {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            return delete_transient($name);
        }
    }
    else {
        return delete_transient($name);
    }
}

/**
 * Flush the cache.
 *
 * @param string $pref Prefix name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return int Count of removed items.
 * @since 3.8.0
 */
function live_weather_station_meta_flcache($pref, $expiration=0) {
    $result = 0;
    if (defined('LIVE_WEATHER_STATION_FILE_CACHE')) {
        if (LIVE_WEATHER_STATION_FILE_CACHE) {
            $cache_dir = WP_CONTENT_DIR . '/cache/live-weather-station/';
            $blog_id = get_current_blog_id();
            $cache_file = $cache_dir . sanitize_file_name($blog_id . '_' . $pref . '*');

            // TODO : implement flush
        }
    }
    return $result;
}

/**
 * Cheap per-IP rate limit for public AJAX endpoints. Sends an HTTP 429 JSON response and stops when exceeded.
 *
 * Defaults: 120 requests per 60 seconds, per client IP and per endpoint. A page with many live controls makes
 * roughly 20-30 calls per endpoint, so this is comfortable for one visitor.
 * Exempt: logged-in users able to manage options or edit posts (administrators, editors, authors, contributors).
 *
 * IMPORTANT for sites behind a reverse proxy / CDN / load balancer: REMOTE_ADDR is then the proxy address, so all
 * visitors share the same counter. Supply the real client IP with the 'live_weather_station_public_rate_limit_ip' filter
 * (e.g. return $_SERVER['HTTP_CF_CONNECTING_IP'] when you trust that header), or raise/disable the limit.
 *
 * Filters:
 * - 'live_weather_station_public_rate_limit' (int $limit, string $action): max requests per window; 0 disables the limit.
 * - 'live_weather_station_public_rate_window' (int $seconds, string $action): window length, default 60.
 * - 'live_weather_station_public_rate_limit_ip' (string $ip, string $action): client IP used as the key; default REMOTE_ADDR
 *   (forwarded headers are never trusted by default since they can be forged).
 *
 * Storage: object cache (atomic increment) when a persistent one is in use; otherwise a transient which is only
 * written every few hits (coarse steps), so the options table is not hit on every request.
 *
 * @param string $action The endpoint identifier.
 * @since 3.8.15
 */
function live_weather_station_public_rate_limit($action) {
    if (current_user_can(live_weather_station_manage_capability()) || current_user_can('edit_posts')) {
        return;
    }
    $limit = (int)apply_filters('live_weather_station_public_rate_limit', 120, $action);
    if ($limit <= 0) {
        return;
    }
    $window = max(1, (int)apply_filters('live_weather_station_public_rate_window', 60, $action));
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
    $ip = (string)apply_filters('live_weather_station_public_rate_limit_ip', $ip, $action);
    $key = 'lws_rl_' . md5($ip . '|' . $action . '|' . (int)floor(time() / $window));
    if (function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache()) {
        wp_cache_add($key, 0, 'lws_rl', $window * 2);
        $count = wp_cache_incr($key, 1, 'lws_rl');
        $count = ($count === false) ? 1 : (int)$count;
    }
    else {
        // Transient fallback: probabilistic coarse counter. One read per hit, and a write (of count + $step) on
        // average every $step hits, so the options table is not written on every request. Precision ~ +/- $step.
        $step = max(1, (int)floor($limit / 10));
        $count = (int)get_transient($key);
        if ($count === 0 || mt_rand(1, $step) === 1) {
            set_transient($key, $count + ($count === 0 ? 1 : $step), $window * 2);
        }
    }
    if ($count > $limit) {
        status_header(429);
        header('Retry-After: ' . $window);
        header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        exit (wp_json_encode(array('error' => 'too_many_requests')));
    }
}
