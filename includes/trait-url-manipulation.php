<?php

/**
 * URL & rewrite manipulation functionalities for Live Weather Station plugin
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

trait Url_Manipulation {

    /**
     * Add tags and url rewrite for path /station/[ID]/[format]
     *
     * @since    3.0.0
     */
    public static function add_url_station_id_format() {
        //add_rewrite_tag('%type%', '([^/]+)', 'type=');
        //add_rewrite_tag('%station%', '([^/]+)', 'station=');
        add_rewrite_rule('^get-weather/([^/]*)/([^/]*)/?', LWS_RELATIVE_PLUGIN_URL.'generator.php?type=$matches[2]&station=$matches[1]','top');
    }

    /**
     * Add query vars for path /station/[ID]/[format]
     *
     * @since    3.0.0
     
    public static function add_query_vars_station_id_format($vars) {
        $vars[] = 'type';
        $vars[] = 'station';
        return $vars;
    }*/

    /**
     * Create rewriterules.
     *
     * @since    3.0.0
     */
    public static function init_rewrite_rules() {
        add_action('init', array(get_called_class(), 'add_url_station_id_format'));
        //add_filter('query_vars', array(get_called_class(), 'add_query_vars_station_id_format'));
    }


}