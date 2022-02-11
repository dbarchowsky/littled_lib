<?php
namespace Littled\Tests\Log;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Log\Debug;
use PHPUnit\Framework\TestCase;


class DebugTest extends TestCase
{
    function testGetShortMethodName()
    {
        $this->assertEquals('DebugTest::testGetShortMethodName', Debug::getShortMethodName());
    }
}