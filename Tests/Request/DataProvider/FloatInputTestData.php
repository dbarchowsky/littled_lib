<?php

namespace Littled\Tests\Request\DataProvider;


use Littled\Request\FloatInput;

class FloatInputTestData
{
	/** @var string */
	public const DEFAULT_LABEL= 'Test Float Input';
	/** @var string */
	public const DEFAULT_KEY = 'floatTest';
	/** @var mixed */
	public $expected;
	/** @var string */
	public $expected_regex;
	/** @var FloatInput */
	public $obj;
	/** @var mixed */
	public $value;
    /** @var string */
    public $css_class;
    /** @var string */
    public $label_override;
    /** @var bool */
    public $display_placeholder;

	public function __construct(
        $expected,
        string $expected_regex,
        $value,
        bool $required=false,
        ?int $index=null,
        string $label_override='',
        string $css_class='',
        bool $display_placeholder=false)
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
		$this->obj = new FloatInput(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required, null, 0, $index);
		if ('[use default]' !== $value) {
			$this->obj->setInputValue($value);
		}
        $this->obj->displayPlaceholder = $display_placeholder;
        $this->value = $value;
        $this->label_override = $label_override;
        $this->css_class = $css_class;
	}
}