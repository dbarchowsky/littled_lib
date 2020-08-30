<?php
namespace Littled\Request;


use Littled\Validation\Validation;

/**
 * Class FloatInput
 * @package Littled\Request
 */
class FloatInput extends RequestInput
{
	/**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @param int|null[optional] $filters Filters for parsing request variables, e.g. FILTER_UNSAFE_RAW, FILTER_SANITIZE_STRING, etc.
	 * @param array|null[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 * @param string|null[optional] $key Key to use in place of the internal $key property value.
	 */
	public function collectFromInput($filters = null, $src = null, $key=null)
	{
		if ($this->bypassCollectPostData===true) {
			return;
		}
		$this->value = Validation::parseNumericInput((($key)?($key):($this->key)), null, $src);
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param \mysqli $mysqli
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to FALSE.
	 * @return string Escaped value.
	 */
	public function escapeSQL($mysqli, $include_quotes=false)
	{
		$value = Validation::parseNumeric($this->value);
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
		$this->value = Validation::parseNumeric($value);
	}

	/**
	 * Render the form input element(s) in the DOM.
	 * @param string|null[optional] $label String to use as input label. If this value is not provided, the object's
	 * $label property value will be used. Defaults to NULL.
	 * @param string[optional] $css_class CSS class name(s) to apply to the input container.
	 * @param array[optional] $options Associative array containing attributes and attribute values to apply to the HTML element.
	 */
	public function render( $label=null, $css_class=null, $options=[] )
	{
		print ("<span class='\"alert alert-warning\">".get_class($this)."::renderInput() )Not implemented.</span></div>");
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		if (($this->isEmpty()===false) && (Validation::parseNumeric($this->value)===null)) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
		parent::validate();
	}
}