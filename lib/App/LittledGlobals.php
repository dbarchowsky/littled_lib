<?php

namespace Littled\App;


use Littled\Exception\ConfigurationUndefinedException;

abstract class LittledGlobals
{
    protected static string|null    $app_base_dir;
    protected static string         $app_domain;
    protected static string|null    $config_path;
    protected static string|null    $error_log;
    protected static string         $mysql_keys_path;
    protected static string|null    $local_template_path;
    protected static string|null    $shared_template_path;
    protected static bool           $show_verbose_errors = false;

    /** @var string                 Name of session variable use dto store CSRF tokens. */
    const                           CSRF_SESSION_KEY = 'csrfToken';
    /** @var string                 Name of request header transmitting csrf token. */
    const                           CSRF_HEADER_KEY = 'X_CSRF_TOKEN';
    /** @var string                 Request variable name to cancel operations. */
    const                           CANCEL_KEY = 'cancel';
    /** @var string                 Request variable name to commit operations. */
    const                           COMMIT_KEY = 'commit';
    /** @var string                 Key of the content type id request variable. */
    const                           CONTENT_TYPE_KEY = 'tid';
    /** @var string                 Cookie variable containing value of flag indicating the user's consent to collecting cookie data */
    const                           COOKIE_CONSENT_KEY = 'hasCookieConsent';
    /** @var string                 Key of the request variable used to pass CSRF tokens. */
    const                           CSRF_TOKEN_KEY = 'csrf';
    /** @var string                 Key of request variable used to pass error messages. */
    const                           ERROR_MSG_KEY = 'err';
    /** @var string                 Request a variable flag indicating that listings are being filtered. */
    const                           FILTER_KEY = 'filter';
    /** @var string                 Key of the record id request variable. */
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
     * @throws ConfigurationUndefinedException
     */
    public static function getAppBaseDir(): string
    {
        if (!isset(static::$app_base_dir) || static::$app_base_dir === null) {
            throw new ConfigurationUndefinedException(
                'Application\'s base directory was not configured within the app.');
        }
        return static::$app_base_dir;
    }

    /**
     * Gets app domain name.
     * @return string App domain name.
     */
    public static function getAppDomain(): string
    {
        return static::$app_domain ?? '';
    }

    /**
     * Configuration base path getter.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getConfigPath(): string
    {
        if (!isset(static::$config_path) || empty(static::$config_path)) {
            throw new ConfigurationUndefinedException('A configuration path has not been configured.');
        }
        return static::$config_path;
    }

    /**
     * Error log path getter.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getErrorLogPath(): string
    {
        if (!isset(static::$error_log) || empty(static::$local_template_path)) {
            throw new ConfigurationUndefinedException('An error log path has not been configured.');
        }
        return static::$error_log;
    }

    /**
     * Returns the current template root path.
     * @return string Template root path.
     * @throws ConfigurationUndefinedException
     */
    public static function getLocalTemplatesPath(): string
    {
        if (!isset(static::$local_template_path) || empty(static::$local_template_path)) {
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
        if (isset(static::$mysql_keys_path) && !empty(static::$mysql_keys_path) &&
            str_starts_with(static::$mysql_keys_path, '/')) {
            return static::$mysql_keys_path;
        }
        $config_path = '';
        try {
            $config_path = static::getConfigPath();
        } catch (ConfigurationUndefinedException $e) {
            /* continue */
        }
        return $config_path . (static::$mysql_keys_path ?? '');
    }

    /**
     * Returns the root URI for the app.
     * @return string Root URI for the app.
     */
    public static function getRootURI(): string
    {
        if (!isset(static::$app_domain) || empty(static::$app_domain)) {
            return '';
        }
        return 'https://' . rtrim(static::getAppDomain(), '/') . '/';
    }

    /**
     * Returns the current template root path.
     * @return string Template root path.
     * @throws ConfigurationUndefinedException
     */
    public static function getSharedTemplatesPath(): string
    {
        if ('' === static::$shared_template_path) {
            throw new ConfigurationUndefinedException('LittledGlobals shared template path value not set.');
        }
        return static::$shared_template_path;
    }

    /**
     * Sets the domain name for the app.
     * @param string $domain App domain name.
     */
    public static function setAppDomain(string $domain = ''): void
    {
        static::$app_domain = $domain;
    }

    /**
     * Configuration base path setter.
     * @param string|null $path
     * @return void
     */
    public static function setConfigPath(string|null $path): void
    {
        static::$config_path = $path ? rtrim($path, '/') . '/' : '';
    }

    /**
     * Error log path setter.
     * @param string $path
     * @return void
     */
    public static function setErrorLogPath(string $path): void
    {
        static::$error_log = $path;
    }

    /**
     * Sets the root template directory path.
     * @param string $path Path to the root directory containing template files.
     */
    public static function setLocalTemplatesPath(string $path): void
    {
        static::$local_template_path = (($path) ? (rtrim($path, '/') . '/') : (''));
    }

    /**
     * Sets path to current MySQL authentication directory.
     * @param string|null $path MySQL keys path.
     * @throws ConfigurationUndefinedException
     */
    public static function setMySQLKeysPath(string|null $path): void
    {
        static::$mysql_keys_path = ($path) ? rtrim($path, '/') . '/' : '';
    }

    /**
     * Sets the root template directory path.
     * @param string $path Path to the root directory containing template files.
     */
    public static function setSharedTemplatesPath(string $path): void
    {
        static::$shared_template_path = (($path) ? (rtrim($path, '/') . '/') : (''));
    }

    /**
     * "Show verbose errors" app setting getter. Inherited classes can adjust the LittledGlobals::$show_verbose_errors
     * property value to override the default behavior.
     * @return bool
     */
    public static function showVerboseErrors(): bool
    {
        return static::$show_verbose_errors;
    }

    /**
     * "Show verbose errors" app setting setter.
     * @param bool $flag
     * @return void
     */
    public static function setVerboseErrors(bool $flag): void
    {
        static::$show_verbose_errors = $flag;
    }
}