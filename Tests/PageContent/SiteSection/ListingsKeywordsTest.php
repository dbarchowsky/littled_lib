<?php
namespace Littled\Tests\PageContent\SiteSection;

use Littled\Exception\ConfigurationUndefinedException;
use PHPUnit\Framework\TestCase;
use Littled\App\LittledGlobals;
use Littled\PageContent\SiteSection\ListingsKeywords;


class ListingsKeywordsTest extends TestCase
{
	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testConstructor()
    {
        $test_record_id = 43;
        $o = new ListingsKeywords($test_record_id, 3);
        self::assertEquals('Record id', $o->id->label);
        self::assertEquals(LittledGlobals::ID_KEY, $o->id->key);
        self::assertEquals($test_record_id, $o->id->value);
    }
}