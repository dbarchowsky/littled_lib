<?php
namespace Littled\Tests\Filters;

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
     * @dataProvider \Littled\Tests\Filters\DataProvider\DateContentFilterTestDataProvider::collectValueTestProvider()
     * @param ?string $value
     * @param ?string $expected
     * @param string $msg
     */
    function testCollectValue(?string $value, ?string $expected, string $msg='')
    {
        $o = new DateContentFilter('Test Date Filter', 'dateFilter');

        if ($value !== null) {
            $_POST[$o->key] = $value;
        }
        $o->collectValue();
        if ($expected === null) {
            $this->assertNull($o->value, $msg);
        }
        else {
            $this->assertEquals($expected, $o->value, $msg);
        }
        $_POST = [];
    }

    /**
     * @dataProvider \Littled\Tests\Filters\DataProvider\DateContentFilterTestDataProvider::escapeSQLTestProvider()
     * @param $value
     * @param ?string $expected
     * @param string $msg
     */
	public function testEscapeSQL($value, ?string $expected, $msg='')
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
        if ($value !== null) {
            $cf->value = $value;
        }
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals($expected, $escaped);
	}
}
