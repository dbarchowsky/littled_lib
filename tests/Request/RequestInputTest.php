<?php
namespace Littled\Tests\Request;
require_once (realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..")."/Base/DatabaseTestCase.php");

use Littled\Tests\Base\DatabaseTestCase;
use Littled\Request\RequestInput;


class RequestInputTest extends DatabaseTestCase
{
	public function testEscapeSQL()
	{
		$o = new RequestInput("Test", "test");
		$mysqli = new \mysqli();
		$this->loadConnectionVars();
		$mysqli->connect($this->db_host, $this->db_user, $this->db_password, $this->db_schema);

		$this->assertEquals("null", $o->escapeSQL($mysqli), "Defaults to 'null'");

		$o->value = '';
		$this->assertEquals("''", $o->escapeSQL($mysqli), "Empty string");

		$o->value = "abc";
		$this->assertEquals("'abc'", $o->escapeSQL($mysqli), "Strings are quoted");

		$this->unloadConnectionVars();
	}

}