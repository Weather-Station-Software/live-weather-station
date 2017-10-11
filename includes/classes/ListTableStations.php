<?php

namespace WeatherStation\UI\ListTable;

use WeatherStation\Data\Output;
use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\Data\Arrays\Generator;

/**
 * Stations list table for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Stations extends Base {

    use Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
    }

    private $limit = 10;
    private $filters = array();
    private $active_guid = array();
    private $show_short_codes = false;
    private $show_publishing = false;
    private $show_sharing = false;
    private $show_time = false;


    public function __construct(){
        global $status, $page;
        parent::__construct(array('singular' => 'event', 'plural' => 'events', 'ajax' => true));
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    private function get_icon($type) {
        $result = '';
        switch ($type) {
            case LWS_NETATMO_SID :
            case LWS_NETATMOHC_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_netatmo_icon()) . '" />';
                break;
            case LWS_LOC_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_loc_icon('#666666')) . '" />';
                break;
            case LWS_OWM_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_owm_icon('#666666')) . '" />';
                break;
            case LWS_WUG_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_wug_icon('#666666')) . '" />';
                break;
            case LWS_RAW_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_raw_icon('#666666')) . '" />';
                break;
            case LWS_REAL_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_real_icon('#666666')) . '" />';
                break;
            case LWS_TXT_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_txt_icon('#666666')) . '" />';
                break;
            case LWS_WFLW_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_weatherflow_icon('#666666')) . '" />';
                break;
        }
        return $result;
    }

    protected function column_title($item){
        switch ($item['station_type']) {
            case LWS_NETATMO_SID :
            case LWS_NETATMOHC_SID :
                if (!(bool)get_option('live_weather_station_auto_manage_netatmo')) {
                    $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                }
                break;
            case LWS_LOC_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=Location&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
            case LWS_OWM_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=OpenWeatherMap&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
            case LWS_WUG_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=WeatherUnderground&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
            case LWS_RAW_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=clientraw&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
            case LWS_REAL_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=realtime&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
            case LWS_TXT_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=stickertags&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
            case LWS_WFLW_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=weatherflow&id=%s">'.__('Edit', 'live-weather-station').'</a>', $item['guid']);
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.__('Remove', 'live-weather-station').'</a>', $item['guid']);
                break;
        }
        $name = sprintf('<a class="row-title" href="?page=lws-stations&action=manage&tab=view&service=station&id=%s"' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>' . $item['station_name'] . '</a>', $item['guid']);
        $actions['log'] = sprintf('<a href="?page=lws-events&station=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>'.__('See events log', 'live-weather-station').'</a>', rawurlencode($item['station_id']));
        return $this->get_icon($item['station_type']) . '&nbsp;' . sprintf('%1$s <br /><span style="color:silver">&nbsp;%2$s, %3$s</span>%4$s', $name, $item['loc_city'], $item['country'], $this->row_actions($actions));
    }

    protected function column_location($item){
        $actions = array(
            'verify'    => sprintf('<a href="https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=%3$s/%1$s/%2$s"' . ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank"' : '') . '>'.__('Verify on a map', 'live-weather-station').'</a>',$item['loc_latitude'],$item['loc_longitude'], get_option('live_weather_station_map_zoom')),
        );
        return sprintf('%1$s - %2$s<br /><span style="color:silver">' . __('Altitude', 'live-weather-station') . ' %3$s</span>%4$s', $item['latitude'], $item['longitude'], $item['altitude'], $this->row_actions($actions));
    }

    /**
     * @param $item
     * @return string
     */
    protected function column_composition($item){
        if ($item['comp_bas'] > 0) {
            $comp[] = sprintf( _n('%s main base', '%s main bases', $item['comp_bas'], 'live-weather-station'), $item['comp_bas']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_ext'] > 0) {
            $comp[] = sprintf( _n('%s outdoor module', '%s outdoor modules', $item['comp_ext'], 'live-weather-station'), $item['comp_ext']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_int'] > 0) {
            $comp[] = sprintf( _n('%s indoor module', '%s indoor modules', $item['comp_int'], 'live-weather-station'), $item['comp_int']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_xtd'] > 0) {
            $comp[] = sprintf( _n('%s extra module', '%s extra modules', $item['comp_xtd'], 'live-weather-station'), $item['comp_xtd']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_vrt'] > 0) {
            $comp[] = sprintf( _n('%s virtual module', '%s virtual modules', $item['comp_vrt'], 'live-weather-station'), $item['comp_vrt']);
        }
        else {
            $comp[] = '';
        }
        $result = '';
        for ($i = 0; $i <= 4; $i++) {
            if ($result == '') {
                $result = $comp[$i];
            }
            else {
                if ($i < 4 && ($comp[$i] != '')) {
                    $follow = false;
                    for ($j = $i+1; $j <= 4; $j++) {
                        if ($comp[$j] != '') {
                            $follow = true;
                        }
                    }
                    if ($follow) {
                        $result .= ', ' . $comp[$i];
                    }
                    else {
                        $result .= ' ' . __('and', 'live-weather-station') . ' ' . $comp[$i];
                    }
                }
                else {
                    if ($comp[$i] != '') {
                        $result .= ' ' . __('and', 'live-weather-station') . ' ' . $comp[$i];
                    }
                }
            }
        }
        if ($result == '') {
            $result = __('none', 'live-weather-station');
        }
        else {
            $result = $result . '.';
        }
        return $result;
    }

    protected function column_data($item){
        $result = '';
        $share = implode(', ', $this->get_sharing_details($item));
        $publish = implode(', ',$this->get_publishing_details($item));
        if ($this->show_sharing && $share != '') {
            if ($result != '') {
                $result .= '<br/>';
            }
            $result .= __('Shared on:', 'live-weather-station') . ' ' . $share . '.';
        }
        if ($this->show_publishing && $publish != '') {
            if ($result != '') {
                $result .= '<br/>';
            }
            $result .= __('Published via:', 'live-weather-station') . ' ' .  $publish . '.';
        }
        return $result;
    }

    protected function column_time($item){
        $last_refresh_icn = $this->output_iconic_value(0, 'refresh', false, false, 'style="color:#999"', 'fa-lg');
        $last_refresh_txt = $this->output_value($item['last_refresh'], 'last_refresh', false, false, 'NAMain', $item['loc_timezone']);
        $last_refresh_diff_txt = ucfirst(self::get_positive_time_diff_from_mysql_utc($item['last_refresh']));
        $s = '<span style="width:100%;cursor: default;">' . $last_refresh_icn . '&nbsp;' . $last_refresh_txt . '</span><br/><span style="padding-left:28px;color:silver">' . $last_refresh_diff_txt . '</span><br/>';
        if ($item['last_seen'] != '0000-00-00 00:00:00') {
            $last_seen_icn = $this->output_iconic_value(0, 'last_seen', false, false, 'style="color:#999"', 'fa-lg');
            $last_seen_txt = $this->output_value($item['last_seen'], 'last_seen', false, false, 'NAMain', $item['loc_timezone']);
            $last_seen_diff_txt = ucfirst(self::get_positive_time_diff_from_mysql_utc($item['last_seen']));
            $s .= '<span style="width:100%;cursor: default;">' . $last_seen_icn . '&nbsp;' . $last_seen_txt . '</span><br/><span style="padding-left:28px;color:silver">' . $last_seen_diff_txt . '</span>';
        }
        return $s;
    }

    protected function column_sc($item){
        if (($item['comp_bas'] + $item['comp_ext'] + $item['comp_int'] + $item['comp_xtd'] + $item['comp_vrt']) == 0) {
            return '';
        }
        $this->active_guid[] = $item['guid'];
        $s = '<a href="#" id="textual-datas-link-' . $item['guid'] . '">' . ucfirst(__('textual datas', 'live-weather-station')) . '</a>, ';
        $s .= '<a href="#" id="lcd-datas-link-' . $item['guid'] . '">' . __('LCD display', 'live-weather-station') . '</a>, ';
        $s .= '<a href="#" id="justgage-datas-link-' . $item['guid'] . '">' . __('clean gauge', 'live-weather-station') . '</a>, ';
        $s .= '<a href="#" id="steelmeter-datas-link-' . $item['guid'] . '">' . __('steel meter', 'live-weather-station') . '</a>.';
        return $s;
    }

    public function get_columns(){
        $columns = array('title' => __('Station', 'live-weather-station'),
            'location' => __('Location', 'live-weather-station'),
            'composition' => __('Composition', 'live-weather-station'),
            'data' => __('Data', 'live-weather-station'));
        if ($this->show_short_codes) {
            $columns['sc'] = __('Shortcodes', 'live-weather-station');
        }
        if ($this->show_time) {
            $columns['time'] = __('Freshness', 'live-weather-station');
        }
        return $columns;
    }

    protected function get_hidden_columns() {
        return array();
    }

    protected function get_sortable_columns() {
        $sortable_columns = array('title' => array('station_name',false));
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        return array();
    }

    protected function init_values() {
        $this->filters = array();
        if (isset($_GET['limit'])) {
            $this->limit = intval($_GET['limit']);
            if (!$this->limit) {
                $this->limit = 10;
            }
        }
    }

    public function usort_reorder($a,$b){
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'station_name';
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
        $result = strcmp(strtolower($a[$orderby]), strtolower($b[$orderby]));
        return ($order==='asc') ? $result : -$result;
    }

    public function prepare_items() {
        $data = $this->get_stations_list();
        $this->show_short_codes = count($data) < 3;
        $count_share = 0;
        $count_publish = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $count_share += $item['owm_sync'] + $item['pws_sync'] + $item['wow_sync'] + $item['wet_sync'] + $item['wug_sync'];
                $count_publish += $item['txt_sync'] + $item['raw_sync'] + $item['real_sync'] + $item['yow_sync'];
            }
        }
        $this->show_sharing = $count_share > 0;
        $this->show_publishing = $count_publish > 0;
        $this->show_time = true;
        $this->init_values();
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        if (count($data) > 0) {
            foreach ($data as &$item) {
                $item['country'] = $this->get_country_name($item['loc_country_code']);
                $item['tz'] = $this->output_timezone($item['loc_timezone']);
                $item['altitude'] = $this->output_value($item['loc_altitude'], 'loc_altitude', true) ;
                $item['latitude'] = $this->output_coordinate($item['loc_latitude'], 'loc_latitude', 6);
                $item['longitude'] = $this->output_coordinate($item['loc_longitude'], 'loc_longitude', 6);
            }
        }
        usort($data, array($this, 'usort_reorder'));
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$this->limit),$this->limit);
        $this->items = $data;
        $this->active_guid = array();
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page' => $this->limit, 'total_pages' => ceil($total_items/$this->limit)));
    }

    private function get_page_url($filters) {
        $args = array('page' => 'lws-stations', 'view' => 'list-table-stations');
        if (count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                if ($filter != '') {
                    $args[$key] = $filter;
                }
            }
        }
        if ($this->limit != 10) {
            $args['limit'] = $this->limit;
        }
        $url = add_query_arg($args, admin_url('admin.php'));
        return $url;
    }

    public function extra_tablenav($which) {
        $list = $this;
        $args = compact('list');
        foreach ($args as $key => $val) {
            $$key = $val;
        }
        if ($which == 'bottom'){
            include(LWS_ADMIN_DIR.'partials/ListTableStationsBottom.php');
            if ($this->show_short_codes) {
                $js_array_textual = $this->get_all_stations_array(true, false, false, true, false, $this->active_guid);
                $js_array_icon = $this->get_all_stations_array(true, false, false, true, false, $this->active_guid);
                $js_array_lcd = $this->get_all_stations_array(false, true, true, false, false, $this->active_guid);
                $js_array_justgage = $this->get_all_stations_array(false, false, true, true, true, $this->active_guid);
                $js_array_steelmeter = $this->get_all_stations_array(false, false, true, true, false, $this->active_guid);

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

                foreach ($js_array_textual as $guid => $station) {
                    $station_guid = $guid;
                    if (isset($station[0])) {
                        $station_name = $station[0];
                    }
                    if (isset($station[1])) {
                        $station_id = $station[1];
                    }
                    if (in_array($guid, $this->active_guid)) {
                        include(LWS_ADMIN_DIR . 'partials/ShortcodesTextual.php');
                        include(LWS_ADMIN_DIR . 'partials/ShortcodesJustgage.php');
                        include(LWS_ADMIN_DIR . 'partials/ShortcodesLCD.php');
                        include(LWS_ADMIN_DIR . 'partials/ShortcodesSteelmeter.php');
                    }
                }
            }
        }
    }

    public function get_line_number_select() {
        $_disp = [10, 20, 30];
        $result = array();
        foreach ($_disp as $d) {
            $l = array();
            $l['value'] = $d;
            $l['text'] = sprintf(esc_html__('Show %d lines per page', 'live-weather-station'), $d);
            $l['selected'] = ($d == $this->limit ? 'selected="selected" ' : '');
            $result[] = $l;
        }
        return $result;
    }
}