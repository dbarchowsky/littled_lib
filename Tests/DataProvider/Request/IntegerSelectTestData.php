<?php
namespace LittledTests\DataProvider\Request;

use Littled\Request\IntegerSelect;


class IntegerSelectTestData extends SelectTestData
{
    /** @var array */
	public const TEST_OPTIONS = array(
		3 => 3,
		88 => 88,
		2 => 2,
		65 => 65,
		5 => 5
	);
    /** @var IntegerSelect Object containing configuration of a select HTML element collecting integer values */
	public IntegerSelect $input;

	function __construct(
		string $expected,
		IntegerSelect $o,
		array $options=[],
		string $override_label='',
		string $css_class='',
		bool $allow_multiple=false,
		?int $options_size=null,
        array $selected=[])
	{
        parent::__construct($expected, $o, $options, $override_label, $css_class, $allow_multiple, $options_size, $selected);
        $this->input = $o;
		if ($allow_multiple) {
			$o->allowMultiple();
		}
		if (0 < $options_size) {
			$o->setOptionsLength($options_size);
		}
	}
}