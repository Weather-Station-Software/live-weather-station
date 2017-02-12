<?php

namespace WeatherStation\SDK\Netatmo\Clients;


/**
 * Netatmo weather station SDK.
 *
 * For more details upon Netatmo APIs, please check https://dev.netatmo.com/doc
 * 
 * @package Includes\Libraries
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.0.0
 */
class NAWSApiClient extends NAApiClient
{

  /**
   * @type PRIVATE & PARTNER API
   * @param string $device_id
   * @param bool $get_favorites : used to retrieve (or not) user's favorite public weather stations
   * @return array of devices
   * @brief Method used to retrieve data for the given weather station or all weather station linked to the user
   */
   public function getData($device_id = NULL, $get_favorites = TRUE)
   {
       return $this->api('getstationsdata', 'GET', array($device_id, $get_favorites));
   }

    /**
     * @type PRIVATE & PARTNER API
     * @param string $device_id
     * @return array of devices
     * @brief Method used to retrieve data for the given healthy home coach or all healthy home coaches linked to the user
     */
    public function getHCData($device_id = NULL)
    {
        return $this->api('gethomecoachsdata', 'GET', array($device_id));
    }

   /**
    * @type PUBLIC, PRIVATE & PARTNER API
    * @param string $device_id
    * @param string $module_id (optional) if specified will retrieve the module's measurements, else it will retrieve the main device's measurements
    * @param string $scale : interval of time between two measurements. Allowed values : max, 30min, 1hour, 3hours, 1day, 1week, 1month
    * @param string $type : type of measurements you wanna retrieve. Ex : "Temperature, CO2, Humidity".
    * @param timestamp (utc) $start (optional) : starting timestamp of requested measurements
    * @param timestamp (utc) $end (optional) : ending timestamp of requested measurements.
    * @param int $limit (optional) : limits numbers of measurements returned (default & max : 1024)
    * @param bool $optimize (optional) : optimize the bandwith usage if true. Optimize = FALSE enables an easier result parsing
    * @param bool $realtime (optional) : Remove time offset (+scale/2) for scale bigger than max
    * @return array of measures and timestamp
    * @brief Method used to retrieve specifig measures of the given weather station
    */
   public function getMeasure($device_id, $module_id, $scale, $type, $start = NULL, $end = NULL, $limit = NULL, $optimize = NULL, $realtime = NULL)
   {
        $params = array('device_id' => $device_id,
                        'scale' => $scale,
                        'type' => $type);

        $optionals = array('module_id' => $module_id,
                           'date_begin' => $start,
                           'date_end' => $end,
                           'limit' => $limit,
                           'optimize' => $optimize,
                           'real_time' => $realtime);
        foreach($optionals as $key => $value)
        {
            if(!is_null($value)) $params[$key] = $value;
        }

       return $this->api('getmeasure', 'GET', $params);
   }

   public function getRainMeasure($device_id, $rainGauge_id, $scale, $start = NULL, $end = NULL, $limit = NULL, $optimize = NULL, $realtime = NULL)
   {
       if($scale === "max")
       {
           $type = "Rain";
       }
       else $type = "sum_rain";

       return $this->getMeasure($device_id, $rainGauge_id, $scale, $type, $start, $end, $limit, $optimize, $realtime);
   }

   public function getWindMeasure($device_id, $windSensor_id, $scale, $start = NULL, $end = NULL, $limit = NULL, $optimize = NULL, $realtime = NULL)
   {
       $type = "WindStrength,WindAngle,GustStrength,GustAngle,date_max_gust";
       return $this->getMeasure($device_id, $windSensor_id, $scale, $type, $start, $end, $limit, $optimize, $realtime);
   }

}

?>
