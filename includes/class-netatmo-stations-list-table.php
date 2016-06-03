<?php
/**
 * OpenWeatherMap stations list table for Live Weather Station plugin
 *
 * @since      2.5.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'class-live-weather-station-list-table.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');


class Netatmo_Stations_List_Table extends Live_Weather_Station_List_Table {

    use Datas_Output;


    public function __construct(){
        global $status, $page;
        parent::__construct(array('singular' => 'station', 'plural' => 'stations', 'ajax' => false));
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    protected function column_title($item){
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&view=edit-netatmo&action=edit-netatmo&netatmo-station=%s">'.__('Manage services', 'live-weather-station').'</a>',$_REQUEST['page'],$item['station_id'])
        );
        return sprintf('%1$s %2$s', $item['station_name'], $this->row_actions($actions));
    }

    protected function column_cb($item){
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['station_id']);
    }

    public function get_columns(){
        $columns = array('cb' => '<input type="checkbox" />',
            'title' => __('Station', 'live-weather-station'),
            'pws' => 'PWS Weather',
            'wow' => 'WOW Met Office',
            'wug' => 'Weather Underground');
        return $columns;
    }

    protected function get_sortable_columns() {
        $sortable_columns = array('title' => array('station_name',false));
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        return array();
    }

    public function prepare_items() {

        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'station_name';
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
            $result = strcmp($a[$orderby], $b[$orderby]);
            return ($order==='asc') ? $result : -$result;
        }
        $per_page = 5;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data = $this->get_stations_informations();
        if (count($data) > 0) {
            foreach ($data as &$item) {
                $item['pws'] = __('No', 'live-weather-station');
                if ($item['pws_sync'] == 1) {
                    $item['pws'] = __('Yes', 'live-weather-station');
                    if ($item['pws_user'] != '') {
                        $item['pws'] =$item['pws'] . ' (<a href="http://www.pwsweather.com/obs/' . $item['pws_user'] . '.html" target="_blank">' . __('see it', 'live-weather-station') . '</a>)';
                    }
                }
                $item['wow'] = __('No', 'live-weather-station');
                if ($item['wow_sync'] == 1) {
                    $item['wow'] = __('Yes', 'live-weather-station');
                    if ($item['wow_user'] != '') {
                        $item['wow'] =$item['wow'] . ' (<a href="http://wow.metoffice.gov.uk/weather/view?siteID=' . $item['wow_user'] . '" target="_blank">' . __('see it', 'live-weather-station') . '</a>)';
                    }
                }
                $item['wug'] = __('No', 'live-weather-station');
                if ($item['wug_sync'] == 1) {
                    $item['wug'] = __('Yes', 'live-weather-station');
                    if ($item['wug_user'] != '') {
                        $item['wug'] =$item['wug'] . ' (<a href="https://www.wunderground.com/personal-weather-station/dashboard?ID=' . $item['wug_user'] . '" target="_blank">' . __('see it', 'live-weather-station') . '</a>)';
                    }
                }
            }
        }
        usort($data, 'usort_reorder');
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil($total_items/$per_page)));
    }
}