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

		list($prev_id, $next_id) = $f->retrieveNeighborIds($record_id);
		$this->assertEquals($expected_prev_id, $prev_id, $msg.', previous id');
		$this->assertEquals($expected_next_id, $next_id, $msg.', next id');
	}
}