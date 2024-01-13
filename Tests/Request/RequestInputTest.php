<?php /** @noinspection PhpUndefinedConstantInspection */

namespace LittledTests\Request;
require_once(APP_BASE_DIR . "/Tests/Base/DatabaseTestCase.php");

use LittledTests\DataProvider\Request\RequestInputTestData;
use LittledTests\DataProvider\Request\RequestInputTestDataProvider;
use mysqli;
use Exception;
use Littled\Request\RequestInput;
use PHPUnit\Framework\TestCase;


class RequestInputTest extends TestCase
{
	protected static mysqli $mysqli;
	public RequestInput $obj;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$mysqli->close();
    }

    /**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		parent::setUp();
        RequestInput::setTemplateBasePath(SHARED_CMS_TEMPLATE_DIR);
		$this->obj = new RequestInput("Test", "test_param");
	}

    public function testClearValidationErrors()
    {
        $this->obj->has_errors = true;
        $this->obj->error = 'Some error message.';
        $this->assertTrue($this->obj->has_errors);

        $this->obj->clearValidationErrors();
        $this->assertFalse($this->obj->has_errors);
        $this->assertEquals('', $this->obj->error);
    }

	public function testClearValue()
	{
		$this->obj->clearValue();
		$this->assertNull($this->obj->value);
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Request\RequestInputTestDataProvider::escapeSQLTestProvider()
     * @param $expected
     * @param $value
     * @param bool $include_quotes
     * @return void
     */
	public function testEscapeSQL($expected, $value, bool $include_quotes=false)
	{
        $i = new RequestInput('Test Input', 'ti');
        if ($value !== '[use default]') {
            $i->value = $value;
        }
		$this->assertSame($expected, $i->escapeSQL(static::$mysqli, $include_quotes));
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Request\RequestInputTestDataProvider::formatAttributeMarkupTestProvider()
     * @param string $expected
     * @param array $attributes
     * @return void
     */
    function testFormatAttributeMarkup(string $expected, array $attributes)
    {
        $o = new RequestInput('Test Label', 'testKey');
        foreach($attributes as $key => $value) {
            $o->setAttribute($key, $value);
        }
        $this->assertEquals($expected, $o->formatAttributesMarkup());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\RequestInputTestDataProvider::formatClassAttributeTestProvider()
     * @param string $expected
     * @param string $css_class
     * @param string $class_override
     * @param bool $has_error
     * @param string $element
     * @return void
     */
    function testFormatClassAttribute(string $expected, string $css_class='', string $class_override='', bool $has_error=false, string $element='default')
    {
        $o = new RequestInput(RequestInputTestDataProvider::TEST_LABEL, RequestInputTestDataProvider::TEST_KEY);
        if ($element==='default' || $element==='input') {
            $o->setInputCSSClass($css_class);
        }
        else {
            $o->setContainerCSSClass($css_class);
        }
        if ($has_error) {
            $o->error = 'Request input error';
            $o->has_errors = true;
        }
        switch ($element) {
            case 'input':
                $this->assertEquals($expected, $o->formatClassAttributeMarkup($class_override, [$o, 'getInputCssClass']));
                break;
            case 'container':
                $this->assertEquals($expected, $o->formatClassAttributeMarkup($class_override, [$o, 'getContainerCssClass']));
                break;
            default:
                $this->assertEquals($expected, $o->formatClassAttributeMarkup($class_override));
                break;
        }
    }

	public function testFormatErrorLabel()
	{
		$this->obj->label = '';
		$this->assertEquals('', $this->obj->formatErrorLabel());

		$this->obj->label = 'all lower case';
		$this->assertEquals('All lower case', $this->obj->formatErrorLabel());

		$this->obj->label = 'ALL UPPER CASE';
		$this->assertEquals('All upper case', $this->obj->formatErrorLabel());

		$this->obj->label = 'Mixed Case';
		$this->assertEquals('Mixed case', $this->obj->formatErrorLabel());
	}

    public function testFormatIndexMarkup()
    {
        $this->assertEquals('', $this->obj->formatIndexMarkup());

        $this->obj->index = 0;
        $this->assertEquals("[0]", $this->obj->formatIndexMarkup());

        $this->obj->index = 4;
        $this->assertEquals("[4]", $this->obj->formatIndexMarkup());

        $this->obj->index = 'str';
        $this->assertEquals("['str']", $this->obj->formatIndexMarkup());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\RequestInputTestDataProvider::isEmptyTestProvider()
     * @param bool $expected
     * @param $value
     * @return void
     */
    public function testIsEmpty(bool $expected, $value)
	{
        if ($value!==null) {
            $this->obj->value = $value;
        }
        $this->assertSame($expected, $this->obj->isEmpty());
	}

    function testGetPreparedStatementTypeIdentifier()
    {
        $this->assertEquals('s', RequestInput::getPreparedStatementTypeIdentifier());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\RequestInputTestDataProvider::saveInFormTestProvider()
     * @return void
     */
    public function testSaveInForm(RequestInputTestData $data)
    {
        $original_path = RequestInput::getTemplateBasePath();
        $original_template = RequestInput::getTemplateFilename();
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
        RequestInput::setTemplateFilename('hidden-input.php');

        // test basic template output
        $o = new RequestInput($data->label, $data->key, $data->required, $data->value, 20, $data->index);
        $this->expectOutputRegex($data->expected);
        $o->saveInForm();

        RequestInput::setTemplateBasePath($original_path);
        RequestInput::setTemplateFilename($original_template);
    }

    function testSetAttribute()
    {
        $o = new RequestInput('Input Test', 'testKey');
        $this->assertCount(0, $o->attributes);

        $r = $o->setAttribute('data-tid', 3);
        $this->assertEquals($o, $r);
        $this->assertCount(1, $o->attributes);
        $this->assertEquals(3, $o->attributes['data-tid']);

        $o->setAttribute('color', 'blue');
        $this->assertCount(2, $o->attributes);
        $this->assertEquals('blue', $o->attributes['color']);

        $o->setAttribute('color', 'red');
        $this->assertCount(2, $o->attributes);
        $this->assertEquals('red', $o->attributes['color']);
    }

    public function testSetContainerCSSClass()
    {
        $o = new RequestInput('Container Test', 'ctKey');
        $original_class = $o->container_css_class;
        $returned = $o->setContainerCSSClass('new-container-class');
        $this->assertNotEquals($original_class, $o->container_css_class);
        $this->assertSame($returned, $o);
    }

    public function testSetInputCSSClass()
    {
        $o = new RequestInput('Test Label', 'testKey');
        $original_class = $o->input_css_class;
        $returned = $o->setInputCSSClass('new-class');
        $this->assertNotEquals($original_class, $o->input_css_class);
        $this->assertSame($returned, $o);
    }

	public function testSetTemplatePath()
	{
        $original = $this->obj::getInputTemplatePath();

		$path = "/path/to/templates/";
		$this->obj::setTemplateBasePath($path);
		$this->assertEquals($path, $this->obj::getTemplateBasePath());

		$new_obj = new RequestInput("New label", "new_param");
		$this->assertEquals($path, $new_obj::getTemplateBasePath());

        $this->obj::setTemplateBasePath($original);
	}
}