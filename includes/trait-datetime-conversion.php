<?php

/**
 * Date/Time conversions functionalities for Live Weather Station plugin
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

trait Datetime_Conversion {
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
        if ($format == '-') {
            $format = get_option('date_format');
        }
        if ( $tz != '') {
            $datetime = new DateTime(date('Y-m-d H:i:s',$ts), new DateTimeZone('UTC'));
            $datetime->setTimezone(new DateTimeZone($tz));
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
            $datetime = new DateTime($ts, new DateTimeZone('UTC'));
            $datetime->setTimezone(new DateTimeZone($tz));
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n($format, strtotime(get_date_from_gmt($ts)));
        }
    }

    /**
     * Converts an UTC time into the correct format (all Netatmo timestamps are UTC).
     *
     * @param   integer $ts The UTC timestamp to be converted.
     * @param   string  $tz Optional. The timezone.
     * @param   string  $format Optional. The time format.
     * @return  string  Formatted time relative to the given timezone.
     * @since    1.0.0
     * @access   protected
     */
    public static function get_time_from_utc($ts, $tz='', $format='-') {
        if ($format == '-') {
            $format = get_option('time_format');
        }
        if ($tz != '') {
            $datetime = new DateTime(date('Y-m-d H:i:s',$ts), new DateTimeZone('UTC'));
            $datetime->setTimezone(new DateTimeZone($tz));
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n($format, strtotime(get_date_from_gmt(date('Y-m-d H:i:s',$ts))));
        }
    }

    /**
     * Converts an UTC time into the correct format (all Netatmo timestamps are UTC).
     *
     * @param   string  $ts The UTC MySql datetime to be converted.
     * @param   string  $tz Optional. The timezone.
     * @param   string  $format Optional. The time format.
     * @return  string  Formatted time relative to the local timezone.
     * @since    1.0.0
     * @access   protected
     */
    public static function get_time_from_mysql_utc($ts, $tz='', $format='-') {
        if ($format == '-') {
            $format = get_option('time_format');
        }
        if ($tz != '') {
            $datetime = new DateTime($ts, new DateTimeZone('UTC'));
            $datetime->setTimezone(new DateTimeZone($tz));
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
     * @access   protected
     */
    public static function get_time_diff_from_utc($from) {
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
    public static function get_minute_diff_from_mysql_utc( $from ) {
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
        $result = self::get_time_from_utc($ts, $tz);
        $now = time();
        if ($comp) {
            if ( $tz != '') {
                $datetime = new DateTime(date('Y-m-d H:i:s',$now), new DateTimeZone('UTC'));
                $datetime->setTimezone(new DateTimeZone($tz));
                $today = $datetime->format('Ymd');
                $datetime = new DateTime(date('Y-m-d H:i:s',$ts), new DateTimeZone('UTC'));
                $datetime->setTimezone(new DateTimeZone($tz));
                $timestamp = $datetime->format('Ymd');
                if ($timestamp != $today) {
                    $datetime = new DateTime(date('Y-m-d H:i:s',$ts), new DateTimeZone('UTC'));
                    $datetime->setTimezone(new DateTimeZone($tz));
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
            $datetime = new DateTime(date('Y-m-d H:i:s',$ts), new DateTimeZone('UTC'));
            $datetime->setTimezone(new DateTimeZone($tz));
            return date_i18n(get_option('date_format').', '.get_option('time_format'), strtotime($datetime->format('Y-m-d H:i:s')));
        }
        else {
            return date_i18n(get_option('date_format').', '.get_option('time_format'), strtotime( get_date_from_gmt(date('Y-m-d H:i:s',$ts))) );
        }
    }

    /**
     * Converts a decimal number of days into the correct format.
     *
     * @param   float   $age The age in decimal number of days.
     * @return  string  Formatted age in years, month, days, hour and minutes.
     * @since    2.0.0
     */
    public static function get_age_from_days($age) {
        $days = floor($age);
        $hours = round(($age-$days)*24);
        $result = $days.' '.__('days', 'live-weather-station').', '.$hours.' '.__('hours', 'live-weather-station');
        return $result;
    }
}