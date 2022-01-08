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
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        $row = $data[0];
        $this->assertIsString($row->name);
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
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        $this->assertGreaterThan(0, $fc->record_count);

        // filter that matches some records
        $fc->name_filter->value = 'unit';
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));

        // filter that matches no records
        $fc->name_filter->value = 'string that does not match';
        $data = $fc->retrieveListings();
        $this->assertCount(0, $data);
    }
}