<?php

namespace WeatherStation\UI\Map;

use WeatherStation\Data\Output;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Arrays\Generator;
use WeatherStation\Data\ID\Handling as IDHandling;
use WeatherStation\System\Help\InlineHelp;



/**
 * This class builds elements of the map view.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
    private $map_type;
    private $map_information;
    private $map_params;
    private $aux_handler;
    private $arg_service;
    private $arg_tab;
    private $arg_action;
    private $service = 'Backend';
    private $init_common = array('loc_latitude' => 0, 'loc_longitude' => 0, 'loc_zoom' => 13);

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $maps The map screen.
     * @since 3.7.0
     */
    public function __construct($Live_Weather_Station, $version, $maps) {
        $page = filter_input(INPUT_GET, 'page');
        if (strpos($page, 'lws-') === false) {
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
        }

        if ($this->map_id === 0 && $this->arg_action === 'form' && $this->arg_tab === 'add-edit') {
            $this->map_id = $this->aux_handler->new_map($this->init_common);
        }
        if ($this->map_id !== 0) {
            $this->edit_map();
            $this->map_information = $this->get_map_detail($this->map_id);
            if (count($this->map_information) > 0) {
                $this->map_name = $this->map_information['name'];
                $this->map_type = $this->map_information['type'];
                $this->map_params = unserialize($this->map_information['params']);
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
        if (!($mid = filter_input(INPUT_GET, 'mid'))) {
            if (!($mid = filter_input(INPUT_POST, 'mid'))) {
                $mid = 0;
            }
        }
        $this->map_id = $mid;
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
     * Edit map details.
     *
     * @since 3.7.0
     */
    public function edit_map() {
       /* if ($this->arg_service == 'station' && $this->arg_tab == 'view' && $this->arg_action == 'manage') {
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
                            $update = true;
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
                            if (array_key_exists('do-export-data', $_POST)) {
                                $success = false;
                                if (array_key_exists('lws-date-start', $_POST) && array_key_exists('lws-date-end', $_POST) && array_key_exists('lws-format', $_POST)) {
                                    $args = array();
                                    $args['init'] = array();
                                    $args['init']['station_id'] = $station['station_id'];
                                    $args['init']['start_date'] = sanitize_text_field($_POST['lws-date-start']);
                                    $args['init']['end_date'] = sanitize_text_field($_POST['lws-date-end']);
                                    $format = sanitize_text_field(strtolower($_POST['lws-format']));
                                    $classname = 'Line' . ucfirst($format) . 'Exporter';
                                    ProcessManager::register($classname, $args);
                                    $success = true;
                                }
                                if ($success) {
                                    $message = lws__('Data export for the station %s has been launched. You will be notified by email of the end of treatment.', 'live-weather-station');
                                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                                    Logger::notice('Export Manager', null, $station['station_id'], $station['station_name'], null, null, null, 'Data export launched.');
                                }
                                else {
                                    $message = lws__('Unable to launch data export for the station %s.', 'live-weather-station');
                                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                    add_settings_error('lws_nonce_error', 200, $message, 'error');
                                    Logger::error('Export Manager', null, $station['station_id'], $station['station_name'], null, null, null, 'Unable to launch data export.');
                                }
                                $update = false;
                            }

                            if (array_key_exists('do-import-data', $_POST)) {
                                $success = false;
                                if (array_key_exists('lws-date-start', $_POST) && array_key_exists('lws-date-end', $_POST) && array_key_exists('lws-format', $_POST) && array_key_exists('lws-ndjson', $_POST)) {
                                    $args = array();
                                    $args['init'] = array();
                                    $args['init']['station_id'] = $station['station_id'];
                                    $args['init']['start_date'] = sanitize_text_field($_POST['lws-date-start']);
                                    $args['init']['end_date'] = sanitize_text_field($_POST['lws-date-end']);
                                    $args['init']['force'] = array_key_exists('lws-option-override', $_POST);
                                    $format = sanitize_text_field(strtolower($_POST['lws-format']));
                                    if ($format === 'netatmo' && $station['station_type'] == LWS_NETATMOHC_SID) {
                                        $format = 'NetatmoHC';
                                    }
                                    if ($format === 'netatmo' && $station['station_type'] == LWS_NETATMO_SID) {
                                        $format = 'NetatmoStation';
                                    }
                                    if ($format === 'pioupiou' && $station['station_type'] == LWS_PIOU_SID) {
                                        $format = 'Pioupiou';
                                    }
                                    if ($format === 'ndjson') {
                                        $args['init']['uuid'] = sanitize_text_field($_POST['lws-ndjson']);
                                        $format = 'LineNdjson';
                                    }
                                    $classname = $format . 'Importer';
                                    ProcessManager::register($classname, $args);
                                    $success = true;
                                }
                                if ($success) {
                                    $message = lws__('Data import for the station %s has been launched. You will be notified by email of the end of treatment.', 'live-weather-station');
                                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                    add_settings_error('lws_nonce_success', 200, $message, 'updated');
                                    Logger::notice('Import Manager', null, $station['station_id'], $station['station_name'], null, null, null, 'Data import launched.');
                                }
                                else {
                                    $message = lws__('Unable to launch data import for the station %s.', 'live-weather-station');
                                    $message = sprintf($message, '<em>' . $station['station_name'] . '</em>');
                                    add_settings_error('lws_nonce_error', 200, $message, 'error');
                                    Logger::error('Import Manager', null, $station['station_id'], $station['station_name'], null, null, null, 'Unable to launch data import.');
                                }
                                $update = false;
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
                            if ($update) {
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
                    }
                } else {
                    $message = lws__('Unable to perform this operation.', 'live-weather-station');
                    add_settings_error('lws_nonce_error', 403, $message, 'error');
                    Logger::critical('Security', null, null, null, null, null, 0, 'Inconsistent or inexistent security token in a backend form submission via HTTP/POST.');
                    Logger::error($this->service, null, null, null, null, null, 0, 'It had not been possible to securely perform an update for a station.');
                }
            }
        }*/
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
                    $result .= '<label for="' . $box_id . '-hide">';
                    $result .= '<input class="hide-postbox-tog" name="' . $box_id . '-hide" type="checkbox" id="' . $box_id . '-hide" value="' . $box_id . '"' . (!in_array($box_id, $hidden) ? ' checked="checked"' : '') . ' />';
                    $result .= $box['title'] . '</label>';
                }
            }
        }
        return $result;
    }

    /**
     * Get the full map.
     *
     * @since 3.7.0
     **/
    public function get() {
        echo '<div class="wrap">';
        echo '<h1>' . $this->map_name . '</h1>';
        settings_errors();
        echo '<form name="lws_map" method="post">';
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
        echo '</div>';
    }

    /**
     * Add all the needed meta boxes.
     *
     * @since 3.7.0
     */
    public function add_metaboxes() {
        // Left column
        add_meta_box('lws-maps', lws__('Map', 'live-weather-station' ), array($this, 'summary_widget'), $this->screen_id, 'advanced', 'default', array('map' => $this->map_information, 'params' => $this->map_params));
        /*if ($this->station_guid != 0) {
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
                if (LWS_WU_ACTIVE) {
                    add_meta_box('lws-sharing-wug', __('Sharing with Weather Underground', 'live-weather-station'), array($this, 'sharing_widget'), $this->screen_id, 'advanced', 'default', array('station' => $station, 'service' => 'wug'));
                }
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
        }*/
    }

    /**
     * Get content of the station widget box.
     *
     * @since 3.7.0
     */
    public function summary_widget($n, $args) {
        if (array_key_exists('map', $args['args']) && array_key_exists('params', $args['args'])) {
            $map_name = $args['args']['map']['name'];
            $map_location = $this->output_coordinate($args['args']['params']['common']['loc_latitude'], 'loc_latitude', 5, true);
            $map_location .= ' ⁛ ' . $this->output_coordinate($args['args']['params']['common']['loc_longitude'], 'loc_longitude', 5, true);
            $map_zoom = $args['args']['params']['common']['loc_zoom'];
            $map_icn = $this->output_iconic_value(0, 'map', false, false, 'style="color:#999"', 'fa-lg fa-fw');
            $location_icn = $this->output_iconic_value(0, 'location', false, false, 'style="color:#999"', 'fa-lg fa-fw');
            $zoom_icn = $this->output_iconic_value(0, 'zoom', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        }
        include(LWS_ADMIN_DIR.'partials/MapSummary.php');
    }

    /**
     * Get content of the location widget box.
     *
     * @since 3.7.0
     */
    public function location_widget($n, $args) {
        $station = array();
        if (array_key_exists('station', $args['args'])) {
            $station = $args['args']['station'];
            $station['txt_coordinates'] = $this->output_coordinate($station['loc_latitude'], 'loc_latitude', 5, true);
            $station['txt_coordinates'] .= ' ⁛ ' . $this->output_coordinate($station['loc_longitude'], 'loc_longitude', 5, true);
            $station['txt_altitude'] = $this->output_value($station['loc_altitude'], 'loc_altitude', true, true);
        }
        $location_icn = $this->output_iconic_value(0, 'location', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $altitude_icn = $this->output_iconic_value(0, 'altitude', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        include(LWS_ADMIN_DIR.'partials/StationLocation.php');
    }

    /**
     * Get content of the tools widget box.
     *
     * @since 3.5.0
     */
    public function tools_widget($n, $args) {
        $manage_link_icn = $this->output_iconic_value(0, 'module', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $manage_link = sprintf('<a href="?page=lws-stations&action=form&tab=manage&service=modules&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>'.__('Manage modules', 'live-weather-station').'</a>', $this->station_guid);
        $import_link_icn = $this->output_iconic_value(0, 'import', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $import_link = sprintf('<a href="?page=lws-stations&action=form&tab=import&service=data&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>'.lws__('Import historical data', 'live-weather-station').'</a>', $this->station_guid);
        $export_link_icn = $this->output_iconic_value(0, 'export', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $export_link = sprintf('<a href="?page=lws-stations&action=form&tab=export&service=data&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>'.lws__('Export historical data', 'live-weather-station').'</a>', $this->station_guid);
        include(LWS_ADMIN_DIR.'partials/StationTools.php');
    }

    /**
     * Get content of the datasharing widget box.
     *
     * @since 3.7.0
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
     * @since 3.7.0
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
                $url = 'https://www.wunderground.com/personal-weather-station/dashboard?ID=' . $station['wug_user'];
                break;
        }
        $target = ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank" ' : '');
        $shared = __('This station is currently shared on', 'live-weather-station') . ' <a href="' . $url . '"' . $target . '>' . __('this page', 'live-weather-station') . '</a>';
        include(LWS_ADMIN_DIR.'partials/StationSharing.php');
    }

    /**
     * Get content of the news box.
     *
     * @since 3.7.0
     */
    public function module_widget($n, $args) {
        $module = array();
        if (array_key_exists('module', $args['args'])) {
            $module = $args['args']['module'];
        }
        $module_icn = $this->output_iconic_value(0, 'module', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $last_seen_icn = $this->output_iconic_value(0, 'last_seen', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $firmware_icn = $this->output_iconic_value(0, 'firmware', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $setup_icn = $this->output_iconic_value(0, 'first_setup', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $refresh_icn = $this->output_iconic_value(0, 'refresh', false, false, 'style="color:#999"', 'fa-lg fa-fw');
        $static_display = true;
        include(LWS_ADMIN_DIR.'partials/StationModule.php');
    }
}