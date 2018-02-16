<?php

namespace WeatherStation\SDK\Generic\Plugin\Season;

/**
 * A Season utility that helps calculate seasons dates.
 *
 * @package Includes\Libraries
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 3.4.0
 * @license GPL
 */

class Calculator {

    private static function getMeteorologicalSeasonStartDate ($year, $month, $tz) {
        switch ($month) {
            case 12:
                return $year.'-12-01';
                break;
            case 1:
            case 2:
                return (string)($year-1).'-12-01';
                break;
            case 3:
            case 4:
            case 5:
            return $year.'-03-01';
                break;
            case 6:
            case 7:
            case 8:
            return $year.'-06-01';
                break;
            case 9:
            case 10:
            case 11:
            return $year.'-09-01';
                break;
        }
        return '';
    }

    private static function getMeteorologicalSeasonEndDate ($year, $month, $tz) {
        $m = 1;
        switch ($month) {
            case 12:
                $m = 2;
                $year += 1;
                break;
            case 1:
            case 2:
                $m = 2;
                break;
            case 3:
            case 4:
            case 5:
                $m = 5;
                break;
            case 6:
            case 7:
            case 8:
                $m = 8;
                break;
            case 9:
            case 10:
            case 11:
                $m = 11;
                break;
        }
        $start = new \DateTime('now', new \DateTimeZone($tz));
        $start->setDate($year, $m, 1);
        $start->setDate($year, $m, $start->format('t'));
        return $start->format('Y-m-d');
    }

    public static function meteorologicalSeasonName ($month, $north_hemisphere=true) {
        if ($north_hemisphere) {
            switch ($month) {
                case 12:
                case 1:
                case 2:
                    return __('winter', 'live-weather-station');
                    break;
                case 3:
                case 4:
                case 5:
                    return __('spring', 'live-weather-station');
                    break;
                case 6:
                case 7:
                case 8:
                    return __('summer', 'live-weather-station');
                    break;
                case 9:
                case 10:
                case 11:
                    return __('autumn', 'live-weather-station');
                    break;
            }
        }
        else {
            switch ($month) {
                case 12:
                case 1:
                case 2:
                    return __('summer', 'live-weather-station');
                    break;
                case 3:
                case 4:
                case 5:
                    return __('autumn', 'live-weather-station');
                    break;
                case 6:
                case 7:
                case 8:
                    return __('winter', 'live-weather-station');
                    break;
                case 9:
                case 10:
                case 11:
                    return __('spring', 'live-weather-station');
                    break;
            }
        }
        return '';
    }

    /**
     * Calculate a list of season matching a list of dates.
     */
    public static function matchingMeteorologicalSeasons($dates, $tz, $north_hemisphere =true) {
        $suf = ' (' . __('meteorological', 'live-weather-station') . ')';
        $seasons = array();
        foreach ($dates as $date) {
            $e = explode('-', $date[0]);
            $start = self::getMeteorologicalSeasonStartDate($e[0], $e[1], $tz);
            $end = self::getMeteorologicalSeasonEndDate($e[0], $e[1], $tz);
            $seasons[] = array($start.':'.$end, $e[0] . ', ' . self::meteorologicalSeasonName($e[1], $north_hemisphere).$suf);
        }
        return array_reverse(lws_array_super_unique($seasons, 0));
    }

    /**
     * Get a standard meteorological season period.
     */
    public static function seasonMeteorologicalPeriod($year, $month, $tz) {
        $start = self::getMeteorologicalSeasonStartDate($year,$month, $tz);
        $end = self::getMeteorologicalSeasonEndDate($year,$month, $tz);
        return $start.':'.$end;
    }

}