<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      1.0.0
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');
require_once(LWS_INCLUDES_DIR.'trait-options-manipulation.php');
require_once(LWS_INCLUDES_DIR.'trait-javascript-array.php');
require_once(LWS_INCLUDES_DIR.'class-netatmo-collector.php');
require_once(LWS_INCLUDES_DIR.'class-owm-collector.php');
require_once(LWS_INCLUDES_DIR.'class-owm-current-collector.php');
require_once(LWS_INCLUDES_DIR.'class-owm-pollution-collector.php');
require_once(LWS_INCLUDES_DIR.'class-weather-computer.php');
require_once(LWS_INCLUDES_DIR.'class-ephemeris-computer.php');
require_once(LWS_INCLUDES_DIR.'class-owm-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-pws-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-wow-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-wug-pusher.php');

class Live_Weather_Station_Admin {

    use Options_Manipulation, Javascript_Array, Datas_Output {
        Datas_Output::get_module_type insteadof Javascript_Array;
        Datas_Output::get_measurement_type insteadof Javascript_Array;
    }

	private $Live_Weather_Station;
	private $version;
    private $netatmo_error = '';
    private $netatmo_warning = '';
    private $owm_error = '';
    private $owm_warning = '';
    private $pushers = array('owm', 'pws', 'wow', 'wug');

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $Live_Weather_Station       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Enqueues the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->Live_Weather_Station, LWS_ADMIN_URL.'css/live-weather-station-admin.min.css', array(), $this->version, 'all');
        wp_enqueue_style('live-weather-station-public.css', LWS_PUBLIC_URL.'css/live-weather-station-public.min.css', array(), $this->version, 'all');
        wp_enqueue_style('font-awesome.css', LWS_PUBLIC_URL.'css/font-awesome.min.css', array(), '4.6.3', 'all');
        wp_enqueue_style('thickbox');
    }

    /**
     * Enqueues the javascripts for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->Live_Weather_Station, LWS_ADMIN_URL.'js/live-weather-station-admin.min.js', array('jquery', 'postbox'), $this->version, false);
        wp_enqueue_script('clipboard.js', LWS_ADMIN_URL.'js/clipboard.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('lws-lcd.js', LWS_PUBLIC_URL.'js/lws-lcd.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('raphael.js', LWS_PUBLIC_URL.'js/raphael.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('justgage.js', LWS_PUBLIC_URL.'js/justgage.min.js', array('raphael.js'), $this->version, false);
        wp_enqueue_script('tween.js', LWS_PUBLIC_URL.'js/tween.min.js', array(), $this->version, true);
        wp_enqueue_script('steelseries.js', LWS_PUBLIC_URL.'js/steelseries.min.js', array('tween.js'), $this->version, true);
        wp_enqueue_script('thickbox');
    }
    
    

    // -----------------------------------------------------------------
    // -- BEGIN SPECIFIC 3.X
    // -----------------------------------------------------------------

    /**
     * Returns a base64 svg resource for use in the main admin menu.
     *
     * @return  string  The svg resource as a base64.
     * @since    3.0.0
     */
    private function get_base64_svg() {
        $source = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 1500 1500">';
        $source .= '<path style="fill:#000" d="M719.726 13.792c-61.976 10.239-110.896 35.253-153.82 78.193-36.114 36.114-56.575 70.805-71.651 122.543l-7.39 25.595-1.707 204.995-1.424 205.292-25.031 21.604c-67.11 58.86-116.859 138.463-139.888 224.060-59.143 220.053 58.86 454.635 271.244 538.229 256.466 101.221 541.375-45.209 608.751-312.773 41.517-163.775-14.213-339.779-142.455-450.094l-23.887-20.758-1.424-203.867-1.707-203.867-7.39-27.583c-12.507-45.209-33.842-83.877-65.404-117.706-49.765-53.162-115.717-82.733-189.935-85.018-17.893-0.549-38.934 0.015-46.885 1.157zM796.778 201.724c17.626 8.533 34.972 26.159 44.068 44.068l6.825 14.213 0.861 247.652 0.564 247.652 18.771 7.969c81.889 34.689 143.3 113.446 158.938 203.007 15.356 87.29-12.507 174.015-76.487 237.977-76.77 76.487-184.815 100.657-286.882 63.697-71.369-25.878-135.346-89.842-161.209-161.209-31.84-88.15-18.19-182.546 37.241-257.31 26.442-35.536 68.802-69.1 108.892-86.146l18.771-7.969v-242.25c0-196.181 0.847-244.521 3.977-255.041 7.969-27.302 32.418-52.036 59.706-60.848 17.345-5.684 48.905-3.413 65.968 4.54z"></path>';
        $source .= '<path style="fill:#000" d="M736.493 923.641c-13.651 3.413-37.821 17.626-45.492 26.723-15.921 18.771-22.182 36.114-22.182 60.284-0.283 20.178 0.847 25.017 9.095 41.796 15.639 32.123 43.504 48.905 80.181 49.188 17.063 0 24.453-1.424 37.241-7.39 33.265-15.639 52.315-45.492 52.315-82.17-0.283-36.397-17.345-64.542-48.341-79.898-19.318-9.393-45.478-13.087-62.821-8.533z"></path>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns the manage_options cap.
     *
     * @return mixed|void
     */
    private function get_manage_options_cap() {
        return apply_filters('lws_manage_options_capability', 'manage_options');
    }
    
    /**
     * Set Weather Station admin menu and submenus in the main dashboard menu.
     *
     * @since    3.0.0
     */
    public function lws_admin_menu() {
        $icon_svg = $this->get_base64_svg();
        $manage_options_cap = $this->get_manage_options_cap();
        add_menu_page('Weather Station - ' . __('Dashboard', 'live-weather-station'), 'Weather Station', $manage_options_cap, 'lws-dashboard', array($this, 'lws_load_admin_page'), $icon_svg, '99.001357');
        add_submenu_page('lws-dashboard', 'Weather Station - ' . __('Dashboard', 'live-weather-station'), __('Dashboard', 'live-weather-station'), $manage_options_cap, 'lws-dashboard', array($this, 'lws_load_admin_page'));
        add_submenu_page('lws-dashboard', 'Weather Station - ' . __('Settings', 'live-weather-station'), __('Settings', 'live-weather-station'), $manage_options_cap, 'lws-settings', array($this, 'lws_load_admin_page'));
        add_submenu_page('lws-dashboard', 'Weather Station - ' . __('Stations', 'live-weather-station'), __('Stations', 'live-weather-station'), $manage_options_cap, 'lws-stations', array($this, 'lws_load_admin_page'));
        add_submenu_page('lws-dashboard', 'Weather Station - ' . __('Events log', 'live-weather-station'), __('Log', 'live-weather-station'), $manage_options_cap, 'lws-events', array($this, 'lws_load_admin_page'));
    }

    /**
     * Load the named partial with its values.
     *
     * @param   string  $name   The name of the partial to load.
     * @param   array   $args   The values to pass to the view.
     * @since    3.0.0
     * @access   protected
     */
    protected function lws_view_admin_page($name, array $args = array()) {
        foreach ($args as $key => $val) {
            $$key = $val;
        }
        wp_dequeue_script('media-upload');
        include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-'.$name.'.php');
    }

    /**
     * Load the right admin page.
     *
     * @since    3.0.0
     */
    function lws_load_admin_page() {
        $page = filter_input(INPUT_GET, 'page');
        switch ($page) {
            case 'lws-events':
                $log_entry = filter_input(INPUT_GET, 'log-entry');
                if (isset($log_entry) && $log_entry != 0) {
                    $view = 'view-log';
                    $log_array = $this->get_log_detail($log_entry);
                    if (is_array($log_array)) {
                        $log = $log_array[0];
                        $log['displayed_timestamp'] = $this->get_date_from_mysql_utc($log['timestamp'], '', 'Y-m-d H:i:s') ;
                        $log['displayed_timestamp'] .= ' (' . $this->get_time_diff_from_mysql_utc($log['timestamp']) .')';
                    }
                    else {
                        $log = array();
                    }
                }
                else {
                    $view = 'list-logs';
                    $log = array();
                }

                $args = compact('log');
                break;
            default:
                $view = 'view-dashboard';
                $args = array();
                break;
        }
        $this->lws_view_admin_page($view, $args);

        /*$args = array( 'page' => 'lws-config' );
        if ( $page == 'disconnect_netatmo' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'config', 'action' => 'disconnect-netatmo');
        }
        if ( $page == 'disconnect_owm' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'config', 'action' => 'disconnect-owm');
        }
        if ( $page == 'manage_owm' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'manage-owm', 'action' => 'manage-owm');
        }
        if ( $page == 'manage_netatmo' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'manage-netatmo', 'action' => 'manage-netatmo');
        }
        if ( $page == 'list_logs' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'list-logs', 'action' => 'list-logs');
        }
        $url = add_query_arg( $args, admin_url( 'options-general.php' )  );
        return $url;*/

        switch ($view) {
            /*case 'config':
                $temperature = $this->get_temperature_unit_name_array();
                $pressure = $this->get_pressure_unit_name_array();
                $wind = $this->get_wind_speed_unit_name_array();
                $rain = $this->get_altitude_unit_name_array();
                $altitude = $this->get_altitude_unit_name_array();
                $distance = $this->get_distance_unit_name_array();
                $viewing_options = $this->get_viewing_options_array();
                $mode_options = $this->get_mode_options_array();
                $obsolescence = $this->get_obsolescence_array();
                $windsemantic = $this->get_windsemantic_array();
                $co = $this->get_co_unit_name_array();
                $minmax = $this->get_minmax_array();
                $netatmo = new Netatmo_Collector();
                $owm = new OWM_Current_Collector();
                $pollution = new OWM_Pollution_Collector();
                $weather = new Weather_Computer();
                $ephemeris = new Ephemeris_Computer();
                $datas = $this->merge_data($netatmo->get_datas(true), $owm->get_datas(), $pollution->get_datas(), $weather->compute(), $ephemeris->compute());
                if ($this->netatmo_error == '') {
                    $error = $netatmo->last_netatmo_error;
                }
                else {
                    $error = $this->netatmo_error;
                }
                if ($this->netatmo_warning == '') {
                    $warning = $netatmo->last_netatmo_warning;
                }
                else {
                    $warning = $this->netatmo_warning;
                }
                if ($this->owm_error == '') {
                    $oerror = $owm->last_owm_error;
                }
                else {
                    $oerror = $this->owm_error;
                }
                if ($this->owm_warning == '') {
                    $owarning = $owm->last_owm_warning;
                }
                else {
                    $owarning = $this->owm_warning;
                }
                if (count($datas) > 0) {
                    $js_array_textual = $this->get_js_array($datas, true, false, false, true);
                    $js_array_icon = $this->get_js_array($datas, true, false, false, true);
                    $js_array_lcd = $this->get_js_array($datas, false, true, true);
                    $js_array_justgage = $this->get_js_array($datas, false, false, true, true, true);
                    $js_array_steelmeter = $this->get_js_array($datas, false, false, true, true, false);
                }
                $js_array_lcd_design = $this->get_lcd_design_js_array();
                $js_array_lcd_size = $this->get_size_js_array();
                $js_array_lcd_speed = $this->get_lcd_speed_js_array();

                $js_array_justgage_design = $this->get_justgage_design_js_array();
                $js_array_justgage_color = $this->get_justgage_color_js_array();
                $js_array_justgage_pointer = $this->get_justgage_pointer_js_array();
                $js_array_justgage_title = $this->get_justgage_title_js_array();
                $js_array_justgage_unit = $this->get_justgage_unit_js_array();
                $js_array_justgage_size = $this->get_size_js_array(true);
                $js_array_justgage_background = $this->get_justgage_background_js_array();

                $js_array_steelmeter_design = $this->get_steelmeter_design_js_array();
                $js_array_steelmeter_frame = $this->get_steelmeter_frame_js_array();
                $js_array_steelmeter_background = $this->get_steelmeter_background_js_array();
                $js_array_steelmeter_orientation = $this->get_steelmeter_orientation_js_array();
                $js_array_steelmeter_glass = $this->get_steelmeter_glass_js_array();
                $js_array_steelmeter_pointer_type = $this->get_steelmeter_pointer_type_js_array();
                $js_array_steelmeter_pointer_color = $this->get_steelmeter_pointer_color_js_array();
                $js_array_steelmeter_knob = $this->get_steelmeter_knob_js_array();
                $js_array_steelmeter_lcd_color = $this->get_steelmeter_lcd_design_js_array();
                $js_array_steelmeter_led_color = $this->get_steelmeter_led_color_js_array();
                $js_array_steelmeter_minmax = $this->get_steelmeter_minmax_js_array();
                $js_array_steelmeter_index_color = $this->get_steelmeter_index_color_js_array();
                $js_array_steelmeter_index_style = $this->get_steelmeter_index_style_js_array();
                $js_array_steelmeter_size = $this->get_size_js_array(false, true, false);

                $status = $this->get_status($oerror != '');
                $args = compact( 'error', 'warning', 'oerror', 'owarning', 'status', 'temperature', 'pressure', 'wind',
                    'rain', 'altitude', 'distance', 'mode_options', 'viewing_options', 'obsolescence', 'minmax', 'datas',
                    'windsemantic', 'co',

                    'js_array_textual', 'js_array_icon',

                    'js_array_lcd', 'js_array_lcd_design', 'js_array_lcd_size', 'js_array_lcd_speed',

                    'js_array_justgage', 'js_array_justgage_design', 'js_array_justgage_size', 'js_array_justgage_color',
                    'js_array_justgage_pointer', 'js_array_justgage_title', 'js_array_justgage_unit', 'js_array_justgage_background',

                    'js_array_steelmeter', 'js_array_steelmeter_design', 'js_array_steelmeter_size', 'js_array_steelmeter_frame',
                    'js_array_steelmeter_background', 'js_array_steelmeter_orientation', 'js_array_steelmeter_glass',
                    'js_array_steelmeter_pointer_type', 'js_array_steelmeter_pointer_color', 'js_array_steelmeter_knob',
                    'js_array_steelmeter_lcd_color', 'js_array_steelmeter_led_color', 'js_array_steelmeter_minmax',
                    'js_array_steelmeter_index_style', 'js_array_steelmeter_index_color');
                break;
            case 'add-edit-owm':
                if (count($station) == 0) {
                    $station = $this->get_owm_station($owm_station_id);
                }
                $countries = $this->get_country_names();
                $timezones = $this->get_timezones_js_array();
                $error = $owm_station_error;
                $args = compact('station', 'countries', 'timezones', 'error');
                break;
            case 'edit-netatmo':
                if (count($station) == 0) {
                    $station = $this->get_station_informations($netatmo_station_id);
                }
                $error = $netatmo_station_error;
                $args = compact('station', 'error');
                break;
            case 'delete-owm':
                $stations = $this->get_owm_stations($owm_station_delete);
                if (count($stations) > 0) {
                    foreach ($stations as &$item) {
                        $item['country'] = $this->get_country_name($item['loc_country_code']);
                    }
                }
                $args = compact('stations');
                break;*/

        }
    }


    // -----------------------------------------------------------------
    // -- END SPECIFIC 3.X
    // -----------------------------------------------------------------
    
    
    /**
     * Purge datas, reset options and disconnect current account.
     *
     * @since    2.0.0
     * @access   public
     * @static
     */
    public static function delete_datas() {
        self::truncate_data_table();
    }

    /**
     * Set a link to the config page in the admin menu.
     *
     * @since    1.0.0
     * @access   public
     */
    public function admin_menu() {
        add_options_page( 'Live Weather Station', 'Live Weather Station', 'manage_options', 'lws-config', array( $this, 'display_configuration_page' ) );
    }

    /**
     * Set a link to the config page in the plugin management page.
     *
     * @since    1.0.0
     * @access   public
     */
    public function admin_settings_link($links) {
        $settings_link = '<a href="'.esc_url( $this->get_page_url() ).'">'.__('Settings').'</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Get the proper admin page url.
     *
     * @since    1.0.0
     */
    protected function get_page_url($page = 'lws-config') {
        $args = array( 'page' => 'lws-config' );
        if ( $page == 'disconnect_netatmo' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'config', 'action' => 'disconnect-netatmo');
        }
        if ( $page == 'disconnect_owm' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'config', 'action' => 'disconnect-owm');
        }
        if ( $page == 'manage_owm' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'manage-owm', 'action' => 'manage-owm');
        }
        if ( $page == 'manage_netatmo' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'manage-netatmo', 'action' => 'manage-netatmo');
        }
        if ( $page == 'list_logs' ) {
            $args = array( 'page' => 'lws-config', 'view' => 'list-logs', 'action' => 'list-logs');
        }
        $url = add_query_arg( $args, admin_url( 'options-general.php' )  );
        return $url;
    }

    /**
     * Load the named partial with its values.
     *
     * @param   string  $name   The name of the partial to load.
     * @param   array   $args   The values to pass to the view.
     * @since    1.0.0
     * @access   protected
     */
    protected function view_page( $name, array $args = array() ) {
        foreach ($args as $key => $val) {
            $$key = $val;
        }
        wp_dequeue_script('media-upload');
        $file = LWS_ADMIN_DIR.'partials/live-weather-station-admin-'.$name.'.php';
        include($file);
    }

    /**
     * Get status and infos.
     *
     * @param   boolean $error Optional. Last OpenWeatherMap error.
     * @return  array   An array containing status elements ready to display
     * @since    1.0.0
     * @access   protected
     */
    protected function get_status($error=false) {
        $status = array();
        $status['enabled'] = get_option('live_weather_station_netatmo_account')[2];
        $status['active'] = get_option('live_weather_station_owm_account')[1] != 2;
        $status['o_enabled'] = (!$error && get_option('live_weather_station_owm_account')[0] != '');
        $status['o_active'] = get_option('live_weather_station_owm_account')[1] != 1;
        $status['account'] = get_option('live_weather_station_netatmo_account')[0];
        $status['version'] = __(LWS_PLUGIN_NAME, 'live-weather-station') . ' / ' . LWS_VERSION ;
        return $status;
    }

    /**
     * Get virtual stations (OWM).
     *
     * @return  array   An array containing the virtual stations.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_virtual_stations() {
        $result = array();
        $stations = $this->get_all_owm_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $sub = array();
                $sub['_id'] = Owm_Current_Client::get_unique_owm_id($station['station_id']);
                $sub['module_name'] = $station['station_name'];
                $sub['station_name'] = $station['station_name'];
                $sub['wifi_status'] = 0;
                $sub['firmware'] = LWS_VERSION;
                $sub['type'] = 'NAMain';
                $sub['data_type'] = array();
                $dd = array();
                $dd['time_utc'] = time();
                $plc = array();
                $plc['altitude'] = $station['loc_altitude'];
                $plc['city'] = $station['loc_city'];
                $plc['country'] = $station['loc_country_code'];
                $plc['timezone'] = $station['loc_timezone'];
                $plc['improveLocProposed'] = true;
                $plc['location'] = array($station['loc_longitude'], $station['loc_latitude']);
                $sub['place'] = $plc;
                $sub['dashboard_data'] = $dd;
                $result['devices'][] = $sub;
            }
        }
        return $result;
    }

    /**
     * Merge data from collectors and computers.
     *
     * @param   array   $netatmo    Array with Netatmo collect.
     * @param   array   $owm        Array with OWM collect.
     * @param   array   $pol        Array with pollution values.
     * @param   array   $comp       Array with computed values.
     * @param   array   $eph        Array with ephemeris values.
     *
     * @return  array   An array containing the merged data.
     * @since    2.0.0
     * @access   protected
     */
    protected function merge_data($netatmo, $owm, $pol, $comp, $eph) {
        $result = array();
        if (is_array($netatmo) && count($netatmo) > 0) {
            $result = $netatmo;
        }
        if (count($result) < count($owm)) {
            $devices = $this->get_virtual_stations();
            if (is_array($devices) && count($devices) > 0) {
                foreach ($devices['devices'] as $device) {
                    $result['devices'][] = $device;
                }
            }
        }
        if (is_array($owm) && count($owm)>0 && is_array($result) && count($result)>0) {
            foreach ($result['devices'] as &$device) {
                $mod = null;
                $k = -1;
                foreach ($owm as $key => $own_module) {
                    if ($own_module['device_id'] == $device['_id']) {
                        $mod = $own_module;
                        $k = $key;
                        break;
                    }
                }
                if (isset($mod) && is_array($mod) && $k != -1) {
                    $device['modules'][] = $mod;
                }
            }
        }
        if (is_array($pol) && count($pol)>0 && is_array($result) && count($result)>0) {
            foreach ($result['devices'] as &$device) {
                $mod = null;
                $k = -1;
                foreach ($pol as $key => $pol_module) {
                    if ($pol_module['device_id'] == $device['_id']) {
                        $mod = $pol_module;
                        $k = $key;
                        break;
                    }
                }
                if (isset($mod) && is_array($mod) && $k != -1) {
                    $device['modules'][] = $mod;
                }
            }
        }
        if (is_array($comp) && count($comp)>0 && is_array($result) && count($result)>0) {
            foreach ($result['devices'] as &$device) {
                $mod = null;
                $k = -1;
                foreach ($comp as $key => $comp_module) {
                    if ($comp_module['device_id'] == $device['_id']) {
                        $mod = $comp_module;
                        $k = $key;
                        break;
                    }
                }
                if (isset($mod) && is_array($mod) && $k != -1) {
                    $device['modules'][] = $mod;
                }
            }
        }
        if (is_array($eph) && count($eph)>0 && is_array($result) && count($result)>0) {
            foreach ($result['devices'] as &$device) {
                $mod = null;
                $k = -1;
                foreach ($eph as $key => $eph_module) {
                    if ($eph_module['device_id'] == $device['_id']) {
                        $mod = $eph_module;
                        $k = $key;
                        break;
                    }
                }
                if (isset($mod) && is_array($mod) && $k != -1) {
                    $device['modules'][] = $mod;
                }
            }
        }
        return $result;
    }

    /**
     * Display the main configuration page.
     *
     * @since    1.0.0
     * @access   public
     */
    public function display_configuration_page() {
        $view = 'config';
        $args = array();
        $owm_station_id = 0;
        $log_entry = 0;
        $netatmo_station_id = '';
        $owm_station_error = 0;
        $netatmo_station_error = array();
        $owm_station_delete = array();
        $station = array();
        if (isset($_GET['view'])) {
            $view = $_GET['view'];
        }
        elseif (isset($_POST['view'])) {
            $view = $_POST['view'];
        }
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'set-values' ) {
            $this->set_values();
        }
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'do-add-edit-owm' ) {
            $station = $this->construct_owm_station();
            if (array_key_exists('error', $station)) {
                $owm_station_error = $station['error'];
                $view = 'add-edit-owm';
            }
            else {
                $this->update_owm_station_table($station);
            }
        }
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'do-edit-netatmo' ) {
            $station = $this->construct_infos_station();
            if (array_key_exists('error', $station)) {
                $netatmo_station_error = $station['error'];
                unset($station['error']);
                $view = 'edit-netatmo';
            }
            else {
                $this->update_infos_table($station);
                flush_rewrite_rules();
            }
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'disconnect-netatmo' ) {
            $this->disconnect_netatmo();
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'disconnect-owm' ) {
            $this->disconnect_owm();
        }
        if ( isset( $_GET['action'] ) && ($_GET['action'] == 'add-edit-owm' || $_GET['action'] == 'add-edit-owm')) {
            if (isset($_GET['owm-station'])) {
                $owm_station_id = $_GET['owm-station'];
            }
        }
        if (isset( $_GET['action'] ) && ($_GET['action'] == 'view-log' || $_GET['action'] == 'view-log')) {
            $view = 'view-log';
            if (isset($_GET['log-entry'])) {
                $log_entry = $_GET['log-entry'];
            }
        }
        if ( isset( $_GET['action'] ) && ($_GET['action'] == 'edit-netatmo' || $_GET['action'] == 'edit-netatmo')) {
            if (isset($_GET['netatmo-station'])) {
                $netatmo_station_id = $_GET['netatmo-station'];
            }
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'manage-owm' ) {
            if ( (isset($_GET['subaction']) && $_GET['subaction'] == 'delete') || (isset($_GET['subaction2']) && $_GET['subaction2'] == 'delete') ) {
                if (isset($_GET['station'])) {
                    $owm_station_delete = $_GET['station'];
                }
                if (count($owm_station_delete) > 0) {
                    $view = 'delete-owm';
                }
            }
            if (isset($_GET['subaction']) && $_GET['subaction'] == 'confirm-delete') {
                if (isset($_GET['delstation'])) {
                    $owm_station_delete = $_GET['delstation'];
                }
                if (count($owm_station_delete) > 0) {
                    $this->delete_owm_station_table($owm_station_delete);
                }
            }
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete-owm' ) {
            if (isset($_GET['owm-station'])) {
                $owm_station_delete[] = $_GET['owm-station'];
            }
            if (count($owm_station_delete) > 0) {
                $view = 'delete-owm';
            }
        }
        switch ($view) {
            case 'config':
                $temperature = $this->get_temperature_unit_name_array();
                $pressure = $this->get_pressure_unit_name_array();
                $wind = $this->get_wind_speed_unit_name_array();
                $rain = $this->get_altitude_unit_name_array();
                $altitude = $this->get_altitude_unit_name_array();
                $distance = $this->get_distance_unit_name_array();
                $viewing_options = $this->get_viewing_options_array();
                $mode_options = $this->get_mode_options_array();
                $obsolescence = $this->get_obsolescence_array();
                $windsemantic = $this->get_windsemantic_array();
                $co = $this->get_co_unit_name_array();
                $minmax = $this->get_minmax_array();
                $netatmo = new Netatmo_Collector();
                $owm = new OWM_Current_Collector();
                $pollution = new OWM_Pollution_Collector();
                $weather = new Weather_Computer();
                $ephemeris = new Ephemeris_Computer();
                $datas = $this->merge_data($netatmo->get_datas(true), $owm->get_datas(), $pollution->get_datas(), $weather->compute(), $ephemeris->compute());
                if ($this->netatmo_error == '') {
                    $error = $netatmo->last_netatmo_error;
                }
                else {
                    $error = $this->netatmo_error;
                }
                if ($this->netatmo_warning == '') {
                    $warning = $netatmo->last_netatmo_warning;
                }
                else {
                    $warning = $this->netatmo_warning; 
                }
                if ($this->owm_error == '') {
                    $oerror = $owm->last_owm_error;
                }
                else {
                    $oerror = $this->owm_error;
                }
                if ($this->owm_warning == '') {
                    $owarning = $owm->last_owm_warning;
                }
                else {
                    $owarning = $this->owm_warning;
                }
                if (count($datas) > 0) {
                    $js_array_textual = $this->get_js_array($datas, true, false, false, true);
                    $js_array_icon = $this->get_js_array($datas, true, false, false, true);
                    $js_array_lcd = $this->get_js_array($datas, false, true, true);
                    $js_array_justgage = $this->get_js_array($datas, false, false, true, true, true);
                    $js_array_steelmeter = $this->get_js_array($datas, false, false, true, true, false);
                }
                $js_array_lcd_design = $this->get_lcd_design_js_array();
                $js_array_lcd_size = $this->get_size_js_array();
                $js_array_lcd_speed = $this->get_lcd_speed_js_array();

                $js_array_justgage_design = $this->get_justgage_design_js_array();
                $js_array_justgage_color = $this->get_justgage_color_js_array();
                $js_array_justgage_pointer = $this->get_justgage_pointer_js_array();
                $js_array_justgage_title = $this->get_justgage_title_js_array();
                $js_array_justgage_unit = $this->get_justgage_unit_js_array();
                $js_array_justgage_size = $this->get_size_js_array(true);
                $js_array_justgage_background = $this->get_justgage_background_js_array();

                $js_array_steelmeter_design = $this->get_steelmeter_design_js_array();
                $js_array_steelmeter_frame = $this->get_steelmeter_frame_js_array();
                $js_array_steelmeter_background = $this->get_steelmeter_background_js_array();
                $js_array_steelmeter_orientation = $this->get_steelmeter_orientation_js_array();
                $js_array_steelmeter_glass = $this->get_steelmeter_glass_js_array();
                $js_array_steelmeter_pointer_type = $this->get_steelmeter_pointer_type_js_array();
                $js_array_steelmeter_pointer_color = $this->get_steelmeter_pointer_color_js_array();
                $js_array_steelmeter_knob = $this->get_steelmeter_knob_js_array();
                $js_array_steelmeter_lcd_color = $this->get_steelmeter_lcd_design_js_array();
                $js_array_steelmeter_led_color = $this->get_steelmeter_led_color_js_array();
                $js_array_steelmeter_minmax = $this->get_steelmeter_minmax_js_array();
                $js_array_steelmeter_index_color = $this->get_steelmeter_index_color_js_array();
                $js_array_steelmeter_index_style = $this->get_steelmeter_index_style_js_array();
                $js_array_steelmeter_size = $this->get_size_js_array(false, true, false);

                $status = $this->get_status($oerror != '');
                $args = compact( 'error', 'warning', 'oerror', 'owarning', 'status', 'temperature', 'pressure', 'wind',
                    'rain', 'altitude', 'distance', 'mode_options', 'viewing_options', 'obsolescence', 'minmax', 'datas',
                    'windsemantic', 'co',

                    'js_array_textual', 'js_array_icon',

                    'js_array_lcd', 'js_array_lcd_design', 'js_array_lcd_size', 'js_array_lcd_speed',

                    'js_array_justgage', 'js_array_justgage_design', 'js_array_justgage_size', 'js_array_justgage_color',
                    'js_array_justgage_pointer', 'js_array_justgage_title', 'js_array_justgage_unit', 'js_array_justgage_background',
                
                    'js_array_steelmeter', 'js_array_steelmeter_design', 'js_array_steelmeter_size', 'js_array_steelmeter_frame',
                    'js_array_steelmeter_background', 'js_array_steelmeter_orientation', 'js_array_steelmeter_glass',
                    'js_array_steelmeter_pointer_type', 'js_array_steelmeter_pointer_color', 'js_array_steelmeter_knob',
                    'js_array_steelmeter_lcd_color', 'js_array_steelmeter_led_color', 'js_array_steelmeter_minmax',
                    'js_array_steelmeter_index_style', 'js_array_steelmeter_index_color');
                break;
            case 'add-edit-owm':
                if (count($station) == 0) {
                    $station = $this->get_owm_station($owm_station_id);
                }
                $countries = $this->get_country_names();
                $timezones = $this->get_timezones_js_array();
                $error = $owm_station_error;
                $args = compact('station', 'countries', 'timezones', 'error');
                break;
            case 'edit-netatmo':
                if (count($station) == 0) {
                    $station = $this->get_station_informations($netatmo_station_id);
                }
                $error = $netatmo_station_error;
                $args = compact('station', 'error');
                break;
            case 'delete-owm':
                $stations = $this->get_owm_stations($owm_station_delete);
                if (count($stations) > 0) {
                    foreach ($stations as &$item) {
                        $item['country'] = $this->get_country_name($item['loc_country_code']);
                    }
                }
                $args = compact('stations');
                break;
            case 'view-log':
                if ($log_entry != 0) {
                    $logarray = $this->get_log_detail($log_entry);
                }
                else {
                    $logarray = null;
                }
                if (is_array($logarray)) {
                    $log = $logarray[0];
                    $log['displayed_timestamp'] = $this->get_date_from_mysql_utc($log['timestamp'], '', 'Y-m-d H:i:s') ;
                    $log['displayed_timestamp'] .= ' (' . $this->get_time_diff_from_mysql_utc($log['timestamp']) .')';
                }
                else {
                    $log = array();
                }
                $args = compact('log');
                break;
        }
        $this->view_page($view, $args);
    }

    /**
     * Deletes datas and reinitializes schedules.
     *
     * @since    2.0.0
     */
    private function reboot() {
        self::delete_datas();
    }

    /**
     * Set configuration values.
     *
     * @since    1.0.0
     */
    protected function set_values() {
        $reboot = false;
        if ( array_key_exists('temperature-unit', $_POST) &&
            array_key_exists('pressure-unit', $_POST) &&
            array_key_exists('viewing-options', $_POST) &&
            array_key_exists('obsolescence', $_POST) &&
            array_key_exists('minmax', $_POST) &&
            array_key_exists('wind-unit', $_POST) &&
            array_key_exists('wind-semantic', $_POST) &&
            array_key_exists('altitude-unit', $_POST) &&
            array_key_exists('rain-unit', $_POST) &&
            array_key_exists('co-unit', $_POST) &&
            array_key_exists('distance-unit', $_POST)) {
            $array_options = array((integer)$_POST['temperature-unit'],
                                    (integer) $_POST['pressure-unit'],
                                    (integer) $_POST['wind-unit'],
                                    (integer) $_POST['viewing-options'],
                                    (integer) $_POST['altitude-unit'],
                                    (integer) $_POST['distance-unit'],
                                    (integer) $_POST['obsolescence'],
                                    (integer) $_POST['minmax'],
                                    (integer) $_POST['wind-semantic'],
                                    (integer) $_POST['rain-unit'],
                                    (integer) $_POST['co-unit']);
            update_option('live_weather_station_settings', $array_options);
        }
        if (array_key_exists('login', $_POST) && array_key_exists('password', $_POST)) {
            $login = stripslashes($_POST['login']);
            $password = stripslashes($_POST['password']);
            if ($login != '' && $password != '') {
                $netatmo = new Netatmo_Collector();
                if ($netatmo->authentication($login, $password)) {
                    Logger::notice('Authentication', 'Netatmo', null, null, null, null, null, 'Correctly connected to service.');
                }
                else {
                    Logger::error('Authentication', 'Netatmo', null, null, null, null, null, 'Unable to connect to service.');
                }
                $this->netatmo_error = $netatmo->last_netatmo_error;
                $this->netatmo_warning = $netatmo->last_netatmo_warning;
                $reboot = true;
            }
        }
        if (array_key_exists('key', $_POST)) {
            $key = stripslashes($_POST['key']);
            if (get_option('live_weather_station_owm_account')[0] != $key) {
                $array_account = array($key, get_option('live_weather_station_owm_account')[1]);
                update_option('live_weather_station_owm_account', $array_account);
                $owm = new Owm_Collector();
                if ($owm->authentication()) {
                    Logger::notice('Authentication', 'OpenWeatherMap', null, null, null, null, null, 'Correctly connected to service.');
                }
                else {
                    Logger::error('Authentication', 'OpenWeatherMap', null, null, null, null, null, 'Unable to connect to service.');
                    $array_account = array('', get_option('live_weather_station_owm_account')[1]);
                    update_option('live_weather_station_owm_account', $array_account);
                }
                $this->owm_error = $owm->last_owm_error;
                $this->owm_warning = $owm->last_owm_warning;
                $reboot = true;
            }
        }
        if (array_key_exists('mode', $_POST)) {
            $mode = $_POST['mode'];
            if (get_option('live_weather_station_owm_account')[1] != $mode) {
                $array_account = array(get_option('live_weather_station_owm_account')[0], $mode);
                update_option('live_weather_station_owm_account', $array_account);
                $reboot = true;
            }
        }
        if ($reboot) {
            $this->reboot();
        }
    }

    /**
     * Disconnect from a Netatmo acount.
     *
     * @since    2.0.0
     */
    protected function disconnect_netatmo() {
        update_option('live_weather_station_netatmo_account', array('', '', false));
        Logger::notice('Authentication', 'Netatmo', null, null, null, null, null, 'Correctly disconnected from service.');
        $this->reboot();
    }

    /**
     * Disconnect from an OpenWeatherMap API key.
     *
     * @since    2.0.0
     */
    protected function disconnect_owm() {
        $array_account = array('', get_option('live_weather_station_owm_account')[1]);
        update_option('live_weather_station_owm_account', $array_account);
        Logger::notice('Authentication', 'OpenWeatherMap', null, null, null, null, null, 'Correctly disconnected from service.');
        $this->reboot();
    }

    /**
     * Construct station array.
     *
     * @return  array   The constructed station.
     *
     * @since    2.0.0
     */
    public function construct_owm_station() {
        $result = array();
        $error = 0;
        if ( array_key_exists('station_id', $_POST) &&
            array_key_exists('station_name', $_POST) &&
            array_key_exists('loc_city', $_POST) &&
            array_key_exists('loc_country_code', $_POST) &&
            array_key_exists('loc_tz', $_POST) &&
            array_key_exists('loc_altitude', $_POST)) {
            $result['station_id'] = $_POST['station_id'];
            $result['station_name'] = stripslashes(htmlspecialchars_decode($_POST['station_name']));
            $result['loc_city'] = stripslashes(htmlspecialchars_decode($_POST['loc_city']));
            $result['loc_timezone'] = $_POST['loc_tz'];
            $result['loc_country_code'] = $_POST['loc_country_code'];
            $result['loc_altitude'] = (int)stripslashes(htmlspecialchars_decode($_POST['loc_altitude']));
            if (array_key_exists('loc_latitude', $_POST) &&
                array_key_exists('loc_longitude', $_POST)) {
                if (is_numeric($_POST['loc_latitude']) && is_numeric($_POST['loc_longitude'])) {
                    $result['loc_latitude'] = (float)$_POST['loc_latitude'];
                    $result['loc_longitude'] = (float)$_POST['loc_longitude'];
                    if ($result['loc_latitude'] < -90 || $result['loc_latitude'] > 90) {
                        $error = 2;
                    }
                    if ($result['loc_longitude'] < -180 || $result['loc_longitude'] > 180) {
                        $error = 2;
                    }
                }
                else {
                    $result['loc_latitude'] = $_POST['loc_latitude'];
                    $result['loc_longitude'] = $_POST['loc_longitude'];
                    $error = 2;
                }
            }
            else {
                $result['loc_latitude'] = '';
                $result['loc_longitude'] = '';
            }
            if ($result['loc_latitude'] == '' && $result['loc_longitude'] == '') {
                $coord = OWM_Current_Collector::get_coordinates_via_owm($result['loc_city'], $result['loc_country_code']);
                if (count($coord) > 0) {
                    if (array_key_exists('loc_longitude', $coord) && array_key_exists('loc_latitude', $coord)) {
                        $result['loc_longitude'] = $coord['loc_longitude'];
                        $result['loc_latitude'] = $coord['loc_latitude'];
                        $error = 4;
                    }
                    else {
                        $error = 1;
                    }
                }
                else {
                    $error = 1;
                }
            }
        }
        else {
            $error = 3;
        }
        if ($error == 0) {
            if (array_key_exists('station_id', $result)) {
                if ($result['station_id'] == 0) {
                    unset($result['station_id']);
                }
            }
        }
        else {
            $result['error'] = $error;
        }
        return $result;
    }

    /**
     * Construct infos station array.
     *
     * @return  array   The constructed station.
     *
     * @since    2.5.0
     */
    public function construct_infos_station() {
        $result = array();
        if ( array_key_exists('station_id', $_POST)) {
            $result = $this->get_station_informations($_POST['station_id']);
            if (!empty($result)) {
                foreach ($result as $key=>$val) {
                    if (strpos($key, 'sync') <= 0 && array_key_exists($key, $_POST)) {
                        $result[$key] = $_POST[$key];
                    }
                }
                try {
                    $netatmo = new Netatmo_Collector();
                    $n = $netatmo->get_datas();
                    $weather = new Weather_Computer();
                    $w = $weather->compute();
                    $datas = $this->merge_data($n, array(), array(), $w, array());
                    foreach ($this->pushers as $pusher) {
                        $key = strtolower($pusher) . '_sync';
                        if (array_key_exists($key, $_POST)) {
                            $result[$key] = 1;
                            $class = strtoupper($pusher) . '_Pusher';
                            $push = new $class;
                            $error = $push->post_data($datas, array($result));
                            if ($error != '') {
                                $result['error'][$pusher] = $error;
                                $result[$key] = 0;
                            }
                        }
                        else {
                            $result[$key] = 0;
                        }
                    }
                }
                catch (Exception $ex) {
                    //error_log(LWS_PLUGIN_NAME . ' / ' . LWS_VERSION . ' / ' . get_class() . ' / ' . get_class($this) . ' / Error code: ' . $ex->getCode() . ' / Error message: ' . $ex->getMessage());
                }
            }
        }
        return $result;
    }
}