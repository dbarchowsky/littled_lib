<?php
namespace Littled\Request;

use Littled\PageContent\ContentUtils;

/**
 * Class StringInput
 * @package Littled\Request
 */
class StringInput extends RequestInput
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
		if ($this->bypassCollectPostData===true) {
			return;
		}

		if (!$key) {
			$key = $this->key;
		}

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
				if(array_key_exists($key, $src)) {
					$this->value = filter_var($src[$key], $filters);
				}
			}
			else {
				/* POST or GET */
				$this->value = filter_input(INPUT_POST, $key, $filters);
				if ($this->value===null || $this->value===false) {
					$this->value = filter_input(INPUT_GET, $key, $filters);
				}
			}
		}
		else {
			/* array */
			if (is_array($src)) {
				/* user-defined source array */
				$arr = [];
				if (array_key_exists($key, $src)) {
					$arr = filter_var($src[$key], FILTER_REQUIRE_ARRAY, $filters);
				}
			}
			else {
				/* POST and GET */
				$arr = filter_input(INPUT_POST, $key, FILTER_REQUIRE_ARRAY, $filters);
				if (!is_array($arr)) {
					$arr = filter_input(INPUT_GET, $key, FILTER_REQUIRE_ARRAY, $filters);
				}
			}
			if (is_array($arr) && array_key_exists($this->index, $arr)) {
				$this->value = $arr[$this->index];
			}
		}
	}

    /**
     * Returns string containing HTML to render the input elements in a form.
     * @param string $label (Optional) Text to display as the label for the form input.
     * A null value will cause the internal label value to be used. An empty
     * string will cause the label to not be rendered at all.
     * @param string $css_class (Optional) CSS class name(s) to apply to the input container.
     */
    public function render( string $label='', string $css_class='' )
    {
        if (!$label) {
            $label=$this->label;
        }
        if (!$css_class) {
            $css_class = $this->cssClass;
        }
        ContentUtils::renderTemplateWithErrors(self::getTemplatePath(), array(
            'input' => &$this,
            'label' => $label,
            'css_class' => $css_class
        ));
    }

    /**
     * Renders the corresponding form field with a label to collect the input data.
     * @param string[optional] $label
     */
    public function renderInput($label=null)
    {
        if (!$label) {
            $label = $this->label;
        }
        ContentUtils::renderTemplateWithErrors(self::getInputTemplateFilename(), array(
            'input' => &$this,
            'label' => $label
        ));
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