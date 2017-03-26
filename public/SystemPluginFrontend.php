<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\Data\Output;
use WeatherStation\SDK\Clientraw\Plugin\StationCollector;


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
	 * @param string $Live_Weather_Station The name of the plugin.
	 * @param string $version The version of this plugin.
     * @since 1.0.0
     *
     */
	public function __construct( $Live_Weather_Station, $version ) {
		$this->Live_Weather_Station = $Live_Weather_Station;
		$this->version = $version;
	}

	/**
	 * Enqueues the stylesheets for the public-front side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style('live-weather-station-public.css');
	}

	/**
	 * Enqueues the scripts for the public-front side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script( $this->Live_Weather_Station, plugin_dir_url( __FILE__ ) . 'js/live-weather-station-public.min.js', array( 'jquery' ), $this->version, false );
	}

    /**
     * Registers (but don't enqueues) the scripts for the public-front side of the site.
     *
     * In doing this way, we can enqueue the needed scripts only when rendering shortcodes...
     *
     * @since 1.0.0
     */
    public function register_scripts() {
        wp_register_script('lws-lcd.js', LWS_PUBLIC_URL.'js/lws-lcd.min.js', array('jquery'), $this->version, true );
        wp_register_script('raphael.js', LWS_PUBLIC_URL.'js/raphael.min.js', array('jquery'), $this->version, true );
        wp_register_script('justgage.js', LWS_PUBLIC_URL.'js/justgage.min.js', array('raphael.js'), $this->version, true );
        wp_register_script('tween.js', LWS_PUBLIC_URL.'js/tween.min.js', array(), $this->version, true );
        wp_register_script('steelseries.js', LWS_PUBLIC_URL.'js/steelseries.min.js', array('tween.js'), $this->version, true );
        wp_register_script('d3.v3.js', LWS_PUBLIC_URL.'js/d3.v3.min.js', array('jquery'), $this->version, true );
        wp_register_script('nv.d3.v3.js', LWS_PUBLIC_URL.'js/nv.d3.v3.min.js', array('d3.v3.js'), $this->version, true );
        wp_register_script('cal-heatmap.js', LWS_PUBLIC_URL.'js/cal-heatmap.min.js', array('d3.v3.js'), $this->version, true );
        wp_register_script('colorbrewer.js', LWS_PUBLIC_URL.'js/colorbrewer.min.js', array(), $this->version, true );
    }

    /**
     * Registers (but don't enqueues) the styles for the public-front side of the site.
     *
     * In doing this way, we can enqueue the needed styles only when rendering shortcodes...
     * /!\ for widgets it is not possible to use registered styles, full enqueueing must be used instead.
     *
     * @since 3.2.0
     */
    public function register_styles() {
        wp_register_style('live-weather-station-public.css', LWS_PUBLIC_URL.'css/live-weather-station-public.min.css', array(), $this->version);
        wp_register_style('font-awesome.css', LWS_PUBLIC_URL.'css/font-awesome.min.css', array(), $this->version);
        //wp_register_style('thickbox');
        wp_register_style('nv.d3.css', LWS_PUBLIC_URL.'css/nv.d3.min.css', array(), $this->version);
        wp_register_style('cal-heatmap.css', LWS_PUBLIC_URL.'css/cal-heatmap.min.css', array(), $this->version);
        wp_register_style('weather-icons.css', LWS_PUBLIC_URL . 'css/weather-icons.min.css', array(), $this->version);
        wp_register_style('weather-icons-wind.css', LWS_PUBLIC_URL . 'css/weather-icons-wind.min.css', array(), $this->version);
    }

	/**
	 * Callback method for querying datas by the lcd control.
	 *
	 * @since 1.0.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function lws_query_steelmeter_datas_callback() {
        $_attributes = array();
        $_attributes['device_id'] = wp_kses($_POST['device_id'], array());
        $_attributes['module_id'] = wp_kses($_POST['module_id'], array());
        $_attributes['measure_type'] = wp_kses($_POST['measure_type'], array());
        $response = $this->steelmeter_value($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for testing clientraw.txt validity.
     *
     * @since 3.0.0
     */
    public function lws_clientraw_test_callback() {
        $_attributes = array();
        $_attributes['connection_type'] = wp_kses($_POST['connection_type'], array());
        $_attributes['resource'] = wp_kses($_POST['resource'], array());
        $collector = new StationCollector();
        $s = $collector->test($_attributes['connection_type'], $_attributes['resource']);
        if ($s == '') {
            $s = __('File is accessible and its format seems good.', 'live-weather-station');
        }
        exit (json_encode(array('result' => $s)));
    }
}
