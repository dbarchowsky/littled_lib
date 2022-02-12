<?php
namespace Littled\Tests\Utility;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Utility\LittledUtility;
use PHPUnit\Framework\TestCase;


class LittledUtilityTest extends TestCase
{
	/**
	 * @dataProvider \Littled\Tests\Utility\DataProvider\LittledUtilityTestDataProvider::joinPathPartsTestProvider()
	 * @param string $expected
	 * @param array $parts
	 * @return void
	 */
	function testJoinPathParts(string $expected, array $parts)
	{
		$this->assertEquals($expected, LittledUtility::joinPathParts($parts));
	}
}