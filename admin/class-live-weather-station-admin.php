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
require_once(LWS_INCLUDES_DIR.'class-owm-current-collector.php');
require_once(LWS_INCLUDES_DIR.'class-owm-pollution-collector.php');
require_once(LWS_INCLUDES_DIR.'class-weather-computer.php');
require_once(LWS_INCLUDES_DIR.'class-ephemeris-computer.php');
require_once(LWS_INCLUDES_DIR.'class-owm-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-pws-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-wow-pusher.php');
require_once(LWS_INCLUDES_DIR.'class-wug-pusher.php');

class Live_Weather_Station_Admin {

    use Options_manipulation, Javascript_Array, Datas_Output {
        Datas_Output::get_module_type insteadof Javascript_Array;
        Datas_Output::get_measurement_type insteadof Javascript_Array;
    }

	private $Live_Weather_Station;
	private $version;
    private $netatmo_error = '';
    private $netatmo_warning = '';
    private $pushers = array('owm', 'pws', 'wow', 'wug');

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $Live_Weather_Station       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $Live_Weather_Station, $version ) {
		$this->Live_Weather_Station = $Live_Weather_Station;
		$this->version = $version;
	}

	/**
	 * Enqueues the stylesheets for the admin area.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->Live_Weather_Station, LWS_ADMIN_URL.'css/live-weather-station-admin.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'live-weather-station-public.css', LWS_PUBLIC_URL.'css/live-weather-station-public.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'thickbox' );
	}

	/**
	 * Enqueues the JavaScript for the admin area.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function enqueue_scripts() {
        wp_enqueue_script( $this->Live_Weather_Station, LWS_ADMIN_URL.'js/live-weather-station-admin.min.js', array( 'jquery', 'postbox' ), $this->version, false );
        wp_enqueue_script( 'clipboard.js', LWS_ADMIN_URL.'js/clipboard.min.js', array('jquery'), $this->version, false );
        wp_enqueue_script( 'lws-lcd.js', LWS_PUBLIC_URL.'js/lws-lcd.min.js', array('jquery'), $this->version, false );
        wp_enqueue_script( 'raphael.js', LWS_PUBLIC_URL.'js/raphael.min.js', array('jquery'), $this->version, false );
        wp_enqueue_script( 'justgage.js', LWS_PUBLIC_URL.'js/justgage.min.js', array('raphael.js'), $this->version, false );
        wp_enqueue_script( 'tween.js', LWS_PUBLIC_URL.'js/tween.min.js', array(), $this->version, true );
        wp_enqueue_script( 'steelseries.js', LWS_PUBLIC_URL.'js/steelseries.min.js', array('tween.js'), $this->version, true );
        wp_enqueue_script( 'thickbox' );
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
        foreach ( $args AS $key => $val ) {
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
                $oerror = $owm->last_owm_error;
                $owarning = $owm->last_owm_warning;
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
                $netatmo->authentication($login, $password);
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
                    $datas = $this->merge_data($n, array(), $w, array());
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