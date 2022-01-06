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
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    function testFetchRecordsMultiple()
    {
        $c = new MySQLConnection();
        $query = "SELECT id, name FROM test_table where name like ? ORDER BY ?";
        $data = $c->fetchRecords($query, 'ss', '%unit%', 'slot');
        $this->assertGreaterThan(0, count($data));
        $this->assertMatchesRegularExpression('/unit/', $data[0]->name);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testFetchRecordsSingle()
    {
        $content_type_id = 2;
        $c = new MySQLConnection();
        $query = "SELECT `name`,`root_dir`,`image_path`,`sub_dir`,`image_label`,`width`,`height`,`med_width`,".
            "`med_height`,`save_mini`,`mini_width`,`mini_height`,`format`,`param_prefix`,`table`,`parent_id`,".
            "`is_cached`,`gallery_thumbnail` FROM `site_section` WHERE id = ?";
        $data = $c->fetchRecords($query, 'i', $content_type_id);
        $this->assertIsArray($data);
        $this->assertEquals('Idea', $data[0]->name);

        $query = "CALL siteSectionExtraPropertiesSelect(?)";
        $data2 = $c->fetchRecords($query, 'i', $content_type_id);
        $this->assertIsArray($data2);
        $this->assertCount(1, $data2);
        $this->assertEquals('idea', $data2[0]->label);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testFetchRecordsWithoutVariables()
    {
        $c = new MySQLConnection();
        $query = 'SELECT * FROM `test_table` ORDER BY `slot` LIMIT 5';
        $data = $c->fetchRecords($query);
        $this->assertGreaterThan(0, count($data));
        $this->assertMatchesRegularExpression('/[A-Za-z]*/', $data[0]->name);
    }

    /**
     * @return void
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     * @throws InvalidQueryException
     * @throws Exception
     */
    function testQueryWithoutVariables()
    {
        date_default_timezone_set('America/Los_Angeles');
        $c = new MySQLConnection();
        $max_id = $this->getMaxTestId($c);

        $query = "UPDATE `test_table` SET `date` = NOW() WHERE id = $max_id";
        $c->query($query);

        $query = "SELECT `date` FROM `test_table` WHERE `id` = $max_id";
        $data = $c->fetchRecords($query);
        $now = date("YmdHi");
        $record_date = date("YmdHi", strtotime($data[0]->date));
        $this->assertEquals($now, $record_date);
    }

    /**
     * @return void
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     * @throws InvalidQueryException
     */
    function testQueryWithVariables()
    {
        $c = new MySQLConnection();
        $max_id = $this->getMaxTestId($c);

        $query = 'INSERT INTO `test_table` (`name`, `bool_col`, `slot`) VALUES (?,?,?)';
        $c->query($query, 'sii', 'unit_test', 1, 20);

        $new_id = $this->getMaxTestId($c);
        $this->assertNotEquals($max_id, $new_id);
    }

    /**
     * @param MySQLConnection $c
     * @return int|null
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    protected function getMaxTestId(MySQLConnection $c): ?int
    {
        $query = 'SELECT MAX(`id`) AS `max_id` FROM `test_table`';
        $result = $c->getMysqli()->query($query);
        if ($result===false) {
            throw new Exception("Error getting max id: ".$c->getMysqli()->error);
        }
        if ($result->num_rows > 0) {
            $max_id = $result->fetch_object()->max_id;
        }
        else {
            $max_id = null;
        }
        $result->free();
        return $max_id;
    }
}