<?php
namespace Littled\Request;


use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\PageContent;
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
	 * @param string[optional] $label If a value is provided, it will override the object's internal $label property value.
	 * @param string[optional] $css_class CSS class name to apply to the form input element.
	 * @throws NotImplementedException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function render($label = null, $css_class = '')
	{
		if (false === $this->isTemplateDefined()) {
			throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
		}

		if (!$label) {
			$label = $this->label;
		}
		$error_class = (($this->hasErrors)?($this::getErrorClass()):(''));
		$css_class = trim(implode(' ', array($this->cssClass, $css_class, $error_class)));
		$selection_state = ((true === $this->value)?(' checked="checked"'):(''));
		$required_str = (($this->required)?($this::getRequiredIndicator()):(''));

		PageContent::render($this::getTemplatePath(),
			array(
				'input' => &$this,
				'label' => $label,
				'css_class' => $css_class,
				'selection_state' => $selection_state,
				'required_field_indication' => $required_str
			));
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