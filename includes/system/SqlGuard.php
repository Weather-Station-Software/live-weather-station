<?php

namespace WeatherStation\System\SQL;

/**
 * Small helpers to build SQL safely with $wpdb->prepare().
 *
 * Values must always go through prepare() placeholders (%s, %d, %f). Identifiers (column/table names) cannot be
 * bound, so they must be validated against an allowlist with ident(). IN() lists need one placeholder per value,
 * see placeholders().
 *
 * @package Includes\System
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.15
 */
class Guard {

    /**
     * Validate an identifier (column name...).
     *
     * @param mixed $name The candidate identifier.
     * @param array|null $allowed Optional allowlist. When given, the identifier must be in it.
     * @return string|null The identifier, or null if it is not acceptable.
     * @since 3.8.15
     */
    public static function ident($name, $allowed = null) {
        if (!is_string($name) || !preg_match('/^[A-Za-z0-9_]{1,64}$/D', $name)) {
            return null;
        }
        if (is_array($allowed) && !in_array($name, $allowed, true)) {
            return null;
        }
        return $name;
    }

    /**
     * Get the placeholders of an IN() list.
     *
     * @param array $values The values of the list.
     * @param string $type The placeholder type: '%s', '%d' or '%f'.
     * @return string The comma separated placeholders, or 'NULL' for an empty list (IN (NULL) matches nothing).
     * @since 3.8.15
     */
    public static function placeholders($values, $type = '%s') {
        if (!is_array($values) || count($values) === 0) {
            return 'NULL';
        }
        return implode(',', array_fill(0, count($values), $type));
    }

    /**
     * Check a date/time string used in a query (YYYY-MM-DD with an optional time part).
     *
     * @param mixed $value The candidate date.
     * @return string|null The date, or null if it is not acceptable.
     * @since 3.8.15
     */
    public static function datetime($value) {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/D', $value)) {
            return $value;
        }
        return null;
    }
}
