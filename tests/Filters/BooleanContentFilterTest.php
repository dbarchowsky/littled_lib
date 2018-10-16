<?php
namespace Littled\Tests\Filters;

use Littled\Filters\BooleanContentFilter;
use PHPUnit\Framework\TestCase;

class BooleanContentFilterTest extends TestCase
{
	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	public function testEscapeSQL()
	{
		$mysqli = new \mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA);

		/* test null value */
		$cf = new BooleanContentFilter('Test Filter', 'p_test', null);
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test integer value */
		$cf->value = 1;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('1', $escaped);

		/* test integer value */
		$cf->value = 0;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('0', $escaped);

		/* test true value */
		$cf->value = true;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('1', $escaped);

		/* test false value */
		$cf->value = false;
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('0', $escaped);
	}
}
