<?php
namespace Littled\Tests\Request;
require_once (realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..")."/Base/DatabaseTestCase.php");

use Littled\Request\BooleanInput;
use Littled\Tests\Base\DatabaseTestCase;
use Littled\Exception\ContentValidationException;

class BooleanInputTest extends DatabaseTestCase
{
	public function testCollectValue()
	{
		$key = "test";
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = array($key => 'true');
		$test = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
		$this->assertEquals('true', $test);
	}

	public function testEscapeSQL()
	{
		$o = new BooleanInput("Test", "test");
		$mysqli = new \mysqli();
		$this->loadConnectionVars();
		$mysqli->connect($this->db_host, $this->db_user, $this->db_password, $this->db_schema);

		$this->assertEquals("null", $o->escapeSQL($mysqli), "Defaults to 'null'");

		$o->value = true;
		$this->assertEquals("1", $o->escapeSQL($mysqli), "True value translates to 1");

		$o->value = false;
		$this->assertEquals("0", $o->escapeSQL($mysqli), "False value translates to 0");

		$o->value = '1';
		$this->assertEquals("", $o->escapeSQL($mysqli), "Invalid value (\"1\") translates to empty string");

		$o->value = 1;
		$this->assertEquals("", $o->escapeSQL($mysqli), "Invalid value (1) translates to empty string");

		$o->value = 'true';
		$this->assertEquals("", $o->escapeSQL($mysqli), "Invalid value (\"true\") translates to empty string");

		$this->unloadConnectionVars();
	}

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

	public function testValidateExceptionOnUnset()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	public function testValidateExceptionOnNull()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->value = null;
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	public function testValidateExceptionOnBadValueWhenRequired()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->value = 'true';
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}

	public function testValidateExceptionOnBadValueWhenNotRequired()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = false;
		$o->value = 'true';
		$o->validate();
		$this->assertTrue($o->hasErrors);
	}
}
