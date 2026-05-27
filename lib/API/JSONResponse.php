<?php

namespace Littled\API;

use Littled\Database\AppContentBase;
use Littled\Exception\ResponseException;
use Littled\Request\RequestInput;

/**
 * Standardized container for JSON responses to api requests.
 */
class JSONResponse extends JSONResponseBase
{
    /** @var JSONField Operation results message. */
    public JSONField $status;
    /** @var JSONField Error message. */
    public JSONField $error;

    /**
     * Class constructor.
     * @param string $key
     */
    public function __construct(string $key = '')
    {
        parent::__construct($key);
        $this->status = new JSONField('status');
        $this->error = new JSONField('error');
    }

    public function __clone(): void
    {
        foreach($this as $property => $value) {
            $this->$property = match(true) {
                $value instanceof JSONField,
                $value instanceof JSONResponse => clone $value,
                default => $value
            };
        }
    }

    /**
     * Hook for inherited classes. Add any necessary cleanup after sending a response to the client.
     * @return void
     */
    public function cleanup()
    {
        // Hook for inherited classes.
    }

    /**
     * Inserts the error string into the object's error property and sends
     * the object's current properties as JSON string response.
     * @param string $error_msg Error message.
     * @throws ResponseException
     */
    public function returnError(string $error_msg): void
    {
        $this->error->value = $error_msg;
        $this->sendResponse();
        $this->cleanup();
        throw new ResponseException($error_msg);
    }

    /**
     * Sets the error message to be returned to the client.
     * @param string $err
     * @return $this
     */
    public function setErrorMessage(string $err): static
    {
        $this->error->value = $err;
        return $this;
    }

    /**
     * Status setter.
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): static
    {
        $this->status->value = $status;
        return $this;
    }
}