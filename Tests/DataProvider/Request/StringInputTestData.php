<?php
namespace LittledTests\DataProvider\Request;

use Littled\Request\StringInput;

class StringInputTestData
{
	/** @var string */
	public const DEFAULT_LABEL= 'Test String Input';
	/** @var string */
	public const DEFAULT_KEY = 'stringTest';
	/** @var mixed */
	public              $expected;
	public string       $expected_regex;
	public StringInput  $obj;
	public              $value;
    public string       $label_override;
    public string       $css_class;
    public bool         $display_placeholder;

	public function __construct(
        $expected,
        string $expected_regex,
        $value, $required=false,
        ?int $index=null,
        string $label_override='',
        string $css_class='',
        bool $display_placeholder=false)
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
		$this->obj = new StringInput(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required, '', 50, $index);
		if ('[use default]' !== $value) {
			$this->obj->setInputValue($value);
		}
        $this->obj->display_placeholder = $display_placeholder;
        $this->value = $value;
        $this->label_override = $label_override;
        $this->css_class = $css_class;
	}
}