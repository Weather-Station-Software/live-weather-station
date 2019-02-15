<?php

namespace WeatherStation\UI\ListTable;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Output;
use WeatherStation\UI\SVG\Handling as SVG;

/**
 * Maps list table for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class Maps extends Base {

    use Output;

    private $limit = 25;
    private $stations = array();

    public function __construct(){
        global $status, $page;
        parent::__construct(array('singular' => 'map', 'plural' => 'maps', 'ajax' => true));
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    private function get_icon($type) {
        $result = '';
        switch ($type) {
            case 'windy' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_windy_grey_logo()) . '" />';
                break;
            case 'stamen' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_stamen_grey_logo()) . '" />';
                break;
            case 'thunderforest' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_thunderforest_grey_logo()) . '" />';
                break;
            case 'mapbox' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_mapbox_grey_logo()) . '" />';
                break;
            case 'maptiler' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_maptiler_grey_logo()) . '" />';
                break;
            case 'navionics' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_navionics_grey_logo()) . '" />';
                break;
            case 'openweathermap' :
                $result = '<img style="width:34px;float:left;padding-right:6px;" src="' . set_url_scheme(SVG::get_base64_owm_grey_logo()) . '" />';
                break;
        }
        return $result;
    }


    protected function column_map($item){
        $type = strtolower($this->get_service_name(100 + $item['type']));
        $actions['view'] = sprintf('<a href="?page=lws-maps&action=form&tab=view&service=' . $type . '&mid=%s">'.__('View', 'live-weather-station').'</a>', $item['id']);
        $actions['edit'] = sprintf('<a href="?page=lws-maps&action=form&tab=add-edit&service=' . $type . '&mid=%s">'.__('Modify', 'live-weather-station').'</a>', $item['id']);
        $actions['delete'] = sprintf('<a href="?page=lws-maps&action=form&tab=delete&service=map&mid=%s">'.__('Remove', 'live-weather-station').'</a>', $item['id']);
        $id = sprintf(__('Map ID #%s'), $item['id']);
        $name = sprintf('<a class="row-title" href="?page=lws-maps&action=form&tab=add-edit&service=' . $type . '&mid=%s"' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . '>' . $item['name'] . '</a>', $item['id']);
        return $this->get_icon($type) . '&nbsp;' . sprintf('%1$s <br /><span style="color:silver">&nbsp;%2$s</span>%3$s', $name, $id, $this->row_actions($actions));
    }

    protected function column_stations($item){
        $result = '-';
        $params = unserialize($item['params']);
        if (!$params['common']['all']) {
            if (array_key_exists('stations', $params)) {
                $list = $params['stations'];
                if (count($list) > 0) {
                    $r = array();
                    foreach ($this->stations as $station) {
                        if (in_array($station['guid'], $list)) {
                            $r[] = $station['station_name'];
                        }
                    }
                    if (count($r) > 0) {
                        $result = implode(', ', $r);
                    }
                }
            }
        }
        else {
            $result = '- ' . __('all', 'live-weather-station') . ' -';
        }
        return $result;
    }

    protected function column_zoom($item){
        $params = unserialize($item['params']);
        if (array_key_exists('common', $params)) {
            if (array_key_exists('loc_zoom', $params['common'])) {
                return $params['common']['loc_zoom'];
            }
        }
        return '-';
    }

    protected function column_size($item){
        $params = unserialize($item['params']);
        $width = '-';
        $height = '-';
        if (array_key_exists('common', $params)) {
            if (array_key_exists('width', $params['common'])) {
                $width = $params['common']['width'];
            }
        }
        if (array_key_exists('common', $params)) {
            if (array_key_exists('height', $params['common'])) {
                $height = $params['common']['height'];
            }
        }
        $s = '<span style="color:silver">' . __('Width:', 'live-weather-station') . '</span>&nbsp;' . $width . '<br/>';
        $s .= '<span style="color:silver">' . __('Height:', 'live-weather-station') . '</span>&nbsp;' . $height;
        return $s;
    }

    protected function column_center($item){
        $params = unserialize($item['params']);
        if (array_key_exists('common', $params)) {
            if (array_key_exists('loc_latitude', $params['common'])) {
                $lat = $this->output_coordinate($params['common']['loc_latitude'], 'loc_latitude', 6);
            }
            else {
                $lat = '-';
            }
            if (array_key_exists('loc_longitude', $params['common'])) {
                $lon = $this->output_coordinate($params['common']['loc_longitude'], 'loc_longitude', 6);
            }
            else {
                $lon = '-';
            }
        }
        return $lat . ' ⁛ ' . $lon;
    }

    public function get_columns(){
        $columns = array(
            'map' => __('Map', 'live-weather-station'),
            'center' => __('Center', 'live-weather-station'),
            'zoom' => __('Zoom', 'live-weather-station'),
            'size' => __('Size', 'live-weather-station'),
            'stations' => __('Stations', 'live-weather-station'));
        return $columns;
    }

    protected function get_hidden_columns() {
        return array();
    }

    public function usort_reorder($a,$b){
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'name';
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
        $result = strcmp(strtolower($a[$orderby]), strtolower($b[$orderby]));
        return ($order==='asc') ? $result : -$result;
    }

    protected function get_sortable_columns() {
        $sortable_columns = array('map' => array('map',false));
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
                $this->limit = 25;
            }
        }
    }

    public function prepare_items() {
        $this->init_values();
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->stations = $this->get_stations_informations();
        $data = $this->get_all_maps();
        usort($data, array($this, 'usort_reorder'));
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$this->limit),$this->limit);
        $this->items = $data;
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page' => $this->limit, 'total_pages' => ceil($total_items/$this->limit)));
    }

    private function get_page_url($filters) {
        $args = array('page' => 'lws-maps', 'view' => 'list-table-maps');
        if (count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                if ($filter != '') {
                    $args[$key] = $filter;
                }
            }
        }
        if ($this->limit != 25) {
            $args['limit'] = $this->limit;
        }
        $url = add_query_arg($args, admin_url('admin.php'));
        return $url;
    }

    public function get_views() {
        return '';
    }

    public function extra_tablenav($which) {
        $list = $this;
        $args = compact('list');
        if ($which == 'bottom'){
            include(LWS_ADMIN_DIR.'partials/ListTableMapsBottom.php');
        }
    }

    public function get_line_number_select() {
        $_disp = [25, 50, 100, 250, 500];
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