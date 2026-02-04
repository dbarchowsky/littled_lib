<?php

namespace Littled\App;

use Littled\Database\DBConnectionSettings;
use Littled\Exception\ConfigurationUndefinedException;


class LittledGlobals
{
    protected static string|null    $app_base_dir;
    protected static string         $app_domain;
    protected static string|null    $config_path;
    protected static string|null    $error_log;
    protected static string|null    $local_template_path;
    protected static string|null    $shared_template_path;
    protected static bool           $show_verbose_errors = false;

    protected static DBConnectionSettings   $db_config;

    /** @var string                 Name of session variable use dto store CSRF tokens. */
    const string                    CSRF_SESSION_KEY = 'csrfToken';
    /** @var string                 Name of request header transmitting csrf token. */
    const string                    CSRF_HEADER_KEY = 'X_CSRF_TOKEN';
    /** @var string                 Request variable name to cancel operations. */
    const string                    CANCEL_KEY = 'cancel';
    /** @var string                 Request variable name to commit operations. */
    const string                    COMMIT_KEY = 'commit';
    /** @var string                 Key of the content type id request variable. */
    const string                    CONTENT_TYPE_KEY = 'tid';
    /** @var string                 Cookie variable containing the value of a flag indicating the user's consent to collecting cookie data */
    const string                    COOKIE_CONSENT_KEY = 'hasCookieConsent';
    /** @var string                 Key of the request variable used to pass CSRF tokens. */
    const string                    CSRF_TOKEN_KEY = 'csrf';
    /** @var string                 Key of request variable used to pass error messages. */
    const string                    ERROR_MSG_KEY = 'err';
    /** @var string                 Request a variable flag indicating that listings are being filtered. */
    const string                    FILTER_KEY = 'filter';
    /** @var string                 Key of the record id request variable. */
    const string                    ID_KEY = 'id';
    /** @var string Request variable containing status message. */
    const string                    INFO_MESSAGE_KEY = 'msg';
    /** @var string Key of the parent id request variable. */
    const string                    PARENT_ID_KEY = 'pid';
    /** @var string Request variable name containing referring URLs. */
    const string                    REFERER_KEY = 'ref';
    /** @var string */
    const string                    OPERATION_KEY = 'op';

    /**
     * @throws ConfigurationUndefinedException
     */
    public static function getAppBaseDir(): string
    {
        if (!isset(static::$app_base_dir) || static::$app_base_dir === null || static::$app_base_dir === '') {
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
     * @param string $key
     * @return mixed
     * @throws ConfigurationUndefinedException
     */
    public static function getAppSetting(string $key): mixed
    {
        if (!isset(static::${$key}) || static::${$key} === null) {
            throw new ConfigurationUndefinedException("A value has not been assigned to the \"$key\" LittledGlobals property.");
        }
        return static::${$key};
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
     * Database connection settings getter.
     * @return DBConnectionSettings
     * @throws ConfigurationUndefinedException
     */
    public static function getDBSettings(): DBConnectionSettings
    {
        if (!isset(static::$db_config)) {
            throw new ConfigurationUndefinedException('Database configuration not set.');
        }
        return static::$db_config;
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
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getKeysPath(): string
    {
        return static::getAppSetting('keys_path');
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
     * @return void
     */
    public static function loadDatabaseConnection(): void
    {
        static::$db_config = (new DBConnectionSettings())
            ->setHost($_ENV['MYSQL_HOST'] ?? '')
            ->setSchema($_ENV['MYSQL_SCHEMA'] ?? '')
            ->setPort($_ENV['MYSQL_PORT'] ?? '')
            ->setUser($_ENV['MYSQL_USER'] ?? '')
            ->setPassword($_ENV['MYSQL_PASS'] ?? '')
            ->setAESKey($_ENV['MYSQL_AES_ENCRYPT_KEY'] ?? '');
    }

    /**
     * Sets the application base directory path.
     * @param string $path Path to the application's base directory.
     */
    public static function setAppBaseDir(string $path): void
    {
        if ($path === '') {
            static::$app_base_dir = '';
            return;
        }
        static::$app_base_dir = rtrim($path, '/') . '/';
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
     * Assign value to app setting property.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setAppSetting(string $key, mixed $value): void
    {
        if (!property_exists(static::class, $key)) {
            return;
        }
        static::${$key} = $value;
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
     * Keys directory path setter.
     * @param string $path
     * @return void
     */
    public static function setKeysPath(string $path): void
    {
        static::setAppSetting('keys_path', $path);
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
     * @param string $path MySQL keys path.
     */
    public static function setMySQLKeysPath(string $path): void
    {
        static::setAppSetting('mysql_keys_path', $path);
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