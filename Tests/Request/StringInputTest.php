<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Tests\Request\DataProvider\StringInputTestData;
use PHPUnit\Framework\TestCase;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;


/**
 * Class StringInputTest
 * @package Littled\Tests\Request
 */
class StringInputTest extends TestCase
{
	/** @var StringInput Test DateInput object. */
	public $obj;

    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->obj = new StringInput("Test date", 'p_date');
    }

    public function testConstructor()
	{
		$obj = new StringInput("Label", "key", false, "test value", 200, 4);
		$this->assertEquals("Label", $obj->label);
		$this->assertEquals("key", $obj->key);
		$this->assertFalse($obj->required);
		$this->assertEquals("test value", $obj->value);
		$this->assertEquals(200, $obj->sizeLimit);
		$this->assertEquals(4, $obj->index);
	}

	public function testConstructorUsingIntegerValue()
	{
		$obj = new StringInput("Label", "key", false, 43);
		$this->assertEquals("43", $obj->value);
	}

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\StringInputTestDataProvider::renderTestProvider()
     * @param StringInputTestData $data
     * @return void
     */
    function testRender(StringInputTestData $data)
    {
        $this->expectOutputRegex($data->expected_regex);
        $data->obj->render($data->label_override, $data->css_class);
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\StringInputTestDataProvider::renderInputTestProvider()
     * @param StringInputTestData $data
     * @return void
     */
    function testRenderInput(StringInputTestData $data)
    {
        $data->obj->cssClass = $data->css_class;
        $this->expectOutputRegex($data->expected_regex);
        $data->obj->renderInput($data->label_override);
    }


    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\StringInputTestDataProvider::setInputValueTestProvider()
     * @param StringInputTestData $data
     * @return void
     */
    public function testSetInputValue(StringInputTestData $data)
    {
        if (null === $data->expected) {
            $this->assertNull($data->obj->value);
        }
        else {
            $this->assertEquals($data->expected, $data->obj->value);
        }
    }

	public function testSetTemplatePath()
	{
        $original = RequestInput::getInputTemplatePath();

		$path = "/path/to/templates/";
		RequestInput::setTemplateBasePath($path);
		$this->assertEquals($path, $this->obj::getTemplateBasePath());

		$new_path = "/new/path/to/templates/";
		$this->obj::setTemplateBasePath($new_path);
		$this->assertNotEquals($path, $this->obj::getTemplateBasePath());
		$this->assertEquals($new_path, $this->obj::getTemplateBasePath());

        RequestInput::setTemplateBasePath($original);
	}

	public function testTemplateFilename()
	{
		$new_filename = 'new-string-template.php';
		$default = $this->obj::getTemplateFilename();

		// make sure the new value is different from the default
		$this->assertNotEquals($new_filename, $default);

		// test the object property after it has been set to a new value
		StringInput::setTemplateFilename('new-string-template.php');
		$this->assertNotEquals($default, $this->obj::getTemplateFilename());
		$this->assertEquals(StringInput::getTemplateFilename(), $this->obj::getTemplateFilename());

		// parent class's template value should remain unchanged
		$this->assertNotEquals(RequestInput::getTemplateFilename(), $this->obj::getTemplateFilename());
	}
}