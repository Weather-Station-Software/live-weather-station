<?php

namespace WeatherStation\System\HTTP;

/**
 * HTTP handling for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait Handling {

    private static $statusTexts = array(

        // Standard codes
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585

        // Unofficial codes
        103 => 'Checkpoint',
        420 => 'Method Failure (Spring Framework) / Enhance Your Calm (Twitter)',
        450 => 'Blocked by Windows Parental Controls (Microsoft)',
        498 => 'Invalid Token (Esri)',
        499 => 'Token Required (Esri) / Request has been forbidden by antivirus',
        509 => 'Bandwidth Limit Exceeded (Apache Web Server/cPanel)',
        530 => 'Site is frozen',

        // Internet Information Services codes
        440 => 'Login Timeout (IIS)',
        449 => 'Retry With (IIS)',
        //451 => 'Redirect (IIS)',

        // nginx codes
        444 => 'No Response (nginx)',
        495 => 'SSL Certificate Error (nginx)',
        496 => 'SSL Certificate Required (nginx)',
        497 => 'HTTP Request Sent to HTTPS Port (nginx)',
        //499 => 'Client Closed Request (nginx)',
        
        // CloudFlare codes
        520 => 'Unknown Error (CloudFlare)',
        521 => 'Web Server Is Down (CloudFlare)',
        522 => 'Connection Timed Out (CloudFlare)',
        523 => 'Origin Is Unreachable (CloudFlare)',
        524 => 'A Timeout Occurred (CloudFlare)',
        525 => 'SSL Handshake Failed (CloudFlare)',
        526 => 'Invalid SSL Certificate (CloudFlare)'

    );

    private static $requestDetail = array('REQUEST_URI', 'REQUEST_METHOD', 'REMOTE_ADDR', 'REMOTE_HOST', 'HTTP_USER_AGENT');

    /**
     * Get http request detail.
     *
     * @since    3.0.0
     */
    public static function get_request_detail_as_text() {
        $result = PHP_EOL;
        $result .= '** REQUEST DETAILS **' . PHP_EOL;
        foreach (self::$requestDetail as $req) {
            if (array_key_exists($req, $_SERVER)) {
                $result .= $req . ' => ' . $_SERVER[$req] . PHP_EOL;
            }
        }
        return $result;
    }

    /**
     * Get http status text.
     *
     * @since    3.0.0
     */
    public static function get_http_status($code) {
        if (array_key_exists($code, self::$statusTexts)) {
            $s = 'HTTP ' . $code . ' / ' . self::$statusTexts[$code] . '.';
        }
        else {
            $s = 'Unknown HTTP error.';
        }
        return $s;
    }


}