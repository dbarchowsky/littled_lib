<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\Validation\Validation;


/**
 * Class BooleanInput
 * @package Littled\Request
 */
class BooleanInput extends RequestInput
{
	/**
	 * Clears the data container value.
	 */
	public function clearValue()
	{
		$this->value = null;
	}

	/**
	 * Collects the value of this form input and stores it in the object.
	 * @param int $filters Filters for parsing request variables, e.g. FILTER_UNSAFE_RAW, FILTER_SANITIZE_STRING, etc.
	 */
	public function collectValue ($filters=null)
	{
		$this->value = Validation::parseBooleanInput($this->key, $this->index);
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param \mysqli $mysqli
	 * @return string SQL-escaped value
	 */
	public function escapeSQL( $mysqli )
	{
		return (($this->value===false || $this->value===null)?('0'):('1'));
	}

	/**
	 * Validates the collected value as a non-empty string within its size limit.
	 * @throws ContentValidationException
	 */
	public function validate ( )
	{
		if ($this->required) {
			if ($this->value===null) {
				$this->throwValidationError(ucfirst($this->label)." is required.");
			}
		}
		if ($this->value!==null && $this->value!==true && $this->value!==false) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
	}
}