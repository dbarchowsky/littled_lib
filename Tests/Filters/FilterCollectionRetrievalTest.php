<?php
namespace Littled\Tests\Filters;

use Littled\Tests\DataProvider\Filters\RetrieveNeighborIdsTestData;
use Littled\Tests\TestHarness\Filters\FilterCollectionChildWithQuery;
use Littled\Tests\TestHarness\Filters\TestTableFilters;
use PHPUnit\Framework\TestCase;
use Exception;

class FilterCollectionRetrievalTest extends TestCase
{
	public const TEST_LISTINGS_LENGTH = 5;

    /**
     * @dataProvider \Littled\Tests\DataProvider\Filters\FilterCollectionTestDataProvider::retrieveNeighborIdsTestProvider()
     * @return void
     * @throws Exception
     */
    function testCalculateRecordOffset(RetrieveNeighborIdsTestData $data)
    {
        $listings_data = $data->filters->retrieveListings();
        $this->assertEquals($data->expected->offset, $data->filters->publicCalculateRecordOffset($data->record_id, $listings_data), $data->msg);
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
    function testRetrieveNeighborIds(RetrieveNeighborIdsTestData $data)
    {
        $data->filters->retrieveNeighborIds($data->record_id);
        $this->assertEquals($data->expected->previous_record_id, $data->expected->previous_record_id, $data->msg);
        $this->assertEquals($data->expected->next_record_id, $data->expected->next_record_id, $data->msg);
    }
}