<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");
require_once(APP_BASE_DIR . "/tests/Base/DatabaseTestCase.php");

use mysqli;
use Exception;
use Littled\Request\RequestInput;
use PHPUnit\Framework\TestCase;


class RequestInputTest extends TestCase
{
	/** @var mysqli Test database connection. */
	public $mysqli;
	/** @var RequestInput Test RequestInput object. */
	public $obj;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		parent::setUp();
		$this->mysqli = new mysqli();
		if (!defined('MYSQL_HOST') ||
			!defined('MYSQL_USER') ||
			!defined('MYSQL_PASS') ||
			!defined('MYSQL_SCHEMA') ||
			!defined('MYSQL_PORT')) {
			throw new Exception("Database connection properties not found.");
		}
        RequestInput::setTemplateBasePath(SHARED_CMS_TEMPLATE_DIR);
		$this->mysqli->connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);
		$this->obj = new RequestInput("Test", "test_param");
	}

    public function testClearValidationErrors()
    {
        $this->obj->hasErrors = true;
        $this->obj->error = 'Some error message.';
        $this->assertTrue($this->obj->hasErrors);

        $this->obj->clearValidationErrors();
        $this->assertFalse($this->obj->hasErrors);
        $this->assertEquals('', $this->obj->error);
    }

	public function testClearValue()
	{
		$this->obj->clearValue();
		$this->assertNull($this->obj->value);
	}

	public function testEscapeSQL()
	{
		$this->assertEquals("null", $this->obj->escapeSQL($this->mysqli), "Defaults to 'null'");

		$this->obj->value = '';
		$this->assertEquals("''", $this->obj->escapeSQL($this->mysqli), "Empty string");

		$this->obj->value = "abc";
		$this->assertEquals("'abc'", $this->obj->escapeSQL($this->mysqli), "Strings are quoted");
	}

	public function testFormatErrorLabel()
	{
		$this->obj->label = null;
		$this->assertEquals('', $this->obj->formatErrorLabel());

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

    public function testSaveInForm()
    {
        RequestInput::setTemplateFilename('forms/input-elements/hidden-input.php');

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
		$path = "/path/to/templates/";
		$this->obj::setTemplateBasePath($path);
		$this->assertEquals($path, $this->obj::getTemplateBasePath());

		$new_obj = new RequestInput("New label", "new_param");
		$this->assertEquals($path, $new_obj::getTemplateBasePath());
	}
}