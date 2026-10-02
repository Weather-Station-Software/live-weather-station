<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Output\Guard;

/**
 * Outdoor weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
abstract class Base extends \WP_Widget {


    /**
     * Get the widget output.
     *
     * @param array $args An array containing the widget's arguments.
     * @param array $instance An array containing settings for the widget.
     * @return string The widget content.
     * @since 3.8.0
     */
    public abstract function widget_content($args, $instance);

    /**
     * Enqueues widget styles.
     *
     * @since 3.8.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('lws-weather-icons');
        wp_enqueue_style('lws-weather-icons-wind');
        live_weather_station_font_awesome();
    }

    /**
     * Get the widget output.
     *
     * @param array $args An array containing the widget's arguments.
     * @param array $instance An array containing settings for the widget.
     * @since 3.8.0
     */
    public function widget($args, $instance) {
        $this->enqueue_styles();
        // The AJAX mode needs a stored widget instance to refer to (the server never trusts client-supplied settings).
        // Widgets rendered without a real sidebar slot (Legacy Widget block, Customizer preview: number = -1 or
        // unsaved) have no stored instance and are rendered inline. Never coerce with absint(): -1 would become 1.
        $number = (isset($this->number) && is_numeric($this->number) && (int)$this->number > 0) ? (int)$this->number : 0;
        if ($number > 0) {
            $stored = get_option('widget_' . $this->id_base);
            if (!is_array($stored) || !isset($stored[$number]) || !is_array($stored[$number]) ||
                self::sanitize_instance($stored[$number]) != self::sanitize_instance((array)$instance)) {
                $number = 0;
            }
        }
        if ($number > 0 && (bool)get_option('live_weather_station_ajax_widget', 1) && !is_admin()) {
            wp_enqueue_script('jquery');
            $cache_id = md5(serialize(array_merge($args, $instance)));
            $result = Cache::get_widget($cache_id);
            if (!$result) {
                $fingerprint = uniqid('', true);
                $uuid = substr ($fingerprint, strlen($fingerprint)-6, 80);
                $widget = strtolower(str_replace('weatherstation\ui\widget\\', '', strtolower(static::class)));
                $uniq = 'widget-' . $widget . '-' . $uuid;
                $id = 'id="' . $uniq . '"';
                $before = '<div id="' . $uniq . '" class="widget lws-widget-wrapper">';
                $after = '</div>';
                if (array_key_exists('before_widget', $args) && array_key_exists('after_widget', $args)) {
                    if (strpos($args['before_widget'], 'id=') != false) {
                        $before = preg_replace('/(id=".*")/iU', $id,  $args['before_widget'], 1);
                    }
                    else {
                        $before = str_replace('class=', $id . ' class=', $args['before_widget']);
                    }
                    $after = $args['after_widget'];
                }
                $result = $before . '&nbsp;';
                $jsInitId = md5(random_bytes(18));
                $result .= live_weather_station_print_begin_script($jsInitId) . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    $.post( ' . Guard::js(LIVE_WEATHER_STATION_AJAX_URL) . ', {action: ' . Guard::js('lws_w_' . $widget) . ', wnum: ' . $number . '}).done(function(data) {$("#' . $uniq . '").html(data);})';
                $result .= '  });' . PHP_EOL;
                $result .= live_weather_station_print_end_script($jsInitId);
                $result .= $after;
                Cache::set_widget($cache_id, $result);
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $result was assembled above from the sidebar wrappers (before_widget/after_widget, set by the theme), a generated id, and a script whose URL and action name go through Guard::js(); the widget number is cast with (int).
            echo $result;
        }
        else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget markup (it can hold script and canvas elements) built by widget_content() of the widget classes, which escape their own values; before_widget/after_widget come from the theme sidebar definition, as for any WordPress widget.
            echo $this->widget_content($args, $instance);
        }
    }

    /**
     * Get the widget settings as js vars.
     *
     * @return string The vars strings ready to print.
     * @since 3.8.0
     */
    public function outputvar($args, $instance) {
        $result = '';
        $excluded = array('name', 'id', 'description', 'widget_id', 'widget_name');
        foreach (array_merge($args, $instance) as $key => $arg) {
            if (!in_array($key, $excluded)) {
                if (!is_string($key) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                    continue;
                }
                if (is_bool($arg)) {
                    $result .= $key . ':' . ($arg?'true':'false') . ', ';
                    continue;
                }
                if (is_numeric($arg) || is_string($arg)) {
                    $result .= $key . ':' . Guard::js($arg) . ', ';
                    continue;
                }
            }
        }
        return $result;
    }

    /**
     * Sanitize a color setting.
     *
     * @param mixed $value The candidate (#rgb, #rrggbb, with or without #).
     * @param string $default The value returned if the candidate is not acceptable.
     * @return string The color, with a leading #, or the default.
     * @since 3.8.15
     */
    protected static function sanitize_color($value, $default = '') {
        if (is_scalar($value) && preg_match('/^#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', trim((string)$value), $m)) {
            return '#' . $m[1];
        }
        return $default;
    }

    /**
     * Sanitize a picture URL setting. The special value "self" is kept.
     * The result can safely be inserted in a CSS url("...") declaration or in an HTML attribute.
     *
     * @param mixed $value The candidate.
     * @return string The URL or an empty string.
     * @since 3.8.15
     */
    protected static function sanitize_url($value) {
        if (!is_string($value)) {
            return '';
        }
        $value = trim($value);
        if ($value === 'self') {
            return $value;
        }
        $url = esc_url_raw($value, array('http', 'https'));
        if ($url === '' || preg_match('/["\\\\\s<>]/', $url)) {
            return '';
        }
        return str_replace(array('(', ')', "'"), array('%28', '%29', '%27'), $url);
    }

    /**
     * Get a CSS background-image declaration from a picture URL.
     *
     * @param string $url The URL.
     * @return string The CSS declaration, or an empty string if the URL is not acceptable.
     * @since 3.8.15
     */
    protected static function css_background($url) {
        $url = self::sanitize_url($url);
        if ($url === '' || $url === 'self') {
            return '';
        }
        return 'background-image: url("' . $url . '");';
    }

    /**
     * Sanitize the settings of a widget instance (same rules as in update() methods), whatever the widget is.
     *
     * @param array $instance The settings.
     * @return array The sanitized settings.
     * @since 3.8.15
     */
    protected static function sanitize_instance($instance) {
        $result = array();
        foreach ((array)$instance as $key => $val) {
            if (!is_string($key) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }
            switch ($key) {
                case 'subtitle':
                case 'format':
                case 'mode':
                case 'bg_opacity':
                case 'width':
                    $result[$key] = absint($val);
                    break;
                case 'bg_color':
                    $result[$key] = self::sanitize_color($val, '#444444');
                    break;
                case 'txt_color':
                    $result[$key] = self::sanitize_color($val, '#ffffff');
                    break;
                case 'station':
                case 'module':
                    $result[$key] = Guard::token($val, 'N/A');
                    break;
                default:
                    if (substr($key, -4) === '_url') {
                        $result[$key] = self::sanitize_url($val);
                    }
                    else {
                        $result[$key] = is_scalar($val) ? sanitize_text_field((string)$val) : '';
                    }
            }
        }
        return $result;
    }

    /**
     * Get the widget content via ajax.
     *
     * Design note: the widget markup is cached and served to every visitor, so no per-user nonce can be baked in it
     * (this is why the former 'lws_widget_nonce' check, which was verified but never created, always failed).
     * Instead, this public endpoint does not trust anything coming from the client: it only receives the number of
     * the widget instance and loads the settings saved by the administrator in the widget option. It is read-only and
     * rate-limited.
     *
     * @since 3.8.0
     */
    public static function lws_widget_callback() {
        live_weather_station_public_rate_limit('lws_widget_' . static::class);
        $widget = new static;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public read-only endpoint (see the docblock above): the number of the widget instance is the only value read, cast with absint(), and the settings come from the saved widget option; no state is modified.
        $number = (isset($_POST['wnum']) && is_scalar($_POST['wnum'])) ? absint($_POST['wnum']) : 0;
        $options = get_option('widget_' . $widget->id_base);
        if ($number < 1 || !is_array($options) || !isset($options[$number]) || !is_array($options[$number])) {
            status_header(404);
            exit ('');
        }
        $args = array('before_widget' => '', 'after_widget' => '', 'before_title' => '', 'after_title' => '');
        $instance = self::sanitize_instance($options[$number]);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget markup (it can hold script and canvas elements) built by widget_content() of the widget classes, which escape their own values; before_widget/after_widget come from the theme sidebar definition, as for any WordPress widget.
        echo $widget->widget_content($args, $instance);
        exit;
    }
}
