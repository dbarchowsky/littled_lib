<?php
namespace Littled\Tests\Request;

use Littled\Request\IntegerInput;
use Littled\Exception\ContentValidationException;
use PHPUnit\Framework\TestCase;

class IntegerInputTest extends TestCase
{
	public function testConstructor()
	{
		$obj = new IntegerInput("Label", "key", false, 0);
		$this->assertEquals(0, $obj->value);
	}

	public function testConstructorUsingStringValue()
	{
		$obj = new IntegerInput("Label", "key", false, "string value");
		$this->assertEquals(null, $obj->value);
	}

	public function testEscapeSQL()
	{
		$o = new IntegerInput("Test", "test");
		$mysqli = new \mysqli();
		$mysqli->connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);

		$this->assertNull($o->value);
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "Defaults to 'null'");

		$o->value = true;
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "True value translates to '1'");

		$o->value = 'true';
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "String 'true' evaluates to null");

		$o->value = '1';
		$this->assertEquals('1', $o->escapeSQL($mysqli), "String '1' evaluates to '1'\"'");

		$o->value = 1;
		$this->assertEquals('1', $o->escapeSQL($mysqli), "Integer value 1 evaluates to '1'\"'");

		$o->value = false;
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "False value translates to '0'");

		$o->value = 'false';
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "String 'false' evaluates to null");

		$o->value = '0';
		$this->assertEquals('0', $o->escapeSQL($mysqli), "String '0' evaluates to '1'\"'");

		$o->value = 0;
		$this->assertEquals('0', $o->escapeSQL($mysqli), "Integer value 0 evaluates to '1'\"'");

		$o->value = 45;
		$this->assertEquals('45', $o->escapeSQL($mysqli), "Valid integer value.");

		$o->value = '56';
		$this->assertEquals('56', $o->escapeSQL($mysqli), "Valid integer value.");

		$o->value = 3.005;
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "Float value evaluates to 'null'\"'");

		$o->value = '3.005';
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "Float value evaluates to 'null'\"'");

		$o->value = 'foobar';
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "Arbitrary string evaluates to 'null'\"'");
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateValidValues()
	{
		$o = new IntegerInput("Test", "test");

		/* not required, default value (null) */
		$o->required = false;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* not required, empty string value */
		$o->required = false;
		$o->value = '';
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* not required, valid integer value of 1 */
		$o->required = false;
		$o->value = 1;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* not required, valid integer value */
		$o->required = false;
		$o->value = 765;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 1;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 0;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* required, valid integer value */
		$o->required = true;
		$o->value = 5248;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* not required, null value */
		$o->required = false;
		$o->value = null;
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* required, integer string */
		$o->required = true;
		$o->value = '1';
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* required, integer string */
		$o->required = true;
		$o->value = '0';
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);

		/* required, integer string */
		$o->required = true;
		$o->value = '8356';
		$this->assertTrue($o->validate());
		$this->assertFalse($o->hasErrors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredDefaultValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredEmptyStringValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$o->value = '';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredStringValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$o->value = 'foo';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateNotRequiredStringValue()
	{
		$o = new IntegerInput('test label', 'ptest', false);
		$o->value = 'foo';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateNotRequiredFloatValue()
	{
		$o = new IntegerInput('test label', 'ptest', false);
		$o->value = 87.56;
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredFloatValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$o->value = 94.052;
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	public function testSetInputValue()
	{
		$o = new IntegerInput("Test object", "bol_test");
		$this->assertNull($o->value);

		$o->setInputValue(true);
		$this->assertNull($o->value);

		$o->setInputValue('true');
		$this->assertNull($o->value);

		$o->setInputValue('1');
		$this->assertEquals(1, $o->value);

		$o->setInputValue(1);
		$this->assertEquals(1, $o->value);

		$o->setInputValue(false);
		$this->assertNull($o->value);

		$o->setInputValue('false');
		$this->assertNull($o->value);

		$o->setInputValue('0');
		$this->assertEquals(0, $o->value);

		$o->setInputValue(0);
		$this->assertEquals(0, $o->value);

		$o->setInputValue(45);
		$this->assertEquals(45, $o->value);

		$o->setInputValue('45');
		$this->assertEquals(45, $o->value);

		$o->setInputValue(32.7);
		$this->assertNull($o->value);

		$o->setInputValue('32.7');
		$this->assertNull($o->value);

		$o->setInputValue('some arbitrary sting');
		$this->assertNull($o->value);

		$o->setInputValue(null);
		$this->assertNull($o->value);
	}
}
