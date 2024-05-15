<?php

namespace Littled\PageContent\Serialized;


use Littled\Exception\ContentValidationException;
use Littled\Request\CategorySelect;
use Littled\Request\RequestInput;
use Littled\Validation\ValidationErrors;

class SerializedContentValidation extends SerializedContentUtils
{
    protected bool $bypass_validation = false;
    protected ValidationErrors $validation_errors;
    /** @var string                 Error message returned when invalid form data is encountered. */
    public string $validation_message = '';

    public function __construct()
    {
        parent::__construct();
        $this->validation_errors = new ValidationErrors();
        $this->validation_message = "Required information is missing.";
    }

    /**
     * Stores new error message string on stack of current error messages.
     * @param string|array $err Array or string containing errors to push onto the current
     * stack of error messages.
     */
    public function addValidationError($err)
    {
        $this->validation_errors->push($err);
    }

    /**
     * Specify if the object should skip validation.
     * @param bool $option (Optional) Set to TRUE (default value) to cause the object to bypass validation.
     * @return void
     */
    public function bypassValidation(bool $option = true)
    {
        $this->bypass_validation = $option;
    }

    /**
     * Clear all current validation error messages.
     * @return void
     */
    public function clearValidationErrors()
    {
        $this->validation_errors->clear();
    }

    /**
     * Returns all validation errors as a single string
     * @param string $delimiter (optional) string to insert between the individual error messages.
     * @param bool $include_header Include the class's generalized error message before specific errors.
     * @return string
     */
    public function getErrorsString(string $delimiter = " \n", bool $include_header = false): string
    {
        if (!$this->hasValidationErrors()) {
            return '';
        }
        return (($include_header && $this->validation_message) ? ($this->validation_message . $delimiter) : ('')) .
            $this->validation_errors->getErrorsString($delimiter);
    }

    /**
     * Indicates if any form data has been entered for the current instance of the object.
     * @return bool Returns true if editing an existing record, a title has been entered, or if any gallery images
     * have been uploaded. Most likely should be overridden in derived classes.
     */
    public function hasData(): bool
    {
        foreach ($this as $property) {
            if ($property instanceof RequestInput && $property->required === true) {
                if ($property->hasData()) {
                    return true;
                }
            } elseif ($property instanceof SerializedContentValidation && $property->hasData()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Tests if the object currently has any validation errors.
     * @return bool Returns TRUE if validation errors are detected, FALSE otherwise.
     */
    public function hasValidationErrors(): bool
    {
        return $this->validation_errors->hasErrors();
    }

    /**
     * Add error message or messages to the beginning of the existing list of errors.
     * @param string|array $err
     * @return void
     */
    public function unshiftValidationError($err)
    {
        $this->validation_errors->unshift($err);
    }

    /**
     * Validates the internal property values of the object for data that is not valid.
     * Updates the $validation_errors property of the object with messages describing the invalid values.
     * @param array $exclude_properties Names of class properties to exclude from validation.
     * @throws ContentValidationException Invalid content found.
     */
    public function validateInput(array $exclude_properties = [])
    {
        if (true === $this->bypass_validation) {
            return;
        }

        $this->validation_errors->clear();
        foreach ($this as $key => $property) {
            if (in_array($key, $exclude_properties)) {
                continue;
            }
            if ($property instanceof RequestInput) {
                try {
                    $property->validate();
                } catch (ContentValidationException $ex) {
                    $this->addValidationError($ex->getMessage());
                }
            } elseif (
                $property instanceof SerializedContentValidation ||
                $property instanceof CategorySelect) {
                try {
                    $property->validateInput();
                } catch (ContentValidationException $ex) {
                    $this->addValidationError($property->validationErrors());
                }
            }
        }
        if ($this->hasValidationErrors()) {
            throw new ContentValidationException($this->validation_message);
        }
    }

    /**
     * Validation errors getter.
     * @return array
     */
    public function validationErrors(): array
    {
        return $this->validation_errors->getList();
    }
}