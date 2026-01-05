<?php
namespace Littled\Validation;

use Littled\App\LittledGlobals;


class InputValidation extends RequestValidation
{
    /**
     * Retrieves any valid integer values passed as request parameters.
     * @codeCoverageIgnore
     * @param int $input_type Token representing an input type, e.g., INPUT_GET or INPUT_POST
     * @param string $key Key in the input collection to use to collect values.
     * @param array $definition Filtering definition to pass to PHP's filter_input_array() routine.
     * @return array|null
     */
    protected static function _filterIntegerInputArray(int $input_type, string $key, array $definition): ?array
    {
        $result = filter_input_array($input_type, $definition);
        if (is_array($result)) {
            $input_value = $result[$key];
            if (is_array($input_value)) {
                return (array_filter($input_value, 'Littled\Validation\Validation::isInteger'));
            } else {
                $value = Validation::parseInteger($input_value);
                if ($value) {
                    return [$value];
                }
            }
        }
        return null;
    }

    /**
     * Returns request variable as explicit integer value, or null if the request variable is not set or does not
     * represent a float value.
     * @param int $filter Filter to apply to the variable value, e.g., FILTER_VALIDATE_INT or FILTER_VALIDATE_FLOAT
     * @param string $key Key in the collection storing the value to look up.
     * @param int|null $index Index of the array to look up if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g., $_GET or $_POST
     * @return string|bool|null
     */
    protected static function _parseInput(
        int $filter,
        string $key,
        ?int $index = null,
        ?array $src = null): bool|string|null
    {
        if ($src === null) {
            $src = static::getDefaultInputSource();
        }
        if (!array_key_exists($key, $src)) {
            return null;
        }
        if ($index !== null) {
            $arr = filter_var($src[$key], $filter, FILTER_REQUIRE_ARRAY);
            if (is_array($arr) && array_key_exists($index, $arr)) {
                return $arr[$index];
            }
        } else if ($filter == FILTER_FLAG_NONE) {
            return $src[$key];
        } else if ($filter == FILTER_UNSAFE_RAW) {
            return strip_tags('' . $src[$key]);
        } else if ($filter === FILTER_VALIDATE_FLOAT) {
            if ($src[$key] === true || $src[$key] === false) {
                return null;
            }
            return filter_var($src[$key], $filter);
        } else {
            return filter_var($src[$key], $filter);
        }
        return '';
    }

    /**
     * Get the default client request input source and check it for an existing value. Returns FALSE if no existing value
     * is found.
     * @param array|null $src
     * @param string $key
     * @return bool
     */
    protected static function checkSourceValue(?array &$src, string $key): bool
    {
        if ($src === null) {
            $src = static::getDefaultInputSource();
        }
        if (!isset($src[$key])) {
            return false;
        }
        return true;
    }

    /**
     * Returns TRUE/FALSE depending on the value of the requested input variable.
     * @param string $key Input variable name in either GET or POST data.
     * @param int|null $index (Optional) index of the element to test, if the variable is an array.
     * @param array|null $src (Optional) array to use in place of GET or POST data.
     * @return bool|null TRUE/FALSE depending on the value of the input variable.
     */
    public static function collectBooleanRequestVar(string $key, ?int $index = null, ?array $src = null): ?bool
    {
        if (!static::checkSourceValue($src, $key)) {
            return null;
        }
        $value = null;
        if ($index !== null) {
            $arr = filter_var($src[$key], RequestValidation::DEFAULT_REQUEST_FILTER, FILTER_REQUIRE_ARRAY);
            if (is_array($arr) && count($arr) >= ($index - 1)) {
                $value = $arr[$index];
            }
        } else {
            $value = $src[$key] === false ? false : trim(filter_var($src[$key], RequestValidation::DEFAULT_REQUEST_FILTER));
        }

        return Validation::parseBoolean($value);
    }

    /**
     * Converts script argument (query string or form data) to an array of numeric values.
     * @param string $key Key containing potential numeric values.
     * @param ?array $src Array of variables to use instead of GET or POST data.
     * @return array|null Returns an array if values are found for the specified key. Null otherwise.
     */
    public static function collectIntegerArrayRequestVar(string $key, ?array $src = null): ?array
    {
        if (!static::checkSourceValue($src, $key)) {
            return [];
        }
        $arr = filter_var($src[$key], FILTER_VALIDATE_FLOAT, FILTER_FORCE_ARRAY);
        if (!is_array($arr)) {
            return [];
        }
        // filter out any elements that are false or null, but keep elements equal to "0"
        $arr = array_filter($arr, function ($i) {
            return (false !== $i && null !== $i);
        });
        // convert float values to int
        $arr = array_map(function ($i) {
            return Validation::parseInteger($i);
        }, $arr);
        // re-index the returned array
        return array_values($arr);
    }

    /**
     * Returns request variable as explicit integer value, or null if the request variable is not set or does not
     * represent a float value.
     * @param string $key Key in the collection storing the value to look up.
     * @param int|null $index Index of the array to look up if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g., $_GET or $_POST
     * @return int|null
     */
    public static function collectIntegerRequestVar(string $key, ?int $index = null, ?array $src = null): ?int
    {
        $value = Validation::_parseInput(FILTER_VALIDATE_FLOAT, $key, $index, $src);
        return Validation::parseInteger($value);
    }

    /**
     * Returns request variable as explicit integer value, or null if the request variable is not set or does not
     * represent a float value.
     * @param string $key Key in the collection storing the value to look up.
     * @param int|null $index Index of the array to look up if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g., $_GET or $_POST
     * @return float|int|null
     */
    public static function collectNumericRequestVar(string $key, ?int $index = null, ?array $src = null): float|int|null
    {
        $value = Validation::_parseInput(FILTER_VALIDATE_FLOAT, $key, $index, $src);
        return Validation::parseNumeric($value);
    }

    /**
     * Converts script argument (query string or form data) to an array of numeric values.
     * @param string $key Key containing potential numeric values.
     * @param array|null $src Optional array of variables to use instead of GET or POST data.
     * @return array|null Returns an array if values are found for the specified key. Null otherwise.
     */
    public static function collectNumericArrayRequestVar(string $key, ?array $src = null): ?array
    {
        if (!static::checkSourceValue($src, $key)) {
            return null;
        }
        $arr = filter_var($src[$key], FILTER_VALIDATE_FLOAT, FILTER_FORCE_ARRAY);
        if (!is_array($arr)) {
            return null;
        }
        return array_values(array_filter($arr));
    }

    /**
     * Searches POST and GET data in that order, for a property corresponding to
     * $key.
     * @param string $key Key of the variable value to collect.
     * @param int $filter Filter token corresponding to the 3rd parameter of PHP's built-in filter_input() routine.
     * @param array|null $src Optional array to use in place of POST or GET data.
     * @return string|null Value found for the requested key. Returns an empty string
     * if none of the collections contain the requested key.
     */
    public static function collectRequestVar(
        string $key,
        int    $filter = RequestValidation::DEFAULT_REQUEST_FILTER,
        ?array $src = null
    ): ?string
    {
        if (!static::checkSourceValue($src, $key)) {
            return null;
        }
        return trim(filter_var($src[$key], $filter));
    }

    /**
     * Converts script argument (query string or form data) to an array of numeric values.
     * @param string $key Key containing potential numeric values.
     * @param array|null $src Optional array of variables to use instead of GET or POST data.
     * @return array|null Returns an array if values are found for the specified key. Null otherwise.
     */
    public static function collectStringArrayRequestVar(
        string $key,
        ?array $src = null,
        int    $filter = RequestValidation::DEFAULT_REQUEST_FILTER
    ): ?array
    {
        if (!static::checkSourceValue($src, $key)) {
            return null;
        }
        $values = filter_var($src[$key], $filter, FILTER_FORCE_ARRAY);
        if (!is_array($values)) {
            return null;
        }
        return array_filter($values);
    }

    /**
     * Searches POST, GET and session data, in that order, for a property corresponding to $key.
     * @param string $key Key of the variable value to collect.
     * @param int $filter Filter token corresponding to the 3rd parameter of PHP's built-in filter_input() routine.
     * @param int|null $index Index of the input if it is part of an array.
     * @param array|null $src Optional array of variables to use instead of POST or GET data.
     * @return string|null Value found for the requested key. Returns an empty string
     * if none of the collections contain the requested key.
     */
    public static function collectStringRequestVar(
        string $key,
        int    $filter = RequestValidation::DEFAULT_REQUEST_FILTER,
        ?int   $index = null,
        ?array $src = null
    ): ?string
    {
        $value = Validation::_parseInput($filter, $key, $index, $src);
        if (!$value && isset($_SESSION[$key]) && strlen(trim($_SESSION[$key])) > 0) {
            $value = trim($_SESSION[$key]);
        }
        return ($value);
    }

    /**
     * Tests POST data for the current requested action. Returns a token indicating
     * the action that can be used in place of testing POST data directly on
     * a page.
     * @return string
     */
    public static function getPageAction(): string
    {
        $action = trim(filter_input(INPUT_POST, LittledGlobals::COMMIT_KEY, RequestValidation::DEFAULT_REQUEST_FILTER));
        if (strlen($action) > 0) {
            $action = LittledGlobals::COMMIT_KEY;
        } else {
            $action = trim(filter_input(INPUT_POST, LittledGlobals::CANCEL_KEY, RequestValidation::DEFAULT_REQUEST_FILTER));
            if (strlen($action) > 0) {
                $action = LittledGlobals::CANCEL_KEY;
            }
        }
        return $action;
    }
}