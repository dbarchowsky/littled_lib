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

	public function __construct($expected, string $expected_regex, $value, $required=false)
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
		$this->obj = new BooleanCheckbox(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required);
		$this->obj->setInputValue($value);
		$this->value = $value;
	}
}