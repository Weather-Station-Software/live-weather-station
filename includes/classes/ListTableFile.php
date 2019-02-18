<?php

namespace WeatherStation\UI\ListTable;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\Output;
use WeatherStation\System\Storage\Manager as FS;

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

    protected function column_station($item){
        $result = $this->output_iconic_filetype($item['ext'], 'style="color:#999"', 'fa-lg fa-fw') . '&nbsp;&nbsp;';
        $result .= $item['station'] . ' - ' . $item['std_size'];
        $result .= '<br /><span style="color:silver">' . $this->get_extension_description($item['ext']) . '</span>';
        return $result;
    }

    protected function column_state($item){
        $actions = array();
        if ($item['state'] === 'none') {
            if ($item['ext'] !== 'ukn') {
                $result = __('Ready', 'live-weather-station');
                if (LWS_PREVIEW) {
                    $actions[] = '<a href="' . $item['url'] . '" target="_blank" >' . lws__('View file', 'live-weather-station').'</a>';
                }
                $actions[] = '<a href="' . $item['url'] . '" download>' . __('Download file', 'live-weather-station').'</a>';
                if ($item['ext'] == 'wsconf.json') {
                    $actions[] = '<a href="' . lws_get_admin_page_url('lws-files', 'form', 'import', 'configuration', false, null, $item['uuid']) . '">' . lws__('Import configuration', 'live-weather-station').'</a>';
                }
            }
            else {
                $result = __('Ready', 'live-weather-station');
            }
        }
        else {
            $result = __('In progress...', 'live-weather-station') . ' ' . $item['progress'] . '%';
        }
        return sprintf('%1$s %2$s', $result, $this->row_actions($actions));
    }

    /*protected function column_from($item){
        return $item['from'];
    }*/

    protected function column_to($item){
        if ($item['ext'] !== 'ukn' && $item['ext'] !== 'wsconf.json') {
            $result = $item['to'];
        }
        else {
            $result = '';
        }
        return $result;
    }

    protected function column_date($item){
        $result = date_i18n(get_option('date_format'), $item['date']);
        $result .='<br /><span style="color:silver">' . $this->get_time_diff_from_utc($item['date']) . '</span>';
        return $result;
    }

    public function get_columns(){
        $columns = array(
            'station' => lws__('Element', 'live-weather-station'),
            'state' => __('State', 'live-weather-station'),
            'from' => __('From', 'live-weather-station'),
            'to' => __('To', 'live-weather-station'),
            'date' => __('Freshness', 'live-weather-station'));
        return $columns;
    }

    protected function get_hidden_columns() {
        return array();
    }

    public function usort_reorder($a,$b){
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date';
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc';
        $result = strcmp(strtolower($a[$orderby]), strtolower($b[$orderby]));
        return ($order==='asc') ? $result : -$result;
    }

    protected function get_sortable_columns() {
        $sortable_columns = array('station' => array('station',false), 'date' => array('date',true), 'from' => array('from',false), 'to' => array('to',false));
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
        $data = $this->add_status(FS::extended_list_dir((bool)get_option('live_weather_station_only_valid_files')));
        usort($data, array($this, 'usort_reorder'));
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$this->limit),$this->limit);
        $this->items = $data;
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
        return '';
    }

    public function extra_tablenav($which) {
        $list = $this;
        $args = compact('list');
        if ($which == 'bottom'){
            include(LWS_ADMIN_DIR.'partials/ListTableFilesBottom.php');
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

    // SPECIFIC METHODS FOR RENDERING

    private function add_status($data) {
        $processes = self::get_status_for_active_background_processes();
        foreach ($data as &$file) {
            if (array_key_exists($file['uuid'], $processes)) {
                $file['state'] = $processes[$file['uuid']]['state'];
                $file['progress'] = $processes[$file['uuid']]['progress'];
            }
        }
        return $data;
    }



}