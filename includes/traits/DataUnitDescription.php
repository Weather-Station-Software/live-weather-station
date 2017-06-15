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
trait Description {
    /**
     * Get available density units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_density_unit($id = 0) {
        switch ($id) {
            case 1:
                $result =  __( 'lb/f³' , 'live-weather-station');
                break;
            default:
                $result = __( 'kg/m³' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available enthalpy units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_enthalpy_unit($id = 0) {
        switch ($id) {
            case 1:
                $result =  __( 'btu/lb' , 'live-weather-station');
                break;
            default:
                $result = __( 'kJ/kg' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available enthalpy units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_enthalpy_unit_full($id = 0) {
        switch ($id) {
            case 1:
                $result =  __( 'british thermal unit per pound' , 'live-weather-station');
                break;
            default:
                $result = __( 'kilojoule per kilogram' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available enthalpy units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_absolute_humidity_unit($id = 0) {
        switch ($id) {
            case 1:
                $result =  __( 'grain/lb' , 'live-weather-station');
                break;
            default:
                $result = __( 'g/kg' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available enthalpy full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_absolute_humidity_unit_full($id = 0) {
        switch ($id) {
            case 1:
                $result =  __( 'grain of water vapor per pound of dry air' , 'live-weather-station');
                break;
            default:
                $result = __( 'gram of water vapor per kilogram of dry air' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available density units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_density_unit_full($id = 0) {
        switch ($id) {
            case 1:
                $result =  __( 'pound per cubic foot' , 'live-weather-station');
                break;
            default:
                $result = __( 'kilogram per cubic meter' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available humidity units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_humidity_unit( $id = 0 ) {
        return __( '%' , 'live-weather-station');
    }

    /**
     * Get available emc units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_emc_unit($id = 0) {
        return __('%' , 'live-weather-station');
    }

    /**
     * Get available humidity full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_humidity_unit_full( $id = 0 ) {
        return __( 'percent' , 'live-weather-station');
    }

    /**
     * Get available emc full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_emc_unit_full( $id = 0 ) {
        return __( 'percent' , 'live-weather-station');
    }

    /**
     * Get available moon illumination units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.0.0
     */
    protected function get_moon_illumination_unit( $id = 0 ) {
        return __( '%' , 'live-weather-station');
    }

    /**
     * Get available moon illumination full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     */
    protected function get_moon_illumination_unit_full( $id = 0 ) {
        return __( 'percent' , 'live-weather-station');
    }

    /**
     * Get available degree diameter units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.0.0
     */
    protected function get_degree_diameter_unit( $id = 0 ) {
        return __( '°' , 'live-weather-station');
    }

    /**
     * Get available degree diameter full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     */
    protected function get_degree_diameter_unit_full( $id = 0 ) {
        return __( 'degree' , 'live-weather-station');
    }

    /**
     * Get available cloudiness units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_cloudiness_unit( $id = 0 ) {
        return __( '%' , 'live-weather-station');
    }

    /**
     * Get available cloudiness full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_cloudiness_unit_full( $id = 0 ) {
        return __( 'percent' , 'live-weather-station');
    }

    /**
     * Get available battery level units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_unit( $id = 0 ) {
        return __( '%' , 'live-weather-station');
    }

    /**
     * Get available battery level full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_battery_unit_full( $id = 0 ) {
        return __( 'percent of capacity' , 'live-weather-station');
    }

    /**
     * Get available signal level units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_unit( $id = 0 ) {
        return __( '%' , 'live-weather-station');
    }

    /**
     * Get available health index units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_health_index_unit( $id = 0 ) {
        return __( '%' , 'live-weather-station');
    }

    /**
     * Get available Chandler Burning index units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_cbi_unit( $id = 0 ) {
        return '';
    }

    /**
     * Get available health index units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_health_index_unit_full( $id = 0 ) {
        return __( 'percent' , 'live-weather-station');
    }

    /**
     * Get available Chandler Burning index units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_cbi_unit_full( $id = 0 ) {
        return '';
    }

    /**
     * Get available signal level full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_unit_full( $id = 0 ) {
        return __( 'percent of strength' , 'live-weather-station');
    }

    /**
     * Get available rain units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_rain_unit( $id = 0 ) {
        switch ($id) {
            case 1:
                $result =  __( 'mm/h' , 'live-weather-station');
                break;
            case 2:
                $result =  __( 'in' , 'live-weather-station');
                break;
            case 3:
                $result =  __( 'in/h' , 'live-weather-station');
                break;
            default:
                $result = __( 'mm' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available rain full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_rain_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result =  __( 'millimeter per hour' , 'live-weather-station');
                break;
            case 2:
                $result =  __( 'inch' , 'live-weather-station');
                break;
            case 3:
                $result =  __( 'inch per hour' , 'live-weather-station');
                break;
            default:
                $result = __( 'millimeter' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available snow units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_snow_unit( $id = 0 ) {
        switch ($id) {
            case 1:
                $result =  __( 'in' , 'live-weather-station');
                break;
            default:
                $result = __( 'cm' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available snow full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_snow_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result =  __( 'inch' , 'live-weather-station');
                break;
            default:
                $result = __( 'centimeter' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available noise units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_noise_unit( $id = 0 ) {
        return __( 'dB' , 'live-weather-station');
    }

    /**
     * Get available noise full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_noise_unit_full( $id = 0 ) {
        return __( 'decibel' , 'live-weather-station');
    }

    /**
     * Get available CO2 units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 1.0.0
     */
    protected function get_co2_unit($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'ppm' , 'live-weather-station');
                break;
            case 1:
                $result = __( 'ppm' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'mg/m³' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'Pa' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available CO2 full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 2.6.0
     */
    protected function get_co2_unit_full($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'part per million' , 'live-weather-station') . ' (' . __( 'volume' , 'live-weather-station') . ')';
                break;
            case 1:
                $result = __( 'part per million' , 'live-weather-station') . ' (' . __( 'mass' , 'live-weather-station') . ')';
                break;
            case 2:
                $result = __( 'milligram per cubic meter' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'pascal' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available CO units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 2.7.0
     */
    protected function get_co_unit($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'ppb' , 'live-weather-station');
                break;
            case 1:
                $result = __( 'ppb' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'μg/m³' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'mPa' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available CO full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 2.7.0
     */
    protected function get_co_unit_full($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'part per billion' , 'live-weather-station') . ' (' . __( 'volume' , 'live-weather-station') . ')';
                break;
            case 1:
                $result = __( 'part per billion' , 'live-weather-station') . ' (' . __( 'mass' , 'live-weather-station') . ')';
                break;
            case 2:
                $result = __( 'microgram per cubic meter' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'millipascal' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available SO2 units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_so2_unit($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'ppb' , 'live-weather-station');
                break;
            case 1:
                $result = __( 'ppb' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'μg/m³' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'mPa' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available SO2 full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_so2_unit_full($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'part per billion' , 'live-weather-station') . ' (' . __( 'volume' , 'live-weather-station') . ')';
                break;
            case 1:
                $result = __( 'part per billion' , 'live-weather-station') . ' (' . __( 'mass' , 'live-weather-station') . ')';
                break;
            case 2:
                $result = __( 'microgram per cubic meter' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'millipascal' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available NO2 units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_no2_unit($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'ppb' , 'live-weather-station');
                break;
            case 1:
                $result = __( 'ppb' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'μg/m³' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'mPa' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available NO2 full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_no2_unit_full($id = 0) {
        $result = '';
        switch ($id) {
            case 0:
                $result = __( 'part per billion' , 'live-weather-station') . ' (' . __( 'volume' , 'live-weather-station') . ')';
                break;
            case 1:
                $result = __( 'part per billion' , 'live-weather-station') . ' (' . __( 'mass' , 'live-weather-station') . ')';
                break;
            case 2:
                $result = __( 'microgram per cubic meter' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'millipascal' , 'live-weather-station');
                break;
        }
        return $result;
    }

    /**
     * Get available o3 units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.7.0
     * @access   protected
     */
    protected function get_o3_unit( $id = 0 ) {
        return __( 'DU' , 'live-weather-station');
    }

    /**
     * Get available o3 full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 2.7.0
     */
    protected function get_o3_unit_full( $id = 0 ) {
        return __( 'dobson unit' , 'live-weather-station');
    }

    /**
     * Get available day length units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_day_length_unit( $id = 0 ) {
        return __( 's' , 'live-weather-station');
    }

    /**
     * Get available day length full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_day_length_unit_full( $id = 0 ) {
        return __( 'second' , 'live-weather-station');
    }

    /**
     * Get available irradiance units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_irradiance_unit( $id = 0 ) {
        return __( 'W/m²' , 'live-weather-station');
    }

    /**
     * Get available irradiance full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_irradiance_unit_full( $id = 0 ) {
        return __( 'watt per square meter' , 'live-weather-station');
    }

    /**
     * Get available illuminance units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_illuminance_unit( $id = 0 ) {
        return __( 'kLux' , 'live-weather-station');
    }

    /**
     * Get available illuminance full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_illuminance_unit_full( $id = 0 ) {
        return __( 'kilolux' , 'live-weather-station');
    }

    /**
     * Get available dusk an dawn twilight units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_dusk_dawn_unit( $id = 0 ) {
        return __( 's' , 'live-weather-station');
    }

    /**
     * Get available dusk an dawn twilight full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.1.0
     */
    protected function get_dusk_dawn_unit_full( $id = 0 ) {
        return __( 'second' , 'live-weather-station');
    }

    /**
     * Get available wind direction units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wind_angle_unit( $id = 0 ) {
        return __( '°' , 'live-weather-station');
    }

    /**
     * Get available wind direction full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_wind_angle_unit_full( $id = 0 ) {
        return __( 'degree' , 'live-weather-station');
    }

    /**
     * Get available pressure units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_pressure_unit( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'inHg' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'mmHg' , 'live-weather-station');
                break;
            default:
                $result = __( 'hPa' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available pressure units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_precise_pressure_unit($id = 0) {
        switch ($id) {
            case 1:
                $result = __( 'inHg' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'mmHg' , 'live-weather-station');
                break;
            default:
                $result = __( 'Pa' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available pressure full units.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit in plain text.
     * @since 3.3.0
     */
    protected function get_precise_pressure_unit_full($id = 0) {
        switch ($id) {
            case 1:
                $result = __( 'inch of mercury' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'millimeter of mercury' , 'live-weather-station');
                break;
            default:
                $result = __( 'pascal' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available pressure full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_pressure_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'inch of mercury' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'millimeter of mercury' , 'live-weather-station');
                break;
            default:
                $result = __( 'hectopascal' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available temperature units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_temperature_unit($id = 0) {
        switch ($id) {
            case 1:
                $result = __( '°F' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'K' , 'live-weather-station');
                break;
            default:
                $result = __( '°C' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available temperature full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_temperature_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'fahrenheit degree' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'kelvin' , 'live-weather-station');
                break;
            default:
                $result = __( 'celcius degree' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available windspeed units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wind_speed_unit( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'mph' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'm/s' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'bf' , 'live-weather-station');
                break;
            case 4:
                $result = __( 'kts' , 'live-weather-station');
                break;
            default:
                $result = __( 'km/h' , 'live-weather-station'); 
        }
        return $result;
    }

    /**
     * Get available windspeed full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_wind_speed_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'mile per hour' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'meter per second' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'beaufort' , 'live-weather-station');
                break;
            case 4:
                $result = __( 'knot' , 'live-weather-station');
                break;
            default:
                $result = __( 'kilometer per hour' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available altitude units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_altitude_unit( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'ft' , 'live-weather-station');
                break;
            default:
                $result = __( 'm' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available ful altitude units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_altitude_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'foot' , 'live-weather-station');
                break;
            default:
                $result = __( 'meter' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available distance units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_distance_unit( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'mi' , 'live-weather-station');
                break;
            default:
                $result = __( 'km' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available distance full units.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit in plain text.
     * @since    2.6.0
     * @access   protected
     */
    protected function get_distance_unit_full( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'mile' , 'live-weather-station');
                break;
            default:
                $result = __( 'kilometer' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available pressure units names.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit name in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_pressure_unit_name( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'inHg' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'mmHg' , 'live-weather-station');
                break;
            default:
                $result = __( 'hPa / mbar' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available temperature units names.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit name in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_temperature_unit_name( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'Fahrenheit - °F' , 'live-weather-station');
                break;
            default:
                $result = __( 'Celcius - °C' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available altitude units names.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit name in plain text.
     * @since    1.2.0
     * @access   protected
     */
    protected function get_altitude_unit_name( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'Imperial system' , 'live-weather-station');
                break;
            default:
                $result = __( 'Metric system' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available density units names.
     *
     * @param integer $id Optional. The unit id.
     * @return string The unit name in plain text.
     * @since 3.3.0
     */
    protected function get_density_unit_name( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'Imperial system' , 'live-weather-station');
                break;
            default:
                $result = __( 'Metric system' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available distance units names.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit name in plain text.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_distance_unit_name( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'Imperial system' , 'live-weather-station');
                break;
            default:
                $result = __( 'Metric system' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get available wind speed units names.
     *
     * @param   integer $id     Optional. The unit id.
     * @return  string  The unit name in plain text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wind_speed_unit_name( $id = 0 ) {
        switch ($id) {
            case 1:
                $result = __( 'mph' , 'live-weather-station');
                break;
            case 2:
                $result = __( 'm/s' , 'live-weather-station');
                break;
            case 3:
                $result = __( 'Beaufort' , 'live-weather-station');
                break;
            case 4:
                $result = __( 'Knot' , 'live-weather-station');
                break;
            default:
                $result = __( 'km/h' , 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get temperatures unit names.
     *
     * @return  array   An array containing the available temperature units names.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_temperature_unit_name_array() {
        $result = array();
        for ($i = 0; $i <= 1; $i++) {
            $result[] = array($i, $this->get_temperature_unit_name($i));
        }
        return $result;
    }

    /**
     * Get pressures unit names.
     *
     * @return  array   An array containing the available pressures units names.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_pressure_unit_name_array() {
        $result = array();
        for ($i = 0; $i <= 2; $i++) {
            $result[] = array($i, $this->get_pressure_unit_name($i));
        }
        return $result;
    }

    /**
     * Get wind speeds unit names.
     *
     * @return  array   An array containing the available wind speed units names.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wind_speed_unit_name_array() {
        $result = array();
        for ($i = 0; $i <= 4; $i++) {
            $result[] = array($i, $this->get_wind_speed_unit_name($i));
        }
        return $result;
    }

    /**
     * Get altitude unit names.
     *
     * @return  array   An array containing the available altitude units names.
     * @since    1.2.0
     * @access   protected
     */
    protected function get_altitude_unit_name_array() {
        $result = array();
        for ($i = 0; $i <= 1; $i++) {
            $result[] = array($i, $this->get_altitude_unit_name($i));
        }
        return $result;
    }

    /**
     * Get density unit names.
     *
     * @return array An array containing the available density units names.
     * @since 3.3.0
     */
    protected function get_density_unit_name_array() {
        $result = array();
        for ($i = 0; $i <= 1; $i++) {
            $result[] = array($i, $this->get_density_unit_name($i));
        }
        return $result;
    }

    /**
     * Get distance unit names.
     *
     * @return  array   An array containing the available distance units names.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_distance_unit_name_array() {
        $result = array();
        for ($i = 0; $i <= 1; $i++) {
            $result[] = array($i, $this->get_distance_unit_name($i));
        }
        return $result;
    }

    /**
     * Get obsolescence values.
     *
     * @return  array   An array containing the available obsolescence values.
     * @since    2.0.0
     * @access   protected
     */
    protected function get_obsolescence_array() {
        $result = array();
        $result[] = array(0, __( 'Never' , 'live-weather-station'));
        $result[] = array(1, __( 'After 30 minutes' , 'live-weather-station'));
        $result[] = array(2, __( 'After 1 hour' , 'live-weather-station'));
        $result[] = array(3, __( 'After 2 hours' , 'live-weather-station'));
        $result[] = array(4, __( 'After 4 hours' , 'live-weather-station'));
        $result[] = array(5, __( 'After 12 hours' , 'live-weather-station'));
        $result[] = array(6, __( 'After 24 hours' , 'live-weather-station'));
        return $result;
    }

    /**
     * Get wind icon semantics values.
     *
     * @return array An array containing the available wind icon semantics values.
     * @since 2.5.0
     */
    protected function get_windsemantics_array() {
        $result = array();
        $result[] = array(0, __( 'Towards... (points to the degree)' , 'live-weather-station'));
        $result[] = array(1, __( 'From... (points directly away from the degree)' , 'live-weather-station'));
        return $result;
    }

    /**
     * Get moon icon set values.
     *
     * @return array An array containing the available moon icon set values.
     * @since 3.0.0
     */
    protected function get_moonicons_array() {
        $result = array();
        $result[] = array(0, __( 'Standard (only the illuminated portion is visible)' , 'live-weather-station'));
        $result[] = array(1, __( 'Reverse (the unlit portion and the edge are visible)' , 'live-weather-station'));
        return $result;
    }

    /**
     * Get gases unit expression.
     *
     * @return array An array containing the available gases expression names.
     * @since 3.1.0
     */
    protected function get_gas_unit_name_array() {
        $result = array();
        $result[] = array(0, __( 'Volume mixing ratio' , 'live-weather-station'));
        $result[] = array(1, __( 'Mass mixing ratio' , 'live-weather-station'));
        $result[] = array(2, __( 'Mass concentration' , 'live-weather-station'));
        $result[] = array(3, __( 'Partial pressure' , 'live-weather-station'));
        return $result;
    }
}