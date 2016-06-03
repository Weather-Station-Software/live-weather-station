<?php

/**
 * Weather client for Live Weather Station plugin
 *
 * @since      2.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-datas-query.php');
require_once(LWS_INCLUDES_DIR.'trait-id-manipulation.php');
require_once(LWS_INCLUDES_DIR.'trait-dashboard-manipulation.php');
require_once(LWS_INCLUDES_DIR.'trait-datetime-conversion.php');


trait Weather_Client {

    use Id_Manipulation, Datetime_Conversion, Dashboard_Manipulation;

    private $delta_time = 30;
    private $value_unknown = -9999;
    protected $facility = 'Weather Computer';
    protected $service_name = null;


    /**
     * Computes the dew point.
     *
     * @param   integer  $t      Temperature in celcius.
     * @param   integer  $h      Humidity in percent.
     * @return  float   The computed dew point.
     *
     * @since    2.0.0
     */
    private function get_dew_point($t, $h) {
        return round((pow(($h/100),1/8) * (112 + (0.9 * $t)) + (0.1 * $t) - 112), 1);
    }

    /**
     * Computes the frost point.
     *
     * @param   integer  $t      Temperature in celcius.
     * @param   integer  $dew    Dew point temperature in celcius.
     * @return  float   The computed frost point.
     *
     * @since    2.0.0
     */
    private function get_frost_point($t, $dew) {
        $t = $t + 273.15;
        $dew = $dew + 273.15;
        $result = $dew - $t + (2671.02/((2954.61/$t)+(2.193665*log($t))-13.3448));
        return round($result - 273.15, 1);
    }

    /**
     * Computes the heat index.
     *
     * @param   integer  $t      Temperature in celcius.
     * @param   integer  $h      Humidity in percent.
     * @return  integer   The heat index.
     *
     * @since    2.0.0
     */
    private function get_heat_index($t, $h) {
        $c1 = -42.379;
        $c2 = 2.04901523;
        $c3 = 10.14333127;
        $c4 = -0.22475541;
        $c5 = -6.83783 * 0.001;
        $c6 = -5.481717 * 0.01;
        $c7 = 1.22874 * 0.001;
        $c8 = 8.5282 * 0.0001;
        $c9 = -1.99 * 0.000001;
        $t = 1.8 * $t + 32;
        $result = $c1 +
            ( $c2 * $t ) +
            ( $c3 * $h ) +
            ( $c4 * $t * $h ) +
            ( $c5 * $t * $t ) +
            ( $c6 * $h * $h ) +
            ( $c7 * $t * $t * $h ) +
            ( $c8 * $t * $h * $h) +
            ( $c9 * $t * $t * $h * $h);
        return round(($result-32)/1.8);
    }

    /**
     * Computes the humidex index.
     *
     * @param   integer  $t      Temperature in celcius.
     * @param   integer  $dew    Dew point temperature in celcius.
     * @return  float   The computed humidex.
     *
     * @since    2.0.0
     */
    private function get_humidex($t, $dew) {
        $dew = $dew + 273.15;
        $e = 6.11 * exp(5417.7530*((1/273.16)-(1/$dew)));
        $result = $t + (0.5555*($e-10));
        return round($result);
    }

    /**
     * Computes the wind chill.
     *
     * @param   integer  $t      Temperature in celcius.
     * @param   integer  $w      Windspeed in km/h.
     * @return  float   The computed wind chill.
     *
     * @since    2.0.0
     */
    private function get_wind_chill($t, $w) {
        if ($w < 4.8) {
            $result = $t + ( 0.2 * $w * (0.1345 * $t - 1.59 ) );
        }
        else {
            $result = 13.12 + (0.6215 * $t) + pow($w, 0.16) * (0.3965 * $t - 11.37);
        }
        return round($result, 1);
    }

    /**
     * Computes the cloud ceiling distance.
     *
     * @param   integer  $t      Temperature in celcius.
     * @param   integer  $dew    Dew point temperature in celcius.
     * @return  float   The computed cloud ceiling distance in meters.
     *
     * @since    2.0.0
     */
    private function get_cloud_ceiling($t, $dew) {
        $result = 125 * ($t - $dew);
        if ($result<0) {
            $result = 0 ;
        }
        return round($result);
    }

    /**
     * Compute dew point, frost point, heat index, humidex and wind chill.
     *
     * @since    2.0.0
     */
    public function compute() {
        $datas = $this->get_reference_values();
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
            $nm = array();
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
                $dew_point = $this->get_dew_point($temperature_ref, $humidity_ref);
                $frost_point = $this->get_frost_point($temperature_ref, $dew_point);
                $heat_index = $this->get_heat_index($temperature_ref, $humidity_ref);
                $humidex = $this->get_humidex($temperature_ref, $dew_point);
                $cloud_ceiling = $this->get_cloud_ceiling($temperature_ref, $dew_point);
                if (!in_array('temperature_ref', $nm['data_type'])) {
                    $nm['data_type'][] = 'temperature_ref';
                }
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
            }
            if ( ($temperature_ref != $this->value_unknown) &&
                ($wind_ref != $this->value_unknown) ) {
                $wind_chill = $this->get_wind_chill($temperature_ref, $wind_ref);
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
                $data['type'], $data['data_type'], $data['dashboard_data']);
            Logger::debug($this->facility, $this->service_name, $data['device_id'], $data['device_name'], $data['_id'], $data['module_name'], 0, 'Success while computing weather indexes.');
        }
        return $result;
    }
}