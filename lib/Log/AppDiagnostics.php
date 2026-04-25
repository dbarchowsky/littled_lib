<?php

namespace Littled\Log;

use Littled\App\LittledGlobals;
use Littled\Exception\LittledException;
use Littled\Exception\OperationFailedException;
use Littled\PageContent\TemplateRenderer;
use Littled\Utility\Mailer;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\ResourceNotFoundException;
use Throwable;

/**
 * @method AppDiagnostics logError(string $err_msg)
 * @method static void logError(string $err_msg)
 * @method AppDiagnostics logException(Throwable $t, bool $logStackTrace=false)
 * @method static void logException(Throwable $t, bool $logStackTrace=false)
 * @method AppDiagnostics setEmailTemplatePath(string $email_template)
 * @method static void setEmailTemplatePath(string $email_template)
 */
class AppDiagnostics
{
    protected string        $message;
    protected string        $source;

    protected static string $password;
    protected static string $recipient_email;
    protected static string $recipient_name = 'Website Health Check';
    protected static string $sender_email;
    protected static string $sender_name = 'Website Health Check';
    protected static string $server;
    protected static string $template_path;

    /**
     * @param string $name
     * @param array $arguments
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function __call(string $name, array $arguments): static
    {
        match ($name) {
            'logError' => static::_logError(...$arguments),
            'logException' => static::_logThrowable(...$arguments),
            'setEmailTemplatePath' => static::_setEmailTemplatePath(...$arguments)
        };
        return $this;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        match ($name) {
            'logError' => static::_logError(...$arguments),
            'logException' => static::_logThrowable(...$arguments),
            'setEmailTemplatePath' => static::_setEmailTemplatePath(...$arguments)
        };
    }

    /**
     * Logs an error message to the error log.
     * @param string $err_msg
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected static function _logError(string $err_msg): void
    {
        $timestamp = static::formatTimestamp();
        error_log("$timestamp $err_msg \n", 3, static::getErrorLogPath());
    }

    /**
     * Logs a Throwable object to the error log.
     * @param Throwable $t
     * @param bool $logStackTrace
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected static function _logThrowable(Throwable $t, bool $logStackTrace=false): void
    {
        $format = '%s: %s in %s:%d';
        if ($logStackTrace) {
            $format .= "\nStack trace:\n%s";
        } else {
            $format .= '%s';
        }

        $message = sprintf(
            $format,
            get_class($t),
            $t->getMessage(),
            $t->getFile(),
            $t->getLine(),
            $logStackTrace ? $t->getTraceAsString() : ''
        );
        $timestamp = static::formatTimestamp();
        error_log("$timestamp $message \n", 3, static::getErrorLogPath());
    }

    protected static function _setEmailTemplatePath(string $email_template): void
    {
        static::$template_path = $email_template;
    }

    /**
     * Clears all static properties.
     * @return void
     */
    public static function clear(): void
    {
        static::$password = '';
        static::$recipient_email = '';
        static::$recipient_name = '';
        static::$sender_email = '';
        static::$sender_name = '';
        static::$server = '';
        static::$template_path = '';
    }

    /**
     * Creates a new instance of AppDiagnostics.
     * @return static
     */
    public static function create(): static
    {
        static::$server = $_ENV['APP_DOMAIN'] ?? '';
        return new static();
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    protected function formatDiagnosticsMessage(): string
    {
        return TemplateRenderer::create()
            ->withTemplate($this::getEmailTemplatePath())
            ->withContext([
                'server' => static::getServer(),
                'source' => $this->source ?? static::getSource(2),
                'message' => $this->message])
            ->renderAsMarkup();
    }

    /**
     * Returns the current timestamp in a human-readable format.
     * @return string
     */
    protected static function formatTimestamp(): string
    {
        return date('[d-M-Y H:i:s e]');
    }

    /**
     * @return string
     */
    public static function getEmailTemplatePath(): string
    {
        return (static::$template_path ?? '');
    }

    /**
     * Returns the path to the error log file.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getErrorLogPath(): string
    {
        return LittledGlobals::getErrorLogPath();
    }

    /**
     * Email body content getter.
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getSenderEmail(): string
    {
        if (!isset(static::$sender_email) || empty(static::$sender_email)) {
            throw new ConfigurationUndefinedException('Sender email is not set.');
        }
        return static::$sender_email;
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getServer(): string
    {
        if (!isset(static::$server) || empty(static::$server)) {
            throw new ConfigurationUndefinedException('Server is not set.');
        }
        return static::$server;
    }

    /**
     * Returns information about the function x number of steps back in the call stack
     *
     * @param int $steps Number of steps back to look (0 = current function, 1 = caller, etc.)
     * @param int $options Optional backtrace options (same as debug_backtrace())
     * @return string Returns function information or null if $step doesn't exist
     */
    protected static function getSource(int $steps = 1, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT): string
    {
        $trace = debug_backtrace($options);

        // Add 1 to steps because we want to skip this function itself
        $targetIndex = $steps + 1;

        if (!isset($trace[$targetIndex])) {
            return '';
        }

        $frame = $trace[$targetIndex];

        return sprintf("%s::%s()\n%s line %s",
        $frame['class'] ?? null,
            $frame['function'] ?? null,
            $frame['file'] ?? null,
            $frame['line'] ?? null);
    }

    /**
     * Instantiate a Mailer instance in a method to allow for mocking in tests.
     * @return Mailer
     */
    protected static function instantiateMailer(): Mailer
    {
        return new Mailer();
    }

    /**
     * Sends an email with diagnostics information.
     * @param int $debug_level
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws OperationFailedException
     */
    public function sendEmail(int $debug_level = 0): static
    {
        try {
            $msg = $this->formatDiagnosticsMessage();
        } catch (ResourceNotFoundException) {
            $msg = $this->message ?? '';
        }
        static::instantiateMailer()
            ->setSender(static::$sender_email, static::$sender_name)
            ->setRecipient(static::$recipient_email, static::$recipient_name)
            ->setPassword(static::$password)
            ->setSubject('BFH Web App Diagnostics')
            ->setBody($msg)
            ->send($debug_level);
        return $this;
    }

    /**
     * Email body content setter.
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function setSenderEmail(string $sender_email): static
    {
        static::$sender_email = $sender_email;
        return $this;
    }

    public static function setServer(string $server): void
    {
        static::$server = $server;
    }

    public function setSource(string|Throwable $source): static
    {
        if (is_string($source)) {
            $this->source = $source;
        }
        else {
            $this->source = $source->getFile() . '(' . $source->getLine() . ") \n" .
                ($source instanceof LittledException ? $source->getFullTraceAsString() : $source->getTraceAsString());
        }
        return $this;
    }
}