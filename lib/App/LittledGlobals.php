<?php
namespace Littled\App;


use Littled\Exception\ConfigurationUndefinedException;

class LittledGlobals
{
	/** @var string App domain name. */
	protected static string $app_domain = '';
	/** @var string Root URI for CMS pages. */
	protected static string $cms_root_uri = '';
	/** @var string Path to directory containing mysql authentication (outside public access). */
	protected static string $mysql_keys_path = '';
	/** @var string Path to directory containing app templates. */
	protected static string $local_template_path = '';
    /** @var string Path to directory containing app templates. */
    protected static string $shared_template_path = '';

	/** @var string Name of session variable use dto store CSRF tokens. */
	const CSRF_SESSION_KEY = 'csrfToken';
    /** @var string Name of request header transmitting csrf token. */
    const CSRF_HEADER_KEY = 'CSRF_TOKEN';

    /** @var string Request variable name to cancel operations. */
    const CANCEL_KEY = 'cancel';
    /** @var string Request variable name to commit operations. */
    const COMMIT_KEY = 'commit';
	/** @var string Key of the content type id request variable. */
	const CONTENT_TYPE_KEY = 'tid';
	/** @var string Cookie variable containing value of flag indicating the user's consent to collecting cookie data  */
	const COOKIE_CONSENT_KEY = 'hasCookieConsent';
	/** @var string Key of request variable used to pass CSRF tokens. */
	const CSRF_TOKEN_KEY = 'csrf';
	/** @var string Key of request variable used to pass error messages. */
	const ERROR_MSG_KEY = 'err';
    /** @var string Request variable flag indicating that listings are being filtered. */
    const FILTER_KEY = 'filter';
    /** @var string Key of the record id request variable. */
    const ID_KEY = 'id';
    /** @var string Request variable containing status message. */
    const INFO_MESSAGE_KEY = 'msg';
    /** @var string Key of the parent id request variable. */
    const PARENT_ID_KEY = 'pid';
	/** @var string Request variable name containing referring URLs. */
	const REFERER_KEY = 'ref';
    /** @var string */
    const OPERATION_KEY = 'op';

	/**
	 * Gets app domain name.
	 * @return string App domain name.
	 */
	public static function getAppDomain(): string
	{
		return static::$app_domain;
	}

	/**
	 * Gets path to current CMS root URI.
	 * @return string CMS root URI.
	 */
	public static function getCMSRootURI(): string
	{
		return static::$cms_root_uri;
	}

	/**
	 * Returns current template root path.
	 * @return string Template root path.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getLocalTemplatesPath(): string
	{
		if (''===static::$local_template_path) {
			throw new ConfigurationUndefinedException('LittledGlobals local template path value not set.');
		}
		return static::$local_template_path;
	}

	/**
	 * Gets path to current MySQL authentication directory.
	 * @return string MySQL keys path.
	 */
	public static function getMySQLKeysPath(): string
	{
		return static::$mysql_keys_path;
	}

	/**
	 * Returns current template root path.
	 * @return string Template root path.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getSharedTemplatesPath(): string
	{
		if (''===static::$shared_template_path) {
			throw new ConfigurationUndefinedException('LittledGlobals shared template path value not set.');
		}
		return static::$shared_template_path;
	}

	/**
	 * Sets the domain name for the app.
	 * @param string $domain App domain name.
	 */
	public static function setAppDomain(string $domain='')
	{
		static::$app_domain = $domain;
	}

	/**
	 * Sets path to current CMS URI root.
	 * @param string $uri CMS URI root.
	 */
	public static function setCMSRootURI(string $uri)
	{
		static::$cms_root_uri = (($uri) ? (rtrim($uri, '/').'/') : (''));
	}

	/**
	 * Sets root template directory path.
	 * @param string $path Path to root directory containing template files.
	 */
	public static function setLocalTemplatesPath(string $path)
	{
		static::$local_template_path = (($path) ? (rtrim($path, '/').'/') : (''));
	}

	/**
	 * Sets path to current MySQL authentication directory.
	 * @param string $path MySQL keys path.
	 */
	public static function setMySQLKeysPath(string $path)
	{
		static::$mysql_keys_path = (($path) ? (rtrim($path, '/').'/') : (''));
	}

	/**
	 * Sets root template directory path.
	 * @param string $path Path to root directory containing template files.
	 */
	public static function setSharedTemplatesPath(string $path)
	{
		static::$shared_template_path = (($path) ? (rtrim($path, '/').'/') : (''));
	}
}