<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Request\FloatInput;
use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;
use Littled\Tests\Request\DataProvider\FloatInputTestData;
use Littled\Tests\TestExtensions\ContentValidationTestCase;
use Exception;
use mysqli;

class FloatInputTest extends ContentValidationTestCase
{
	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
	}

	public function testConstructor()
	{
		$obj = new FloatInput("Label", "key", false, 0);
		$this->assertEquals(0, $obj->value);
	}

	public function testConstructorUsingStringValue()
	{
		$obj = new FloatInput("Label", "key", false, "string value");
		$this->assertEquals(null, $obj->value);
	}

    /**
     * @throws Exception
     */
    public function testEscapeSQL()
	{
        if (!defined('MYSQL_HOST') ||
            !defined('MYSQL_USER') ||
            !defined('MYSQL_PASS') ||
            !defined('MYSQL_SCHEMA') ||
            !defined('MYSQL_PORT')) {
            throw new Exception('Database connection properties not defined.');
        }

		$o = new FloatInput("Test", "test");
		$mysqli = new mysqli();
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
		$this->assertEquals('3.005', $o->escapeSQL($mysqli), "Float value evaluates to 'null'\"'");

		$o->value = '3.005';
		$this->assertEquals('3.005', $o->escapeSQL($mysqli), "Float value evaluates to 'null'\"'");

		$o->value = 'foobar';
		$this->assertEquals('NULL', $o->escapeSQL($mysqli), "Arbitrary string evaluates to 'null'\"'");
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateValidValues()
	{
		$o = new FloatInput("Test", "test");

		/* not required, default value (null) */
		$o->required = false;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* not required, empty string value */
		$o->required = false;
		$o->value = '';
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* not required, valid integer value of 1 */
		$o->required = false;
		$o->value = 1;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* not required, valid integer value */
		$o->required = false;
		$o->value = 765;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 1;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 0;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, valid integer value */
		$o->required = true;
		$o->value = 5248;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* not required, null value */
		$o->required = false;
		$o->value = null;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, integer string */
		$o->required = true;
		$o->value = '1';
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, integer string */
		$o->required = true;
		$o->value = '0';
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, integer string */
		$o->required = true;
		$o->value = '8356';
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, float value */
		$o->required = true;
		$o->value = 99.06;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		/* required, float string */
		$o->required = true;
		$o->value = '99.06';
		$o->validate();
		$this->assertFalse($o->hasErrors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredDefaultValue()
	{
		$o = new FloatInput('test label', 'ptest', true);
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredEmptyStringValue()
	{
		$o = new FloatInput('test label', 'ptest', true);
		$o->value = '';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredStringValue()
	{
		$o = new FloatInput('test label', 'ptest', true);
		$o->value = 'foo';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateNotRequiredStringValue()
	{
		$o = new FloatInput('test label', 'ptest', false);
		$o->value = 'foo';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::renderTestProvider()
	 * @param FloatInputTestData $data
	 * @return void
	 */
	function testRender(FloatInputTestData $data)
	{
		$this->expectOutputRegex($data->expected_regex);
		$data->obj->render();
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::setInputValueTestProvider()
	 * @param FloatInputTestData $data
	 * @return void
	 */
	public function testSetInputValue(FloatInputTestData $data)
	{
		if (null === $data->expected) {
			$this->assertNull($data->obj->value);
		}
		else {
			$this->assertEquals($data->expected, $data->obj->value);
		}
	}
}
