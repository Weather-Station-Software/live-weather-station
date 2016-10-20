<?php

namespace WeatherStation\Data\Type;

    /**
     * Types descriptions functionalities for Weather Station plugin.
     *
     * @package Includes\Traits
     * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
     * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
     * @since 1.0.0
     */


trait Description {
    /**
     * Get the service type in plain text.
     *
     * @param integer $type The type of the station.
     * @return string The type of the service in plain text.
     * @since 3.0.0
     */
    protected function get_service_name($type) {
        switch ($type) {
            case 0:
                $result = 'Netatmo';
                break;
            case 1:
            case 2:
                $result = 'OpenWeatherMap';
                break;
            case 3:
                $result = 'WeatherUnderground';
                break;
            case 4:
            case 5:
            case 6:
                $result = 'File Handler';
                break;
            default:
                $result = null;
        }
        return $result;
    }

    /**
     * Get the module type in plain text.
     *
     * @param string $type The type of the module.
     * @param boolean $module Optional. Get the module name.
     * @return string The type of the module in plain text.
     * @since 1.0.0
     */
    protected function get_module_type($type, $module=true) {
        switch (strtolower($type)) {
            case 'namain':
                $result = __('Base station', 'live-weather-station');
                break;
            case 'namodule1': // Outdoor module
                $result = __('Outdoor module', 'live-weather-station');
                break;
            case 'namodule3': // Rain gauge
                $result = __('Rain gauge', 'live-weather-station');
                break;
            case 'namodule2': // Wind gauge
                $result = __('Wind gauge', 'live-weather-station');
                break;
            case 'namodule4': // Additional indoor module
                $result = __('Indoor module', 'live-weather-station');
                break;


            case 'namodule9': // Additional indoor module
                $result = __('Extra module', 'live-weather-station');
                break;
            case 'nacomputed': // Computed values virtual module
                $result = __('[Computed Values]', 'live-weather-station');
                if (!$module) {
                    $result = __('Indexes', 'live-weather-station') . ' (' . __('virtual module', 'live-weather-station') . ')';
                }
                break;
            case 'nacurrent': // Current weather (from OWM) virtual module
                $result = __('[OpenWeatherMap Records]', 'live-weather-station');
                if (!$module) {
                    $result = __('Current records', 'live-weather-station') . ' (' . __('virtual module', 'live-weather-station') . ')';
                }
                break;
            case 'napollution': // Pollution (from OWM) virtual module
                $result = __('[OpenWeatherMap Pollution]', 'live-weather-station');
                if (!$module) {
                    $result = __('Pollution probe', 'live-weather-station') . ' (' . __('virtual module', 'live-weather-station') . ')';
                }
                break;
            case 'naforecast': // Forecast (from OWM) virtual module
                $result = __('[OpenWeatherMap Forecast]', 'live-weather-station');
                if (!$module) {
                    $result = __('Forecast', 'live-weather-station') . ' (' . __('virtual module', 'live-weather-station') . ')';
                }
                break;
            case 'naephemer': // Ephemeris virtual module
                $result = __('[Ephemeris]', 'live-weather-station');
                if (!$module) {
                    $result = __('Ephemeris', 'live-weather-station') . ' (' . __('virtual module', 'live-weather-station') . ')';
                }
                break;
            default:
                $result = __('Unknown module', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the full name for fake modules.
     *
     * @param string $type The type of the module.
     * @return string The type of the module in plain text.
     * @since 3.0.0
     */
    protected function get_fake_module_name($type) {
        switch (strtolower($type)) {
            case 'namain':
                $result = __('Station', 'live-weather-station');
                break;
            case 'namodule1': // Outdoor module
                $result = __('Outdoor', 'live-weather-station');
                break;
            case 'namodule2': // Wind gauge
                $result = __('Wind', 'live-weather-station');
                break;
            case 'namodule3': // Rain gauge
                $result = __('Rain', 'live-weather-station');
                break;
            default:
                $result = __('Unknown module', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the measurement type in plain text.
     *
     * @param   string $type    The type of the measurement.
     * @param   boolean $abbr    Optional. Is the type must be abbreviated.
     * @param   string $module_type    Optional. The type of the module.
     * @return  string  The type of the measurement in plain text.
     * @since    1.0.0
     * @access   protected 
     */
    protected function get_measurement_type ($type, $abbr=false, $module_type='NAMain') { 
        switch (strtolower($type)) {
            case 'firmware':
                $result = ($abbr ? __('Firmware', 'live-weather-station') : __('Firmware version', 'live-weather-station'));
                break;
            case 'battery':
                $result = ($abbr ? __('Battery', 'live-weather-station') : __('Battery level', 'live-weather-station'));
                break;
            case 'signal':
                $result = ($abbr ? __('WiFi', 'live-weather-station') : __('RF/WiFi signal quality', 'live-weather-station'));
                break;
            case 'co2':
                $result = ($abbr ? __('CO₂', 'live-weather-station') : __('Carbon dioxide', 'live-weather-station'));
                break;
            case 'co':
                $result = ($abbr ? __('CO', 'live-weather-station') : __('Carbon monoxide', 'live-weather-station'));
                break;
            case 'o3':
                $result = ($abbr ? __('O₃', 'live-weather-station') : __('Ozone', 'live-weather-station'));
                break;
            case 'co_distance':
                $result = ($abbr ? __('CO', 'live-weather-station') : __('Carbon monoxide probe distance', 'live-weather-station'));
                break;
            case 'o3_distance':
                $result = ($abbr ? __('O₃', 'live-weather-station') : __('Ozone probe distance', 'live-weather-station'));
                break;
            case 'humidity':
                $result = ($abbr ? __('Humidity', 'live-weather-station') : __('Humidity', 'live-weather-station'));
                break;
            case 'humint':
                $result = ($abbr ? __('Indoor humidity', 'live-weather-station') : __('Indoor humidity', 'live-weather-station'));
                break;
            case 'humext':
                $result = ($abbr ? __('Outdoor humidity', 'live-weather-station') : __('Outdoor humidity', 'live-weather-station'));
                break;
            case 'humidity_ref':
                $result = ($abbr ? __('Humidity', 'live-weather-station') : __('Reference humidity', 'live-weather-station'));
                break;
            case 'noise':
                $result = ($abbr ? __('Noise', 'live-weather-station') : __('Noise level', 'live-weather-station'));
                break;
            case 'pressure':
                $result = ($abbr ? __('Pressure', 'live-weather-station') : __('Atmospheric pressure', 'live-weather-station'));
                break;
            case 'pressure_trend':
                $result = ($abbr ? __('Pressure', 'live-weather-station') : __('Pressure trend', 'live-weather-station'));
                break;
            case 'temperature':
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Temperature', 'live-weather-station'));
                break;
            case 'tempint':
                $result = ($abbr ? __('Indoor temperature', 'live-weather-station') : __('Indoor temperature', 'live-weather-station'));
                break;
            case 'tempext':
                $result = ($abbr ? __('Outdoor temperature', 'live-weather-station') : __('Outdoor temperature', 'live-weather-station'));
                break;
            case 'temperature_ref':
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Reference temperature', 'live-weather-station'));
                break;
            case 'wind_ref':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Reference wind strength', 'live-weather-station'));
                break;
            case 'temperature_max':
            case 'max_temp':
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Highest temperature of the day', 'live-weather-station'));
                break;
            case 'temperature_min':
            case 'min_temp':
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Lowest temperature of the day', 'live-weather-station'));
                break;
            case 'temperature_trend':
            case 'temp_trend':
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Temperature trend', 'live-weather-station'));
                break;
            case 'dew_point':
                $result = ($abbr ? __('Dew point', 'live-weather-station') : __('Dew point', 'live-weather-station'));
                break;
            case 'frost_point':
                $result = ($abbr ? __('Frost point', 'live-weather-station') : __('Frost point', 'live-weather-station'));
                break;
            case 'heat_index':
                $result = ($abbr ? __('Heat', 'live-weather-station') : __('Heat index', 'live-weather-station'));
                break;
            case 'humidex':
                $result = ($abbr ? __('Humidex', 'live-weather-station') : __('Humidex', 'live-weather-station'));
                break;
            case 'wind_chill':
                $result = ($abbr ? __('Wind chill', 'live-weather-station') : __('Wind chill', 'live-weather-station'));
                break;
            case 'cloud_ceiling':
                $result = ($abbr ? __('Cloud base', 'live-weather-station') : __('Cloud base altitude', 'live-weather-station'));
                break;
            case 'cloudcover':
            case 'cloud_cover':
            case 'cloudiness':
                $result = ($abbr ? __('Cloudiness', 'live-weather-station') : __('Cloudiness', 'live-weather-station'));
                break;
            case 'rain':
                if (strtolower($module_type)=='namodule3') {
                    $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain rate', 'live-weather-station'));
                }
                else {
                    $result = ($abbr ? __('Rain', 'live-weather-station') : __('Accumulated rainfall for the last 3 hours', 'live-weather-station'));
                }
                break;
            case 'snow':
                $result = ($abbr ? __('Snow', 'live-weather-station') : __('Accumulated snowfall for the last 3 hours', 'live-weather-station'));
                break;
            case 'rain_hour_aggregated':
            case 'sum_rain_1':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Accumulated rainfall for the last hour', 'live-weather-station'));
                break;
            case 'rain_day_aggregated':
            case 'sum_rain_24':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Accumulated rainfall for today', 'live-weather-station'));
                break;
            case 'windangle':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind direction', 'live-weather-station'));
                break;
            case 'windstrength':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind strength', 'live-weather-station'));
                break;
            case 'gustangle':
                $result = ($abbr ? __('Gust', 'live-weather-station') : __('Gust direction', 'live-weather-station'));
                break;
            case 'guststrength':
                $result = ($abbr ? __('Gust', 'live-weather-station') : __('Gust strength', 'live-weather-station'));
                break;
            case 'windangle_max':
            case 'windangle_hour_max':
            case 'max_wind_angle':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind direction for the maximal wind strength for the last hour', 'live-weather-station'));
                break;
            case 'windstrength_max':
            case 'windstrength_hour_max':
            case 'max_wind_str':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Maximal wind strength for the last hour', 'live-weather-station'));
                break;
            case 'windangle_day_max':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind direction for the maximal wind strength today', 'live-weather-station'));
                break;
            case 'windstrength_day_max':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Maximal wind strength today', 'live-weather-station'));
                break;
            case 'loc_altitude':
            case 'altitude':
                $result = ($abbr ? __('Altitude', 'live-weather-station') : __('Altitude', 'live-weather-station'));
                break;
            case 'loc_latitude':
            case 'latitude':
                $result = ($abbr ? __('Latitude', 'live-weather-station') : __('Latitude', 'live-weather-station'));
                break;
            case 'loc_longitude':
            case 'longitude':
                $result = ($abbr ? __('Longitude', 'live-weather-station') : __('Longitude', 'live-weather-station'));
                break;
            case 'loc_timezone':
            case 'timezone':
                $result = ($abbr ? __('TZ', 'live-weather-station') : __('Time zone', 'live-weather-station'));
                break;
            case 'aggregated':
                $result = ($abbr ? __('all', 'live-weather-station') : __('[all measures]', 'live-weather-station'));
                break;
            case 'outdoor':
                $result = ($abbr ? __('outdoor', 'live-weather-station') : __('[outdoor measures]', 'live-weather-station'));
                break;
            case 'sunrise':
                $result = ($abbr ? __('Sunrise', 'live-weather-station') : __('Sunrise', 'live-weather-station'));
                break;
            case 'sunset':
                $result = ($abbr ? __('Sunset', 'live-weather-station') : __('Sunset', 'live-weather-station'));
                break;
            case 'moonrise':
                $result = ($abbr ? __('Moonrise', 'live-weather-station') : __('Moonrise', 'live-weather-station'));
                break;
            case 'moonset':
                $result = ($abbr ? __('Moonset', 'live-weather-station') : __('Moonset', 'live-weather-station'));
                break;
            case 'moon_age':
                $result = ($abbr ? __('Age', 'live-weather-station') : __('Moon age', 'live-weather-station'));
                break;
            case 'moon_phase':
                $result = ($abbr ? __('Phase', 'live-weather-station') : __('Moon phase', 'live-weather-station'));
                break;
            case 'moon_illumination':
                $result = ($abbr ? __('Illumination', 'live-weather-station') : __('Moon illumination', 'live-weather-station'));
                break;
            case 'moon_distance':
                $result = ($abbr ? __('Distance', 'live-weather-station') : __('Moon distance', 'live-weather-station'));
                break;
            case 'moon_diameter':
                $result = ($abbr ? __('Size', 'live-weather-station') : __('Moon angular size', 'live-weather-station'));
                break;
            case 'sun_distance':
                $result = ($abbr ? __('Distance', 'live-weather-station') : __('Sun distance', 'live-weather-station'));
                break;
            case 'sun_diameter':
                $result = ($abbr ? __('Size', 'live-weather-station') : __('Sun angular size', 'live-weather-station'));
                break;
            case 'last_seen':
                $result = ($abbr ? __('Seen', 'live-weather-station') : __('Last seen', 'live-weather-station'));
                break;
            case 'last_refresh':
                $result = ($abbr ? __('Refresh', 'live-weather-station') : __('Last refresh', 'live-weather-station'));
                break;
            case 'first_setup':
                $result = ($abbr ? __('Setup', 'live-weather-station') : __('First setup', 'live-weather-station'));
                break;
            case 'last_setup':
                $result = ($abbr ? __('Setup', 'live-weather-station') : __('Last setup', 'live-weather-station'));
                break;
            case 'last_upgrade':
                $result = ($abbr ? __('Upgrade', 'live-weather-station') : __('Last firmware upgrade', 'live-weather-station'));
                break;
            default:
                $result = ($abbr ? '?' : __('Unknown measurement', 'live-weather-station'));
        }
        return $result;
    }
}






