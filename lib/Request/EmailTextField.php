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
	 * EmailTextField constructor.
	 * @param $label
	 * @param $param
	 * @param bool[optional] $required
	 * @param null[optional] $value
	 * @param int[optional] $size_limit
	 * @param null[optional] $index
	 */
	public function __construct($label, $param, $required = false, $value = null, $size_limit = 255, $index = null)
	{
		parent::__construct($label, $param, $required, $value, $size_limit, $index);
	}

	public function validate()
	{
		parent::validate();
		if ($this->required)
		{
			if (Validation::validateEmailAddress($this->value)===false)
			{
				$this->throwValidationError($this->formatErrorLabel()." is not in a recognizable email format.");
			}
		}
	}
}