<?php
namespace Littled\Tests\Filters;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Filters\BooleanContentFilter;
use PHPUnit\Framework\TestCase;
use Exception;
use mysqli;

class BooleanContentFilterTest extends TestCase
{
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
            throw new Exception("Database properties not set.");
        }
		$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);

		/* test null value */
		$cf = new BooleanContentFilter('Test Filter', 'p_test', null);
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test integer value */
		$cf->value = 1;
		$escaped_2 = $cf->escapeSQL($mysqli);
		$this->assertEquals('1', $escaped_2);

		/* test integer value */
		$cf->value = 0;
		$escaped_3 = $cf->escapeSQL($mysqli);
		$this->assertEquals('0', $escaped_3);

		/* test true value */
		$cf->value = true;
		$escaped_4 = $cf->escapeSQL($mysqli);
		$this->assertEquals('1', $escaped_4);

		/* test false value */
		$cf->value = false;
		$escaped_5 = $cf->escapeSQL($mysqli);
		$this->assertEquals('0', $escaped_5);
	}
}
