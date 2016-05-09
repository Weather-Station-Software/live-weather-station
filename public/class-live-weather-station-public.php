<?php
/**
 * The public front functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/public
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since		1.0.0
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');

class Live_Weather_Station_Public {

	use Datas_Output;

	private $Live_Weather_Station;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $Live_Weather_Station       The name of the plugin.
	 * @param      string    $version    				The version of this plugin.
	 * @access	public
	 */
	public function __construct( $Live_Weather_Station, $version ) {
		$this->Live_Weather_Station = $Live_Weather_Station;
		$this->version = $version;
	}

	/**
	 * Enqueues the stylesheets for the public-front side of the site.
	 *
	 * @since    1.0.0
	 * @access 	public
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->Live_Weather_Station, LWS_PUBLIC_URL.'css/live-weather-station-public.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueues the scripts for the public-front side of the site.
	 *
	 * @since    1.0.0
	 * @access	public
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script( $this->Live_Weather_Station, plugin_dir_url( __FILE__ ) . 'js/live-weather-station-public.min.js', array( 'jquery' ), $this->version, false );
	}

    /**
     * Registers (but don't enqueues) the scripts for the public-front side of the site.
     *
     * In doing this way, we can enqueue the needed scripts only when rendering shortcodes...
     *
     * @since    1.0.0
     * @access	public
     */
    public function register_scripts() {
        wp_register_script( 'lws-lcd.js', LWS_PUBLIC_URL.'js/lws-lcd.min.js', array('jquery'), $this->version, true );
        wp_register_script( 'raphael.js', LWS_PUBLIC_URL.'js/raphael.min.js', array('jquery'), $this->version, true );
        wp_register_script( 'justgage.js', LWS_PUBLIC_URL.'js/justgage.min.js', array('raphael.js'), $this->version, true );
        wp_register_script( 'tween.js', LWS_PUBLIC_URL.'js/tween.min.js', array(), $this->version, true );
        wp_register_script( 'steelseries.js', LWS_PUBLIC_URL.'js/steelseries.min.js', array('tween.js'), $this->version, true );
    }

	/**
	 * Callback method for querying datas by the lcd control.
	 *
	 * @since    1.0.0
	 */
	public function lws_query_lcd_datas_callback() {
        $device_id = wp_kses($_POST['device_id'], array());
        $module_id = wp_kses($_POST['module_id'], array());
        $measure_type = wp_kses($_POST['measure_type'], array());
        $computed = !(bool)get_option('live_weather_station_settings')[3] ;
        if ((strtolower($module_id) == 'outdoor') || (strtolower($measure_type) == 'aggregated' && Owm_Current_Client::is_owm_current_module($module_id))) {
            $raw_datas = $this->get_outdoor_datas($device_id, true);
            $measure_type = 'outdoor';
        }
        elseif (strtolower($measure_type) == 'aggregated' && Owm_Current_Client::is_owm_pollution_module($module_id)) {
            $raw_datas = $this->get_pollution_datas($device_id, false);
            $measure_type = 'pollution';
        }
        elseif (strtolower($module_id) == 'aggregated') {
            $raw_datas = $this->get_all_datas($device_id, true);
        }
        else {
            $raw_datas = $this->get_module_datas($module_id, (Owm_Current_Client::is_owm_pollution_module($module_id) ? false : true));
        }
        $response = array();
        if (array_key_exists('condition', $raw_datas)) {
            $measure['min'] = 0;
            $measure['max'] = 0;
            $measure['value'] = 0;
            $measure['unit'] = '';
            $measure['decimals'] = 1;
            $measure['sub_unit'] = '';
            $measure['show_sub_unit'] = false;
            $measure['show_min_max'] = false;
            $measure['title'] = __( 'Error code ' , 'live-weather-station').$raw_datas['condition']['value'];
            if ($raw_datas['condition']['value'] == 3 || $raw_datas['condition']['value'] == 4) {
                $save_locale = setlocale(LC_ALL,'');
                setlocale(LC_ALL, get_locale());
                $measure['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', __( 'No data' , 'live-weather-station'));
                setlocale(LC_ALL, $save_locale);
            }
            $measure['battery'] = 'full';
            $measure['trend'] = '';
            $measure['show_trend'] = false;
            $measure['show_alarm'] = true;
            $measure['signal'] = 0;
            $response[] = $measure;
        }
        else {
            $datas = $this->format_lcd_datas($raw_datas, $measure_type, $computed);
            if ($datas['condition']['value'] != 0) {
                $measure['min'] = 0;
                $measure['max'] = 0;
                $measure['value'] = 0;
                $measure['unit'] = '';
                $measure['decimals'] = 1;
                $measure['sub_unit'] = '';
                $measure['show_sub_unit'] = false;
                $measure['show_min_max'] = false;
                $measure['title'] = __( 'Error code ' , 'live-weather-station').$datas['condition']['value'];
                if ($datas['condition']['value'] == 3 || $datas['condition']['value'] == 4) {
                    $save_locale = setlocale(LC_ALL,'');
                    setlocale(LC_ALL, get_locale());
                    $measure['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', __( 'No data' , 'live-weather-station'));
                    setlocale(LC_ALL, $save_locale);
                }
                $measure['battery'] = 'full';
                $measure['trend'] = '';
                $measure['show_trend'] = false;
                $measure['show_alarm'] = true;
                $measure['signal'] = 0;
                $response[] = $measure;
            }
            else {
                $response = $datas['datas'];
            }
        }
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying config for the clean gauge control.
     *
     * @since    2.1.0
     */
    public function lws_query_justgage_config_callback() {
        $_attributes = array();
        $_attributes['id'] = wp_kses($_POST['id'], array());
        $_attributes['device_id'] = wp_kses($_POST['device_id'], array());
        $_attributes['module_id'] = wp_kses($_POST['module_id'], array());
        $_attributes['measure_type'] = wp_kses($_POST['measure_type'], array());
        $_attributes['design'] = wp_kses($_POST['design'], array());
        $_attributes['color'] = wp_kses($_POST['color'], array());
        $_attributes['pointer'] = wp_kses($_POST['pointer'], array());
        $_attributes['title'] = wp_kses($_POST['title'], array());
        $_attributes['subtitle'] = wp_kses($_POST['subtitle'], array());
        $_attributes['unit'] = wp_kses($_POST['unit'], array());
        $_attributes['size'] = wp_kses($_POST['size'], array());
        $response = $this->justgage_attributes($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying datas by the clean gauge control.
     *
     * @since    2.1.0
     */
    public function lws_query_justgage_datas_callback() {
        $_attributes = array();
        $_attributes['device_id'] = wp_kses($_POST['device_id'], array());
        $_attributes['module_id'] = wp_kses($_POST['module_id'], array());
        $_attributes['measure_type'] = wp_kses($_POST['measure_type'], array());
        $response = $this->justgage_value($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying config for the clean gauge control.
     *
     * @since    2.2.0
     */
    public function lws_query_steelmeter_config_callback() {
        $_attributes = array();
        $_attributes['device_id'] = wp_kses($_POST['device_id'], array());
        $_attributes['module_id'] = wp_kses($_POST['module_id'], array());
        $_attributes['measure_type'] = wp_kses($_POST['measure_type'], array());
        $_attributes['design'] = wp_kses($_POST['design'], array());
        $_attributes['frame'] = strtoupper(wp_kses($_POST['frame'], array()));
        $_attributes['background'] = strtoupper(wp_kses($_POST['background'], array()));
        $_attributes['orientation'] = strtoupper(wp_kses($_POST['orientation'], array()));
        $_attributes['main_pointer_type'] = strtoupper(wp_kses($_POST['main_pointer_type'], array()));
        $_attributes['main_pointer_color'] = strtoupper(wp_kses($_POST['main_pointer_color'], array()));
        $_attributes['aux_pointer_type'] = strtoupper(wp_kses($_POST['aux_pointer_type'], array()));
        $_attributes['aux_pointer_color'] = strtoupper(wp_kses($_POST['aux_pointer_color'], array()));
        $_attributes['knob'] = strtoupper(wp_kses($_POST['knob'], array()));
        $_attributes['lcd'] = strtoupper(wp_kses($_POST['lcd'], array()));
        $_attributes['alarm'] = strtoupper(wp_kses($_POST['alarm'], array()));
        $_attributes['trend'] = strtoupper(wp_kses($_POST['trend'], array()));
        $_attributes['minmax'] = wp_kses($_POST['minmax'], array());
        $_attributes['index_style'] = strtoupper(wp_kses($_POST['index_style'], array()));
        $_attributes['index_color'] = strtoupper(wp_kses($_POST['index_color'], array()));
        $_attributes['glass'] = strtoupper(wp_kses($_POST['glass'], array()));
        $_attributes['size'] = wp_kses($_POST['size'], array());
        $response = $this->steelmeter_attributes($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying datas by the clean gauge control.
     *
     * @since    2.2.0
     */
    public function lws_query_steelmeter_datas_callback() {
        $_attributes = array();
        $_attributes['device_id'] = wp_kses($_POST['device_id'], array());
        $_attributes['module_id'] = wp_kses($_POST['module_id'], array());
        $_attributes['measure_type'] = wp_kses($_POST['measure_type'], array());
        $response = $this->steelmeter_value($_attributes);
        exit (json_encode ($response));
    }
}
