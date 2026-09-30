<?php

namespace WeatherStation\System\Plugin;

use WeatherStation\Data\Output;
use WeatherStation\SDK\Clientraw\Plugin\StationCollector;


/**
 * The public front functionality of the plugin.
 *
 * @package Public
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
class Frontend {

	use Output;

	private $Live_Weather_Station;
	private $version;
	private $allowed_shortcodes = array('live-weather-station-textual', 'live-weather-station-icon', 'live-weather-station-snapshot', 'live-weather-station-timelapse');

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
        lws_register_style('lws-public', LWS_PUBLIC_URL, 'css/live-weather-station-public.min.css');
        lws_register_style('lws-font-chart-icons', LWS_PUBLIC_URL, 'css/font-chart-icons.min.css');
        lws_register_style('lws-lcd', LWS_PUBLIC_URL, 'css/lws-lcd.min.css');
        lws_register_style('lws-table', LWS_PUBLIC_URL, 'css/live-weather-station-table.min.css');
        lws_register_style('lws-font-awesome-4', LWS_PUBLIC_URL, 'css/fontawesome-4.min.css');
        lws_register_style('lws-font-awesome-5', LWS_PUBLIC_URL, 'css/fontawesome-5.min.css');
        lws_register_style('lws-weather-icons', LWS_PUBLIC_URL, 'css/weather-icons.min.css');
        lws_register_style('lws-weather-icons-wind', LWS_PUBLIC_URL, 'css/weather-icons-wind.min.css');
        lws_register_style('lws-nvd3', LWS_PUBLIC_URL, 'css/nv.d3.min.css', array(), false);
        lws_register_style('lws-cal-heatmap', LWS_PUBLIC_URL, 'css/cal-heatmap.min.css');
        lws_register_style('lws-leaflet', LWS_PUBLIC_URL, 'css/leaflet.min.css');
        wp_register_style('lws-navionics', 'https://webapiv2.navionics.com/dist/webapi/webapi.min.css');
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
     * Modify the tags when rendering scripts.
     *
     * For now, only "defer" tag is supported.
     *
     * @since 3.5.3
     */
    public function modify_scripts($tag, $handle) {
        $scripts_to_defer = array('lws-fa-brands', 'lws-fa-regular', 'lws-fa-solid');
        foreach($scripts_to_defer as $defer_script) {
            if ($defer_script === $handle) {
                return str_replace(' src', ' defer src', $tag);
            }
        }
        return $tag;
    }

    /**
     * Registers (but don't enqueues) the scripts for the public-front side of the site.
     *
     * In doing this way, we can enqueue the needed scripts only when rendering shortcodes...
     *
     * @since 1.0.0
     */
    public function register_scripts() {
        lws_register_script('lws-public', LWS_PUBLIC_URL, 'js/live-weather-station-public.min.js');
        lws_register_script('lws-lcd', LWS_PUBLIC_URL, 'js/lws-lcd.min.js', array('jquery'));
        lws_register_script('lws-tween', LWS_PUBLIC_URL, 'js/tween.min.js');
        lws_register_script('lws-steelseries', LWS_PUBLIC_URL, 'js/steelseries.min.js', array('lws-tween'));
        lws_register_script('lws-radarchart', LWS_PUBLIC_URL, 'js/radarchart.min.js', array('lws-d3'));
        lws_register_script('lws-bilinechart', LWS_PUBLIC_URL, 'js/bilinechart.min.js', array('lws-nvd3'));
        lws_register_script('lws-scale-radial', LWS_PUBLIC_URL, 'js/d3-scale-radial.min.js', array('lws-d3'));
        lws_register_script('lws-windrose', LWS_PUBLIC_URL, 'js/windrose.min.js', array('lws-d3', 'lws-scale-radial'));
        lws_register_script('lws-clipboard', LWS_ADMIN_URL , 'js/clipboard.min.js', array('jquery'));
        lws_register_script('lws-raphael', LWS_PUBLIC_URL , 'js/raphael.min.js', array('jquery'));
        lws_register_script('lws-justgage', LWS_PUBLIC_URL , 'js/justgage.min.js', array('lws-raphael'));
        lws_register_script('lws-d3', LWS_PUBLIC_URL , 'js/d3.v3.min.js', array('jquery'));
        lws_register_script('lws-d4', LWS_PUBLIC_URL , 'js/d3.v4.min.js', array('jquery'));
        lws_register_script('lws-nvd3', LWS_PUBLIC_URL , 'js/nv.d3.v3.min.js', array('lws-d3'));
        lws_register_script('lws-cal-heatmap', LWS_PUBLIC_URL , 'js/cal-heatmap.min.js', array('lws-d3'));
        lws_register_script('lws-colorbrewer', LWS_PUBLIC_URL , 'js/colorbrewer.min.js');
        lws_register_script('lws-spin', LWS_PUBLIC_URL , 'js/spin.min.js');
        lws_register_script('lws-fa-loader', LWS_PUBLIC_URL , 'js/fontawesome.min.js');
        lws_register_script('lws-fa-all', LWS_PUBLIC_URL , 'js/fontawesome-all.min.js');
        lws_register_script('lws-fa-brands', LWS_PUBLIC_URL , 'js/fa-brands.min.js', array('lws-fa-loader'));
        lws_register_script('lws-fa-regular', LWS_PUBLIC_URL , 'js/fa-regular.min.js', array('lws-fa-loader'));
        lws_register_script('lws-fa-solid', LWS_PUBLIC_URL , 'js/fa-solid.min.js', array('lws-fa-loader'));
        lws_register_script('lws-leaflet', LWS_PUBLIC_URL, 'js/leaflet-140.min.js');
        lws_register_script('lws-stamen-boot', LWS_PUBLIC_URL, 'js/stamen.min.js');
        wp_register_script('lws-windy-boot', 'https://api4.windy.com/assets/libBoot.js');
        wp_register_script('lws-navionics', 'https://webapiv2.navionics.com/dist/webapi/webapi.min.no-dep.js');

    }

	/**
	 * Callback method for querying data for graphs.
	 *
	 * @since 3.4.0
	 */
	public function lws_graph_data_callback() {
        $this->lws_rate_limit('lws_graph_data_callback');
        $attributes = array();
        foreach ($this->graph_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        for ($i = 1; $i <= 8; $i++) {
            if (array_key_exists('device_id_'.$i, $_POST)) {
                $attributes['device_id_'.$i] = $this->lws_post_value('device_id_'.$i);
                foreach ($this->graph_allowed_series as $param) {
                    if (array_key_exists($param.'_'.$i, $_POST)) {
                        $attributes[$param.'_'.$i] = $this->lws_post_value($param.'_'.$i);
                    }
                }
            }
        }
        $result = $this->graph_query($this->graph_prepare($attributes), true);
        exit ($this->lws_result_values($result));
    }

    /**
     * Callback method for querying data for graphs.
     *
     * @since 3.8.0
     */
    public function lws_ltgraph_data_callback() {
        $this->lws_rate_limit('lws_ltgraph_data_callback');
        $attributes = array();
        foreach ($this->ltgraph_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        for ($i = 1; $i <= 8; $i++) {
            foreach ($this->ltgraph_allowed_series as $param) {
                if (array_key_exists($param.'_'.$i, $_POST)) {
                    $attributes[$param.'_'.$i] = $this->lws_post_value($param.'_'.$i);
                }
            }
        }
        $result = $this->graph_query($this->ltgraph_prepare($attributes), true);
        exit ($this->lws_result_values($result));
    }

    /**
     * Callback method for querying data for radial.
     *
     * @since 3.8.0
     */
    public function lws_radial_data_callback() {
        $this->lws_rate_limit('lws_radial_data_callback');
        $attributes = array();
        foreach ($this->radial_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        $result = $this->graph_query($this->radial_prepare($attributes), true);
        exit ($this->lws_result_values($result));
    }

    /**
     * Callback method for querying code to inject for graphs.
     *
     * @since 3.4.0
     */
    public function lws_graph_code_callback() {
        $this->lws_rate_limit('lws_graph_code_callback');
        $attributes = array();
        foreach ($this->graph_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        for ($i = 1; $i <= 8; $i++) {
            if (array_key_exists('device_id_'.$i, $_POST)) {
                $attributes['device_id_'.$i] = $this->lws_post_value('device_id_'.$i);
                foreach ($this->graph_allowed_series as $param) {
                    if (array_key_exists($param.'_'.$i, $_POST)) {
                        $attributes[$param.'_'.$i] = $this->lws_post_value($param.'_'.$i);
                    }
                }
            }
        }
        exit ($this->graph_shortcodes($attributes));
    }

    /**
     * Callback method for querying code to inject for graphs.
     *
     * @since 3.8.0
     */
    public function lws_ltgraph_code_callback() {
        $this->lws_rate_limit('lws_ltgraph_code_callback');
        $attributes = array();
        foreach ($this->ltgraph_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        for ($i = 1; $i <= 8; $i++) {
            foreach ($this->ltgraph_allowed_series as $param) {
                if (array_key_exists($param.'_'.$i, $_POST)) {
                    $attributes[$param.'_'.$i] = $this->lws_post_value($param.'_'.$i);
                }
            }
        }
        exit ($this->ltgraph_shortcodes($attributes));
    }

    /**
     * Callback method for querying code to inject for climat textual.
     *
     * @since 3.8.0
     */
    public function lws_lttextual_code_callback() {
        $this->lws_rate_limit('lws_lttextual_code_callback');
        $attributes = array();
        foreach ($this->lttextual_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        exit ($this->lttextual_shortcodes($attributes));
    }

    /**
     * Callback method for querying code to inject for graphs.
     *
     * @since 3.8.0
     */
    public function lws_radial_code_callback() {
        $this->lws_rate_limit('lws_radial_code_callback');
        $attributes = array();
        foreach ($this->radial_allowed_parameter as $param) {
            if (array_key_exists($param, $_POST)) {
                $attributes[$param] = $this->lws_post_value($param);
            }
        }
        exit ($this->radial_shortcodes($attributes));
    }

    /**
     * Callback method for querying measurements by the lcd control.
     *
     * @since 1.0.0
     */
    public function lws_query_lcd_measurements_callback() {
        $this->lws_rate_limit('lws_query_lcd_measurements_callback');
        $_attributes = array();
        $_attributes['device_id'] = $this->lws_post_value('device_id');
        $_attributes['module_id'] = $this->lws_post_value('module_id');
        $_attributes['measure_type'] = $this->lws_post_value('measure_type');
        $response = $this->lcd_value($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying config for the clean gauge control.
     *
     * @since 2.1.0
     */
    public function lws_query_justgage_config_callback() {
        $this->lws_rate_limit('lws_query_justgage_config_callback');
        $_attributes = array();
        $_attributes['id'] = $this->lws_post_value('id');
        $_attributes['device_id'] = $this->lws_post_value('device_id');
        $_attributes['module_id'] = $this->lws_post_value('module_id');
        $_attributes['measure_type'] = $this->lws_post_value('measure_type');
        $_attributes['design'] = $this->lws_post_value('design');
        $_attributes['color'] = $this->lws_post_value('color');
        $_attributes['pointer'] = $this->lws_post_value('pointer');
        $_attributes['title'] = $this->lws_post_value('title');
        $_attributes['subtitle'] = $this->lws_post_value('subtitle');
        $_attributes['unit'] = $this->lws_post_value('unit');
        $_attributes['size'] = $this->lws_post_value('size');
        if (array_key_exists('force', $_POST)) {
            $_attributes['force'] = $this->lws_post_value('force');
        }
        $response = $this->justgage_attributes($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying measurements by the clean gauge control.
     *
     * @since 2.1.0
     */
    public function lws_query_justgage_measurements_callback() {
        $this->lws_rate_limit('lws_query_justgage_measurements_callback');
        $_attributes = array();
        $_attributes['device_id'] = $this->lws_post_value('device_id');
        $_attributes['module_id'] = $this->lws_post_value('module_id');
        $_attributes['measure_type'] = $this->lws_post_value('measure_type');
        $response = $this->justgage_value($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying config for the clean gauge control.
     *
     * @since 2.2.0
     */
    public function lws_query_steelmeter_config_callback() {
        $this->lws_rate_limit('lws_query_steelmeter_config_callback');
        $_attributes = array();
        $_attributes['device_id'] = $this->lws_post_value('device_id');
        $_attributes['module_id'] = $this->lws_post_value('module_id');
        $_attributes['measure_type'] = $this->lws_post_value('measure_type');
        $_attributes['design'] = $this->lws_post_value('design');
        $_attributes['frame'] = strtoupper($this->lws_post_value('frame'));
        $_attributes['background'] = strtoupper($this->lws_post_value('background'));
        $_attributes['orientation'] = strtoupper($this->lws_post_value('orientation'));
        $_attributes['main_pointer_type'] = strtoupper($this->lws_post_value('main_pointer_type'));
        $_attributes['main_pointer_color'] = strtoupper($this->lws_post_value('main_pointer_color'));
        $_attributes['aux_pointer_type'] = strtoupper($this->lws_post_value('aux_pointer_type'));
        $_attributes['aux_pointer_color'] = strtoupper($this->lws_post_value('aux_pointer_color'));
        $_attributes['knob'] = strtoupper($this->lws_post_value('knob'));
        $_attributes['lcd'] = strtoupper($this->lws_post_value('lcd'));
        $_attributes['alarm'] = strtoupper($this->lws_post_value('alarm'));
        $_attributes['trend'] = strtoupper($this->lws_post_value('trend'));
        $_attributes['minmax'] = $this->lws_post_value('minmax');
        $_attributes['index_style'] = strtoupper($this->lws_post_value('index_style'));
        $_attributes['index_color'] = strtoupper($this->lws_post_value('index_color'));
        $_attributes['glass'] = strtoupper($this->lws_post_value('glass'));
        $_attributes['size'] = $this->lws_post_value('size');
        $response = $this->steelmeter_attributes($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for querying measurements by the clean gauge control.
     *
     * @since 2.2.0
     */
    public function lws_query_steelmeter_measurements_callback() {
        $this->lws_rate_limit('lws_query_steelmeter_measurements_callback');
        $_attributes = array();
        $_attributes['device_id'] = $this->lws_post_value('device_id');
        $_attributes['module_id'] = $this->lws_post_value('module_id');
        $_attributes['measure_type'] = $this->lws_post_value('measure_type');
        $response = $this->steelmeter_value($_attributes);
        exit (json_encode ($response));
    }

    /**
     * Callback method for testing clientraw.txt validity.
     *
     * Registered only for logged-in users (wp_ajax_lws_clientraw_test, no nopriv) and restricted to administrators: it
     * performs an outbound connection / file access on a user-supplied resource.
     *
     * @since 3.0.0
     */
    public function lws_clientraw_test_callback() {
        if (!current_user_can(apply_filters('lws_manage_options_capability', 'manage_options'))) {
            wp_send_json(array('result' => __('You are not allowed to do this.', 'live-weather-station')), 403);
        }
        check_ajax_referer('lws_clientraw_test', 'nonce');
        $_attributes = array();
        $_attributes['connection_type'] = $this->lws_post_value('connection_type');
        $_attributes['resource'] = $this->lws_post_value('resource');
        $collector = new StationCollector();
        $s = $collector->test($_attributes['connection_type'], $_attributes['resource']);
        if ($s == '') {
            $s = __('File is accessible and its format seems good.', 'live-weather-station');
        }
        exit (json_encode(array('result' => $s)));
    }


    /**
     * Callback method for rendering allowed shortcodes (i.e. outputting only HTML).
     *
     * @since 3.6.0
     */
    public function lws_shortcode_callback() {
        $this->lws_rate_limit('lws_shortcode_callback');
        $shortcode = $this->lws_post_value('sc');
        // Magic quotes add backslashes before quotes: remove them so the shortcode parser works. Values are never trusted by the SQL layer.
        $shortcode = wp_unslash($shortcode);
        if (strpos($shortcode, '[') === false) {
            $shortcode = '[' . $shortcode . ']';
        }
        $allowed = false;
        if (substr_count($shortcode, '[') === 1 && preg_match('/^\[([a-z0-9_-]+)\b/i', $shortcode, $tag_match)) {
            $allowed = in_array($tag_match[1], $this->allowed_shortcodes, true);
        }
        if ($allowed) {
            exit(do_shortcode($shortcode));
        }
        else {
            exit('<p>' . esc_html__('Malformed shortcode. Please verify it!', 'live-weather-station') . '</p>');
        }
    }

    /**
     * Get a sanitized value from $_POST.
     *
     * @param string $key The key of the value.
     * @return string The sanitized value, empty string if missing or not a scalar.
     * @since 3.8.15
     */
    private function lws_post_value($key) {
        if (isset($_POST[$key]) && is_scalar($_POST[$key])) {
            $value = wp_kses($_POST[$key], array());
            // Anonymous visitors must not bypass the cache: only administrators may force a fresh computation.
            if ($key === 'cache' && $value === 'no_cache' && !current_user_can(apply_filters('lws_manage_options_capability', 'manage_options'))) {
                return 'cache';
            }
            return $value;
        }
        return '';
    }

    /**
     * Get the 'values' part of a graph query result as a JSON string, whatever the result is.
     *
     * @param mixed $result The result of graph_query().
     * @return string A JSON string (an empty series if the result is unusable).
     * @since 3.8.15
     */
    private function lws_result_values($result) {
        if (is_array($result) && isset($result['values']) && is_scalar($result['values']) && (string)$result['values'] !== '') {
            return (string)$result['values'];
        }
        return '[]';
    }

    /**
     * Rate limit wrapper (see lws_public_rate_limit() in functions.php).
     *
     * @param string $action The endpoint identifier.
     * @since 3.8.15
     */
    private function lws_rate_limit($action) {
        lws_public_rate_limit($action);
    }

    public static function lws_widget_callback() {
        exit ('D O N E !');
    }
}
