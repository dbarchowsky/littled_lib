<?php
namespace Littled\Tests\Request\DataProvider;

use Littled\Request\IntegerSelect;

class IntegerSelectTestData
{
	/** @var string */
	public const TEST_LABEL = 'test select';
	/** @var string */
	public const TEST_KEY = 'p_key';
	/** @var array */
	public const TEST_OPTIONS = array(
		3 => 3,
		88 => 88,
		2 => 2,
		65 => 65,
		5 => 5
	);
	public IntegerSelect $input;
	public string $expected;
	public string $override_label;
	public string $css_class;
	public array $options;

	function __construct(
		string $expected,
		IntegerSelect $o,
		array $options=[],
		string $override_label='',
		string $css_class='',
		bool $allow_multiple=false,
		?int $options_size=null)
	{
		$this->input = $o;
		$this->expected = $expected;
		$this->options = $options;
		$this->override_label = $override_label;
		$this->css_class = $css_class;
		if ($allow_multiple) {
			$o->allowMultiple();
		}
		if (0 < $options_size) {
			$o->setOptionsLength($options_size);
		}
	}
}