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
	 * @param array|null[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput ($filters=null, $src=null)
	{
		$this->value = Validation::parseBooleanInput($this->key, $this->index, $src);
	}

	/**
	 * Assigns a value to the object, with checks to make sure that the stored value is a boolean.
	 * @param boolean $value Value to assign.
	 */
	public function setInputValue ($value)
	{
		$this->value = Validation::parseBoolean($value);
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param \mysqli $mysqli
	 * @return string SQL-escaped value
	 */
	public function escapeSQL( $mysqli )
	{
		if ($this->value===false || $this->value==='false' || $this->value==='0' || $this->value===0) {
			return ('0');
		}
		if ($this->value===true || $this->value==='true' || $this->value==='1' || $this->value===1) {
			return ('1');
		}
		return ('null');
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