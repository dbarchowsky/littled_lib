<?php

namespace Littled\PageContent\Serialized;


use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;

class SerializedContentValidation extends SerializedContentUtils
{
    /** @var int */
    protected $bypass_validation = false;

	/**
	 * Stores new error message string on stack of current error messages.
	 * @param string|array $err Array or string containing errors to push onto the current
	 * stack of error messages.
	 */
	public function addValidationError($err)
	{
		if (is_array($err)) {
			$this->validationErrors = array_merge($this->validationErrors, $err);
		}
		else {
            $this->validationErrors[] = $err;
		}
	}

    /**
     * Stores new error message string at the beginning of the stack of current error messages.
     * @param string|array $err Array or string containing errors to push onto the current
     * stack of error messages.
     */
    public function unshiftValidationError($err)
    {
        if (is_array($err)) {
            $this->validationErrors = array_merge($err, $this->validationErrors);
        }
        else {
            array_unshift($this->validationErrors, $err);
        }
    }

    /**
     * Specify if the object should skip validation.
     * @param bool $option (Optional) Set to TRUE (default value) to cause the object to bypass validation.
     * @return void
     */
    public function bypassValidation(bool $option=true)
    {
        $this->bypass_validation = $option;
    }

    /**
	 * Removes all validation errors from the object.
	 */
	public function clearValidationErrors()
	{
		$this->validationErrors = array();
	}

	/**
	 * Returns all validation errors as a single string
	 * @param string $delimiter (optional) string to insert between the individual error messages.
	 * @return string
	 */
	public function getErrorsString(string $delimiter=" \n"): string
	{
		return implode($delimiter, $this->validationErrors);
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return bool Returns true if editing an existing record, a title has been entered, or if any gallery images
	 * have been uploaded. Most likely should be overridden in derived classes.
	 */
	public function hasData(): bool
	{
		foreach($this as $property)
		{
			if ($property instanceof RequestInput && $property->required===true) {
				if ($property->isEmpty()===false) {
					return true;
				}
			}
			elseif($property instanceof SerializedContentValidation && $property->hasData()) {
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
		return (count($this->validationErrors) > 0);
	}

	/**
	 * Validates the internal property values of the object for data that is not valid.
	 * Updates the $validation_errors property of the object with messages describing the invalid values.
	 * @param array $exclude_properties Names of class properties to exclude from validation.
	 * @throws ContentValidationException Invalid content found.
	 */
	public function validateInput(array $exclude_properties=[])
	{
        if (true===$this->bypass_validation) {
            return;
        }

		$this->validationErrors = [];
		foreach($this as $key => $property) {
			if (in_array($key, $exclude_properties)) {
				continue;
			}
			if ($property instanceof RequestInput)
			{
				try {
					$property->validate();
				}
				catch(ContentValidationException $ex) {
					$this->addValidationError($ex->getMessage());
				}
			}
			elseif($property instanceof SerializedContentValidation) {
				try {
					$property->validateInput();
				}
				catch(ContentValidationException $ex) {
					$this->addValidationError($property->validationErrors);
				}
			}
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("Some required information is missing.");
		}
	}
}