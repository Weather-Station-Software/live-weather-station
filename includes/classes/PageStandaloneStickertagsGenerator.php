<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Logs\LoggableException;
use WeatherStation\Data\Output;

/**
 * Class to generate stickertags.txt text files.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Stickertags extends TXTGenerator {

    use Output;

    /**
     * Get formatted datas ready to send.
     *
     * @param array $params Parameters for selecting data to send.
     * @param string $subformat Optional. The subformat requested.
     * @throws LoggableException If request is malformed.
     * @return string The content ready to send.
     * @since 3.0.0
     */
     protected function get_data($params, $subformat='standard') {
         Logger::debug('Stickertags Renderer', null, null, null, null, null, null, print_r($params, true));
         if (is_array($params) && !empty($params) && array_key_exists('station', $params)) {
             try {
                 $station = $this->get_station_informations_by_station_id($params['station']);
                 if (!is_array($station) || empty($station)) {
                     throw new \Exception();
                 }
             }
             catch (\Exception $e) {
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