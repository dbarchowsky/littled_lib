<?php
namespace LittledTests\Log;

use Littled\Log\Log;
use PHPUnit\Framework\TestCase;


class DebugTest extends TestCase
{
    function testGetShortMethodName()
    {
        $this->assertEquals('DebugTest::testGetShortMethodName', Log::getShortMethodName());
    }
}