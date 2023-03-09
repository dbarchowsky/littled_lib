<?php
namespace Littled\Tests\DataProvider\Request\StringSelect;

use Exception;
use Littled\Exception\ContentValidationException;
use Littled\Request\StringSelect;
use Littled\Tests\DataProvider\Request\SelectTestData;

class StringSelectTestData extends SelectTestData
{
	public const TEST_OPTIONS = array(
		3 => 'option 3',
		88 => 'option 88',
		2 => 'option two',
		65 => 'option 65',
		'foo' => 'option foo',
		'bar' => 'option bar',
		5 => 'option 5'
	);
    /** @var StringSelect Object containing configuration of a select HTML element collecting string values */
	public StringSelect $input;

	function __construct(
		string $expected,
		StringSelect $o,
		array $options=[],
		string $override_label='',
		string $css_class='',
		bool $allow_multiple=false,
		?int $options_size=null)
	{
        parent::__construct($expected, $o, $options, $override_label, $css_class, $allow_multiple, $options_size);
        $this->input = $o;
		if ($allow_multiple) {
			$o->allowMultiple();
		}
		if (0 < $options_size) {
			$o->setOptionsLength($options_size);
		}
	}

    public function mapMultipleTestProvider(): array
    {
        return array(
            $this->expected
        );
    }
}