<?php
namespace Littled\Tests\Request;

use Littled\Request\RequestInput;
use Littled\Request\StringSelect;
use Littled\Tests\DataProvider\Request\StringSelect\StringSelectTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package Littled\Tests\Request
 */
class StringSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    function testAllowMultiple()
    {
        $o = new StringSelect('Label', 'key');
        $this->assertFalse($o->allow_multiple);

        $o->allowMultiple();
        $this->assertTrue($o->allow_multiple);

        $o->allowMultiple(false);
        $this->assertFalse($o->allow_multiple);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\StringSelect\CollectRequestDataSingleTestDataProvider::testProvider()
     * @param ?string $expected
     * @param string $key
     * @param array $post_data
     * @return void
     */
    function testCollectRequestDataSingle(
        ?string $expected,
        string $key,
        array $post_data=[])
    {
        $_POST = $post_data;

        $o = new StringSelect('Test select', $key, false, '', 100);
        $o->collectRequestData();
        $this->assertEquals($expected, $o->value);

        // restore state
        $_POST = [];
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\StringSelect\CollectRequestDataMultipleTestDataProvider::testProvider()
     * @param ?array $expected
     * @param string $key
     * @param array $post_data
     * @param array|null $custom_data
     * @return void
     */
    function testCollectRequestDataMultiple(
        ?array $expected,
        string $key,
        array $post_data=[],
        ?array $custom_data=null)
    {
        $_POST = $post_data;

        $o = new StringSelect('Test select', 'testKey', false, [], 100);
        $o->allowMultiple();
        $o->collectRequestData($custom_data);
        $this->assertEquals($expected, $o->value);

        // restore state
        $_POST = [];
    }

    /**
	 * @dataProvider \Littled\Tests\DataProvider\Request\StringSelect\StringSelectTestDataProvider::renderTestProvider()
	 * @param StringSelectTestData $data
	 * @return void
	 */
	function testRender(StringSelectTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->input->render($data->override_label, $data->css_class, $data->options);
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\StringSelect\StringSelectTestDataProvider::setInputValueTestProvider()
     * @param array $expected
     * @param $value
     * @return void
     */
    function testSetInputValue(array $expected, $value)
    {
        $o = new StringSelect('Select test', 'testKey', false, [], 100);
        $o->setInputValue($value);
        $this->assertEquals($expected, $o->value);
    }
}