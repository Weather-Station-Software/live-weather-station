<?php
/**
 * Events log list table for Live Weather Station plugin
 *
 * @since      2.8.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'class-live-weather-station-list-table.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');


class Log_List_Table extends Live_Weather_Station_List_Table {

    use Datas_Output;

    private $limit = 25;
    private $level = '';
    private $station = '';
    private $stations = array();
    private $system = '';
    private $systems = array();
    private $service = '';
    private $services = array();
    private $filters = array();

    public function __construct(){
        global $status, $page;
        parent::__construct(array('singular' => 'event', 'plural' => 'events', 'ajax' => true));
        $this->stations = $this->get_stations_array();
        $this->systems = $this->get_systems_array();
        $this->services = $this->get_services_array();
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    protected function column_system($item){
        $color = Logger::get_color($item['level']);
        if ($color != '') {
            $color = 'style="color:' . $color . '"';
        }
        $s = sprintf('?page=%s&view=view-log&action=view-log&log-entry=%s',$_REQUEST['page'],$item['id']);
        $result = '<i ' . $color . ' class="fa fa-fw fa-lg ' . Logger::get_icon($item['level']) . '"></i>&nbsp;' . $item['system'] . ' ' . $item['version'];
        $result .= '&nbsp;<a href="' . $s . '" target="_blank" title="'. __('See detail in new window', 'live-weather-station') . '"><i style="color:silver" class="fa fa-fw fa-eye"></i></a>';
        $result .= '<br /><span style="color:silver">' . Logger::get_name($item['level']) . ' ' . __('code', 'live-weather-station') . ' ' . $item['code'] . '</span>';
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
            'timestamp' => __('Time', 'live-weather-station'),
            'service' => __('Service', 'live-weather-station'),
            'device_name' => __('Station', 'live-weather-station'),
            'message' => __('Message', 'live-weather-station'));
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
        $this->filters = array();
        if (isset($_GET['limit'])) {
            $this->limit = intval($_GET['limit']);
            if (!$this->limit) {
                $this->limit = 25;
            }
        }
        if (isset($_GET['level'])) {
            $this->level = strtolower(sanitize_text_field(urldecode($_GET['level'])));
            if (!array_key_exists($this->level, Logger::$severity)) {
                $this->level = '';
            }
            else {
                $this->filters['level'] = $this->level;
            }
        }
        if (isset($_GET['station'])) {
            $this->station = sanitize_text_field(urldecode($_GET['station']));
            if (!array_key_exists($this->station, $this->stations)) {
                $this->station = '';
            }
            else {
                $this->filters['device_id'] = $this->station;
            }
        }
        if (isset($_GET['system'])) {
            $this->system = sanitize_text_field(urldecode($_GET['system']));
            if (!array_key_exists($this->system, $this->systems)) {
                $this->system = '';
            }
            else {
                $this->filters['system'] = $this->system;
            }
        }
        if (isset($_GET['service'])) {
            $this->service = sanitize_text_field(urldecode($_GET['service']));
            if (!array_key_exists($this->service, $this->services)) {
                $this->service = '';
            }
            else {
                $this->filters['service'] = $this->service;
            }
        }
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
        $args = array('page' => 'lws-config', 'view' => 'list-logs', 'action' => 'list-logs');
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
        $url = add_query_arg( $args, admin_url( 'options-general.php' )  );
        return $url;
    }

    public function get_views() {
        $filters = $this->filters;
        unset($filters['level']);
        $s1 = '<a href="' . $this->get_page_url($filters) . '"' . ( $this->level == '' ? ' class="current"' : '') . '>' . __('All', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $filters['level'] = 'notice';
        $s2 = '<a href="' . $this->get_page_url($filters) . '"' . ( $this->level == 'notice' ? ' class="current"' : '') . '>' . __('Notices &amp; beyond', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $filters['level'] = 'error';
        $s3 = '<a href="' . $this->get_page_url($filters) . '"' . ( $this->level == 'error' ? ' class="current"' : '') . '>' . __('Errors &amp; beyond', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $status_links = array( 'all' => $s1, 'notices' => $s2, 'errors' => $s3);
        return $status_links;
    }

    public function extra_tablenav($which) {
        $list = $this;
        $args = compact('list');
        foreach ($args as $key => $val) {
            $$key = $val;
        }
        if ($which == 'top'){
            include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-list-logs-top.php');
        }
        if ($which == 'bottom'){
            include(LWS_ADMIN_DIR.'partials/live-weather-station-admin-list-logs-bottom.php');
        }
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

    public function get_system_select() {
        $result = array();
        $l = array();
        $l['value'] = '';
        $l['text'] = esc_html__('All systems', 'live-weather-station');
        $l['selected'] = ($this->system == '' ? 'selected="selected" ' : '');
        $result[] = $l;
        foreach ($this->systems as $system) {
            $l = array();
            $l['value'] = $system;
            $l['text'] = $system;
            $l['selected'] = ($this->system == $system ? 'selected="selected" ' : '');
            $result[] = $l;
        }
        return $result;
    }

    public function get_service_select() {
        $result = array();
        $l = array();
        $l['value'] = '';
        $l['text'] = esc_html__('All services', 'live-weather-station');
        $l['selected'] = ($this->service == '' ? 'selected="selected" ' : '');
        $result[] = $l;
        foreach ($this->services as $service) {
            $l = array();
            $l['value'] = $service;
            $l['text'] = $service;
            $l['selected'] = ($this->service == $service ? 'selected="selected" ' : '');
            $result[] = $l;
        }
        return $result;
    }

    public function get_station_select() {
        $result = array();
        $l = array();
        $l['value'] = '';
        $l['text'] = esc_html__('All stations', 'live-weather-station');
        $l['selected'] = ($this->station == '' ? 'selected="selected" ' : '');
        $result[] = $l;
        foreach ($this->stations as $key => $station) {
            $l = array();
            $l['value'] = $key;
            $l['text'] = $station;
            $l['selected'] = ($this->station == $key ? 'selected="selected" ' : '');
            $result[] = $l;
        }
        return $result;
    }
    
}