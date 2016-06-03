<?php
/**
 * OpenWeatherMap stations list table for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'class-live-weather-station-list-table.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');


class Owm_Stations_List_Table extends Live_Weather_Station_List_Table {
    
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
            'edit'      => sprintf('<a href="?page=%s&view=add-edit-owm&action=add-edit-owm&owm-station=%s">'.__('Edit', 'live-weather-station').'</a>',$_REQUEST['page'],$item['station_id']),
            'verify'    => sprintf('<a href="https://maps.google.com?q=%s,%s" target="_blank">'.__('Verify on a map', 'live-weather-station').'</a>',$item['loc_latitude'],$item['loc_longitude']),
            'delete'    => sprintf('<a href="?page=%s&view=manage-owm&action=delete-owm&owm-station=%s">'.__('Delete', 'live-weather-station').'</a>',$_REQUEST['page'],$item['station_id']),
        );
        return sprintf('%1$s <span style="color:silver">(%2$s, %3$s)</span>%4$s', $item['station_name'], $item['loc_city'], $item['country'], $this->row_actions($actions));
    }

    protected function column_cb($item){
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['station_id']);
    }

    public function get_columns(){
        $columns = array('cb' => '<input type="checkbox" />',
            'title' => __('Station', 'live-weather-station'),
            'tz' => __('Time zone', 'live-weather-station'),
            'latitude' => __('Latitude', 'live-weather-station'),
            'longitude' => __('Longitude', 'live-weather-station'),
            'altitude' => __('Altitude', 'live-weather-station'));
        return $columns;
    }

    protected function get_sortable_columns() {
        $sortable_columns = array('title' => array('station_name',false));
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        $actions = array('delete' => __('Delete', 'live-weather-station'));
        return $actions;
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
        $data = $this->get_owm_stations_list();
        if (count($data) > 0) {
            foreach ($data as &$item) {
                $item['country'] = $this->get_country_name($item['loc_country_code']);
                $item['tz'] = $this->output_timezone($item['loc_timezone']);
                $item['altitude'] = $this->output_value($item['loc_altitude'], 'loc_altitude', true) ;
                $item['latitude'] = $this->output_coordinate($item['loc_latitude'], 'loc_latitude', 6);
                $item['longitude'] = $this->output_coordinate($item['loc_longitude'], 'loc_longitude', 6);
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