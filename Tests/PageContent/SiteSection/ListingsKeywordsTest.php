<?php

namespace Littled\Tests\PageContent\SiteSection;

use Littled\App\LittledGlobals;
use Littled\PageContent\SiteSection\ListingsKeywords;

class ListingsKeywordsTest extends \PHPUnit\Framework\TestCase
{
    function testConstructor()
    {
        $test_record_id = 43;
        $o = new ListingsKeywords($test_record_id, 3);
        self::assertEquals('Record id', $o->id->label);
        self::assertEquals(LittledGlobals::ID_KEY, $o->id->key);
        self::assertEquals($test_record_id, $o->id->value);
    }
}