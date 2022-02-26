<?php
namespace Littled\Request;


use Exception;
use Littled\PageContent\ContentUtils;

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
	 * Allow multiple setter. If set to true, multiple choices can be selected from the drop down options.
	 * @return void
	 */
	public function allowMultiple()
	{
		$this->allow_multiple = true;
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
    public function render(string $label='', string $css_class='', array $options=[])
    {
	    try {
		    ContentUtils::renderTemplate(static::getTemplatePath(),
			    array('input' => $this,
				    'label' => $label,
				    'css_class' => $css_class,
				    'options' => $options));
	    }
	    catch(Exception $e) {
		    ContentUtils::printError($e->getMessage());
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