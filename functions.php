<?php
/**
 * Utilities functions.
 *
 * @package Bootstrap
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
function lws_get_admin_page_url($page='lws-dashboard', $action=null, $tab=null, $service=null, $dashboard=false, $id=null) {
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
 * Get the displaylanguage id.
 *
 * @return string The id of the language.
 *
 * @since 3.3.0
 */
function lws_get_display_language_id()
{
    $lang = 'en';
    $extra_language = array('fr');
    $l = strtolower(lws_get_display_locale());
    foreach ($extra_language as $extra) {
        if (strpos($l, $extra) === 0) {
            $lang = $extra;
            break;
        }
    }
    return $lang;
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
    switch (get_option('live_weather_station_fa_mode')) {
        case 1:
            if ($all) {
                wp_enqueue_script('lws-fa-all');
            }
            else {
                //wp_enqueue_script('lws-fa-brands');
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
            break;
        case 2:
            wp_dequeue_style('lws-font-awesome');
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
            break;
        case 3:
            if ($all) {
                wp_dequeue_script('lws-fa-all');
            }
            else {
                //wp_dequeue_script('lws-fa-brands');
                wp_dequeue_script('lws-fa-regular');
                wp_dequeue_script('lws-fa-solid');
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
            break;
        default:
            wp_enqueue_style('lws-font-awesome');
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