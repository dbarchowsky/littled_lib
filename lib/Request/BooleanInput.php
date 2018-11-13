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
	 * @param string|null[optional] $key Key to use in place of the internal $key property value.
	 */
	public function collectFromInput ($filters=null, $src=null, $key=null)
	{
		$this->value = Validation::parseBooleanInput((($key)?($key):($this->key)), $this->index, $src);
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
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to FALSE.
	 * @return string SQL-escaped value
	 */
	public function escapeSQL($mysqli, $include_quotes=false)
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
	 * Render the form input element(s) in the DOM.
	 * @param string|null[optional] $label String to use as input label. If this value is not provided, the object's
	 * $label property value will be used. Defaults to NULL.
	 * @param string[optional] $css_class CSS class name(s) to apply to the input container.
	 */
	public function render( $label=null, $css_class=null )
	{
		print ("<span class='\"alert alert-warning\">BooleanInput::renderInput() )Not implemented.</span></div>");
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