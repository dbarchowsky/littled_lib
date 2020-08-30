<?php
namespace Littled\App;


class LittledGlobals
{
	/** @var string App domain name. */
	protected static $appDomain = '';
	/** @var string Root URI for CMS pages. */
	protected static $cmsRootURI = '';
	/** @var string Path to directory containing mysql authentication (outside of public access). */
	protected static $mysqlKeysPath = '';
	/** @var string Path to directory containing app templates. */
	protected static $templatePath;

	/** @var string Name of session variable use dto store CSRF tokens. */
	const CSRF_SESSION_KEY = 'csrfToken';

	/** @var string Name of request variable used to pass content type. */
	const CONTENT_TYPE_PARAM = 'tid';
	/** @var string Name of request variable used to pass CSRF tokens. */
	const CSRF_TOKEN_PARAM = 'csrf';
	/** @var string ID request variable name. */
	const ID_PARAM = 'id';

	/** @var string Request variable name holding record ids. */
	const P_ID = 'id';
	/** @var string Request variable name to cancel operations. */
	const P_CANCEL = 'cancel';
	/** @var string Request variable name to commit operations. */
	const P_COMMIT = 'commit';
	/** @var string Request variable flag indicating that listings are being filtered. */
	const P_FILTER = 'filter';
	/** @var string Request variable containing status message. */
	const P_MESSAGE = 'msg';
	/** @var string Request variable name containing referring URLs. */
	const P_REFERER = 'ref';

	/**
	 * Gets app domain name.
	 * @return string App domain name.
	 */
	public static function getAppDomain()
	{
		return (static::$appDomain);
	}

	/**
	 * Gets path to current CMS root URI.
	 * @return string CMS root URI.
	 */
	public static function getCMSRootURI()
	{
		return (static::$cmsRootURI);
	}

	/**
	 * Gets path to current MySQL authentication directory.
	 * @return string MySQL keys path.
	 */
	public static function getMySQLKeysPath()
	{
		return (static::$mysqlKeysPath);
	}

	/**
	 * Returns current template root path.
	 * @return string Template root path.
	 */
	public static function getTemplatePath()
	{
		return (static::$templatePath);
	}

	/**
	 * Sets the domain name for the app.
	 * @param string $domain App domain name.
	 */
	public static function setAppDomain($domain)
	{
		static::$appDomain = (($domain) ? ($domain) : (''));
	}

	/**
	 * Sets path to current CMS URI root.
	 * @param string $uri CMS URI root.
	 */
	public static function setCMSRootURI($uri)
	{
		static::$cmsRootURI = (($uri) ? (rtrim($uri, '/').'/') : (''));
	}

	/**
	 * Sets path to current MySQL authentication directory.
	 * @param string $path MySQL keys path.
	 */
	public static function setMySQLKeysPath($path)
	{
		static::$mysqlKeysPath = (($path) ? (rtrim($path, '/').'/') : (''));
	}

	/**
	 * Sets root template directory path.
	 * @param string $path Path to root directory containing template files.
	 */
	public static function setTemplatePath($path)
	{
		static::$templatePath = (($path) ? (rtrim($path, '/').'/') : (''));
	}
}