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
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Stations extends Base {

    use Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_comparable_dimensions insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
        Output::get_dimension_name insteadof Generator;
        Output::get_operation_name insteadof Generator;
        Output::get_extension_description insteadof Generator;
    }

    private $limit = 10;
    private $filters = array();
    private $active_guid = array();
    private $show_publishing = false;
    private $show_sharing = false;
    private $show_time = false;


    public function __construct(){
        global $status, $page;
        parent::__construct(array('singular' => 'station', 'plural' => 'stations', 'ajax' => true));
    }

    protected function column_default($item, $column_name){
        return esc_html($item[$column_name]);
    }

    private function get_icon($type) {
        $result = '';
        switch ($type) {
            case LIVE_WEATHER_STATION_NETATMO_SID :
            case LIVE_WEATHER_STATION_NETATMOHC_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_netatmo_icon()) . '" />';
                break;
            case LIVE_WEATHER_STATION_LOC_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_loc_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_OWM_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_owm_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_WUG_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_wug_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_RAW_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_raw_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_REAL_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_real_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_TXT_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_txt_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_WFLW_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_weatherflow_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_PIOU_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_piou_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_BSKY_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_bloomsky_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_AMBT_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_ambient_icon('#666666')) . '" />';
                break;
            case LIVE_WEATHER_STATION_WLINK_SID :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_weatherlink_icon('#666666')) . '" />';
                break;
        }
        return $result;
    }

    protected function column_title($item){
        $notice = '';
        $actions['see'] = sprintf('<a href="?page=lws-stations&action=manage&tab=view&service=station&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>'.esc_html__('View', 'live-weather-station').'</a>', rawurlencode($item['station_id']));
        switch ($item['station_type']) {
            case LIVE_WEATHER_STATION_NETATMO_SID :
            case LIVE_WEATHER_STATION_NETATMOHC_SID :
                if (!(bool)get_option('live_weather_station_auto_manage_netatmo')) {
                    $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                }
                break;
            case LIVE_WEATHER_STATION_BSKY_SID :
                // BloomSky stopped its service in 2022: the stored data are kept, the station can only be viewed or removed.
                $notice = '<br /><span style="color:#b32d2e">&nbsp;' . esc_html__('Service no longer available', 'live-weather-station') . '</span>';
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_LOC_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=Location&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_OWM_SID :
            case LIVE_WEATHER_STATION_WUG_SID :
                // Collection services removed: the station can only be viewed or removed.
                $notice = '<br /><span style="color:#b32d2e">&nbsp;' . esc_html__('Service no longer available', 'live-weather-station') . '</span>';
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_RAW_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=clientraw&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_REAL_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=realtime&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_TXT_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=stickertags&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_WFLW_SID :
                $wflw_parts = explode(LIVE_WEATHER_STATION_SERVICE_SEPARATOR, (string)$item['service_id'], 2);
                if (count($wflw_parts) < 2 || $wflw_parts[1] === '') {
                    $notice = '<br /><span style="color:#b32d2e">&nbsp;' . esc_html__('Personal access token required', 'live-weather-station') . '</span>';
                }
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=weatherflow&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_PIOU_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=pioupiou&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_AMBT_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=ambient&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
            case LIVE_WEATHER_STATION_WLINK_SID :
                $actions['edit'] = sprintf('<a href="?page=lws-stations&action=form&tab=add-edit&service=weatherlink&id=%s">'.esc_html__('Modify', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                $actions['delete'] = sprintf('<a href="?page=lws-stations&action=form&tab=delete&service=station&id=%s">'.esc_html__('Remove', 'live-weather-station').'</a>', rawurlencode($item['guid']));
                break;
        }
        $name = sprintf('<a class="row-title" href="?page=lws-stations&action=manage&tab=view&service=station&id=%s"' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>%s</a>', rawurlencode($item['guid']), esc_html($item['station_name']));
        $actions['log'] = sprintf('<a href="?page=lws-events&station=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>'.__('Browse events', 'live-weather-station').'</a>', rawurlencode($item['station_id']));
        return $this->get_icon($item['station_type']) . '&nbsp;' . sprintf('%1$s <br /><span style="color:silver">&nbsp;%2$s, %3$s</span>%5$s%4$s', $name, esc_html($item['loc_city']), esc_html($item['country']), $this->row_actions($actions), $notice);
    }

    protected function column_location($item){
        $actions = array(
            'verify'    => sprintf('<a href="https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=%3$s/%1$s/%2$s"' . ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank" rel="noopener noreferrer"' : '') . '>'.esc_html__('Verify on a map', 'live-weather-station').'</a>',rawurlencode($item['loc_latitude']),rawurlencode($item['loc_longitude']), (int)get_option('live_weather_station_map_zoom')),
        );
        return sprintf('%1$s - %2$s<br /><span style="color:silver">' . esc_html__('Altitude', 'live-weather-station') . ' %3$s</span>%4$s', $item['latitude'], $item['longitude'], $item['altitude'], $this->row_actions($actions));
    }

    /**
     * @param $item
     * @return string
     */
    protected function column_composition($item){
        if ($item['comp_bas'] > 0) {
            $comp[] = sprintf( /* translators: %s: number of main bases of the station */ _n('%s main base', '%s main bases', $item['comp_bas'], 'live-weather-station'), $item['comp_bas']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_ext'] > 0) {
            $comp[] = sprintf( /* translators: %s: number of outdoor modules of the station */ _n('%s outdoor module', '%s outdoor modules', $item['comp_ext'], 'live-weather-station'), $item['comp_ext']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_int'] > 0) {
            $comp[] = sprintf( /* translators: %s: number of indoor modules of the station */ _n('%s indoor module', '%s indoor modules', $item['comp_int'], 'live-weather-station'), $item['comp_int']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_xtd'] > 0) {
            $comp[] = sprintf( /* translators: %s: number of extra modules of the station */ _n('%s extra module', '%s extra modules', $item['comp_xtd'], 'live-weather-station'), $item['comp_xtd']);
        }
        else {
            $comp[] = '';
        }
        if ($item['comp_vrt'] > 0) {
            $comp[] = sprintf( /* translators: %s: number of virtual modules of the station */ _n('%s virtual module', '%s virtual modules', $item['comp_vrt'], 'live-weather-station'), $item['comp_vrt']);
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
        $actions = array(
            sprintf('<a href="?page=lws-stations&action=form&tab=manage&service=modules&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>'.esc_html__('Manage modules', 'live-weather-station').'</a>', rawurlencode($item['guid'])),
        );
        return sprintf('%1$s %2$s', $result, $this->row_actions($actions));
    }

    protected function column_shortcode($item){
        $c = sprintf('<a href="?page=lws-stations&action=shortcode&tab=current&service=station&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>'.esc_html__('Current records', 'live-weather-station').'</a>', rawurlencode($item['guid']));
        $d = sprintf('<a href="?page=lws-stations&action=shortcode&tab=daily&service=station&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>'.live_weather_station_lcfirst(esc_html__('Daily data', 'live-weather-station')).'</a>', rawurlencode($item['guid']));
        $y = sprintf('<a href="?page=lws-stations&action=shortcode&tab=yearly&service=station&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>'.live_weather_station_lcfirst(esc_html__('Historical data', 'live-weather-station')).'</a>', rawurlencode($item['guid']));
        $cc = sprintf('<a href="?page=lws-stations&action=shortcode&tab=climat&service=station&id=%s" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . '>'.live_weather_station_lcfirst(esc_html__('Climatological data', 'live-weather-station')).'</a>', rawurlencode($item['guid']));
        return $c . ', ' . $d . ', ' . $y . ', ' . $cc . '.';
    }

    protected function column_data($item){
        $result = '';
        $share = implode(', ', $this->get_sharing_details($item));
        $publish = implode(', ',$this->get_publishing_details($item));
        if ($this->show_sharing && $share != '') {
            if ($result != '') {
                $result .= '<br/>';
            }
            $result .= esc_html__('Shared on:', 'live-weather-station') . ' ' . $share . '.';
        }
        if ($this->show_publishing && $publish != '') {
            if ($result != '') {
                $result .= '<br/>';
            }
            $result .= esc_html__('Published via:', 'live-weather-station') . ' ' .  $publish . '.';
        }
        return $result;
    }

    protected function column_time($item){
        $result = '';
        if (array_key_exists('last_refresh', $item) && $item['last_refresh'] != '0000-00-00 00:00:00') {
            $last_refresh_icn = $this->output_iconic_value(0, 'refresh', false, false, '#999');
            $last_refresh_txt = $this->output_value($item['last_refresh'], 'last_refresh', false, false, 'NAMain', $item['loc_timezone']);
            $last_refresh_diff_txt = ucfirst(self::get_positive_time_diff_from_mysql_utc($item['last_refresh']));
            $result .= '<span style="width:100%;cursor: default;">' . $last_refresh_icn . '&nbsp;' . $last_refresh_txt . '</span><br/><span style="padding-left:28px;color:silver">' . $last_refresh_diff_txt . '</span><br/>';
        }
        if (array_key_exists('last_seen', $item) && $item['last_seen'] != '0000-00-00 00:00:00') {
            $last_seen_icn = $this->output_iconic_value(0, 'last_seen', false, false, '#999');
            $last_seen_txt = $this->output_value($item['last_seen'], 'last_seen', false, false, 'NAMain', $item['loc_timezone']);
            $last_seen_diff_txt = ucfirst(self::get_positive_time_diff_from_mysql_utc($item['last_seen']));
            $result .= '<span style="width:100%;cursor: default;">' . $last_seen_icn . '&nbsp;' . $last_seen_txt . '</span><br/><span style="padding-left:28px;color:silver">' . $last_seen_diff_txt . '</span>';
        }
        return $result;
    }

    public function get_columns(){
        $columns = array('title' => __('Station', 'live-weather-station'),
            'location' => __('Location', 'live-weather-station'),
            'composition' => __('Composition', 'live-weather-station'),
            'shortcode' => __('Shortcodes', 'live-weather-station'),
            'data' => __('Data', 'live-weather-station'));
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
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination parameter, nothing is modified.
        if (isset($_GET['limit'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination parameter, cast to integer.
            $this->limit = intval($_GET['limit']);
            if (!$this->limit) {
                $this->limit = 10;
            }
        }
    }

    public function usort_reorder($a,$b){
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter, restricted to existing column keys below.
        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_key($_REQUEST['orderby']) : 'station_name';
        if (!array_key_exists($orderby, $a)) {
            $orderby = 'station_name';
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter, reduced to 'asc' or 'desc'.
        $order = (!empty($_REQUEST['order']) && strtolower(sanitize_text_field(wp_unslash($_REQUEST['order']))) === 'desc') ? 'desc' : 'asc';
        $result = strcmp(strtolower($a[$orderby]), strtolower($b[$orderby]));
        return ($order==='asc') ? $result : -$result;
    }

    public function prepare_items() {
        $data = $this->get_stations_list();
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
            include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/ListTableStationsBottom.php');
        }
    }

    public function get_line_number_select() {
        $_disp = [10, 20, 30];
        $result = array();
        foreach ($_disp as $d) {
            $l = array();
            $l['value'] = $d;
            $l['text'] = sprintf(/* translators: %d: number of lines per page */ esc_html__('Show %d lines per page', 'live-weather-station'), $d);
            $l['selected'] = ($d == $this->limit ? 'selected="selected" ' : '');
            $result[] = $l;
        }
        return $result;
    }
}