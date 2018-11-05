<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\PageContent\PageContent;


/**
 * Class URLTextFieldInput
 * @package Littled\Request
 */
class URLTextField extends StringTextField
{
	/**
	 * Returns string containing HTML to render the input elements in a form.
	 * @param string $label Text to display as the label for the form input.
	 * A null value will cause the internal label value to be used. An empty
	 * string will cause the label to not be rendered at all.
	 * @param string[optional] $css_class CSS class name(s) to apply to the input container.
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function render( $label=null,  $css_class='' )
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