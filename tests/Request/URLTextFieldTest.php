<?php
namespace Littled\Tests\Request;


use Littled\Exception\ContentValidationException;
use PHPUnit\Framework\TestCase;
use Littled\Database\MySQLConnection;
use Littled\Request\URLTextField;


/**
 * Class URLTextFieldTest
 * @package Littled\Tests\Request
 */
class URLTextFieldTest extends TestCase
{
	/** @var URLTextField Test DateInput object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;

	public function setUp()
	{
		$this->obj = new URLTextField("Test date", 'p_date');
		$this->conn = new MySQLConnection();
	}

	/**
	 * @throws \Littled\Exception\ContentValidationException
	 */
	public function testValidateValidURL()
	{
		$url = "http://www.littledamien.com";
		$this->obj->setInputValue($url);
		$this->obj->validate();
		$this->assertEquals($url, $this->obj->value);
	}

	public function testValidateInvalidURL()
	{
		$url = "some garbage string";
		$this->obj->setInputValue($url);
		try {
			$this->obj->validate();
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals("Test date does not appear to be a valid URL.", $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateTransformedURL()
	{
		$url = "https://littledamien.com/<scr"."ipt>test</scr"."ipt>";
		$this->obj->setInputValue($url);
		$this->obj->validate();
		$this->assertEquals("https://littledamien.com/test", $this->obj->value);
	}
}