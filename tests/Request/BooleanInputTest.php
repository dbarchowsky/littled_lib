<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use mysqli;
use Exception;
use Littled\Request\RequestInput;
use Littled\Request\BooleanInput;
use Littled\Exception\ContentValidationException;
use PHPUnit\Framework\TestCase;

class BooleanInputTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(SHARED_CMS_TEMPLATE_DIR);
    }

    /**
	 * @throws Exception
	 */
	public function testEscapeSQL()
	{
		$o = new BooleanInput("Test", "test");
		$mysqli = new mysqli();
        if (!defined('MYSQL_HOST') ||
            !defined('MYSQL_USER') ||
            !defined('MYSQL_PASS') ||
            !defined('MYSQL_SCHEMA') ||
            !defined('MYSQL_PORT')) {
            throw new Exception("Database connection properties not found.");
        }
		$mysqli->connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);

		$this->assertNull($o->value);
		$this->assertEquals("null", $o->escapeSQL($mysqli), "Defaults to 'null'");

		$o->value = true;
		$this->assertEquals("1", $o->escapeSQL($mysqli), "True value translates to '1'");

		$o->value = 'true';
		$this->assertEquals("1", $o->escapeSQL($mysqli), "String 'true' evaluates to '1'\"'");

		$o->value = '1';
		$this->assertEquals("1", $o->escapeSQL($mysqli), "String '1' evaluates to '1'\"'");

		$o->value = 1;
		$this->assertEquals("1", $o->escapeSQL($mysqli), "Integer value 1 evaluates to '1'\"'");

		$o->value = false;
		$this->assertEquals("0", $o->escapeSQL($mysqli), "False value translates to '0'");

		$o->value = 'false';
		$this->assertEquals("0", $o->escapeSQL($mysqli), "String 'false' evaluates to '1'\"'");

		$o->value = '0';
		$this->assertEquals("0", $o->escapeSQL($mysqli), "String '0' evaluates to '1'\"'");

		$o->value = 0;
		$this->assertEquals("0", $o->escapeSQL($mysqli), "Integer value 0 evaluates to '1'\"'");

		$o->value = 45;
		$this->assertEquals("null", $o->escapeSQL($mysqli), "Integer value other than 0 or 1 evaluates to 'null'\"'");

		$o->value = 1.005;
		$this->assertEquals("null", $o->escapeSQL($mysqli), "Float value evaluates to 'null'\"'");

		$o->value = 'foobar';
		$this->assertEquals("null", $o->escapeSQL($mysqli), "Arbitrary string evaluates to 'null'\"'");
	}

    public function testFormatValueMarkup()
    {
        $o = new BooleanInput('Boolean Label', 'booleanTest');
        $this->assertEquals('', $o->formatValueMarkup());

        $o->value = 1;
        $this->assertEquals('1', $o->formatValueMarkup());
        $o->value = true;
        $this->assertEquals('1', $o->formatValueMarkup());
        $o->value = 'on';
        $this->assertEquals('1', $o->formatValueMarkup());

        $o->value = 0;
        $this->assertEquals('0', $o->formatValueMarkup());
        $o->value = false;
        $this->assertEquals('0', $o->formatValueMarkup());
        $o->value = 'off';
        $this->assertEquals('0', $o->formatValueMarkup());

        $o->value = null;
        $this->assertEquals('', $o->formatValueMarkup());
    }

	public function testIsEmpty()
	{
		$o = new BooleanInput('Test', 'test');

		$o->value = true;
		self::assertTrue($o->isEmpty());

		$o->value = false;
		self::assertTrue($o->isEmpty());
	}

    public function testSaveInForm()
    {
        RequestInput::setTemplateFilename('forms/input-elements/hidden-input.php');
        $o = new BooleanInput("Boolean Test", "booleanTest");
        $expected = "<input type=\"hidden\" name=\"$o->key\" value=\"\" />\n";
        $this->expectOutputString($expected);
        $o->saveInForm();

        $o->value = true;
        $expected = $expected."<input type=\"hidden\" name=\"$o->key\" value=\"1\" />\n";
        $this->expectOutputString($expected);
        $o->saveInForm();

        $o->value = false;
        $expected = $expected."<input type=\"hidden\" name=\"$o->key\" value=\"0\" />\n";
        $this->expectOutputString($expected);
        $o->saveInForm();
    }

	/**
	 * @throws ContentValidationException
	 */
	public function testValidate()
	{
		$o = new BooleanInput("Test", "test");

		/* tests that should not cause validation errors */
		$o->required = false;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		$o->required = false;
		$o->value = true;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		$o->required = false;
		$o->value = false;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		$o->required = false;
		$o->value = null;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		$o->required = true;
		$o->value = true;
		$o->validate();
		$this->assertFalse($o->hasErrors);

		$o->required = true;
		$o->value = false;
		$o->validate();
		$this->assertFalse($o->hasErrors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnUnset()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnNull()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->value = null;
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnBadValueWhenRequired()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->value = 'true';
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnBadValueWhenNotRequired()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = false;
		$o->value = 'true';
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	public function testSetInputValue()
	{
		$o = new BooleanInput("Test object", "bol_test");
		$this->assertNull($o->value);

		$o->setInputValue(true);
		$this->assertTrue($o->value);

		$o->setInputValue('true');
		$this->assertTrue($o->value);

		$o->setInputValue('1');
		$this->assertTrue($o->value);

		$o->setInputValue(1);
		$this->assertTrue($o->value);

		$o->setInputValue(false);
		$this->assertFalse($o->value);

		$o->setInputValue('false');
		$this->assertFalse($o->value);

		$o->setInputValue('0');
		$this->assertFalse($o->value);

		$o->setInputValue(0);
		$this->assertFalse($o->value);

		$o->setInputValue(45);
		$this->assertNull($o->value);

		$o->setInputValue(32.7);
		$this->assertNull($o->value);

		$o->setInputValue('some arbitrary sting');
		$this->assertNull($o->value);
	}
}
