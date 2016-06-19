<?php

/**
 * Units conversions functionalities for Live Weather Station plugin
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'netatmo_api/src/Constants/AppliCommonPublic.php');

trait Unit_Conversion {

    private $radio_correct_db = 5;
    private $wifi_correct_db = 1;
    private $battery_max = 6000;
    private $signal_max = 0;
    private $battery_cutoff = 500;
    private $signal_cutoff = 10;

    /**
     * Get the co2 expressed in its unique unit.
     *
     * @param   mixed   $value  The value of the co2.
     * @return  string  The co2 expressed in its unique unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_co2($value)
    {
        $result = $value;
        return sprintf('%d', round($result, 0));
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
     * Get the CO expressed in specific unit.
     *
     * @param   mixed   $value  The value of the CO.
     * @param   integer $id     Optional. The unit id.
     * @return  string  The CO expressed in specific unit.
     * @since    2.7.0
     * @access   protected
     */
    protected function get_co($value, $id = 0)
    {
        $result = $value;
        $format = '%.6F';
        $prec = 6;
        switch ($id) {
            case 1:  // pCO = vmrCO * pressure
                $result = $result * 10;
                $format = '%.4F';
                $prec = 4;
                break;
            case 2:  // mCO = vmrCO * 1.66
                $result = $result * 1.66;
                $format = '%.5F';
                $prec = 5;
                break;
        }
        return sprintf($format, round($result, $prec));
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
        $format = '%d';
        $prec = 0;
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
        $prec = 1;
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
                if ($value < 117.4) {
                    $result = 11;
                }
                if ($value < 102.4) {
                    $result = 10;
                }
                if ($value < 88.1) {
                    $result = 9;
                }
                if ($value < 74.6) {
                    $result = 8;
                }
                if ($value < 61.8) {
                    $result = 7;
                }
                if ($value < 49.9) {
                    $result = 6;
                }
                if ($value < 38.8) {
                    $result = 5;
                }
                if ($value < 28.7) {
                    $result = 4;
                }
                if ($value < 19.7) {
                    $result = 3;
                }
                if ($value < 11.9) {
                    $result = 2;
                }
                if ($value < 5.5) {
                    $result = 1;
                }
                if ($value < 1.1) {
                    $result = 0;
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
                if ($value <= NABatteryLevelModule::BATTERY_LEVEL_3) {$result = 4;}
                if ($value > NABatteryLevelModule::BATTERY_LEVEL_3) {$result = 3;}
                if ($value > NABatteryLevelModule::BATTERY_LEVEL_2) {$result = 2;}
                if ($value > NABatteryLevelModule::BATTERY_LEVEL_1) {$result = 1;}
                if ($value > NABatteryLevelModule::BATTERY_LEVEL_0) {$result = 0;}
                break;
            case 'namodule2': // Wind gauge
                if ($value <= NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3) {$result = 4;}
                if ($value > NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3) {$result = 3;}
                if ($value > NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_2) {$result = 2;}
                if ($value > NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_1) {$result = 1;}
                if ($value > NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_0) {$result = 0;}
                break;
            case 'namodule4': // Additional indoor module
                if ($value <= NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3) {$result = 4;}
                if ($value > NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3) {$result = 3;}
                if ($value > NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_2) {$result = 2;}
                if ($value > NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_1) {$result = 1;}
                if ($value > NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_0) {$result = 0;}
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
                $max = $this->battery_max - NABatteryLevelModule::BATTERY_LEVEL_3 + $this->battery_cutoff;
                $val = $value - NABatteryLevelModule::BATTERY_LEVEL_3 + $this->battery_cutoff;
                break;
            case 'namodule2': // Wind gauge
                $max = $this->battery_max - NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3 + $this->battery_cutoff;
                $val = $value - NABatteryLevelWindGaugeModule::WG_BATTERY_LEVEL_3 + $this->battery_cutoff;
                break;
            case 'namodule4': // Additional indoor module
                $max = $this->battery_max - NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3 + $this->battery_cutoff;
                $val = $value - NABatteryLevelIndoorModule::INDOOR_BATTERY_LEVEL_3 + $this->battery_cutoff;
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
                $max = NAWifiRssiThreshold::RSSI_THRESHOLD_0 + $this->signal_cutoff;
                $min = NAWifiRssiThreshold::RSSI_THRESHOLD_2 - $this->signal_cutoff;
                $val = $value;
                break;
            case 'namodule1': // Outdoor module
            case 'namodule3': // Rain gauge
            case 'namodule2': // Wind gauge
            case 'namodule4': // Additional indoor module
                $max = NARadioRssiTreshold::RADIO_THRESHOLD_0 + $this->signal_cutoff;
                $min = NARadioRssiTreshold::RADIO_THRESHOLD_3 - $this->signal_cutoff;
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
        if ($value >= NARadioRssiTreshold::RADIO_THRESHOLD_0 + $this->radio_correct_db) {$result = -1;}
        if ($value < NARadioRssiTreshold::RADIO_THRESHOLD_0 + $this->radio_correct_db) {$result = 0;}
        if ($value < NARadioRssiTreshold::RADIO_THRESHOLD_1 + $this->radio_correct_db) {$result = 1;}
        if ($value < NARadioRssiTreshold::RADIO_THRESHOLD_2 + $this->radio_correct_db) {$result = 2;}
        if ($value < NARadioRssiTreshold::RADIO_THRESHOLD_3 + $this->radio_correct_db) {$result = 3;}
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
        if ($value >= NAWifiRssiThreshold::RSSI_THRESHOLD_0 + $this->wifi_correct_db) {$result = -1;}
        if ($value < NAWifiRssiThreshold::RSSI_THRESHOLD_0 + $this->wifi_correct_db) {$result = 0;}
        if ($value < NAWifiRssiThreshold::RSSI_THRESHOLD_1 + $this->wifi_correct_db) {$result = 1;}
        if ($value < NAWifiRssiThreshold::RSSI_THRESHOLD_2 + $this->wifi_correct_db) {$result = 2;}
        return $result;
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
}
