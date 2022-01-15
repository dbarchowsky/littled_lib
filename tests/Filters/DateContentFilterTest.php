<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Filters\DateContentFilter;
use PHPUnit\Framework\TestCase;
use Exception;
use mysqli;

class DateContentFilterTest extends TestCase
{
    function testConstructor()
    {
        // null for initial value
        $df = new DateContentFilter('date filter', 'dateFilter', null, null, 'cookieKey');
        $this->assertNull($df->value);

        // empty string for initial value
        $df = new DateContentFilter('date filter', 'dateFilter', '', null, 'cookieKey');
        $this->assertNull($df->value);
    }

    /**
     * @return void
     * @throws Exception
     */
	public function testEscapeSQL()
	{
        if (!defined('MYSQL_HOST') ||
            !defined('MYSQL_USER') ||
            !defined('MYSQL_PASS') ||
            !defined('MYSQL_SCHEMA') ||
            !defined('MYSQL_PORT')) {
            throw new Exception("Database connection not defined.");
        }
		$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);

		/* test null value */
		$cf = new DateContentFilter('Test Filter', 'p_test', null, 50);
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('NULL', $escaped);

		/* test empty string */
		$cf->value = '';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('NULL', $escaped);

		/* test valid date string */
		$cf->value = '6/15/2016';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals("'2016-06-15'", $escaped);

		/* test invalid date string */
		$cf->value = 'fdfjdlfadlfdslfjdl';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('NULL', $escaped);

		/* test integer value */
		$cf->value = 45;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('NULL', $escaped);

		/* test bool value */
		$cf->value = true;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('NULL', $escaped);
	}
}
