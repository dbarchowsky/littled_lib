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
     * @inheritDoc
     */
	public function render(string $label='', string $css_class='', array $context=[])
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