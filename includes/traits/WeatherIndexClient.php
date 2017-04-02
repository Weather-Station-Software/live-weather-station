<?php

namespace WeatherStation\SDK\Generic\Plugin\Weather\Index;

use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\DateTime\Conversion as Datetime_Conversion;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\ID\Handling as Id_Manipulation;

/**
 * Index client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
trait Client {

    use Id_Manipulation, Datetime_Conversion, Dashboard_Manipulation;

    private $delta_time = 30;
    private $value_unknown = -9999; 
    protected $facility = 'Weather Computer';
    protected $service_name = null;

    /**
     * Compute weather indexes.
     *
     * @param mixed $station_type Optional. The station type to compute.
     * @return array Computed values.
     * @since 2.0.0
     */
    public function compute($station_type=false) {
        $datas = $this->get_reference_values($station_type);
        $result = array();
        foreach ($datas as $id => $data) {
            $temperature_ref = $this->value_unknown;
            $humidity_ref = $this->value_unknown;
            $wind_ref = $this->value_unknown;
            if (array_key_exists('temperature', $data)) {
                $temperature_ref = $data['temperature'];
            }
            if (array_key_exists('humidity', $data)) {
                $humidity_ref = $data['humidity'];
            }
            if (array_key_exists('windstrength', $data)) {
                $wind_ref = $data['windstrength'];
            }
            $place = array();
            $place['country'] = $data['loc_country'];
            $place['city'] = $data['loc_city'];
            $place['altitude'] = $data['loc_altitude'];
            $place['timezone'] = $data['loc_timezone'];
            $place['location'] = array($data['loc_longitude'], $data['loc_latitude']);
            $nm = array();
            $nm['place'] = $place;
            $nm['device_id'] = $id;
            $nm['device_name'] = $data['name'];
            $nm['_id'] = self::get_computed_virtual_id($id);
            $nm['module_name'] = __('[Computed Values]', 'live-weather-station');
            $nm['type'] = 'NAComputed';
            $nm['firmware'] = LWS_VERSION;
            $nm['rf_status'] = 0 ;
            $nm['battery_vp'] = 6000 ;
            $nm['data_type'] = array();
            $nm['dashboard_data'] = array();
            if ( ($temperature_ref != $this->value_unknown) &&
                ($humidity_ref != $this->value_unknown) ) {
                $cbi = $this->compute_cbi($temperature_ref, $humidity_ref);
                $dew_point = $this->compute_dew_point($temperature_ref, $humidity_ref);
                $frost_point = $this->compute_frost_point($temperature_ref, $dew_point);
                $heat_index = $this->compute_heat_index($temperature_ref, $humidity_ref);
                $humidex = $this->compute_humidex($temperature_ref, $dew_point);
                $cloud_ceiling = $this->compute_cloud_ceiling($temperature_ref, $dew_point);
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                $nm['data_type'][] = 'cbi';
                $nm['data_type'][] = 'humidity_ref';
                $nm['data_type'][] = 'dew_point';
                $nm['data_type'][] = 'frost_point';
                $nm['data_type'][] = 'heat_index';
                $nm['data_type'][] = 'humidex';
                $nm['data_type'][] = 'cloud_ceiling';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['humidity_ref'] = $humidity_ref;
                $nm['dashboard_data']['dew_point'] = $dew_point;
                $nm['dashboard_data']['frost_point'] = $frost_point;
                $nm['dashboard_data']['heat_index'] = $heat_index;
                $nm['dashboard_data']['humidex'] = $humidex;
                $nm['dashboard_data']['cloud_ceiling'] =  $cloud_ceiling;
                $nm['dashboard_data']['cbi'] =  $cbi;
            }
            if ( ($temperature_ref != $this->value_unknown) &&
                ($wind_ref != $this->value_unknown) ) {
                $wind_chill = $this->compute_wind_chill($temperature_ref, $wind_ref);
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                $nm['data_type'][] = 'wind_ref';
                $nm['data_type'][] = 'wind_chill';
                $nm['dashboard_data']['wind_time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['wind_ref'] = $wind_ref;
                $nm['dashboard_data']['wind_chill'] = $wind_chill;
            }
            if (count($nm['dashboard_data']) > 0) {
                $result[] = $nm;
            }
        }
        foreach ($result as $data) {
            $this->get_dashboard($data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data'], $data['place']);
            Logger::info($this->facility, $this->service_name, $data['device_id'], $data['device_name'], $data['_id'], $data['module_name'], 0, 'Weather indexes computed.');
        }
        return $result;
    }
}