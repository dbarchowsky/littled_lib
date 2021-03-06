<?php

namespace Littled\PageContent\Serialized;


use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;

class SerializedContentValidation extends SerializedContentUtils
{
	/**
	 * Stores new error message string on stack of current error messages.
	 * @param $err string|array Array or string containing errors to push onto the current
	 * stack of error messages.
	 */
	public function addValidationError($err)
	{
		if (is_array($err)) {
			$this->validationErrors = array_merge($this->validationErrors, $err);
		}
		else {
			array_push($this->validationErrors, $err);
		}
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
			if ($property instanceof RequestInput && $property->required===true)
			{
				if ($property->isEmpty()===false)
				{
					return true;
				}
			}
			elseif($property instanceof SerializedContentValidation && $property->hasData())
			{
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
	 * @param array[optional] $exclude_properties Names of class properties to exclude from validation.
	 * @throws ContentValidationException Invalid content found.
	 */
	public function validateInput($exclude_properties=[])
	{
		$this->validationErrors = [];
		foreach($this as $key => $property)
		{
			if (in_array($key, $exclude_properties))
			{
				continue;
			}
			if ($property instanceof RequestInput)
			{
				try
				{
					$property->validate();
				}
				catch(ContentValidationException $ex)
				{
					$this->addValidationError($ex->getMessage());
				}
			}
			elseif($property instanceof SerializedContentValidation)
			{
				try
				{
					$property->validateInput();
				}
				catch(ContentValidationException $ex)
				{
					if (strlen($ex->getMessage()) > 0)
					{
						$this->addValidationError($ex->getMessage());
					}
					$this->addValidationError($property->validationErrors);
				}
			}
		}
		if (count($this->validationErrors) > 0)
		{
			throw new ContentValidationException("Some required information is missing.");
		}
	}
}