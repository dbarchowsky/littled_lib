<?php
namespace Littled\Tests\Database;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;
use PHPUnit\Framework\TestCase;
use Exception;

class MySQLConnectionTest extends TestCase
{
	/**
	 * @throws InvalidQueryException
	 */
	public function testConnection()
	{
		$c = new MySQLConnection();
		try {
			$c->getMysqli();
		}
		catch(ConfigurationUndefinedException $ex) {
			$this->assertEquals('MYSQL_HOST not found in app settings.', $ex->getMessage());
		}
		$query = "SELECT * FROM `article` ORDER BY id";
		$rs = $c->fetchRecords($query);
		$this->assertGreaterThan(0, count($rs), "Number of records returned by fetchRecords()");
		$row = $rs[0];
		$this->assertIsNumeric($row->id);
        $this->assertIsString($row->title);
	}

	/**
	 * @throws Exception
	 */
	public function testDefaultConnection()
	{
		$c = new MySQLConnection();
		$c->connectToDatabase();
		$this->assertTrue($c->hasConnection());
	}

	/**
	 * @throws Exception
	 */
	public function testEscapeSQLValue()
	{
		$c = new MySQLConnection();

		/* test that no database connection doesn't throw Exception */
		$escaped = $c->escapeSQLValue(200);
		$this->assertEquals('200', $escaped);
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

    /**
     * @return void
     * @throws InvalidQueryException
     */
    function testFetchRecords()
    {
        $query = "CALL siteSectionExtraPropertiesSelect(2)";
        $c = new MySQLConnection();
        $data = $c->fetchRecords($query);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
    }
}