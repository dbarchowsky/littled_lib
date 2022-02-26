<?php
namespace Littled\Request;


use Exception;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\ContentUtils;

/**
 * Class DateTextField
 * @package Littled\Tests\Request
 */
class DateTextField extends DateInput
{
    /** @var string Defaults to "datepicker" in order to bind graphical calendar widget */
    public string $input_css_class='datepicker';

    /**
     * Returns a formatted string value that can be inserted into front-facing form fields.
     * @param string $date_format (Optional) Format to apply to the date value.
     * @return string|null Formatted date string value.
     */
	public function formatFrontFacingValue(string $date_format='n/j/Y'): ?string
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
     * @param string $label (Optional) If a value is provided, it will override the object's internal $label property value.
     * @param string $css_class (Optional) CSS class name to apply to the form input element.
     * @param array $options (Optional) Options to display.
     */
	public function render(string $label='', string $css_class='', array $options=[])
    {
        try {
            ContentUtils::renderTemplate(static::getTemplatePath(),
                array('input' => $this,
                    'label' => $label,
                    'css_class' => $css_class));
        }
        catch(Exception $e) {
            ContentUtils::printError($e->getMessage());
        }
    }
}