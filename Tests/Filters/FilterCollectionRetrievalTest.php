<?php
namespace Littled\Tests\Filters;

use Littled\Tests\DataProvider\Filters\FilterCollectionTestExpectations;
use Littled\Tests\TestHarness\Filters\FilterCollectionChildWithQuery;
use Littled\Tests\TestHarness\Filters\TestTableFilters;
use Exception;

/**
 * Tests for FilterCollection routines that retrieve data.
 * Inherits from FilterCollectionTest in order to share its setup and tear down routines, specifically routines to
 * create temporary test records in test_table table.
 */
class FilterCollectionRetrievalTest extends FilterCollectionTestBase
{
	public const TEST_LISTINGS_LENGTH = 5;

    /**
     * @dataProvider \Littled\Tests\DataProvider\Filters\FilterCollectionTestDataProvider::calculateRecordPositionOnPageTestProvider()
     * @return void
     * @throws Exception
     */
    function testCalculateRecordPositionOnPage(?int $expected, ?int $record_id, ?int $page, ?int $listings_length, string $name_filter='')
    {
	    // N.B. take into account that temp records are added to the table as part of the setup for this class
	    $f = new TestTableFilters();
		$f->page->value = $page;
		$f->listings_length->value = $listings_length;
	    $f->name_filter->value = $name_filter;
        $data = $f->retrieveListings();
        $this->assertEquals($expected, $f->publicCalculateRecordOffset($record_id, $data));
    }

    /**
	 * @throws Exception
	 */
	function testSearchTitles()
	{
		$f = new TestTableFilters();
		$f->page->value = null;
		$f->listings_length->value = null;
		$f->name->value = '';

		$data = $f->searchTitles();
		$data1_size = count($data);
		$this->assertGreaterThan(0, $data1_size);

		$f->name->value = 'foo';
		$data = $f->searchTitles();
		$this->assertGreaterThan(0, count($data));
		$this->assertGreaterThan(count($data), $data1_size);
	}

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListings()
    {
        $fc = new TestTableFilters();
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
        $fc = new FilterCollectionChildWithQuery();

        // no filters
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        $this->assertGreaterThan(0, $fc->record_count);

        // filter that matches some records
        $fc->name_filter->value = 'foo';
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));

        // filter that matches no records
        $fc->name_filter->value = 'string that does not match';
        $data = $fc->retrieveListings();
        $this->assertCount(0, $data);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Filters\FilterCollectionTestDataProvider::retrieveNeighborIdsTestProvider()
     * @return void
     * @throws Exception
     */
	function testRetrieveNeighborIds(
		FilterCollectionTestExpectations $expected,
		int $record_id,
		?int $page,
		?int $listings_length,
		string $name_filter='',
		string $msg=''
	)
    {
		$f = new TestTableFilters();
		$f->page->value = $page;
	    $f->listings_length->value = $listings_length;
	    $f->name_filter->value = $name_filter;
        $f->retrieveNeighborIds($record_id);
        $this->assertEquals($expected->previous_record_id, $expected->previous_record_id, $msg);
        $this->assertEquals($expected->next_record_id, $expected->next_record_id, $msg);
    }
}