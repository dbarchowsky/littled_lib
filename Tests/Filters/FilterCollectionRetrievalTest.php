<?php
namespace Littled\Tests\Filters;

use Littled\Tests\Filters\TestHarness\FilterCollectionChildWithQuery;
use Littled\Tests\Filters\TestHarness\TestTableFilters;
use PHPUnit\Framework\TestCase;
use Exception;

class FilterCollectionRetrievalTest extends TestCase
{
	public const TEST_LISTINGS_LENGTH = 5;

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
	 * @dataProvider \Littled\Tests\Filters\DataProvider\FilterCollectionTestDataProvider::retrieveNeighborIdsTestProvider()
	 * @throws Exception
	 */
	function testRetrieveNeighborIds(?int $expected_prev_id, ?int $expected_next_id, int $record_id, int $page, string $msg)
	{
		$f = new TestTableFilters();
		$f->page->value = $page;
		$f->listings_length->value = self::TEST_LISTINGS_LENGTH;

		$f->retrieveNeighborIds($record_id);
		$this->assertEquals($expected_prev_id, $f->previous_record_id, $msg.', previous id');
		$this->assertEquals($expected_next_id, $f->next_record_id, $msg.', next id');
	}
}