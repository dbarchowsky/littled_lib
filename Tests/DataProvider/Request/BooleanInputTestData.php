<?php

namespace LittledTests\DataProvider\Request;


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
	public string $expected_regex;
	/** @var BooleanCheckbox */
	public BooleanCheckbox $obj;
	/** @var mixed */
	public $value;
    /** @var string */
    public string $label_override;
    /** @var string */
    public string $class_override;
    public string $msg;

	public function __construct(
               $expected,
        string $expected_regex,
               $value,
               $required=false,
        bool   $has_errors=false,
        string $label_override='',
        string $input_css_class='',
        string $container_css_class='',
        string $class_override='',
        string $msg='')
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
        $this->msg = $msg;
		$this->obj = new BooleanCheckbox(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required);
		$this->obj->setInputValue($value);
		$this->value = $value;
        $this->obj->setInputCSSClass($input_css_class);
        $this->obj->setContainerCSSClass($container_css_class);
        $this->obj->has_errors = $has_errors;
        $this->class_override = $class_override;
        $this->label_override = $label_override;
	}
}