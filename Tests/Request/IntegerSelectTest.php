<?php
namespace LittledTests\Request;

use Littled\Request\IntegerSelect;
use Littled\Request\RequestInput;
use LittledTests\DataProvider\Request\IntegerSelectTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package LittledTests\Request
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

        $o->allowMultiple();
        $this->assertTrue($o->doesAllowMultiple());

        $o->allowMultiple(false);
        $this->assertFalse($o->doesAllowMultiple());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::lookupValueInSelectedValuesTestProvider()
     * @param bool $expected
     * @param int|int[]|null $selected_values
     * @param bool $allow_multiple
     * @param ?int $value
     * @return void
     */
    function testLookupValueInSelectedValues(bool $expected, $selected_values, bool $allow_multiple, ?int $value)
    {
        $o = new IntegerSelect('Test Input', 'testInput');
        $o->allow_multiple = $allow_multiple;
        $o->value = $selected_values;
        $this->assertSame($expected, $o->lookupValueInSelectedValues($value));
    }

    /**
	 * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::renderTestProvider()
	 * @param IntegerSelectTestData $data
	 * @return void
	 */
	function testRender(IntegerSelectTestData $data)
	{
        $this->_testRender($data);
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::renderUsingProcedureTestProvider()
     * @param IntegerSelectTestData $data
     * @return void
     */
    function testRenderUsingProcedure(IntegerSelectTestData $data)
    {
        $this->_testRender($data);
    }

    /**
     * @param IntegerSelectTestData $data
     * @return void
     */
    protected function _testRender(IntegerSelectTestData $data)
    {
        $this->expectOutputRegex($data->expected);
        $data->input->value = $data->selected;
        $data->input
            ->setOptions($data->options)
            ->render($data->override_label, $data->css_class);
    }
}