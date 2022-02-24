<?php
namespace Littled\Tests\Log;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Exception;
use Littled\Log\Debug;
use PHPUnit\Framework\TestCase;
use Throwable;


class DebugTest extends TestCase
{
    function testGetShortMethodName()
    {
        $this->assertEquals('DebugTest::testGetShortMethodName', Debug::getShortMethodName());
    }
}