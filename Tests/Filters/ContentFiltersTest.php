<?php
namespace LittledTests\Filters;

use Littled\App\LittledGlobals;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;
use Littled\Filters\FilterCollection;
use Littled\PageContent\SiteSection\ContentProperties;
use LittledTests\TestHarness\Filters\ContentFiltersProcedureChild;
use LittledTests\TestHarness\Filters\ContentFiltersChild;
use LittledTests\TestHarness\PageContent\SiteSection\ContentPropertiesTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use PHPUnit\Framework\TestCase;
use Exception;

class ContentFiltersTest extends TestCase
{
    /** @var int */
    const CHILD_CONTENT_TYPE_ID = 1;

    function testConstruct()
    {
        $cf = new ContentFiltersChild();
        $this->assertEquals('article', $cf->content_properties->label->value);
        $this->assertEquals(ContentProperties::class, get_class($cf->content_properties));
    }

    function testConstructUsingContentPropertiesSubclass()
    {
        $cf = new ContentFiltersChild(ContentPropertiesTestHarness::class);
        $this->assertEquals(ContentPropertiesTestHarness::class, get_class($cf->content_properties));

        $this->expectException(InvalidTypeException::class);
        new ContentFiltersChild(FilterCollection::class);
    }

    /**
     * @return void
     * @throws NotImplementedException
     */
    function testContentTypeIdUnset()
    {
        self::expectExceptionMessageMatches('/Content type id not set.*ContentFilters/');
        ContentFilters::getContentTypeId();
    }

    /**
     * @return void
     * @throws NotImplementedException
     */
    function testContentTypeId()
    {
        ContentFilters::setContentTypeId(22);
        $this->assertEquals(22, ContentFilters::getContentTypeId());

        $this->assertEquals(self::CHILD_CONTENT_TYPE_ID, ContentFiltersChild::getContentTypeId());
    }

    function testDefaultListingsLength()
    {
        $cf = new ContentFiltersChild();
        $this->assertGreaterThan(0, $cf->listings_length->value);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testPluralLabel()
    {
        $cf = new ContentFiltersChild();
        $cf->retrieveListings();
        $this->assertGreaterThan(1, $cf->record_count);
        $this->assertEquals('Articles', $cf->pluralLabel());

        $cf->record_count = 1;
        $this->assertEquals('Article', $cf->pluralLabel());

        $cf->record_count = 0;
        $this->assertEquals('Articles', $cf->pluralLabel());
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListings()
    {
        $cf = new ContentFiltersChild();
        $data = $cf->retrieveListings();
        $this->assertGreaterThan(2, count($data));
        $last_id = 0;
        $i = 0;
        foreach($data as $row) {
            if ($i> 2) {
                break;
            }
            $this->assertIsNumeric($row->id);
            $this->assertIsString($row->title);
            $this->assertNotEquals($last_id, $row->id);
            $last_id = $row->id;
            $i++;
        }
        $this->assertGreaterThan(0, $cf->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListingsUsingProcedure()
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        $cf = new ContentFiltersProcedureChild();
        $data = $cf->retrieveListings();
        $this->assertCount($cf->listings_length->value, $data);
        $this->assertIsNumeric($cf->record_count);
        $this->assertGreaterThan($cf->listings_length->value, $cf->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testExecuteListingsQueryAsAssociativeArray()
    {
        $cf = new ContentFiltersChild();
        $data = $cf->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        foreach($data as $row) {
            $this->assertIsNumeric($row->id);
            $this->assertIsString($row->title);
        }
    }
}
