<?php
namespace Littled\Request;

use Littled\Validation\Validation;

/**
 * Class IntegerInput
 * @package Littled\Request
 */
class IntegerInput extends RequestInput
{
	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param \mysqli $mysqli
	 * @return string Escaped value.
	 */
	public function escapeSQL($mysqli)
	{
		$value = Validation::parseInteger($this->value);
		if ($value===null) {
			return('NULL');
		}
		return ($mysqli->real_escape_string($value));
	}

	/**
	 * @param integer $value Value to assign as the value of the object.
	 */
	public function setInputValue($value)
	{
		$this->value = Validation::parseInteger($value);
	}

	/**
	 * Validates the object's current value stored in its $value property.
	 * @returns True if no validation errors are found.
	 * @throws \Littled\Exception\ContentValidationException
	 */
	public function validate()
	{
		parent::validate();
		if (($this->isEmpty()===false) && (Validation::parseInteger($this->value)===null)) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
		return (true);
	}
}