<?php

/**
 * Abstract class to push data to weather services.
 *
 * @since      2.5.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-query.php');
require_once(LWS_INCLUDES_DIR.'trait-unit-conversion.php');


abstract class Live_Weather_Station_Pusher {

    use Datas_Query, Unit_Conversion;

    public $time_shift = 1200;  // consider data as obsolete after 20 minutes
    public $facility = 'Pusher';

    /**
     * Get the service Name.
     *
     * @return  string   The service name.
     * @since   3.0.0
     */
    abstract protected function get_service_name();

    /**
     * Format Netatmo data to be pushed.
     *
     * @param   array   $data      Collected Netatmo datas.
     * @return  array   The data ready to push.
     * @since   2.5.0
     */
    abstract protected function get_pushed_data($data);

    /**
     * Completes data to be pushed.
     *
     * @param   array   $device      The actual device.
     * @param   array   $station      Station details.
     * @return  array   The completed device.
     * @since   2.5.0
     */
    abstract protected function complete_pushed_data($device, $station);

    /**
     * Test if this station must be pushed.
     *
     * @param   array   $station      Station details.
     * @return  boolean   True if these station data must be pushed, false otherwise.
     * @since   2.5.0
     */
    abstract protected function ready_for_push($station);

    /**
     * Get the post url.
     *
     * @return  string   The url where to post data.
     * @since   2.5.0
     */
    abstract protected function get_post_url();

    /**
     * Get the user/pwd string for CURLOPT_USERPWD option.
     *
     * @param   array   $station      Station details.
     * @return  string   User and password ready to use by curl_setopt.
     * @since   2.5.0
     */
    abstract protected function get_userpwd($station);

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @since   2.5.0
     */
    abstract protected function process_result($content, $station);

    /**
     * Process the result of the post.
     *
     * @param   array   $content      Result of the post.
     * @param   array   $station      Station details.
     * @throws Exception Contains HTTP error code & message
     * @since   2.5.0
     */
    protected function _process_result($content, $station) {
        $error = false;
        $code = 0;
        $message = 'Unknown error';
        $response = $content['response'];
        if (array_key_exists('code', $response)) {
            $code = $response['code'];
            if ($code != '200') {
                $error = true;
                if (array_key_exists('message', $response)) {
                    $message = $response['message'];
                }
            }
        }
        else {
            $error = true;
        }
        if ($error) {
            throw new Exception($message, $code);
        }
        else {
            $this->process_result($content, $station);
        }
    }

    /**
     * Post station's datas to service.
     *
     * @param   array   $data       Collected Netatmo datas.
     * @param   array   $stations   Optional. Test specificaly these stations.
     * @return  string  Error string if any.
     * @since    2.5.0
     */
    public function post_data($data, $stations = array()) {
        if (empty($stations)) {
            $test = false;
            $stations = $this->get_stations_informations();
        }
        else {
            $test = true;
        }
        $devices = $this->get_pushed_data($data);
        foreach ($stations as $station) {
            if (!$this->ready_for_push($station) && !$test) {
                continue;
            }
            if (array_key_exists($station['station_id'], $devices)) {
                $device = $devices[$station['station_id']];
                $sid = $station['station_id'];
                $sname = $station['station_name'];
                if (!empty($device)) {
                    $values = $this->complete_pushed_data($device, $station);
                    $auth = $this->get_userpwd($station);
                    try {
                        $args = array();
                        if ($auth != '') {
                            $args['headers'] = array ('Authorization' => 'Basic ' . base64_encode($auth));
                        }
                        $args['body'] = $values;
                        $content = wp_remote_post($this->get_post_url(), $args);
                        Logger::debug($this->facility, $this->get_service_name(), $sid, $sname, null, null, 999, 'Raw data: ' . print_r($content,true));
                        if (is_wp_error($content)) {
                            throw new Exception($content->get_error_message());
                        }
                        $this->process_result($content, $station);
                        if ($test) {
                            Logger::notice($this->facility, $this->get_service_name(), $sid, $sname, null, null, null, 'Service connectivity test: OK.');
                            return '';
                        }
                    }
                    catch (Exception $ex) {
                        if ($test) {
                            Logger::notice($this->facility, $this->get_service_name(), $sid, $sname, null, null, $ex->getCode(), 'Service connectivity test: KO / ' . $ex->getMessage());
                            return $ex->getMessage();
                        }
                        else {
                            Logger::error($this->facility, $this->get_service_name(), $sid, $sname, null, null, $ex->getCode(), $ex->getMessage());
                        }
                    }
                }
            }
        }
    }
}