<?php
namespace Littled\Request;

use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;

/**
 * Class StringInput
 * @package Littled\Request
 */
class StringInput extends RenderedInput
{
    /** @var string Form input element template filename */
    protected static $input_template_filename = 'string-text-input.php';
    /** @var string */
    protected static $template_filename = 'string-text-field.php';

    /**
	 * {@inheritDoc}
	 */
	public function clearValue()
	{
		$this->value = "";
	}

	/**
	 * Collects the value of this form input and stores it in the object.
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @param ?int $filters Filters for parsing request variables, e.g. FILTER_UNSAFE_RAW, FILTER_SANITIZE_STRING, etc.
	 * @param ?string $key Key to use in place of the internal $key property value.
	 */
	public function collectRequestData (?array $src=null, ?int $filters=null, ?string $key=null)
	{
		if (true===$this->bypassCollectPostData) {
			return;
		}
        $key = $key ?: $this->key;
		if (null===$filters) {
            $filters = FILTER_UNSAFE_RAW;
		}
        $this->value = Validation::collectStringRequestVar($key, $filters, $this->index, $src);
	}

	/**
	 * Sets the internal value of the object. Casts any values as strings.
	 * @param mixed $value
	 */
	public function setInputValue($value)
	{
		$this->value = "$value";
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate ( )
	{
		if ($this->required) {
			if (!is_string($this->value)) {
				$this->throwValidationError($this->formatErrorLabel()." is required.");
			}
			if (strlen(trim($this->value)) < 1) {
				$this->throwValidationError($this->formatErrorLabel()." is required.");
			}
			if (strlen($this->value) > $this->sizeLimit) {
				$this->throwValidationError($this->formatErrorLabel()." is limited to $this->sizeLimit characters.");
			}
		}
	}
}