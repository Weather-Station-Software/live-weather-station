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
     * Registers (but don't enqueues) the styles for the public-front side of the site.
     *
     * In doing this way, we can enqueue the needed styles only when rendering shortcodes...
     * /!\ for widgets it is not possible to use registered styles, full enqueueing must be used instead.
     *
     * @since 3.2.0
     */
    public function register_styles() {
        wp_register_style('lws-public', LWS_PUBLIC_URL.'css/live-weather-station-public.min.css', array(), $this->version);
        wp_register_style('lws-font-chart-icons', LWS_PUBLIC_URL.'css/font-chart-icons.min.css', array(), $this->version);
        wp_register_style('lws-lcd', LWS_PUBLIC_URL.'css/lws-lcd.min.css', array(), $this->version);
        wp_register_style('lws-table', LWS_PUBLIC_URL.'css/live-weather-station-table.min.css', array(), $this->version);
        if ((bool)get_option('live_weather_station_use_cdn')) {
            wp_register_style('lws-font-awesome', '//cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css', array());
            wp_register_style('lws-weather-icons', '//cdn.jsdelivr.net/npm/weathericons@2.1.0/css/weather-icons.min.css', array());
            wp_register_style('lws-weather-icons-wind', '//cdn.jsdelivr.net/npm/weathericons@2.1.0/css/weather-icons-wind.min.css', array());
            wp_register_style('lws-nvd3', '//cdn.jsdelivr.net/npm/nvd3@1.8.6/build/nv.d3.min.css', array());
            wp_register_style('lws-cal-heatmap', '//cdn.jsdelivr.net/npm/cal-heatmap@3.6.2/cal-heatmap.css', array());
        }
        else {
            wp_register_style('lws-font-awesome', LWS_PUBLIC_URL.'css/font-awesome.min.css', array(), $this->version);
            wp_register_style('lws-weather-icons', LWS_PUBLIC_URL . 'css/weather-icons.min.css', array(), $this->version);
            wp_register_style('lws-weather-icons-wind', LWS_PUBLIC_URL . 'css/weather-icons-wind.min.css', array(), $this->version);
            wp_register_style('lws-nvd3', LWS_PUBLIC_URL.'css/nv.d3.min.css', array(), $this->version);
            wp_register_style('lws-cal-heatmap', LWS_PUBLIC_URL.'css/cal-heatmap.min.css', array(), $this->version);
        }
    }

	/**
	 * Enqueues the stylesheets for the public-front side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style('lws-public');
	}

	/**
	 * Enqueues the scripts for the public-front side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script('lws-public');
	}

    /**
     * Registers (but don't enqueues) the scripts for the public-front side of the site.
     *
     * In doing this way, we can enqueue the needed scripts only when rendering shortcodes...
     *
     * @since 1.0.0
     */
    public function register_scripts() {
        wp_register_script('lws-public', LWS_PUBLIC_URL.'js/live-weather-station-public.min.js', array(), $this->version);
        wp_register_script('lws-lcd', LWS_PUBLIC_URL.'js/lws-lcd.min.js', array('jquery'), $this->version, true);
        wp_register_script('lws-tween', LWS_PUBLIC_URL.'js/tween.min.js', array(), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
        wp_register_script('lws-steelseries', LWS_PUBLIC_URL.'js/steelseries.min.js', array('lws-tween'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
        if ((bool)get_option('live_weather_station_use_cdn')) {
            wp_register_script('lws-clipboard', '//cdn.jsdelivr.net/npm/clipboard@1.5.2/dist/clipboard.min.js', array('jquery'), (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-raphael', '//cdn.jsdelivr.net/npm/raphael@2.1.4/raphael-min.js', array('jquery'), false, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-justgage', '//cdn.jsdelivr.net/npm/justgage@1.2.2/justgage.js', array('lws-raphael'), false, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-d3', '//cdn.jsdelivr.net/npm/d3@3.5.17/d3.min.js', array('jquery'), false, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-nvd3', '//cdn.jsdelivr.net/npm/nvd3@1.8.6/build/nv.d3.min.js', array('lws-d3'), false, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-cal-heatmap', '//cdn.jsdelivr.net/npm/cal-heatmap@3.6.2/cal-heatmap.min.js', array('lws-d3'), false, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-colorbrewer', '//cdn.jsdelivr.net/npm/colorbrewer@1.0.0/colorbrewer.js', array(), false, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-spin', '//cdn.jsdelivr.net/npm/spin.js@2.3.2/spin.min.js', array(), false, (bool)get_option('live_weather_station_footer_scripts', false));
        }
        else {
            wp_register_script('lws-clipboard', LWS_ADMIN_URL.'js/clipboard.min.js', array('jquery'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-raphael', LWS_PUBLIC_URL.'js/raphael.min.js', array('jquery'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-justgage', LWS_PUBLIC_URL.'js/justgage.min.js', array('lws-raphael'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-d3', LWS_PUBLIC_URL.'js/d3.v3.min.js', array('jquery'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-nvd3', LWS_PUBLIC_URL.'js/nv.d3.v3.min.js', array('lws-d3'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-cal-heatmap', LWS_PUBLIC_URL.'js/cal-heatmap.min.js', array('lws-d3'), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-colorbrewer', LWS_PUBLIC_URL.'js/colorbrewer.min.js', array(), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
            wp_register_script('lws-spin', LWS_PUBLIC_URL.'js/spin.min.js', array(), $this->version, (bool)get_option('live_weather_station_footer_scripts', false));
        }
    }

	/**
	 * Callback method for querying data for graphs.
	 *
	 * @since 3.4.0
	 */
	public function lws_graph_data_callback() {
        $attributes = array();
        foreach ($this->graph_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = wp_kses($_POST[$param], array());
            }
        }
        for ($i = 1; $i <= 8; $i++) {
            if (array_key_exists('device_id_'.$i, $_POST)) {
                $attributes['device_id_'.$i] = wp_kses($_POST['device_id_'.$i], array());
                foreach ($this->graph_allowed_serie as $param) {
                    if (array_key_exists($param.'_'.$i, $_POST)) {
                        $attributes[$param.'_'.$i] = wp_kses($_POST[$param.'_'.$i], array());
                    }
                }
            }
        }
        $result = $this->graph_query($this->graph_prepare($attributes), true);
        exit ($result['values']);
    }

    /**
     * Callback method for querying code to inject for graphs.
     *
     * @since 3.4.0
     */
    public function lws_graph_code_callback() {
        $attributes = array();
        foreach ($this->graph_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = wp_kses($_POST[$param], array());
            }
        }
        for ($i = 1; $i <= 8; $i++) {
            if (array_key_exists('device_id_'.$i, $_POST)) {
                $attributes['device_id_'.$i] = wp_kses($_POST['device_id_'.$i], array());
                foreach ($this->graph_allowed_serie as $param) {
                    if (array_key_exists($param.'_'.$i, $_POST)) {
                        $attributes[$param.'_'.$i] = wp_kses($_POST[$param.'_'.$i], array());
                    }
                }
            }
        }
        exit ($this->graph_shortcodes($attributes));
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
