<?php

namespace WeatherStation\UI\ListTable;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display filters of the events log (level, station, system, service, limit): nothing is modified.

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Output;

/**
 * Events log list table for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */
class Log extends Base {

    use Output;

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
        $this->stations = $this->get_log_stations_array();
        $this->systems = $this->get_log_systems_array();
        $this->services = $this->get_log_services_array();
    }

    protected function column_default($item, $column_name){
        return esc_html($item[$column_name]);
    }

    protected function column_system($item){
        $color = Logger::get_color($item['level']);
        if ($color != '') {
            $color = 'style="color:' . esc_attr($color) . '"';
        }
        $s = esc_url(add_query_arg(array('page' => 'lws-events', 'view' => 'log-detail', 'log-entry' => absint($item['id'])), admin_url('admin.php')));
        $result = '<i ' . $color . ' class="' . LIVE_WEATHER_STATION_FAS . ' fa-fw fa-lg ' . Logger::get_icon($item['level']) . '"></i>&nbsp;';
        $result .= '&nbsp;<a class="row-title" href="' . $s . '" ' . ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank" rel="noopener noreferrer" ' : '') . 'title="'. esc_attr(ucfirst(__('see details', 'live-weather-station'))) . '">' . esc_html($item['system'] . ' ' . $item['version']) . '</a>';
        $result .= '<br /><span style="color:silver">Event ' . absint($item['id']) . ', ' . esc_html(Logger::get_name($item['level'])) . ' ' . esc_html__('code', 'live-weather-station') . ' ' . esc_html($item['code']) . '</span>';
        return $result;
    }

    protected function column_service($item){
        if ($item['service'] == 'N/A') {
            return '<span style="color:silver">' . __('N/A', 'live-weather-station') . '</span>';
        }
        else {
            return esc_html($item['service']);
        }
    }

    protected function column_message($item){
        $trunc = 50;
        if (strlen($item['message']) > $trunc) {
            return esc_html(substr($item['message'],0 , $trunc - 1)) . '...';
        }
        else {
            return esc_html($item['message']);
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
                return esc_html($item['device_name']) . '<br /><span style="color:silver">' . esc_html($item['module_name']) . '</span>';
            }
            else {
                return esc_html($item['device_name']);
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
            $this->limit = intval(sanitize_text_field(wp_unslash($_GET['limit'])));
            if (!in_array($this->limit, [25, 50, 100, 250, 500], true)) {
                $this->limit = 25;
            }
        }
        if (isset($_GET['level'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The value is unslashed, URL decoded then sanitized by sanitize_text_field() (the urldecode() call in between hides it from the sniff).
            $this->level = strtolower(sanitize_text_field(urldecode(wp_unslash($_GET['level']))));
            if (!array_key_exists($this->level, Logger::$severity)) {
                $this->level = '';
            }
            else {
                if ($this->level != '') {
                    $this->filters['level'] = $this->level;
                }
            }
        }
        if (isset($_GET['station'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The value is unslashed, URL decoded then sanitized by sanitize_text_field() (the urldecode() call in between hides it from the sniff).
            $this->station = sanitize_text_field(urldecode(wp_unslash($_GET['station'])));
            if (!array_key_exists($this->station, $this->stations)) {
                $this->station = '';
            }
            else {
                if ($this->station != '') {
                    $this->filters['station'] = $this->station;
                }
            }
        }
        if (isset($_GET['system'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The value is unslashed, URL decoded then sanitized by sanitize_text_field() (the urldecode() call in between hides it from the sniff).
            $this->system = sanitize_text_field(urldecode(wp_unslash($_GET['system'])));
            if (!array_key_exists($this->system, $this->systems)) {
                $this->system = '';
            }
            else {
                if ($this->system != '') {
                    $this->filters['system'] = $this->system;
                }
            }
        }
        if (isset($_GET['service'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The value is unslashed, URL decoded then sanitized by sanitize_text_field() (the urldecode() call in between hides it from the sniff).
            $this->service = sanitize_text_field(urldecode(wp_unslash($_GET['service'])));
            if (!array_key_exists($this->service, $this->services)) {
                $this->service = '';
            }
            else {
                if ($this->service != '') {
                    $this->filters['service'] = $this->service;
                }
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
        $args = array('page' => 'lws-events', 'view' => 'list-table-logs');
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
        $filters = $this->filters;
        unset($filters['level']);
        $s1 = '<a href="' . esc_url($this->get_page_url($filters)) . '"' . ( $this->level == '' ? ' class="current"' : '') . '>' . __('All', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $filters['level'] = 'notice';
        $s2 = '<a href="' . esc_url($this->get_page_url($filters)) . '"' . ( $this->level == 'notice' ? ' class="current"' : '') . '>' . __('Notices &amp; beyond', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
        $filters['level'] = 'error';
        $s3 = '<a href="' . esc_url($this->get_page_url($filters)) . '"' . ( $this->level == 'error' ? ' class="current"' : '') . '>' . __('Errors &amp; beyond', 'live-weather-station') . ' <span class="count">(' . $this->get_log_count($filters) . ')</span></a>';
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
            include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/ListTableLogsTop.php');
        }
        if ($which == 'bottom'){
            include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/ListTableLogsBottom.php');
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
            $l['text'] = sprintf(/* translators: %d: Number of lines displayed per page. */ esc_html__('Show %d lines per page', 'live-weather-station'), $d);
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
            $l['value'] = esc_attr($system);
            $l['text'] = esc_html($system);
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
            $l['value'] = esc_attr($service);
            $l['text'] = esc_html($service);
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
            if ($key) {
                $l = array();
                $l['value'] = esc_attr($key);
                $l['text'] = esc_html($station);
                $l['selected'] = ($this->station == $key ? 'selected="selected" ' : '');
                $result[] = $l;
            }
        }
        return $result;
    }
    
}