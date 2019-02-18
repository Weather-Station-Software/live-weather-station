<?php

namespace WeatherStation\SDK\Generic\Plugin\Common;

/**
 * Common utilities for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
trait Utilities {


    /**
     * Computes the Zambretti forecast index.
     *
     * @param float $p Sea level pressure (in Pascal).
     * @param string $trend Pressure trend ('stable', 'up' or 'down').
     * @param float $w Wind direction in degrees.
     * @param boolean $north Optional. True if in north hemisphere, false otherwise.
     * @param integer $pressure_max Optional. Max sea level pressure (in Pascal).
     * @param integer $pressure_min Optional. Min sea level pressure (in Pascal).
     * @return string The computed Zambretti forecast.
     * @since 3.8.0
     */
    protected function compute_zambretti_forecast($p, $trend, $w, $north=true, $pressure_max=105000, $pressure_min=95000) {
        if ($pressure_max - $pressure_min <= 0) {
            $pressure_max = 105000;
            $pressure_min = 95000;
        }
        $rise_options = array('25', '25', '25', '24', '24', '19', '16', '12', '11', '9', '8', '6', '5', '2', '1', '1', '0', '0', '0', '0', '0', '0');
        $steady_options = array('25', '25', '25', '25', '25', '25', '23', '23', '22', '18', '15', '13', '10', '4', '1', '1', '0', '0', '0', '0', '0', '0');
        $fall_options = array('25', '25', '25', '25', '25', '25', '25', '25', '23', '23', '21', '20', '17', '14', '7', '3', '1', '1', '1', '0', '0', '0');
        $pressure_min = $pressure_min / 100;
        $pressure_max = $pressure_max / 100;
        $p = $p / 100;
        while ($w < 0) {
            $w = $w + 360;
        }
        $dir = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N');
        $w = $dir[(int)round((($w % 360) / 22.5) + 0.4)];
        $month = date('n');
        $summer = (($month >= 4) && ($month <= 9));
        $range = ($pressure_max - $pressure_min);
        $constant = round(($range / 22), 3);
        if ($north) {
            if ($w == 'N') {
                $p += 6 / 100 * $range;
            }
            elseif ($w == 'NNE') {
                $p += 5 / 100 * $range;
            }
            elseif ($w == 'NE') {
                $p += 5 / 100 * $range;
            } 
            elseif ($w == 'ENE') {
                $p += 2 / 100 * $range;
            } 
            elseif ($w == 'E') {
                $p -= 0.5 / 100 * $range;
            } 
            elseif ($w == 'ESE') {
                $p -= 2 / 100 * $range;
            } 
            elseif ($w == 'SE') {
                $p -= 5 / 100 * $range;
            } 
            elseif ($w == 'SSE') {
                $p -= 8.5 / 100 * $range;
            } 
            elseif ($w == 'S') {
                $p -= 12 / 100 * $range;
            } 
            elseif ($w == 'SSW') {
                $p -= 10 / 100 * $range;  
            } 
            elseif ($w == 'SW') {
                $p -= 6 / 100 * $range;
            } 
            elseif ($w == 'WSW') {
                $p -= 4.5 / 100 * $range;  
            } 
            elseif ($w == 'W') {
                $p -= 3 / 100 * $range;
            } 
            elseif ($w == 'WNW') {
                $p -= 0.5 / 100 * $range;
            }
            elseif ($w == 'NW') {
                $p += 1.5 / 100 * $range;
            } 
            elseif ($w == 'NNW') {
                $p += 3 / 100 * $range;
            }
            if ($summer) {
                if ($trend == 'up') {
                    $p += 7 / 100 * $range;
                } 
                elseif ($trend == 'down') { 
                    $p -= 7 / 100 * $range;
                }
            }
        } 
        else {
            if ($w == 'S') {
                $p += 6 / 100 * $range;
            }
            elseif ($w == 'SSW') {
                $p += 5 / 100 * $range;
            }
            elseif ($w == 'SW') {
                $p += 5 / 100 * $range;
            }
            elseif ($w == 'WSW') {
                $p += 2 / 100 * $range;
            }
            elseif ($w == 'W') {
                $p -= 0.5 / 100 * $range;
            }
            elseif ($w == 'WNW') {
                $p -= 2 / 100 * $range;
            }
            elseif ($w == 'NW') {
                $p -= 5 / 100 * $range;
            }
            elseif ($w == 'NNW') {
                $p -= 8.5 / 100 * $range;
            }
            elseif ($w == 'N') {
                $p -= 12 / 100 * $range;
            }
            elseif ($w == 'NNE') {
                $p -= 10 / 100 * $range;
            }
            elseif ($w == 'NE') {
                $p -= 6 / 100 * $range;
            }
            elseif ($w == 'ENE') {
                $p -= 4.5 / 100 * $range;
            }
            elseif ($w == 'E') {
                $p -= 3 / 100 * $range;
            }
            elseif ($w == 'ESE') {
                $p -= 0.5 / 100 * $range;
            }
            elseif ($w == 'SE') {
                $p += 1.5 / 100 * $range;
            }
            elseif ($w == 'SSE') {
                $p += 3 / 100 * $range;
            }
            if (!$summer) {
                if ($trend == 'up') {
                    $p += 7 / 100 * $range;
                } elseif ($trend == 'down') {
                    $p -= 7 / 100 * $range;
                }
            }
        }
        if ($p == $pressure_max) {
            $p = $pressure_max - 1;
        }
        $option = floor(($p - $pressure_min) / $constant);
        if ($option < 0) {
            $result = 'X:';
            $option = 0;
        }
        elseif ($option > 21) {
            $result = 'X:';
            $option = 21;
        }
        else {
            $result = 'S:';
        }
        if ($trend == 'up') {
            $result .= $rise_options[$option];
        }
        elseif ($trend == 'down') {
            $result .= $fall_options[$option];
        }
        else {
            $result .= $steady_options[$option];
        }
        return $result;
    }

    /**
     * Computes the vapor pressure.
     *
     * @param float $t Temperature in celcius.
     * @param float $h Humidity in percent.
     * @return float The computed partial vapor pressure (in Pascal).
     * @since 3.3.0
     */
    protected function compute_partial_vapor_pressure($t, $h) {
        $p = $this->compute_saturation_vapor_pressure($t);
        return round($h * $p / 100, 0);
    }

    /**
     * Computes the saturation vapor pressure.
     *
     * @param float $t Temperature in celcius.
     * @return float The computed saturation vapor pressure (in Pascal).
     * @since 3.3.0
     */
    protected function compute_saturation_vapor_pressure($t) {
        if ($t < 0) {
            $result = pow(10, 2.7877 + ((9.756 * $t) / (272.7 + $t)));
        }
        else {
            $result = pow(10, 2.7877 + ((7.625 * $t) / (241.6 + $t)));
        }
        return round($result);
    }

    /**
     * Computes the absolute humidity.
     *
     * @param float $t Temperature in celcius.
     * @param float $p Pressure in pascal.
     * @param float $h Humidity in percent.
     * @return float The computed absolute humidity (in kg/kg).
     * @since 3.3.0
     */
    protected function compute_partial_absolute_humidity($t, $p, $h) {
        $pvap = $this->compute_partial_vapor_pressure($t, $h);
        return round(0.622 * $pvap / ($p - $pvap), 5);
    }

    /**
     * Computes the saturation absolute humidity.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $p Pressure in pascal.
     * @return float The computed saturation absolute humidity (in kg/kg).
     * @since 3.3.0
     */
    protected function compute_saturation_absolute_humidity($t, $p) {
        $pvap = $this->compute_saturation_vapor_pressure($t);
        return round(0.622 * $pvap / ($p - $pvap), 5);
    }

    /**
     * Computes the specific enthalpy.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $p Pressure in pascal.
     * @param integer $h Humidity in percent.
     * @return float The computed specific enthalpy (in J/kg).
     * @since 3.3.0
     */
    protected function compute_specific_enthalpy($t, $p, $h) {
        $x = $this->compute_partial_absolute_humidity($t, $p, $h);
        return round(((1.006 * $t) + ($x * (2501 + (1.83 * $t)))) * 1000);
    }

    /**
     * Computes the air density.
     *
     * @param float $t Temperature in celcius.
     * @param float $p Pressure in pascal.
     * @param float $h Humidity in percent.
     * @return float The computed air density (in kg/m^3).
     * @since 3.3.0
     */
    protected function compute_air_density($t, $p, $h) {
        if ($p == 0) {
            return 0;
        }
        $Ps = $this->compute_saturation_vapor_pressure($t) / $p;
        $Rh = 287.06 / (1 - (($h / 100 ) * $Ps * (1 - (287.06 / 461))));
        return round($p / ($Rh * ($t+273.15)), 5);
    }

    /**
     * Computes the equilibrium moisture content (for wood).
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return float The computed equilibrium moisture content.
     * @since 3.3.0
     */
    protected function compute_emc($t, $h) {
        $t = 1.8 * $t + 32;
        $h = $h / 100;
        $W = 330 + (0.452 * $t) + (0.00415 * pow($t, 2));
        $k = 0.791 + (4.63 * pow (10, -4) * $t) - (8.44 * pow (10, -7) * pow($t, 2));
        $k1 = 6.34 + (7.75 * pow (10, -4) * $t) - (9.35 * pow (10, -5) * pow($t, 2));
        $k2 = 1.09 + (2.84 * pow (10, -4) * $t) - (9.04 * pow (10, -5) * pow($t, 2));
        $a = $k * $h;
        $b = 1 - $a;
        $c = ($k1 * $k * $h) + (2 * $k1 * $k2 * pow($k2, 2) * pow($h, 2));
        $d = 1 + ($k1 * $k * $h) + ($k1 * $k2 * pow($k2, 2) * pow($h, 2));
        return round((1800 / $W) * (($a / $b) + ($c / $d)), 1);
    }

    /**
     * Computes the wet bulb temperature.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return float The computed wet bulb temperature (in celcius).
     * @since 3.3.0
     */
    protected function compute_wet_bulb($t, $h) {
        return round((0.2831 * pow ($h, 0.2735) * $t) + (0.0003018 * pow($h, 2)) + (0.01289 * $h) - 4.0962, 1);
    }

    /**
     * Computes the delta-t.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return float The computed delta-t (in celcius).
     * @since 3.7.0
     */
    protected function compute_delta_t($t, $h) {
        return round($t - $this->compute_wet_bulb($t, $h), 1);
    }

    /**
     * Computes the potential temperature.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $p Pressure in pascal.
     * @return float The computed potential temperature (in celcius).
     * @since 3.3.0
     */
    protected function compute_potential_temperature($t, $p) {
        return round( $t * pow((100000 / $p), 0.286), 1);
    }

    /**
     * Computes the equivalent temperature.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $p Pressure in pascal.
     * @return float The computed equivalent temperature (in celcius).
     * @since 3.3.0
     */
    protected function compute_equivalent_temperature($t, $p) {
        $r = $this->compute_saturation_absolute_humidity($t, $p);
        $lc = 2264.76 / 1004;
        return round( $t + ($lc * $r), 1);
    }

    /**
     * Computes the equivalent potential temperature.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $p Pressure in pascal.
     * @return float The computed equivalent potential temperature (in celcius).
     * @since 3.3.0
     */
    protected function compute_equivalent_potential_temperature($t, $p) {
        if ($t == 0) {
            return 0.0;
        }
        $te = $this->compute_equivalent_temperature($t, $p);
        $tp = $this->compute_potential_temperature($t, $p);
        return round( $te * $tp / $t, 1);
    }

    /**
     * Computes the density altitude.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $p Pressure in pascal.
     * @param integer $h Humidity in percent.
     * @return integer The computed density altitude (in meters).
     * @since 3.8.0
     */
    protected function compute_density_altitude($t, $p, $h) {
        $k = 145366.45 * 0.3048;
        $d = $this->compute_dew_point($t, $h);
        $e = 6.11 * pow(10, (7.5 * $d) / (237.7 + $d));
        $tv = ($t + 273.15) / (1 - ((1 - 0.622) * ($e * 100 / $p)));
        $tv = ((9 / 5) * ($tv - 273.15) + 32) + 459.69;
        $pres = ($p / 3386.39);
        return round( $k * (1 - pow((17.326 * $pres / $tv), 0.235)), 0);
    }

    /**
     * Computes the pressure altitude.
     *
     * @param integer $p Pressure in pascal.
     * @return integer The computed pressure altitude (in meters).
     * @since 3.8.0
     */
    protected function compute_pressure_altitude($p) {
        $k = 145366.45 * 0.3048;
        return round( $k * (1 - pow(($p / 101325), 0.190284)), 0);
    }

    /**
     * Computes the Chandler Burning index.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return float The computed Chandler Burning index.
     * @since 3.1.0
     */
    protected function compute_cbi($t, $h) {
        return round((((110 - 1.373 * $h) - 0.54 * (10.20 - $t)) * (124 * pow(10,(-0.0142 * $h))))/60, 1);
    }

    /**
     * Computes the dew point.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return float The computed dew point.
     * @since 3.1.0
     */
    protected function compute_dew_point($t, $h) {
        return round((pow(($h/100),1/8) * (112 + (0.9 * $t)) + (0.1 * $t) - 112), 1);
    }

    /**
     * Computes the frost point.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $dew Dew point temperature in celcius.
     * @return float The computed frost point.
     * @since 3.1.0
     */
    protected function compute_frost_point($t, $dew) {
        $t = $t + 273.15;
        $dew = $dew + 273.15;
        $result = $dew - $t + (2671.02/((2954.61/$t)+(2.193665*log($t))-13.3448));
        return round($result - 273.15, 1);
    }

    /**
     * Computes the heat index.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return integer The heat index.
     * @since 3.1.0
     */
    protected function compute_heat_index($t, $h) {
        $c1 = -42.379;
        $c2 = 2.04901523;
        $c3 = 10.14333127;
        $c4 = -0.22475541;
        $c5 = -6.83783 * 0.001;
        $c6 = -5.481717 * 0.01;
        $c7 = 1.22874 * 0.001;
        $c8 = 8.5282 * 0.0001;
        $c9 = -1.99 * 0.000001;
        $t = (1.8 * $t) + 32;
        $result =    $c1 +
                    ($c2 * $t) +
                    ($c3 * $h) +
                    ($c4 * $t * $h) +
                    ($c5 * $t * $t) +
                    ($c6 * $h * $h) +
                    ($c7 * $t * $t * $h) +
                    ($c8 * $t * $h * $h) +
                    ($c9 * $t * $t * $h * $h);
        return round(($result-32)/1.8);
    }

    /**
     * Computes the humidex index.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $dew Dew point temperature in celcius.
     * @return float The computed humidex.
     * @since 3.1.0
     */
    protected function compute_humidex($t, $dew) {
        $dew = $dew + 273.15;
        $e = 6.11 * exp(5417.7530*((1/273.16)-(1/$dew)));
        $result = $t + (0.5555*($e-10));
        return round($result);
    }

    /**
     * Computes the wind chill.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $w Windspeed in km/h.
     * @return float The computed wind chill.
     * @since 3.1.0
     */
    protected function compute_wind_chill($t, $w) {
        if ($w < 4.8) {
            $result = $t + ( 0.2 * $w * (0.1345 * $t - 1.59 ) );
        }
        else {
            $result = 13.12 + (0.6215 * $t) + pow($w, 0.16) * (0.3965 * $t - 11.37);
        }
        return round($result, 1);
    }

    /**
     * Computes the Steadman index (Australian Apparent Temperature).
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @param integer $w Windspeed in km/h.
     * @return float The computed Steadman index.
     * @since 3.7.0
     */
    protected function compute_steadman($t, $h, $w) {
        $b = $this->compute_wet_bulb($t, $h);
        $v = $this->compute_partial_vapor_pressure($t, $h) / 100;
        $w = $w / 3.6;
        return round($b - 4 + ($v / 3) + ($w * 0.7), 1);
    }

    /**
     * Computes the Summer Simmer index.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $h Humidity in percent.
     * @return integer The summer simmer index.
     * @since 3.1.0
     */
    protected function compute_summer_simmer($t, $h) {
        $t = 1.8 * $t + 32;
        $result = 1.98 * ($t - (0.55 - 0.0055 * $h) * ($t - 58)) - 56.83;
        return round(($result - 32) / 1.8, 1);
    }

    /**
     * Computes the cloud ceiling distance.
     *
     * @param integer $t Temperature in celcius.
     * @param integer $dew Dew point temperature in celcius.
     * @return float The computed cloud ceiling distance in meters.
     * @since 3.1.0
     */
    protected function compute_cloud_ceiling($t, $dew) {
        $result = 125 * ($t - $dew);
        if ($result<0) {
            $result = 0 ;
        }
        return round($result);
    }

    /**
     * Compute health index from 0 (unhealthy) to 100 (healthy).
     *
     * @param float $temperature Temperature in °C. Null if unknown.
     * @param float $humidity Humidity in %. Null if unknown.
     * @param float $co2 CO2 in ppm. Null if unknown.
     * @param float $noise Noise level in dB. Null if unknown.
     * @return array Computed health index and reasons.
     * @since 3.1.0
     */
    public function compute_health_index($temperature, $humidity, $co2, $noise) {
        $max = 100000;
        $result = array();
        if (isset($temperature)) {
            $val = abs($temperature-19);
            if ($val < 1) {
                $result['hi_temperature'] = 15;
            }
            elseif ($val < 2) {
                $result['hi_temperature'] = 5;
            }
            elseif ($val < 3) {
                $result['hi_temperature'] = -15;
            }
            elseif ($val < 4) {
                $result['hi_temperature'] = -25;
            }
            elseif ($val < 5) {
                $result['hi_temperature'] = -35;
            }
            elseif ($val < 6) {
                $result['hi_temperature'] = -45;
            }
            elseif ($val < 7) {
                $result['hi_temperature'] = -70;
            }
            elseif ($val < 8) {
                $result['hi_temperature'] = -100;
            }
            else {
                $result['hi_temperature'] = -200;
            }
        }
        if (isset($humidity)) {
            $val = abs($humidity-52);
            if ($val < 3) {
                $result['hi_humidity'] = 3;
            }
            elseif ($val < 6) {
                $result['hi_humidity'] = 0;
            }
            elseif ($val < 9) {
                $result['hi_humidity'] = -3;
            }
            elseif ($val < 12) {
                $result['hi_humidity'] = -6;
            }
            elseif ($val < 15) {
                $result['hi_humidity'] = -9;
            }
            elseif ($val < 20) {
                $result['hi_humidity'] = -15;
            }
            elseif ($val < 25) {
                $result['hi_humidity'] = -30;
            }
            else {
                $result['hi_humidity'] = -100;
            }
        }
        if (isset($co2)) {
            $result['hi_co2'] = (600 - $co2) / 20; // 400 => +10  2000 => -70
        }
        if (isset($noise)) {
            if ($noise < 25) {
                $result['hi_noise'] = 15;
            }
            elseif ($noise < 30) {
                $result['hi_noise'] = 10;
            }
            elseif ($noise < 35) {
                $result['hi_noise'] = 5;
            }
            elseif ($noise < 40) {
                $result['hi_noise'] = -1;
            }
            elseif ($noise < 45) {
                $result['hi_noise'] = -5;
            }
            elseif ($noise < 50) {
                $result['hi_noise'] = -15;
            }
            elseif ($noise < 55) {
                $result['hi_noise'] = -40;
            }
            else {
                $result['hi_noise'] = -100;
            }
        }
        if (isset($temperature) && isset($humidity)) {
            $dew = $this->compute_dew_point($temperature, $humidity);
            $humidex = $this->compute_humidex($temperature, $dew);
        }
        else {
            $dew = null;
            $humidex = null;
        }
        if (isset($dew)) {
            $val = $temperature-$dew;
            if ($val < 1) {
                $result['hi_dew'] = -30;
            }
            elseif ($val < 3) {
                $result['hi_dew'] = 0;
            }
            else {
                $result['hi_dew'] = 4;
            }
        }
        if (isset($humidex)) {
            if ($humidex < 25) {
                $result['hi_humidex'] = 10;
            }
            elseif ($humidex < 30) {
                $result['hi_humidex'] = -1;
            }
            elseif ($humidex < 35) {
                $result['hi_humidex'] = -10;
            }
            elseif ($humidex < 40) {
                $result['hi_humidex'] = -20;
            }
            elseif ($humidex < 45) {
                $result['hi_humidex'] = -30;
            }
            elseif ($humidex < 50) {
                $result['hi_humidex'] = -40;
            }
            else {
                $result['hi_humidex'] = $max;
            }
        }
        $val = 50;
        foreach ($result as $idx) {
            $val += $idx;
        }
        if ($val > 100) {
            $val = 100;
        }
        if ($val < 0) {
            $val = 0;
        }
        if (count($result) > 1) {
            $result['health_idx'] = round($val, 0);
        }
        else {
            $result = array();
        }
        return $result;
    }
}