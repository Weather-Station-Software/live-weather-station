<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\Data\Output;


/**
 * The public front functionality of the plugin.
 *
 * @package Public
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Frontend {

	use Output;

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
        $_attributes = array();
        $_attributes['device_id'] = wp_kses($_POST['device_id'], array());
        $_attributes['module_id'] = wp_kses($_POST['module_id'], array());
        $_attributes['measure_type'] = wp_kses($_POST['measure_type'], array());
        $response = $this->lcd_value($_attributes);
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