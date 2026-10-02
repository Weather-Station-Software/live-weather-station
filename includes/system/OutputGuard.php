<?php

namespace WeatherStation\System\Output;

/**
 * Helpers to validate values and to escape them for the right output context.
 *
 * Always escape at the moment of output, for the exact context where the value lands:
 * HTML text -> esc_html(), HTML attribute -> esc_attr(), URL -> esc_url(), inline JS value -> js().
 * Values coming from shortcode attributes or AJAX parameters must also be validated with enum(), token(),
 * color(), css_size() or int() before being used to build markup, JS or CSS.
 *
 * @package Includes\System
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.15
 */
class Guard {

    /**
     * Encode a value as a JS literal that is safe inside an inline <script> block and inside HTML attributes.
     * The result already contains the quotes for strings: use it in place of '"' . $x . '"'.
     *
     * @param mixed $value The value.
     * @return string The JS literal.
     * @since 3.8.15
     */
    public static function js($value) {
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $result = wp_json_encode($value, $flags);
        return ($result === false) ? '""' : $result;
    }

    /**
     * Get a value only if it is in an allowlist.
     *
     * @param mixed $value The candidate.
     * @param array $allowed The allowed values.
     * @param mixed $default The value returned if the candidate is not allowed.
     * @return mixed The value or the default.
     * @since 3.8.15
     */
    public static function enum($value, $allowed, $default = '') {
        return (is_scalar($value) && in_array((string)$value, array_map('strval', $allowed), true)) ? (string)$value : $default;
    }

    /**
     * Get a value only if it is a simple token (letters, digits and _ : . -), i.e. an identifier, a MAC address, a CSS class.
     *
     * @param mixed $value The candidate.
     * @param string $default The value returned if the candidate is not acceptable.
     * @param int $max_length The max length.
     * @return string The token or the default.
     * @since 3.8.15
     */
    public static function token($value, $default = '', $max_length = 128) {
        if (is_scalar($value) && preg_match('/^[A-Za-z0-9_:.\-]{1,' . (int)$max_length . '}$/D', (string)$value)) {
            return (string)$value;
        }
        return $default;
    }

    /**
     * Get a value only if it is an integer.
     *
     * @param mixed $value The candidate.
     * @param int $default The value returned if the candidate is not an integer.
     * @return int The integer or the default.
     * @since 3.8.15
     */
    public static function int($value, $default = 0) {
        return (is_scalar($value) && preg_match('/^-?\d{1,12}$/D', (string)$value)) ? (int)$value : $default;
    }

    /**
     * Get a value only if it is a CSS color: #rgb, #rrggbb, #rrggbbaa or a color name.
     *
     * @param mixed $value The candidate.
     * @param string $default The value returned if the candidate is not acceptable.
     * @return string The color or the default.
     * @since 3.8.15
     */
    public static function color($value, $default = '') {
        if (is_scalar($value) && preg_match('/^(#[0-9A-Fa-f]{3,8}|[A-Za-z]{3,30}|(rgb|hsl)a?\(\s*[0-9.%]+\s*(,\s*[0-9.%]+\s*){2,3}\))$/D', (string)$value)) {
            return (string)$value;
        }
        return $default;
    }

    /**
     * Get a value only if it is a token, or several tokens separated by | (composite sets like avg|mid).
     *
     * @param mixed $value The candidate.
     * @param string $default The value returned if the candidate is not acceptable.
     * @param int $max_length The max length.
     * @return string The value or the default.
     * @since 3.8.15
     */
    public static function composite($value, $default = '', $max_length = 128) {
        if (is_scalar($value) && preg_match('/^[A-Za-z0-9_:.\-|]{1,' . (int)$max_length . '}$/D', (string)$value)) {
            return (string)$value;
        }
        return $default;
    }

    /**
     * Get a value only if it is a CSS size like 300px, 50%, 12em.
     *
     * @param mixed $value The candidate.
     * @param string $default The value returned if the candidate is not acceptable.
     * @return string The size or the default.
     * @since 3.8.15
     */
    public static function css_size($value, $default = '') {
        if (is_scalar($value) && preg_match('/^(auto|\d{1,4}(\.\d{1,2})?(px|%|em|rem|vh|vw|vmin|vmax)?)$/D', (string)$value)) {
            return (string)$value;
        }
        return $default;
    }
}
