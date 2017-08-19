<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\Data\Output;

/**
 * Abstract class to generate text files.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
abstract class TXTGenerator {

    protected $content_type = 'Content-type: text/plain; charset=utf-8';

    /**
     * Send headers.
     *
     * @param mixed $filename The filename to generate as attachement.
     * @since 3.0.0
     */
    private function send_header($filename=false) {
        if ((bool)get_option('live_weather_station_txt_cache_bypass')) {
            $tsstring = gmdate('D, d M Y H:i:s ') . 'GMT';
            $etag = md5($tsstring);
            header('Pragma: no-cache');
            header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
            header('Last-Modified: '. $tsstring);
            header('Expires: '. $tsstring);
            header('ETag: "{' . $etag . '}"');
        }
        header($this->content_type);
        if ($filename) {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
    }

    /**
     * Send content.
     *
     * @param   string   $content      The content to send.
     * @since   3.0.0
     */
    private function send_content($content) {
        echo $content;
    }

    /**
     * Get formatted datas ready to send.
     *
     * @param array $params Parameters for selecting data to send.
     * @param string $subformat Optional. The subformat requested.
     * @return string The content ready to send.
     * @since 3.0.0
     */
    abstract protected function get_data($params, $subformat='standard');

    /**
     * Send the file.
     *
     * @param array $params Parameters for selecting data to send.
     * @param string $subformat Optional. The subformat request.
     * @param mixed $filename Optional. The filename has to be generatee as attachement.
     * @since 3.0.0
     */
    public function send($params, $subformat='standard', $filename=false) {
        $d = $this->get_data($params, $subformat);
        $this->send_header($filename);
        $this->send_content($d);
    }

}