<?php

namespace Littled\Exception;

use Exception;
use ReturnTypeWillChange;

class LittledException extends Exception
{
    /**
     * NotImplementedException constructor.
     * @param string $message Error message.
     * @param Exception|null $previous
     */
    public function __construct(string $message, $code = 0, Exception $previous = null)
    {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    /**
     * custom string representation of object
     */
    #[ReturnTypeWillChange] public function __toString()
    {
        return static::class . ": [$this->code]: {$this->message}\n";
    }
}