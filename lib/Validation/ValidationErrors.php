<?php
namespace Littled\Validation;

class ValidationErrors
{
    /** @var string[] */
    protected array $errors=[];

    /**
     * Clear any existing validation errors.
     * @return void
     */
    public function clear()
    {
        $this->errors = [];
    }

    /**
     * Returns all validation errors as a single string
     * @param string $delimiter (optional) string to insert between the individual error messages.
     * @return string
     */
    public function getErrorsString(string $delimiter=" \n"): string
    {
        return implode($delimiter, $this->errors);
    }

    /**
     * Returns all current error messages as an array of messages.
     * @return string[]
     */
    public function getList(): array
    {
        return $this->errors;
    }

    /**
     * Returns boolean indicating that errors are present.
     * @return bool
     */
    public function hasErrors(): bool
    {
        return (count($this->errors) > 0);
    }

    /**
     * Push new error message onto the stack of existing errors.
     * @param string|array $error Array or string containing errors to push onto the current
     * @return void
     */
    public function push($error)
    {
        if (is_array($error)) {
            $this->errors = array_merge($this->errors, $error);
        }
        else {
            $this->errors[] = $error;
        }
    }

    /**
     * Stores new error message string at the beginning of the stack of current error messages.
     * @param string|array $error Array or string containing errors to push onto the current stack of error messages.
     */
    public function unshift($error)
    {
        if (is_array($error)) {
            $this->errors = array_merge($error, $this->errors);
        }
        else {
            array_unshift($this->errors, $error);
        }
    }
}