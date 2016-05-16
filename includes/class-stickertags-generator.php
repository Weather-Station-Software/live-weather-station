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
                 throw new Exception('', 400);
             }
             if (array_key_exists('txt_sync', $station) && $station['txt_sync'] == 1) {
                 $result = '';
                 $result = print_r($station, true);







             }
             else {
                 throw new Exception('The station does not publish its data via this method/format.', 405);
             }
         }
         else {
             throw new Exception('', 400);
         }
         return $result;
     }
}