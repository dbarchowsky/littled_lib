<?php
namespace Littled\Request;


use Littled\Validation\Validation;

class StringSelect extends StringInput
{
	/** @var string */
	protected static string $input_template_filename = 'string-select-input.php';
	/** @var string */
	protected static string $template_filename = 'string-select-field.php';
	/** @var bool */
	public bool $allow_multiple = false;
	/** @var ?int */
	public ?int $options_length = null;

	/**
	 * Allow multiple setter. If set to true, multiple choices can be selected from the drop-down options.
     * @param bool $allow Flag indicating if multiple values are allowed or not.
	 * @return void
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
     * Returns input size attribute markup to inject into template.
     * @return string
     */
    public function formatSizeAttributeMarkup(): string
    {
        return ((0 < $this->options_length)?(" size=\"$this->options_length\""):(''));
    }

	/**
	 * Options length getter.
	 * @return int|null
	 */
	public function getOptionsLength(): ?int
	{
		return $this->options_length;
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
        if (is_array($value)) {
            $value = array_map(function ($e) { return (''.$e); }, $value);
            $this->value = array_values(array_filter($value, function($e) { return ($e!=''); }));
        }
        elseif(''.$value) {
            $this->value = array($value);
        }
        else {
            $this->value = [];
        }
    }

	/**
	 * Options length setter. If this value is set, the number of options displayed will be limited to length value.
	 * @param int $len
	 * @return void
	 */
	public function setOptionsLength(int $len)
	{
		$this->options_length = $len;
	}
}