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
     * @return  string  The probable timezone.
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
}