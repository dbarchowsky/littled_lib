<?php
namespace Littled\Tests\Request;
require_once (realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..")."/Base/DatabaseTestCase.php");

use Littled\Request\RequestInput;
use PHPUnit\Framework\TestCase;


class RequestInputTest extends TestCase
{
	/** @var \mysqli Test database connection. */
	public $mysqli;
	/** @var RequestInput Test RequestInput object. */
	public $obj;

	public function setUp()
	{
		$this->mysqli = new \mysqli();
		$this->mysqli->connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA);
		$this->obj = new RequestInput("Test", "test_param");
	}

	public function testEscapeSQL()
	{
		$this->assertEquals("null", $this->obj->escapeSQL($this->mysqli), "Defaults to 'null'");

		$this->obj->value = '';
		$this->assertEquals("''", $this->obj->escapeSQL($this->mysqli), "Empty string");

		$this->obj->value = "abc";
		$this->assertEquals("'abc'", $this->obj->escapeSQL($this->mysqli), "Strings are quoted");
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