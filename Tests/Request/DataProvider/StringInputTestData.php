<?php
namespace Littled\Tests\Request\DataProvider;

use Littled\Request\StringInput;

class StringInputTestData
{
	/** @var string */
	public const DEFAULT_LABEL= 'Test String Input';
	/** @var string */
	public const DEFAULT_KEY = 'stringTest';
	/** @var mixed */
	public $expected;
	/** @var string */
	public $expected_regex;
	/** @var StringInput */
	public $obj;
	/** @var mixed */
	public $value;
    /** @var string */
    public $label_override;
    /** @var string */
    public $css_class;

	public function __construct($expected, string $expected_regex, $value, $required=false, ?int $index=null, string $label_override='', string $css_class='')
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
		$this->obj = new StringInput(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required, '', 50, $index);
		if ('[use default]' !== $value) {
			$this->obj->setInputValue($value);
		}
        $this->value = $value;
        $this->label_override = $label_override;
        $this->css_class = $css_class;
	}
}