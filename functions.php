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
function lws_register_style($handle, $source, $file, $deps = array()) {
    if ((bool)get_option('live_weather_station_use_cdn')) {
        if ($source == LWS_ADMIN_URL) {
            $file = '//cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/admin/' . $file;
        }
        else {
            $file = '//cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/public/' . $file;
        }
        wp_register_style($handle, $file, $deps);
    }
    else {
        wp_register_style($handle, $source . $file, $deps, (bool)get_option('live_weather_station_use_cdn') ? false : LWS_VERSION);
    }
}

/**
 * Registers (but don't enqueues) a script asset of the plugin.
 *
 * Regarding user's option, asset is ready to enqueue from local plugin dir or from CDN (jsDelivr)
 *
 * @since 3.5.0
 */
function lws_register_script($handle, $source, $file, $deps = array()) {
    if ((bool)get_option('live_weather_station_use_cdn')) {
        if ($source == LWS_ADMIN_URL) {
            $file = '//cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/admin/' . $file;
        }
        else {
            $file = '//cdn.jsdelivr.net/wp/' . LWS_PLUGIN_SLUG . '/tags/' . LWS_VERSION . '/public/' . $file;
        }
        wp_register_script($handle, $file, $deps, false, (bool)get_option('live_weather_station_footer_scripts', false));
    }
    else {
        wp_register_script($handle, $source . $file, $deps, (bool)get_option('live_weather_station_use_cdn') ? false : LWS_VERSION, (bool)get_option('live_weather_station_footer_scripts', false));
    }
}