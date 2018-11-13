<?php

namespace WeatherStation\UI\ListTable;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Output;

/**
 * Files list table for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
class File extends Base {

    use Output;

    private $limit = 25;

    public function __construct(){
        global $status, $page;
        parent::__construct(array('singular' => 'file', 'plural' => 'files', 'ajax' => true));
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    protected function column_system($item){
        $color = Logger::get_color($item['level']);
        if ($color != '') {
            $color = 'style="color:' . $color . '"';
        }
        $s = sprintf('?page=%s&view=log-detail&log-entry=%s',$_REQUEST['page'],$item['id']);
        $result = '<i ' . $color . ' class="' . LWS_FAS . ' fa-fw fa-lg ' . Logger::get_icon($item['level']) . '"></i>&nbsp;';
        $result .= '&nbsp;<a class="row-title" href="' . $s . '" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" ' : '') . 'title="'. ucfirst(__('see details', 'live-weather-station')) . '">' . $item['system'] . ' ' . $item['version'] . '</a>';
        $result .= '<br /><span style="color:silver">Event ' . $item['id'] . ', ' . Logger::get_name($item['level']) . ' ' . __('code', 'live-weather-station') . ' ' . $item['code'] . '</span>';
        return $result;
    }

    protected function column_service($item){
        if ($item['service'] == 'N/A') {
            return '<span style="color:silver">' . __('N/A', 'live-weather-station') . '</span>';
        }
        else {
            return $item['service'];
        }
    }

    protected function column_message($item){
        $trunc = 50;
        if (strlen($item['message']) > $trunc) {
            return substr($item['message'],0 , $trunc - 1) . '...';
        }
        else {
            return $item['message'];
        }
    }

    protected function column_timestamp($item){
        $result = $this->get_date_from_mysql_utc($item['timestamp'], '', 'Y-m-d H:i:s') ;
        $result .='<br /><span style="color:silver">' . $this->get_positive_time_diff_from_mysql_utc($item['timestamp']) . '</span>';
        return $result;
    }

    protected function column_device_name($item){
        if ($item['device_name'] == 'N/A') {
            return '<span style="color:silver">' . __('N/A', 'live-weather-station') . '</span>';
        }
        else {
            if ($item['module_name'] != 'N/A') {
                return $item['device_name'] . '<br /><span style="color:silver">' . $item['module_name'] . '</span>';
            }
            else {
                return $item['device_name'];
            }
        }
    }

    public function get_columns(){
        $columns = array('system' => __('Event', 'live-weather-station'),
            'file' => lws__('File', 'live-weather-station'),
            'station' => __('Station', 'live-weather-station'),
            'from' => lws__('From', 'live-weather-station'),
            'to' => lws__('To', 'live-weather-station'),
            'size' => lws__('Size', 'live-weather-station'));
        return $columns;
    }

    protected function get_hidden_columns() {
        return array();
    }

    protected function get_sortable_columns() {
        return array();
    }

    public function get_bulk_actions() {
        return array();
    }

    protected function init_values() {
        // list files
    }

    public function prepare_items() {
        $this->init_values();
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum();
        $total_items = $this->get_log_count($this->filters);
        $this->items = $this->get_log_list($this->filters, ($current_page-1)*$this->limit, $this->limit);
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page' => $this->limit, 'total_pages' => ceil($total_items/$this->limit)));
    }

    private function get_page_url($filters) {
        $args = array('page' => 'lws-files', 'view' => 'list-table-files');
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
        /*$filters = $this->filters;
        unset($filters['level']);
        $s1 = '<a href="' . $this->get_page_url($filters) . '"' . ( $this->level == '' ? ' class="current"' : '') . '>' . __('All', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $filters['level'] = 'notice';
        $s2 = '<a href="' . $this->get_page_url($filters) . '"' . ( $this->level == 'notice' ? ' class="current"' : '') . '>' . __('Notices &amp; beyond', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $filters['level'] = 'error';
        $s3 = '<a href="' . $this->get_page_url($filters) . '"' . ( $this->level == 'error' ? ' class="current"' : '') . '>' . __('Errors &amp; beyond', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $status_links = array( 'all' => $s1, 'notices' => $s2, 'errors' => $s3);*/
        return '';
    }

    public function extra_tablenav($which) {
        /*$list = $this;
        $args = compact('list');
        foreach ($args as $key => $val) {
            $$key = $val;
        }
        if ($which == 'top'){
            include(LWS_ADMIN_DIR.'partials/ListTableLogsTop.php');
        }
        if ($which == 'bottom'){
            include(LWS_ADMIN_DIR.'partials/ListTableLogsBottom.php');
        }*/
    }

    // SPECIFIC METHODS FOR RENDERING

    public function get_level() {
        return $this->level;
    }

    public function get_station() {
        return $this->station;
    }

    public function get_system() {
        return $this->system;
    }

    public function get_service() {
        return $this->service;
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