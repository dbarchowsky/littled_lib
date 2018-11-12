<?php
namespace Littled\Request;


use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;

/**
 * Class DateTextField
 * @package Littled\Tests\Request
 */
class DateTextField extends DateInput
{
    /**
     * Returns a formatted string value that can be inserted into front-facing form fields.
     * @param string[optional] $date_format Format to apply to the date value.
     * @return string|null Formatted date string value.
     */
	public function formatFrontFacingValue($date_format='n/j/Y')
	{
	    try {
            return ($this->formatDateValue($date_format));
        }
        catch(ContentValidationException $ex) {
	        return ($this->value);
        }
	}

    /**
     * Renders the form input HTML that is inserted into the DOM and used to collect the object's value from end users.
     * @param string[optional] $label If a value is provided, it will override the object's internal $label property value.
     * @param string[optional] $css_class CSS class name to apply to the form input element.
     * @throws NotImplementedException
     */
	public function render($label = null, $css_class = '')
    {
        throw new NotImplementedException("render() method is not implemented.");
    }
}