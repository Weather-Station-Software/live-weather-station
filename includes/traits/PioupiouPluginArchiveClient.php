<?php

namespace WeatherStation\SDK\Pioupiou\Plugin;

use WeatherStation\SDK\Pioupiou\Exception;
use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Pioupiou\PIOUApiClient;
use WeatherStation\System\Quota\Quota;
use WeatherStation\Data\DateTime\Conversion as DateTimeConversion;


/**
 * Pioupiou archive client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
trait ArchiveClient {

    use DateTimeConversion;

    protected $facility = 'Import Manager';

    /**
     * Get and store station's data.
     * @param string $service_id The pioupiou station id to get archive for.
     * @param string $station_id The station id to get archive for.
     * @param string $station_name The station name.
     * @param string $tz The timezone for the station.
     * @param string $start_date The UTC starting date.
     * @param string $end_date The UTC ending date.
     * @return array
     * @since 3.7.0
     */
    public function get_archive($service_id, $station_id, $station_name, $tz, $start_date, $end_date) {
        $start = self::sub_days_to_mysql_date(date('Y-m-d',$start_date), 1);
        $stop = self::add_days_to_mysql_date(date('Y-m-d',$end_date), 1);
        $date = new \DateTime('now', new \DateTimeZone($tz));
        $offset = 0;//$date->getOffset();
        $result = array();
        try {
            $piou = new PIOUApiClient();
            if (Quota::verify('Pioupiou', 'GET')) {
                $response = $piou->getRawPublicStationArchive($service_id, $start, $stop);
                $raw_data = json_decode($response, true);
                if (is_array($raw_data) && !array_key_exists('error_code', $raw_data)) {
                    if (array_key_exists('data', $raw_data)) {
                        //$start_date = date('Y-m-d',$start_date);
                        //$end_date = date('Y-m-d',$end_date);
                        foreach ($raw_data['data'] as $line) {
                            if (count($line) === 8) {
                                try {
                                    $ts = strtotime($line[0]) + $offset;
                                }
                                catch(\Exception $ex) {
                                    continue;
                                }
                                //if (self::mysql_is_ordered($start_date, date('Y-m-d',$ts)) && self::mysql_is_ordered(date('Y-m-d',$ts), $end_date)) {
                                if (($start_date <= $ts) && ($ts <= $end_date)){
                                    if (is_numeric($line[4])) {
                                        if (!array_key_exists('windstrength', $result)) {
                                            $result['windstrength'] = array();
                                        }
                                        $result['windstrength'][$ts] = round($line[4], 1);
                                    }
                                    if (is_numeric($line[5])) {
                                        if (!array_key_exists('guststrength', $result)) {
                                            $result['guststrength'] = array();
                                        }
                                        $result['guststrength'][$ts] = round($line[5], 1);
                                    }
                                    if (is_numeric($line[6])) {
                                        if (!array_key_exists('windangle', $result)) {
                                            $result['windangle'] = array();
                                            $result['gustangle'] = array();
                                            $result['winddirection'] = array();
                                            $result['gustdirection'] = array();
                                        }
                                        $angle = (int)round($line[6]);
                                        $direction = (int)floor(($angle + 180) % 360);
                                        $result['windangle'][$ts] = $angle;
                                        $result['gustangle'][$ts] = $angle;
                                        $result['winddirection'][$ts] = $direction;
                                        $result['gustdirection'][$ts] = $direction;
                                    }
                                }
                                else {
                                    continue;
                                }
                            }
                        }
                    }
                    Logger::notice($this->facility, 'Pioupiou', $station_id, $station_name, null, null, 0, 'Data retrieved.');
                }
                else {
                    Logger::warning($this->facility, 'Pioupiou', $station_id, $station_name, null, null, 1, 'Pioupiou servers has returned unrecognized response: ' . $response);
                }
            }
            else {
                Logger::warning($this->facility, 'Pioupiou', $station_id, $station_name, null, null, 0, 'Quota manager has forbidden to retrieve data.');
            }
        }
        catch(\Exception $ex)
        {
            if (strpos($ex->getMessage(), 'JSON /') > -1) {
                Logger::warning($this->facility, 'Pioupiou', $station_id, $station_name, null, null, $ex->getCode(), 'Pioupiou servers has returned empty response. Retry will be done shortly.');
            }
            else {
                Logger::warning($this->facility, 'Pioupiou', $station_id, $station_name, null, null, $ex->getCode(), 'Temporary unable to contact Pioupiou servers. Retry will be done shortly.');
            }
        }
        return $result;
    }
}