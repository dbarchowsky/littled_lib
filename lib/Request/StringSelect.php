<?php
namespace Littled\Request;


use Exception;
use Littled\PageContent\ContentUtils;

/**
 * Class StringSelect
 * @package Littled\Request
 */
class StringSelect extends StringInput
{
	/** @var string */
	protected static $input_template_filename = 'string-select-input.php';
	/** @var string */
	protected static $template_filename = 'string-select-field.php';
	/** @var bool */
	public $allow_multiple = false;
	/** @var int */
	public $options_length = null;

	/**
	 * Allow multiple setter. If set to true, multiple choices can be selected from the drop down options.
	 * @return void
	 */
	public function allowMultiple()
	{
		$this->allow_multiple = true;
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