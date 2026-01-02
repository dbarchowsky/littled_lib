<?php

namespace Littled\Exception;

use Exception;
use Littled\Log\Log;
use Littled\PageContent\ContentUtils;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;
use ReturnTypeWillChange;

class LittledException extends Exception
{
    /**
     * NotImplementedException constructor.
     * @param string $message Error message.
     * @param Exception|null $previous
     */
    public function __construct(string $message, $code = 0, Exception|null $previous = null)
    {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    /**
     * custom string representation of the instance of the exception
     */
    #[ReturnTypeWillChange] public function __toString()
    {
        return static::class . " [$this->code]: {$this->message}\n";
    }

    public function getExceptionTypeMessage(): string
    {
        return static::getBaseClass() . " ({$this->code}) {$this->message}\n";
    }

    protected static function getBaseClass(): string
    {
        $pos = strrpos(static::class, '\\');
        return substr(static::class, $pos + 1);
    }

    /**
     * Returns a formatted error message with the exception class name.
     * @param string $message
     * @return string
     */
    public function throwMessage(string $message): string
    {
        return "$message: (" . static::getBaseClass(static::class) . " [$this->code]) {$this->message}\n";
    }
}