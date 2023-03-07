<?php
namespace Littled\Tests\Filters;

use Littled\Tests\TestHarness\Filters\KeywordContentFiltersTestHarness;
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