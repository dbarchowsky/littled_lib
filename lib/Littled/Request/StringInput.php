<?php
namespace Littled\Request;

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
	 */
	public function collectValue ( $filters=null )
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
			$this->value = filter_input(INPUT_POST, $this->key, $filters);
			if ($this->value===null || $this->value===false) {
				$this->value = filter_input(INPUT_GET, $this->key, $filters);
			}
		}
		else {
			/* array */
			$arr = filter_input(INPUT_POST, $this->key, FILTER_REQUIRE_ARRAY, $filters);
			if (!is_array($arr)) {
				$arr = filter_input(INPUT_GET, $this->key, FILTER_REQUIRE_ARRAY, $filters);
			}
			if (is_array($arr) && array_key_exists($this->index, $arr)) {
				$this->value = $arr[$this->index];
			}
		}
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