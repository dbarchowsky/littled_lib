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
		$this->mysqli->connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);
		$this->obj = new RequestInput("Test", "test_param");
	}

	public function testClearValue()
	{
		$this->obj->clearValue();
		self::assertNull($this->obj->value);
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
		self::assertEquals('', $this->obj->formatErrorLabel());

		$this->obj->label = '';
		self::assertEquals('', $this->obj->formatErrorLabel());

		$this->obj->label = 'all lower case';
		self::assertEquals('All lower case', $this->obj->formatErrorLabel());

		$this->obj->label = 'ALL UPPER CASE';
		self::assertEquals('All upper case', $this->obj->formatErrorLabel());

		$this->obj->label = 'Mixed Case';
		self::assertEquals('Mixed case', $this->obj->formatErrorLabel());
	}

	public function testIsEmpty()
	{
		self::assertTrue($this->obj->isEmpty());

		$this->obj->value = '';
		self::assertTrue($this->obj->isEmpty());

		$this->obj->value = ' ';
		self::assertTrue($this->obj->isEmpty());

		$this->obj->value = 1;
		self::assertFalse($this->obj->isEmpty());

		$this->obj->value = 0;
		self::assertFalse($this->obj->isEmpty());

		$this->obj->value = 16;
		self::assertFalse($this->obj->isEmpty());

		$this->obj->value = 16.23;
		self::assertFalse($this->obj->isEmpty());

		$this->obj->value = -8;
		self::assertFalse($this->obj->isEmpty());

		$this->obj->value = false;
		self::assertFalse($this->obj->isEmpty());

		$this->obj->value = true;
		self::assertFalse($this->obj->isEmpty());
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