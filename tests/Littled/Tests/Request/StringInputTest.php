<?php
namespace Littled\Tests\Request;


use PHPUnit\Framework\TestCase;
use Littled\Database\MySQLConnection;
use Littled\Request\StringInput;


/**
 * Class StringInputTest
 * @package Littled\Tests\Request
 */
class StringInputTest extends TestCase
{
	/** @var StringInput Test DateInput object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;

	public function setUp() : void
	{
		$this->obj = new StringInput("Test date", 'p_date');
		$this->conn = new MySQLConnection();
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

	public function testCollectPostData()
	{

	}

	public function testConstructorUsingIntegerValue()
	{
		$obj = new StringInput("Label", "key", false, 43);
		$this->assertEquals("43", $obj->value);
	}

	public function testSetInputValue()
	{
		$this->obj->setInputValue('');
		$this->assertEquals('', $this->obj->value);

		$this->obj->setInputValue('test value');
		$this->assertEquals('test value', $this->obj->value);

		$this->obj->setInputValue(4573);
		$this->assertEquals('4573', $this->obj->value);

		$this->obj->setInputValue(null);
		$this->assertEquals('', $this->obj->value);

		$this->obj->setInputValue(873.03);
		$this->assertEquals('873.03', $this->obj->value);
	}

	public function testSetTemplatePath()
	{
		$path = "/path/to/templates/";
		\Littled\Request\RequestInput::setTemplateBasePath($path);
		$this->assertEquals($path, $this->obj::getTemplateBasePath());

		$new_path = "/new/path/to/templates/";
		$this->obj::setTemplateBasePath($new_path);
		$this->assertNotEquals($path, $this->obj::getTemplateBasePath());
		$this->assertEquals($new_path, $this->obj::getTemplateBasePath());
	}
}