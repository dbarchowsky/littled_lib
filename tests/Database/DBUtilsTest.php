<?php

namespace Littled\Tests\Database;

use Littled\Database\DBUtils;
use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

class DBUtilsTest extends TestCase
{
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
}