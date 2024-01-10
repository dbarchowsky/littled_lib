<?php

namespace LittledTests\DataProvider\Request;


class HiddenInputTestData extends IntegerInputTestData
{
	public int $value_override;

	public function __construct(
		$expected,
		string $expected_regex,
		string $msg,
		$value,
		bool $required = false,
		?int $index = null,
		string $input_css_class = '',
		string $container_css_class = '',
		string $css_override = '',
		string $label_override = '',
		bool $display_placeholder = false,
		?int $value_override=null
	)
	{
		parent::__construct($expected, $expected_regex, $msg, $value, $required, $index, $input_css_class, $container_css_class, $css_override, $label_override, $display_placeholder);
		if ($value_override !== null) {
			$this->value_override = $value_override;
		}
	}
}