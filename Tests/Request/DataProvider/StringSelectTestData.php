<?php
namespace Littled\Tests\Request\DataProvider;

use Littled\Request\StringSelect;

class StringSelectTestData
{
	/** @var string */
	public const TEST_LABEL = 'test select';
	/** @var string */
	public const TEST_KEY = 'p_key';
	/** @var array */
	public const TEST_OPTIONS = array(
		3 => 'option 3',
		88 => 'option 88',
		2 => 'option two',
		65 => 'option 65',
		'foo' => 'option foo',
		'bar' => 'option bar',
		5 => 'option 5'
	);
	/** @var StringSelect */
	public $input;
	/** @var string */
	public $expected;
	/** @var string */
	public $override_label;
	/** @var string */
	public $css_class;
	/** @var array */
	public $options;

	function __construct(
		string $expected,
		StringSelect $o,
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