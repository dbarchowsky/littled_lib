<?php

namespace Littled\Exception;

use Exception;
use ReturnTypeWillChange;
use Throwable;


class LittledException extends Exception
{
    public string                           $frontend_error;

    /**
     * @param string                        $message Error message.
     * @param int                           $code
     * @param Exception|null                $previous
     */
    public function __construct(string $message, int $code = 0, Exception|null $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    /**
     * custom string representation of the instance of the exception
     */
    #[ReturnTypeWillChange] public function __toString()
    {
        return static::class . " [$this->code]: $this->message\n";
    }

    public function getExceptionTypeMessage(): string
    {
        return static::getBaseClass() . " ($this->code) $this->message\n";
    }

    /**
     * Returns an error message to be displayed on the frontend.
     * @return string
     */
    public function getFrontendError(): string
    {
        return ($this->frontend_error ?? '') ?: $this->message;
    }

    /**
     * Returns the full trace of the exception, including previous exceptions.
     * @return array
     */
    public function getFullTrace(): array
    {
        $trace = $this->getTrace();

        $previous = $this->getPrevious();
        while ($previous !== null) {
            $trace = array_merge($trace, $previous->getTrace());
            $previous = $previous->getPrevious();
        }

        return $trace;
    }

    /**
     * @return string
     */
    protected static function getBaseClass(): string
    {
        $pos = strrpos(static::class, '\\');
        return substr(static::class, $pos + 1);
    }

    /**
     * Sets a message to be displayed on the frontend.
     * @param string $error
     * @return $this
     */
    public function setFrontendError(string $error): static
    {
        $this->frontend_error = $error;
        return $this;
    }

    /**
     * Returns a formatted error message with the exception class name.
     * @param string $message
     * @return string
     */
    public function throwMessage(string $message): string
    {
        return "$message: (" . static::getBaseClass() . " [$this->code]) $this->message\n";
    }
}