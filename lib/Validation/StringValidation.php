<?php
namespace Littled\Validation;

use Littled\Exception\ContentValidationException;
use DateTime;


class StringValidation extends ValueValidation
{
    /**
     * Tests if there are non-whitespace characters in a string.
     * @param string|null $var
     * @return bool
     */
    public static function isStringBlank(?string $var): bool
    {
        return (trim(''.$var) === '');
    }

    /**
     * Tests if a variable is a string of more than 0 characters.
     * @param mixed $var Variable to test
     * @return bool TRUE if the variable holds a string value of more than 0 characters.
     */
    public static function isStringWithContent(mixed $var): bool
    {
        return (
            is_string($var) &&
            strlen($var) > 0
        );
    }

    /**
     * Tests if a string starts with a vowel.
     * @param string|null $str
     * @return bool
     */
    public static function startsWithVowel(?string $str): bool
    {
        if (trim(''.$str) === '') {
            return false;
        }
        return in_array(strtolower($str[0]), ['a', 'e', 'i', 'o', 'u']);
    }

    /**
     * Strips HTML tags from request variable value.
     * @param string $key
     * @param array $whitelist_tags
     * @param int|null $index
     * @param array|null $src
     * @return string
     */
    public static function stripTags(string $key, array $whitelist_tags = [], ?int $index = null, ?array $src = null): string
    {
        $value = Validation::_parseInput(FILTER_FLAG_NONE, $key, $index, $src);
        return strip_tags('' . $value, $whitelist_tags);
    }

    /**
     * Tests date string to see if it is in a recognized format.
     * @param string $date Date string to test.
     * @param array|null $formats Data formats to test.
     * @return DateTime
     * @throws ContentValidationException
     */
    public static function validateDateString(string $date, ?array $formats = null): DateTime
    {
        if ($formats == null) {
            $formats = [
                'Y-m-d',
                'm/d/y',
                'm/d/Y',
                'n/j/y',
                'n/j/Y',
                'F d, Y',
                'F j, Y',
                'M d, Y',
                'M j, Y'
            ];
        } elseif (!is_array($formats)) {
            $formats = [$formats];
        }

        foreach ($formats as $format) {
            $d = Validation::_testDateFormat($date, $format);
            if ($d instanceof DateTime) {
                return ($d);
            }
        }
        throw new ContentValidationException('Unrecognized date value.');
    }
}