<?php

namespace WeatherStation\UI\Station;

use WeatherStation\Data\Output;
use WeatherStation\Engine\Page\Standalone\Framework;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Arrays\Generator;

use WeatherStation\SDK\OpenWeatherMap\Plugin\Pusher as OWM_Pusher;
use WeatherStation\SDK\PWSWeather\Plugin\Pusher as PWS_Pusher;
use WeatherStation\SDK\MetOffice\Plugin\Pusher as WOW_Pusher;
use WeatherStation\SDK\WeatherUnderground\Plugin\Pusher as WUG_Pusher;

/**
 * This class builds elements of the station view.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

class Handling {

    use Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
    }

    private $Live_Weather_Station;
    private $version;
    private $screen;
    private $screen_id;
    private $station_id;
    private $station_name;
    private $service = 'Backend';
    private $publishable = array(LWS_NETATMO_SID, LWS_LOC_SID, LWS_OWM_SID, LWS_WUG_SID);
    private $sharable = array(LWS_NETATMO_SID, LWS_RAW_SID, LWS_REAL_SID);

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $station The station screen.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version, $station) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        if (!($id = filter_input(INPUT_GET, 'id'))) {
            if (!$id = filter_input(INPUT_POST, 'id')) {
                $id = 0;
            }
        }
        if (strpos($id, ':') > 0) {
            $id = $this->get_station_guid_by_station_id($id);
        }
        $this->station_id = $id;

        if ($id != 0) {
            $this->edit_station();
            $this->station_name = $this->get_infos_station_name_by_guid($id);
            $this->screen = $station;
            $this->screen_id = 'lws-station-' . $id;
        }
        add_action('load-' . $station, array($this, 'station_add_options'));
        add_action('admin_footer-' . $station, array($this, 'station_add_footer'));
        add_filter('screen_settings', array($this, 'append_screen_settings'), 10, 2);
    }

    /**
     * Edit station details.
     *
     * @since 3.0.0
     */
    public function edit_station() {
        if (!($tab = filter_input(INPUT_POST, 'tab'))) {
            $tab = filter_input(INPUT_GET, 'tab');
        }
        if (!($action = filter_input(INPUT_POST, 'action'))) {
            $action = filter_input(INPUT_GET, 'action');
        }
        if (!($service = filter_input(INPUT_POST, 'service'))) {
            $service = filter_input(INPUT_GET, 'service');
        }
        if ($service == 'station' && $tab == 'edit' && $action == 'manage') {
            $station = array();
            if (wp_verify_nonce((array_key_exists('_wpnonce', $_POST) ? $_POST['_wpnonce'] : ''), 'edit-station')) {
                if (array_key_exists('guid', $_POST)) {
                    $guid = stripslashes(htmlspecialchars_decode($_POST['guid']));
                    $save = false;
                    $connect = false;
                    $owm = false;
                    $wug = false;
                    $pws = false;
                    $wow = false;
                    //Logger::debug(null, null, null, null, null, null, null, print_r($_POST, true));
                    if (($guid != 0) && ($guid == $this->station_id)) {
                        $station = $this->get_station_informations_by_guid($guid);
                        if (array_key_exists('submit-publish', $_POST)) {
                            if (array_key_exists('txt_sync', $_POST)) {
                                $station['txt_sync'] = 1;
                            } else {
                                $station['txt_sync'] = 0;
                            }
                            $save = true;
                        }
                        if (array_key_exists('wow-unshare', $_POST)) {
                            $station['wow_sync'] = 0;
                            $station['wow_user'] = '';
                            $station['wow_password'] = '';
                            $save = true;
                        }
                        if (array_key_exists('pws-unshare', $_POST)) {
                            $station['pws_sync'] = 0;
                            $station['pws_user'] = '';
                            $station['pws_password'] = '';
                            $save = true;
                        }
                        if (array_key_exists('wug-unshare', $_POST)) {
                            $station['wug_sync'] = 0;
                            $station['wug_user'] = '';
                            $station['wug_password'] = '';
                            $save = true;
                        }
                        if (array_key_exists('wow-share', $_POST)) {
                            if (array_key_exists('user', $_POST) && array_key_exists('password', $_POST)) {
                                $station['wow_user'] = stripslashes(htmlspecialchars_decode($_POST['user']));
                                $station['wow_password'] = stripslashes(htmlspecialchars_decode($_POST['password']));
                                $station['wow_sync'] = 1;
                                $wow = true;
                                $connect = true;
                            }
                        }
                        if (array_key_exists('pws-share', $_POST)) {
                            if (array_key_exists('user', $_POST) && array_key_exists('password', $_POST)) {
                                $station['pws_user'] = stripslashes(htmlspecialchars_decode($_POST['user']));
                                $station['pws_password'] = stripslashes(htmlspecialchars_decode($_POST['password']));
                                $station['pws_sync'] = 1;
                                $pws = true;
                                $connect = true;
                            }
                        }
                        if (array_key_exists('wug-share', $_POST)) {
                            if (array_key_exists('user', $_POST) && array_key_exists('password', $_POST)) {
                                $station['wug_user'] = stripslashes(htmlspecialchars_decode($_POST['user']));
                                $station['wug_password'] = stripslashes(htmlspecialchars_decode($_POST['password']));
                                $station['wug_sync'] = 1;
                                $wug = true;
                                $connect = true;
                            }
                        }
                        if ($connect) {
                            $this->update_stations_table($station);
                            $datas = array();
                            $save = true;
                            $service_name = null;
                            $result = 'unknown reason...';
                            try {
                                if ($owm) {
                                    $push = new OWM_Pusher();
                                    if (($result = $push->push_data(array($station))) != '') {
                                        $station['owm_sync'] = 0;
                                        $station['owm_user'] = '';
                                        $station['owm_password'] = '';
                                        $save = false;
                                        $service_name = 'OpenWeatherMap';
                                    }
                                }
                                if ($wug) {
                                    $push = new WUG_Pusher();
                                    if (($result = $push->push_data(array($station))) != '') {
                                        $station['wug_sync'] = 0;
                                        $station['wug_user'] = '';
                                        $station['wug_password'] = '';
                                        $save = false;
                                        $service_name = 'Weather Underground';
                                    }
                                }
                                if ($wow) {
                                    $push = new WOW_Pusher();
                                    if (($result = $push->push_data(array($station))) != '') {
                                        $station['wow_sync'] = 0;
                                        $station['wow_user'] = '';
                                        $station['wow_password'] = '';
                                        $save = false;
                                        $service_name = 'WOW Met Office';
                                    }
                                }
                                if ($pws) {
                                    $push = new PWS_Pusher();
                                    if (($result = $push->push_data(array($station))) != '') {
                                        $station['pws_sync'] = 0;
                                        $station['pws_user'] = '';
                                        $station['pws_password'] = '';
                                        $save = false;
                                        $service_name = 'PWS Weather';
                                    }
                                }
                               if (!$save) {
                                   $message = __('Unable to activate data sharing with %s.', 'live-weather-station');
                                   $message = sprintf($message, '<em>' . $service_name . '</em>');
                                   add_settings_error('lws_nonce_error', 403, $message, 'error');
                                   Logger::error($this->service, $service_name, $station['station_id'], $station['station_name'], null, null, null, 'Unable to share data with this service: ' . $result);
                               }
                            }
                            catch (\Exception $ex) {
                                //error_log(LWS_PLUGIN_NAME . ' / ' . LWS_VERSION . ' / ' . get_class() . ' / ' . get_class($this) . ' / Error code: ' . $ex->getCode() . ' / Error message: ' . $ex->getMessage());
                            }
                        }
                        if ($this->update_stations_table($station)) {
                            if ($save) {
                                $message = __('The station %s has been correctly updated.', 'live-weather-station');
                                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                add_settings_error('lws_nonce_success', 200, $message, 'updated');
                                Logger::notice($this->service, null, $station['station_id'], $station['station_name'], null, null, null, 'Station updated.');
                                Framework::apply_configuration();
                            }
                            else {
                                $message = __('Unable to update the station %s.', 'live-weather-station');
                                $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                add_settings_error('lws_nonce_error', 403, $message, 'error');
                                Logger::error($this->service, null, $station['station_id'], $station['station_name'], null, null, null, 'Unable to update the station.');
                            }
                        }
                        else {
                            $message = __('Unable to update the station %s.', 'live-weather-station');
                            $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                            add_settings_error('lws_nonce_error', 403, $message, 'error');
                            Logger::error($this->service, null, $station['station_id'], $station['station_name'], null, null, null, 'Unable to update the station.');
                        }
                    }
                }
            } else {
                $message = __('Unable to perform this update.', 'live-weather-station');
                add_settings_error('lws_nonce_error', 403, $message, 'error');
                Logger::critical('Security', null, null, null, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to securely perform an update for a station.');
            }
        }
    }

    /**
     * Add options.
     *
     * @since 3.0.0
     */
    public function station_add_options() {
        $this->add_metaboxes();
    }

    /**
     * Add footer scripts.
     *
     * @since 3.0.0
     */
    public function station_add_footer() {
        $result = '';
        $result .= '<script type="text/javascript">';
        $result .= "    jQuery(document).ready( function($) {";
        $result .= "        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');";
        $result .= "        if(typeof postboxes !== 'undefined')";
        $result .= "            postboxes.add_postbox_toggles('" . $this->screen_id . "');";
        $result .= "    });";
        $result .= '</script>';
        echo $result;
    }

    /**
     * Append custom panel HTML to the "Screen Options" box of the current page.
     * Callback for the 'screen_settings' filter.
     *
     * @param string $current Current content.
     * @param \WP_Screen $screen Screen object.
     * @return string The HTML code to append to "Screen Options"
     */
    public function append_screen_settings($current, $screen){
        if (!isset($screen->id)) {
            return $current;
        }
        $s = convert_to_screen($this->screen);
        if ($screen->id !== $s->id) {
            return $current;
        }
        if ($this->station_id == 0) {
            return $current;
        }
        $current .= '<div id="lws_station" class="metabox-prefs custom-options-panel requires-autosave"><input type="hidden" name="_wpnonce-lws_station" value="' . wp_create_nonce('save_settings_lws_station') . '" />';
        $current .= $this->get_options();
        $current .= '</div>';
        return $current ;
    }

    /**
     * Get the box options.
     *
     * @return string The HTML code to append.
     * @since 3.0.0
     */
    public function get_options() {
        $result = '<fieldset class="metabox-prefs">';
        $result .= '<legend>' . __('Boxes', 'live-weather-station') . '</legend>';
        $result .= $this->meta_box_prefs($this->screen_id);
        $result .= '<legend>' . __('Modules', 'live-weather-station') . '</legend>';
        $result .= $this->meta_box_prefs($this->screen_id, true);
        $result .= '</fieldset>';
        return $result;
    }

    /**
     * Prints the meta box preferences for station screen meta.
     *
     * @param string|\WP_Screen $screen Screen object or name.
     * @param boolean $module Optional. Module boxes only.
     * @return string The HTML code to append.
     * @since 3.0.0
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
                    if (false == $box || ! $box['title']) {
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
                    $result .= '<label for="' . $box_id . '-hide">';
                    $result .= '<input class="hide-postbox-tog" name="' . $box_id . '-hide" type="checkbox" id="' . $box_id . '-hide" value="' . $box_id . '"' . (!in_array($box_id, $hidden) ? ' checked="checked"' : '') . ' />';
                    $result .= $box['title'] . '</label>';
                }
            }
        }
        return $result;
    }

    /**
     * Get the full station.
     *
     * @since 3.0.0
     **/
    public function get() {
        echo '<div class="wrap">';
        echo '<h1>' . $this->station_name . '</h1>';
        settings_errors();
        echo '<form name="lws_station" method="post">';
        echo '<div id="dashboard-widgets-wrap">';
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
        echo '    <div id="dashboard-widgets" class="metabox-holder">';
        echo '        <div id="postbox-container-1" class="postbox-container">';
        do_meta_boxes($this->screen_id,'advanced',null);
        echo '        </div>';
        echo '        <div id="postbox-container-2" class="postbox-container">';
        do_meta_boxes($this->screen_id,'side',null);
        echo '        </div>';
        echo '        <div id="postbox-container-3" class="postbox-container">';
        do_meta_boxes($this->screen_id,'column3',null);
        echo '        </div>';
        echo '        <div id="postbox-container-4" class="postbox-container">';
        do_meta_boxes($this->screen_id,'column4',null);
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.0.0
     */
    public function add_metaboxes() {
        if ($this->station_id != 0) {
            $data = $this->get_all_formated_datas($this->station_id);
            $station = $data['station'];
            $gid = strtolower(str_replace(':', '', $station['station_id']));
            // Left column
            add_meta_box('lws-station', __('Station', 'live-weather-station' ), array($this, 'station_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
            add_meta_box('lws-location', __('Location', 'live-weather-station' ), array($this, 'location_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
            add_meta_box('lws-shortcodes', __('Shortcodes', 'live-weather-station' ), array($this, 'shortcodes_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
            if (in_array($station['station_type'], $this->publishable)) {
                add_meta_box('lws-datapublishing', __('Data publishing', 'live-weather-station' ), array($this, 'publishing_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
            }
            if (in_array($station['station_type'], $this->sharable)) {
                add_meta_box('lws-sharing-wow', __('Sharing with Met Office', 'live-weather-station'), array($this, 'sharing_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station, 'service' => 'wow'));
                add_meta_box('lws-sharing-pws', __('Sharing with PWS Weather', 'live-weather-station'), array($this, 'sharing_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station, 'service' => 'pws'));
                add_meta_box('lws-sharing-wug', __('Sharing with Weather Underground', 'live-weather-station'), array($this, 'sharing_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station, 'service' => 'wug'));
            }
            // Right column
            if (count($data) > 0) {
                if (count($data['module']) > 0) {
                    foreach ($data['module'] as $m) {
                        $id = 'lws-module-s' . $gid . '-m' . strtolower(str_replace(':', '', $m['module_id']));
                        add_meta_box($id, $m['module_name'], array($this, 'module_widget'), $this->screen_id, 'side', 'default', array('module' => $m));
                    }
                }
            }
        }
    }

    /**
     * Get content of the station widget box.
     *
     * @since 3.0.0
     */
    public function station_widget($n, $args) {
        $station = array();
        if (array_key_exists('station', $args['args'])) {
            $station = $args['args']['station'];
            $station['txt_location'] = $station['loc_city'] . ', ' . $this->get_country_name($station['loc_country_code']);
            $station['txt_timezone'] = $this->output_timezone($station['loc_timezone']);
        }
        $station_name_icn = $this->output_iconic_value(0, 'station_name', false, false, 'style="color:#999"', 'fa-lg');
        $location_icn = $this->output_iconic_value(0, 'city', false, false, 'style="color:#999"', 'fa-lg');
        $timezone_icn = $this->output_iconic_value(0, 'timezone', false, false, 'style="color:#999"', 'fa-lg');
        include(LWS_ADMIN_DIR.'partials/StationStation.php');
    }

    /**
     * Get content of the location widget box.
     *
     * @since 3.0.0
     */
    public function location_widget($n, $args) {
        $station = array();
        if (array_key_exists('station', $args['args'])) {
            $station = $args['args']['station'];
            $station['txt_coordinates'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 5, true);
            $station['txt_coordinates'] .= ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 5, true);
            $station['txt_altitude'] = $this->output_value($station['loc_altitude'], 'loc_altitude', true, true);
        }
        $location_icn = $this->output_iconic_value(0, 'location', false, false, 'style="color:#999"', 'fa-lg');
        $altitude_icn = $this->output_iconic_value(0, 'altitude', false, false, 'style="color:#999"', 'fa-lg');
        include(LWS_ADMIN_DIR.'partials/StationLocation.php');
    }

    /**
     * Get content of the datasharing widget box.
     *
     * @since 3.0.0
     */
    public function publishing_widget($n, $args) {
        $station = array();
        if (array_key_exists('station', $args['args'])) {
            $station = $args['args']['station'];
        }
        include(LWS_ADMIN_DIR.'partials/StationPublishing.php');
    }

    /**
     * Get content of the short codes widget box.
     *
     * @since 3.0.0
     */
    public function shortcodes_widget($n, $args) {
        $station = array();
        if (array_key_exists('station', $args['args'])) {
            $station = $args['args']['station'];
            $station_guid = $station['guid'];
            $station_name = $station['station_name'];
            $station_id = $station['station_id'];
            $guids = array($station_guid);

            include(LWS_ADMIN_DIR.'partials/StationShortcodes.php');

            $js_array_textual = $this->get_all_stations_array(true, false, false, true, false, $guids);
            $js_array_icon = $this->get_all_stations_array(true, false, false, true, false, $guids);
            $js_array_lcd = $this->get_all_stations_array(false, true, true, false, false, $guids);
            $js_array_justgage = $this->get_all_stations_array(false, false, true, true, true, $guids);
            $js_array_steelmeter = $this->get_all_stations_array(false, false, true, true, false, $guids);

            $js_array_justgage_design = $this->get_justgage_design_js_array();
            $js_array_justgage_color = $this->get_justgage_color_js_array();
            $js_array_justgage_pointer = $this->get_justgage_pointer_js_array();
            $js_array_justgage_title = $this->get_justgage_title_js_array();
            $js_array_justgage_unit = $this->get_justgage_unit_js_array();
            $js_array_justgage_size = $this->get_size_js_array(true);
            $js_array_justgage_background = $this->get_justgage_background_js_array();

            $js_array_lcd_design = $this->get_lcd_design_js_array();
            $js_array_lcd_size = $this->get_size_js_array();
            $js_array_lcd_speed = $this->get_lcd_speed_js_array();

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

            include(LWS_ADMIN_DIR.'partials/ShortcodesTextual.php');
            include(LWS_ADMIN_DIR.'partials/ShortcodesJustgage.php');
            include(LWS_ADMIN_DIR.'partials/ShortcodesLCD.php');
            include(LWS_ADMIN_DIR.'partials/ShortcodesSteelmeter.php');
        }
    }

    /**
     * Get content of the publishing on... widget box.
     *
     * @since 3.0.0
     */
    public function sharing_widget($n, $args) {
        $station = array();
        if (array_key_exists('station', $args['args'])) {
            $station = $args['args']['station'];
        }
        $service = null;
        if (array_key_exists('service', $args['args'])) {
            $service = $args['args']['service'];
        }
        $connected = $station[$service.'_sync'];
        $user = $station[$service.'_user'];
        $password = $station[$service.'_password'];
        switch ($service) {
            case 'wow':
                $f1 = __('Site ID', 'live-weather-station');
                $f2 = __('Authentication key', 'live-weather-station');
                $url = 'http://wow.metoffice.gov.uk/weather/view?siteID=' . $station['wow_user'];
                break;
            case 'pws':
                $f1 = __('Station ID', 'live-weather-station');
                $f2 = __('Password', 'live-weather-station');
                $url = 'http://www.pwsweather.com/obs/' . $station['pws_user'] . '.html';
                break;
            case 'wug':
                $f1 = __('Station ID', 'live-weather-station');
                $f2 = __('Password', 'live-weather-station');
                $url = 'https://www.wunderground.com/personal-weather-station/dashboard?ID=' . $station['wug_user'] . '&apiref=d97bd03904cd49c5';
                break;
        }
        $target = ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank" ' : '');
        $shared = __('This station is currently shared on', 'live-weather-station') . ' <a href="' . $url . '"' . $target . '>' . __('this page', 'live-weather-station') . '</a>';
        include(LWS_ADMIN_DIR.'partials/StationSharing.php');
    }

    /**
     * Get content of the news box.
     *
     * @since 3.0.0
     */
    public function module_widget($n, $args) {
        $module = array();
        if (array_key_exists('module', $args['args'])) {
            $module = $args['args']['module'];
        }
        $module_icn = $this->output_iconic_value(0, 'module', false, false, 'style="color:#999"', 'fa-lg');
        $last_seen_icn = $this->output_iconic_value(0, 'last_seen', false, false, 'style="color:#999"', 'fa-lg');
        $firmware_icn = $this->output_iconic_value(0, 'firmware', false, false, 'style="color:#999"', 'fa-lg');
        $setup_icn = $this->output_iconic_value(0, 'first_setup', false, false, 'style="color:#999"', 'fa-lg');
        $refresh_icn = $this->output_iconic_value(0, 'refresh', false, false, 'style="color:#999"', 'fa-lg');
        include(LWS_ADMIN_DIR.'partials/StationModule.php');
    }
}