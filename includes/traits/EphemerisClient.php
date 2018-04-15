<?php

namespace WeatherStation\SDK\Generic\Plugin\Ephemeris;

use WeatherStation\System\Logs\Logger;
use WeatherStation\SDK\Generic\Plugin\Astronomy\MoonPhase;
use WeatherStation\SDK\Generic\Plugin\Astronomy\MoonRiseSet;
use WeatherStation\Data\Dashboard\Handling as Dashboard_Manipulation;
use WeatherStation\Data\DateTime\Conversion as Datetime_Conversion;
use WeatherStation\Data\ID\Handling as Id_Manipulation;

/**
 * Ephemeris client for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
trait Client {

    use Id_Manipulation, Datetime_Conversion, Dashboard_Manipulation;

    protected $ephemeris_datas;
    protected $facility = 'Ephemeris Computer';
    protected $service_name = null;


    /**
     * Compute ephemeris.
     *
     * @param mixed $station_type Optional. The station type to compute.
     * @return array Computed values.
     * @since    2.0.0
     */
    public function compute($station_type=false) {
        $result = array();
        $stations = $this->get_located_operational_stations_list($station_type);
        foreach ($stations as $id => $station) {
            if (array_key_exists('loc_longitude', $station) && array_key_exists('loc_latitude', $station)) {
                $lat = $station['loc_latitude'];
                $lon = $station['loc_longitude'];
            }
            else {
                Logger::warning($this->facility, $this->service_name, $id, $station['device_name'], null, null, 135, 'Can\'t compute ephemeris for a station without coordinates.');
                continue;
            }
            $tz = $station['loc_timezone'];
            $place = array();
            $place['country'] = $station['loc_country'];
            $place['city'] = $station['loc_city'];
            $place['altitude'] = $station['loc_altitude'];
            $place['timezone'] = $tz;
            $place['location'] = array($lon, $lat);
            $nm = array();
            $nm['place'] = $place;
            $nm['device_id'] = $id;
            $nm['device_name'] = $station['device_name'];
            $nm['_id'] = self::get_ephemeris_virtual_id($id);
            $nm['type'] = 'NAEphemer';
            $nm['module_name'] = __('[Ephemeris]', 'live-weather-station');
            $nm['battery_vp'] = 6000;
            $nm['rf_status'] = 0;
            $nm['firmware'] = LWS_VERSION;
            $dashboard = array() ;
            $dashboard['time_utc'] = time();
            // sunrise & sunset
            $time_rise = time()-36000;
            $time_set = time()-36000;
            $datetime = new \DateTime();
            $datetime->setTimestamp(time());
            $datetime->setTimezone(new \DateTimeZone($tz));
            $month = $datetime->format('m');
            $day = $datetime->format('d');
            for ($fact = -1; $fact <= 2; $fact++) {
                $sunrise = date_sunrise(time()+(86400*$fact), SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
                $verif = new \DateTime();
                $verif->setTimestamp($sunrise);
                $verif->setTimezone(new \DateTimeZone($tz));
                if ($month == $verif->format('m') && $day == $verif->format('d')) {
                    $time_rise = time()+(86400*$fact);
                    break;
                }
            }
            for ($fact = -1; $fact <= 2; $fact++) {
                $sunset = date_sunset(time()+(86400*$fact), SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
                $verif = new \DateTime();
                $verif->setTimestamp($sunset);
                $verif->setTimezone(new \DateTimeZone($tz));
                if ($month == $verif->format('m') && $day == $verif->format('d')) {
                    $time_set = time()+(86400*$fact);
                    break;
                }
            }
            $dashboard['sunrise'] = date_sunrise($time_rise, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
            $dashboard['sunrise_c'] = date_sunrise($time_rise, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 96);
            $dashboard['sunrise_n'] = date_sunrise($time_rise, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 102);
            $dashboard['sunrise_a'] = date_sunrise($time_rise, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 108);
            $dashboard['sunset'] = date_sunset($time_set,  SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
            $dashboard['sunset_c'] = date_sunset($time_set, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 96);
            $dashboard['sunset_n'] = date_sunset($time_set, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 102);
            $dashboard['sunset_a'] = date_sunset($time_set, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 108);
            // lengths of day
            if ($dashboard['sunset'] && $dashboard['sunrise']) {
                $dashboard['day_length'] = $dashboard['sunset'] - $dashboard['sunrise'];
            }
            else {
                $dashboard['day_length'] = -1;
            }
            if ($dashboard['sunset_c'] && $dashboard['sunrise_c']) {
                $dashboard['day_length_c'] = $dashboard['sunset_c'] - $dashboard['sunrise_c'];
            }
            else {
                $dashboard['day_length_c'] = -1;
            }
            if ($dashboard['sunset_n'] && $dashboard['sunrise_n']) {
                $dashboard['day_length_n'] = $dashboard['sunset_n'] - $dashboard['sunrise_n'];
            }
            else {
                $dashboard['day_length_n'] = -1;
            }
            if ($dashboard['sunset_a'] && $dashboard['sunrise_a']) {
                $dashboard['day_length_a'] = $dashboard['sunset_a'] - $dashboard['sunrise_a'];
            }
            else {
                $dashboard['day_length_a'] = -1;
            }
            // lengths of dawn
            $dashboard['dawn_length_a'] = -1;
            $dashboard['dawn_length_n'] = -1;
            $dashboard['dawn_length_c'] = -1;
            if ($dashboard['sunrise_n'] && $dashboard['sunrise_a']) {
                $dashboard['dawn_length_a'] = $dashboard['sunrise_n'] - $dashboard['sunrise_a'];
            }
            if ($dashboard['sunrise_c'] && $dashboard['sunrise_n']) {
                $dashboard['dawn_length_n'] = $dashboard['sunrise_c'] - $dashboard['sunrise_n'];
            }
            if ($dashboard['sunrise'] && $dashboard['sunrise_c']) {
                $dashboard['dawn_length_c'] = $dashboard['sunrise'] - $dashboard['sunrise_c'];
            }
            // lengths of dusk
            $dashboard['dusk_length_a'] = -1;
            $dashboard['dusk_length_n'] = -1;
            $dashboard['dusk_length_c'] =-1;
            if ($dashboard['sunset_a'] && $dashboard['sunset_n']) {
                $dashboard['dusk_length_a'] = $dashboard['sunset_a'] - $dashboard['sunset_n'];
            }
            if ($dashboard['sunset_n'] && $dashboard['sunset_c']) {
                $dashboard['dusk_length_n'] = $dashboard['sunset_n'] - $dashboard['sunset_c'];
            }
            if ($dashboard['sunset_c'] && $dashboard['sunset']) {
                $dashboard['dusk_length_c'] = $dashboard['sunset_c'] - $dashboard['sunset'];
            }
            try {
                $datetime = new \DateTime(date('Y-m-d H:i:s',time()), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                $month = $datetime->format('m');
                $day = $datetime->format('d');
                $moon = MoonRiseSet::calculateMoonTimes($datetime->format('m'), $datetime->format('d'), $datetime->format('Y'), $lat, $lon);
                $moonrise = $moon->moonrise;
                $moonset = $moon->moonset;
                $datetime = new \DateTime(date('Y-m-d H:i:s',time()-86400), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                $moon = MoonRiseSet::calculateMoonTimes($datetime->format('m'), $datetime->format('d'), $datetime->format('Y'), $lat, $lon);
                $moonrise_yesterday = $moon->moonrise;
                $moonset_yesterday = $moon->moonset;
                $datetime = new \DateTime(date('Y-m-d H:i:s',time()+86400), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                $moon = MoonRiseSet::calculateMoonTimes($datetime->format('m'), $datetime->format('d'), $datetime->format('Y'), $lat, $lon);
                $moonrise_tomorrow = $moon->moonrise;
                $moonset_tomorrow = $moon->moonset;
                $datetime = new \DateTime(date('Y-m-d H:i:s',$moonrise_yesterday), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                if ($month == $datetime->format('m') && $day == $datetime->format('d')) {
                    $moonrise = $moonrise_yesterday;
                }
                $datetime = new \DateTime(date('Y-m-d H:i:s',$moonset_yesterday), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                if ($month == $datetime->format('m') && $day == $datetime->format('d')) {
                    $moonset = $moonset_yesterday;
                }
                $datetime = new \DateTime(date('Y-m-d H:i:s',$moonrise_tomorrow), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                if ($month == $datetime->format('m') && $day == $datetime->format('d')) {
                    $moonrise = $moonrise_tomorrow;
                }
                $datetime = new \DateTime(date('Y-m-d H:i:s',$moonset_tomorrow), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                if ($month == $datetime->format('m') && $day == $datetime->format('d')) {
                    $moonset = $moonset_tomorrow;
                }
                $dashboard['moonrise'] = $moonrise;
                $dashboard['moonset'] = $moonset;
            }
            catch(\Exception $ex) {
                $dashboard['moonrise'] = -9999;
                $dashboard['moonset'] = -9999;
            }
            try {
                $moon_phase = new MoonPhase();
                $dashboard['moon_age'] = round($moon_phase->age(), 3);
                $dashboard['moon_phase'] = round($moon_phase->phase(),4);
                $dashboard['moon_illumination'] = round($moon_phase->illumination()*100);
                $dashboard['moon_distance'] = round($moon_phase->distance());
                $dashboard['moon_diameter'] = round($moon_phase->diameter(), 9);
                $dashboard['sun_distance'] = round($moon_phase->sundistance());
                $dashboard['sun_diameter'] = round($moon_phase->sundiameter(), 9);
            }
            catch(\Exception $ex) {
                $dashboard['moon_age'] = -9999;
                $dashboard['moon_phase'] = -9999;
                $dashboard['moon_illumination'] = -9999;
                $dashboard['moon_distance'] = -9999;
                $dashboard['moon_diameter'] = -9999;
                $dashboard['sun_distance'] = -9999;
                $dashboard['sun_diameter'] = -9999;
            }
            $prefs = array('sunrise', 'sunset');
            $posts = array('', 'c', 'n', 'a');
            foreach ($prefs as $pref) {
                if (!isset($dashboard[$pref])) {
                    $dashboard[$pref] = -1;
                }
                else {
                    if (!($dashboard[$pref])) {
                        $dashboard[$pref] = -1;
                    }
                }
                foreach ($posts as $post) {
                    if (!isset($dashboard[$pref . '_' . $post])) {
                        if ($post != '') {
                            $dashboard[$pref . '_' . $post] = -1;
                        }
                    }
                    else {
                        if (!$dashboard[$pref . '_' . $post]) {
                            if ($post != '') {
                                $dashboard[$pref . '_' . $post] = -1;
                            }
                        }
                    }
                }
            }
            $nm['dashboard_data'] = $dashboard;
            $nm['data_type'] = array('sunrise','sunrise_c','sunrise_n','sunrise_a', 'sunset','sunset_c','sunset_n',
                                     'sunset_a', 'moonrise', 'moonset', 'day_length', 'day_length_c', 'day_length_n',
                                     'day_length_a', 'dawn_length_a','dawn_length_n', 'dawn_length_c', 'dusk_length_a',
                                     'dusk_length_n', 'dusk_length_c', 'moon_age', 'moon_phase', 'moon_illumination',
                                     'moon_distance', 'moon_diameter', 'sun_distance', 'sun_diameter');
            if (count($nm['dashboard_data']) > 0) {
                $result[] = $nm;
            }
        }
        foreach ($result as $data) {
            $this->get_dashboard($data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data'], $data['place']);
            Logger::info($this->facility, $this->service_name, $data['device_id'], $data['device_name'], $data['_id'], $data['module_name'], 0, 'Ephemeris data computed.');
        }
        return $result;
    }
}