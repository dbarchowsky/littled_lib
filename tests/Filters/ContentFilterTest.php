<?php
namespace Littled\Tests\Filters;

require_once (realpath(dirname(__FILE__).'/../../').'/_dbo/connections/test_data.php');

use Littled\Filters\ContentFilter;
use PHPUnit\Framework\TestCase;

class ContentFilterTest extends TestCase
{
	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	public function testEscapeSQL()
	{
		$mysqli = new \mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA);

		/* test null value */
		$cf = new ContentFilter('Test Filter', 'p_test', null, 50);
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('null', $escaped);

		/* test empty string */
		$cf->value = '';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('\'\'', $escaped);

		/* test non-empty string */
		$cf->value = 'foo';
		$escaped = $cf->escapeSQL($mysqli);
		$this->assertEquals('\'foo\'', $escaped);

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
