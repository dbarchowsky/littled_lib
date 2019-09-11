<?php
namespace Littled\Tests\Request;


use Littled\Exception\ContentValidationException;
use Littled\Request\EmailTextField;
use PHPUnit\Framework\TestCase;
use Littled\Database\MySQLConnection;


/**
 * Class EmailTextField
 * @package Littled\Tests\Request
 */
class EmailTextFieldTest extends TestCase
{
	/** @var EmailTextField Test EmailTextField object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;

	public function setUp() : void
	{
		$this->obj = new EmailTextField("Test email", 'p_email');
		$this->conn = new MySQLConnection();
	}

	public function testConstructor()
	{
		$obj = new EmailTextField("Label", "key", false, "dbarchowsky@gmail.com", 200, 4);
		$this->assertEquals("Label", $obj->label);
		$this->assertEquals("key", $obj->key);
		$this->assertFalse($obj->required);
		$this->assertEquals("dbarchowsky@gmail.com", $obj->value);
		$this->assertEquals(200, $obj->sizeLimit);
		$this->assertEquals(4, $obj->index);
	}

	public function testValidateNotRequired()
	{
		$this->obj->required = false;
		self::assertNull($this->obj->validate());
	}

	public function testValidateDefaultValue()
	{
		$this->obj->required = true;
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateNullValue()
	{
		$this->obj->required = true;
		$this->obj->value = null;
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateEmptyString()
	{
		$this->obj->required = true;
		$this->obj->value = "";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateBlankString()
	{
		$this->obj->required = true;
		$this->obj->value = " ";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateIntegerValue()
	{
		$this->obj->required = true;
		$this->obj->value = 43;
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateMissingDomain()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowsky";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateMissingTLDAndPeriod()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowsky@gmail";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateMissingAtSign()
	{
		$this->obj->required = true;
		$this->obj->value = "gmail.com";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateMissingName()
	{
		$this->obj->required = true;
		$this->obj->value = "@gmail.com";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateMissingTLD()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowksy@gmail.";
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateValidEmails()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowsky@gmail.com";
		self::assertNull($this->obj->validate());

		$this->obj->value = "dbar.chowsky@gmail.com";
		self::assertNull($this->obj->validate());
	}
}