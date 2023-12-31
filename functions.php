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
function lws_get_admin_page_url($page='lws-dashboard', $action=null, $tab=null, $service=null, $dashboard=false, $id=null, $xid=null) {
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
function lws_re_get_admin_page_url($params) {
    $set = array('page', 'tab', 'action', 'service', 'id');
    $args = array();
    foreach ($set as $arg) {
        if (isset($_POST[$arg])) {
            $args[$arg] = $_POST[$arg];
        }
        if (isset($_GET[$arg])) {
            $args[$arg] = $_GET[$arg];
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
function lws_get_display_locale($user_id = 0) {
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
function lws_lcfirst($str) {
    if (strpos(strtolower(lws_get_display_locale()), 'de') === 0) {
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
function lws_object_to_array($obj) {
    $arr = array();
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? lws_object_to_array($val) : $val;
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
function lws_array_orderby(){
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
function lws_array_compare_1($a, $b){
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
function lws_array_super_unique($array, $key){
    $temp_array = array();
    foreach ($array as &$v) {
        if (!isset($temp_array[$v[$key]]))
            $temp_array[$v[$key]] =& $v;
    }
    $array = array_values($temp_array);
    return $array;
}

/**
 * Registers (but don't enqueues) a style asset of the plugin.
 *
 * Regarding user's option, asset is ready to enqueue from local plugin dir or from CDN (jsDelivr)
 *
 * @since 3.5.0
 */
function lws_register_style($handle, $source, $file, $deps = array(), $cdn_available=true) {
    if ((bool)get_option('live_weather_station_use_cdn') && $cdn_available) {
        if ($source == LWS_ADMIN_URL) {
            $file = 'https://cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/admin/' . $file;
        }
        else {
            $file = 'https://cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/public/' . $file;
        }
        wp_register_style($handle, $file, $deps, null);
    }
    else {
        wp_register_style($handle, $source . $file, $deps, LWS_VERSION);
    }
}

/**
 * Registers (but don't enqueues) a script asset of the plugin.
 *
 * Regarding user's option, asset is ready to enqueue from local plugin dir or from CDN (jsDelivr)
 *
 * @since 3.5.0
 */
function lws_register_script($handle, $source, $file, $deps = array(), $cdn_available=true) {
    if ((bool)get_option('live_weather_station_use_cdn') && $cdn_available) {
        if ($source == LWS_ADMIN_URL) {
            $file = 'https://cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/admin/' . $file;
        }
        else {
            $file = 'https://cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/public/' . $file;
        }
        wp_register_script($handle, $file, $deps, null, (bool)get_option('live_weather_station_footer_scripts', false));
    }
    else {
        wp_register_script($handle, $source . $file, $deps, LWS_VERSION, (bool)get_option('live_weather_station_footer_scripts', false));
    }
}


/**
 * Enqueues the right scripts and/or stylesheets regarding the selected version of Font Awesome
 *
 * @since 3.5.3
 */
function lws_font_awesome($all=false) {
    $mode = get_option('live_weather_station_fa_mode');
    if (is_admin()) {
        $mode = 1;
    }
    switch ($mode) {
        case 0:                                             // Font Awesome 4 outputted by Weather Station
            wp_enqueue_style('lws-font-awesome-4');
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'fa');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fa');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fa');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', false);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', false);
            }
            break;
        case 1:                                             // Font Awesome 5 outputted by Weather Station as CSS
            wp_enqueue_style('lws-font-awesome-5');
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'far');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fab');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fas');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', true);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', false);
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
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'far');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fab');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fas');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', true);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', true);
            }
            break;
        case 3:                                             // Font Awesome 4 outputted by theme or other plugin
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'fa');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fa');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fa');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', false);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', false);
            }
            break;
        case 4:                                             // Font Awesome 5 outputted by theme or other plugin as CSS
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'far');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fab');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fas');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', true);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', false);
            }
            break;
        case 5:                                             // Font Awesome 5 outputted by theme or other plugin as JS+SVG
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'far');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fab');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fas');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', true);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', true);
            }
            break;
        case 6:                                             // Font Awesome official plugin
            wp_dequeue_style('lws-font-awesome-4');
            wp_dequeue_style('lws-font-awesome-5');
            wp_dequeue_script('lws-fa-all');
            wp_dequeue_script('lws-fa-brands');
            wp_dequeue_script('lws-fa-regular');
            wp_dequeue_script('lws-fa-solid');
            if (!defined('LWS_FAR')) {
                define('LWS_FAR', 'far');
            }
            if (!defined('LWS_FAB')) {
                define('LWS_FAB', 'fab');
            }
            if (!defined('LWS_FAS')) {
                define('LWS_FAS', 'fas');
            }
            if (!defined('LWS_FA5')) {
                define('LWS_FA5', true);
            }
            if (!defined('LWS_FA_SVG')) {
                define('LWS_FA_SVG', true);
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

function is_lws_active() {
    return in_array( 'live-weather-station/live-weather-station.php', (array) get_option( 'active_plugins', array() ) ) || is_lws_active_for_network();
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
function is_lws_active_for_network() {
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
function lws_get_locale_name($locale, $in_locale = null) {
    $result = $locale;
    if (LWS_I18N_LOADED) {
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
function lws_get_region_name($locale, $in_locale = null) {
    $result = $locale;
    if (LWS_I18N_LOADED) {
        $result = \Locale::getDisplayRegion($locale, $in_locale);
    }
    return $result;
}

/**
 * Try to send an alert email.
 *
 * @since 3.7.0
 */
function lws_send_alert_message() {
    if (defined('LWS_WUG_ALERT_TO') && defined('LWS_WUG_ALERT_SUBJECT') && defined('LWS_WUG_ALERT_MESSAGE')) {
        if (function_exists('wp_mail')) {
            wp_mail(LWS_WUG_ALERT_TO, LWS_WUG_ALERT_SUBJECT, LWS_WUG_ALERT_MESSAGE);
        }
    }
}

/**
 * Print the beginning of the script tag.
 *
 * @param string $jsInitId Optional. The uid of the init function.
 * @return string The output ready to print.
 * @since 3.7.0
 */
function lws_print_begin_script($jsInitId='') {
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
function lws_print_end_script($jsInitId='') {
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
function lws_sanitize_width_height_field($s, $u=array('px')) {
    $s = trim(strtolower(sanitize_text_field($s)));
    switch ($s) {
        case 'auto':
        case 'initial':
        case 'inherit':
            $result = $s;
            break;
        default:
            $i = (int)$s;
            if ($i != 0 && $i < 2000) {
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
function lws_sanitize_width_field($w) {
    return lws_sanitize_width_height_field($w, array('cm', 'mm', 'in', 'px', 'pt', 'pc', 'em', 'ex', 'ch', 'rem', 'vw', 'vh', 'vmin', 'vmax', '%'));
}

/**
 * Sanitize width.
 *
 * @param string $h The width.
 * @return string The sanitized width.
 * @since 3.7.0
 */
function lws_sanitize_height_field($h) {
    return lws_sanitize_width_height_field($h, array('cm', 'mm', 'in', 'px', 'pt', 'pc', 'em', 'ex', 'ch', 'rem', 'vw', 'vh', 'vmin', 'vmax'));
}


/**
 * Adapt phpinfo line.
 *
 * @param string $i The line.
 * @return string The adapted line.
 * @since 3.7.5
 */
function lws_phpinfo_line($i) {
    return ".phpinfodisplay " . preg_replace( '/,/', ',.phpinfodisplay ', $i);
}

/**
 * Simulate iconv function but without iconv support.
 *
 * @param string $string The string to convert.
 * @return string The converted string.
 * @since 3.7.5
 */
function lws_iconv($string) {
    $string = remove_accents($string);
    $string = str_replace('₂', '2', $string);
    $string = str_replace('₃', '3', $string);
    return $string;
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
function lws__($text, $domain='default') {
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
function lws_n($single, $plural, $number, $domain = 'default' ) {
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
function esc_html_lws__($text, $domain='default') {
    return $text;
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
function esc_html_e_lws__($text, $domain='default') {
    echo $text;
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
function lws_meta_cache($name, $value, $expiration=0) {
    if (defined('LWS_FILE_CACHE')) {
        if (LWS_FILE_CACHE && ($expiration == 0 || $expiration >= 120)) {
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
                $blog_id = get_current_blog_id();
                $cache_file = $cache_dir . sanitize_file_name($blog_id . '_' . $name);
                try {
                    file_put_contents($cache_file, serialize($value));
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
 * Read the value of a cache item.
 *
 * @param string $name  Cache name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool|mixed False if value was not set and value if it was set.
 * @since 3.8.0
 */
function lws_meta_uncache($name, $expiration=0) {
    if (defined('LWS_FILE_CACHE')) {
        if (LWS_FILE_CACHE && ($expiration == 0 || $expiration >= 120)) {
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
                        return unserialize(file_get_contents($cache_file));
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
function lws_meta_rmcache($name) {
    if (defined('LWS_FILE_CACHE')) {
        if (LWS_FILE_CACHE) {
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
function lws_meta_flcache($pref, $expiration=0) {
    $result = 0;
    if (defined('LWS_FILE_CACHE')) {
        if (LWS_FILE_CACHE) {
            $cache_dir = WP_CONTENT_DIR . '/cache/live-weather-station/';
            $blog_id = get_current_blog_id();
            $cache_file = $cache_dir . sanitize_file_name($blog_id . '_' . $pref . '*');

            // TODO : implement flush
        }
    }
    return $result;
}