<?php

namespace Littled\Tests\Request\DataProvider;


use Littled\Request\BooleanCheckbox;

class BooleanInputTestData
{
	/** @var string */
	public const DEFAULT_LABEL= 'Test Checkbox Input';
	/** @var string */
	public const DEFAULT_KEY = 'checkboxInputTest';
	/** @var mixed */
	public $expected;
	/** @var string */
	public $expected_regex;
	/** @var BooleanCheckbox */
	public $obj;
	/** @var mixed */
	public $value;
    /** @var string */
    public $label_override;
    /** @var string */
    public $class_override;

	public function __construct($expected, string $expected_regex, $value, $required=false, bool $has_errors=false, string $label_override='', string $css_class='', string $class_override='')
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
		$this->obj = new BooleanCheckbox(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required);
		$this->obj->setInputValue($value);
		$this->value = $value;
        if ($css_class) {
            $this->obj->cssClass = $css_class;
        }
        $this->obj->hasErrors = $has_errors;
        $this->class_override = $class_override;
        $this->label_override = $label_override;
	}
}