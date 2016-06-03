<?php

/**
 * OpenWeatherMap base client for Live Weather Station plugin
 *
 * @since      2.7.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-dashboard-manipulation.php');
require_once(LWS_INCLUDES_DIR.'trait-id-manipulation.php');


trait Owm_Client {

    use Dashboard_Manipulation, Id_Manipulation;

    protected $service_name = 'OpenWeatherMap';

    /**
     * Synchronize main table with station table.
     *
     * @since    2.7.0
     */
    protected function synchronize_owm() {
        $list = array();
        $stations = $this->get_all_owm_stations();
        if (count($stations) > 0) {
            foreach ($stations as $station) {
                $device_id = self::get_unique_owm_id($station['station_id']);
                $updates = array() ;
                $updates['device_id'] = $device_id;
                $updates['device_name'] = $station['station_name'];
                $updates['module_id'] = $device_id;
                $updates['module_type'] = 'NAMain';
                $updates['module_name'] = $station['station_name'];
                $updates['measure_timestamp'] = date('Y-m-d H:i:s');
                $updates['measure_type'] = 'loc_altitude';
                $updates['measure_value'] = $station['loc_altitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_latitude';
                $updates['measure_value'] = $station['loc_latitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_longitude';
                $updates['measure_value'] = $station['loc_longitude'];
                $this->update_data_table($updates);
                $updates['measure_type'] = 'loc_timezone';
                $updates['measure_value'] = $station['loc_timezone'];
                $this->update_data_table($updates);
                $list[] = $device_id;
            }
            $this->clean_owm_from_table($list);
        }
    }

    /**
     * Store station's datas.
     *
     * @param   array   $datas   OWM collected datas
     * @since    2.7.0
     */
    private function store_owm_datas($datas) {
        foreach ($datas as $data) {
            $this->get_dashboard($data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data']);
        }
    }
}