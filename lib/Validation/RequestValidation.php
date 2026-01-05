<?php
namespace Littled\Validation;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidRequestException;
use Littled\Exception\InvalidValueException;


class RequestValidation extends StringValidation
{
    public const DEFAULT_REQUEST_FILTER = FILTER_UNSAFE_RAW;
    /** @var string[] $eu_countries */
    protected static array $eu_countries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
        'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'unknown'];
    protected static string $geo_lookup_api_address =  'https://api.country.is/'; // 'http://ip-api.com/json/';
    protected static string $country_code_key = 'country';

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
            /** Cookie key can only be set with the user's consent. */
            return true;
        }
        return false;
    }

    /**
     * Get the IP address of a website visitor to inspect their location
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
     * @throws InvalidRequestException
     */
    public static function getClientLocation(string $ip = ''): array
    {
        // Validate client IP
        if (!$ip || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = Validation::getClientIP();
        }
        if (!$ip) {
            throw new InvalidValueException('Could not determine client IP.');
        }

        // API that will return IPs location properties.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, static::$geo_lookup_api_address . $ip);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch); // string
        curl_close($ch);

        // look up country in API response
        $ip_data = json_decode($response, true);
        return self::processGeoLookupResponse($ip_data);
    }

    /**
     * Gets a default input source. POST or REQUEST data if present, or API client data.
     * @param array $ignore_keys Optional array of keys to ignore in GET or POST data
     * @return array
     */
    public static function getDefaultInputSource(array $ignore_keys = []): array
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
        return AppBase::getAjaxRequestData() ?? [];
    }

    /**
     * Tests if a client is located in the European Union based on their IP.
     * @param string $ip (Optional) explicit IP value to test.
     * @return bool True if the client request is determined to be originating in the EU.
     * @throws InvalidValueException
     * @throws InvalidRequestException
     */
    public static function isEUClient(string $ip = ''): bool
    {
        $data = Validation::getClientLocation($ip);
        $cc = '';
        if ($data && !empty($data[static::$country_code_key])) {
            $cc = $data[static::$country_code_key];
        }
        if (!$cc) {
            throw new InvalidRequestException('Could not determine client location.');
        }
        return (in_array($cc, static::$eu_countries));
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
     * Process response from geographical location lookup provider to ensure it's in a workable format.
     * @param array|null $response
     * @return array
     * @throws InvalidRequestException
     */
    protected static function processGeoLookupResponse(array|null $response): array
    {
        $error_keys = ['error'];
        if ($response === null) {
            $response = [
                static::$country_code_key => 'unknown'
            ];
        }
        if (!array_key_exists(static::$country_code_key, $response)) {
            $response[static::$country_code_key] = 'unknown';
        }
        if (count(array_intersect_key(array_flip($error_keys), $response)) > 0) {
            throw new InvalidRequestException('Geo lookup response contains errors.');
        }
        return $response;
    }

    /**
     * Override the default geographical lookup api address.
     * @param string $api_url
     * @return void
     */
    public static function setGeoLookupProvider(string $api_url): void
    {
        static::$geo_lookup_api_address = $api_url;
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
        $csrf = trim(filter_var($csrf, self::DEFAULT_REQUEST_FILTER));
        return ($csrf === $_SESSION[LittledGlobals::CSRF_SESSION_KEY]);
    }

    /**
     * Check for valid CSRF token.
     * @param object|null $data Optional object that will contain the CSRF token. POST-data is used by default if this
     * parameter is not supplied.
     * @return bool TRUE if the CSRF token is valid, FALSE otherwise.
     */
    public static function validateCSRF(?object $data = null): bool
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
}