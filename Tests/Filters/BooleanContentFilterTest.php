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
     * @dataProvider \Littled\Tests\Filters\DataProvider\BooleanContentFilterTestDataProvider::escapeSQLTestProvider()
     * @return void
     * @throws Exception
     */
	public function testEscapeSQL(string $expected, $value, string $msg='')
	{
        if (!defined('MYSQL_HOST') ||
            !defined('MYSQL_USER') ||
            !defined('MYSQL_PASS') ||
            !defined('MYSQL_SCHEMA') ||
            !defined('MYSQL_PORT')) {
            throw new Exception("Database properties not set.");
        }
		$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);

		$cf = new BooleanContentFilter('Test Filter', 'p_test');
        $cf->value = $value;
		$this->assertEquals($expected, $cf->escapeSQL($mysqli), $msg);
	}

    /**
     * @dataProvider \Littled\Tests\Filters\DataProvider\BooleanContentFilterTestDataProvider::formatQueryStringTestProvider()
     * @param string $expected
     * @param $value
     * @return void
     */
    function testFormatQueryString(string $expected, $value, $msg)
    {
        $o = new BooleanContentFilter('Label', 'key');
        $o->value = $value;
        $this->assertEquals($expected, $o->formatQueryString(), $msg);
    }
}
