<?php

namespace WeatherStation\Data\DateTime;


/**
 * Date/Time handling functionalities for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
trait Handling {

    /**
     * Get the probale timezone.
     *
     * @return string The probable timezone.
     * @since 3.6.0
     */
    protected function get_probable_timezone($country, $utc) {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country);
        if (count($timezones) == 0) {
            switch ($country) {
                case 'AN':
                    $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ATLANTIC);
                    break;
                case 'CP':
                    $timezones = array('Pacific/Tahiti');
                    break;
                case 'DG':
                    $timezones = array('Indian/Chagos');
                    break;
                case 'EA':
                    $timezones = array('Africa/Ceuta');
                    break;
                case 'HM':
                    $timezones = array('Antarctica/Mawson');
                    break;
                case 'IC':
                    $timezones = array('Europe/Madrid');
                    break;
                default:
                    return 'UTC';
            }
        }
        foreach ($timezones as $timezone) {
            $tz = new \DateTimeZone($timezone);
            if ($utc == $tz->getOffset(new \DateTime) / 3600) {
                return $timezone;
            }
        }
        return 'UTC';
    }

    /**
     * Get the site timezone.
     *
     * @return string The site timezone.
     * @since 3.8.0
     */
    protected function get_site_timezone() {
        $tz = get_option('timezone_string', '');
        if ($tz != '') {
            return $tz;
        }
        else {
            $offset = get_option('gmt_offset', '');
            if ($offset != '') {
                if ($offset < 0) {
                    $tz = '-';
                }
                else {
                    $tz = '+';
                }
                $t = (string)abs((int)$offset);
                while (strlen($t) < 2) {
                    $t = '0' . $t;
                }
                $tz = $tz . $t;
                if ((int)$offset != $offset) {
                    $tz = $tz . '30';
                }
                else {
                    $tz = $tz . '00';
                }
                return $tz;
            }
        }
        return 'UTC';
    }

    /**
     * Get the night / day status of the station.
     *
     * @param float $lat The latitude of the station.
     * @param float $lon The longitude of the station.
     * @param string $tz The timezone of the station.
     * @return boolean True if it's day, false otherwise.
     * @since 3.8.0
     */
    protected function check_day($lat, $lon, $tz) {
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
        $sunrise = date_sunrise($time_rise, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
        $sunset = date_sunset($time_set, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
        return (time() > $sunrise && time() < $sunset);
    }

    /**
     * Check if we are less than 4 hours to a sunset or sunrise.
     *
     * @param float $lat The latitude of the station.
     * @param float $lon The longitude of the station.
     * @param string $tz The timezone of the station.
     * @return boolean True if it's day, false otherwise.
     * @since 3.8.0
     */
    protected function check_mixday($lat, $lon, $tz) {
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
        $sunrise = date_sunrise($time_rise, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
        $sunset = date_sunset($time_set, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, 90+(50/60));
        if (time() > $sunrise && time() < $sunset) { // Currently day
            return ($sunset - time() < 60 * 60 * 4);
        }
        else { // Currently night
            return ($sunrise - time() < 60 * 60 * 4);
        }
    }
}