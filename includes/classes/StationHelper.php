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

use WeatherStation\Engine\Module\Maintainer as ModuleMaintainer;
use WeatherStation\Engine\Module\Current\Gauge;
use WeatherStation\Engine\Module\Current\Lcd;
use WeatherStation\Engine\Module\Current\Meter;
use WeatherStation\Engine\Module\Current\Textual;
use WeatherStation\Engine\Module\Daily\AStream as DailyAStream;
use WeatherStation\Engine\Module\Daily\DistributionRC as DailyDistributionRC;
use WeatherStation\Engine\Module\Daily\ValueRC as DailyValueRC;
use WeatherStation\Engine\Module\Daily\Line as DailyLine;
use WeatherStation\Engine\Module\Daily\DoubleLine as DailyDoubleLine;
use WeatherStation\Engine\Module\Daily\Lines as DailyLines;
use WeatherStation\Engine\Module\Daily\Windrose as DailyWindrose;
use WeatherStation\Engine\Module\Yearly\AStream as YearlyAStream;
use WeatherStation\Engine\Module\Yearly\CStick as YearlyCStick;
use WeatherStation\Engine\Module\Yearly\Line as YearlyLine;
use WeatherStation\Engine\Module\Yearly\DoubleLine as YearlyDoubleLine;
use WeatherStation\Engine\Module\Yearly\Bar as YearlyBar;
use WeatherStation\Engine\Module\Yearly\Bars as YearlyBars;
use WeatherStation\Engine\Module\Yearly\CalendarHM as YearlyCalendarHM;
use WeatherStation\Engine\Module\Yearly\BCLine as YearlyBCLine;
use WeatherStation\Engine\Module\Yearly\DistributionRC as YearlyDistributionRC;
use WeatherStation\Engine\Module\Yearly\ValueRC as YearlyValueRC;
use WeatherStation\Engine\Module\Yearly\Lines as YearlyLines;
use WeatherStation\Engine\Module\Yearly\StackedAreas as YearlyStackedAreas;
use WeatherStation\Engine\Module\Yearly\Windrose as YearlyWindrose;
use WeatherStation\System\Plugin\Deactivator;
use WeatherStation\System\Device\Manager as DeviceManager;


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
        Output::get_comparable_dimensions insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
        Output::get_dimension_name insteadof Generator;
        Output::get_operation_name insteadof Generator;
    }

    private $Live_Weather_Station;
    private $version;
    private $screen;
    private $screen_id;
    private $station_guid;
    private $station_id;
    private $station_name;
    private $station_information;
    private $arg_service;
    private $arg_tab;
    private $arg_action;
    private $service = 'Backend';
    private $publishable = array(LWS_NETATMO_SID, LWS_LOC_SID, LWS_OWM_SID, LWS_RAW_SID, LWS_REAL_SID, LWS_WUG_SID, LWS_WFLW_SID);
    private $sharable = array(LWS_NETATMO_SID, LWS_RAW_SID, LWS_REAL_SID, LWS_WFLW_SID);
    private $publishing_proto = array('txt', 'raw', 'real', 'yow');

    /**
     * Register all available modules.
     *
     * @since 3.4.0
     */
    private function register_modules() {
        Textual::register_module('current');
        Gauge::register_module('current');
        Lcd::register_module('current');
        Meter::register_module('current');
        DailyLine::register_module('daily');
        DailyLines::register_module('daily');
        DailyDoubleLine::register_module('daily');
        DailyWindrose::register_module('daily');
        DailyDistributionRC::register_module('daily');
        DailyValueRC::register_module('daily');
        DailyAStream::register_module('daily');
        YearlyLine::register_module('yearly');
        YearlyBar::register_module('yearly');
        YearlyCStick::register_module('yearly');
        YearlyLines::register_module('yearly');
        YearlyBars::register_module('yearly');
        YearlyDoubleLine::register_module('yearly');
        YearlyBCLine::register_module('yearly');
        YearlyCalendarHM::register_module('yearly');
        YearlyStackedAreas::register_module('yearly');
        YearlyWindrose::register_module('yearly');
        YearlyDistributionRC::register_module('yearly');
        YearlyValueRC::register_module('yearly');
        YearlyAStream::register_module('yearly');
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $station The station screen.
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version, $station) {
        $page = filter_input(INPUT_GET, 'page');
        if (strpos($page, 'lws-') === false) {
            return;
        }
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
        $this->register_modules();
        $this->get_args();
        if ($this->station_guid != 0) {
            $this->edit_station();
            $this->station_information = $this->get_station_informations_by_guid($this->station_guid);
            $this->station_name = $this->station_information['station_name'];
            $this->station_id = $this->station_information['station_id'];
            $this->screen = $station;
            $pref = 'lws-station-';
            if ($this->arg_action == 'shortcode') {
                $pref .= 'shortcode-';
                if ($this->arg_service != '') {
                    $pref .= $this->arg_service . '-';
                }
            }
            $this->screen_id = $pref . $this->station_guid;
        }
        if ($this->arg_action == 'manage') {
            add_action('load-' . $station, array($this, 'station_add_options'));
            add_action('admin_footer-' . $station, array($this, 'station_add_footer'));
            add_filter('screen_settings', array($this, 'append_screen_settings'), 10, 2);
        }
        else {
            add_action('admin_footer-' . $station, array($this, 'station_add_footer'));
        }
    }

    /**
     * Get the module array.
     *
     * @return array An array containing the available instanciated modules;
     * @since 3.4.0
     */
    private function get_modules() {
        $result = array();
        foreach (ModuleMaintainer::get_modules($this->arg_tab) as $class){
            $module = new $class($this->station_information);
            if ($this->arg_service == $module->get_id()) {
                $module->select();
            }
            $result[] = $module;
        }
        return $result;
    }

    /**
     * Get the inline help for module.
     *
     * @param string $type The type of module to view.
     * @return string The inline help, ready to insert.
     * @since 3.4.0
     */
    public function get_help_modules($type) {
        $result = '';
        foreach (ModuleMaintainer::get_modules($type) as $class){
            $module = new $class($this->station_information);
            $result .= '<p><i style="color:#666666" class="' . $module->get_icon() . '"></i>&nbsp;<strong>' . ucfirst($module->get_name()) . '</strong> &mdash; ' . $module->get_hint() . '</p>';
        }
        return $result;
    }

    /**
     * Get all the args view.
     *
     * @since 3.4.0
     */
    private function get_args() {
        if (!($id = filter_input(INPUT_GET, 'id'))) {
            if (!($id = filter_input(INPUT_POST, 'id'))) {
                $id = 0;
            }
        }
        if (strpos($id, ':') > 0) {
            $id = $this->get_station_guid_by_station_id($id);
        }
        $this->station_guid = $id;
        if (!($tab = filter_input(INPUT_POST, 'tab'))) {
            $this->arg_tab = filter_input(INPUT_GET, 'tab');
        }
        if (!($action = filter_input(INPUT_POST, 'action'))) {
            $this->arg_action = filter_input(INPUT_GET, 'action');
        }
        if (!($service = filter_input(INPUT_POST, 'service'))) {
            $this->arg_service = filter_input(INPUT_GET, 'service');
        }
        $this->arg_tab = strtolower($this->arg_tab);
        $this->arg_action = strtolower($this->arg_action);
        $this->arg_service = strtolower($this->arg_service);
    }

    /**
     * Edit station details.
     *
     * @since 3.0.0
     */
    public function edit_station() {
        if ($this->arg_service == 'station' && $this->arg_tab == 'view' && $this->arg_action == 'manage') {
            $station = array();
            if (array_key_exists('_wpnonce', $_POST)) {
                if (wp_verify_nonce($_POST['_wpnonce'], 'edit-station')) {
                    if (array_key_exists('guid', $_POST)) {
                        $guid = stripslashes(htmlspecialchars_decode($_POST['guid']));
                        $save = false;
                        $connect = false;
                        $owm = false;
                        $wug = false;
                        $pws = false;
                        $wow = false;
                        if (($guid != 0) && ($guid == $this->station_guid)) {
                            $station = $this->get_station_informations_by_guid($guid);
                            if (array_key_exists('submit-publish', $_POST)) {
                                foreach ($this->publishing_proto as $proto) {
                                    if (array_key_exists($proto . '_sync', $_POST)) {
                                        $station[$proto . '_sync'] = 1;
                                    } else {
                                        $station[$proto . '_sync'] = 0;
                                    }
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
                            if (array_key_exists('do-manage-modules', $_POST)) {
                                $m = array();
                                $modules = array();
                                foreach ($_POST as $key => $p) {
                                    if (strpos($key, 'lws-name-') === 0) {
                                        $k = str_replace('lws-name-', '', $key);
                                        if (!array_key_exists($k, $m)) {
                                            $m[$k] = array();
                                        }
                                        $m[$k]['screen_name'] = (string)stripslashes(htmlspecialchars_decode($p));
                                    }
                                    if (strpos($key, 'lws-hidden-') === 0) {
                                        $k = str_replace('lws-hidden-', '', $key);
                                        if (!array_key_exists($k, $m)) {
                                            $m[$k] = array();
                                        }
                                        $m[$k]['hidden'] = (integer)stripslashes(htmlspecialchars_decode($p));
                                    }
                                }
                                if (count($m) > 0) {
                                    foreach ($m as $k => $module) {
                                        $add = array();
                                        $add['device_id'] = $station['station_id'];
                                        $add['module_id'] = $k;
                                        if ($module['screen_name'] != '') {
                                            $add['screen_name'] = $module['screen_name'];
                                        }
                                        $add['hidden'] = $module['hidden'];
                                        $modules[] = $add;
                                    }
                                    if (count($modules) > 0) {
                                        DeviceManager::set_modules_details($modules);
                                        $save = true;
                                    }
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
                                } catch (\Exception $ex) {
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
                                } else {
                                    $message = __('Unable to update the station %s.', 'live-weather-station');
                                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                                    Logger::error($this->service, null, $station['station_id'], $station['station_name'], null, null, null, 'Unable to update the station.');
                                }
                            } else {
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
        if ($this->station_guid == 0) {
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
        include(LWS_ADMIN_DIR.'partials/StationTab.php');
        settings_errors();
        if ($this->arg_action == 'manage') {
            echo '<form name="lws_station" method="post">';
            echo '<div id="dashboard-widgets-wrap">';
            wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
            wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
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
        if ($this->arg_action == 'shortcode') {
            wp_enqueue_style('lws-font-chart-icons');
            wp_enqueue_script('lws-clipboard');
            $modules = $this->get_modules();
            echo '<div id="dashboard-widgets-wrap">';
            echo '    <div id="shortcodes-widgets" class="metabox-holder">';
            echo '        <div id="shortcodes-container" class="postbox-container" style="width:100%">';
            include(LWS_ADMIN_DIR.'partials/ChooseModuleType.php');
            foreach ($modules as $module) {
                if ($module->is_selected()) {
                    $module->print_form();
                }
            }
            echo '        </div>';
            echo '    </div>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.0.0
     */
    public function add_metaboxes() {
        if ($this->station_guid != 0) {
            $data = $this->get_all_formated_datas($this->station_guid);
            $station = $data['station'];
            $gid = strtolower(str_replace(':', '', $station['station_id']));
            // Left column
            add_meta_box('lws-station', __('Station', 'live-weather-station' ), array($this, 'station_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
            add_meta_box('lws-location', __('Location', 'live-weather-station' ), array($this, 'location_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
            add_meta_box('lws-tools', __('Tools', 'live-weather-station' ), array($this, 'tools_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station));
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
            if ($station['oldest_data'] != '0000-00-00') {
                $station['oldest_data_txt'] = __('Oldest data from', 'live-weather-station') . ' ' .$this->output_value($station['oldest_data'], 'oldest_data', false, false, 'NAMain', $station['loc_timezone']);
                $station['oldest_data_diff_txt'] = self::get_positive_time_diff_from_mysql_utc($station['oldest_data']);
            }
            else {
                $station['oldest_data_txt'] = false;
            }
        }
        $station_name_icn = $this->output_iconic_value(0, 'station_name', false, false, 'style="color:#999"', 'fa-lg');
        $location_icn = $this->output_iconic_value(0, 'city', false, false, 'style="color:#999"', 'fa-lg');
        $timezone_icn = $this->output_iconic_value(0, 'timezone', false, false, 'style="color:#999"', 'fa-lg');
        $histo_icn = $this->output_iconic_value(0, 'historical', false, false, 'style="color:#999"', 'fa-lg');
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
     * Get content of the tools widget box.
     *
     * @since 3.5.0
     */
    public function tools_widget($n, $args) {
        $manage_link_icn = $this->output_iconic_value(0, 'module', false, false, 'style="color:#999"', 'fa-lg');
        $manage_link = sprintf('<a href="?page=lws-stations&action=form&tab=manage&service=modules&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>'.__('Manage modules', 'live-weather-station').'</a>', $this->station_guid);
        include(LWS_ADMIN_DIR.'partials/StationTools.php');
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
        $static_display = true;
        include(LWS_ADMIN_DIR.'partials/StationModule.php');
    }
}