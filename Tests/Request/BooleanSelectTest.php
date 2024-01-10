<?php
namespace LittledTests\Request;

use Littled\Exception\NotImplementedException;
use Littled\Request\BooleanSelect;
use Littled\Request\RequestInput;
use LittledTests\DataProvider\Request\BooleanSelectTestData;
use PHPUnit\Framework\TestCase;

class BooleanSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    function doesAllowMultiple()
    {
        $o = new BooleanSelect('Test Input', 'boolKey');

        // always false for BooleanSelect
        $o->allowMultiple();
        $this->assertFalse($o->doesAllowMultiple());

        $o->allowMultiple(false);
        $this->assertFalse($o->doesAllowMultiple());
    }

    public function testLookupValueInSelectedValuesWhenUninitialized()
    {
        $o = new BooleanSelect('Test Input', 'testBool');
        $this->assertFalse($o->lookupValueInSelectedValues(true));
        $this->assertFalse($o->lookupValueInSelectedValues(false));
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\BooleanSelectTestDataProvider::lookupValueInSelectedValuesTestProvider()
     * @param bool $expected
     * @param null|bool|int|string $input_value
     * @param null|bool|int|string $test_value
     * @return void
     */
    public function testLookupValueInSelectedValues(bool $expected, $input_value, $test_value)
    {
        $key = 'testKey';
        $_POST = array($key => $input_value);
        $o = new BooleanSelect('Test Input', $key);
        $o->collectRequestData();
        $this->assertSame($expected, $o->lookupValueInSelectedValues($test_value));
        $_POST = [];
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\BooleanSelectTestDataProvider::renderInputTestProvider()
     * @param BooleanSelectTestData $data
     * @return void
     * @throws NotImplementedException
     */
    function testRenderInput(BooleanSelectTestData $data)
    {
        $this->expectOutputRegex($data->expected);
        $data->input
            ->setOptions(array('' => ' ', '1' => 'enabled', '0' => 'disabled'))
            ->renderInput($data->label_override, $data->options);
    }

    function testSetOptions()
    {
        $o = new BooleanSelect('Test Input', 'testKey');
        $ret = $o->setOptions(array('' => ' ', 'yes' => 'yes', 'no' => 'no'));
        $this->assertCount(3, $o->getOptions());
        $this->assertContains('yes', $o->getOptions());
        $this->assertEquals($ret, $o);
    }
}