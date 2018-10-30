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
	 * IntegerInput constructor
	 * @param string $label Input label
	 * @param string $param value of the name attribute of the input
	 * @param boolean[optional] $required Flag indicating if this form data is required. Defaults to FALSE.
	 * @param mixed[optional] $value Initial value of the input. Defaults to NULL.
	 * @param int $size_limit[optional] Maximum size in bytes of the value when it is stored in the database (for strings). Defaults to 0.
	 * @param int $index[optional] Index of this input if it is part of an array of inputs with the same name attribute. Defaults to NULL.
	 */
	public function __construct(string $label, string $param, bool $required = false, ?string $value = null, int $size_limit = 0, ?int $index = null)
	{
		parent::__construct($label, $param, $required, $value, $size_limit, $index);
		$this->setInputValue($value);
	}

	/**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @param int|null[optional] $filters Filters for parsing request variables, e.g. FILTER_UNSAFE_RAW, FILTER_SANITIZE_STRING, etc.
	 * @param array|null[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 * @param string|null[optional] $key Key to use in place of the internal $key property value.
	 */
	public function collectFromInput($filters = null, $src = null, $key=null)
	{
		$this->value = Validation::collectIntegerRequestVar((($key)?($key):($this->key)), null, $src);
	}

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