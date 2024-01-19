<?php
namespace LittledTests\Request;

use Exception;
use Littled\Request\RequestInput;
use Littled\Request\StringSelect;
use LittledTests\DataProvider\Request\StringSelect\StringSelectTestData;
use LittledTests\DataProvider\Request\StringSelect\ValidateTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package LittledTests\Request
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

        $o->setAllowMultiple();
        $this->assertTrue($o->allow_multiple);

        $o->setAllowMultiple(false);
        $this->assertFalse($o->allow_multiple);
    }

    function doesAllowMultiple()
    {
        $o = new StringSelect('Test Input', 'intKey');

        $o->setAllowMultiple(true);
        $this->assertTrue($o->allowMultiple());

        $o->setAllowMultiple(false);
        $this->assertFalse($o->allowMultiple());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\StringSelect\CollectRequestDataSingleTestDataProvider::testProvider()
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
     * @dataProvider \LittledTests\DataProvider\Request\StringSelect\CollectRequestDataMultipleTestDataProvider::testProvider()
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
        $o->setAllowMultiple();
        $o->collectRequestData($custom_data);
        $this->assertEquals($expected, $o->value);

        // restore state
        $_POST = [];
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\StringSelect\StringSelectTestDataProvider::lookupValueInSelectedValuesTestProvider()
     * @param bool $expected
     * @param bool $allow_multiple
     * @param $selections
     * @param $value
     * @return void
     */
    function testLookupValueInSelectedValues(bool $expected, bool $allow_multiple, $selections, $value)
    {
        $o = new StringSelect('Test Input', 'testInput', false, '', 100);
        $o->setAllowMultiple($allow_multiple);
        $o->value = $selections;
        $this->assertSame($expected, $o->lookupValueInSelectedValues($value));
    }

    /**
	 * @dataProvider \LittledTests\DataProvider\Request\StringSelect\StringSelectTestDataProvider::renderTestProvider()
	 * @param StringSelectTestData $data
	 * @return void
	 */
	function testRender(StringSelectTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->input
            ->setOptions($data->options)
            ->render($data->override_label, $data->css_class);
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Request\StringSelect\StringSelectTestDataProvider::setInputValueTestProvider()
     * @param string|array $expected
     * @param bool $allow_multiple
     * @param string|array $value
     * @return void
     */
    function testSetInputValue($expected, bool $allow_multiple, $value)
    {
        $o = new StringSelect('Select test', 'testKey', false, [], 100);
        $o->setAllowMultiple($allow_multiple);
        $o->setInputValue($value);
        $this->assertEquals($expected, $o->value);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\StringSelect\StringSelectTestDataProvider::validateTestProvider()
     * @param ValidateTestData $data
     * @return void
     */
    public function testValidate( ValidateTestData $data )
    {
        $_POST = $data->post_data;
        $o = new StringSelect('Label', $data->key, $data->required, '', 100);
        $o->setAllowMultiple($data->allow_multiple);
        $o->collectRequestData();
        try {
            $o->validate();
            if ($data->expected->exception) {
                $this->assertEquals(false, true, "Expected exception {$data->expected->exception} not thrown.");
            }
            $this->assertCount($data->expected->count, $o->value);
        }
        catch(Exception $e) {
            if ($data->expected->exception) {
                $this->assertInstanceOf($data->expected->exception, $e);
                $this->assertMatchesRegularExpression($data->expected->exception_msg, $e->getMessage());
            }
            else {
                $this->assertEquals(false, true, 'Unexpected Exception thrown.');
            }
        }

        $_POST = [];  // << restore state
    }
}