<?php

/**
 * Class to generate stickertags.txt text files.
 *
 * @since      3.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'class-live-weather-station-txt-generator.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');


class Stickertags_Generator extends Live_Weather_Station_Txt_Generator {

    use Datas_Output;

    /**
     * Get formatted datas ready to send.
     *
     * @param   array   $params      Parameters for selecting data to send.
     * @return  string  The content ready to send.
     * @throws Exception    
     * @since   3.0.0
     */
     protected function get_data($params) {
         if (is_array($params) && !empty($params) && array_key_exists('station', $params)) {
             try {
                 $station = $this->get_station_informations($params['station']);
                 if (!is_array($station) || empty($station)) {
                     throw new Exception();
                 }
             }
             catch (Exception $e) {
                 throw new LoggableException('error', 'Stickertags Renderer', null, $params['station'], null, null, null , 400, 'Not a valid station ID.');
             }
             if (array_key_exists('txt_sync', $station) && $station['txt_sync'] == 1) {
                 $result = $this->format_stickertags_data($this->get_outdoor_datas($params['station'], false, true));
                 Logger::info('Stickertags Renderer', null, $station['station_id'], $station['station_name'], null, null , 0, 'Success while rendering data.');
             }
             else {
                 throw new LoggableException('error', 'Stickertags Renderer', null, $station['station_id'], $station['station_name'], null, null , 405, 'The station does not publish its data via this method/format.');
             }
         }
         else {
             throw new LoggableException('error', 'Stickertags Renderer', null, null, null, null, null , 400, 'Not a valid station ID.');
         }
         return $result;
     }
}