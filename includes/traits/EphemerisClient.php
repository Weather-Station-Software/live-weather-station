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
     * @since    2.0.0
     */
    public function compute() {
        $result = array();
        $stations = $this->get_located_operational_stations_list();
        foreach ($stations as $id => $station) {
            $lat = $station['loc_latitude'];
            $lon = $station['loc_longitude'];
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
            $dashboard['sunrise'] = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
            $dashboard['sunset'] = date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
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
            $nm['dashboard_data'] = $dashboard;
            $nm['data_type'] = array('sunrise', 'sunset', 'moonrise', 'moonset', 'moon_age', 'moon_phase', 'moon_illumination', 'moon_distance', 'moon_diameter', 'sun_distance', 'sun_diameter');
            if (count($nm['dashboard_data']) > 0) {
                $result[] = $nm;
            }
        }
        foreach ($result as $data) {
            $this->get_dashboard($data['device_id'], $data['device_name'], $data['_id'], $data['module_name'],
                $data['type'], $data['data_type'], $data['dashboard_data'], $data['place']);
            Logger::debug($this->facility, $this->service_name, $data['device_id'], $data['device_name'], $data['_id'], $data['module_name'], 0, 'Success while computing ephemeris.');
        }
        return $result;
    }
}