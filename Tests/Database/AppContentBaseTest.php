<?php
namespace Littled\Tests\Database;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Database\AppContentBase;
use PHPUnit\Framework\TestCase;


class AppContentBaseTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\Database\DataProvider\AppContentBaseTestDataProvider::makePluralTestProvider()
     * @param string $str
     * @param string $expected
     * @return void
     */
    function testMakePlural(string $str, string $expected)
    {
        $this->assertEquals($expected, AppContentBase::makePlural($str));
    }
}