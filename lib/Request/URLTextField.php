<?php
namespace Littled\Request;


use Littled\Exception\ContentValidationException;

/**
 * Class URLTextFieldInput
 * @package Littled\Request
 */
class URLTextField extends StringTextField
{
	/**
	 * URLTextField constructor.
	 * Override parent to set a default field length of 255 characters. This can be overridden if needed.
	 * @param string $label Label to display in the browser for the form input.
	 * @param string $param Variable name used to pass the value in the form or request data.
	 * @param bool[optional] $required Flag indicating that a value is required for this element in the form. Defaults to FALSE.
	 * @param string|null[optional] $value Value entered in the form for this property. Defaults to NULL.
	 * @param int[optional] $size_limit Size limit of the field in the form element and in the database.
	 * @param int|null[optional] $index Indicates that there are multiple values entered for this property.
	 */
	function __construct($label, $param, $required = false, $value = null, $size_limit = 255, $index = null)
	{
		parent::__construct($label, $param, $required, $value, $size_limit, $index);
	}

	/**
	 * {@inheritDoc}
	 */
	public function render( string $label='',  string $css_class='' )
	{
		/** TODO mark the form input as having type="url" */
		parent::render($label, $css_class);
	}

	/**
	 * Filters the values stored in the object to only be sanitized URLs.
	 * @param mixed $value Value to assign to the object.
	 */
	public function setInputValue($value)
	{
		$this->value = filter_var(strip_tags($value), FILTER_SANITIZE_URL);
	}

	/**
	 * Validates the value entered into the form to ensure that it is a valid URL. If the value is accepted, the
	 * $value property of the object is updated with the filtered URL value.
	 * @throws ContentValidationException
	 */
	public function validate()
	{
		$value = filter_var($this->value, FILTER_VALIDATE_URL);
		if ($value === false) {
			$this->throwValidationError(ucfirst($this->label)." does not appear to be a valid URL.");
		}
		$this->value = filter_var($value, FILTER_SANITIZE_URL);
	}
}