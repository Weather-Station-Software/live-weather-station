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

    private $delta_time = 1800;
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
            $winddirection_ref = $this->value_unknown;
            $pressure_ref = $this->value_unknown;
            $pressure_sl_ref = $this->value_unknown;
            $pressure_trend_ref = 'stable';
            if (array_key_exists('temperature', $data)) {
                $temperature_ref = $data['temperature'];
            }
            if (array_key_exists('humidity', $data)) {
                $humidity_ref = $data['humidity'];
            }
            if (array_key_exists('windstrength', $data)) {
                $wind_ref = $data['windstrength'];
            }
            if (array_key_exists('winddirection', $data)) {
                $winddirection_ref = $data['winddirection'];
            }
            if (array_key_exists('pressure', $data)) {
                $pressure_ref = $data['pressure'];
            }
            if (array_key_exists('pressure_trend', $data)) {
                $pressure_trend_ref = $data['pressure_trend'];
            }
            if (array_key_exists('pressure_sl', $data)) {
                $pressure_sl_ref = $data['pressure_sl'];
            }
            $place = array();
            $place['country'] = $data['loc_country'];
            $place['city'] = $data['loc_city'];
            $place['altitude'] = $data['loc_altitude'];
            $place['timezone'] = $data['loc_timezone'];
            if (array_key_exists('loc_longitude', $datas) && array_key_exists('loc_latitude', $datas)) {
                $place['location'] = array($data['loc_longitude'], $data['loc_latitude']);
            }
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
                $dew_point = $this->compute_dew_point($temperature_ref, $humidity_ref);
                $frost_point = $this->compute_frost_point($temperature_ref, $dew_point);
                $humidex = $this->compute_humidex($temperature_ref, $dew_point);
                $cloud_ceiling = $this->compute_cloud_ceiling($temperature_ref, $dew_point);
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                if (!in_array('humidity_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'humidity_ref';
                }
                $nm['data_type'][] = 'cbi';
                $nm['data_type'][] = 'dew_point';
                $nm['data_type'][] = 'frost_point';
                $nm['data_type'][] = 'heat_index';
                $nm['data_type'][] = 'humidex';
                $nm['data_type'][] = 'cloud_ceiling';
                $nm['data_type'][] = 'wet_bulb';
                $nm['data_type'][] = 'delta_t';
                $nm['data_type'][] = 'partial_vapor_pressure';
                $nm['data_type'][] = 'wood_emc';
                $nm['data_type'][] = 'summer_simmer';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['humidity_ref'] = $humidity_ref;
                $nm['dashboard_data']['dew_point'] = $dew_point;
                $nm['dashboard_data']['frost_point'] = $frost_point;
                $nm['dashboard_data']['heat_index'] = $this->compute_heat_index($temperature_ref, $humidity_ref);
                $nm['dashboard_data']['humidex'] = $humidex;
                $nm['dashboard_data']['cloud_ceiling'] = $cloud_ceiling;
                $nm['dashboard_data']['cbi'] = $this->compute_cbi($temperature_ref, $humidity_ref);
                $nm['dashboard_data']['wet_bulb'] = $this->compute_wet_bulb($temperature_ref, $humidity_ref);
                $nm['dashboard_data']['delta_t'] = $this->compute_delta_t($temperature_ref, $humidity_ref);
                $nm['dashboard_data']['partial_vapor_pressure'] = $this->compute_partial_vapor_pressure($temperature_ref, $humidity_ref);
                $nm['dashboard_data']['wood_emc'] = $this->compute_emc($temperature_ref, $humidity_ref);
                $nm['dashboard_data']['summer_simmer'] = $this->compute_summer_simmer($temperature_ref, $humidity_ref);
            }
            if ( ($temperature_ref != $this->value_unknown) &&
                ($humidity_ref != $this->value_unknown) &&
                ($pressure_ref != $this->value_unknown)) {
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                if (!in_array('humidity_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'humidity_ref';
                }
                if (!in_array('pressure_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'pressure_ref';
                }
                $nm['data_type'][] = 'air_density';
                $nm['data_type'][] = 'partial_absolute_humidity';
                $nm['data_type'][] = 'specific_enthalpy';
                $nm['data_type'][] = 'alt_density';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['humidity_ref'] = $humidity_ref;
                $nm['dashboard_data']['pressure_ref'] = $pressure_ref;
                $nm['dashboard_data']['air_density'] = $this->compute_air_density($temperature_ref, 100 * $pressure_ref, $humidity_ref);
                $nm['dashboard_data']['partial_absolute_humidity'] = $this->compute_partial_absolute_humidity($temperature_ref, 100 * $pressure_ref, $humidity_ref);
                $nm['dashboard_data']['specific_enthalpy'] = $this->compute_specific_enthalpy($temperature_ref, 100 * $pressure_ref, $humidity_ref);
                $nm['dashboard_data']['alt_density'] = $this->compute_density_altitude($temperature_ref, 100 * $pressure_ref, $humidity_ref);
            }

            if ( ($temperature_ref != $this->value_unknown) &&
                ($pressure_ref != $this->value_unknown)) {
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                if (!in_array('pressure_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'pressure_ref';
                }
                $nm['data_type'][] = 'saturation_absolute_humidity';
                $nm['data_type'][] = 'potential_temperature';
                $nm['data_type'][] = 'equivalent_temperature';
                $nm['data_type'][] = 'equivalent_potential_temperature';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['pressure_ref'] = $pressure_ref;
                $nm['dashboard_data']['saturation_absolute_humidity'] = $this->compute_saturation_absolute_humidity($temperature_ref, 100 * $pressure_ref);
                $nm['dashboard_data']['potential_temperature'] = $this->compute_potential_temperature($temperature_ref, 100 * $pressure_ref);
                $nm['dashboard_data']['equivalent_temperature'] = $this->compute_equivalent_temperature($temperature_ref, 100 * $pressure_ref);
                $nm['dashboard_data']['equivalent_potential_temperature'] = $this->compute_equivalent_potential_temperature($temperature_ref, 100 * $pressure_ref);
            }
            if ($pressure_ref != $this->value_unknown) {
                if (!in_array('pressure_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'pressure_ref';
                }
                $nm['data_type'][] = 'alt_pressure';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['pressure_ref'] = $pressure_ref;
                $nm['dashboard_data']['alt_pressure'] = $this->compute_pressure_altitude(100 * $pressure_ref);
            }
            if ($temperature_ref != $this->value_unknown) {
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                $nm['data_type'][] = 'saturation_vapor_pressure';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['saturation_vapor_pressure'] = $this->compute_saturation_vapor_pressure($temperature_ref);
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
            if ( ($temperature_ref != $this->value_unknown) &&
                ($humidity_ref != $this->value_unknown) &&
                ($wind_ref != $this->value_unknown) ) {
                $steadman = $this->compute_steadman($temperature_ref, $humidity_ref, $wind_ref);
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
                if (!in_array('humidity_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'humidity_ref';
                }
                if (!in_array('wind_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'wind_ref';
                }
                $nm['data_type'][] = 'steadman';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['temperature_ref'] = $temperature_ref;
                $nm['dashboard_data']['humidity_ref'] = $humidity_ref;
                $nm['dashboard_data']['wind_ref'] = $wind_ref;
                $nm['dashboard_data']['steadman'] = $steadman;
            }
            if ( ($pressure_sl_ref != $this->value_unknown) &&
                ($winddirection_ref != $this->value_unknown) ) {
                $zcast = $this->compute_zambretti_forecast(100 * $pressure_sl_ref, $pressure_trend_ref, $winddirection_ref, $data['north'], 100 * $data['pressure_sl_max'], 100 * $data['pressure_sl_min']);
                $nm['data_type'][] = 'zcast_live';
                $nm['dashboard_data']['time_utc'] = time();
                $nm['dashboard_data']['zcast_live'] = $zcast;
                if (array_key_exists('loc_timezone', $data)) {
                    $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
                    $datetime->setTimezone(new \DateTimeZone($data['loc_timezone']));
                    $h = (int)$datetime->format('H');
                    $m = (int)$datetime->format('i');
                    if ($h == 9 && $m < 22) {
                        $nm['data_type'][] = 'zcast_best';
                        $nm['dashboard_data']['zcast_best'] = $zcast;
                    }
                }
            }
            if (count($nm['dashboard_data']) > 0) {
                $result[] = $nm;
            }
        }
        foreach ($result as $data) {
            $this->get_dashboard(9999, $data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data'], $data['place']);
            Logger::info($this->facility, $this->service_name, $data['device_id'], $data['device_name'], $data['_id'], $data['module_name'], 0, 'Weather indexes computed.');
        }
        return $result;
    }
}