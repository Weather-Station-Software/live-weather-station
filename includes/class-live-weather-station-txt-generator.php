<?php

/**
 * Abstract class to generate text files.
 *
 * @since      3.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-output.php');


abstract class Live_Weather_Station_Txt_Generator {

    use Datas_Output;

    /**
     * Send headers.
     *
     * @param   mixed   $filename     The filename to generate as attachement.
     * @since   3.0.0
     */
    private function send_header($filename=false) {
        header('Content-type: text/plain; charset=utf-8');
        header("Cache-Control: max-age=1");
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
     * @param   array   $params      Parameters for selecting data to send.
     * @return  string  The content ready to send.
     * @since   3.0.0
     */
    abstract protected function get_data($params);

    /**
     * Send the file.
     *
     * @param   array   $params      Parameters for selecting data to send.
     * @param   mixed   $filename     The filename to generate as attachement.
     * @since   3.0.0
     */
    public function send($params, $filename=false) {
        $this->send_header($filename);
        $this->send_content($this->get_data($params));
    }

}