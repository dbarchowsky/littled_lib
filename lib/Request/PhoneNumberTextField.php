<?php

namespace Littled\Request;

use Littled\Exception\ContentValidationException;

class PhoneNumberTextField extends StringTextField
{
	/**
	 * @return null
	 * @throws ContentValidationException
	 */
	public function validate()
	{
		parent::validate();
		if (strlen("".$this->value)>0) {
			$pattern = "/^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/";
			if (!preg_match($pattern, $this->value)) {
				$this->throwValidationError($this->formatErrorLabel()." is not in a recognized format.");
			}
		}
		return null;
	}
}