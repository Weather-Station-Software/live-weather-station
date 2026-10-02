<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Arrays\Generator;
use WeatherStation\Data\ID\Handling as IDHandling;
use WeatherStation\System\Help\InlineHelp;
use WeatherStation\System\Output\Guard;



/**
 * This class builds elements of the map view.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

class Handling {

    use IDHandling, Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_comparable_dimensions insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
        Output::get_dimension_name insteadof Generator;
        Output::get_operation_name insteadof Generator;
        Output::get_extension_description insteadof Generator;
    }

    private $Live_Weather_Station;
    private $version;
    private $screen;
    private $screen_id;
    private $map_id;
    private $map_name;
    private $map_type = 0;
    private $map_information;
    private $map_params;
    private $aux_handler;
    private $arg_service;
    private $arg_tab;
    private $arg_action;
    private $service = 'Backend';
    private $init_common = array('loc_latitude' => 51.476852, 'loc_longitude' => -0.000500, 'loc_zoom' => 4, 'refresh' => false, 'all' => true, 'width' => '100%', 'height' => '400px');  // Royal Observatory Greenwich, London, UK

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $maps The map screen.
     * @since 3.7.0
     */
    public function __construct($Live_Weather_Station, $version, $maps) {
        $page = (string)filter_input(INPUT_GET, 'page');
        if (strpos($page, 'lws-') === false) {
            return;
        }
        // This object is built for every admin user: nothing must be read or done without the capability.
        if (!current_user_can(live_weather_station_manage_capability())) {
            return;
        }
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        $this->get_args();

        switch ($this->arg_service) {
            case 'windy':
                $this->map_type = 1;
                $this->aux_handler = new WindyHandling();
                break;
            case 'stamen':
                $this->map_type = 2;
                $this->aux_handler = new StamenHandling();
                break;
            case 'thunderforest':
                $this->map_type = 3;
                $this->aux_handler = new ThunderforestHandling();
                break;
            case 'mapbox':
                $this->map_type = 4;
                $this->aux_handler = new MapboxHandling();
                break;
            case 'openweathermap':
                $this->map_type = 5;
                $this->aux_handler = new OpenweathermapHandling();
                break;
            case 'maptiler':
                $this->map_type = 6;
                $this->aux_handler = new MaptilerHandling();
                break;
            case 'navionics':
                $this->map_type = 7;
                $this->aux_handler = new StamenHandling(); // Retired Navionics maps are handled with the Stamen map (OpenStreetMap fallback).
                break;
            default:
                $this->map_type = 0;
                break;
        }

        // A map is created only with a valid nonce (bound to the map service) and never on a plain navigation.
        $creation_nonce = '';
        if (isset($_POST['_wpnonce']) && is_string($_POST['_wpnonce'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- This reads the nonce itself, it is verified by wp_verify_nonce() a few lines below.
            $creation_nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
        }
        elseif (isset($_GET['_wpnonce']) && is_string($_GET['_wpnonce'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- This reads the nonce itself, it is verified by wp_verify_nonce() a few lines below.
            $creation_nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
        }
        if ($this->map_id === 0 && $this->arg_action === 'form' && $this->arg_tab === 'add-edit' && $this->map_type != 0 && isset($this->aux_handler) && wp_verify_nonce($creation_nonce, 'lws-new-map-' . $this->arg_service)) {
            $barycenter = self::get_all_stations_barycenter();
            $this->init_common['loc_latitude'] = $barycenter['latitude'];
            $this->init_common['loc_longitude'] = $barycenter['longitude'];
            $this->map_id = $this->aux_handler->new_map($this->init_common);
        }
        if ($this->map_id !== 0) {
            $this->edit_map();
            $this->map_information = $this->get_map_detail($this->map_id);
            if (count($this->map_information) > 0) {
                $this->map_name = $this->map_information['name'];
                $this->map_type = $this->map_information['type'];
                $this->map_params = unserialize($this->map_information['params'], array('allowed_classes' => false));
                if (isset($this->aux_handler)) {
                    $this->aux_handler->set_map($this->map_information, '400px');
                }
            } else {
                //Logger::error('Export Manager', null, $station['station_id'], $station['station_name'], null, null, null, 'Unable to launch data export.');
            }
        }
        $this->screen = $maps;
        $this->screen_id = 'lws-map-' . $this->map_id;
        add_action('load-' . $maps, array($this, 'map_add_options'));
        add_action('admin_footer-' . $maps, array($this, 'map_add_footer'));
    }

    /**
     * Get all the args view.
     *
     * @since 3.7.0
     */
    private function get_args() {
        // phpcs:disable WordPress.Security.NonceVerification -- read-only: the map id only selects what is displayed, edit_map() verifies its own nonce before saving.
        $mid = 0;
        if (isset($_GET['mid'])) {
            $mid = absint(wp_unslash($_GET['mid']));
        }
        if (!$mid && isset($_POST['mid'])) {
            $mid = absint(wp_unslash($_POST['mid']));
        }
        $this->map_id = $mid;
        // phpcs:enable WordPress.Security.NonceVerification
        if (!($tab = filter_input(INPUT_POST, 'tab'))) {
            $tab = filter_input(INPUT_GET, 'tab');
        }
        if (!($action = filter_input(INPUT_POST, 'action'))) {
            $action = filter_input(INPUT_GET, 'action');
        }
        if (!($service = filter_input(INPUT_POST, 'service'))) {
            $service = filter_input(INPUT_GET, 'service');
        }
        $this->arg_tab = strtolower((string)$tab);
        $this->arg_action = strtolower((string)$action);
        $this->arg_service = strtolower((string)$service);
    }

    /**
     * Edit map details.
     *
     * @since 3.7.0
     */
    public function edit_map() {
       if (!current_user_can(live_weather_station_manage_capability())) {
           return;
       }
       if ($this->arg_service != 'map' && $this->arg_tab == 'add-edit' && $this->arg_action == 'form') {
            if (array_key_exists('lws-map-' . $this->map_id . '-nonce', $_POST)) {
                if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lws-map-' . $this->map_id . '-nonce'])), 'lws-map-' . $this->map_id)) {
                    if (array_key_exists('save-map', $_POST)) {
                        // The handler is chosen by the service of the URL: it must match the stored type of the map.
                        $detail = $this->get_map_detail($this->map_id);
                        if (isset($this->aux_handler) && is_array($detail) && count($detail) > 0 && isset($detail['type']) && (int)$detail['type'] === (int)$this->map_type) {
                            $this->aux_handler->set_map($detail, 'auto');
                            $this->aux_handler->save_map();
                            $message = __('This map has been correctly updated.', 'live-weather-station');
                            add_settings_error('lws_nonce_success', 200, $message, 'updated');
                            Logger::notice($this->service, $this->aux_handler->service, null, null, null, null, null, 'Map updated.');
                        }
                        else {
                            $message = __('Unable to update this map.', 'live-weather-station');
                            add_settings_error('lws_nonce_error', 403, $message, 'error');
                            Logger::error($this->service, null, null, null, null, null, null, 'Unable to update a map.');
                        }
                    }
                }
                else {
                    $message = __('Unable to perform this operation.', 'live-weather-station');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::critical('Security', null, null, null, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                    Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to securely perform an update for a map.');
                }
            }
        }
    }

    /**
     * Add options.
     *
     * @since 3.7.0
     */
    public function map_add_options() {
        $this->add_metaboxes();
    }

    /**
     * Add footer scripts.
     *
     * @since 3.7.0
     */
    public function map_add_footer() {
        $result = '';
        $jsInitId = md5(random_bytes(18));
        $result .= live_weather_station_print_begin_script($jsInitId);
        $result .= "    jQuery(document).ready( function($) {";
        $result .= "        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');";
        $result .= "        if(typeof postboxes !== 'undefined')";
        $result .= "            postboxes.add_postbox_toggles(" . Guard::js($this->screen_id) . ");";
        $result .= "        $('#common-station-selector').change(function() {";
        $result .= "            $('#stations-selector').prop('disabled', $('#common-station-selector').val() == 'all');";
        $result .= "        });";
        $result .= "        $('#common-station-selector').change()";
        $result .= "    });";
        $result .= live_weather_station_print_end_script($jsInitId);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $result is only made of the fixed script fragments written above, wrapped by live_weather_station_print_begin_script()/end_script(); the screen id is passed through Guard::js().
        echo $result;
    }

    /**
     * Prints the meta box preferences for map screen meta.
     *
     * @param string|\WP_Screen $screen Screen object or name.
     * @param boolean $module Optional. Module boxes only.
     * @return string The HTML code to append.
     * @since 3.7.0
     */
    public function meta_box_prefs($screen, $module=false) {
        global $wp_meta_boxes;
        $result = '';
        if (empty($wp_meta_boxes[$screen])) {
            return '';
        }
        $hidden = get_hidden_meta_boxes($screen);
        foreach (array_keys($wp_meta_boxes[$screen]) as $context) {
            foreach (array('high', 'core', 'default', 'low') as $priority) {
                if (!isset( $wp_meta_boxes[$screen][$context][$priority])) {
                    continue;
                }
                foreach ($wp_meta_boxes[$screen][$context][$priority] as $box) {
                    if (false == $box || !$box['title']) {
                        continue;
                    }
                    if ('submitdiv' == $box['id'] || 'linksubmitdiv' == $box['id']) {
                        continue;
                    }
                    if (strpos($box['id'], 's-module-s') > 0) {
                        if (!$module) {
                            continue;
                        }
                    }
                    else {
                        if ($module) {
                            continue;
                        }
                    }
                    $box_id = $box['id'];
                    $result .= '<label for="' . esc_attr($box_id) . '-hide">';
                    $result .= '<input class="hide-postbox-tog" name="' . esc_attr($box_id) . '-hide" type="checkbox" id="' . esc_attr($box_id) . '-hide" value="' . esc_attr($box_id) . '"' . (!in_array($box_id, $hidden) ? ' checked="checked"' : '') . ' />';
                    $result .= wp_kses_post($box['title']) . '</label>';
                }
            }
        }
        return $result;
    }

    /**
     * Get a box.
     *
     * @param string $id The box id.
     * @param string $title The box title.
     * @param string $content The box content.
     * @param string $footer Optional. The box footer.
     * @param string $special_footer Optional. A special footer for the box.
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    protected function get_box($id, $title, $content, $footer='', $special_footer='') {
        $result = '';
        $result .= '<div class="meta-box-sortables" style="width:100%;">';
        $result .= '<div class="postbox" id="' . esc_attr($id) . '" style="min-width:300px;">';
        $result .= '<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">' . esc_html__('Click to toggle', 'live-weather-station') . '</span><span class="toggle-indicator" aria-hidden="true"></span></button>';
        $result .= '<h3 class="hndle" style="cursor:default"><span>' . wp_kses_post($title) . '</span><span class="' . esc_attr($id) . '-spinner" style ="float: initial;margin-top:-4px;margin-bottom:-1px;"></span></h3>';
        $result .= '<div class="inside" style="text-align:center;">';
        $result .= $content;
        $result .= '</div>';
        if ($special_footer != '') {
            $result .= $special_footer;
        }
        if ($footer != '') {
            $result .= '<div id="major-publishing-actions">';
            $result .= '<div id="publishing-action">';
            $result .= $footer;
            $result .= '</div>';
            $result .= '<div class="clear"></div>';
            $result .= '</div>';
        }
        $result .= '</div>';
        $result .= '</div>';
        return $result;
    }

    /**
     * Get a box for shortcode text.
     *
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    protected function get_shortcode_box() {
        wp_enqueue_script('lws-clipboard');
        $id = 'lws-map-sc-' . $this->map_id;
        $jsInitId = md5(random_bytes(18));
        $result = live_weather_station_print_begin_script($jsInitId);
        $result .= 'jQuery(document).ready(function($) {';
        $result .= '  new ClipboardJS(".copy-sc-map-button");';
        $result .= '});';
        $result .= live_weather_station_print_end_script($jsInitId);
        $title = __('Shortcode', 'live-weather-station');
        $content = '<textarea readonly rows="1" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="' . esc_attr($id) . '">[live-weather-station-map id="' . esc_textarea($this->map_id) . '"]</textarea>';
        $footer = '<button data-clipboard-target="#' . esc_attr($id) . '" class="button button-primary copy-sc-map-button">' . esc_html__('Copy', 'live-weather-station'). '</button>';
        return $result . $this->get_box('lws-shortcode-id', $title, $content, $footer);
    }

    /**
     * Get the full map.
     *
     * @since 3.7.0
     **/
    public function get() {
        if (!is_array($this->map_information) || count($this->map_information) === 0 || !isset($this->aux_handler)) {
            // Unknown map id, invalid creation nonce or unknown service: nothing to edit or to preview.
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Map', 'live-weather-station') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('This map does not exist.', 'live-weather-station') . '</p></div>';
            echo '<p><a class="button button-primary" href="' . esc_url(live_weather_station_get_admin_page_url('lws-maps')) . '">' . esc_html__('Back to the maps list', 'live-weather-station') . '</a></p>';
            echo '</div>';
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->map_name) . '</h1>';
        if ($this->arg_tab === 'add-edit') {
            settings_errors();
            echo '<form name="lws-map" id="lws-map" method="post">';
            echo '<div id="dashboard-widgets-wrap">';
            wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
            wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
            wp_nonce_field('lws-map-' . $this->map_id, 'lws-map-' . $this->map_id . '-nonce', false);
            echo '<input name="mid" type="hidden" value="' . esc_attr($this->map_id) . '" />';
            echo '    <div id="dashboard-widgets" class="metabox-holder">';
            echo '        <div id="postbox-container-1" class="postbox-container">';
            do_meta_boxes($this->screen_id, 'advanced', null);
            echo '        </div>';
            echo '        <div id="postbox-container-2" class="postbox-container">';
            do_meta_boxes($this->screen_id, 'side', null);
            echo '        </div>';
            echo '        <div id="postbox-container-3" class="postbox-container">';
            do_meta_boxes($this->screen_id, 'column3', null);
            echo '        </div>';
            echo '        <div id="postbox-container-4" class="postbox-container">';
            do_meta_boxes($this->screen_id, 'column4', null);
            echo '        </div>';
            echo '    </div>';
            echo '</div>';
            echo '</form>';
        }
        echo '<div id="preview" class="metabox-holder" style="margin:-8px;">';
        echo '<div class="postbox-container" style="width:100%">';
        echo '<div class="main-boxes-container">';
        echo '<div class="row-boxes-container">';
        echo '<div class="item-boxes-container" id="lws-preview">';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- complete map block (form controls, script and style elements) built by the map handler classes (output*() of MapBaseHelper and of the per-provider helpers), which escape their dynamic values themselves; wp_kses_post() would strip the form controls and scripts. The title is escaped here.
        echo $this->get_box('map-preview', esc_html__('Preview (without size constraints)', 'live-weather-station'), $this->aux_handler->output());
        echo '</div>';
        echo '</div>';
        echo '<div class="row-boxes-container">';
        echo '<div class="item-boxes-container" id="lws-shortcode">';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_shortcode_box() builds a script block plus a textarea and a button, using esc_attr(), esc_textarea() and esc_html__() for every dynamic or translated part; kses would strip the script.
        echo $this->get_shortcode_box();
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.7.0
     */
    public function add_metaboxes() {
        if (isset($this->aux_handler) && is_array($this->map_information) && count($this->map_information) > 0) {
            // Left column
            add_meta_box('lws-maps', __('Map', 'live-weather-station' ), array($this, 'summary_widget'), $this->screen_id, 'advanced', 'default', array('map' => $this->map_information, 'params' => $this->map_params));
            add_meta_box('lws-misc', __('Misc', 'live-weather-station' ), array($this, 'detail_widget'), $this->screen_id, 'advanced', 'default', array('map' => $this->map_information, 'params' => $this->map_params));
            add_meta_box('lws-actions', __('Actions', 'live-weather-station' ), array($this, 'action_widget'), $this->screen_id, 'advanced', 'default', array('map' => $this->map_information, 'params' => $this->map_params));
            add_meta_box('lws-stations', __('Stations', 'live-weather-station' ), array($this, 'station_widget'), $this->screen_id, 'side', 'default', array('map' => $this->map_information, 'params' => $this->map_params));

            // Right column
            if ($this->aux_handler->has_feature()) {
                add_meta_box('lws-features', __('Features', 'live-weather-station' ), array($this, 'feature_widget'), $this->screen_id, 'column3', 'default', array('map' => $this->map_information, 'params' => $this->map_params));
            }
            if ($this->aux_handler->has_control()) {
                add_meta_box('lws-controls', __('Controls', 'live-weather-station' ), array($this, 'control_widget'), $this->screen_id, 'column4', 'default', array('map' => $this->map_information, 'params' => $this->map_params));
            }
        }
    }

    /**
     * Get content of the map box.
     *
     * @since 3.7.0
     */
    public function summary_widget($n, $args) {
        if (array_key_exists('map', $args['args']) && array_key_exists('params', $args['args'])) {
            $map_name = esc_html($args['args']['map']['name']);
            $map_location = $this->output_coordinate($args['args']['params']['common']['loc_latitude'], 'loc_latitude', 5, true);
            $map_location .= ' ⁛ ' . $this->output_coordinate($args['args']['params']['common']['loc_longitude'], 'loc_longitude', 5, true);
            $map_location = str_replace(' ', '&nbsp;', $map_location);
            $map_zoom = (int)$args['args']['params']['common']['loc_zoom'];
            $map_icn = $this->output_iconic_value(0, 'map', false, false, '#999');
            $location_icn = $this->output_iconic_value(0, 'location', false, false, '#999');
            $zoom_icn = $this->output_iconic_value(0, 'zoom', false, false, '#999');
        }
        include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/MapSummary.php');
    }

    /**
     * Get content of the map box.
     *
     * @since 3.7.0
     */
    public function action_widget($n, $args) {
        echo '<div style="text-align:center;"><input type="submit" name="save-map" id="save-map" class="button button-primary" value="' . esc_attr__('Save & Refresh Preview', 'live-weather-station') . '"  /></div>';
    }

    /**
     * Get content of the map detail box.
     *
     * @since 3.7.0
     */
    public function detail_widget($n, $args) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- complete map block (form controls, script and style elements) built by the map handler classes (output*() of MapBaseHelper and of the per-provider helpers), which escape their dynamic values themselves; wp_kses_post() would strip the form controls and scripts.
        echo $this->aux_handler->output_detail();
    }

    /**
     * Get content of the stations box.
     *
     * @since 3.7.0
     */
    public function station_widget($n, $args) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- complete map block (form controls, script and style elements) built by the map handler classes (output*() of MapBaseHelper and of the per-provider helpers), which escape their dynamic values themselves; wp_kses_post() would strip the form controls and scripts.
        echo $this->aux_handler->output_stations();
    }

    /**
     * Get content of the feature box.
     *
     * @since 3.7.0
     */
    public function feature_widget($n, $args) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- complete map block (form controls, script and style elements) built by the map handler classes (output*() of MapBaseHelper and of the per-provider helpers), which escape their dynamic values themselves; wp_kses_post() would strip the form controls and scripts.
        echo $this->aux_handler->output_feature();
    }

    /**
     * Get content of the control box.
     *
     * @since 3.7.0
     */
    public function control_widget($n, $args) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- complete map block (form controls, script and style elements) built by the map handler classes (output*() of MapBaseHelper and of the per-provider helpers), which escape their dynamic values themselves; wp_kses_post() would strip the form controls and scripts.
        echo $this->aux_handler->output_control();
    }
}