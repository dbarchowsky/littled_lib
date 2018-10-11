<?php
namespace Littled\Tests\Filters;

require_once (realpath(dirname(__FILE__).'/../../').'/_dbo/connections/test_data.php');

use Littled\Filters\DateContentFilter;
use PHPUnit\Framework\TestCase;

class DateContentFilterTest extends TestCase
{
	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	public function testEscapeSQL()
	{
		$mysqli = new \mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA);

		/* test null value */
		$cf = new DateContentFilter('Test Filter', 'p_test', null, 50);
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test empty string */
		$cf->value = '';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test valid date string */
		$cf->value = '6/15/2016';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals("'2016-06-15'", $escaped);

		/* test invalid date string */
		$cf->value = 'fdfjdlfadlfdslfjdl';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test integer value */
		$cf->value = 45;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test bool value */
		$cf->value = true;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);
	}
}
