<?php
namespace Littled\Tests\Database;

require_once(realpath(dirname(__FILE__) . '/../../') . '/_dbo/connections/test_data.php');

use Littled\Database\MySQLConnection;
use Littled\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;


class MySQLConnectionTest extends TestCase
{
	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testConnection()
	{
		$c = new MySQLConnection();
		try {
			$c->getMysqli();
		}
		catch(\Littled\Exception\ConfigurationUndefinedException $ex) {
			$this->assertEquals('MYSQL_HOST not found in app settings.', $ex->getMessage());
		}
		$query = "SELECT * FROM `test_data` ORDER BY id ASC";
		$rs = $c->fetchRecords($query);
		$this->assertEquals(count($rs), 4,"Number of records returned by fetchRecords()");
		$row = $rs[0];
		$this->assertEquals('MAY14', $row->code, "Test field from first row.");
	}

	/**
	 * @throws \Exception
	 */
	public function testDefaultConnection()
	{
		$c = new MySQLConnection();
		$c->connectToDatabase();
		$this->assertTrue($c->hasConnection());
	}

	/**
	 * @throws \Exception
	 */
	public function testInvalidPortByHostName()
	{
		$c = new MySQLConnection();
		$this->expectException(ConnectionException::class);
		$c->connectToDatabase(TEST_HOST_BY_NAME, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, TEST_INVALID_PORT);
		$this->assertFalse($c->hasConnection());
	}

	/**
	 * @throws ConnectionException
	 */
	public function testInvalidPortByIP()
	{
		$c = new MySQLConnection();
		$this->expectException(ConnectionException::class);
		$c->connectToDatabase(TEST_HOST_BY_IP, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, TEST_INVALID_PORT);
		$this->assertFalse($c->hasConnection());
	}

	/**
	 * @throws \Exception
	 */
	public function testNonDefaultPort()
	{
		$c = new MySQLConnection();
		$c->connectToDatabase(TNDP_HOST, TNDP_USER, TNDP_PASSWORD, TNDP_SCHEMA, TNDP_PORT);
		$this->assertTrue($c->hasConnection());
	}

	/**
	 * @throws \Exception
	 */
	public function testEscapeSQLValue()
	{
		$c = new MySQLConnection();

		/* test that no database connection doesn't throw Exception */
		$escaped = $c->escapeSQLValue(200);
		$this->assertEquals('\'200\'', $escaped);
		$this->assertTrue($c->hasConnection());

		/* test null value */
		$escaped = $c->escapeSQLValue(null);
		$this->assertEquals('null', $escaped);

		/* test empty string */
		$escaped = $c->escapeSQLValue('');
		$this->assertEquals('\'\'', $escaped);

		/* test non-empty string */
		$escaped = $c->escapeSQLValue('foo');
		$this->assertEquals('\'foo\'', $escaped);

		/* test wildcard */
		$escaped = $c->escapeSQLValue('foo%bar');
		$this->assertEquals('\'foo%bar\'', $escaped);

		/* test true value */
		$escaped = $c->escapeSQLValue(true);
		$this->assertEquals('1', $escaped);

		/* test false value */
		$escaped = $c->escapeSQLValue(false);
		$this->assertEquals('0', $escaped);
	}
}