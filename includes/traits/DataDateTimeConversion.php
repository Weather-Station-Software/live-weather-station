<?php

namespace WeatherStation\Data\DateTime;

use WeatherStation\SDK\Generic\Plugin\Season\Calculator;

/**
 * Date/Time conversions functionalities for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
trait Conversion {

    /**
     * Get the timestamp corresponding to midnight of today in a specific timezone.
     *
     * @param string  $tz The timezone.
     * @return integer The timestamp corresponding to midnight of today in this timezone.
     * @since 3.4.0
     */
    public static function get_local_today_midnight($tz) {
        $datetime = new \DateTime('today midnight', new \DateTimeZone($tz));
        return $datetime->getTimestamp();
    }

    /**
     * Get the timestamp corresponding to noon of today in a specific timezone.
     *
     * @param string  $tz The timezone.
     * @return integer The timestamp corresponding to noon of today in this timezone.
     * @since 3.4.0
     */
    public static function get_local_today_noon($tz) {
        return self::get_local_today_midnight($tz)+86399;
    }

    /**
     * Get the timestamp corresponding to midnight of yesterday in a specific timezone.
     *
     * @param string  $tz The timezone.
     * @return integer The timestamp corresponding to midnight of yesterday in this timezone.
     * @since 3.4.0
     */
    public static function get_local_yesterday_midnight($tz) {
        $datetime = new \DateTime('yesterday midnight', new \DateTimeZone($tz));
        return $datetime->getTimestamp();
    }

    /**
     * Get the timestamp corresponding to noon of yesterday in a specific timezone.
     *
     * @param string  $tz The timezone.
     * @return integer The timestamp corresponding to noon of yesterday in this timezone.
     * @since 3.4.0
     */
    public static function get_local_yesterday_noon($tz) {
        return self::get_local_yesterday_midnight($tz)+86399;
    }

    /**
     * Get the date corresponding to yesterday in a specific timezone.
     *
     * @param string  $tz The timezone.
     * @return string The timestamp corresponding to yesterday in this timezone.
     * @since 3.4.0
     */
    public static function get_local_date($tz) {
        $datetime = new \DateTime('yesterday midnight', new \DateTimeZone($tz));
        $datetime->setTime(12, 0, 0);
        return $datetime->format('Y-m-d');
    }

    /**
     * Get the timestamp corresponding to midnight, N days ago in a specific timezone.
     *
     * @param integer $n The number of days ago.
     * @param string $tz The timezone.
     * @return integer The timestamp corresponding to midnight, N days ago in a specific timezone.
     * @since 3.4.0
     */
    public static function get_local_n_days_ago_midnight($n, $tz) {
        $datetime = new \DateTime('yesterday midnight', new \DateTimeZone($tz));
        $datetime->sub(new \DateInterval('P'.$n.'D'));
        return $datetime->getTimestamp();
    }

    /**
     * Converts a date expressed in specific TZ into an UTC date.
     *
     * @param integer $ts The date to be converted.
     * @param string $tz The timezone.
     * @return integer Epoch timestamp.
     * @since 3.0.0
     */
    public static function get_date_from_tz($ts, $tz) {
        $datetime = new \DateTime(date('Y-m-d H:i:s', $ts), new \DateTimeZone($tz));
        $datetime->setTimezone(new \DateTimeZone('UTC'));
        return $datetime->getTimestamp();
    }

    /**
     * Converts an UTC date into the correct format (all Netatmo timestamps are UTC).
     *
     * @param   integer $ts The UTC timestamp to be converted.
     * @param   string  $tz Optional. The timezone.
     * @param   string  $format Optional. The date format.
     * @return  string  Formatted date relative to the given timezone.
     * @since    1.0.0
     * @access   protected
     */
    public static function get_date_from_utc($ts, $tz='', $format='-') {
        if ($ts == -1) {
            return __('N/A', 'live-weather-station');
        }
        if ($format == '-') {
            $format = get_option('date_format');
        }
        if ($tz != '') {
            $datetime = new \DateTime(date('Y-m-d H:i:s', $ts), new \DateTimeZone('UTC'));
            $datetime->setTimezone(new \DateTimeZone($tz));
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n($format, strtotime(get_date_from_gmt(date('Y-m-d H:i:s',$ts))) );
        }
    }

    /**
     * Converts an UTC date into the correct format (all Netatmo timestamps are UTC).
     *
     * @param   string  $ts The UTC MySql datetime to be converted.
     * @param   string  $tz Optional. The timezone.
     * @param   string  $format Optional. The date format.
     * @return  string   Formatted date relative to the given timezone.
     * @since    1.0.0
     * @access   protected
     */
    public static function get_date_from_mysql_utc($ts, $tz='', $format='-') {
        if ($format == '-') {
            $format = get_option('date_format');
        }
        if ($tz != '') {
            $datetime = new \DateTime($ts, new \DateTimeZone('UTC'));
            $datetime->setTimezone(new \DateTimeZone($tz));
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n($format, strtotime(get_date_from_gmt($ts)));
        }
    }

    /**
     * Converts an UTC datetime into the correct format.
     *
     * @param string $ts The UTC MySql datetime to be converted.
     * @param string $tz The timezone.
     * @param string $ps Optional. String to add at the end.
     * @return string Date "offsetted" to the given timezone.
     * @since 3.4.0
     */
    public static function get_js_datetime_from_mysql_utc($ts, $tz, $ps='000') {
        $utc_tz = new \DateTimeZone('UTC');
        $target_tz = new \DateTimeZone($tz);
        $utc_date = new \DateTime($ts, $utc_tz);
        $result = (string)($utc_date->getTimestamp()+ $target_tz->getOffset($utc_date)).$ps;
        return $result;
    }

    /**
     * Converts an UTC date into the correct format.
     *
     * @param string $ts The UTC MySql date to be converted.
     * @param string $tz The timezone.
     * @param string $ps Optional. String to add at the end.
     * @return string Date "offsetted" to the given timezone.
     * @since 3.4.0
     */
    public static function get_js_date_from_mysql_utc($ts, $tz, $ps='000') {
        $ts .= ' 12:00:00';
        $utc_tz = new \DateTimeZone('UTC');
        //$target_tz = new \DateTimeZone($tz);
        $utc_date = new \DateTime($ts, $utc_tz);
        $result = (string)($utc_date->getTimestamp()).$ps;
        return $result;
    }

    /**
     * Get a standard period id for a shifted month.
     *
     * @param integer $value The value of month to shift.
     * @param string $tz The timezone.
     * @return string A standard start:end period.
     * @since 3.4.0
     */
    public static function get_shifted_month($value, $tz) {
        $current = new \DateTime('now', new \DateTimeZone($tz));
        $year = $current->format('Y');
        $month = (integer)$current->format('m') + $value;
        while ($month > 12) {
            $month -= 12;
            $year -= 1;
        }
        while ($month < 0) {
            $month += 12;
            $year += 1;
        }
        $start = new \DateTime('now', new \DateTimeZone($tz));
        $start->setDate($year, $month, 1);
        $end = new \DateTime('now', new \DateTimeZone($tz));
        $end->setDate($year, $month, $start->format('t'));
        return $start->format('Y-m-d') . ':' . $end->format('Y-m-d');
    }

    /**
     * Get a standard period id for a shifted meteorological season.
     *
     * @param integer $value The value of meteorological season to shift.
     * @param string $tz The timezone.
     * @return string A standard start:end period.
     * @since 3.4.0
     */
    public static function get_shifted_meteorological_season($value, $tz) {
        $current = new \DateTime('now', new \DateTimeZone($tz));
        $year = $current->format('Y');
        $month = (integer)$current->format('m') + ($value * 3);
        while ($month > 12) {
            $month -= 12;
            $year += 1;
        }
        while ($month <= 0) {
            $month += 12;
            $year -= 1;
        }
        return Calculator::seasonMeteorologicalPeriod($year,$month, $tz);
    }

    /**
     * Get a standard period id for a shifted year.
     *
     * @param integer $value The value of year to shift.
     * @param string $tz The timezone.
     * @return string A standard start:end period.
     * @since 3.4.0
     */
    public static function get_shifted_year($value, $tz) {
        $current = new \DateTime('now', new \DateTimeZone($tz));
        if ($value == '-0') {
            $value = 0;
        }
        $year = (integer)$current->format('Y') + $value;
        $start = new \DateTime('now', new \DateTimeZone($tz));
        $start->setDate($year, 1, 1);
        $end = new \DateTime('now', new \DateTimeZone($tz));
        $end->setDate($year, 12, 31);
        return $start->format('Y-m-d') . ':' . $end->format('Y-m-d');
    }

    /**
     * Converts an UTC time into the correct format (all timestamps *are* UTC).
     *
     * @param integer $ts The UTC timestamp to be converted.
     * @param string $tz Optional. The timezone.
     * @param string $format Optional. The time format.
     * @return string Formatted time relative to the given timezone.
     * @since 1.0.0
     */
    public static function get_time_from_utc($ts, $tz='', $format='-') {
        if ($ts == -1) {
            return __('N/A', 'live-weather-station');
        }
        if ($format == '-') {
            $format = get_option('time_format');
        }
        if ($tz != '') {
            $datetime = new \DateTime(date('Y-m-d H:i:s',$ts), new \DateTimeZone('UTC'));
            $datetime->setTimezone(new \DateTimeZone($tz));
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n($format, strtotime(get_date_from_gmt(date('Y-m-d H:i:s',$ts))));
        }
    }

    /**
     * Converts an UTC time into the correct format (all timestamps *are* UTC).
     *
     * @param   string  $ts The UTC MySql datetime to be converted.
     * @param   string  $tz Optional. The timezone.
     * @param   string  $format Optional. The time format.
     * @return  string  Formatted time relative to the local timezone.
     * @since    1.0.0
     */
    public static function get_time_from_mysql_utc($ts, $tz='', $format='-') {
        if ($format == '-') {
            $format = get_option('time_format');
        }
        if ($tz != '') {
            $datetime = new \DateTime($ts, new \DateTimeZone('UTC'));
            $datetime->setTimezone(new \DateTimeZone($tz));
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n($format, strtotime(get_date_from_gmt($ts)));
        }
    }

    /**
     * Get the difference between now and a date, in human readable style (like "8 minutes ago" or "in 19 seconds").
     *
     * @param   integer $from The UTC timestamp from which the difference must be computed (as today).
     * @return  string  Human readable time difference.
     * @since    1.0.0
     */
    public static function get_time_diff_from_utc($from) {
        if (!is_numeric($from)) {
            return self::get_time_diff_from_mysql_utc($from);
        }
        if ($from == -1) {
            return __('N/A', 'live-weather-station');
        }
        if ($from < time()) {
            return sprintf( __('%s ago', 'live-weather-station'), human_time_diff($from));
        }
        else {
            return sprintf( __('in %s', 'live-weather-station'), human_time_diff($from));
        }
    }

    /**
     * Get the difference between now and a date, in human readable style (like "8 minutes ago" or "now").
     *
     * @param   string $from The UTC MySql datetime from which the difference must be computed (as today).
     * @return  string  Human readable time difference.
     * @since    1.0.0
     */
    public static function get_positive_time_diff_from_mysql_utc($from) {
        if (strtotime($from) < time()) {
            return sprintf( __('%s ago', 'live-weather-station'), human_time_diff(strtotime($from)));
        }
        else {
            return __('currently', 'live-weather-station');
        }
    }

    /**
     * Get the difference between now and a date, in human readable style (like "8 minutes ago" or "in 19 seconds").
     *
     * @param   string $from The UTC MySql datetime from which the difference must be computed (as today).
     * @return  string  Human readable time difference.
     * @since    1.0.0
     */
    public static function get_time_diff_from_mysql_utc($from) {
        if (strtotime($from) < time()) {
            return sprintf( __('%s ago', 'live-weather-station'), human_time_diff(strtotime($from)));
        }
        else {
            return sprintf( __('in %s', 'live-weather-station'), human_time_diff(strtotime($from)));
        }
    }

    /**
     * Get the difference between now and a date, in minutes.
     *
     * @param   integer     $from The UTC timestamp from which the difference must be computed (as today).
     * @return  integer     Time difference in minutes.
     * @since    2.0.0
     * @access   protected
     */
    public static function get_minute_diff_from_utc($from) {
        return round ((abs( strtotime(get_date_from_gmt(date('Y-m-d H:i:s'))) - $from ))/60);
    }

    /**
     * Get the difference between now and a date, in minutes.
     *
     * @param   integer     $from The UTC timestamp from which the difference must be computed (as today).
     * @return  integer     Time difference in minutes.
     * @since    2.0.0
     * @access   protected
     */
    public static function get_minute_diff_from_mysql_utc($from) {
        return round ((abs( strtotime(get_date_from_gmt(date('Y-m-d H:i:s'))) - strtotime(get_date_from_gmt($from))))/60);
    }

    /**
     * Converts an UTC time into the correct format (all ephemeris timestamps are UTC).
     *
     * @param   integer     $ts The UTC timestamp to be converted.
     * @param   string      $tz Optional. The timezone.
     * @param   boolean     $comp   Must be completed by day name.
     * @return  string  Formatted time relative to the given timezone.
     * @since    2.0.0
     */
    public static function get_rise_set_short_from_utc($ts, $tz='', $comp=false) {
        $mod = $ts % 60;
        if ($mod > 29) {
            $ts = $ts + 60 - $mod;
        }
        else {
            $ts = $ts - $mod;
        }
        $result = self::get_time_from_utc($ts, $tz);
        $now = time();
        if ($comp) {
            if ( $tz != '') {
                $datetime = new \DateTime(date('Y-m-d H:i:s',$now), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                $today = $datetime->format('Ymd');
                $datetime = new \DateTime(date('Y-m-d H:i:s',$ts), new \DateTimeZone('UTC'));
                $datetime->setTimezone(new \DateTimeZone($tz));
                $timestamp = $datetime->format('Ymd');
                if ($timestamp != $today) {
                    $datetime = new \DateTime(date('Y-m-d H:i:s',$ts), new \DateTimeZone('UTC'));
                    $datetime->setTimezone(new \DateTimeZone($tz));
                    $result = $result.' ('.date_i18n('D', strtotime($datetime->format('Y-m-d H:i:s'))).')';
                }
            }
            else {
                $today = date('Ymd', strtotime(get_date_from_gmt(date('Y-m-d H:i:s', $now))));
                $timestamp = date('Ymd', strtotime(get_date_from_gmt(date('Y-m-d H:i:s', $ts))));
                if ($timestamp != $today) {
                    $result = $result.' ('.date_i18n( 'D', strtotime( get_date_from_gmt(date('Y-m-d H:i:s',$ts)))).')';
                }
            }
        }
        return $result;
    }

    /**
     * Converts an UTC time into the correct format (all ephemeris timestamps are UTC).
     *
     * @param   integer $ts The UTC timestamp to be converted.
     * @param   string  $tz Optional. The timezone.
     * @return  string  Formatted time relative to the given timezone.
     * @since    2.0.0
     */
    public static function get_rise_set_long_from_utc($ts, $tz='') {
        if ( $tz != '') {
            $datetime = new \DateTime(date('Y-m-d H:i:s',$ts), new \DateTimeZone('UTC'));
            $datetime->setTimezone(new \DateTimeZone($tz));
            return date_i18n(get_option('date_format').', '.get_option('time_format'), strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n(get_option('date_format').', '.get_option('time_format'), strtotime(get_date_from_gmt(date('Y-m-d H:i:s',$ts))));
        }
    }

    /**
     * Converts a decimal number of days into the correct format.
     *
     * @param float $age The age in decimal number of days.
     * @return string Formatted age in days and hours.
     * @since 2.0.0
     */
    public static function get_age_from_days($age) {
        $days = floor($age);
        $hours = round(($age-$days)*24);
        $result = $days.' '.__('days', 'live-weather-station').', '.$hours.' '.__('hours', 'live-weather-station');
        return $result;
    }

    /**
     * Converts a decimal number of seconds into the correct format.
     *
     * @param integer $age The age in seconds.
     * @return string Formatted age in days, hours, minutes and seconds.
     * @since 3.1.0
     */
    public static function get_age_hours_from_seconds($age) {
        $intervals = array(
            array(60, __('second', 'live-weather-station'), __('seconds', 'live-weather-station')),
            array(60, __('minute', 'live-weather-station'), __('minutes', 'live-weather-station')),
            array(100000, __('hour', 'live-weather-station'), __('hours', 'live-weather-station'))
        );
        $value = array();
        foreach ($intervals as $interval) {
            $val = $age % $interval[0];
            $age = round(($age-$val)/$interval[0], 0);
            if ($val > 0) {
                if ($val == 1) {
                    $value[] = $val . ' ' . $interval[1];
                }
                else {
                    $value[] = $val . ' ' . $interval[2];
                }
            }
        }
        return implode(', ', array_reverse($value));
    }

    /**
     * Verify if it's really the night (astronomical night).
     *
     * @param integer $sunrise_a The astronomical sunrise timestamp.
     * @param integer $sunset_a The astronomical sunset timestamp.
     * @param integer $time Optional. The time to match.
     * @return boolean True if it's night, false otherwise.
     * @since 3.1.0
     */
    public function is_it_night($sunrise_a, $sunset_a, $time=null) {
        if ($sunrise_a == -1 || $sunset_a == -1) {
            return false;
        }
        if (is_null($time)) {
            $time = time();
        }
        if ($time < $sunrise_a) {
            return true;
        }
        if ($time > $sunset_a) {
            return true;
        }
        return false;
    }

    /**
     * Verify if we are in dawn twilight.
     *
     * @param integer $sunrise The sunrise utc timestamp.
     * @param integer $sunrise_a The astronomical sunrise timestamp.
     * @param integer $sunset_a The astronomical sunset timestamp.
     * @param integer $time Optional. The time to match.
     * @return boolean True if it's dawn, false otherwise.
     * @since 3.1.0
     */
    public function is_it_dawn($sunrise, $sunrise_a, $sunset_a, $time=null) {
        if ($sunrise_a == -1 || $sunset_a == -1 || $sunrise == -1) {
            return false;
        }
        if (is_null($time)) {
            $time = time();
        }
        if (($time < $sunrise) && !$this->is_it_night($sunrise_a, $sunset_a, $time)) {
            return true;
        }
        return false;
    }

    /**
     * Verify if we are in dusk twilight.
     *
     * @param integer $sunset The sunset utc timestamp.
     * @param integer $sunrise_a The astronomical sunrise timestamp.
     * @param integer $sunset_a The astronomical sunset timestamp.
     * @param integer $time Optional. The time to match.
     * @return boolean True if it's dusk, false otherwise.
     * @since 3.1.0
     */
    public function is_it_dusk($sunset, $sunrise_a, $sunset_a, $time=null) {
        if ($sunrise_a == -1 || $sunset_a == -1 || $sunset == -1) {
            return false;
        }
        if (is_null($time)) {
            $time = time();
        }
        if (($time > $sunset) && !$this->is_it_night($sunrise_a, $sunset_a, $time)) {
            return true;
        }
        return false;
    }

    /**
     * Calculate the percentage of elapsed dawn twilight.
     *
     * @param integer $sunrise The sunrise timestamp.
     * @param integer $sunrise_a The astronomical sunrise timestamp.
     * @param integer $time Optional. The time to match.
     * @return integer The percentage of dawn.
     * @since 3.1.0
     */
    public function dawn_percentage($sunrise, $sunrise_a, $time=null) {
        if ($sunrise_a == -1 || $sunrise == -1) {
            return 0;
        }
        if (is_null($time)) {
            $time = time();
        }
        $result = ($sunrise - $time) / ($sunrise - $sunrise_a);
        return round(100*$result, 0);
    }

    /**
     * Calculate the percentage of elapsed dusk twilight.
     *
     * @param integer $sunset The sunset timestamp.
     * @param integer $sunset_a The astronomical sunset timestamp.
     * @param integer $time Optional. The time to match.
     * @return boolean True if it's dusk, false otherwise.
     * @since 3.1.0
     */
    public function dusk_percentage($sunset, $sunset_a, $time=null) {
        if ($sunset == -1 || $sunset_a == -1) {
            return 0;
        }
        if (is_null($time)) {
            $time = time();
        }
        $result = ($sunset_a - $time) / ($sunset_a - $sunset);
        return round(100*$result, 0);
    }
}