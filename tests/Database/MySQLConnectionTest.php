<?php
use Littled\Database\MySQLConnection;

define('MYSQL_HOST', 'littledamienii');
define('MYSQL_USER', 'chicot_bfhcom');
define('MYSQL_PASS', 'bP@@BGf17G2z');
define('MYSQL_SCHEMA', 'chicot_bfhcom');

class MySQLConnectionTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		$c = new MySQLConnection();
		try {
			$c->getMysqli();
		}
		catch(\Littled\Exception\ConfigurationUndefinedException $ex) {
			$this->assertEquals('MYSQL_HOST not found in app settings.', $ex->getMessage());
		}
		$rs = $c->fetchRecords("select * from promotion order by id asc");
		$this->assertCount(4, $rs, "Number of records returned by fetchRecords()");
		$row = $rs[0];
		$this->assertEquals('MAY14', $row->code, "Test field from first row.");
	}
}