<?php
namespace Littled\Tests\Request;

use Littled\Request\IntegerSelect;
use Littled\Request\RequestInput;
use Littled\Tests\DataProvider\Request\IntegerSelectTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package Littled\Tests\Request
 */
class IntegerSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    function doesAllowMultiple()
    {
        $o = new IntegerSelect('Test Input', 'intKey');

        $o->allowMultiple(true);
        $this->assertTrue($o->doesAllowMultiple());

        $o->allowMultiple(false);
        $this->assertFalse($o->doesAllowMultiple());
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\IntegerSelectTestDataProvider::lookupValueInSelectedValuesTestProvider()
     * @param bool $expected
     * @param int|int[]|null $selected_values
     * @param bool $allow_multiple
     * @param int $value
     * @return void
     */
    function testLookupValueInSelectedValues(bool $expected, $selected_values, bool $allow_multiple, int $value)
    {
        $o = new IntegerSelect('Test Input', 'testInput');
        $o->allow_multiple = $allow_multiple;
        $o->value = $selected_values;
        $this->assertSame($expected, $o->lookupValueInSelectedValues($value));
    }

    /**
	 * @dataProvider \Littled\Tests\DataProvider\Request\IntegerSelectTestDataProvider::renderTestProvider()
	 * @param IntegerSelectTestData $data
	 * @return void
	 */
	function testRender(IntegerSelectTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->input->setOptions($data->options)->render($data->override_label, $data->css_class);
	}
}