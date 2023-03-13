<?php
namespace Littled\Request;

use Littled\Validation\Validation;


class StringSelect extends StringInput implements RequestSelectInterface
{
    public bool                 $allow_multiple = false;
	protected static string     $input_template_filename = 'string-select-input.php';
	protected static string     $template_filename = 'string-select-field.php';
    /** @var int[]              List of available options to include in dropdown menus */
    public array                $options;
	public ?int                 $options_length = null;
    /** @var string[]|string */
    public                      $value;

	/**
	 * @inheritDoc
	 */
	public function allowMultiple(bool $allow=true)
	{
		$this->allow_multiple = $allow;
	}

    /**
     * @inheritDoc
     */
    public function collectRequestData (?array $src=null, ?int $filters=null, ?string $key=null)
    {
        if (true===$this->bypass_collect_request_data) {
            return;
        }
        $key = $key ?: $this->key;
        if (null===$filters) {
            $filters = Validation::DEFAULT_REQUEST_FILTER;
        }
        $this->value = Validation::collectStringArrayRequestVar($key, $src, $filters);
        if ($this->allow_multiple) {
            if ($this->value===null) {
                $this->value = [];
            }
        }
        else {
            if (is_array($this->value) && count($this->value) > 0) {
                $this->value = $this->value[0];
            }
            else {
                $this->value = '';
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function doesAllowMultiple(): bool
    {
        return $this->allow_multiple;
    }

    /**
     * Returns input size attribute markup to inject into template.
     * @return string
     */
    public function formatSizeAttributeMarkup(): string
    {
        return ((0 < $this->options_length)?(" size=\"$this->options_length\""):(''));
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
	 * @inheritDoc
	 */
	public function getOptionsLength(): ?int
	{
		return $this->options_length;
	}

    /**
     * @inheritDoc
     * @param null|string $value
     * @return bool
     */
    public function lookupValueInSelectedValues($value): bool
    {
        if (is_array($this->value)) {
            return in_array($value, $this->value);
        }
        else {
            return ($value!=='') && ($value === $this->value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $label='', string $css_class='', array $context=[])
    {
        if (!array_key_exists('options', $context)) {
            $context = array('options' => $context);
        }
        parent::render($label, $css_class, $context);
    }

    /**
     * @inheritDoc
     */
    public function setInputValue($value)
    {
        if ($this->allow_multiple) {
            // value is an array of strings
            if (is_array($value)) {
                $value = array_map(function ($e) {
                    return ('' . $e);
                }, $value);
                $this->value = array_values(array_filter($value, function ($e) {
                    return ($e != '');
                }));
            } elseif ('' . $value) {
                $this->value = array($value);
            } else {
                $this->value = [];
            }
        }
        else {
            // value is a single string
            if (is_array($value)) {
                $this->value = ((count($value)>0)? (''.$value[0]) : '');
            }
            else {
                $this->value = filter_var($value);
            }
        }
    }

	/**
	 * @inheritDoc
	 */
	public function setOptionsLength(int $len)
	{
		$this->options_length = $len;
	}

    /**
     * @inheritDoc
     * @param string[] $options
     * @return $this
     */
    public function setOptions(array $options): StringSelect
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if (!$this->required) {
            return;
        }
        if ($this->allow_multiple) {
            if (count($this->value) < 1) {
                $this->throwValidationError($this->formatErrorLabel() . " is required.");
            }
        }
        else {
            if (''.$this->value==='') {
                $this->throwValidationError($this->formatErrorLabel() . " is required.");
            }
        }
    }
}