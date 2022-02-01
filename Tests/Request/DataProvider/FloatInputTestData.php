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

	public function __construct($expected, string $expected_regex, $value, $required=false)
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
		$this->obj = new FloatInput(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required);
		if ('[use default]' !== $value) {
			$this->obj->setInputValue($value);
		}
		$this->value = $value;
	}
}