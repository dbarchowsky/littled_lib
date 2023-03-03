<?php /** @noinspection PhpUndefinedConstantInspection */

namespace Littled\Tests\Request;
require_once(APP_BASE_DIR . "/Tests/Base/DatabaseTestCase.php");

use Littled\Tests\DataProvider\Request\RequestInputTestDataProvider;
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
     * @dataProvider \Littled\Tests\DataProvider\Request\RequestInputTestDataProvider::escapeSQLTestProvider()
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
     * @dataProvider \Littled\Tests\DataProvider\Request\RequestInputTestDataProvider::formatClassAttributeTestProvider()
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

	public function testIsEmpty()
	{
		$this->assertTrue($this->obj->isEmpty());

		$this->obj->value = '';
		$this->assertTrue($this->obj->isEmpty());

		$this->obj->value = ' ';
		$this->assertTrue($this->obj->isEmpty());

		$this->obj->value = 1;
		$this->assertFalse($this->obj->isEmpty());

		$this->obj->value = 0;
		$this->assertFalse($this->obj->isEmpty());

		$this->obj->value = 16;
		$this->assertFalse($this->obj->isEmpty());

		$this->obj->value = 16.23;
		$this->assertFalse($this->obj->isEmpty());

		$this->obj->value = -8;
		$this->assertFalse($this->obj->isEmpty());

		$this->obj->value = false;
		$this->assertFalse($this->obj->isEmpty());

		$this->obj->value = true;
		$this->assertFalse($this->obj->isEmpty());
	}

    function testGetPreparedStatementTypeIdentifier()
    {
        $this->assertEquals('s', RequestInput::getPreparedStatementTypeIdentifier());
    }

    public function testSaveInForm()
    {
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
        RequestInput::setTemplateFilename('hidden-input.php');

        // test basic template output
        $expected = "<input type=\"hidden\" name=\"{$this->obj->key}\" value=\"{$this->obj->value}\" />\n";
        $this->expectOutputString($expected);
        $this->obj->saveInForm();

        // test output with index value set to 0
        // N.B. output is cumulative
        $this->obj->index = 0;
        $expected = $expected."<input type=\"hidden\" name=\"{$this->obj->key}[{$this->obj->index}]\" value=\"{$this->obj->value}\" />\n";
        $this->expectOutputString($expected);
        $this->obj->saveInForm();

        // test output with index value set to non-zero
        $this->obj->index = 4;
        $expected = $expected."<input type=\"hidden\" name=\"{$this->obj->key}[{$this->obj->index}]\" value=\"{$this->obj->value}\" />\n";
        $this->expectOutputString($expected);
        $this->obj->saveInForm();

        // test output with index value set to string
        $this->obj->index = 'hello';
        $expected = $expected."<input type=\"hidden\" name=\"{$this->obj->key}['{$this->obj->index}']\" value=\"{$this->obj->value}\" />\n";
        $this->expectOutputString($expected);
        $this->obj->saveInForm();
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