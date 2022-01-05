<?php

namespace Littled\Tests\Database;

use Littled\Database\DBUtils;
use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

class DBUtilsTest extends TestCase
{
    function testDisplayQueryOptions()
    {
        // Test using table from database
        $query = 'SELECT id, name FROM `contact_status` ORDER BY id';
        $selected = array(null);
        $this->expectOutputRegex('/<option value="2">attempted contact<\/option>/');
        DBUtils::displayQueryOptions($query, $selected);
        ob_clean();

        // Set one of the options as "selected"
        $selected = array(4);
        $this->expectOutputRegex('/<option value="4" selected="selected">received rejection<\/option>/');
        DBUtils::displayQueryOptions($query, $selected);
        ob_clean();

        // query result set columns do not match what's expected in routine
        $query = 'SELECT id, name AS `value` FROM `contact_status` ORDER BY id';
        $this->expectOutputRegex('/<option value="" disabled="disabled" class="alert alert-error">Error retrieving options: Missing required.*<\/option>/');
        DBUtils::displayQueryOptions($query, $selected);
    }

	function testFormatSqlDate()
	{
		$now = time();
		$sql_date = DBUtils::formatSqlDate();

		$this->assertEquals(date('Y-m-d H:i:s', $now), $sql_date);

		$date = strtotime('May 13, 2013 2:16 am');
		$this->assertEquals('2013-05-13 02:16:00', DBUtils::formatSqlDate($date));

		$str_date = '06/04/2008 2:05 pm';
		$this->assertEquals('2008-06-04 14:05:00', DBUtils::formatSqlDate($str_date));
	}

    function testIsProcedure()
    {
        $this->assertFalse(DBUtils::isProcedure('SELECT * FROM `article`'));
        $this->assertFalse(DBUtils::isProcedure('DELETE FROM `article` WHERE id = 44'));
        $this->assertFalse(DBUtils::isProcedure("INSERT INTO `article` (title, text) VALUES ('hello', 'hello hello')"));
        $this->assertTrue(DBUtils::isProcedure("CALL articleListingsSelect(1, 50, '%search%', @total_results)"));
    }
}