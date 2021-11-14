<?php
namespace Littled\App;

class AppBase
{
	function __construct()
	{

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
	 * Generate CSRF token and store it in a session variable.
	 * A token is not generated if one already exists for the session.
	 */
	public static function generateCSRFToken()
	{
		if (!isset($_SESSION[LittledGlobals::CSRF_SESSION_KEY])) {
			$_SESSION[LittledGlobals::CSRF_SESSION_KEY] = base64_encode(openssl_random_pseudo_bytes(32));
		}
		// $_SESSION[CSRF_AJAX_PARAM] = '12345abcde';
	}

	/**
	 * Returns the CSRF token for this session.
	 * @return string|null
	 */
	public static function getCSRFToken(): ?string
	{
		if (!isset($_SESSION[LittledGlobals::CSRF_SESSION_KEY])) {
			AppBase::generateCSRFToken();
		}
		return $_SESSION[LittledGlobals::CSRF_SESSION_KEY];
	}

	/**
	 * Redirect to the site's error page with error to display on the page.
	 * @param string $error_msg
	 * @param string $url
	 * @param string $key
	 */
	public static function redirectToErrorPage(string $error_msg, string $url, string $key)
	{
		header("Location: $url?$key='".urlencode($error_msg));
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