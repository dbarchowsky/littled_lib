<?php

namespace Littled\Validation;

use DateTime;
use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidRequestException;
use Littled\Exception\InvalidValueException;
use stdClass;


/**
 * Assorted static validation routines.
 */
class Validation
{
    public const DEFAULT_REQUEST_FILTER = FILTER_UNSAFE_RAW;
    protected static string $geo_lookup_api_address = 'http' . '://www.geoplugin.net/json.gp?ip=';
    /** @var string[] $eu_countries */
    protected static array $eu_countries = ["AT", "BE", "BG", "HR", "CY", "CZ", "DK", "EE", "FI", "FR", "DE", "GR",
        "HU", "IE", "IT", "LV", "LT", "LU", "MT", "NL", "PL", "PT", "RO", "SK", "SI", "ES", "SE"];

    /**
     * Retrieves any valid integer values passed as request parameters.
     * @param int $input_type Token representing input type, e.g. INPUT_GET or INPUT_POST
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
                    return (array($value));
                }
            }
        }
        return null;
    }

    /**
     * Returns request variable as explicit integer value, or null if the request variable is not set or does not
     * represent a float value.
     * @param int $filter Filter to apply to the variable value, e.g. FILTER_VALIDATE_INT or FILTER_VALIDATE_FLOAT
     * @param string $key Key in the collection storing the value to look up.
     * @param int|null $index Index of the array to look up, if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g. $_GET or $_POST
     * @return string|bool|null
     */
    protected static function _parseInput(int $filter, string $key, ?int $index = null, ?array $src = null)
    {
        if ($src === null) {
            $src = static::getDefaultInputSource();
        }
        if (!array_key_exists($key, $src)) {
            return null;
        }
        if ($index !== null) {
            $arr = filter_var($src[$key], $filter, FILTER_REQUIRE_ARRAY);
            if (is_array($arr) && count($arr) >= ($index - 1)) {
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
     * Checks if the user has provided consent to store cookie data. Returns result as TRUE/FALSE.
     * @return bool Flag indicating that prior consent was found.
     */
    public static function checkForCookieConsent(): bool
    {
        if ((isset($_COOKIE) &&
                !empty($_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY])) ||
            (isset($_SESSION) &&
                array_key_exists(LittledGlobals::COOKIE_CONSENT_KEY, $_SESSION) &&
                $_SESSION[LittledGlobals::COOKIE_CONSENT_KEY] === true)
        ) {
            /** Cookie key can only be set with user's consent. */
            return true;
        }
        return false;
    }

    /**
     * Get default client request input source and check it for an existing value. Returns FALSE if no existing value
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
     * @param string $key Input variable name in either GET  or POST data.
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
            $arr = filter_var($src[$key], Validation::DEFAULT_REQUEST_FILTER, FILTER_REQUIRE_ARRAY);
            if (is_array($arr) && count($arr) >= ($index - 1)) {
                $value = $arr[$index];
            }
        } else {
            $value = $src[$key] === false ? false : trim(filter_var($src[$key], Validation::DEFAULT_REQUEST_FILTER));
        }

        return Validation::parseBoolean($value);
    }

    /**
     * Converts script argument (query string or form data) to array of numeric values.
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
     * @param int|null $index Index of the array to look up, if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g. $_GET or $_POST
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
     * @param int|null $index Index of the array to look up, if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g. $_GET or $_POST
     * @return float|int
     */
    public static function collectNumericRequestVar(string $key, ?int $index = null, ?array $src = null)
    {
        $value = Validation::_parseInput(FILTER_VALIDATE_FLOAT, $key, $index, $src);
        return Validation::parseNumeric($value);
    }

    /**
     * Converts script argument (query string or form data) to array of numeric values.
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
     * @return mixed Value found for the requested key. Returns an empty string
     * if none of the collections contain the requested key.
     */
    public static function collectRequestVar(
        string $key,
        int    $filter = Validation::DEFAULT_REQUEST_FILTER,
        ?array $src = null
    ): ?string
    {
        if (!static::checkSourceValue($src, $key)) {
            return null;
        }
        return trim(filter_var($src[$key], $filter));
    }

    /**
     * @deprecated Use Validation::collectStringRequestVar() instead.
     */
    public static function collectStringInput(
        string $key,
        int    $filter = Validation::DEFAULT_REQUEST_FILTER,
        ?int   $index = null,
        ?array $src = null
    ): ?string
    {
        return Validation::collectStringRequestVar($key, $filter, $index, $src);
    }

    /**
     * Converts script argument (query string or form data) to array of numeric values.
     * @param string $key Key containing potential numeric values.
     * @param array|null $src Optional array of variables to use instead of GET or POST data.
     * @return array|null Returns an array if values are found for the specified key. Null otherwise.
     */
    public static function collectStringArrayRequestVar(
        string $key,
        ?array $src = null,
        int    $filter = Validation::DEFAULT_REQUEST_FILTER
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
     * @return string Value found for the requested key. Returns an empty string
     * if none of the collections contain the requested key.
     */
    public static function collectStringRequestVar(
        string $key,
        int    $filter = Validation::DEFAULT_REQUEST_FILTER,
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
     * Get IP address of website visitor for the purposes of inspecting their location
     * @return string IP address
     */
    protected static function getClientIP(): string
    {
        if (array_key_exists('HTTP_CLIENT_ID', $_SERVER) &&
            filter_var($_SERVER['HTTP_CLIENT_ID'], FILTER_VALIDATE_IP)) {
            return filter_var($_SERVER['HTTP_CLIENT_ID'], FILTER_VALIDATE_IP);
        }
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) &&
            filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            return filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP);
        }
        if (array_key_exists('REMOTE_ADDR', $_SERVER) &&
            filter_var(@$_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }

    /**
     * Get location properties of client IP.
     * @param string $ip (Optional) IP address to inspect.
     * @return array Location data
     * @throws InvalidValueException
     */
    public static function getClientLocation(string $ip = ''): array
    {
        // Validate client IP
        if (!$ip || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = Validation::getClientIP();
        }
        if (!$ip) {
            throw new InvalidValueException("Could not determine client IP.");
        }

        // API that will return IPs location properties.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, static::$geo_lookup_api_address . $ip);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch); // string
        curl_close($ch);

        // lookup country in API response
        $ip_data = json_decode($response, true);
        return str_replace('&quot;', '"', $ip_data);
    }

    /**
     * Gets default input source. POST or REQUEST data if present, or API client data.
     * @param array $ignore_keys Optional array of keys to ignore in GET or POST data
     * @return array
     */
    protected static function getDefaultInputSource(array $ignore_keys = []): array
    {
        // first return either REQUEST or POST data collections
        $src = array_merge($_GET, $_POST);
        foreach ($ignore_keys as $key) {
            unset($src[$key]);
        }
        if (count($src) > 0) {
            return $src;
        }
        // fall back to API request client data
        return AppBase::getAjaxRequestData() ?: [];
    }

    /**
     * Tests POST data for the current requested action. Returns a token indicating
     * the action that can be used in place of testing POST data directly on
     * a page.
     * @return string
     */
    public static function getPageAction(): string
    {
        $action = trim(filter_input(INPUT_POST, LittledGlobals::COMMIT_KEY, Validation::DEFAULT_REQUEST_FILTER));
        if (strlen($action) > 0) {
            $action = LittledGlobals::COMMIT_KEY;
        } else {
            $action = trim(filter_input(INPUT_POST, LittledGlobals::CANCEL_KEY, Validation::DEFAULT_REQUEST_FILTER));
            if (strlen($action) > 0) {
                $action = LittledGlobals::CANCEL_KEY;
            }
        }
        return $action;
    }

    /**
     * Tests if client is located in the European Union based on their IP.
     * @param string $ip (Optional) explicit IP value to test.
     * @return bool True if the client request is determined to be originating in the EU.
     * @throws InvalidValueException
     * @throws InvalidRequestException
     */
    public static function isEUClient(string $ip = ''): bool
    {
        $data = Validation::getClientLocation($ip);
        $cc = '';
        if ($data && !empty($data['geoplugin_countryCode'])) {
            $cc = $data['geoplugin_countryCode'];
        }
        if (!$cc) {
            throw new InvalidRequestException("Could not determine client location.");
        }
        return (in_array($cc, static::$eu_countries));
    }

    /**
     * Tests if string value represents an integer value.
     * @param mixed $value Value to test.
     * @return bool
     */
    public static function isInteger($value): bool
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
     * Tests if a variable is a string of more than 0 characters.
     * @param mixed $var Variable to test
     * @return bool TRUE if the variable holds a string value of more than 0 characters.
     */
    public static function isStringWithContent($var): bool
    {
        return (
            is_string($var) &&
            strlen($var) > 0
        );
    }

    /**
     * Tests if $sub is a subclass of $base. Both parameters can the qualified names of the classes as strings.
     * @param $sub
     * @param string $base
     * @return bool
     */
    public static function isSubclass($sub, string $base): bool
    {
        return (
            (is_object($sub) && get_class($sub) == $base) ||
            ($sub == $base) ||
            (is_subclass_of($sub, $base)));
    }

    /**
     * Tests value and returns TRUE if it evaluates to some string that equates with a "true" flag.
     * Returns FALSE only if the value evaluates to some string that equates with a "false" flag.
     * Returns NULL if the value doesn't make sense in a TRUE/FALSE context.
     * @param mixed $value Value to test.
     * @return ?bool TRUE, FALSE, or NULL
     */
    public static function parseBoolean($value): ?bool
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
     * @deprecated Use Validation::collectBooleanRequestVar() instead.
     * Returns TRUE/FALSE depending on the value of the requested input variable.
     * @param string $key Input variable name in either GET  or POST data.
     * @param int|null $index (Optional) index of the element to test, if the variable is an array.
     * @param array|null $src (Optional) array to use in place of GET or POST data.
     * @return bool|null TRUE/FALSE depending on the value of the input variable.
     */
    public static function parseBooleanInput(string $key, ?int $index = null, ?array $src = null): ?bool
    {
        return Validation::collectBooleanRequestVar($key, $index, $src);
    }

    /**
     * @deprecated Use Validation::collectNumericRequestVar() instead.
     * Returns request variable as explicit float value, or null if the request variable is not set or does not
     * represent a float value.
     * @param string $key Key in the collection storing the value to look up.
     * @param int|null $index Index of the array to look up, if the variable's value is an array.
     * @param array|null $src Array to search for $key, e.g. $_GET or $_POST
     * @return float|null
     */
    public static function parseFloatInput(string $key, ?int $index = null, ?array $src = null): ?float
    {
        return Validation::collectNumericRequestVar($key, $index, $src);
    }

    /**
     * Tests a variable and returns its equivalent explicit integer value, or null if the variable value doesn't
     * represent an integer value.
     * @param mixed $value Value to test.
     * @return int|null Value explicitly converted to an integer value, or null if the value does not represent an
     * integer value.
     */
    public static function parseInteger($value): ?int
    {
        if (is_numeric($value)) {
            return ((int)round($value));
        }
        return null;
    }

    /**
     * @param string $key
     * @param int|null $index
     * @param array|null $src
     * @return int|null
     * @deprecated Use collectIntegerRequestVar() instead.
     */
    public static function parseIntegerInput(string $key, ?int $index = null, ?array $src = null): ?int
    {
        return Validation::collectIntegerRequestVar($key, $index, $src);
    }

    /**
     * Converts a given string value to a numeric equivalent.
     * @param mixed $value Value to parse.
     * @return float|int
     */
    public static function parseNumeric($value)
    {
        if (true === $value || false === $value) {
            return null;
        }
        if (is_numeric($value)) {
            if (strpos($value, ".") !== false) {
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
     * Deprecated. Use collectNumericArrayRequestVar() instead.
     * @param string $key
     * @return array|null
     * @deprecated
     */
    public static function parseNumericArrayInput(string $key): ?array
    {
        return (Validation::collectNumericArrayRequestVar($key));
    }

    /**
     * @deprecated Use Validation::collectNumericRequestVar() instead
     * - Searches GET and POST data for the variable value matching $key.
     * - Validates the value & returns an integer value only if the input value is numeric.
     * @param string $key Name of the input parameter holding the value of interest.
     * @param int|null $index (Optional) index within input array of the value of interest.
     * @param array|null $src (Optional) collection from which the value will be extracted. Post and Get data will be used
     * if this parameter is not supplied.
     * @return float|null
     */
    public static function parseNumericInput(string $key, ?int $index = null, ?array $src = null): ?float
    {
        return Validation::collectNumericRequestVar($key, $index, $src);
    }

    /**
     * Separates a route path into its parts based on backslash delimiters.
     * @param string $route
     * @return array
     */
    public static function parseRouteParts(string $route): array
    {
        /**
         * array_values() - reindex the array
         * array_filters() - strip empty strings from the result
         */
        return array_map(function ($e) {
            return ((Validation::isInteger($e)) ? (Validation::parseInteger($e)) : ($e));
        },
            array_values(array_filter(preg_split('/\//', $route))));
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
     * Tests a CSRF token stored in a string variable against the CSRF token currently stored in Session data.
     * @param string $csrf CSRF token value to test.
     * @return bool TRUE if the CSRF token matches the token stored in session data.
     */
    protected static function testCSRFValue(string $csrf): bool
    {
        if ($csrf === '') {
            return false;
        }
        $csrf = trim(filter_var($csrf, Validation::DEFAULT_REQUEST_FILTER));
        return ($csrf === $_SESSION[LittledGlobals::CSRF_SESSION_KEY]);
    }

    /**
     * Check for valid CSRF token.
     * @param stdClass|null $data Optional object that will contain the CSRF token. POST data is used by default if this
     * parameter is not supplied.
     * @return bool TRUE if the CSRF token is valid, FALSE otherwise.
     */
    public static function validateCSRF(?stdClass $data = null): bool
    {
        // Session must contain the master token value
        if (!isset($_SESSION) || !array_key_exists(LittledGlobals::CSRF_SESSION_KEY, $_SESSION) ||
            $_SESSION[LittledGlobals::CSRF_SESSION_KEY] === '') {
            return false;
        }

        if ($data) {
            // Collect from local data if it exists. Local data has precedence over any other source.
            if (!property_exists($data, LittledGlobals::CSRF_TOKEN_KEY)) {
                return false;
            }
            return Validation::testCSRFValue($data->{LittledGlobals::CSRF_TOKEN_KEY});
        }
        $csrf = '';
        $header_key = 'HTTP_' . LittledGlobals::CSRF_HEADER_KEY;
        if (array_key_exists($header_key, $_SERVER)) {
            // Test any tokens detected in request header data
            // Continue to search other locations if no token is stored in request headers.
            $csrf = $_SERVER[$header_key];
        }
        return Validation::testCSRFValue($csrf);
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
            $formats = array(
                'Y-m-d',
                'm/d/y',
                'm/d/Y',
                'n/j/y',
                'n/j/Y',
                'F d, Y',
                'F j, Y',
                'M d, Y',
                'M j, Y'
            );
        } elseif (!is_array($formats)) {
            $formats = array($formats);
        }

        foreach ($formats as $format) {
            $d = Validation::_testDateFormat($date, $format);
            if ($d instanceof DateTime) {
                return ($d);
            }
        }
        throw new ContentValidationException("Unrecognized date value.");
    }

    /**
     * Validates email address.
     * @param string $email Email address to validate
     * @return bool True if the email is in a valid format
     */
    public static function validateEmailAddress(string $email): bool
    {
        return (preg_match("/\S+@\S+\.\S+/", $email) && (!preg_match("/,\/;/", $email)));
    }
}
