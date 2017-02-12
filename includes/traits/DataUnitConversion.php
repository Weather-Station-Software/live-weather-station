<?php

namespace WeatherStation\Data\Unit;

/**
 * Units conversions functionalities for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Conversion {

    private $radio_correct_db = 35;
    private $wifi_correct_db = 1;
    private $battery_max = 6000;
    private $signal_max = 0;
    private $battery_cutoff = 500;
    private $signal_cutoff = 10;
    private $beaufort_thresholds = array(1.1, 5.5, 11.9, 19.7, 28.7, 38.8, 49.9, 61.8, 74.6, 88.1, 102.4, 117.4, 143);

    /**
     * Get the health index expressed in its unique unit.
     *
     * @param mixed $value The value of the health index.
     * @return string The health index expressed in its unique unit.
     * @since 3.1.0
     */
    protected function get_health_index($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the Chandler Burning index expressed in its unique unit.
     *
     * @param mixed $value The value of the Chandler Burning index.
     * @return string The Chandler Burning index expressed in its unique unit.
     * @since 3.1.0
     */
    protected function get_cbi($value)
    {
        $result = $value;
        return sprintf('%.1F', round($result, 1));
    }

    /**
     * Get the mass mixing ratio from volume mixing ratio.
     *
     * @param float $vmr The volume mixing ratio value.
     * @param float $density The density/air of the converted gas.
     * @return float The mass mixing ratio value.
     * @since 3.1.0
     */
    protected function convert_from_vmr_to_mmr($vmr, $density)
    {
        $result = $vmr / $density;
        return $result;
    }

    /**
     * Get the mass concentration from volume mixing ratio.
     *
     * @param float $vmr The volume mixing ratio value.
     * @param float $molecular_mass The molecular mass of the converted gas.
     * @param float $molar_volume Optional. The molar volume of the air.
     * @return float The mass concentration value.
     * @since 3.1.0
     */
    protected function convert_from_vmr_to_mass_concentration($vmr, $molecular_mass, $molar_volume=24.45 )
    {
        $result = ($vmr * $molecular_mass) / $molar_volume;
        return $result;
    }

    /**
     * Get the partial pressure from volume mixing ratio.
     *
     * @param float $vmr The volume mixing ratio value.
     * @param float $pressure Optional. The total gas pressure (in Pa).
     * @return float The partial pressure value.
     * @since 3.1.0
     */
    protected function convert_from_vmr_to_partial_pressure($vmr, $pressure=100000.0)
    {
        $result = $vmr * $pressure / 1000000;
        return $result;
    }

    /**
     * Get the co2 expressed in specific unit.
     *
     * @param mixed $value The value of the co2.
     * @param integer $id Optional. The unit id.
     * @return string The co2 expressed in specific unit.
     * @since 1.0.0
     */
    protected function get_co2($value, $id=0)
    {
        $result = $value;
        $format = '%d';
        $prec = 0;
        switch ($id) {
            case 1:
                $result = $this->convert_from_vmr_to_mmr($value, 1.53);
                break;
            case 2:
                $result = $this->convert_from_vmr_to_mass_concentration($value, 44.01);
                break;
            case 3:
                $result = $this->convert_from_vmr_to_partial_pressure($value, 101325);
                $format = '%.1F';
                $prec = 1;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get the CO expressed in specific unit.
     *
     * @param mixed $value The value of the CO.
     * @param integer $id Optional. The unit id.
     * @return string The CO expressed in specific unit.
     * @since 2.7.0
     */
    protected function get_co($value, $id=0)
    {
        $value *= 1000;
        $result = $value;
        $format = '%d';
        $prec = 0;
        switch ($id) {
            case 1:
                $result = $this->convert_from_vmr_to_mmr($value, 0.97);
                break;
            case 2:
                $result = $this->convert_from_vmr_to_mass_concentration($value, 28.01);
                break;
            case 3:
                $result = $this->convert_from_vmr_to_partial_pressure($value);
                //$format = '%.1F';
                //$prec = 1;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get the SO2 expressed in specific unit.
     *
     * @param mixed $value The value of the SO2.
     * @param integer $id Optional. The unit id.
     * @return string The SO2 expressed in specific unit.
     * @since 3.1.0
     */
    protected function get_so2($value, $id=0)
    {
        $value *= 1000;
        $result = $value;
        $format = '%d';
        $prec = 0;
        switch ($id) {
            case 1:
                $result = $this->convert_from_vmr_to_mmr($value, 2.22);
                break;
            case 2:
                $result = $this->convert_from_vmr_to_mass_concentration($value, 64.06);
                break;
            case 3:
                $result = $this->convert_from_vmr_to_partial_pressure($value);
                //$format = '%.1F';
                //$prec = 1;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get the NO2 expressed in specific unit.
     *
     * @param mixed $value The value of the NO2.
     * @param integer $id Optional. The unit id.
     * @return string The NO2 expressed in specific unit.
     * @since 3.1.0
     */
    protected function get_no2($value, $id=0)
    {
        $value *= 1000;
        $result = $value;
        $format = '%d';
        $prec = 0;
        switch ($id) {
            case 1:
                $result = $this->convert_from_vmr_to_mmr($value, 1.88);
                break;
            case 2:
                $result = $this->convert_from_vmr_to_mass_concentration($value, 46.01);
                break;
            case 3:
                $result = $this->convert_from_vmr_to_partial_pressure($value);
                //$format = '%.1F';
                //$prec = 1;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get the o3 expressed in its unique unit.
     *
     * @param   mixed   $value  The value of the co2.
     * @return  string  The o3 expressed in its unique unit.
     * @since    2.7.0
     * @access   protected
     */
    protected function get_o3($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get o3 expressed in standard unit.
     *
     * @param mixed $value The value of o3.
     * @return string The o3 expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_o3($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the humidity expressed in its unique unit.
     *
     * @param   mixed   $value  The value of the humidity.
     * @return  string  The humidity expressed in its unique unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_humidity($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get humidity expressed in standard unit.
     *
     * @param mixed $value The value of humidity.
     * @return string The humidity expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_humidity($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the moon illumibation expressed in its unique unit.
     *
     * @param   mixed   $value  The value of the humidity.
     * @return  string  The moon illumibation expressed in its unique unit.
     * @since    2.0.0
     */
    protected function get_moon_illumination($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the cloudiness expressed in its unique unit.
     *
     * @param   mixed   $value  The value of the cloudiness.
     * @return  string  The cloudiness expressed in its unique unit.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_cloudiness($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get cloudiness expressed in standard unit.
     *
     * @param mixed $value The value of cloudiness.
     * @return string The cloudiness expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_cloudiness($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the noise expressed in its unique unit.
     *
     * @param   mixed $value  The value of the noise.
     * @return  string  The noise expressed in its unique unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_noise($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get noise expressed in standard unit.
     *
     * @param mixed $value The value of noise.
     * @return string The noise expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_noise($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the rain expressed in its unique unit.
     *
     * @param   mixed   $value The value of the rain.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The rain expressed in its unique unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_rain($value, $id = 0)
    {
        $result = $value;
        $format = '%.1F';
        $prec = 1;
        switch ($id) {
            case 2:  // l(in) = l(mm) / 25.4
            case 3:
                $result = $value / 25.4;
                $format = '%.2F';
                $prec = 2;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get rain expressed in standard unit.
     *
     * @param mixed $value The value of rain.
     * @param integer $id The unit id.
     * @return string The rain expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_rain($value, $id)
    {
        $result = $value;
        switch ($id) {
            case 1:  // l(mm) = l(in) * 25.4
                $result = $value * 25.4;
                break;
        }
        return sprintf('%.1F', round($result, 1));
    }

    /**
     * Get the degree diameter expressed in its unique unit.
     *
     * @param   mixed   $value The value of the degree diameter.
     * @return  string  The rain expressed in its unique unit.
     * @since    2.0.0
     */
    protected function get_degree_diameter($value)
    {
        $result = $value;
        return sprintf('%.4F', round($result, 4));
    }

    /**
     * Get the snow expressed in specific unit.
     *
     * @param   mixed   $value  The value of the snow.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The snow expressed in specific unit.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_snow($value, $id = 0)
    {
        $result = $value / 10;
        if ($value > 0 && $result < 1) {
            $result = 1;
        }
        $format = '%d';
        $prec = 0;
        switch ($id) {
            case 1:
                $result = $value / 25.4;
                $format = '%.1F';
                $prec = 1;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get snow expressed in standard unit.
     *
     * @param mixed $value The value of snow.
     * @param integer $id The unit id.
     * @return string The snow expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_snow($value, $id)
    {
        $result = $value;
        switch ($id) {
            case 0:
                $result = $value * 10;
                break;
            case 1:
                $result = $value * 25.4;
                break;
        }
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the day expressed in its unique unit.
     *
     * @param mixed $value The value of the day.
     * @return string The day length expressed in its unique unit.
     * @since 3.1.0
     */
    protected function get_day_length($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the dusk or dawn expressed in its unique unit.
     *
     * @param mixed $value The value of the dusk or dawn twilight.
     * @return string The dusk or dawn twilight expressed in its unique unit.
     * @since 3.1.0
     */
    protected function get_dusk_dawn($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the wind angle expressed in its unique unit.
     *
     * @param   mixed   $value  The value of the wind angle.
     * @return  string  The wind angle expressed in its unique unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wind_angle($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get wind angle expressed in standard unit.
     *
     * @param mixed $value The value of wind angle.
     * @return string The wind angle expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_wind_angle($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the temperature expressed in specific unit.
     *
     * @param   mixed   $value  The value of the temperature.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The temperature expressed in specific unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_temperature($value, $id = 0)
    {
        $result = $value;
        switch ($id) {
            case 1:  // T(°F) = 1.8 T(°C) + 32
                $result = 1.8 * $result + 32;
                break;
            case 2:  // T(K) = T(°C) + 273.15
                $result = $result + 273.15;
                break;
        }
        return sprintf('%.1F', round($result, 1));
    }

    /**
     * Get temperature expressed in standard unit.
     *
     * @param mixed $value The value of temperature.
     * @param integer $id The unit id.
     * @return string The temperature expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_temperature($value, $id)
    {
        $result = $value;
        switch ($id) {
            case 1:  // T(°C) = ( T(°F) - 32 ) / 1.8
                $result = ($result - 32) / 1.8;
                break;
            case 2:  // T(°C) = T(K) + 273.15
                $result = $result - 273.15;
                break;
        }
        return sprintf('%.1F', round($result, 1));
    }

    /**
     * Get the pressure expressed in specific unit.
     *
     * @param   mixed   $value  The value of the pressure.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The pressure expressed in specific unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_pressure($value, $id = 0)
    {
        $result = $value;
        $format = '%.1F';
        $prec = 1;
        switch ($id) {
            case 1:  // P(inHg) = P(hPa) / 33.8639
                $result = $result / 33.8639;
                $format = '%.2F';
                $prec = 2;
                break;
            case 2:  // P(mmHg) = P(hPa) / 1.33322368
                $result = $result / 1.33322368;
                break;
            case 3:
                $format = '%.1F';
                $prec = 1;
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get the pressure expressed in standard unit.
     *
     * @param mixed $value The value of the pressure.
     * @param integer $id The unit id.
     * @return  string  The pressure expressed in standard unit.
     * @since    3.0.0
     */
    protected function get_reverse_pressure($value, $id)
    {
        $result = $value;
        switch ($id) {
            case 1:  // P(hPa) = P(inHg) * 33.8639
                $result = $result * 33.8639;
                break;
            case 2:  // P(hPa) = P(mmHg) * 1.33322368
                $result = $result * 1.33322368;
                break;
        }
        return sprintf('%.1F', round($result, 1));
    }

    /**
     * Get the wind speed expressed in specific unit.
     *
     * @param   mixed   $value  The value of the wind speed.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The wind speed expressed in specific unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wind_speed($value, $id = 0)
    {
        $result = $value;
        $format = '%d';
        $prec = 0;
        switch ($id) {
            case 1:  // V(mph) = V(km/h) / 1.609344
                $result = $result / 1.609344;
                if ($result < 10) {
                    $format = '%.1F';
                }
                break;
            case 6:  // V(mph) = V(km/h) / 1.609344
                $format = '%.2F';
                $prec = 2;
                $result = $result / 1.609344;
                break;
            case 2:  // V(m/s) = V(km/h) / 3.6
                $result = $result / 3.6;
                $format = '%.1F';
                $prec = 1;
                break;
            case 5:  // V(m/s) = V(km/h) / 3.6
                $format = '%.2F';
                $prec = 2;
                $result = $result / 3.6;
                break;
            case 3:  // see https://en.wikipedia.org/wiki/Beaufort_scale
                $result = 12;
                for ($i = 11; $i >= 0; $i--) {
                    if ($value < $this->beaufort_thresholds[$i] ) {
                        $result = $i;
                    }
                }
                break;
            case 4:  // V(kn) = V(km/h) / 1.852
                $result = $result / 1.852;
                if ($result < 10) {
                    $format = '%.1F';
                }
                break;
        }
        return sprintf($format, round($result, $prec));
    }

    /**
     * Get the wind state (normal, advisory or warning).
     *
     * @param mixed $value The value of the wind speed.
     * @param boolean $text Optional. Return string if true, integer value otherwise.
     * @return integer|string The state of the wind.
     * @since 3.0.0
     */
    protected function get_wind_state($value, $text=false)
    {
        if ($text) {
            $result = __('none', 'live-weather-station');
        }
        else {
            $result = 0;
        }
        $beaufort = $this->get_wind_speed($value, 3);
        if ($beaufort >= 6) {
            if ($text) {
                $result = __('Small craft', 'live-weather-station');
            }
            else {
                $result = 1;
            }
        }
        if ($beaufort >= 8) {
            if ($text) {
                $result = __('Gale', 'live-weather-station');
            }
            else {
                $result = 2;
            }
        }
        if ($beaufort >= 10) {
            if ($text) {
                $result = __('Storm', 'live-weather-station');
            }
            else {
                $result = 3;
            }
        }
        if ($beaufort >= 12) {
            if ($text) {
                $result = __('Hurricane', 'live-weather-station');
            }
            else {
                $result = 4;
            }
        }
        return $result;
    }

    /**
     * Get wind speed expressed in standard unit.
     *
     * @param mixed $value The value of wind speed.
     * @param integer $id The unit id.
     * @return string The wind speed expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_wind_speed($value, $id)
    {
        $result = $value;
        $format = '%d';
        $prec = 1;
        switch ($id) {
            case 1:  // V(km/h) = V(mph) * 1.609344
            case 6:
                $result = $result * 1.609344;
                break;
            case 2:  // V(km/h) = V(m/s) * 3.6
            case 5:
                $result = $result * 3.6;
                break;
            case 3:  // see https://en.wikipedia.org/wiki/Beaufort_scale
                if ($value == 12) {
                    $result = 130 ;
                }
                elseif ($value == 0) {
                    $result = 0;
                }
                else {
                    $result = ($this->beaufort_thresholds[$value]+$this->beaufort_thresholds[$value-1]) / 2;
                }
                break;
            case 4:  // V(km/h) = V(kn) * 1.852
                $result = $result * 1.852;
                break;
        }
        return sprintf('%d', round($result, 0));
    }


    /**
     * Get the battery level in numeric format.
     *
     * @param   integer $value  The value of the battery gauge.
     * @param   string  $type   The type of the module.
     * @return  integer The battery level in numeric format.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_level($value, $type) {
        switch (strtolower($type)) {
            case 'namain':
                $result = -1;
                break;
            case 'namodule1': // Outdoor module
            case 'namodule3': // Rain gauge
                if ($value <= \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_3) {$result = 4;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_3) {$result = 3;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_2) {$result = 2;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_1) {$result = 1;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_0) {$result = 0;}
                break;
            case 'namodule2': // Wind gauge
                if ($value <= \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3) {$result = 4;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3) {$result = 3;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_2) {$result = 2;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_1) {$result = 1;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_0) {$result = 0;}
                break;
            case 'namodule4': // Additional indoor module
                if ($value <= \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3) {$result = 4;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3) {$result = 3;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_2) {$result = 2;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_1) {$result = 1;}
                if ($value > \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_0) {$result = 0;}
                break;
            default:
                $result = 5;
        }
        return $result;
    }

    /**
     * Get the battery level in percent.
     *
     * @param   integer $value  The value of the battery gauge.
     * @param   string  $type   The type of the module.
     * @return  integer The battery level in percent.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_percentage($value, $type) {
        switch (strtolower($type)) {
            case 'namain':
                $max = 100;
                $val = 100;
                break;
            case 'namodule1': // Outdoor module
            case 'namodule3': // Rain gauge
                $max = $this->battery_max - \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_3 + $this->battery_cutoff;
                $val = $value - \WeatherStation\SDK\Netatmo\Common\NABatteryLevelModule::BATTERY_LEVEL_3 + $this->battery_cutoff;
                break;
            case 'namodule2': // Wind gauge
                $max = $this->battery_max - \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3 + $this->battery_cutoff;
                $val = $value - \WeatherStation\SDK\Netatmo\Common\NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3 + $this->battery_cutoff;
                break;
            case 'namodule4': // Additional indoor module
                $max = $this->battery_max - \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3 + $this->battery_cutoff;
                $val = $value - \WeatherStation\SDK\Netatmo\Common\NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3 + $this->battery_cutoff;
                break;
            case 'nacomputed':
            case 'nacurrent':
            case 'napollution':
                $max = 100;
                $val = 100;
                break;
            default:
                $max = 100;
                $val = 0;
        }
        if ($val<0) {$val=0;}
        if ($val>$max) {$val=$max;}
        return sprintf('%d',round(100*$val/$max));
    }

    /**
     * Get the signal level in percent.
     *
     * @param   integer $value  The value of the signal gauge.
     * @param   string  $type   The type of the module.
     * @return  integer The signal level in percent.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_percentage($value, $type) {
        switch (strtolower($type)) {
            case 'namain':
                $max = \WeatherStation\SDK\Netatmo\Common\NAWifiRssiThreshold::RSSI_THRESHOLD_0 + $this->signal_cutoff;
                $min = \WeatherStation\SDK\Netatmo\Common\NAWifiRssiThreshold::RSSI_THRESHOLD_2 - $this->signal_cutoff;
                $val = $value;
                break;
            case 'namodule1': // Outdoor module
            case 'namodule3': // Rain gauge
            case 'namodule2': // Wind gauge
            case 'namodule4': // Additional indoor module
                $max = \WeatherStation\SDK\Netatmo\Common\NARadioRssiTreshold::RADIO_THRESHOLD_0 + $this->signal_cutoff;
                $min = \WeatherStation\SDK\Netatmo\Common\NARadioRssiTreshold::RADIO_THRESHOLD_3 - $this->signal_cutoff;
                $val = $value;
                break;
            default:
                $max = 100;
                $min = 0;
                $val = 0;
        }
        if ($val<$min) {$val=$min;}
        if ($val>$max) {$val=$max;}
        return sprintf('%d',round(100-(100*($val-$min)/($max-$min))));
    }

    /**
     * Get the RF level in numeric format.
     *
     * @param   integer $value  The value of the RF gauge.
     * @return  integer The RF level in numeric format.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_rf_level($value) {
        $result = -1;
        if ($value < \WeatherStation\SDK\Netatmo\Common\NARadioRssiTreshold::RADIO_THRESHOLD_0 + $this->radio_correct_db) {$result = 0;}
        if ($value < \WeatherStation\SDK\Netatmo\Common\NARadioRssiTreshold::RADIO_THRESHOLD_1) {$result = 1;}
        if ($value < \WeatherStation\SDK\Netatmo\Common\NARadioRssiTreshold::RADIO_THRESHOLD_2) {$result = 2;}
        if ($value < \WeatherStation\SDK\Netatmo\Common\NARadioRssiTreshold::RADIO_THRESHOLD_3) {$result = 3;}
        return $result;
    }

    /**
     * Get the wifi level in numeric format.
     *
     * @param   integer $value  The value of the wifi gauge.
     * @return  integer The wifi level in numeric format.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wifi_level($value) {
        $result = -1;
        if ($value < \WeatherStation\SDK\Netatmo\Common\NAWifiRssiThreshold::RSSI_THRESHOLD_0 + $this->wifi_correct_db) {$result = 0;}
        if ($value < \WeatherStation\SDK\Netatmo\Common\NAWifiRssiThreshold::RSSI_THRESHOLD_1) {$result = 1;}
        if ($value < \WeatherStation\SDK\Netatmo\Common\NAWifiRssiThreshold::RSSI_THRESHOLD_2) {$result = 2;}
        if ($value == 9999) {
            $result = $value;
        }
        return $result;
    }

    /**
     * Get the altitude of cloud ceiling expressed in specific unit.
     *
     * @param mixed $value The value of the altitude.
     * @param integer $id Optional. The unit id.
     * @return string The cloud ceiling altitude expressed in specific unit.
     * @since 3.0.9
     */
    protected function get_cloud_ceiling($value, $id = 0)
    {
        $result = $this->get_altitude($value, $id);
        if ($result < 100) {
            $result = 5 * round($result/5, 0);
        }
        elseif ($result < 200) {
            $result = 10 * round($result/10, 0);
        }
        elseif ($result < 500) {
            $result = 50 * round($result/50, 0);
        }
        elseif ($result < 2000) {
            $result = 100 * round($result/100, 0);
        }
        elseif ($result < 10000) {
            $result = 500 * round($result/500, 0);
        }
        else {
            $result = 1000 * round($result/1000, 0);
        }
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the altitude expressed in specific unit.
     *
     * @param   mixed   $value  The value of the altitude.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The altitude expressed in specific unit.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_altitude($value, $id = 0)
    {
        $result = $value;
        switch ($id) {
            case 1:  // D(ft) = D(m) / 0.3048
                $result = $result / 0.3048;
                break;
        }
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get altitude expressed in standard unit.
     *
     * @param mixed $value The value of altitude.
     * @param integer $id The unit id.
     * @return string The altitude expressed in standard unit.
     * @since 3.0.0
     */
    protected function get_reverse_altitude($value, $id)
    {
        $result = $value;
        switch ($id) {
            case 1:  // D(m) = D(ft) * 0.3048
                $result = $result * 0.3048;
                break;
        }
        return sprintf('%d', round($result, 0));
    }

    /**
     * Get the distance expressed in specific unit.
     *
     * @param   mixed   $value  The value of the distance.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The distance expressed in specific unit.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_distance_from_kilometers($value, $id = 0)
    {
        $result = $value;
        switch ($id) {
            case 1:  // D(mi) = D(km) / 1.609
                $result = $result / 1.609;
                break;
        }
        return sprintf('%d', round($result));
    }

    /**
     * Get the distance expressed in specific unit.
     *
     * @param   mixed   $value  The value of the distance.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The distance expressed in specific unit.
     * @since    2.7.0
     */
    protected function get_distance_from_meters($value, $id = 0)
    {
        $result = $value / 1000;
        switch ($id) {
            case 1:  // D(mi) = D(km) / 1.609
                $result = $result / 1.609;
                break;
        }
        return sprintf('%.1F', round($result, 1));
    }

    /**
     * Get the standard value of a value expressed in user's unit.
     *
     * @param mixed $value The value to output.
     * @param string $type The type of the value.
     * @return string The value expressed in the standard unit.
     * @since 3.0.0
     */

    protected function convert_value($value, $type) {
        $result = $value;
        switch (strtolower($type)) {
            case 'pressure':
            case 'pressure_min':
            case 'pressure_max':
                $result = $this->get_reverse_pressure($value, get_option('live_weather_station_unit_pressure'));
                break;
            case 'humidity':
            case 'humidity_min':
            case 'humidity_max':
            case 'humint':
            case 'humext':
            case 'humidity_ref':
                $result = $this->get_reverse_humidity($value);
                break;
            case 'temperature':
            case 'tempint':
            case 'tempext':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'heat_index':
            case 'humidex':
            case 'wind_chill':
                $result = $this->get_reverse_temperature($value, get_option('live_weather_station_unit_temperature'));
                break;
            case 'loc_altitude':
            case 'cloud_ceiling':
                $result = $this->get_reverse_altitude($value, get_option('live_weather_station_unit_altitude'));
                break;
            case 'cloudiness':
                $result = $this->get_cloudiness($value);
                break;
            /*case 'co2':
                $result = $this->get_reverse_co2($value);
                break;
            case 'o3':
                $result = $this->get_reverse_o3($value);
                break;
            case 'co':
                $result = $this->get_reverse_co($value, get_option('live_weather_station_unit_co'));
                break;*/
            case 'noise':
                $result = $this->get_reverse_noise($value);
                break;
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
            case 'rain_yesterday_aggregated':
            case 'rain_month_aggregated':
            case 'rain_season_aggregated':
            case 'rain_year_aggregated':
                $result = $this->get_reverse_rain($value, get_option('live_weather_station_unit_rain_snow'));
                break;
            case 'snow':
                $result = $this->get_reverse_snow($value, get_option('live_weather_station_unit_rain_snow'));
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                $result = $this->get_reverse_wind_angle($value);
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
            case 'wind_ref':
                $result = $this->get_reverse_wind_speed($value, get_option('live_weather_station_unit_wind_strength'));
                break;
        }
        return $result;
    }
}
