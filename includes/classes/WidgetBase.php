<?php

namespace WeatherStation\UI\Widget;

use WeatherStation\System\Cache\Cache;

/**
 * Outdoor weather widget class for Weather Station plugin
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
        lws_font_awesome();
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
        if ((bool)get_option('live_weather_station_ajax_widget', 1) && !is_admin()) {
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
                $result .= lws_print_begin_script($jsInitId) . PHP_EOL;
                $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
                $result .= '    $.post( "' . LWS_AJAX_URL . '", {action: "lws_w_' . $widget . '", ' . str_replace('\'', '\\\'', $this->outputvar($args, $instance)) . '}).done(function(data) {$("#' . $uniq . '").html(data);})';
                $result .= '  });' . PHP_EOL;
                $result .= lws_print_end_script($jsInitId);
                $result .= $after;
                Cache::set_widget($cache_id, $result);
            }
            echo $result;
        }
        else {
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
                if (is_bool($arg)) {
                    $result .= $key . ':' . ($arg?'true':'false') . ', ';
                    continue;
                }
                if (is_numeric($arg)) {
                    $result .= $key . ':' . $arg . ', ';
                    continue;
                }
                if (is_string($arg)) {
                    $result .= $key . ':"' . str_replace('"', '\"', $arg) . '", ';
                    continue;
                }
            }
        }
        return $result;
    }

    /**
     * Get the widget content via ajax.
     *
     * @since 3.8.0
     */
    public static function lws_widget_callback() {
        $widget = new static;
        $args = array();
        if (array_key_exists('before_widget', $_POST)) {
            $args['before_widget'] = wp_kses($_POST['before_widget'], array());
        }
        else {
            $args['before_widget'] = '';
        }
        if (array_key_exists('after_widget', $_POST)) {
            $args['after_widget'] = wp_kses($_POST['after_widget'], array());
        }
        else {
            $args['after_widget'] = '';
        }
        if (array_key_exists('before_title', $_POST)) {
            $args['before_title'] = wp_kses($_POST['before_title'], array());
        }
        else {
            $args['before_title'] = '';
        }
        if (array_key_exists('after_title', $_POST)) {
            $args['after_title'] = wp_kses($_POST['after_title'], array());
        }
        else {
            $args['after_title'] = '';
        }
        $instance = array();
        $excluded = array('action', 'before_widget', 'after_widget', 'before_title', 'after_title');
        foreach ($_POST as $key => $val) {
            if (!in_array($key, $excluded)) {
                $instance[$key] = $val;
            }
        }
        exit ($widget->widget_content($args, $instance));
    }
}


