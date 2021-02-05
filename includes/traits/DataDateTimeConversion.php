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
     * Get period values array.
     *
     * @param array $station The station informations.
     * @param string $oldest_date Oldest record for the station.
     * @param boolean $rolling Optional. The array must contains rolling periods.
     * @param boolean $noned Optional. The array must contains the "none" period.
     * @return array An array containing the period values ready to convert to a JS array.
     * @since 3.8.0
     */
    public function get_period_values($station, $oldest_date, $rolling=true, $noned=false) {
        $result = array();

        // Rolling days
        if ($rolling) {
            $period = array();
            foreach (array(7, 15, 30, 60, 90) as $i) {
                $period[] = array('rdays-' . $i, sprintf(__('Last %s days', 'live-weather-station'), $i));
            }
            $result[] = array('rolling-days', $period);
        }

        // Sliding month
        $period = array();
        for ($i=0; $i<=12; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'month-'.$i, __('Current month', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-month',  $period);

        // Sliding meteorological season
        $period = array();
        for ($i=0; $i<=4; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'mseason-'.$i, __('Current meteorological season', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-mseason',  $period);

        // Sliding astronomical season
        $period = array();
        for ($i=0; $i<=4; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'aseason-'.$i, __('Current astronomical season', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-aseason',  $period);

        // Sliding year
        $period = array();
        for ($i=0; $i<=10; $i++) {
            $s = '';
            if ($i != 0) {
                $s = ' - ' . $i;
            }
            $period[] = array( 'year-'.$i, __('Current year', 'live-weather-station') . $s);
        }
        $result[] = array('sliding-year',  $period);

        // Fixed year & month
        $fixed_month = array();
        $fixed_year = array();
        $start = new \DateTime($oldest_date, new \DateTimeZone($station['loc_timezone']));
        $current = new \DateTime($oldest_date, new \DateTimeZone($station['loc_timezone']));
        $util = new \DateTime($oldest_date, new \DateTimeZone($station['loc_timezone']));
        $year = $start->format('Y');
        $month = $start->format('m');
        $end = new \DateTime('now', new \DateTimeZone($station['loc_timezone']));
        while ($year != $end->format('Y') || $month != $end->format('m')) {
            $current->setDate($year, $month, 1);
            $util->setDate($year, $month, $current->format('t'));
            $fixed_month[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y, F', strtotime($current->format('Y-m-d H:i:s'))));
            $month += 1;
            if ($month > 12) {
                $current->setDate($year, 1, 1);
                $util->setDate($year, 12, 31);
                $fixed_year[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y', strtotime($current->format('Y-m-d H:i:s'))));
                $month = 1;
                $year += 1;
            }
        }
        $current->setDate($year, $month, 1);
        $util->setDate($year, $month, $current->format('t'));
        $fixed_month[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y, F', strtotime($end->format('Y-m-d H:i:s'))));
        $current->setDate($year, 1, 1);
        $util->setDate($year, 12, 31);
        $fixed_year[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), date_i18n('Y', strtotime($end->format('Y-m-d H:i:s'))));
        if (empty($fixed_month)) {
            $fixed_month = array(array('none', 'none'));
        }
        $result[] = array('fixed-month', $noned?array_merge(array(array('0', __('None', 'live-weather-station'))), array_reverse($fixed_month)):array_reverse($fixed_month));
        if (empty($fixed_year)) {
            $fixed_year = array(array('none', 'none'));
        }
        $result[] = array('fixed-year', $noned?array_merge(array(array('0', __('None', 'live-weather-station'))), array_reverse($fixed_year)):array_reverse($fixed_year));

        // Fixed meteorological season
        $result[] = array('fixed-mseason', $noned?array_merge(array(array('0', __('None', 'live-weather-station'))), Calculator::matchingMeteorologicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0)):Calculator::matchingMeteorologicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0));

        // Fixed astronomical season
        //$result[] = array('fixed-aseason', Season::matchingAstronomicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0));

        // Aggregated year & month
        $aggregated_month = array();
        for ($m=1; $m<13; $m++) {
            if ($m < (int)$start->format('m')) {
                $y = (int)$start->format('Y') + 1;
            }
            else {
                $y = (int)$start->format('Y');
            }
            if ($m > (int)$end->format('m')) {
                $e = (int)$end->format('Y') - 1;
            }
            else {
                $e = (int)$end->format('Y');
            }
            $current->setDate($y, $m, 1);
            $util->setDate($e, $m, 1);
            $util->setDate($e, $m, $util->format('t'));
            if ($y <= $e) {
                $aggregated_month[] = array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), sprintf('%s, %s ⇥ %s', date_i18n('F', strtotime($current->format('Y-m-d H:i:s'))), $y, $e));
            }
        }
        $result[] = array('aggregated-month', $aggregated_month);
        $current->setDate($start->format('Y'), 1, 1);
        $util->setDate($end->format('Y'), 12, 31);
        $result[] = array('aggregated-year', array(array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), sprintf('%s ⇥ %s', $current->format('Y'), $util->format('Y')))));

        // Aggregated meteorological season
        $result[] = array('aggregated-mseason', $noned?array_merge(array(array('0', __('None', 'live-weather-station'))), Calculator::matchingClimatologicalMeteorologicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0)):Calculator::matchingClimatologicalMeteorologicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0));

        // Aggregated astronomical season
        //$result[] = array('aggregated-aseason', Season::matchingAstronomicalSeasons($fixed_month, $station['loc_timezone'], $station['loc_latitude'] >= 0));

        // Rotating year
        $result[] = array('rotating-year', array(array($current->format('Y-m-d') . ':' . $util->format('Y-m-d'), sprintf('%s ⇥ %s', $current->format('Y'), $util->format('Y')))));

        $result[] = array('none',  array(array('none', 'none')));
        return $result;
    }

    /**
     * Add months to a date.
     *
     * @param \DateTime $date The date.
     * @param integer $months The number of months to add (may be positive or negative).
     * @return \DateTime The modified date.
     * @since 3.8.0
     */
    public function date_add_month($date,$months){
        $years = floor(abs($months / 12));
        $leap = 29 <= $date->format('d');
        $m = 12 * (0 <= $months?1:-1);
        for ($a = 1;$a < $years;++$a) {
            $date = $this->date_add_month($date, $m);
        }
        $months -= ($a - 1) * $m;
        $init = clone $date;
        if (0 != $months) {
            $modifier = $months . ' months';
            $date->modify($modifier);
            if ($date->format('m') % 12 != (12 + $months + (int)$init->format('m')) % 12) {
                $day = $date->format('d');
                $init->modify("-{$day} days");
            }
            $init->modify($modifier);
        }
        $y = $init->format('Y');
        if ($leap && ($y % 4) == 0 && ($y % 100) != 0 && 28 == $init->format('d')) {
            $init->modify('+1 day');
        }
        return $init;
    }

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
     * Get the timestamp corresponding to the middle of today in a specific timezone.
     *
     * @param string  $tz The timezone.
     * @return integer The timestamp corresponding to the middle of today in this timezone.
     * @since 3.7.3
     */
    public static function get_local_today_middle($tz) {
        return self::get_local_today_midnight($tz)+43200;
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
     * @param string $tz Optional. The timezone.
     * @return integer The timestamp corresponding to midnight, N days ago in a specific timezone.
     * @since 3.4.0
     */
    public static function get_local_n_days_ago_midnight($n, $tz='UTC') {
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
     * Add x days to a MySql date.
     *
     * @param string $date The MySql date.
     * @param string $days Number of days to add.
     * @return string The new MySql date.
     * @since 3.7.0
     */
    public static function add_days_to_mysql_date($date, $days) {
        $datetime = \DateTime::createFromFormat('Y-m-d', $date);
        $datetime->add(new \DateInterval('P' . $days . 'D'));
        return $datetime->format('Y-m-d');
    }

    /**
     * Sub x days to a MySql date.
     *
     * @param string $date The MySql date.
     * @param string $days Number of days to substract.
     * @return string The new MySql date.
     * @since 3.7.0
     */
    public static function sub_days_to_mysql_date($date, $days) {
        $datetime = \DateTime::createFromFormat('Y-m-d', $date);
        $datetime->sub(new \DateInterval('P' . $days . 'D'));
        return $datetime->format('Y-m-d');
    }

    /**
     * Compare two MySql dates.
     *
     * @param string $date1 The first MySql date.
     * @param string $date2 The second MySql date.
     * @return boolean The comparison of MySql dates.
     * @since 3.7.0
     */
    public static function mysql_is_ordered($date1, $date2) {
        $datetime1 = \DateTime::createFromFormat('Y-m-d', $date1);
        $datetime2 = \DateTime::createFromFormat('Y-m-d', $date2);
        return $datetime2->getTimestamp() >= $datetime1->getTimestamp();
    }

    /**
     * Verify a MySql date.
     *
     * @param string $date The MySql date to verify.
     * @return boolean True if it's a valid date. False otherwise..
     * @since 3.7.0
     */
    public static function verify_mysql_date($date) {
        try {
            $datetime = \DateTime::createFromFormat('Y-m-d', $date);
            return $datetime->format('Y-m-d') == $date;
        }
        catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Converts an UTC datetime into the correct format.
     *
     * @param string $ts The UTC MySql datetime to be converted.
     * @param string $tz The timezone.
     * @param integer $factor Optional. Factor to multiply by.
     * @return string Date "offsetted" to the given timezone.
     * @since 3.4.0
     */
    public static function get_js_datetime_from_mysql_utc($ts, $tz, $factor=1000) {
        $utc_tz = new \DateTimeZone('UTC');
        $target_tz = new \DateTimeZone($tz);
        $utc_date = new \DateTime($ts, $utc_tz);
        $result = ($utc_date->getTimestamp() + $target_tz->getOffset($utc_date)) * $factor;
        return $result;
    }

    /**
     * Converts an UTC date into the correct format.
     *
     * @param string $ts The UTC MySql date to be converted.
     * @param string $tz The timezone.
     * @param integer $factor Optional. String to add at the end.
     * @return integer Date "offsetted" to the given timezone.
     * @since 3.4.0
     */
    public static function get_js_date_from_mysql_utc($ts, $tz, $factor=1000) {
        $ts .= ' 12:00:00';
        $utc_tz = new \DateTimeZone('UTC');
        //$target_tz = new \DateTimeZone($tz);
        $utc_date = new \DateTime($ts, $utc_tz);
        $result = $utc_date->getTimestamp() * $factor;
        return $result;
    }

    /**
     * Get a standard period id for rolling days.
     *
     * @param integer $value The value of days to roll off.
     * @param string $tz The timezone.
     * @return string A standard start:end period.
     * @since 3.4.0
     */
    public static function get_rolling_days($value, $tz) {
        $end = new \DateTime('-1 day', new \DateTimeZone($tz));
        $start = new \DateTime(sprintf('-%s days', $value), new \DateTimeZone($tz));
        return $start->format('Y-m-d') . ':' . $end->format('Y-m-d');
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
            $year += 1;
        }
        while ($month < 0) {
            $month += 12;
            $year -= 1;
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
     * Get a standard meteorological season period.
     *
     * @param integer $year The value of the year.
     * @param integer $month The value of the month.
     * @param string $tz The timezone.
     * @return string A standard start:end period.
     * @since 3.8.0
     */
    public static function get_season_meteorological_period($year,$month, $tz) {
        return Calculator::seasonMeteorologicalPeriod($year,$month, $tz);
    }

    /**
     * Get a list of months for a meteorological season.
     *
     * @param integer $month The value of the month.
     * @return array A array containing valid months.
     * @since 3.8.0
     */
    public static function get_meteorological_season_months($month) {
        return Calculator::meteorologicalSeasonMonths($month);
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
     * Converts a decimal number of seconds into an array.
     *
     * @param integer $age The age in seconds.
     * @param boolean $legend Optional. Add the legend.
     * @return array Array of days, hours, minutes and seconds.
     * @since 3.6.0
     */
    public static function get_age_array_from_seconds($age, $legend=false) {
        $intervals = array(
            array(60, __('second', 'live-weather-station'), __('seconds', 'live-weather-station')),
            array(60, __('minute', 'live-weather-station'), __('minutes', 'live-weather-station')),
            array(100000, __('hour', 'live-weather-station'), __('hours', 'live-weather-station'))
        );
        $value = array();
        foreach ($intervals as $interval) {
            $val = $age % $interval[0];
            $age = round(($age-$val)/$interval[0], 0);
            if (($val > 0 && $legend) || ($val >= 0 && !$legend)) {
                $value[] = $val . ($legend? ' ' . $interval[($val === 1?1:2)] : '');
            }
        }
        return array_reverse($value);
    }

    /**
     * Converts a decimal number of seconds into an array.
     *
     * @param integer $age The age in seconds.
     * @param string $format Optional. The format.
     * @return array Array of hours, minutes and seconds.
     * @since 3.8.0
     */
    public static function get_age_format_from_seconds($age, $format='hh-mm') {
        $value = array();
        foreach (array(60, 60, 100000) as $key => $interval) {
            $val = $age % $interval;
            $age = round(($age-$val)/$interval, 0);
            if (($format == 'hh-mm' && $key != 0) || $format == 'hh-mm-ss') {
                $value[] = str_pad($val, 2, '0', STR_PAD_LEFT);
            }
        }
        return array_reverse($value);
    }

    /**
     * Converts a decimal number of seconds into the correct format.
     *
     * @param integer $age The age in seconds.
     * @param string $format Optional. Special format if needed.
     * @return string Formatted age in days, hours, minutes and seconds.
     * @since 3.1.0
     */
    public static function get_age_hours_from_seconds($age, $format='') {
        $result = implode(', ', self::get_age_array_from_seconds($age, true));
        if ($result === '') {
            $result = __('less than 1 second', 'live-weather-station');
        }
        if ($format == 'hh-mm') {
            $result = implode(':', self::get_age_format_from_seconds($age, 'hh-mm'));
        }
        if ($format == 'hh-mm-ss') {
            $result = implode(':', self::get_age_format_from_seconds($age, 'hh-mm-ss'));
        }
        return $result;
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
        if (!is_numeric($sunrise) || !is_numeric($sunrise_a)) {
            return 0;
        }
        if ($sunrise_a - $sunrise === 0) {
            return 100;
        }
        if (is_null($time)) {
            $time = time();
        }
        $result = ($sunrise - $time) / ($sunrise - $sunrise_a);
        return round(100 * $result, 0);
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
        if (!is_numeric($sunset) || !is_numeric($sunset_a)) {
            return 0;
        }
        if ($sunset_a - $sunset === 0) {
            return 100;
        }
        if (is_null($time)) {
            $time = time();
        }
        $result = ($sunset_a - $time) / ($sunset_a - $sunset);
        return round(100 * $result, 0);
    }

    /**
     * Get the longest continuous period.
     *
     * @param array $list The list of sorted dates.
     * @return array An array containing length (in days), start and end UTC dates.
     * @since 3.8.0
     */
    public function get_longest_period($list) {
        $result = array('length' => 0, 'start' => '1971-08-21', 'end' => '1971-08-21');
        $start = null;
        $last = null;
        $maxstart = null;
        $maxend = null;
        $count = 0;
        $max = 0;
        if (count($list) > 0) {
            foreach ($list as $d) {
                $date = $d['val'];
                if (isset($start)) {
                    $dlast = new \DateTime($last);
                    $ddate = new \DateTime($date);
                    if ($ddate->getTimestamp() - $dlast->getTimestamp() == 86400) {
                        $last = $date;
                        $count += 1;
                    }
                    else {
                        $count = 1;
                        $start = $date;
                        $last = $date;
                    }
                    if ($count >= $max) {
                        $maxstart = $start;
                        $maxend = $last;
                        $max = $count;
                    }
                }
                else {
                    $count = 1;
                    $max = 1;
                    $start = $date;
                    $last = $date;
                }
            }
            $result['length'] = $max;
            $result['start'] = $maxstart;
            $result['end'] = $maxend;
        }
        return $result;
    }

}