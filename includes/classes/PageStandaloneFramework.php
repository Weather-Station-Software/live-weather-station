<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Logs\LoggableException;
use WeatherStation\System\HTTP\Client as HTTP;
use WeatherStation\System\URL\Handling as URL;

/**
 * Serves the public feeds (/get-weather/[station]/[format]/) from inside WordPress.
 *
 * The request is recognized through the query variables filled by the rewrite rules (or by the plain query string
 * fallback) and answered on the `template_redirect` action: no standalone PHP file that has to load WordPress by itself.
 *
 * @package Includes\Classes
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Framework {

    use URL;

    /**
     * The query variable carrying the station id.
     *
     * @since 3.9.0
     */
    const QUERY_VAR_STATION = 'live_weather_station_station';

    /**
     * The query variable carrying the feed type.
     *
     * @since 3.9.0
     */
    const QUERY_VAR_TYPE = 'live_weather_station_type';

    /**
     * The query variable carrying the feed subformat.
     *
     * @since 3.9.0
     */
    const QUERY_VAR_SUBFORMAT = 'live_weather_station_subformat';

    /**
     * The formats served by a feed class.
     *
     * @since 3.9.0
     */
    const SERVED_TYPES = array('stickertags', 'yowindow');

    /**
     * The formats that have a public address but no feed class: they answer 501.
     *
     * @since 3.9.0
     */
    const NOT_IMPLEMENTED_TYPES = array('clientraw', 'realtime');

    /**
     * The HTTP codes the feeds may answer; any other code becomes a 500.
     *
     * @since 3.9.0
     */
    const ERROR_CODES = array(400, 404, 405, 501);

    /**
     * Apply configuration (flush the rewrite rules).
     *
     * @since 3.0.0
     */
    public static function apply_configuration() {
        self::apply();
    }

    /**
     * Get the value of a query variable as a string.
     *
     * @param string $name The query variable name.
     * @return string The value, or an empty string if it is missing or not a string.
     * @since 3.9.0
     */
    private static function query_value($name) {
        $value = get_query_var($name, '');
        return is_string($value) ? $value : '';
    }

    /**
     * Rate limit the logging of this anonymous endpoint (max 60 log rows per minute) to avoid log flooding.
     *
     * @param boolean $critical Optional. The row is a critical one.
     * @return boolean True if a log row may be written now.
     * @since 3.9.0
     */
    private static function may_log($critical=false) {
        // Fixed window (start timestamp stored, never slid) so sustained traffic cannot mute logging forever.
        // Critical rows have their own counter and are never muted by informational ones.
        $key = $critical ? 'lws_standalone_log_rate_c' : 'lws_standalone_log_rate';
        $now = time();
        $state = get_transient($key);
        if (!is_array($state) || !isset($state['s'], $state['c']) || ($now - (int)$state['s']) >= 60) {
            $state = array('s' => $now, 'c' => 0);
        }
        if ((int)$state['c'] >= 60) {
            return false;
        }
        $state['c'] = (int)$state['c'] + 1;
        set_transient($key, $state, 120);
        return true;
    }

    /**
     * Answer 404 with an empty body.
     *
     * @since 3.9.0
     */
    private static function not_found() {
        status_header(404);
        header('X-Content-Type-Options: nosniff');
        exit();
    }

    /**
     * Handle a feed request: the entry point of the `template_redirect` action.
     *
     * Does nothing if the current request is not a feed request.
     *
     * @since 3.9.0
     */
    public static function handle_request() {
        $station = self::query_value(self::QUERY_VAR_STATION);
        $type = self::query_value(self::QUERY_VAR_TYPE);
        if ($station === '' && $type === '') {
            return;
        }
        // The rewrite rules capture the station id as it appears in the path (the colons may be percent-encoded).
        $station = rawurldecode($station);
        $type = strtolower($type);
        $subformat = 'standard';
        $raw_subformat = self::query_value(self::QUERY_VAR_SUBFORMAT);
        if ($raw_subformat !== '' && preg_match('/^[A-Za-z0-9_-]{1,40}$/D', $raw_subformat) === 1) {
            $subformat = strtolower($raw_subformat);
        }
        if (preg_match('/^[A-Z0-9]{2}(?::[A-F0-9]{2}){5}$/iD', $station) !== 1 || preg_match('/^[a-z0-9_]{1,40}$/D', $type) !== 1) {
            self::not_found();
        }
        if (in_array($type, self::NOT_IMPLEMENTED_TYPES, true)) {
            self::error(501);
        }
        if (!in_array($type, self::SERVED_TYPES, true)) {
            self::not_found();
        }
        try {
            $classname = '\WeatherStation\Engine\Page\Standalone\\' . ucfirst($type);
            $generator = new $classname();
            status_header(200);
            $generator->send(array('station' => $station), $subformat);
        }
        catch (LoggableException $ex) {
            Logger::exception($ex);
            self::error(self::error_code($ex));
        }
        catch (\Throwable $ex) {
            self::error(self::error_code($ex));
        }
        if (self::may_log()) {
            Logger::info('Page Generator', null, null, null, null, null , 0, 'Success while rendering file.' . HTTP::get_request_detail_as_text());
        }
        exit();
    }

    /**
     * Map an exception to the HTTP code to send.
     *
     * @param \Throwable $ex The exception.
     * @return int The HTTP code: one of the allowed codes, 500 otherwise.
     * @since 3.9.0
     */
    private static function error_code($ex) {
        $code = (int)$ex->getCode();
        return in_array($code, self::ERROR_CODES, true) ? $code : 500;
    }

    /**
     * Renders error in output, and stops.
     *
     * @param integer $code Optional. An HTTP error code.
     * @since 3.0.0
     */
    private static function error($code = 501) {
        // The feed classes send their headers only once the content is built: a failure leaves them unsent.
        if (!headers_sent()) {
            status_header($code);
            header('Content-type: text/plain; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        $message = HTTP::get_http_status($code);
        echo esc_html(LIVE_WEATHER_STATION_PLUGIN_NAME . ' / ' . $message);
        if (self::may_log(true)) {
            Logger::critical('Page Generator', null, null, null, null, null , $code, 'Unable to generate the requested page. Header "'. $message .'" sent to client.'  . HTTP::get_request_detail_as_text());
        }
        exit();
    }

}
