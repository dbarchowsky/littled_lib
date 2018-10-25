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

	public function setUp()
	{
		$this->obj = new StringInput("Test date", 'p_date');
		$this->conn = new MySQLConnection();
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

}