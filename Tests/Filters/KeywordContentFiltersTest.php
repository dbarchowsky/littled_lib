<?php
namespace LittledTests\Filters;

use LittledTests\TestHarness\Filters\KeywordContentFiltersTestHarness;
use PHPUnit\Framework\TestCase;


class KeywordContentFiltersTest extends TestCase
{
    function testConstructor()
    {
        $o = new KeywordContentFiltersTestHarness();
        $this->assertTrue(property_exists($o, 'keyword'));
        $this->assertEquals('kw', $o->keyword->key);
    }
}