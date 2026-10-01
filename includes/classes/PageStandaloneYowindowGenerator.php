<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Logs\LoggableException;
use WeatherStation\Data\Output;

/**
 * Class to generate stickertags.txt text files.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
class Yowindow extends TXTGenerator {

    use Output;

    /**
     * Get formatted measurements ready to send.
     *
     * @param array $params Parameters for selecting data to send.
     * @param string $subformat Optional. The subformat requested.
     * @throws LoggableException If request is malformed.
     * @return string The content ready to send.
     * @since 3.3.0
     */
    protected function get_data($params, $subformat='standard') {
        $this->content_type = 'Content-type: application/xml; charset=utf-8';
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- print_r( $params, true ) only builds the text handed to the plugin Logger::debug(), nothing is printed.
        Logger::debug('YoWindow XML Renderer', null, null, null, null, null, null, print_r($params, true));
        if (is_array($params) && !empty($params) && array_key_exists('station', $params)) {
            try {
                $station = $this->get_station_information_by_station_id($params['station']);
                if (!is_array($station) || empty($station)) {
                    throw new \Exception();
                }
            }
            catch (\Exception $e) {
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- constant message; LoggableException is only logged and answered as text/plain through PageStandaloneFramework::error(), which replaces it by the HTTP status text.
                throw new LoggableException('error', 'YoWindow XML Renderer', null, $params['station'], null, null, null , 400, 'Not a valid station ID.');
            }
            if (array_key_exists('yow_sync', $station) && $station['yow_sync'] == 1) {
                $data = $this->format_yowindow_data($this->get_outdoor_measurements($params['station'], false, true));
                $result = $data['values'];
                $this->timestamp = $data['timestamp'];
                Logger::info('YoWindow XML Renderer', null, $station['station_id'], $station['station_name'], null, null , 0, 'Success while rendering data.');
            }
            else {
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- constant message; LoggableException is only logged and answered as text/plain through PageStandaloneFramework::error(), which replaces it by the HTTP status text.
                throw new LoggableException('error', 'YoWindow XML Renderer', null, $station['station_id'], $station['station_name'], null, null , 405, 'The station does not publish its data via this method/format.');
            }
        }
        else {
            throw new LoggableException('error', 'YoWindow XML Renderer', null, null, null, null, null , 400, 'Not a valid station ID.');
        }
        return $result;
    }
}