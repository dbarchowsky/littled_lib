<?php
namespace Littled\Validation;

use DateTime;


class ValueValidation
{
    /**
     * Tests a date string against a specified date format string.
     * @param string $date Date string to test.
     * @param string $format Date format string to use to evaluate the date string.
     * @return DateTime|null Returns DateTime object representing the date if the date string matches the date format
     * string. Returns null otherwise.
     */
    protected static function _testDateFormat(string $date, string $format): ?DateTime
    {
        $d = DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) == $date) {
            return ($d);
        }
        return null;
    }

    /**
     * Converts a given string value to a numeric equivalent.
     * @param mixed $value Value to parse.
     * @return float|int|null
     */
    public static function parseNumeric(mixed $value): float|int|null
    {
        if (true === $value || false === $value) {
            return null;
        }
        if (is_numeric($value)) {
            if (str_contains($value, '.')) {
                return ((float)$value);
            } elseif ($value > PHP_INT_MAX) {
                return ((float)$value);
            } else {
                return ((int)$value);
            }
        }
        return null;
    }

    public static function parseNumericArray(array $arr): array
    {
        return array_values(
            array_map(
                fn($el) => Validation::parseNumeric($el),
                array_filter($arr, fn($el) => is_numeric($el))));
    }

    /**
     * Tests if string value represents an integer value.
     * @param mixed $value Value to test.
     * @return bool
     */
    public static function isInteger(mixed $value): bool
    {
        if (is_int($value)) {
            return true;
        }
        if (is_string($value)) {
            if ($value == '') {
                return false;
            }
            if ($value[0] == '-') {
                return ctype_digit(substr($value, 1));
            } else {
                return ctype_digit($value);
            }
        }
        return false;
    }

    /**
     * Tests value and returns TRUE if it evaluates to some string that equates with a "true" flag.
     * Returns FALSE only if the value evaluates to some string that equates with a "false" flag.
     * Returns NULL if the value doesn't make sense in a TRUE/FALSE context.
     * @param mixed $value Value to test.
     * @return ?bool TRUE, FALSE, or NULL
     */
    public static function parseBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return ($value);
        }
        if (in_array($value, [1, '1', 'true', 'on', 'yes'])) {
            return true;
        }
        if (in_array($value, [0, '0', 'false', 'off', 'no'])) {
            return false;
        }
        return null;
    }

    /**
     * Tests a variable and returns its equivalent explicit integer value, or null if the variable value doesn't
     * represent an integer value.
     * @param mixed $value Value to test.
     * @return int|null Value explicitly converted to an integer value, or null if the value does not represent an
     * integer value.
     */
    public static function parseInteger(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return ((int)round($value));
        }
        return null;
    }
}