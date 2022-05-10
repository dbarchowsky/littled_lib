<?php
namespace Littled\App;

use Littled\Request\RequestInput;
use Exception;

/**
 * Base class of a web app containing global utility functions for the app.
 */
class AppBase
{
    /** @var string */
    protected static $error_key = 'err';
    /** @var string */
    protected static $error_page_url = '/error.php';

    /**
     * Class constructor
     */
	function __construct()
	{
        /* nothing here for now. put logic in child classes. */
	}

    /**
     * Assigns JSON request data values to object properties.
     * @param ?object $data
     */
    public function collectJsonRequestData(?object $data=null)
    {
        if (is_null($data)) {
            $json = file_get_contents('php://input');
            $data = json_decode($json);
        }
        foreach($this as $item) {
            if (is_object($item) && method_exists($item, 'collectJsonRequestData')) {
                /** @var RequestInput $item */
                $item->collectJsonRequestData($data);
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
        $bytes = random_bytes(ceil($length/2));
        return (substr(bin2hex($bytes), 0, $length));
    }

    /**
     * Returns the path to the app's document root.
     * @return string Path to document root. Or empty string if the path is unavailable.
     */
    public static function getAppRootDir() : string
    {
        if ($_SERVER['DOCUMENT_ROOT']) {
            return rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/';
        }
        return ('');
    }

    /**
	 * Returns the CSRF token for this session.
	 * @return string|null
	 */
	public static function getCSRFToken(): ?string
	{
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
	 * Redirect to the site's error page with error to display on the page.
	 * @param string $error_msg Error message to inject into the error page template.
	 * @param string $url (Optional) URL of the global site error page.
	 * @param string $key (Optional) key used to store and retrieve error message.
	 */
	public static function redirectToErrorPage(string $error_msg, string $url='', string $key='')
	{
        $url = $url ?: AppBase::getErrorPageURL();
        $key = $key ?: AppBase::getErrorKey();
		header("Location: $url?$key=".urlencode($error_msg));
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

    /**
	 * @deprecated Use /Littled/Log/Debug::output() instead.
	 * Print debug message including information about the location of the call to debugmsg() and the type of the object being worked on.
	 * @param string $error_msg Message to print.
	 */
	public function debugmsg( string $error_msg )
	{
		if (defined('SUPPRESS_DEBUG') && SUPPRESS_DEBUG==true) {
			return;
		}
		print "<div class=\"debug\"><span class=\"formerror\">*** DEBUG *** </span>";
		$arDbg = debug_backtrace();
		print ("<span class=\"dimtext\">[".$arDbg[1]['class']."::".$arDbg[1]['function']."() line ".$arDbg[0]["line"]."] [".get_class($this)."]</span> ");
		print htmlentities($error_msg, ENT_QUOTES)."</div>\n";
	}
}