<?php
namespace Littled\Request;


use Littled\Validation\Validation;

/**
 * Class StringTextFieldInput
 * @package Littled\Request
 */
class EmailTextField extends StringTextField
{
	/**
	 * {@inheritDoc}
	 */
	public function __construct($label, $param, $required = false, $value = null, $size_limit = 255, $index = null)
	{
		parent::__construct($label, $param, $required, $value, $size_limit, $index);
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		parent::validate();
		if (strlen(trim($this->value)) > 0)
		{
			if (Validation::validateEmailAddress($this->value)===false)
			{
				$this->throwValidationError($this->formatErrorLabel()." is not in a recognized email format.");
			}
		}
	}
}