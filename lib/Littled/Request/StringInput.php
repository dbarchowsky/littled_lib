<?php
namespace Littled\Request;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;


/**
 * Class StringInput
 * @package Littled\Request
 */
class StringInput extends RequestInput
{
	/**
	 * Clears the data container value.
	 */
	public function clearValue()
	{
		$this->value = "";
	}

	/**
	 * Collects the value of this form input and stores it in the object.
	 * @param int $filters Filters for parsing request variables, e.g. FILTER_UNSAFE_RAW, FILTER_SANITIZE_STRING, etc.
	 * @param array|null[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput ($filters=null, $src=null)
	{
		if ($filters===null) {
			if (strpos($this->class, "mce-editor")!==false) {
				$filters = FILTER_UNSAFE_RAW;
			}
			else {
				$filters = FILTER_SANITIZE_STRING;
			}
		}
		$this->value = null;
		if ($this->index===null) {
			/* single value */
			if (is_array($src)) {
				/* user-defined source array */
				$this->value = null;
				if(array_key_exists($this->key, $src)) {
					$this->value = filter_var($src[$this->key], $filters);
				}
			}
			else {
				/* POST or GET */
				$this->value = filter_input(INPUT_POST, $this->key, $filters);
				if ($this->value===null || $this->value===false) {
					$this->value = filter_input(INPUT_GET, $this->key, $filters);
				}
			}
		}
		else {
			/* array */
			if (is_array($src)) {
				/* user-defined source array */
				$arr = [];
				if (array_key_exists($this->key, $src)) {
					$arr = filter_var($src[$this->key], FILTER_REQUIRE_ARRAY, $filters);
				}
				if (is_array($arr) && array_key_exists($this->index, $arr)) {
					$this->value = $arr[$this->index];
				}
			}
			else {
				/* POST and GET */
				$arr = filter_input(INPUT_POST, $this->key, FILTER_REQUIRE_ARRAY, $filters);
				if (!is_array($arr)) {
					$arr = filter_input(INPUT_GET, $this->key, FILTER_REQUIRE_ARRAY, $filters);
				}
				if (is_array($arr) && array_key_exists($this->index, $arr)) {
					$this->value = $arr[$this->index];
				}
			}
		}
	}

	/**
	 * Returns the HTML to use to include the form input in a DOM.
	 * @param string $label Text to display as the label for the form input.
	 * A null value will cause the internal label value to be used. An empty
	 * string will cause the label to not be rendered at all.
	 * @param string[optional] $css_class CSS class name(s) to apply to the input container.
	 * @throws ConfigurationUndefinedException
	 */
	public function render( $label=null,  $css_class='' )
	{
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("Form input directory path not defined.");
		}
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
	 * @throws ConfigurationUndefinedException
	 */
	function renderInput()
	{
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("Form input directory path not defined.");
		}
	}

	/**
	 * Sets the internal value of the object. Casts any values as strings.
	 * @param mixed $value
	 */
	public function setInputValue($value)
	{
		$this->value = "{$value}";
	}

	/**
	 * Validates the collected value as a non-empty string within its size limit.
	 * @throws ContentValidationException
	 */
	public function validate ( )
	{
		if ($this->required) {
			if (!is_string($this->value)) {
				$this->throwValidationError("{$this->label} is required.");
			}
			if (strlen($this->value) < 1) {
				$this->throwValidationError("{$this->label} is required.");
			}
			if (strlen($this->value) > $this->sizeLimit) {
				$this->throwValidationError("{$this->label} is limited to {$this->sizeLimit} characters.");
			}
		}
	}
}