<?php

namespace Littled\App;

use Exception;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

/**
 * Base class of a web app containing global utility functions for the app.
 */
class AppBase
{
    /** @var string */
    protected static string $error_key = 'err';
    /** @var string */
    protected static string $error_page_url = '/error.php';
    /** @var string Default stream for AJAX request data */
    protected static string $ajax_input_stream = 'php://input';

    /**
     * Class constructor
     */
    function __construct()
    {
        /* nothing here for now. put logic in child classes. */
    }

    /**
     * Assigns client ajax request data values to object properties.
     * @param ?object $data
     */
    public function collectAjaxClientRequestData(?object $data = null)
    {
        if (is_null($data)) {
            $data = (object)static::getAjaxRequestData();
        }
        foreach ($this as $item) {
            if (is_object($item) && method_exists($item, 'collectAjaxRequestData')) {
                /** @var RequestInput $item */
                $item->collectAjaxRequestData($data);
            }
            elseif(Validation::isSubclass($item, SerializedContent::class)) {
                /** @var SerializedContent $item */
                $item->collectAjaxClientRequestData($data);
            }
        }
    }

    /**
     * Generate string to use as CSRF token.
     * @return string CSRF token value.
     */
    public static function generateCSRFToken(): string
    {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }

    /**
     * Generates unique strings to use as identifier tokens.
     * @param int $length Number of characters in the token.
     * @return string
     * @throws Exception
     */
    public static function generateUniqueToken(int $length): string
    {
        $bytes = random_bytes(ceil($length / 2));
        return (substr(bin2hex($bytes), 0, $length));
    }

    /**
     * Returns the stream currently being used as the source of ajax client request data.
     * @return string
     */
    protected static function getAjaxInputStream(): string
    {
        return static::$ajax_input_stream;
    }

    /**
     * Returns an array containing client request data sent as AJAX request in request headers.
     * @return array Returns an array containing client request data or null if no data is available in the ajax request headers.
     */
    public static function getAjaxRequestData(): ?array
    {
        $data = (array)json_decode(file_get_contents(static::$ajax_input_stream));
        return ((count($data) > 0) ? ($data) : (null));
    }

    /**
     * Returns the path to the app's document root.
     * @return string Path to document root. Or empty string if the path is unavailable.
     */
    public static function getAppRootDir(): string
    {
        if ($_SERVER['DOCUMENT_ROOT']) {
            return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/';
        }
        return ('');
    }

    /**
     * Returns the CSRF token for this session.
     * @param bool $ignore_consent Optional flag allowing calling function to ignore any preferences found for respecting cookie consent.
     * @return string|null
     */
    public static function getCSRFToken(bool $ignore_consent = false): ?string
    {
        // make sure to enable http only cookies on the site so as not to run afoul of cookie consent
        if (!isset($_SESSION[LittledGlobals::CSRF_SESSION_KEY])) {
            AppBase::storeCSRFToken();
        }
        return $_SESSION[LittledGlobals::CSRF_SESSION_KEY];
    }

    /**
     * Error key getter.
     * @return string
     */
    public static function getErrorKey(): string
    {
        return static::$error_key;
    }

    /**
     * Error page url getter.
     * @return string
     */
    public static function getErrorPageURL(): string
    {
        return static::$error_page_url;
    }

    /**
     * Gets client request data from either POST variables, GET variables, or AJAX client request data.
     * @param array|null $src (Optional) Specify either $_POST or $_GET with this parameter to exclude the other from
     * the data that can be returned.
     * @return array
     */
    public static function getRequestData(?array $src = null): array
    {
        if ($src === null) {
            $src = array_merge($_POST, $_GET);
        }
        if (count($src) < 1) {
            $src = self::getAjaxRequestData() ?: [];
        }
        return $src;
    }

    /**
     * Redirect to the site's error page with error to display on the page.
     * @param string $error_msg Error message to inject into the error page template.
     * @param string $url (Optional) URL of the global site error page.
     * @param string $key (Optional) key used to store and retrieve error message.
     */
    public static function redirectToErrorPage(string $error_msg, string $url = '', string $key = '')
    {
        $url = $url ?: static::getErrorPageURL();
        $key = $key ?: static::getErrorKey();
        header("Location: $url?$key=" . urlencode($error_msg));
    }

    /**
     * API input stream setter
     * @param string $input_stream
     * @return void
     */
    public static function setAjaxInputStream(string $input_stream)
    {
        static::$ajax_input_stream = $input_stream;
    }

    /**
     * Error key setter.
     * @param string $key
     * @return void
     */
    public static function setErrorKey(string $key)
    {
        static::$error_key = $key;
    }

    /**
     * Error page url setter.
     * @param string $url
     * @return void
     */
    public static function setErrorPageURL(string $url)
    {
        static::$error_page_url = $url;
    }

    /**
     * Starts session for app if one does not already exist.
     * Tests client request headers to determine if the client request originates in the EU.
     * Sets cookie consent values depending on whether the client request originated in the EU or not.
     * @return void
     */
    public static function startSessionTestingForEU()
    {
        if (Validation::checkForCookieConsent() === true) {
            if (session_id() == "") {
                session_start();
            }
        } else {
            try {
                if (Validation::isEUClient() === false) {
                    if (session_id() == '') {
                        session_start();
                    }
                    setcookie(LittledGlobals::COOKIE_CONSENT_KEY, '1', time() + (60 * 60 * 24 * 90));
                    $_SESSION[LittledGlobals::COOKIE_CONSENT_KEY] = true;
                }
            } catch (Exception $e) {
                /** ignore errors but don't set cookie consent */
            }
        }
    }

    /**
     * Generate CSRF token and store it in a session variable.
     * A token is not generated if one already exists for the session.
     * @return void
     */
    public static function storeCSRFToken()
    {
        if (!isset($_SESSION[LittledGlobals::CSRF_SESSION_KEY])) {
            $_SESSION[LittledGlobals::CSRF_SESSION_KEY] = AppBase::generateCSRFToken();
        }
        // $_SESSION[CSRF_AJAX_PARAM] = '12345abcde';
    }
}