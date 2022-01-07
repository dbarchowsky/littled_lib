<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Tests\Filters\Samples\TestTableFilters;
use Littled\Tests\Filters\Samples\TestTableFiltersWithProcedure;
use Littled\Tests\Filters\Samples\TestTableFiltersWithQuery;
use PHPUnit\Framework\TestCase;
use Exception;

class FilterCollectionTest extends TestCase
{
    function testFormatListingsQueryNotImplemented()
    {
        // Test when not implemented in child class
        $fc = new TestTableFilters();
        $args = $fc->formatListingsQueryTest();
        $this->assertCount(3, $args);
        $this->assertEquals('', $args[0]);  /* query string */
        $this->assertEquals('', $args[1]);  /* types descriptor */
        $this->assertNull($args[2]);                /* start of variables to bind to query */
    }

    /**
     * @return void
     */
    function testFormatListingsQueryUsingProcedure()
    {
        $fc = new TestTableFiltersWithProcedure();
        $args = $fc->formatListingsQuery();
        $this->assertCount(9, $args);
        $this->assertMatchesRegularExpression('/^CALL testTableListingsSelect\(/', $args[0]);
        $this->assertEquals('iisiiss', $args[1]);
        $this->assertNull($args[2]); /* page filter, the first filter value in the list */
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListings()
    {
        $fc = new TestTableFiltersWithProcedure();
        $result = $fc->retrieveListings();
        $this->assertGreaterThan(0, $result->num_rows);
        $row = $result->fetch_object();
        $this->assertIsString($row->name);
        $result->free();
        $this->assertGreaterThan(0, $fc->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListingsWithQuery()
    {
        $fc = new TestTableFiltersWithQuery();

        // no filters
        $result = $fc->retrieveListings();
        $this->assertGreaterThan(0, $result->num_rows);
        $result->free();
        $this->assertGreaterThan(0, $fc->record_count);

        // filter that matches some records
        $fc->name_filter->value = 'unit';
        $result = $fc->retrieveListings();
        $this->assertGreaterThan(0, $result->num_rows);
        $result->free();

        // filter that matches no records
        $fc->name_filter->value = 'string that does not match';
        $result = $fc->retrieveListings();
        $this->assertEquals(0, $result->num_rows);
        $result->free();
    }
}