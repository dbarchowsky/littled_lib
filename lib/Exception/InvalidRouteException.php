<?php
namespace Littled\Exception;

use Exception;

/**
 * Exceptions thrown in cases of invalid route values.
 */
class InvalidRouteException extends Exception
{
	/**
	 * InvalidTypeException constructor.
	 * @param string $message Error message.
	 * @param int $code Error code.
	 * @param ?Exception $previous
	 */
    public function __construct($message, $code = 0, ?Exception $previous = null)
    {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

	/**
	 * custom string representation of object
	 */
    public function __toString()
    {
        return __CLASS__ . ": [$this->code]: $this->message\n";
    }
}