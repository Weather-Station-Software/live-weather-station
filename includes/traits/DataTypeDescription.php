<?php

namespace WeatherStation\Data\Type;

    /**
     * Types descriptions functionalities for Weather Station plugin.
     *
     * @package Includes\Traits
     * @author Jason Rouet <https://www.jasonrouet.com/>.
     * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
     * @since 1.0.0
     */


trait Description {

    /**
     * Get the service type in plain text.
     *
     * @param integer $type The type of the service.
     * @return string The type of the service in plain text.
     * @since 3.0.0
     */
    protected function get_service_name($type) {
        switch ($type) {
            case 0:
            case 6:
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
            case 7:
                $result = 'File Handler';
                break;
            case 8:
                $result = 'WeatherFlow';
                break;
            case 9:
                $result = 'Pioupiou';
                break;
            case 10:
                $result = 'BloomSky';
                break;
            case 11:
                $result = 'Ambient';
                break;
            case 12:
                $result = 'WeatherLink';
                break;
            case 101:
                $result = 'Windy';
                break;
            case 102:
                $result = 'Stamen';
                break;
            case 103:
                $result = 'Thunderforest';
                break;
            case 104:
                $result = 'Mapbox';
                break;
            case 105:
                $result = 'OpenWeatherMap';
                break;
            case 106:
                $result = 'MapTiler';
                break;
            case 107:
                $result = 'Navionics';
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
     * @param boolean $nice Optional. Get the module nice name.
     * @return string The type of the module in plain text.
     * @since 1.0.0
     */
    protected function get_module_type($type, $module=true, $nice=false) {
        switch (strtolower($type)) {
            case 'namain':
                $result = __('Base station', 'live-weather-station');
                break;
            case 'namodule1': // Outdoor module
                $result = __('Outdoor module', 'live-weather-station');
                break;
            case 'namodule2': // Wind gauge
                $result = __('Wind gauge', 'live-weather-station');
                break;
            case 'namodule3': // Rain gauge
                $result = __('Rain gauge', 'live-weather-station');
                break;
            case 'namodule4': // Additional indoor module
                $result = __('Indoor module', 'live-weather-station');
                break;
            case 'namodule5': // Solar module
                $result = __('Solar module', 'live-weather-station');
                break;
            case 'namodule6': // Soil module
                $result = __('Soil module', 'live-weather-station');
                break;
            case 'namodule7': // Thunderstorm module
                $result = __('Thunderstorm module', 'live-weather-station');
                break;
            case 'namodule9': // Additional module
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
            case 'namodulev': // Video module
                $result = __('Video module', 'live-weather-station');
                break;
            case 'namodulep': // Video module
                $result = __('Picture module', 'live-weather-station');
                break;
            default:
                $result = __('Unknown module', 'live-weather-station');
        }
        if ($nice) {
            if (strpos($result, ' (') > 0) {
                $result = substr($result, 0, strpos($result, ' ('));
            }
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
            case 'namodule4': // Indoor module
                $result = __('Indoor', 'live-weather-station');
                break;
            case 'namodule5': // Solar module
                $result = __('Solar', 'live-weather-station');
                break;
            case 'namodule6': // Soil module
                $result = __('Soil', 'live-weather-station');
                break;
            case 'namodule7': // Thunderstorm module
                $result = __('Thunderstorm', 'live-weather-station');
                break;

            case 'namodule9': // Extra module
                $result = __('Extra', 'live-weather-station');
                break;
            case 'namodulev': // Video module
                $result = __('Video', 'live-weather-station');
                break;
            case 'namodulep': // Picture module
                $result = __('Picture', 'live-weather-station');
                break;
            default:
                $result = __('Unknown module', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the measurement type in plain text.
     *
     * @param string $type The type of the measurement.
     * @param boolean $abbr Optional. Is the type must be abbreviated.
     * @param string $module_type Optional. The type of the module.
     * @param boolean $meaning Optional. Just the meaning.
     * @return string The type of the measurement in plain text.
     * @since 1.0.0
     */
    protected function get_measurement_type ($type, $abbr=false, $module_type='NAMain', $meaning=false) {
        switch (strtolower($type)) {
            case 'weather':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Conditions', 'live-weather-station') : __('Current conditions', 'live-weather-station'));
                break;
            case 'firmware':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Firmware', 'live-weather-station') : __('Firmware version', 'live-weather-station'));
                break;
            case 'battery':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Battery', 'live-weather-station') : __('Battery level', 'live-weather-station'));
                break;
            case 'signal':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('WiFi', 'live-weather-station') : __('RF/WiFi signal quality', 'live-weather-station'));
                break;
            case 'co2':
            case 'co2_min':
            case 'co2_max':
            case 'co2_trend':
                $result = ($abbr ? __('CO₂', 'live-weather-station') : __('Carbon dioxide concentration', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Carbon dioxide', 'live-weather-station');
                }
                break;
            case 'health_idx':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Health', 'live-weather-station') : __('Health index', 'live-weather-station'));
                break;
            case 'cbi':
                $result = ($abbr ? __('CBi', 'live-weather-station') : __('Chandler burning index', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Burning index', 'live-weather-station');
                }
                break;
            case 'co':
                $result = ($abbr ? __('CO', 'live-weather-station') : __('Carbon monoxide concentration', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Carbon monoxide', 'live-weather-station');
                }
                break;
            case 'o3':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('O₃', 'live-weather-station') : __('Ozone layer', 'live-weather-station'));
                break;
            case 'co_distance':
                $result = ($abbr ? __('CO', 'live-weather-station') : __('Carbon monoxide probe distance', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('CO probe distance', 'live-weather-station');
                }
                break;
            case 'o3_distance':
                $result = ($abbr ? __('O₃', 'live-weather-station') : __('Ozone layer probe distance', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Ozone probe dist.', 'live-weather-station');
                }
                break;
            case 'humidity':
            case 'humidity_min':
            case 'humidity_max':
            case 'humidity_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Relative humidity', 'live-weather-station') : __('Relative humidity', 'live-weather-station'));
                break;
            case 'a_humidity':
            case 'a_humidity_min':
            case 'a_humidity_max':
            case 'a_humidity_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Absolute humidity', 'live-weather-station') : __('Absolute humidity', 'live-weather-station'));
                break;
            case 'humint':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Indoor humidity', 'live-weather-station') : __('Indoor humidity', 'live-weather-station'));
                break;
            case 'humext':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Outdoor humidity', 'live-weather-station') : __('Outdoor humidity', 'live-weather-station'));
                break;
            case 'humidity_ref':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Relative humidity', 'live-weather-station') : __('Reference relative humidity', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rel. humidity (ref)', 'live-weather-station');
                }
                break;
            case 'noise':
            case 'noise_min':
            case 'noise_max':
            case 'noise_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Noise', 'live-weather-station') : __('Noise level', 'live-weather-station'));
                break;
            case 'pressure':
            case 'pressure_min':
            case 'pressure_max':
            case 'pressure_trend':
                $result = ($abbr ? __('Pressure', 'live-weather-station') : __('Atmospheric pressure', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Atmo. pressure', 'live-weather-station');
                }
                break;
            case 'pressure_sl':
            case 'pressure_sl_min':
            case 'pressure_sl_max':
            case 'pressure_sl_trend':
                $result = ($abbr ? __('Pressure', 'live-weather-station') : __('Barometric pressure', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Baro. pressure', 'live-weather-station');
                }
                break;
            case 'temperature':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Temperature', 'live-weather-station'));
                break;
            case 'tempint':
                $result = ($abbr ? __('Indoor temperature', 'live-weather-station') : __('Indoor temperature', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Indoor temp.', 'live-weather-station');
                }
                break;
            case 'tempext':
                $result = ($abbr ? __('Outdoor temperature', 'live-weather-station') : __('Outdoor temperature', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Outdoor temp.', 'live-weather-station');
                }
                break;
            case 'temperature_ref':
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Reference temperature', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Temperature (ref)', 'live-weather-station');
                }
                break;
            case 'pressure_ref':
                $result = ($abbr ? __('Pressure', 'live-weather-station') : __('Reference pressure', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Pressure (ref)', 'live-weather-station');
                }
                break;
            case 'wind_ref':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Reference wind strength', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Wind (ref)', 'live-weather-station');
                }
                break;
            case 'temperature_max':
            case 'max_temp':
            case 'temperature_min':
            case 'min_temp':
            case 'temperature_trend':
            case 'temp_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Temperature', 'live-weather-station'));
                break;
            case 'dew_point':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Dew point', 'live-weather-station') : __('Dew point', 'live-weather-station'));
                break;
            case 'frost_point':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Frost point', 'live-weather-station') : __('Frost point', 'live-weather-station'));
                break;
            case 'heat_index':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Heat', 'live-weather-station') : __('Heat index', 'live-weather-station'));
                break;
            case 'humidex':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Humidex', 'live-weather-station') : __('Humidex', 'live-weather-station'));
                break;
            case 'wind_chill':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Wind chill', 'live-weather-station') : __('Wind chill', 'live-weather-station'));
                break;
            case 'steadman':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Steadman', 'live-weather-station') : __('Steadman index', 'live-weather-station'));
                break;
            case 'delta_t':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Delta-T', 'live-weather-station') : __('Delta-T', 'live-weather-station'));
                break;
            case 'summer_simmer':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Summer Simmer', 'live-weather-station') : __('Summer Simmer index', 'live-weather-station'));
                break;
            case 'cloud_ceiling':
                $result = ($abbr ? __('Cloud base', 'live-weather-station') : __('Cloud base altitude', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Cloud altitude', 'live-weather-station');
                }
                break;
            case 'rain':
                if (strtolower($module_type)=='namodule3') {
                    if ($meaning){$abbr = false;}
                    $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain rate', 'live-weather-station'));
                }
                else {
                    $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, last hour accumulation', 'live-weather-station'));
                    if ($meaning) {
                        /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                        $result = __('Rain last hour', 'live-weather-station');
                    }
                }
                break;
            case 'snow':
                $result = ($abbr ? __('Snow', 'live-weather-station') : __('Snow, last 3 hours accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Snow last 3h', 'live-weather-station');
                }
                break;
            case 'rain_hour_aggregated':
            case 'sum_rain_1':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, last hour accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rain last hour', 'live-weather-station');
                }
                break;
            case 'rain_day_aggregated':
            case 'sum_rain_24':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, daily accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rain today', 'live-weather-station');
                }
                break;
            case 'rain_yesterday_aggregated':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, yesterday accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rain yesterday', 'live-weather-station');
                }
                break;
            case 'rain_month_aggregated':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, monthly accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rain this month', 'live-weather-station');
                }
                break;
            case 'rain_season_aggregated':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, seasonal accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rain this season', 'live-weather-station');
                }
                break;
            case 'rain_year_aggregated':
                $result = ($abbr ? __('Rain', 'live-weather-station') : __('Rain, yearly accumulation', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Rain this year', 'live-weather-station');
                }
                break;
            case 'windangle':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind source', 'live-weather-station'));
                break;
            case 'winddirection':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind direction', 'live-weather-station'));
                break;
            case 'windstrength':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind strength', 'live-weather-station'));
                break;
            case 'gustangle':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Gust', 'live-weather-station') : __('Gust source', 'live-weather-station'));
                break;
            case 'gustdirection':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Gust', 'live-weather-station') : __('Gust direction', 'live-weather-station'));
                break;
            case 'guststrength':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Gust', 'live-weather-station') : __('Gust strength', 'live-weather-station'));
                break;
            case 'windangle_max':
            case 'windangle_hour_max':
            case 'max_wind_angle':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind source for the maximal wind strength for the last hour', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Wind source / 1h', 'live-weather-station');
                }
                break;
            case 'winddirection_max':
            case 'winddirection_hour_max':
            case 'max_wind_source':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind direction for the maximal wind strength for the last hour', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Wind dir. / 1h', 'live-weather-station');
                }
                break;
            case 'windstrength_max':
            case 'windstrength_hour_max':
            case 'max_wind_str':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind strength, maximum of the last hour', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Wind last hour', 'live-weather-station');
                }
                break;
            case 'windangle_day_max':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind source for the maximal wind strength today', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Wind source today', 'live-weather-station');
                }
                break;
            case 'winddirection_day_max':
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind direction for the maximal wind strength today', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Wind dir. today', 'live-weather-station');
                }
                break;
            case 'windstrength_day_min':
            case 'windstrength_day_max':
            case 'windstrength_day_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Wind', 'live-weather-station') : __('Wind strength', 'live-weather-station'));
                break;
            case 'guststrength_day_min':
            case 'guststrength_day_max':
            case 'guststrength_day_trend':
            if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Gust', 'live-weather-station') : __('Gust strength', 'live-weather-station'));
                break;
            case 'loc_altitude':
            case 'altitude':
            if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Altitude', 'live-weather-station') : __('Altitude', 'live-weather-station'));
                break;
            case 'loc_latitude':
            case 'latitude':
            if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Latitude', 'live-weather-station') : __('Latitude', 'live-weather-station'));
                break;
            case 'loc_longitude':
            case 'longitude':
            if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Longitude', 'live-weather-station') : __('Longitude', 'live-weather-station'));
                break;
            case 'loc_timezone':
            case 'timezone':
            if ($meaning){$abbr = false;}
                $result = ($abbr ? __('TZ', 'live-weather-station') : __('Time zone', 'live-weather-station'));
                break;
            case 'aggregated':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('all', 'live-weather-station') : __('[all measures]', 'live-weather-station'));
                break;
            case 'psychrometric':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('psychrometric', 'live-weather-station') : __('[psychrometric values]', 'live-weather-station'));
                break;
            case 'outdoor':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('outdoor', 'live-weather-station') : __('[outdoor measures]', 'live-weather-station'));
                break;
            case 'sunrise':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Sunrise', 'live-weather-station') : __('Sunrise', 'live-weather-station'));
                break;
            case 'sunrise_c':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('C. dawn', 'live-weather-station') : __('Civil dawn', 'live-weather-station'));
                break;
            case 'sunrise_n':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('N. dawn', 'live-weather-station') : __('Nautical dawn', 'live-weather-station'));
                break;
            case 'sunrise_a':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('A. dawn', 'live-weather-station') : __('Astronomical dawn', 'live-weather-station'));
                break;
            case 'sunset':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Sunset', 'live-weather-station') : __('Sunset', 'live-weather-station'));
                break;
            case 'sunset_c':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('C. dusk', 'live-weather-station') : __('Civil dusk', 'live-weather-station'));
                break;
            case 'sunset_n':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('N. dusk', 'live-weather-station') : __('Nautical dusk', 'live-weather-station'));
                break;
            case 'sunset_a':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('A. dusk', 'live-weather-station') : __('Astronomical dusk', 'live-weather-station'));
                break;
            case 'moonrise':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Moonrise', 'live-weather-station') : __('Moonrise', 'live-weather-station'));
                break;
            case 'day_length':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Day', 'live-weather-station') : __('Day duration', 'live-weather-station'));
                break;
            case 'day_length_c':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('C. day', 'live-weather-station') : __('Civil day duration', 'live-weather-station'));
                break;
            case 'day_length_n':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('N. day', 'live-weather-station') : __('Nautical day duration', 'live-weather-station'));
                break;
            case 'day_length_a':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('A. day', 'live-weather-station') : __('Astronomical day duration', 'live-weather-station'));
                break;
            case 'dawn_length_c':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('C.T. dawn', 'live-weather-station') : __('Civil twilight - dawn', 'live-weather-station'));
                break;
            case 'dawn_length_n':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('N.T. dawn', 'live-weather-station') : __('Nautical twilight - dawn', 'live-weather-station'));
                break;
            case 'dawn_length_a':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('A.T. dawn', 'live-weather-station') : __('Astronomical twilight - dawn', 'live-weather-station'));
                break;
            case 'dusk_length_c':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('C.T. dusk', 'live-weather-station') : __('Civil twilight - dusk', 'live-weather-station'));
                break;
            case 'dusk_length_n':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('N.T. dusk', 'live-weather-station') : __('Nautical twilight - dusk', 'live-weather-station'));
                break;
            case 'dusk_length_a':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('A.T. dusk', 'live-weather-station') : __('Astronomical twilight - dusk', 'live-weather-station'));
                break;
            case 'moonset':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Moonset', 'live-weather-station') : __('Moonset', 'live-weather-station'));
                break;
            case 'moon_age':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Age', 'live-weather-station') : __('Moon age', 'live-weather-station'));
                break;
            case 'moon_phase':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Phase', 'live-weather-station') : __('Moon phase', 'live-weather-station'));
                break;
            case 'moon_illumination':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Illumination', 'live-weather-station') : __('Moon illumination', 'live-weather-station'));
                break;
            case 'moon_distance':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Distance', 'live-weather-station') : __('Moon distance', 'live-weather-station'));
                break;
            case 'moon_diameter':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Size', 'live-weather-station') : __('Moon angular size', 'live-weather-station'));
                break;
            case 'sun_distance':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Distance', 'live-weather-station') : __('Sun distance', 'live-weather-station'));
                break;
            case 'sun_diameter':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Size', 'live-weather-station') : __('Sun angular size', 'live-weather-station'));
                break;
            case 'last_seen':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Seen', 'live-weather-station') : __('Last seen', 'live-weather-station'));
                break;
            case 'last_refresh':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Refresh', 'live-weather-station') : __('Last refresh', 'live-weather-station'));
                break;
            case 'first_setup':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Setup', 'live-weather-station') : __('First setup', 'live-weather-station'));
                break;
            case 'last_setup':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Setup', 'live-weather-station') : __('Last setup', 'live-weather-station'));
                break;
            case 'last_upgrade':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Upgrade', 'live-weather-station') : __('Last firmware upgrade', 'live-weather-station'));
                break;
            // PSYCHROMETRY
            case 'air_density':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Air density', 'live-weather-station') : __('Air density', 'live-weather-station'));
                break;
            case 'wet_bulb':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Wet bulb', 'live-weather-station') : __('Wet bulb temperature', 'live-weather-station'));
                break;
            case 'saturation_vapor_pressure':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('sVP', 'live-weather-station') : __('Saturation vapor pressure', 'live-weather-station'));
                break;
            case 'partial_vapor_pressure':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('pVP', 'live-weather-station') : __('Partial vapor pressure', 'live-weather-station'));
                break;
            case 'vapor_pressure':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('VP', 'live-weather-station') : __('Vapor pressure', 'live-weather-station'));
                break;
            case 'saturation_absolute_humidity':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Sat. abs. humidity', 'live-weather-station') : __('Saturation absolute humidity', 'live-weather-station'));
                break;
            case 'partial_absolute_humidity':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Part. abs. humidity', 'live-weather-station') : __('Partial absolute humidity', 'live-weather-station'));
                break;
            case 'absolute_humidity':
            case 'absolute_humidity_min':
            case 'absolute_humidity_max':
            case 'absolute_humidity_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Abs. humidity', 'live-weather-station') : __('Absolute humidity', 'live-weather-station'));
                break;
            case 'specific_enthalpy':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Enthalpy', 'live-weather-station') : __('Specific enthalpy', 'live-weather-station'));
                break;
            case 'wood_emc':
            case 'emc':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('EMC', 'live-weather-station') : __('Equilibrium moisture content', 'live-weather-station'));
                break;
            case 'equivalent_temperature':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Eq. temperature', 'live-weather-station') : __('Equivalent temperature', 'live-weather-station'));
                break;
            case 'potential_temperature':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Pot. temperature', 'live-weather-station') : __('Potential temperature', 'live-weather-station'));
                break;
            case 'equivalent_potential_temperature':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Eq. pot. temperature', 'live-weather-station') : __('Equivalent potential temperature', 'live-weather-station'));
                break;
            case 'alt_pressure':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Pressure alt.', 'live-weather-station') : __('Pressure altitude', 'live-weather-station'));
                break;
            case 'alt_density':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Density alt.', 'live-weather-station') : __('Density altitude', 'live-weather-station'));
                break;
            case 'zcast_best':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Zambretti', 'live-weather-station') : __('Zambretti forecast (at 09:00)', 'live-weather-station'));
                break;
            case 'zcast_live':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Zambretti', 'live-weather-station') : __('Zambretti forecast (live)', 'live-weather-station'));
                break;
            // SOLAR
            case 'irradiance':
            case 'irradiance_min':
            case 'irradiance_max':
            case 'irradiance_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Irradiance', 'live-weather-station') : __('Solar irradiance', 'live-weather-station'));
                break;
            case 'sunshine':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Sunshine', 'live-weather-station') : __('Sunshine duration', 'live-weather-station'));
                break;
            case 'uv_index':
            case 'uv_index_min':
            case 'uv_index_max':
            case 'uv_index_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('UV', 'live-weather-station') : __('UV index', 'live-weather-station'));
                break;
            case 'illuminance':
            case 'illuminance_min':
            case 'illuminance_max':
            case 'illuminance_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Illuminance', 'live-weather-station') : __('Solar illuminance', 'live-weather-station'));
                break;
            // SOIL
            case 'soil_temperature':
            case 'soil_temperature_min':
            case 'soil_temperature_max':
            case 'soil_temperature_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Temperature', 'live-weather-station') : __('Soil temperature', 'live-weather-station'));
                break;
            case 'leaf_wetness':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Wetness', 'live-weather-station') : __('Leaf wetness', 'live-weather-station'));
                break;
            case 'moisture_content':
            case 'moisture_content_min':
            case 'moisture_content_max':
            case 'moisture_content_trend':
                $result = ($abbr ? __('Moisture', 'live-weather-station') : __('Soil moisture content', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Soil moist. content', 'live-weather-station');
                }
                break;
            case 'moisture_tension':
            case 'moisture_tension_min':
            case 'moisture_tension_max':
            case 'moisture_tension_trend':
                $result = ($abbr ? __('Moisture', 'live-weather-station') : __('Soil moisture tension', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Soil moist. tension', 'live-weather-station');
                }
                break;
            case 'evapotranspiration':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('ET', 'live-weather-station') : __('Evapotranspiration', 'live-weather-station'));
                break;
            // THUNDERSTORM
            case 'strike_count':
                $result = ($abbr ? __('Strikes tot.', 'live-weather-station') : __('Strikes count', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Strikes (total)', 'live-weather-station');
                }
                break;
            case 'strike_instant':
                $result = ($abbr ? __('Strikes', 'live-weather-station') : __('Strikes count in last minute', 'live-weather-station'));
                if ($meaning) {
                    /* translators: appears in controls as "meaning" of the measurement, must not have more than 18 car. You can use abbreviated words. */
                    $result = __('Strikes per min.', 'live-weather-station');
                }
                break;
            case 'strike_distance':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Strike dist.', 'live-weather-station') : __('Strike distance', 'live-weather-station'));
                break;
            case 'strike_bearing':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Strike bear.', 'live-weather-station') : __('Strike bearing', 'live-weather-station'));
                break;
            // MISC
            case 'cloudcover':
            case 'cloud_cover':
            case 'cloudiness':
            case 'cloudiness_min':
            case 'cloudiness_max':
            case 'cloudiness_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Cloudiness', 'live-weather-station') : __('Cloudiness', 'live-weather-station'));
                break;
            case 'visibility':
            case 'visibility_min':
            case 'visibility_max':
            case 'visibility_trend':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Visibility', 'live-weather-station') : __('Visibility', 'live-weather-station'));
                break;
            case 'picture':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Image', 'live-weather-station') : __('Image', 'live-weather-station'));
                break;
            case 'video':
                if ($meaning){$abbr = false;}
                $result = ($abbr ? __('Video', 'live-weather-station') : __('Video', 'live-weather-station'));
                break;
            case 'video_imperial':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Video', 'live-weather-station') : __('Video with imperial subtitles', 'live-weather-station'));
                break;
            case 'video_metric':
                if ($meaning) {$abbr = true;}
                $result = ($abbr ? __('Video', 'live-weather-station') : __('Video with metric subtitles', 'live-weather-station'));
                break;
            default:
                $result = ($abbr ? '?' : __('Unknown measurement', 'live-weather-station'));
        }
        $spec = (strpos(strtolower($type),'strength_day_') !== false);
        $t = str_replace('_max', '', $type);
        $t = str_replace('_min', '', $t);
        $t = str_replace('_trend', '', $t);
        if (in_array($t, array('temp', 'absolute_humidity', 'cloudiness', 'co2', 'guststrength', 'humidity', 'illuminance', 'irradiance', 'moisture_content', 'moisture_tension', 'noise', 'pressure', 'pressure_sl', 'temperature', 'soil_temperature', 'uv_index', 'visibility', 'windstrength'))) {
            $spec = true;
        }
        if ($meaning) {
            if ($spec) {
                if (strpos(strtolower($type),'_max') !== false) {
                    $result = sprintf(__('%s (max)', 'live-weather-station'), $result);
                }
                if (strpos(strtolower($type),'_min') !== false) {
                    $result = sprintf(__('%s (min)', 'live-weather-station'), $result);
                }
                if (strpos(strtolower($type),'_trend') !== false) {
                    $result = sprintf(__('%s (trend)', 'live-weather-station'), $result);
                }
            }
            if ($type == 'max_temp') {
                $result = sprintf(__('%s (max)', 'live-weather-station'), $result);
            }
            if ($type == 'min_temp') {
                $result = sprintf(__('%s (min)', 'live-weather-station'), $result);
            }
        }
        else {
            if ($spec && !$abbr) {
                if (strpos(strtolower($type),'_max') !== false) {
                    $result = sprintf(__('%s, maximum value for today', 'live-weather-station'), $result);
                }
                if (strpos(strtolower($type),'_min') !== false) {
                    $result = sprintf(__('%s, minimum value for today', 'live-weather-station'), $result);
                }
                if (strpos(strtolower($type),'_trend') !== false) {
                    $result = sprintf(__('%s, trend', 'live-weather-station'), $result);
                }
            }
            if ($type == 'max_temp') {
                $result = sprintf(__('%s, maximum value for today', 'live-weather-station'), $result);
            }
            if ($type == 'min_temp') {
                $result = sprintf(__('%s, minimum value for today', 'live-weather-station'), $result);
            }
        }
        return $result;
    }
    /**
     * Get the dimension name in plain text.
     *
     * @param string $type The dimension type.
     * @param boolean $ten Optional. Get spec in power of ten.
     * @param boolean $plural Optional. Get the plural form.
     * @return  string  The name of the dimension in plain text.
     * @since 3.4.0
     */
    protected function get_dimension_name($type, $ten=false, $plural=false) {
        $n = ($plural ? 2 : 1);
        switch (strtolower($type)) {
            case 'percentage':
                $result =  _n('Percentage', 'Percentages', $n,  'live-weather-station');
                break;
            case 'length':
                $result =  _n('Length', 'Lengths', $n, 'live-weather-station');
                break;
            case 'concentration-m':
                $result =  _n('Concentration', 'Concentrations', $n, 'live-weather-station');
                if ($ten) {
                    $result .= ' (10⁻⁶)';
                }
                break;
            case 'concentration-b':
                $result =  _n('Concentration', 'Concentrations', $n, 'live-weather-station');
                if ($ten) {
                    $result .= ' (10⁻⁹)';
                }
                break;
            case 'area-density':
                $result =  _n('Area density', 'Area densities', $n, 'live-weather-station');
                break;
            case 'count':
            case 'base-11':
            case 'dimensionless':
                $result =  _n('Dimensionless', 'Dimensionless', $n, 'live-weather-station');
                break;
            case 'angle':
                $result =  _n('Angle', 'Angles', $n, 'live-weather-station');
                break;
            case 'speed':
                $result =  _n('Speed', 'Speeds', $n, 'live-weather-station');
                break;
            case 'rate':
                $result =  _n('Rate', 'Rates', $n, 'live-weather-station');
                break;
            case 'pressure':
                $result =  _n('Pressure', 'Pressures', $n, 'live-weather-station');
                break;
            case 'pressure-h':
                $result =  _n('Pressure', 'Pressures', $n, 'live-weather-station');
                if ($ten) {
                    $result .= ' (10²)';
                }
                break;
            case 'temperature':
                $result =  _n('Temperature', 'Temperatures', $n, 'live-weather-station');
                break;
            case 'duration':
                $result =  _n('Duration', 'Durations', $n, 'live-weather-station');
                break;
            case 'density':
                $result =  _n('Density', 'Densities', $n, 'live-weather-station');
                break;
            case 'humidity':
                $result =  _n('Relative humidity', 'Relative humidity', $n, 'live-weather-station');
                break;
            case 'a-humidity':
                $result =  _n('Absolute humidity', 'Absolute humidity', $n, 'live-weather-station');
                break;
            case 'irradiance':
                $result =  _n('Power flux density', 'Power flux densities', $n, 'live-weather-station');
                break;
            case 'illuminance':
                $result =  _n('Luminous flux density', 'Luminous flux densities', $n, 'live-weather-station');
                break;
            case 'specific-energy':
                $result =  _n('Specific energy', 'Specific energies', $n, 'live-weather-station');
                break;
            case 'specific-energy-k':
                $result =  _n('Specific energy', 'Specific energies', $n, 'live-weather-station');
                if ($ten) {
                    $result .= ' (10³)';
                }
                break;
            default:
                $result =  _n('Unknown', 'Unknown', $n, 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the comparable dimensions.
     * @return array The comparable dimensions.
     * @since 3.4.0
     */
    protected function get_comparable_dimensions() {
        return array('percentage', 'pressure-h', 'pressure', 'temperature', 'concentration-m', 'duration', 'length', 'angle', 'speed', 'area-density', 'rate', 'density', 'irradiance', 'illuminance', 'specific-energy', 'specific-energy-k', 'a-humidity');
    }

    /**
     * Get the historical operation name.
     *
     * @param string $operation The operation id.
     * @param boolean $plural Optional. Plural name.
     * @return string The historical operation name.
     * @since 3.4.0
     */
    protected function get_operation_name($operation, $plural=false) {
        $n = 1;
        if ($plural) {
            $n = 2;
        }
        if (strpos($operation, '|') == 0) {
            switch (strtolower($operation)) {
                case 'min' : $result = _n('minimum value','minimum values', $n, 'live-weather-station'); break;
                case 'max' : $result = _n('maximum value', 'maximum values', $n, 'live-weather-station'); break;
                case 'avg' : $result = _n('average value', 'average values', $n, 'live-weather-station'); break;
                case 'dev' : $result = _n('standard deviation', 'standard deviations', $n, 'live-weather-station'); break;
                case 'med' : $result = _n('median value', 'median values', $n, 'live-weather-station'); break;
                case 'agg' : $result = _n('aggregated value', 'aggregated values', $n, 'live-weather-station'); break;
                case 'dom' : $result = _n('prevalent value', 'prevalent values', $n, 'live-weather-station'); break;
                case 'amp' : $result = _n('amplitude', 'amplitudes', $n, 'live-weather-station'); break;
                case 'mid' : $result = _n('mid-range value', 'mid-range values', $n, 'live-weather-station'); break;
                case 'maxhr' : $result = _n('hourly maximum', 'hourly maximum', $n, 'live-weather-station'); break;
                default : $result = __('unknown', 'live-weather-station');
            }
        }
        else {
            $op = explode('|', $operation);
            $result = $this->get_operation_name($op[0], $plural) . ' ‣ ' . $this->get_operation_name($op[1], $plural);
        }
        return $result;
    }

    /**
     * Get the file extension description.
     *
     * @param string $ext The extension.
     * @return string The file extension description.
     * @since 3.4.0
     */
    protected function get_extension_description($ext) {
        switch (strtolower($ext)) {
            case 'csv' : $result = __('Comma-separated values','live-weather-station'); break;
            case 'dsv' : $result = __('Delimiter-separated values', 'live-weather-station'); break;
            case 'tsv' : $result = __('Tab-separated values', 'live-weather-station'); break;
            case 'ndjson' : $result = __('Standard import/export values', 'live-weather-station'); break;
            case 'wsconf.json' : $result = __('Weather Station configuration', 'live-weather-station'); break;
            default : $result = __('Unknown format', 'live-weather-station');
        }
        return $result;
    }
}