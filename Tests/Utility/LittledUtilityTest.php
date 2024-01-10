<?php
namespace LittledTests\Utility;

use Littled\Utility\LittledUtility;
use PHPUnit\Framework\TestCase;


class LittledUtilityTest extends TestCase
{
	/**
	 * @dataProvider \LittledTests\DataProvider\Utility\LittledUtilityTestDataProvider::joinPathsTestProvider()
	 * @param string $expected
	 * @param array $parts
	 * @return void
	 */
	function testJoinPaths(string $expected, array $parts)
	{
		$this->assertEquals($expected, call_user_func_array(array('\Littled\Utility\LittledUtility', 'joinPaths'), $parts));
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Utility\LittledUtilityTestDataProvider::overlapTestProvider()
     * @param string $expected
     * @param string $a
     * @param string $b
     * @return void
     */
    function testOverlap(string $expected, string $a, string $b)
    {
        $this->assertEquals($expected, LittledUtility::overlap($a, $b));
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Utility\LittledUtilityTestDataProvider::stripPathLevelsTestProvider()
     * @param string $expected
     * @param string $path
     * @param int $levels
     * @param string $msg
     * @return void
     */
    function testStripPathLevels(string $expected, string $path, int $levels, string $msg='')
    {
        $this->assertEquals($expected, LittledUtility::stripPathLevels($path, $levels), $msg);
    }
}