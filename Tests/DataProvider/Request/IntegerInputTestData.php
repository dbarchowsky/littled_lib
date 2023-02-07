<?php

namespace Littled\Tests\DataProvider\Request;


use Littled\Request\IntegerInput;

class IntegerInputTestData
{
	/** @var string */
	public const DEFAULT_LABEL= 'Test Integer Input';
	/** @var string */
	public const DEFAULT_KEY = 'intTest';
	/** @var mixed */
	public $expected;
	/** @var string */
	public string $expected_regex;
    public string $msg='';
	/** @var IntegerInput */
	public IntegerInput $obj;
	/** @var mixed */
	public $value;
    /** @var string */
    public string $css_override;
    /** @var string */
    public string $label_override;
    /** @var bool */
    public bool $display_placeholder;

	public function __construct(
               $expected,
        string $expected_regex,
        string $msg,
               $value,
        bool   $required=false,
        ?int   $index=null,
        string $input_css_class='',
        string $container_css_class='',
        string $css_override='',
        string $label_override='',
        bool   $display_placeholder=false)
	{
		$this->expected = $expected;
		$this->expected_regex = $expected_regex;
        $this->msg = $msg;
		$this->obj = new IntegerInput(self::DEFAULT_LABEL, self::DEFAULT_KEY, $required, null, 0, $index);
		if ('[use default]' !== $value) {
			$this->obj->setInputValue($value);
		}
        $this->obj->display_placeholder = $display_placeholder;
        if ($input_css_class) {
            $this->obj->setInputCSSClass($input_css_class);
        }
        if ($container_css_class) {
            $this->obj->setContainerCSSClass($container_css_class);
        }
        $this->value = $value;
        $this->label_override = $label_override;
        $this->css_override = $css_override;
	}
}